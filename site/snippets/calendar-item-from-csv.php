<?php
/** @var Kirby\Cms\Page $child */
/** @var CalendarFromCsvPage $csvSource */

use Kirby\Toolkit\Str;

$showTags        = $showTags ?? true;
$showDescription = $showDescription ?? true;
$showExtra       = $showExtra ?? true;
$showNodo        = $showNodo ?? true;
$class           = $class ?? '';
$padding         = $padding ?? '15px';

$titolo = $csvSource->fieldByRole($child->content()->toArray(), 'title');
$nodo   = $csvSource->fieldByRole($child->content()->toArray(), 'subtitle');
$rawDate= $csvSource->fieldByRole($child->content()->toArray(), 'date');
$orario = $csvSource->fieldByRole($child->content()->toArray(), 'orario');
if (!$orario) $orario = $csvSource->formatTime($rawDate);

$tag1   = $csvSource->fieldByRole($child->content()->toArray(), 'tag1');
$tag2   = $csvSource->fieldByRole($child->content()->toArray(), 'tag2');
$desc   = $csvSource->fieldByRole($child->content()->toArray(), 'description');
$extra  = $csvSource->extraFields($child->content()->toArray());
$filterColors = $csvSource->filterColors();

$baseSlug = $child->content()->get('base_slug')->value();
$itemUrl  = $baseSlug ? $csvSource->url() . '/' . $baseSlug : $child->url();
?>

<div class="card-master calendar-item-card no_hover <?= esc($class) ?>">
  <div class="cards-details orange no_hover" style="padding: <?= esc($padding) ?>;">
    
    <div class="cards-title">
      <h2><?= esc($titolo) ?></h2>
    </div>

    <div class="cards-categories">
      <?php if ($orario): ?>
        <span class="tag">
          <?= esc($orario) ?>
        </span> 
      <?php endif; ?>
      <?php if ($showDescription && $desc): ?>
        → <?= esc(Str::excerpt($desc, 150)) ?>
      <?php endif; ?>

      <?php if ($showTags && ($tag1 || $tag2)): ?>
        <hr>
        <?php if ($tag1): ?>
          <?php foreach (Str::split($tag1, ',') as $t): ?>
            <span class="tag alt">
              <?= esc(trim($t)) ?>
            </span>
          <?php endforeach; ?>
        <?php endif; ?>

        <?php if ($tag2): ?>
          <?php foreach (Str::split($tag2, ',') as $t): ?>
            <span class="tag">
              <?= esc(trim($t)) ?>
            </span>
          <?php endforeach; ?>
        <?php endif; ?>
      <?php endif; ?>
    </div>

    <?php if ($showNodo && $nodo): ?>
      <hr>
      <?php $color = $filterColors['nodo'] ?? $filterColors['subtitle'] ?? '#000'; ?>
      <p class="nodo" style="color:<?= $color ?>;"><?= esc($nodo) ?></p>
    <?php endif; ?>

    <?php if ($showExtra && !empty($extra)): ?>
      <hr>
      <div class="extra-fields">
        <?php foreach ($extra as $key => $val): ?>
          <p><strong><?= esc(Str::excerpt($val, 50)) ?></strong></p>
          <hr>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</div>
