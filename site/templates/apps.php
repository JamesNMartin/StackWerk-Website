<?php
/**
 * @var Kirby\Cms\Page $page
 */
?>
<?php snippet('layout', slots: true) ?>

<section class="hero hero--page">
  <div class="container">
    <h1 class="display display--page"><?= $page->headline()->or($page->title())->html() ?></h1>
    <?php if ($page->intro()->isNotEmpty()): ?>
    <p class="lede"><?= $page->intro()->html() ?></p>
    <?php endif ?>
  </div>
</section>

<section class="section section--tight">
  <div class="container">
    <div class="app-grid app-grid--wide">
      <?php foreach ($page->children()->listed() as $app): ?>
      <?php snippet('app-card', ['app' => $app]) ?>
      <?php endforeach ?>
    </div>
  </div>
</section>

<?php endsnippet() ?>
