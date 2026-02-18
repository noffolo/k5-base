<?php snippet('header') ?>
<?php snippet('menu') ?>
<?php snippet('check_banner',['posizione' => 'sopra']) ?>

<?php snippet('page_navigator') ?>
<?php snippet('page_cover') ?>
<?php snippet('layouts', ['layout_content' => $page->contenuto()]) ?>

<?php
use Kirby\Toolkit\Str;

$isSearch = isset($results) && $results instanceof Kirby\Cms\Pages && $results->isNotEmpty();

if ($isSearch) {
  $items = $results;
} else {
  $items = $page->children();
}

$filterColors = $page->filterColors();

$activeFilters = $_GET['filter'] ?? [];
if (!is_array($activeFilters)) $activeFilters = [];

// ============= URL BUILDER =============
function build_url($base, $merge = []) {
  $qs = $_GET;
  if (!array_key_exists('refresh', $merge)) unset($qs['refresh']);
  if (array_key_exists('filter', $merge)) {
    $qs['filter'] = $merge['filter'];
    unset($merge['filter']);
  }
  
  // Se stiamo settando un mese, rimuoviamo i parametri di paginazione vecchia
  if (array_key_exists('month', $merge)) {
      unset($qs['offset'], $qs['limit']);
  }

  $qs = array_replace_recursive($qs, $merge);

  $filter = function (&$arr) use (&$filter) {
    foreach ($arr as $k => $v) {
      if (is_array($v)) {
        $filter($arr[$k]);
        if ($arr[$k] === [] || $arr[$k] === null) unset($arr[$k]);
      } else {
        if ($v === null || $v === '') unset($arr[$k]);
      }
    }
  };
  $filter($qs);
  $query = http_build_query($qs);
  return $base . (empty($query) ? '' : '?' . $query);
}
?>

<main id="main" class="main wrap">
  <?php
  $filterable = $page->filterableFields();
  if (!empty($filterable)):
  ?>
  <div class="container-collection">
    <div class="collection-filters" id="filters-bar">
      <div class="container-categories">
        
        <fieldset id="months" class="filters-container-fieldset control-group" data-filter-group="months" 
        style="gap: 5px; margin-bottom: 0;">
          <?php 
          $availableMonths = $page->availableMonths();
          $currentMonth = get('month');
          foreach ($availableMonths as $m): 
              $isActive = ($currentMonth === $m['key']);
          ?>
            <a class="single-filter month-filter tag <?= $isActive ? 'active' : '' ?>" 
               href="<?= build_url($page->url(), ['month' => $isActive ? null : $m['key']]) ?>"
               style="background-color: #c800ff; color: white; border: none; padding: 5px 15px; border-radius: 20px; text-decoration: none; font-size: 0.9rem; display: inline-flex; align-items: center; justify-content: center; min-height: 30px; <?= $isActive ? 'outline: 2px solid #c800ff; background-color: white; color: #c800ff;' : '' ?>">
              <?= esc($m['label']) ?>
            </a>
          <?php endforeach; ?>
        </fieldset>

        <?php if (!empty(array_filter($activeFilters)) || $currentMonth): ?>
        <fieldset class="filters-container-fieldset control-group" data-filter-group="categories" data-logic="and">
          <a class="all-filter single-filter control white" 
             href="<?= $page->url() ?>" title="tutti">TUTTI</a>
        </fieldset>
        <?php endif; ?>

        <?php foreach ($filterable as $field): ?>
          <?php
            $values = $page->filterValues($field);
            if (empty($values)) continue;
          ?>
          <fieldset id="<?= $field ?>" class="filters-container-fieldset control-group" data-filter-group="categories" data-logic="and">
            <?php
              $currentCsv = $activeFilters[$field] ?? '';
              $currentArr = array_values(array_unique(array_filter(array_map('trim', explode(',', (string)$currentCsv)))));
            ?>

            <?php foreach ($values as $val): ?>
              <?php
                $slugVal   = Str::slug($val);
                $slugArr   = array_map(fn($v) => Str::slug($v), $currentArr);
                $isActive  = in_array($slugVal, $slugArr, true);

                $newArr = $slugArr;
                if ($isActive) {
                  $newArr = array_values(array_diff($newArr, [$slugVal]));
                } else {
                  $newArr[] = $slugVal;
                }

                $newArr = array_values(array_unique(array_filter($newArr)));
                $newFilters = $activeFilters;
                if (empty($newArr)) {
                  unset($newFilters[$field]);
                } else {
                  $newFilters[$field] = implode(',', $newArr);
                }

                $params = [
                  'filter' => $newFilters,
                ];

                $slug = Str::slug($val);
              ?>
              <a class="single-filter control <?= $slug ?> <?php if ($isActive): ?>active<?php endif; ?>" 
                 href="<?= build_url($page->url(), $params) ?>">
                <?= esc($val) ?>
              </a>
            <?php endforeach; ?>
          </fieldset>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
  <?php endif; ?>

  <?php
  // Dynamic styles for CSV filters
  echo "<style>";
  // Style for "TUTTI" button
  echo "
  .single-filter.white:not(.active) {
      outline: 1px solid black !important;
      color: black !important;
      background-color: transparent !important;
  }
  ";
  foreach ($filterColors as $field => $color) {
      $values = $page->filterValues($field);
      foreach ($values as $val) {
          $slug = Str::slug($val);
          echo "
          .single-filter.{$slug}:not(.active) {
              outline: 1px solid {$color} !important;
              color: {$color} !important;
              background-color: transparent !important;
          }
          .single-filter.{$slug}.active {
              outline: 2px solid black !important;
              color: black !important;
              background-color: transparent !important;
          }
          .single-filter.{$slug}:hover {
              outline: 2px solid black !important;
              color: black !important;
          }
          ";
      }
  }
  echo "</style>";
  ?>


  <?php if ($items->isEmpty()): ?>
    <p style="text-align: center; margin: 50px 0;">Nessuna voce trovata.</p>
  <?php else: ?>
    <?php 
    // Group items by date
    $groupedItems = [];
    foreach ($items as $child) {
        $dateStr = $page->fieldByRole($child->content()->toArray(), 'date');
        $formattedDate = $page->formatDate($dateStr) ?: 'Altro';
        $groupedItems[$formattedDate][] = $child;
    }
    ?>

    <?php snippet('calendar-carousel', [
      'groupedItems' => $groupedItems,
      'title'        => $page->title()->value(),
      'csvSource'    => $page
    ]) ?>

  <?php endif; ?>
</main>

<?php if($page->parent() !== null): ?>
  <?php $parent = $page->parent() ?>
  <?php if ($parent && method_exists($parent, 'collection_toggle') && $parent->collection_toggle()->toBool()): ?>
    <?php snippet('page_related_list') ?>
  <?php endif; ?>
<?php endif; ?>

<?php snippet('check_banner',['posizione' => 'sotto']) ?>
<?php snippet('layouts', ['layout_content' => $site->footer(), 'class' => 'footer']) ?>
<?php snippet('footer') ?>
