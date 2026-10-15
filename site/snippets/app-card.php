<?php
/**
 * A card linking to an app's page.
 *
 * @var Kirby\Cms\Page $app
 */
$icon = $app->appIcon();
$meta = array_filter([$app->platform()->value(), $app->price()->value()]);
?>
<article class="app-card app-theme" style="<?= $app->accentStyle() ?>">
  <?php if ($icon): ?>
  <img class="app-icon" src="<?= $icon->url() ?>" width="<?= $icon->width() ?>" height="<?= $icon->height() ?>" alt="" loading="lazy">
  <?php endif ?>
  <div class="app-card__body">
    <h3 class="app-card__title">
      <a href="<?= $app->url() ?>"><?= $app->title()->html() ?></a>
      <?php if ($app->isComingSoon()): ?><span class="badge">Coming soon</span>
      <?php elseif ($app->availability()->value() === 'beta'): ?><span class="badge">Beta</span><?php endif ?>
    </h3>
    <p class="app-card__tagline"><?= $app->tagline()->html() ?></p>
    <?php if ($meta): ?>
    <p class="app-card__meta"><?= esc(implode(' · ', $meta)) ?></p>
    <?php endif ?>
  </div>
  <span class="app-card__go" aria-hidden="true"><?php snippet('icon', ['name' => 'arrow-right']) ?></span>
</article>
