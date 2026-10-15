<?php
/**
 * @var Kirby\Cms\Page $page
 */
?>
<?php snippet('layout', slots: true) ?>

<article>
  <header class="hero hero--page">
    <div class="container container--text">
      <h1 class="display display--page"><?= $page->title()->html() ?></h1>
      <?php if ($page->intro()->isNotEmpty()): ?>
      <p class="lede"><?= $page->intro()->html() ?></p>
      <?php endif ?>
    </div>
  </header>
  <div class="section section--tight">
    <div class="container container--text prose">
      <?= $page->text()->kt() ?>
    </div>
  </div>
</article>

<?php endsnippet() ?>
