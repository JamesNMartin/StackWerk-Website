<?php
/**
 * A screenshot in a window frame, linking to the full-size image.
 *
 * @var Kirby\Cms\File      $file
 * @var Kirby\Cms\File|null $dark    the same view in the dark theme, for dark-mode visitors
 * @var bool                 $eager   load straight away (above the fold)
 * @var bool                 $caption show the caption (default true)
 */
$dark    ??= null;
$eager   ??= false;
$caption ??= true;
?>
<figure class="shot">
  <a class="window" href="<?= $file->url() ?>">
    <picture>
      <?php if ($dark): ?>
      <source srcset="<?= $dark->url() ?>" media="(prefers-color-scheme: dark)" width="<?= $dark->width() ?>" height="<?= $dark->height() ?>">
      <?php endif ?>
      <img src="<?= $file->url() ?>" width="<?= $file->width() ?>" height="<?= $file->height() ?>" alt="<?= $file->alt()->esc() ?>"<?= $eager ? ' fetchpriority="high"' : ' loading="lazy"' ?> decoding="async">
    </picture>
  </a>
  <?php if ($caption && $file->caption()->isNotEmpty()): ?>
  <figcaption><?= $file->caption()->html() ?></figcaption>
  <?php endif ?>
</figure>
