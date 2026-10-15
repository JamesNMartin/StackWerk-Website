<#
.SYNOPSIS
  Sets up PHP and Composer on Windows for working on the StackWerk website, then starts it.

.DESCRIPTION
  Safe to run as often as you like, and doesn't need admin. It:
    1. finds PHP 8.2-8.5, or installs PHP 8.3 with winget;
    2. makes PHP's php.ini point at its extension folder and switch on the
       extensions Composer and Kirby need (curl, fileinfo, gd, intl, mbstring, openssl, zip);
    3. finds Composer, or downloads it (checking its checksum);
    4. adds a `composer` command to the PowerShell profile you already use, running Composer
       through PHP, so `composer install` and `composer start` work in PowerShell;
    5. runs `composer install` (Kirby and its libraries);
    6. starts the site at http://localhost:8000 and opens it (Ctrl+C stops it).

  Run it from the project folder:
    powershell -ExecutionPolicy Bypass -File scripts\windows-dev.ps1

.PARAMETER Port
  The port for the site (default 8000).

.PARAMETER NoStart
  Set everything up, but don't start the site.
#>
param(
    [int]$Port = 8000,
    [switch]$NoStart
)

$Extensions = 'curl', 'fileinfo', 'gd', 'intl', 'mbstring', 'openssl', 'zip'
$IniMarker  = '; Added by scripts/windows-dev.ps1 (StackWerk website)'

function Write-Step([string]$Text) { Write-Host "`n==> $Text" -ForegroundColor Cyan }
function Write-Done([string]$Text) { Write-Host "    $Text" -ForegroundColor Green }
function Write-Note([string]$Text) { Write-Host "    $Text" }
function Stop-Setup([string]$Text) { Write-Host "`n$Text" -ForegroundColor Red; exit 1 }

# Picks up anything installed since this terminal opened.
function Update-SessionPath {
    $env:Path = [Environment]::GetEnvironmentVariable('Path', 'Machine') + ';' + [Environment]::GetEnvironmentVariable('Path', 'User')
}

# Runs PHP without printing its startup warnings; returns its output lines.
function Invoke-Php([string]$Exe, [string[]]$Arguments) {
    $previous = $ErrorActionPreference
    $ErrorActionPreference = 'Continue'
    try { & $Exe -d display_startup_errors=0 -d log_errors=0 @Arguments 2>$null }
    finally { $ErrorActionPreference = $previous }
}

# The real php.exe: follows winget's symlinks and other launchers.
function Find-Php {
    $command = Get-Command php -CommandType Application -ErrorAction SilentlyContinue | Select-Object -First 1
    if (-not $command) { return $null }

    $binary = (Invoke-Php $command.Source @('-n', '-r', 'echo PHP_BINARY;')) -join ''
    if (-not $binary) { $binary = $command.Source }

    $item = Get-Item $binary -ErrorAction SilentlyContinue
    if ($item -and $item.Target) { $binary = @($item.Target)[0] }

    return $binary
}

# Rewrites php.ini: drops any extension_dir line and any line for our extensions, then adds
# them again at the end, pointing at the ext folder next to php.exe. Running it twice is harmless.
function Set-PhpIni([string]$Ini, [string]$ExtDir, [string[]]$Wanted) {
    $names   = ($Extensions | ForEach-Object { [regex]::Escape($_) }) -join '|'
    $ownLine = '^\s*(extension_dir\s*=|extension\s*=\s*"?(php_)?(' + $names + ')(\.dll)?"?\s*(;.*)?$)'

    $lines = @(Get-Content $Ini | Where-Object { $_ -notmatch $ownLine -and $_ -ne $IniMarker })
    $end = $lines.Count
    while ($end -gt 0 -and $lines[$end - 1].Trim() -eq '') { $end-- }
    $lines = @(if ($end -gt 0) { $lines[0..($end - 1)] })

    $lines += ''
    $lines += $IniMarker
    $lines += "extension_dir = `"$ExtDir`""
    $lines += $Wanted | ForEach-Object { "extension=$_" }

    Set-Content -Path $Ini -Value $lines
}

# --- 1. PHP ------------------------------------------------------------------

Write-Step 'Checking PHP'
Update-SessionPath
$php = Find-Php

if (-not $php) {
    if (-not (Get-Command winget -ErrorAction SilentlyContinue)) {
        Stop-Setup 'PHP is not installed and winget is not available. Install PHP 8.3 from https://windows.php.net/download/ and run this again.'
    }
    Write-Note 'PHP not found; installing PHP 8.3 with winget...'
    winget install --id PHP.PHP.8.3 --exact --source winget --accept-source-agreements --accept-package-agreements
    Update-SessionPath
    $php = Find-Php
    if (-not $php) { Stop-Setup 'winget installed PHP, but this terminal still cannot find it. Close the terminal, open a new one and run this again.' }
}

$version   = (Invoke-Php $php @('-n', '-r', 'echo PHP_VERSION;')) -join ''
$versionId = 0
[void][int]::TryParse(((Invoke-Php $php @('-n', '-r', 'echo PHP_VERSION_ID;')) -join ''), [ref]$versionId)
if ($versionId -lt 80200 -or $versionId -ge 80600) {
    Stop-Setup "Kirby needs PHP 8.2 to 8.5, but $php is PHP $version. Install PHP 8.3 with: winget install PHP.PHP.8.3"
}
Write-Done "PHP $version at $php"

# --- 2. php.ini --------------------------------------------------------------

Write-Step 'Setting up php.ini'
$phpDir = Split-Path $php
$extDir = Join-Path $phpDir 'ext'
if (-not (Test-Path $extDir)) { Stop-Setup "PHP's extension folder isn't where expected: $extDir" }

# Extensions built into this PHP need no line; ones without a DLL can't be switched on.
$builtIn = @(Invoke-Php $php @('-n', '-m'))
$wanted  = @()
foreach ($name in $Extensions) {
    if ($builtIn -contains $name) { continue }
    if (Test-Path (Join-Path $extDir "php_$name.dll")) { $wanted += $name }
    else { Write-Note "Warning: this PHP has no $name extension (php_$name.dll)" }
}

$ini = (Invoke-Php $php @('-r', 'echo php_ini_loaded_file();')) -join ''
if (-not $ini) {
    $ini = Join-Path $phpDir 'php.ini'
    $template = Join-Path $phpDir 'php.ini-development'
    if (Test-Path $template) { Copy-Item $template $ini } else { Set-Content -Path $ini -Value '[PHP]' }
    Write-Note "Created $ini"
}

Set-PhpIni -Ini $ini -ExtDir $extDir -Wanted $wanted

# PHP looks for php.ini next to php.exe; if it still doesn't find it, point PHPRC at the folder.
if (-not ((Invoke-Php $php @('-r', 'echo php_ini_loaded_file();')) -join '')) {
    [Environment]::SetEnvironmentVariable('PHPRC', $phpDir, 'User')
    $env:PHPRC = $phpDir
    Write-Note "Set PHPRC to $phpDir so PHP finds its php.ini"
}

$loaded  = @(Invoke-Php $php @('-m'))
$missing = @($Extensions | Where-Object { $loaded -notcontains $_ })
if ($missing.Count -gt 0) { Stop-Setup ("PHP still can't load: " + ($missing -join ', ') + ". Check $ini") }
Write-Done "$ini now loads $($Extensions -join ', ')"

# --- 3. Composer -------------------------------------------------------------

Write-Step 'Checking Composer'
$phar = $null
$composerCommand = Get-Command composer -CommandType Application -ErrorAction SilentlyContinue | Select-Object -First 1
if ($composerCommand) {
    $candidate = Join-Path (Split-Path $composerCommand.Source) 'composer.phar'
    if (Test-Path $candidate) { $phar = $candidate }
}

if (-not $phar) {
    $toolsDir = Join-Path $env:LOCALAPPDATA 'StackWerk'
    $phar = Join-Path $toolsDir 'composer.phar'
    if (-not (Test-Path $phar)) {
        Write-Note 'Downloading Composer from getcomposer.org...'
        New-Item -ItemType Directory -Force -Path $toolsDir | Out-Null
        [Net.ServicePointManager]::SecurityProtocol = [Net.ServicePointManager]::SecurityProtocol -bor [Net.SecurityProtocolType]::Tls12
        $base = 'https://getcomposer.org/download/latest-stable'
        Invoke-WebRequest -UseBasicParsing -Uri "$base/composer.phar" -OutFile $phar
        $expected = ((Invoke-WebRequest -UseBasicParsing -Uri "$base/composer.phar.sha256sum").Content -split '\s+')[0]
        if ((Get-FileHash $phar -Algorithm SHA256).Hash -ne $expected) {
            Remove-Item $phar
            Stop-Setup 'The Composer download did not match its checksum, so it was deleted. Try again.'
        }
    }
}
$composerVersion = Invoke-Php $php @($phar, '--version', '--no-ansi') | Select-Object -First 1
Write-Done "$composerVersion ($phar)"

# A `composer` command for PowerShell that runs Composer through PHP directly. It goes only into
# the profile folders of PowerShell editions you already have a profile for (PowerShell 7 and/or
# Windows PowerShell), so it can't trip a "running scripts is disabled" error in one you don't use.
$profileTag  = '# StackWerk website: run Composer through PHP'
$profileLine = "function composer { php '$phar' @args }  $profileTag"
$documents   = [Environment]::GetFolderPath('MyDocuments')
$added       = @()
foreach ($edition in 'PowerShell', 'WindowsPowerShell') {
    $folder = Join-Path $documents $edition
    if (-not (Get-ChildItem -Path $folder -Filter '*profile.ps1' -ErrorAction SilentlyContinue)) { continue }

    $profilePath = Join-Path $folder 'profile.ps1'
    $existing = @(if (Test-Path $profilePath) { Get-Content $profilePath })
    if ($existing -notcontains $profileLine) {
        $kept = @($existing | Where-Object { $_ -notlike "*$profileTag" })
        Set-Content -Path $profilePath -Value ($kept + $profileLine)
    }
    $added += $profilePath
}
if ($added.Count -gt 0) {
    Write-Done ('New PowerShell windows can run `composer install` and `composer start` (' + ($added -join ', ') + ')')
}
else {
    Write-Note 'To use `composer` in PowerShell, add this line to your PowerShell profile:'
    Write-Note "  $profileLine"
}

# --- 4. The site -------------------------------------------------------------

$root = if ($PSScriptRoot) { Split-Path -Parent $PSScriptRoot } else { (Get-Location).Path }
if (-not (Test-Path (Join-Path $root 'composer.json'))) { $root = (Get-Location).Path }
if (-not (Test-Path (Join-Path $root 'composer.json'))) { Stop-Setup 'Run this from the StackWerk-Website folder.' }

Write-Step 'Installing Kirby (composer install)'
Push-Location $root
try {
    & $php $phar install --no-interaction
    if ($LASTEXITCODE -ne 0) { Stop-Setup 'composer install failed; see the messages above.' }
}
finally { Pop-Location }
Write-Done 'Kirby is installed'

if ($NoStart) {
    Write-Host "`nAll set. To start the site, run this script again without -NoStart." -ForegroundColor Green
    exit 0
}

Write-Step "Starting the site at http://localhost:$Port (Ctrl+C stops it)"
Write-Note "The Panel, for editing content, is at http://localhost:$Port/panel"
Start-Process "http://localhost:$Port"
Push-Location $root
try { & $php -S "localhost:$Port" kirby/router.php }
finally { Pop-Location }
