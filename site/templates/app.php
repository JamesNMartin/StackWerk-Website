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
 * @var array|null           $release
 * @var array                $installers
 * @var array                $portables
 * @var array|null           $primary
 * @var string|null          $source
 * @var Kirby\Cms\Page|null  $policy
 */
$status    = $page->availability()->value();
$hasFaq    = $page->faq()->isNotEmpty();
$published = $release['published'] ?? null;

$schema = array_filter([
    '@context'            => 'https://schema.org',
    '@type'               => 'SoftwareApplication',
    'name'                => $page->title()->value(),
    'description'         => $page->tagline()->value(),
    'url'                 => $page->url(),
    'image'               => $icon?->url(),
    'applicationCategory' => $page->category()->or('UtilitiesApplication')->value(),
    'operatingSystem'     => $page->platform()->value(),
    'softwareVersion'     => $release['version'] ?? null,
    'downloadUrl'         => $primary['url'] ?? null,
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
          <?php if ($status === 'beta'): ?><span class="badge">Beta</span><?php endif ?>
        </h1>
        <p class="app-hero__tagline"><?= $page->tagline()->html() ?></p>
        <?php if ($page->summary()->isNotEmpty()): ?>
        <p class="app-hero__summary"><?= $page->summary()->html() ?></p>
        <?php endif ?>

        <?php if ($status === 'soon'): ?>
        <p class="app-hero__meta"><span class="badge badge--large">Coming soon</span></p>
        <?php elseif ($primary): ?>
        <div class="actions">
          <a class="button button--app button--large" href="<?= esc($primary['url']) ?>">
            <?php snippet('icon', ['name' => 'download']) ?>
            <?= $page->downloadButton()->or('Download')->html() ?>
          </a>
          <?php if ($source): ?>
          <a class="button button--ghost" href="<?= $source ?>">
            <?php snippet('icon', ['name' => 'code-xml']) ?>
            Source on GitHub
          </a>
          <?php endif ?>
        </div>
        <p class="app-hero__meta">
          <?= esc(implode(' · ', array_filter([
              isset($release['version']) ? 'Version ' . $release['version'] : null,
              $page->price()->value(),
              $primary['label'] . ($primary['size'] ? ', ' . $primary['size'] : ''),
          ]))) ?>
          · <a href="#download">Other downloads</a>
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
        <?php if ($hasFaq): ?><li><a href="#faq">FAQ</a></li><?php endif ?>
        <?php if ($policy): ?><li><a href="<?= $policy->url() ?>">Privacy</a></li><?php endif ?>
      </ul>
      <?php if ($primary): ?>
      <a class="button button--app button--small" href="#download"><?php snippet('icon', ['name' => 'download']) ?> Download</a>
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

  <?php if ($status !== 'soon' && ($installers || $portables)): ?>
  <section class="section section--rule" id="download" aria-labelledby="download-title">
    <div class="container">
      <header class="section-header">
        <h2 class="section-title" id="download-title">Download <?= $page->title()->html() ?></h2>
        <p class="section-intro">
          <?php if ($release): ?>
          Version <?= esc($release['version']) ?><?php if ($published): ?>, released <time datetime="<?= date('Y-m-d', $published) ?>"><?= date('F j, Y', $published) ?></time><?php endif ?>.
          <a href="<?= esc($release['url']) ?>">Release notes</a>
          <?php else: ?>
          The latest version, from the releases page on GitHub.
          <?php endif ?>
        </p>
      </header>

      <?php if ($installers): ?>
      <ul class="downloads">
        <?php foreach ($installers as $download): ?>
        <li class="download<?= $download['primary'] ? ' download--primary' : '' ?>">
          <h3 class="download__label"><?= esc($download['label']) ?></h3>
          <?php if ($download['text'] !== ''): ?>
          <p class="download__text"><?= esc($download['text']) ?></p>
          <?php endif ?>
          <a class="button <?= $download['primary'] ? 'button--app' : 'button--ghost' ?>" href="<?= esc($download['url']) ?>">
            <?php snippet('icon', ['name' => 'download']) ?>
            Download<?= $download['size'] ? ' <span class="button__note">' . esc($download['size']) . '</span>' : '' ?>
          </a>
          <?php if ($download['file']): ?>
          <p class="download__file"><?= esc($download['file']) ?></p>
          <?php endif ?>
        </li>
        <?php endforeach ?>
      </ul>
      <?php endif ?>

      <?php if ($portables): ?>
      <p class="portables">
        <span>No installer?</span>
        <?php foreach ($portables as $download): ?>
        <a href="<?= esc($download['url']) ?>"><?= esc($download['label']) ?><?= $download['size'] ? ' <span class="portables__size">' . esc($download['size']) . '</span>' : '' ?></a>
        <?php endforeach ?>
      </p>
      <?php endif ?>

      <?php if ($page->downloadNote()->isNotEmpty()): ?>
      <p class="download-note"><?= $page->downloadNote()->html() ?></p>
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
    </div>
  </section>
  <?php endif ?>

</div>

<?php endslot() ?>
<?php endsnippet() ?>
