<?php
/**
 * Inlines a line icon from assets/icons (Lucide).
 *
 * @var string      $name  icon file name without .svg
 * @var string|null $class extra CSS class
 */
$file = kirby()->root('assets') . '/icons/' . basename($name ?? '') . '.svg';

if (empty($name) === false && is_file($file) === true) {
    $classes = trim('icon ' . ($class ?? ''));
    $svg     = preg_replace('/\s+class="[^"]*"/', '', file_get_contents($file), 1);

    echo preg_replace('/<svg\b/', '<svg class="' . esc($classes) . '" aria-hidden="true" focusable="false"', $svg, 1);
}
