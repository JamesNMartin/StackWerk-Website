<?php
/**
 * Microsoft's "Download from the Microsoft Store" badge, linking to an app's listing.
 * Dark badge on light pages, light badge on dark ones, as Microsoft's badge guidelines ask.
 *
 * @var string $url  the listing, from $page->storeUrl()
 */
?>
<a class="store-badge" href="<?= esc($url) ?>">
  <picture>
    <source srcset="<?= url('assets/images/microsoft-store-badge-light.svg') ?>" media="(prefers-color-scheme: dark)">
    <img src="<?= url('assets/images/microsoft-store-badge-dark.svg') ?>" width="161" height="44" alt="Download from the Microsoft Store">
  </picture>
</a>
