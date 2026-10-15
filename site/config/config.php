<?php

/**
 * Settings for every environment.
 * Host-specific files override these: config.localhost.php when you run
 * `composer start`, config.stackwerk.co.php on the live site.
 *
 * @see https://getkirby.com/docs/reference/system/options
 */
return [
    'debug' => false,

    // The Panel (/panel) can only be installed from localhost: create the
    // first account locally, then upload site/accounts (see README.md).
    'panel' => [
        'install' => false,
    ],

    // Kirby's built-in page cache is left off: app pages show the latest
    // release from GitHub, which is cached on its own by the stackwerk plugin.

    // How long (minutes) release details fetched from GitHub are reused.
    'stackwerk.website.releases.ttl' => 60,
];
