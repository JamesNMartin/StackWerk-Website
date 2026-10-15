# StackWerk website

The source of [stackwerk.co](https://stackwerk.co), the home of StackWerk's apps. It's built with
[Kirby](https://getkirby.com) 5, a file-based CMS: every page is a folder in `content/`, and there's no database.

The first app is **Winston**, a native Windows 11 client for Mastodon, at
[stackwerk.co/apps/winston](https://stackwerk.co/apps/winston) (or the short link `stackwerk.co/winston`).

## What's on the site

| Page | Template | What it shows |
| --- | --- | --- |
| `/` | `home` | Headline, the spotlight app with its screenshot, the other apps, and "How the apps are made" |
| `/apps` | `apps` | A card for every app |
| `/apps/winston` | `app` | Winston's product page: hero with the Microsoft Store badge, features, screenshots, platform and privacy panels, install steps, requirements and FAQ |
| `/apps/winston/privacy` | `legal` | Winston's privacy policy |
| `/about`, `/privacy` | `default`, `legal` | About StackWerk; the website's privacy page |
| `/sitemap.xml`, `/robots.txt` | (plugin routes) | For search engines |

Things worth knowing:

- **Apps come from the Microsoft Store.** An app page shows "Coming soon to the Microsoft Store" until its *Store ID*
  is filled in; then Microsoft's "Download from the Microsoft Store" badge appears in the hero, the *Get* section, the
  sticky bar and the home page, linking to the listing. The badge images are Microsoft's, saved in `assets/images/`
  (dark badge on light pages, light badge on dark ones, as Microsoft asks).
- **Each app has its own colour.** The *Accent colour* field colours that app's buttons, icons and highlights; text on
  it switches between white and black for contrast.
- **Light and dark.** The site follows the visitor's system setting, and an app can have a dark-theme hero screenshot.
- **No tracking.** No cookies for visitors, no analytics and no third-party requests: fonts, icons and the Store badge
  are served from the site. `/privacy` says so, and `scripts/smoke-test.sh` fails if a page starts setting a cookie.
- **No GitHub links.** The apps are closed source, so the smoke test also fails if any page links to github.com.
- **A contact email shows up everywhere once set.** Fill in *Contact email* under the Panel's settings and it appears in
  the footer, under the app FAQ and at the end of each privacy policy. The Microsoft Store also asks for a support
  contact and a privacy policy URL: `https://stackwerk.co/apps/winston/privacy` is Winston's.
- **Search and sharing.** Every page has a description, canonical URL and Open Graph image (`og.png` in a page's folder,
  else `assets/images/og.png`); app pages add `SoftwareApplication` structured data.

## Working on it locally

You need PHP 8.2 to 8.5 and [Composer](https://getcomposer.org).

```sh
composer install    # installs Kirby into kirby/ and its libraries into vendor/
composer start      # serves the site at http://localhost:8000
composer test       # in another terminal: checks every page responds as it should
```

On `localhost` Kirby runs with debug mode on (`site/config/config.localhost.php`), so errors show in the browser.

**On Windows**, one script does all of this: it installs PHP with winget if needed, sets up its `php.ini` (extension
folder and the extensions Composer and Kirby need), gets Composer, runs `composer install` and starts the site.
It's safe to run every time you want to work on the site, and doesn't need admin:

```powershell
powershell -ExecutionPolicy Bypass -File scripts\windows-dev.ps1
```

`-NoStart` sets up without starting the site, and `-Port 8080` uses another port. The script also adds a `composer`
command to your PowerShell profile that runs Composer through PHP, for setups where Composer's own `composer.bat`
can't find PHP.

### Editing content

Open **http://localhost:8000/panel**. The first visit asks you to create an account; that account lives in
`site/accounts/`, which git ignores.

You can also edit the text files in `content/` directly. Each page is a folder with one `.txt` file named after its
template; fields are separated by `----`. Text fields use [KirbyText](https://getkirby.com/docs/guide/content/text-formatting)
(Markdown, plus tags like `(link: apps/winston text: Winston)`).

### Adding an app

1. In the Panel, open **Apps** and add a page. Pick the **App** template.
2. On **Overview**, fill in the tagline, summary, platform, price and accent colour, upload the icon to the *App icon*
   section, and choose a *Hero screenshot*.
3. On **Microsoft Store**, add the install steps and requirements. Once the app is live in the Store, fill in its
   *Store ID* (Partner Center → the app → Product identity, 12 characters like `9XXXXXXXXXXX`) and the badges appear.
4. Fill in **Features**, upload **Screenshots** (each needs alternative text), and add **FAQ** entries.
5. To give the app a privacy policy, add a page under it with the **Policy** template; the app page links to it.
6. Set the page to **Public** (listed) so it appears on the home page and in the menus.

To feature a different app on the home page, pick it as *Spotlight app* on the home page.

### Where things are

| Path | What's there |
| --- | --- |
| `content/` | All pages, images and text |
| `site/blueprints/` | The Panel's forms for each page type (`pages/`), file type (`files/`), and the site |
| `site/templates/`, `site/snippets/` | The HTML. `snippets/layout.php` is the page shell: head, header and footer |
| `site/controllers/app.php` | Gets an app page's Store link, hero and screenshots ready for its template |
| `site/plugins/stackwerk/` | Page helpers (`appIcon`, `accentStyle`, `storeUrl`, `isComingSoon`…), sitemap, robots and `/<app>` short links |
| `site/config/` | Settings; `config.localhost.php` and `config.stackwerk.co.php` apply only on those hosts |
| `assets/css/site.css` | All the styles, with colours as variables at the top |
| `assets/fonts/` | Space Grotesk and Inter (SIL Open Font License) |
| `assets/icons/` | [Lucide](https://lucide.dev) icons (ISC licence); the feature icon picker lists the ones in `site/blueprints/fields/icon.yml` |
| `scripts/smoke-test.sh` | Checks each page's status and content (run by `composer test` and CI) |

## Checks and the deploy package

Every push runs `.github/workflows/site.yml`: it installs Kirby, checks the PHP, starts the site and runs the smoke test.
It then uploads a ready-to-upload copy of the site, Kirby included, as the **stackwerk-site** artifact on the run's page.

## Putting it on stackwerk.co

### What the server needs

- PHP 8.2 to 8.5 with the `curl`, `ctype`, `dom`, `filter`, `hash`, `iconv`, `json`, `libxml`, `mbstring`,
  `openssl` and `SimpleXML` extensions (most hosts have them), plus `gd` or `imagick` if you'll upload images in the Panel.
- Apache with `mod_rewrite` (the included `.htaccess` does the rest), or nginx with the configuration below.
- The web server must be able to write to `content/`, `media/`, `site/accounts/`, `site/cache/` and `site/sessions/`.

### Uploading

Either:

- **Download the package from CI:** open the latest successful run of the *Site* workflow on GitHub, download the
  **stackwerk-site** artifact, unzip it, and upload its contents (including the hidden `.htaccess`) to the folder
  stackwerk.co serves, often `public_html`; or
- **Build on the server:** `git clone` this repository into that folder and run `composer install --no-dev`. To update
  later, `git pull` and run `composer install --no-dev` again.

Then open https://stackwerk.co and check the pages, and `https://stackwerk.co/winston` for the short link.

If your host doesn't already send `www.stackwerk.co` and plain `http://` to `https://stackwerk.co`, uncomment the
redirect near the top of `.htaccess` once HTTPS works for the domain.

### The Panel on the live site

For safety, Kirby won't create the first Panel account on a public server. Either create it locally and upload
`site/accounts/` to the server, or temporarily add `'panel' => ['install' => true]` to
`site/config/config.stackwerk.co.php`, create the account at https://stackwerk.co/panel, then take that line out again.

Kirby is free to try, but a site in production needs a [Kirby licence](https://getkirby.com/buy). Once bought,
activate it in the Panel (the *Activate* button in the sidebar); the licence file is saved to `site/config/.license`,
which git ignores.

Changes made in the Panel on the live site are written to the server's `content/` folder, not to git. Pick one place
to edit (git or the live Panel) or copy `content/` back into the repository now and then, so a deploy doesn't
overwrite edits.

### nginx

```nginx
server {
  server_name stackwerk.co;
  root /var/www/stackwerk.co;
  index index.php;

  # Kirby's sources, content and settings are never served directly
  location ~ ^/(content|site|kirby|vendor)/ { rewrite ^ /index.php last; }
  location ~ /\.(?!well-known/) { deny all; }

  location / { try_files $uri $uri/ /index.php$is_args$args; }

  location ~ \.php$ {
    include fastcgi_params;
    fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    fastcgi_pass unix:/run/php/php8.3-fpm.sock;
  }
}
```

## Screenshots

Winston's screenshots come from its own CI: the *smoke-screenshots* artifact of a Winston build (the `smoke-signedin`
folder), which shows made-up accounts on a test server. To refresh them, replace the images in `content/1_apps/1_winston/` keeping their file
names, or upload new ones in the Panel.
