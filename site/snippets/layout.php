<?php
/**
 * The page shell: <head>, header, footer. Templates fill the default slot,
 * and can add to <head> with the `head` slot.
 *
 * @var Kirby\Cms\App  $kirby
 * @var Kirby\Cms\Site $site
 * @var Kirby\Cms\Page $page
 */
$title = $page->isHomePage()
    ? $site->title()->value() . ' · ' . rtrim($page->headline()->or($page->title())->value(), '.')
    : $page->title()->value() . ($page->eyebrow()->isNotEmpty() ? ' · ' . $page->eyebrow()->value() : '') . ' · ' . $site->title()->value();

$description = $page->metaDescription();

$ogImage = $page->image('og.png')?->url() ?? url('assets/images/og.png');
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= esc($title) ?></title>
  <?php if ($description !== ''): ?>
  <meta name="description" content="<?= esc($description) ?>">
  <?php endif ?>
  <link rel="canonical" href="<?= $page->url() ?>">

  <meta property="og:site_name" content="<?= esc($site->title()) ?>">
  <meta property="og:type" content="website">
  <meta property="og:title" content="<?= esc($page->isHomePage() ? $site->title() : $page->title()) ?>">
  <?php if ($description !== ''): ?>
  <meta property="og:description" content="<?= esc($description) ?>">
  <?php endif ?>
  <meta property="og:url" content="<?= $page->url() ?>">
  <meta property="og:image" content="<?= $ogImage ?>">
  <meta name="twitter:card" content="summary_large_image">

  <meta name="color-scheme" content="light dark">
  <meta name="theme-color" content="#f6f4ef" media="(prefers-color-scheme: light)">
  <meta name="theme-color" content="#141311" media="(prefers-color-scheme: dark)">
  <link rel="icon" href="<?= url('assets/images/favicon.svg') ?>" type="image/svg+xml">
  <link rel="icon" href="<?= url('assets/images/favicon-32.png') ?>" type="image/png" sizes="32x32">
  <link rel="apple-touch-icon" href="<?= url('assets/images/apple-touch-icon.png') ?>">
  <link rel="sitemap" type="application/xml" href="<?= url('sitemap.xml') ?>">

  <link rel="preload" href="<?= url('assets/fonts/space-grotesk-latin-wght-normal.woff2') ?>" as="font" type="font/woff2" crossorigin>
  <link rel="preload" href="<?= url('assets/fonts/inter-latin-wght-normal.woff2') ?>" as="font" type="font/woff2" crossorigin>
  <?= css('assets/css/site.css?v=' . filemtime($kirby->root('assets') . '/css/site.css')) ?>
  <?= $slots->head() ?>
</head>
<body class="template-<?= $page->intendedTemplate() ?>">
  <a class="skip-link" href="#main">Skip to content</a>

  <header class="site-header">
    <div class="container site-header__inner">
      <a class="brand" href="<?= $site->url() ?>" aria-label="<?= esc($site->title()) ?> home">
        <?php snippet('logo') ?>
        <span class="brand__name">Stack<b>Werk</b></span>
      </a>
      <nav class="site-nav" aria-label="Main">
        <ul>
          <?php foreach ($site->children()->listed() as $item): ?>
          <li>
            <a href="<?= $item->url() ?>"<?= $item->isOpen() ? ' aria-current="' . ($item->isActive() ? 'page' : 'true') . '"' : '' ?>><?= $item->title()->html() ?></a>
          </li>
          <?php endforeach ?>
        </ul>
      </nav>
    </div>
  </header>

  <main id="main">
    <?= $slot ?>
  </main>

  <footer class="site-footer">
    <div class="container site-footer__inner">
      <div class="site-footer__brand">
        <a class="brand brand--small" href="<?= $site->url() ?>" aria-label="<?= esc($site->title()) ?> home">
          <?php snippet('logo') ?>
          <span class="brand__name">Stack<b>Werk</b></span>
        </a>
        <?php if ($site->description()->isNotEmpty()): ?>
        <p><?= $site->description()->html() ?></p>
        <?php endif ?>
      </div>

      <nav class="site-footer__nav" aria-label="Apps">
        <h2>Apps</h2>
        <ul>
          <?php foreach (page('apps')?->children()->listed() ?? [] as $app): ?>
          <li><a href="<?= $app->url() ?>"><?= $app->title()->html() ?></a></li>
          <?php endforeach ?>
        </ul>
      </nav>

      <nav class="site-footer__nav" aria-label="StackWerk">
        <h2><?= $site->title()->html() ?></h2>
        <ul>
          <?php foreach ($site->children()->listed()->not('apps') as $item): ?>
          <li><a href="<?= $item->url() ?>"><?= $item->title()->html() ?></a></li>
          <?php endforeach ?>
          <?php if ($privacy = page('privacy')): ?>
          <li><a href="<?= $privacy->url() ?>"><?= $privacy->title()->html() ?></a></li>
          <?php endif ?>
          <?php if ($site->email()->isNotEmpty()): ?>
          <li><?= Html::email($site->email()->value(), 'Contact') ?></li>
          <?php endif ?>
        </ul>
      </nav>

      <?php if ($site->links()->isNotEmpty()): ?>
      <nav class="site-footer__nav" aria-label="Elsewhere">
        <h2>Elsewhere</h2>
        <ul>
          <?php foreach ($site->links()->toStructure() as $link): ?>
          <li><a href="<?= esc($link->url()) ?>" rel="<?= str_contains(strtolower($link->label()), 'mastodon') ? 'me noopener' : 'noopener' ?>"><?= $link->label()->html() ?></a></li>
          <?php endforeach ?>
        </ul>
      </nav>
      <?php endif ?>
    </div>
    <div class="container">
      <div class="site-footer__legal">
        <p>© <?= date('Y') ?> <?= $site->title()->html() ?><?= $site->owner()->isNotEmpty() ? ' · ' . $site->owner()->html() : '' ?></p>
        <p>Windows is a trademark of the Microsoft group of companies. Mastodon is a trademark of Mastodon gGmbH.</p>
      </div>
    </div>
  </footer>
</body>
</html>
