<?php

use Kirby\Cms\Page;

return function (Page $page) {
    $available = $page->availability()->value() !== 'soon';
    $release   = $available ? $page->latestRelease() : null;
    $downloads = $available ? $page->downloadLinks() : [];

    $installers = array_values(array_filter($downloads, fn ($d) => $d['kind'] !== 'portable'));
    $portables  = array_values(array_filter($downloads, fn ($d) => $d['kind'] === 'portable'));

    $primary = null;
    foreach ($downloads as $download) {
        if ($download['primary'] === true) {
            $primary = $download;
            break;
        }
    }
    $primary ??= $installers[0] ?? null;

    $repo = trim((string)$page->github()->value());
    $hero = $page->hero()->toFile();
    $heroDark = $page->heroDark()->toFile();

    return [
        'icon'        => $page->appIcon(),
        'hero'        => $hero,
        'heroDark'    => $heroDark,
        // the hero screenshots are shown at the top, so the gallery leaves them out
        'screenshots' => $page->images()->template('screenshot')->not(...array_filter([$hero, $heroDark]))->sortBy('sort', 'asc', 'filename', 'asc'),
        'release'     => $release,
        'installers'  => $installers,
        'portables'   => $portables,
        'primary'     => $primary,
        'source'      => StackWerk\Releases::isRepo($repo) ? 'https://github.com/' . $repo : null,
        'policy'      => $page->children()->filterBy('intendedTemplate', 'legal')->first(),
    ];
};
