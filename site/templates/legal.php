<?php
/**
 * A policy page, e.g. a privacy policy. Under an app it is that app's policy.
 *
 * @var Kirby\Cms\Site $site
 * @var Kirby\Cms\Page $page
 */
$app = $page->parent()?->intendedTemplate()->name() === 'app' ? $page->parent() : null;
?>
<?php snippet('layout', slots: true) ?>

<article<?= $app ? ' class="app-theme" style="' . $app->accentStyle() . '"' : '' ?>>
  <header class="hero hero--page">
    <div class="container container--text">
      <?php if ($app): ?>
      <p class="eyebrow eyebrow--app"><a href="<?= $app->url() ?>"><?= $app->title()->html() ?></a></p>
      <?php endif ?>
      <h1 class="display display--page"><?= $page->title()->html() ?></h1>
      <?php if ($page->updated()->isNotEmpty()): ?>
      <p class="page-meta">Last updated <time datetime="<?= $page->updated()->toDate('Y-m-d') ?>"><?= $page->updated()->toDate('F j, Y') ?></time></p>
      <?php endif ?>
      <?php if ($page->intro()->isNotEmpty()): ?>
      <p class="lede"><?= $page->intro()->html() ?></p>
      <?php endif ?>
    </div>
  </header>
  <div class="section section--tight">
    <div class="container container--text prose">
      <?= $page->text()->kt() ?>
      <?php if ($site->email()->isNotEmpty()): ?>
      <h2>Questions</h2>
      <p>Email <?= Html::email($site->email()->value()) ?>.</p>
      <?php endif ?>
    </div>
  </div>
</article>

<?php endsnippet() ?>
