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

<div class="card-master no_hover <?= esc($class) ?>" style="text-decoration: none; color: inherit; display: block; height: 100%;">
  <div class="cards-details orange no_hover" style="padding: <?= esc($padding) ?>; height: 100%; display: flex; flex-direction: column;">
    
    <div class="cards-title">
      <h2 style="font-size: 1.5rem; margin: 0; margin-bottom: 5px; font-weight: 700;"><?= esc($titolo) ?></h2>
    </div>

    <div class="cards-categories" style="margin-bottom: 5px; display: flex; flex-wrap: wrap; gap: 5px; flex-direction: row;">
      <?php if ($orario): ?>
        <span class="tag" style="margin:0;">
          <?= esc($orario) ?>
        </span> 
      <?php endif; ?>
      <?php if ($showDescription && $desc): ?>
        → <?= esc(Str::excerpt($desc, 150)) ?>
      <?php endif; ?>

      <?php if ($showTags && ($tag1 || $tag2)): ?>
        <hr style="border: none; border-top: 1px solid whitesmoke!important; margin: 5px 0; width: 100%;">
        <?php if ($tag1): ?>
          <?php foreach (Str::split($tag1, ',') as $t): ?>
            <span class="tag alt" style="margin:0;">
              <?= esc(trim($t)) ?>
            </span>
          <?php endforeach; ?>
        <?php endif; ?>

        <?php if ($tag2): ?>
          <?php foreach (Str::split($tag2, ',') as $t): ?>
            <span class="tag" style="margin:0;">
              <?= esc(trim($t)) ?>
            </span>
          <?php endforeach; ?>
        <?php endif; ?>
      <?php endif; ?>
    </div>

    <?php if ($showNodo && $nodo): ?>
      <hr style="border: none; border-top: 1px solid whitesmoke!important; margin: 5px 0;">
      <?php $color = $filterColors['nodo'] ?? $filterColors['subtitle'] ?? '#000'; ?>
      <p class="nodo" style="color:<?= $color ?>; font-weight: bold; margin-bottom: 0; font-size: 1rem;"><?= esc($nodo) ?></p>
    <?php endif; ?>

    <?php if ($showExtra && !empty($extra)): ?>
      <hr style="border: none; border-top: 1px solid whitesmoke!important; margin: 5px 0;">
      <div class="extra-fields" style="font-size: 0.8rem; opacity: 1;">
        <?php foreach ($extra as $key => $val): ?>
          <p style="margin: 0; font-size: 1rem;"><strong><?= esc(Str::excerpt($val, 50)) ?></strong></p>
          <hr style="border: none; border-top: 1px solid whitesmoke!important; margin: 5px 0;">
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</div>
