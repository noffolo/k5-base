<img
  class="img-base <?= ($contain ?? false) ? 'contain' : 'cover' ?> <?= ($isFirst ?? false) ? '' : 'lazyload' ?>"
  <?= ($isFirst ?? false) ? 'fetchpriority="high" loading="eager"' : 'loading="lazy"' ?>
  <?= ($isFirst ?? false) ? 'src' : 'data-src' ?>="<?= $image->thumb([
      'width'   => 1280,
      'format'  => 'webp',
      'quality' => 75
    ])->url() ?>"
  data-sizes="auto"
  <?= ($isFirst ?? false) ? 'srcset' : 'data-srcset' ?>="<?= $image->srcset(
      [320, 640, 960, 1280, 1600, 1920],
      ['format' => 'webp', 'quality' => 75]
    ) ?>"
  width="<?= $image->width() ?>"
  height="<?= $image->height() ?>"
  alt="<?= html($image->alt() ?? $image->filename()) ?>"
  style="
    <?= !empty($min_height) ? 'min-height: ' . $min_height . ';' : '' ?>
    <?= !empty($min_width)  ? 'min-width: '  . $min_width  . ';' : '' ?>
    aspect-ratio: <?= $ratio ?? 'auto' ?>;
  "
>
