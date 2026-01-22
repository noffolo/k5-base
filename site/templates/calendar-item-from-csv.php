<?php snippet('header') ?>
<?php snippet('menu') ?>
<?php snippet('check_banner', ['posizione' => 'sopra']) ?>

<?php
use Kirby\Toolkit\Str;

// ================== CONFIG & HELPERS ==================

// Accessor semplice dei campi
$val = function (string $key) use ($page) {
  return $page->content()->get($key);
};

// Parent e colori filtri (mappa alias => colore)
$parent       = $page->parent(); // Kirby\Cms\Page|null
$filterColors = $page->parent()?->filterColors() ?? [];

// Limite del parent (coerente con la lista)
$parentLimitDefault = max(1, (int)($parent?->page_size()?->toInt() ?: 20));

// URL del parent
$parentUrl = $parent?->url() ?? '#';

/**
 * Restituisce l'alias corretto da usare nei filtri dell'URL,
 * cercandolo nel parent->alias_map() tra i campi marcati come filter=true.
 */
$getAliasKey = function (string $label) use ($parent): string {
  $fallback = Str::slug($label);
  if (!$parent || $parent->alias_map()->isEmpty()) return $fallback;

  $want = Str::slug($label);
  foreach ($parent->alias_map()->toStructure() as $row) {
    $header   = Str::slug((string)$row->header()->value());
    $alias    = Str::slug((string)$row->alias()->value());
    $isFilter = $row->filter()->toBool();

    if (!$isFilter) continue;
    if ($header === $want || $alias === $want) {
      return $alias;
    }
  }
  return $fallback;
};

/**
 * Costruisce un URL completo per il filtro, slugificando il valore per evitare problemi con accenti.
 * Usa lo stesso algoritmo di Kirby\Toolkit\Str::slug() del model.
 */
$parentFilterUrl = function (string $fieldLabel, string $value) use ($parentUrl, $parentLimitDefault, $getAliasKey) {
  if ($parentUrl === '#') return '#';
  $aliasKey = $getAliasKey($fieldLabel);
  $slugValue = Str::slug(trim($value)); // ✅ SLUGIFY (garantisce coerenza con model)
  $qs = [
    'filter' => [ $aliasKey => $slugValue ],
    'offset' => 0,
    'limit'  => $parentLimitDefault,
  ];
  return $parentUrl . '?' . http_build_query($qs);
};

// Titolo fallback dal parent
$titleFallback = ($parent && $parent->title_fallback()->isNotEmpty())
  ? $parent->title_fallback()->value()
  : 'Provvisoriamente senza titolo';

// ================== COSTRUZIONE DELL’ELENCO CAMPI ==================
$excluded = ['title', 'titolo', 'riguarda', 'obiettivo', 'problema', 'nodo', 'tag','faq', 'base_slug'];

// 1) Leggo la mappa alias dal parent
$mappedAliases = [];
if ($parent && $parent->alias_map()->isNotEmpty()) {
  foreach ($parent->alias_map()->toStructure() as $row) {
    $alias = trim($row->alias()->value());
    if ($alias !== '') {
      $mappedAliases[] = $alias;
    }
  }
}
$mappedAliases = array_values(array_diff($mappedAliases, $excluded));

// 2) Toggle "include_only"
$includeOnly = $parent ? $parent->include_only()->toBool() : false;

// 3) Tutti i campi presenti nell’item
$allFields = array_keys($page->content()->toArray());

// 4-5) Calcolo ordine finale
if ($includeOnly) {
  $fieldsOrder = $mappedAliases;
} else {
  $unmapped    = array_values(array_diff($allFields, array_merge($mappedAliases, $excluded)));
  $fieldsOrder = array_merge($mappedAliases, $unmapped);
}

// 6) Filtro + dedup
$fieldsOrder = array_values(array_diff($fieldsOrder, $excluded));
if (empty($fieldsOrder)) {
  $fieldsOrder = array_values(array_diff($allFields, $excluded));
}
$fieldsOrder = array_values(array_unique($fieldsOrder));
?>

<article class="wrap" style="margin-top: 50px; margin-bottom: 50px;">
  <div class="cards-details orange" style="padding: 40px; border-radius: 15px;">
    
    <header style="text-align: center; margin-bottom: 40px;">
      <h1 style="font-weight: 700; font-size: 3.2rem; margin-bottom: 10px; line-height: 1.1;">
        <?= esc($page->titolo()->or($titleFallback)) ?>
      </h1>
      <?php if ($page->nodo()->isNotEmpty()): ?>
        <?php $colorNodo = $filterColors['nodo'] ?? $filterColors['subtitle'] ?? '#000'; ?>
        <p class="nodo" style="color:<?= $colorNodo ?>; font-weight: bold; font-size: 1.5rem; margin-top: 0;">
          <?= esc($page->nodo()) ?>
        </p>
      <?php endif; ?>

      <?php if ($page->obiettivo()->isNotEmpty()): ?>
        <p class="obiettivo" style="font-size: 1.2rem; margin-top: 20px; max-width: 800px; margin-left: auto; margin-right: auto;">
          <?= esc($page->obiettivo()) ?>
        </p>
      <?php endif; ?>
    </header>

    <div class="item-categories" style="display: flex; flex-wrap: wrap; gap: 10px; justify-content: center; margin-bottom: 40px;">
      <?php if ($page->riguarda()->isNotEmpty()): ?>
        <?php foreach ($page->riguarda()->split(',') as $riguarda): ?>
          <?php
            $riguarda = trim($riguarda);
            if ($riguarda === '') continue;
            $color    = $filterColors['riguarda'] ?? '#000';
            $href     = $parentFilterUrl('riguarda', $riguarda);
          ?>
          <a class="tag" href="<?= $href ?>" style="background-color: transparent!important; border: 1px solid <?= $color ?>!important; color: <?= $color ?>!important; text-decoration: none;">
            <?= esc($riguarda) ?>
          </a>
        <?php endforeach; ?>
      <?php endif; ?>

      <?php if ($page->problema()->isNotEmpty()): ?>
        <?php foreach ($page->problema()->split(',') as $problema): ?>
          <?php
            $problema = trim($problema);
            if ($problema === '') continue;
            $color    = $filterColors['problema'] ?? '#000';
            $href     = $parentFilterUrl('problema', $problema);
          ?>
          <a class="tag" href="<?= $href ?>" style="background-color: transparent!important; border: 1px solid <?= $color ?>!important; color: <?= $color ?>!important; text-decoration: none;">
            <?= esc($problema) ?>
          </a>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>

    <section class="csv-content-page" style="max-width: 800px; margin: 0 auto;">
      <?php foreach ($fieldsOrder as $key): ?>
        <?php if (str_ends_with($key, '_all')) continue; ?>
        
        <?php 
          $field = $val($key); 
          $rawText = $field->value();
          $isDateField = $parent ? $parent->isDate($rawText) : false;
          if ($isDateField && $page->content()->get($key . '_all')->isNotEmpty()) {
              $rawText = $page->content()->get($key . '_all')->value();
          }
          $isTimeField = Str::contains($key, 'ora') || Str::contains($key, 'time');
        ?>
        
        <?php if ($field && $field->isNotEmpty()): ?>
          <div class="field field--<?= esc($key) ?>" style="margin-bottom: 30px; padding-bottom: 20px; border-bottom: 1px solid rgba(0,0,0,0.1);">
            <h3 class="field-name" style="margin-bottom: 10px; font-weight: bold; font-weight: 700; font-size: 1.2rem; text-transform: uppercase;">
              <?= str_replace('_', ' ', ucfirst(esc($key))) ?>
            </h3>
            <div class="field-content" style="font-size: 1.1rem; line-height: 1.6;">
              <?php 
                if ($isDateField && $parent) {
                  $dates = $parent->splitDates($rawText);
                  if (empty($dates)) $dates = [$rawText];
                  foreach ($dates as $idx => $d) {
                    if ($idx > 0) echo '<br>';
                    if (preg_match('/\s*[-–—]\s*/', $d)) {
                       echo esc($parent->formatDate($d)) . ' - ' . esc($parent->formatTime($d));
                    } else {
                       echo esc($parent->formatDate($d));
                    }
                  }
                } elseif ($isTimeField && $parent) {
                  echo esc($parent->formatTime($rawText));
                } else {
                  echo $field->kt();
                }
              ?>
            </div>
          </div>
        <?php endif; ?>
      <?php endforeach; ?>
    </section>

    <?php if ($page->tag()->isNotEmpty() || $page->faq()->isNotEmpty()): ?>
      <footer style="margin-top: 40px; display: flex; flex-wrap: wrap; gap: 10px; justify-content: center;">
        <?php if ($page->tag()->isNotEmpty()): ?>
          <?php foreach ($page->tag()->split(',') as $tag): ?>
            <?php
              $tag = trim($tag);
              if ($tag === '') continue;
              $color    = $filterColors['tag'] ?? '#000';
              $href     = $parentFilterUrl('tag', $tag);
            ?>
            <a class="tag" href="<?= $href ?>" style="background-color: #000!important; color: #fff!important; text-decoration: none;">
              #<?= esc($tag) ?>
            </a>
          <?php endforeach; ?>
        <?php endif; ?>

        <?php if ($page->faq()->isNotEmpty()): ?>
          <?php foreach ($page->faq()->split(',') as $faq): ?>
            <?php
              $faq = trim($faq);
              if ($faq === '') continue;
              $color    = $filterColors['faq'] ?? '#000';
              $href     = $parentFilterUrl('faq', $faq);
            ?>
            <a class="tag" href="<?= $href ?>" style="background-color: transparent!important; border: 1px solid #000!important; color: #000!important; text-decoration: none; font-style: italic;">
              FAQ: <?= esc($faq) ?>
            </a>
          <?php endforeach; ?>
        <?php endif; ?>
      </footer>
    <?php endif; ?>

  </div>
</article>

<?php if ($parent && $parent->collection_toggle()->toBool()): ?>
  <?php snippet('page_related_list') ?>
<?php endif; ?>

<?php snippet('check_banner', ['posizione' => 'sotto']) ?>
<?php snippet('footer') ?>
