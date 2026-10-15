<?php

use Kirby\Cms\App as Kirby;
use Kirby\Cms\File;
use Kirby\Cms\Response;
use Kirby\Toolkit\Xml;
use StackWerk\Releases;

load([
    'StackWerk\\Releases' => 'src/Releases.php',
], __DIR__);

Kirby::plugin('stackwerk/website', [
    'options' => [
        'cache'        => true,
        'releases.ttl' => 60,
    ],

    'pageMethods' => [
        /** The app's icon: its image with the `icon` file template. */
        'appIcon' => function (): ?File {
            return $this->images()->template('icon')->first();
        },
        /**
         * CSS custom properties for the app's accent colour: `--app`, and
         * `--app-ink` for text placed on it (white or near-black, whichever reads better).
         */
        'accentStyle' => function (): string {
            $hex = strtolower(trim((string)$this->color()->value()));

            if (preg_match('/^#([0-9a-f]{3}|[0-9a-f]{6})$/', $hex) !== 1) {
                return '';
            }

            if (strlen($hex) === 4) {
                $hex = '#' . $hex[1] . $hex[1] . $hex[2] . $hex[2] . $hex[3] . $hex[3];
            }

            // WCAG relative luminance; white text needs the background below ~0.18 to reach 4.5:1
            $channel = function (string $part): float {
                $c = hexdec($part) / 255;
                return $c <= 0.03928 ? $c / 12.92 : (($c + 0.055) / 1.055) ** 2.4;
            };
            $luminance = 0.2126 * $channel(substr($hex, 1, 2))
                       + 0.7152 * $channel(substr($hex, 3, 2))
                       + 0.0722 * $channel(substr($hex, 5, 2));

            return '--app: ' . $hex . '; --app-ink: ' . ($luminance > 0.18 ? '#141311' : '#ffffff') . ';';
        },
        /** The newest GitHub release of the repository in the page's `github` field. */
        'latestRelease' => function (): ?array {
            return Releases::latest(trim((string)$this->github()->value()));
        },
        /** The page's `downloads`, linked to the files of its latest release. */
        'downloadLinks' => function (): array {
            return Releases::downloads($this);
        },
        /** The SEO description: its own, else a summary or intro, else the site's. */
        'metaDescription' => function (): string {
            foreach (['description', 'summary', 'tagline', 'intro'] as $field) {
                if ($this->content()->get($field)->isNotEmpty()) {
                    return $this->content()->get($field)->excerpt(200)->value();
                }
            }

            return $this->site()->description()->value() ?? '';
        },
    ],

    'routes' => [
        [
            'pattern' => 'sitemap.xml',
            'action'  => function () {
                $home  = site()->homePage();
                $pages = site()->index()->not($home)->filterBy('intendedTemplate', '!=', 'error')->prepend($home->id(), $home);

                $xml  = '<?xml version="1.0" encoding="utf-8"?>' . "\n";
                $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

                foreach ($pages as $page) {
                    $xml .= '  <url><loc>' . Xml::encode($page->url()) . '</loc>';
                    $xml .= '<lastmod>' . $page->modified('c', 'date') . '</lastmod></url>' . "\n";
                }

                $xml .= '</urlset>' . "\n";

                return new Response($xml, 'application/xml');
            },
        ],
        [
            'pattern' => 'sitemap',
            'action'  => fn () => Response::redirect(url('sitemap.xml'), 301),
        ],
        [
            'pattern' => 'robots.txt',
            'action'  => function () {
                $txt  = "User-agent: *\n";
                $txt .= "Disallow: /panel\n";
                $txt .= "\n";
                $txt .= 'Sitemap: ' . url('sitemap.xml') . "\n";

                return new Response($txt, 'text/plain');
            },
        ],
        [
            // Short links to apps: stackwerk.co/winston → stackwerk.co/apps/winston
            'pattern' => '(:any)',
            'action'  => function (string $slug) {
                if (page($slug) === null && ($app = page('apps/' . $slug)) !== null) {
                    return Response::redirect($app->url(), 302);
                }

                $this->next();
            },
        ],
    ],
]);
