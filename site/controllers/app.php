<?php

use Kirby\Cms\Page;

return function (Page $page) {
    $hero     = $page->hero()->toFile();
    $heroDark = $page->heroDark()->toFile();

    return [
        'icon'        => $page->appIcon(),
        'hero'        => $hero,
        'heroDark'    => $heroDark,
        // the hero screenshots are shown at the top, so the gallery leaves them out
        'screenshots' => $page->images()->template('screenshot')->not(...array_filter([$hero, $heroDark]))->sortBy('sort', 'asc', 'filename', 'asc'),
        'comingSoon'  => $page->isComingSoon(),
        'storeUrl'    => $page->storeUrl(),
        'policy'      => $page->children()->filterBy('intendedTemplate', 'legal')->first(),
    ];
};
