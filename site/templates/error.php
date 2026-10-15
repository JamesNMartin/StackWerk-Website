<?php
/**
 * @var Kirby\Cms\Site $site
 * @var Kirby\Cms\Page $page
 */
?>
<?php snippet('layout', slots: true) ?>

<section class="hero hero--page hero--error">
  <div class="container container--text">
    <p class="eyebrow">Error 404</p>
    <h1 class="display display--page"><?= $page->headline()->or($page->title())->html() ?></h1>
    <?php if ($page->text()->isNotEmpty()): ?>
    <div class="lede prose"><?= $page->text()->kt() ?></div>
    <?php endif ?>
    <div class="actions">
      <a class="button button--primary" href="<?= $site->url() ?>">Go to the home page</a>
      <?php if ($apps = page('apps')): ?>
      <a class="button button--ghost" href="<?= $apps->url() ?>">See the apps</a>
      <?php endif ?>
    </div>
  </div>
</section>

<?php endsnippet() ?>
