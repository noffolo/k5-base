<?php
/** @var Kirby\Cms\Collection $collection */
use Kirby\Toolkit\Str;
use function Site\Helpers\Collection\getOccurrences;

$todayStart = strtotime('today');
$next30Days = $todayStart + (30 * 86400);
$monthRequested = get('month');

// 1. Get all occurrences from the collection
$allOccurrences = getOccurrences($collection);

// 2. Extract available months for filters
$availableMonths = [];
$months_it = [
    1 => 'gennaio', 2 => 'febbraio', 3 => 'marzo', 4 => 'aprile',
    5 => 'maggio', 6 => 'giugno', 7 => 'luglio', 8 => 'agosto',
    9 => 'settembre', 10 => 'ottobre', 11 => 'novembre', 12 => 'dicembre'
];

foreach ($allOccurrences as $occ) {
    if ($occ['timestamp'] > 0) {
        $key = date('Y-m', $occ['timestamp']);
        if (!isset($availableMonths[$key])) {
            $m = (int)date('n', $occ['timestamp']);
            $availableMonths[$key] = [
                'key'   => $key,
                'label' => $months_it[$m] . ' ' . date('Y', $occ['timestamp']),
                'ts'    => $occ['timestamp']
            ];
        }
    }
}
ksort($availableMonths);

// 3. Filter occurrences by requested month or fallback to next 30 days
$filteredOccurrences = [];
foreach ($allOccurrences as $occ) {
    if ($monthRequested) {
        if (date('Y-m', $occ['timestamp']) === $monthRequested) {
            $filteredOccurrences[] = $occ;
        }
    } else {
        // Default: only today and future (or next 30 days)
        if ($occ['timestamp'] >= $todayStart) {
            $filteredOccurrences[] = $occ;
        }
    }
}

// 4. Sort and group by date
usort($filteredOccurrences, fn($a, $b) => $a['timestamp'] <=> $b['timestamp']);

$groupedOccurrences = [];
foreach ($filteredOccurrences as $occ) {
    $dateLabel = date('j', $occ['timestamp']) . ' ' . $months_it[(int)date('n', $occ['timestamp'])];
    $groupedOccurrences[$dateLabel][] = $occ;
}

// URL builder for month filters
if (!function_exists('build_calendar_url')) {
    function build_calendar_url($base, $merge = []) {
        $qs = $_GET;
        $qs = array_replace_recursive($qs, $merge);
        foreach ($qs as $k => $v) if ($v === null || $v === '') unset($qs[$k]);
        $query = http_build_query($qs);
        return $base . (empty($query) ? '' : '?' . $query);
    }
}
?>

<div class="calendar-view-container">
    
    <!-- MONTH FILTERS (styled as chips) -->
    <?php if (!empty($availableMonths)): ?>
    <div class="container-collection" style="padding-bottom: 0;">
        <div class="collection-filters" id="months-filters-bar" style="border-bottom: none; margin-bottom: 0;">
            <div class="container-categories">
                <fieldset class="filters-container-fieldset control-group" data-filter-group="months" style="gap: 5px; margin-bottom: 0;">
                    <?php foreach ($availableMonths as $m): 
                        $isActive = ($monthRequested === $m['key']);
                    ?>
                        <a class="single-filter month-filter tag <?= $isActive ? 'active' : '' ?>" 
                           href="<?= build_calendar_url($page->url(), ['month' => $isActive ? null : $m['key']]) ?>"
                           style="background-color: silver; color: black; border: none; padding: 5px 15px; border-radius: 20px; text-decoration: none; font-size: 0.9rem; display: inline-flex; align-items: center; justify-content: center; min-height: 30px; <?= $isActive ? 'outline: 2px solid black; background-color: white; color: black;' : '' ?>">
                          <?= esc($m['label']) ?>
                        </a>
                    <?php endforeach; ?>
                </fieldset>
            </div>
        </div>
    </div>
    <hr style="border: none; border-top: 1px solid whitesmoke!important; margin: 2px 0;">
    <?php endif; ?>
    <!-- Standard Category Filters (they have their own container-collection wrapper) -->
    <?php snippet('collection-filters', [
        'logic' => $logic ?? 'or'
    ]) ?>

    <!-- CAROUSEL GRID -->
    <?php if (empty($groupedOccurrences)): ?>
        <p style="text-align: center; margin: 50px 0;">Nessun appuntamento trovato.</p>
    <?php else: ?>
        <div class="cm-carousel" role="region" aria-label="<?= esc($page->title()) ?>">
          <div class="cm-carousel__track" tabindex="0">
            <?php foreach ($groupedOccurrences as $dateHeader => $group): ?>
              <div class="date-group cm-card--slide" style="text-align: center; min-width: 400px; scroll-snap-align: start;">
                <h3 class="date-header" style="font-weight: 700; font-size: 2.22rem; border-bottom: 2px solid #000; padding-bottom: 5px; margin-bottom: 20px; margin: 15px;">
                  <?= esc($dateHeader) ?>
                </h3>
                <div class="block-grid-a-list" style="justify-content: center; display: flex; flex-direction: column; gap: 15px; padding: 15px;">
                  <?php foreach ($group as $occ): ?>
                    <div class="single-cards" style="width: 100%; min-width: 100%!important;">
                      <?php snippet('calendar-item', [
                          'child'      => $occ['page'],
                          'occurrence' => $occ
                      ]) ?>
                    </div>
                  <?php endforeach ?>
                </div>
              </div>
            <?php endforeach ?>
          </div>
        </div>
    <?php endif; ?>
</div>
