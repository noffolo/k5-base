<?php
/** @var array $groupedItems */
/** @var string $title */
/** @var CalendarFromCsvPage $csvSource */
?>
<div class="cm-carousel" role="region" aria-label="<?= esc($title ?: 'Calendario') ?>">
  <div class="cm-carousel__track" tabindex="0">
    <?php foreach ($groupedItems as $dateHeader => $group): ?>
      <div class="date-group cm-card--slide" style="text-align: center; min-width: 400px; scroll-snap-align: start;">
        <h3 class="date-header" style="font-weight: 700; font-size: 2.22rem; border-bottom: 2px solid #000; padding-bottom: 5px; margin-bottom: 20px; margin: 15px;">
          <?= esc($dateHeader) ?>
        </h3>
        <div class="block-grid-a-list" style="justify-content: center; display: flex; flex-direction: column; gap: 15px; padding: 15px;">
          <?php foreach ($group as $child): ?>
            <div class="single-cards" style="width: 100%; min-width: 100%!important;">
              <?php snippet('calendar-item-from-csv', [
                'child'     => $child,
                'csvSource' => $csvSource,
                'showTags'  => false,
                'showExtra' => false,
                'showNodo'  => false,
                'class'     => 'no_hover',
                'padding'   => '0 15px'
            ]) ?>
            </div>
          <?php endforeach ?>
        </div>
      </div>
    <?php endforeach ?>
  </div>
</div>
