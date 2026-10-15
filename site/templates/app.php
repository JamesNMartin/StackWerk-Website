<?php
/**
 * An app's product page.
 *
 * @var Kirby\Cms\Site       $site
 * @var Kirby\Cms\Page       $page
 * @var Kirby\Cms\File|null  $icon
 * @var Kirby\Cms\File|null  $hero
 * @var Kirby\Cms\File|null  $heroDark
 * @var Kirby\Cms\Files      $screenshots
 * @var bool                 $comingSoon
 * @var string|null          $storeUrl
 * @var Kirby\Cms\Page|null  $policy
 */
$hasFaq = $page->faq()->isNotEmpty();
$hasGet = $page->install()->isNotEmpty() || $page->requirements()->isNotEmpty() || $comingSoon === false;
$meta   = array_filter([$page->price()->value(), $page->platform()->value()]);

$schema = array_filter([
    '@context'            => 'https://schema.org',
    '@type'               => 'SoftwareApplication',
    'name'                => $page->title()->value(),
    'description'         => $page->tagline()->value(),
    'url'                 => $page->url(),
    'image'               => $icon?->url(),
    'applicationCategory' => $page->category()->or('UtilitiesApplication')->value(),
    'operatingSystem'     => $page->platform()->value(),
    'downloadUrl'         => $comingSoon ? null : $page->storeUrl(false),
    'screenshot'          => array_map(fn ($file) => $file->url(), array_values(array_filter([$hero, $heroDark, ...$screenshots->values()]))) ?: null,
    'offers'              => strtolower($page->price()->value() ?? '') === 'free'
        ? ['@type' => 'Offer', 'price' => '0', 'priceCurrency' => 'USD']
        : null,
    'publisher'           => ['@type' => 'Organization', 'name' => $site->title()->value(), 'url' => $site->url()],
]);
?>
<?php snippet('layout', slots: true) ?>
<?php slot('head') ?>
  <script type="application/ld+json"><?= json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP) ?></script>
<?php endslot() ?>
<?php slot() ?>

<div class="app-page app-theme" style="<?= $page->accentStyle() ?>">

  <section class="app-hero">
    <div class="container app-hero__inner">
      <div class="app-hero__text">
        <?php if ($icon): ?>
        <img class="app-icon app-icon--hero" src="<?= $icon->url() ?>" width="<?= $icon->width() ?>" height="<?= $icon->height() ?>" alt="<?= $icon->alt()->or($page->title() . ' icon')->esc() ?>">
        <?php endif ?>
        <?php if ($page->eyebrow()->isNotEmpty()): ?>
        <p class="eyebrow eyebrow--app"><?= $page->eyebrow()->html() ?></p>
        <?php endif ?>
        <h1 class="display app-hero__title">
          <?= $page->title()->html() ?>
          <?php if ($page->availability()->value() === 'beta'): ?><span class="badge">Beta</span><?php endif ?>
        </h1>
        <p class="app-hero__tagline"><?= $page->tagline()->html() ?></p>
        <?php if ($page->summary()->isNotEmpty()): ?>
        <p class="app-hero__summary"><?= $page->summary()->html() ?></p>
        <?php endif ?>

        <div class="actions">
          <?php if ($comingSoon): ?>
          <p class="coming-soon"><?php snippet('icon', ['name' => 'sparkles']) ?> Coming soon to the Microsoft Store</p>
          <?php else: ?>
          <?php snippet('store-badge', ['url' => $storeUrl]) ?>
          <?php endif ?>
        </div>
        <?php if ($meta || $hasGet): ?>
        <p class="app-hero__meta">
          <?= esc(implode(' · ', $meta)) ?>
          <?php if ($hasGet): ?><?= $meta ? ' · ' : '' ?><a href="#get">Requirements</a><?php endif ?>
        </p>
        <?php endif ?>
      </div>

      <?php if ($hero): ?>
      <div class="app-hero__shot">
        <?php snippet('shot', ['file' => $hero, 'dark' => $heroDark, 'eager' => true, 'caption' => false]) ?>
      </div>
      <?php endif ?>
    </div>
  </section>

  <nav class="app-nav" aria-label="<?= $page->title()->esc() ?>">
    <div class="container app-nav__inner">
      <a class="app-nav__name" href="#main">
        <?php if ($icon): ?><img src="<?= $icon->url() ?>" width="24" height="24" alt=""><?php endif ?>
        <?= $page->title()->html() ?>
      </a>
      <ul class="app-nav__links">
        <?php if ($page->features()->isNotEmpty()): ?><li><a href="#features">Features</a></li><?php endif ?>
        <?php if ($screenshots->isNotEmpty()): ?><li><a href="#screenshots">Screenshots</a></li><?php endif ?>
        <?php if ($hasGet): ?><li><a href="#get">Get <?= $page->title()->html() ?></a></li><?php endif ?>
        <?php if ($hasFaq): ?><li><a href="#faq">FAQ</a></li><?php endif ?>
        <?php if ($policy): ?><li><a href="<?= $policy->url() ?>">Privacy</a></li><?php endif ?>
      </ul>
      <?php if ($comingSoon === false): ?>
      <a class="button button--app button--small" href="<?= esc($storeUrl) ?>"><?php snippet('icon', ['name' => 'download']) ?> Get <?= $page->title()->html() ?></a>
      <?php endif ?>
    </div>
  </nav>

  <?php if ($page->features()->isNotEmpty()): ?>
  <section class="section" id="features" aria-labelledby="features-title">
    <div class="container">
      <header class="section-header">
        <h2 class="section-title" id="features-title"><?= $page->featuresHeadline()->or('Features')->html() ?></h2>
        <?php if ($page->featuresIntro()->isNotEmpty()): ?>
        <p class="section-intro"><?= $page->featuresIntro()->html() ?></p>
        <?php endif ?>
      </header>
      <ul class="features">
        <?php foreach ($page->features()->toStructure() as $feature): ?>
        <li class="feature">
          <span class="feature__icon"><?php snippet('icon', ['name' => $feature->icon()->value()]) ?></span>
          <h3><?= $feature->title()->html() ?></h3>
          <p><?= $feature->text()->html() ?></p>
        </li>
        <?php endforeach ?>
      </ul>
    </div>
  </section>
  <?php endif ?>

  <?php if ($screenshots->isNotEmpty()): ?>
  <section class="section section--tinted" id="screenshots" aria-labelledby="screenshots-title">
    <div class="container">
      <header class="section-header">
        <h2 class="section-title" id="screenshots-title"><?= $page->galleryHeadline()->or('Screenshots')->html() ?></h2>
        <?php if ($page->galleryIntro()->isNotEmpty()): ?>
        <p class="section-intro"><?= $page->galleryIntro()->html() ?></p>
        <?php endif ?>
      </header>
      <div class="gallery">
        <?php foreach ($screenshots as $file): ?>
        <?php snippet('shot', ['file' => $file]) ?>
        <?php endforeach ?>
      </div>
    </div>
  </section>
  <?php endif ?>

  <?php if ($page->nativeItems()->isNotEmpty() || $page->privacyText()->isNotEmpty()): ?>
  <section class="section" aria-label="Platform and privacy">
    <div class="container split">
      <?php if ($page->nativeItems()->isNotEmpty()): ?>
      <div class="panel">
        <h2 class="panel__title"><?= $page->nativeHeadline()->or('Made for the platform')->html() ?></h2>
        <?php if ($page->nativeText()->isNotEmpty()): ?>
        <p><?= $page->nativeText()->html() ?></p>
        <?php endif ?>
        <ul class="checklist">
          <?php foreach ($page->nativeItems()->split() as $item): ?>
          <li><?php snippet('icon', ['name' => 'check']) ?><?= esc($item) ?></li>
          <?php endforeach ?>
        </ul>
      </div>
      <?php endif ?>
      <?php if ($page->privacyText()->isNotEmpty()): ?>
      <div class="panel panel--app">
        <span class="panel__icon"><?php snippet('icon', ['name' => 'lock-keyhole']) ?></span>
        <h2 class="panel__title"><?= $page->privacyHeadline()->or('Privacy')->html() ?></h2>
        <div class="prose"><?= $page->privacyText()->kt() ?></div>
        <?php if ($policy): ?>
        <p><a class="link-arrow" href="<?= $policy->url() ?>">Read the <?= $policy->title()->lower()->html() ?> <?php snippet('icon', ['name' => 'arrow-right']) ?></a></p>
        <?php endif ?>
      </div>
      <?php endif ?>
    </div>
  </section>
  <?php endif ?>

  <?php if ($hasGet): ?>
  <section class="section section--rule" id="get" aria-labelledby="get-title">
    <div class="container">
      <header class="section-header">
        <h2 class="section-title" id="get-title">Get <?= $page->title()->html() ?></h2>
        <?php if ($comingSoon): ?>
        <p class="section-intro"><?= $page->title()->html() ?> is coming soon to the Microsoft Store. <?= $page->storeNote()->html() ?></p>
        <?php endif ?>
      </header>

      <?php if ($comingSoon === false): ?>
      <div class="store">
        <?php snippet('store-badge', ['url' => $storeUrl]) ?>
        <?php if ($page->storeNote()->isNotEmpty()): ?>
        <p class="store__note"><?= $page->storeNote()->html() ?></p>
        <?php endif ?>
      </div>
      <?php endif ?>

      <div class="split split--install">
        <?php if ($page->install()->isNotEmpty()): ?>
        <div>
          <h3 class="subsection-title">Installing</h3>
          <ol class="steps">
            <?php foreach ($page->install()->toStructure() as $step): ?>
            <li>
              <strong><?= $step->title()->html() ?></strong>
              <?php if ($step->text()->isNotEmpty()): ?>
              <span><?= $step->text()->kti() ?></span>
              <?php endif ?>
            </li>
            <?php endforeach ?>
          </ol>
        </div>
        <?php endif ?>
        <?php if ($page->requirements()->isNotEmpty()): ?>
        <div>
          <h3 class="subsection-title">Requirements</h3>
          <dl class="specs">
            <?php foreach ($page->requirements()->toStructure() as $requirement): ?>
            <div>
              <dt><?= $requirement->label()->html() ?></dt>
              <dd><?= $requirement->value()->html() ?></dd>
            </div>
            <?php endforeach ?>
          </dl>
        </div>
        <?php endif ?>
      </div>
    </div>
  </section>
  <?php endif ?>

  <?php if ($hasFaq): ?>
  <section class="section section--rule" id="faq" aria-labelledby="faq-title">
    <div class="container">
      <h2 class="section-title" id="faq-title">Questions</h2>
      <div class="faq">
        <?php foreach ($page->faq()->toStructure() as $item): ?>
        <details>
          <summary><?= $item->question()->html() ?></summary>
          <div class="prose"><?= $item->answer()->kt() ?></div>
        </details>
        <?php endforeach ?>
      </div>
      <?php if ($site->email()->isNotEmpty()): ?>
      <p class="faq-contact">Something else? Email <?= Html::email($site->email()->value()) ?>.</p>
      <?php endif ?>
    </div>
  </section>
  <?php endif ?>

</div>

<?php endslot() ?>
<?php endsnippet() ?>
