<?php
/**
 * @var Kirby\Cms\Site $site
 * @var Kirby\Cms\Page $page
 */
$apps      = page('apps')?->children()->listed() ?? new Kirby\Cms\Pages();
$spotlight = $page->spotlight()->toPage() ?? $apps->first();
$others    = $spotlight ? $apps->not($spotlight) : $apps;
?>
<?php snippet('layout', slots: true) ?>

<section class="hero hero--home">
  <div class="container">
    <?php if ($page->eyebrow()->isNotEmpty()): ?>
    <p class="eyebrow"><?= $page->eyebrow()->html() ?></p>
    <?php endif ?>
    <h1 class="display"><?= $page->headline()->or($page->title())->html() ?></h1>
    <?php if ($page->intro()->isNotEmpty()): ?>
    <p class="lede"><?= $page->intro()->html() ?></p>
    <?php endif ?>
    <div class="actions">
      <a class="button button--primary" href="<?= page('apps')?->url() ?? '#apps' ?>">See the apps <?php snippet('icon', ['name' => 'arrow-right']) ?></a>
      <?php if ($about = page('about')): ?>
      <a class="button button--ghost" href="<?= $about->url() ?>"><?= $about->title()->html() ?></a>
      <?php endif ?>
    </div>
  </div>
</section>

<?php if ($apps->isNotEmpty()): ?>
<section class="section" id="apps" aria-labelledby="apps-title">
  <div class="container">
    <header class="section-header">
      <h2 class="section-title" id="apps-title"><?= $page->appsHeadline()->or('Apps')->html() ?></h2>
    </header>

    <?php if ($spotlight): ?>
    <?php $icon = $spotlight->appIcon(); $hero = $spotlight->hero()->toFile(); ?>
    <?php $heroDark = $spotlight->heroDark()->toFile(); ?>
    <article class="spotlight app-theme" style="<?= $spotlight->accentStyle() ?>">
      <div class="spotlight__text">
        <?php if ($icon): ?>
        <img class="app-icon app-icon--large" src="<?= $icon->url() ?>" width="<?= $icon->width() ?>" height="<?= $icon->height() ?>" alt="">
        <?php endif ?>
        <?php if ($spotlight->eyebrow()->isNotEmpty()): ?>
        <p class="eyebrow eyebrow--app"><?= $spotlight->eyebrow()->html() ?></p>
        <?php endif ?>
        <h3 class="spotlight__title"><?= $spotlight->title()->html() ?></h3>
        <p class="spotlight__tagline"><?= $spotlight->tagline()->html() ?></p>
        <?php if ($spotlight->summary()->isNotEmpty()): ?>
        <p class="spotlight__summary"><?= $spotlight->summary()->html() ?></p>
        <?php endif ?>
        <div class="actions">
          <a class="button button--app" href="<?= $spotlight->url() ?>">Meet <?= $spotlight->title()->html() ?> <?php snippet('icon', ['name' => 'arrow-right']) ?></a>
          <?php if ($spotlight->availability()->value() !== 'soon' && $spotlight->downloads()->isNotEmpty()): ?>
          <a class="button button--ghost" href="<?= $spotlight->url() ?>#download"><?php snippet('icon', ['name' => 'download']) ?> Download</a>
          <?php endif ?>
        </div>
      </div>
      <?php if ($hero): ?>
      <div class="spotlight__shot">
        <?php snippet('shot', ['file' => $hero, 'dark' => $heroDark, 'caption' => false]) ?>
      </div>
      <?php endif ?>
    </article>
    <?php endif ?>

    <?php if ($others->isNotEmpty()): ?>
    <div class="app-grid">
      <?php foreach ($others as $app): ?>
      <?php snippet('app-card', ['app' => $app]) ?>
      <?php endforeach ?>
    </div>
    <?php endif ?>
  </div>
</section>
<?php endif ?>

<?php if ($page->principles()->isNotEmpty()): ?>
<section class="section section--rule" aria-labelledby="principles-title">
  <div class="container">
    <header class="section-header">
      <h2 class="section-title" id="principles-title"><?= $page->principlesHeadline()->or('How we build')->html() ?></h2>
    </header>
    <ul class="principles">
      <?php foreach ($page->principles()->toStructure() as $principle): ?>
      <li class="principle">
        <?php snippet('icon', ['name' => $principle->icon()->value(), 'class' => 'principle__icon']) ?>
        <h3><?= $principle->title()->html() ?></h3>
        <p><?= $principle->text()->html() ?></p>
      </li>
      <?php endforeach ?>
    </ul>
  </div>
</section>
<?php endif ?>

<?php endsnippet() ?>
