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
];
