#!/usr/bin/env bash
# Checks that every page of the site responds as it should.
#
#   composer start              # in one terminal
#   scripts/smoke-test.sh       # in another (or: composer test)
#
# Takes the site's address as an optional argument (default http://localhost:8000).
set -u

base="${1:-http://localhost:8000}"
failures=0

# check <path> <status> [text the page must contain]...
check() {
  local path="$1" status="$2"
  shift 2
  local body headers code
  body=$(mktemp)
  headers=$(mktemp)
  code=$(curl -s -o "$body" -D "$headers" -w '%{http_code}' "$base$path")

  if [ "$code" != "$status" ]; then
    echo "FAIL $path: status $code, expected $status"
    failures=$((failures + 1))
  elif grep -qiE '<b>(warning|notice|deprecated|fatal error)</b>|Whoops' "$body"; then
    echo "FAIL $path: the page shows a PHP error"
    failures=$((failures + 1))
  elif grep -qi '^set-cookie:' "$headers"; then
    echo "FAIL $path: sets a cookie, but the privacy page promises visitors none"
    failures=$((failures + 1))
  else
    local missing=""
    for text in "$@"; do
      grep -qF -- "$text" "$body" || missing="$missing \"$text\""
    done
    if [ -n "$missing" ]; then
      echo "FAIL $path: missing$missing"
      failures=$((failures + 1))
    else
      echo "ok   $path ($code)"
    fi
  fi
  rm -f "$body" "$headers"
}

check /                     200 'Native apps' 'Meet Winston' 'href="'"$base"'/apps/winston"'
check /apps                 200 'Winston' 'A native Windows 11 client for Mastodon.'
check /apps/winston         200 'Download for Windows' 'Download Winston' 'application/ld+json' 'id="features"' 'id="faq"'
check /apps/winston/privacy 200 'Privacy policy' 'Last updated'
check /about                200 'StackWerk is a one-person software workshop'
check /privacy              200 'No cookies for visitors'
check /no-such-page         404 'This page wandered off.'
check /winston              302
check /sitemap.xml          200 '<urlset' '/apps/winston</loc>'
check /robots.txt           200 'Sitemap:'

# Download buttons should point at the files of Winston's latest release. GitHub can
# be unreachable or rate-limit a runner, so this only warns: the page then links to
# the releases page instead.
if curl -s "$base/apps/winston" | grep -q 'releases/download/'; then
  echo "ok   download buttons link to the latest release's files"
else
  echo "warn download buttons link to the releases page (GitHub couldn't be reached?)"
fi

if [ "$failures" -gt 0 ]; then
  echo "$failures check(s) failed"
  exit 1
fi
echo "All checks passed"
