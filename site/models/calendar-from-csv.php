<?php

use Kirby\Cms\Pages;
use Kirby\Http\Remote;
use Kirby\Toolkit\Str;

require_once __DIR__ . '/default.php';

class CalendarFromCsvPage extends DefaultPage
{
    /** per-request caches */
    protected static ?string $csvBodyCache = null;
    protected static ?int    $rowCountCache = null;      // conteggio risultati (post-filtri) per la richiesta corrente
    protected static ?array  $rowMapCache = null;        // slug => content (per item O(1))
    protected static ?array  $filtersIndexCache = null;  // alias => [values] tokenizzati per la richiesta corrente
    protected static $searchPoolCache = null;            // Pages di TUTTE le righe (senza limiti/filtri) per la search
    protected array          $filters = [];              // alias marcati come filtrabili nel Panel
    protected array          $rolesIndex = [];           // role => alias
    protected array          $csvErrors = [];

    public function csvHealth(): string
    {
        $body = $this->fetchCsvBody(true);
        if ($body) {
            if (empty($this->csvErrors)) {
                return "✅ Connessione stabilita e dati ricevuti.";
            } else {
                return "⚠️ Attenzione: " . implode(" ", $this->csvErrors);
            }
        }
        return "❌ Errore critico: " . ($this->csvErrors[0] ?? "Impossibile caricare il CSV.");
    }

    /* ===== Utils di percorso ===== */
    protected function requestPath(): string
    {
        return trim($this->kirby()->request()->url()->path()->toString(), '/');
    }
    protected function parentPath(): string
    {
        return trim(parse_url($this->url(), PHP_URL_PATH), '/');
    }
    /** Se l'URL è /parent/child ritorna "child", altrimenti null */
    protected function requestedChildSlug(): ?string
    {
        $req = $this->requestPath();
        $parent = $this->parentPath();
        if ($req === $parent || $req === '') return null;
        if (Str::startsWith($req, $parent . '/')) {
            $rest = substr($req, strlen($parent) + 1);
            $child = strtok($rest, '/');
            return $child ?: null;
        }
        return null;
    }

    /** Colori associati agli alias filtrabili */
public function filterColors(): array
{
    $colors = [];
    $struct = $this->alias_map()->isNotEmpty() ? $this->alias_map()->toStructure() : [];
    foreach ($struct as $row) {
        $alias = Str::slug(trim($row->alias()->value() ?? ''));
        if ($row->filter()->toBool() && $alias !== '') {
            $colore = trim($row->colore()->value() ?? '');
            if ($colore !== '') {
                $colors[$alias] = $colore;
            }
        }
    }
    return $colors;
}
    /* ===== Tokenizer per campi con liste "A, B, C" ===== */
    protected function tokenize(?string $raw): array
    {
        if ($raw === null) return [];
        $parts = array_map('trim', explode(',', $raw));
        $parts = array_filter($parts, fn($v) => $v !== '');
        // dedup case-insensitive mantenendo il testo “bello”
        $seen = [];
        $out  = [];
        foreach ($parts as $p) {
            $k = Str::slug($p);
            if (!isset($seen[$k])) {
                $seen[$k] = true;
                $out[] = $p;
            }
        }
        return array_values($out);
    }

    /* ===== Default dal Panel ===== */
    protected function sheetDefaults(): array
    {
        $ttlField   = (int)($this->cache_ttl()->toInt() ?? 0);
        $ttl        = $ttlField > 0 ? $ttlField : (int) option('sheet.cacheTtl', 300);

        $psField    = (int)($this->page_size()->toInt() ?? 0);
        $pageSize   = $psField > 0 ? $psField : (int) option('sheet.pageSize', 20);

        // fallback generico per TUTTI i campi vuoti
        $fallback   = trim($this->fallback()->or('—')->value());
        $fallback   = $fallback !== '' ? $fallback : '—';

        $includeOnly = $this->include_only()->toBool();

        return [
            'ttl'         => $ttl,
            'pageSize'    => $pageSize,
            'fallback'    => $fallback,
            'includeOnly' => $includeOnly,
        ];
    }

    /* ===== Fetch CSV con Conditional GET + Anti-stampede Lock ===== */
    protected function fetchCsvBody(bool $force = false): ?string
    {
        // blocca richieste del Panel a meno che non sia forzato (es. per health check)
        $path = $this->requestPath();
        if (!$force && ($path === 'panel' || Str::startsWith($path, 'panel') || Str::contains($path, '/panel'))) {
            return null;
        }

        if (self::$csvBodyCache !== null) {
            return self::$csvBodyCache;
        }

        $csvUrl = $this->content()->get('csv_url')->value();
        if (!$csvUrl) {
            $this->csvErrors[] = "URL CSV non configurato.";
            return null;
        }

        $defaults    = $this->sheetDefaults();
        $kirbyCache  = $this->kirby()->cache('sheet');
        $ttl         = (int) $defaults['ttl'];
        $keyBody     = 'csv:' . md5($csvUrl);
        $keyMeta     = 'csvmeta:' . md5($csvUrl);

        // Cache calda
        if (!$force && get('refresh') !== '1') {
            if ($csv = $kirbyCache->get($keyBody)) {
                self::$csvBodyCache = $csv;
                return $csv;
            }
        }

        // Anti-stampede lock
        $lockPath = kirby()->roots()->cache() . '/sheet.lock';
        @is_dir(dirname($lockPath)) || @mkdir(dirname($lockPath), 0775, true);
        $lock = @fopen($lockPath, 'c');
        if ($lock) @flock($lock, LOCK_EX);

        try {
            $meta    = $kirbyCache->get($keyMeta) ?: [];
            $headers = ['Accept-Encoding' => 'gzip'];
            if (!empty($meta['etag']))          $headers['If-None-Match']     = $meta['etag'];
            if (!empty($meta['last_modified'])) $headers['If-Modified-Since'] = $meta['last_modified'];

            $res = Remote::get($csvUrl, ['timeout' => 3, 'headers' => $headers]);

            if ($res->code() === 304 && ($stale = $kirbyCache->get($keyBody))) {
                $csvBody = $stale;
            } elseif ($res->code() === 200) {
                $csvBody = $res->content();
                if ($ttl > 0) $kirbyCache->set($keyBody, $csvBody, $ttl);
                $h = $res->headers();
                $kirbyCache->set($keyMeta, [
                    'etag'          => $h['etag'][0]          ?? null,
                    'last_modified' => $h['last-modified'][0] ?? null,
                ], $ttl);
            } else {
                $this->csvErrors[] = "Risposta HTTP non valida ({$res->code()}).";
                if ($stale = $kirbyCache->get($keyBody)) {
                    $csvBody = $stale;
                } else {
                    return null;
                }
            }
        } catch (\Throwable $e) {
            $this->csvErrors[] = "Errore di connessione: " . $e->getMessage();
            if ($stale = $kirbyCache->get($keyBody)) {
                $csvBody = $stale;
            } else {
                return null;
            }
        } finally {
            if ($lock) { @flock($lock, LOCK_UN); @fclose($lock); }
        }

        self::$csvBodyCache = $csvBody;
        return $csvBody;
    }

    /* ===== Alias (Panel override > base) + elenco campi filtrabili ===== */
    protected function buildAliases(): array
    {
        $baseAliases = [
            'slug'     => 'slug',
            'template' => 'template',
            'title'    => 'title',
            'name'     => 'name',
        ];

        $aliasMap = [];
        $filters  = [];
        $roles    = [];
        $struct = $this->alias_map()->isNotEmpty() ? $this->alias_map()->toStructure() : [];
        foreach ($struct as $row) {
            $h = Str::slug(trim($row->header()->value() ?? ''));
            $a = Str::slug(trim($row->alias()->value() ?? ''));
            $r = trim($row->role()->value() ?? 'none');
            if ($h !== '' && $a !== '') {
                $aliasMap[$h] = $a;
                if ($row->filter()->toBool()) {
                    $filters[] = $a;
                }
                if ($r !== 'none') {
                    $roles[$r] = $a;
                }
            }
        }
        $this->filters    = $filters;
        $this->rolesIndex = $roles;

        return array_merge($baseAliases, $aliasMap);
    }

    /** Ritorna il valore di un campo in base al ruolo assegnato o dedotto */
    public function fieldByRole(array $assoc, string $role): ?string
    {
        if (empty($this->rolesIndex)) {
            $this->buildAliases();
        }

        // 1) Se c'è un alias mappato a questo ruolo
        if (isset($this->rolesIndex[$role])) {
            $alias = $this->rolesIndex[$role];
            if (isset($assoc[$alias]) && trim($assoc[$alias]) !== '') {
                return $assoc[$alias];
            }
        }

        // 2) Fallback euristici per ruoli comuni
        if ($role === 'title') {
            return $assoc['titolo'] ?? $assoc['title'] ?? $assoc['name'] ?? null;
        }

        if ($role === 'date') {
            // Cerca un campo che sembra una data se non mappato
            foreach ($assoc as $k => $v) {
                if ($this->isDate($v)) return $v;
            }
        }

        if ($role === 'orario') {
            return $assoc['orario'] ?? $assoc['ora'] ?? $assoc['time'] ?? null;
        }

        // 3) Handling for tags
        if ($role === 'tag' || $role === 'tag1') {
            return $assoc['tag1'] ?? $assoc['tag'] ?? null;
        }
        if ($role === 'tag2') {
            return $assoc['tag2'] ?? null;
        }

        return null;
    }

    /** Ritorna tutti i campi che NON hanno un ruolo specifico assegnato */
    public function extraFields(array $assoc): array
    {
        if (empty($this->rolesIndex)) {
            $this->buildAliases();
        }

        $mappedAliases = array_values($this->rolesIndex);
        $baseFields = ['slug', 'template', 'titolo', 'title', 'name'];

        $extra = [];
        foreach ($assoc as $k => $v) {
            if (in_array($k, $mappedAliases)) continue;
            if (in_array($k, $baseFields)) continue;
            if ($k === 'base_slug') continue;
            if (str_ends_with($k, '_all')) continue;
            if (trim((string)$v) === '') continue;
            
            $extra[$k] = $v;
        }
        return $extra;
    }

    /** Heuristic per identificare se una stringa è una data */
    public function isDate($val): bool
    {
        if (!is_string($val) || trim($val) === '') return false;
        // Formato comune in Google Sheets/CSV: "10/10/2025" o "2025-10-10" o "10/10/2025 10.41.24"
        return preg_match('/^\d{1,2}[\/\-\.]\d{1,2}[\/\-\.]\d{2,4}/', trim($val)) === 1;
    }

    /** Split a string containing multiple dates (separated by comma or newline) */
    public function splitDates(?string $val): array
    {
        if (!$val || trim($val) === '') return [];
        // Split by comma or newline, ma preserva i trattini usati per range orari (es: 17:00-19:00)
        // Usiamo un lookahead/lookbehind se vogliamo essere furbi, ma uno split base su virgola/newline di solito basta
        $parts = preg_split('/[,\\n]+/', $val);
        $parts = array_map('trim', $parts);
        return array_values(array_filter($parts, fn($v) => $v !== ''));
    }

    /** Parser robusto per date/orari (con mesi IT e formati vari) */
    public function parseToTimestamp(?string $val, ?int $hintYear = null): int
    {
        if (!$val || trim($val) === '') return 0;

        // 0) Pre-normalizzazione: trasforma 17_30 in 17:30
        $clean = preg_replace('/(\d{1,2})_(\d{2})/', '$1:$2', strtolower(trim($val)));
        $clean = preg_replace('/\s+/', ' ', $clean);
        
        // 1) Traduzione mesi (LUNGHI PRIMA per evitare match parziali)
        $itMonths = [
            'gennaio' => 'january', 'febbraio' => 'february', 'marzo' => 'march',
            'aprile' => 'april', 'maggio' => 'may', 'giugno' => 'june',
            'luglio' => 'july', 'agosto' => 'august', 'settembre' => 'september',
            'ottobre' => 'october', 'novembre' => 'november', 'dicembre' => 'december',
            'gen' => 'jan', 'feb' => 'feb', 'mar' => 'mar', 'apr' => 'apr',
            'mag' => 'may', 'giu' => 'jun', 'lug' => 'jul', 'ago' => 'aug',
            'set' => 'sep', 'ott' => 'oct', 'nov' => 'nov', 'dic' => 'dec'
        ];
        foreach ($itMonths as $it => $en) {
            $clean = str_replace($it, $en, $clean);
        }

        // 2) Normalizzazione Separatori per Data e Ora
        // Se c'è un separatore " - " o " – ", proviamo a isolare la data
        $parts = preg_split('/\s*[-–—]\s*/', $clean);
        $dateStr = $parts[0];
        $timeStr = isset($parts[1]) ? $parts[1] : '';

        $dateParts = explode(' ', $dateStr);
        $normalizedDateParts = [];
        $hasYear = false;
        foreach ($dateParts as $p) {
            $sub = explode('.', $p);
            if (count($sub) >= 2) {
                $last = end($sub);
                if (strlen($last) === 4) {
                    $hasYear = true;
                    $normalizedDateParts[] = str_replace('.', '-', $p);
                } else {
                    $normalizedDateParts[] = str_replace('.', ':', $p);
                }
            } else {
                $pNorm = str_replace('/', '-', $p);
                if (preg_match('/-\d{4}$/', $pNorm)) $hasYear = true;
                $normalizedDateParts[] = $pNorm;
            }
        }
        $dateStr = implode(' ', $normalizedDateParts);

        // Se non abbiamo l'anno ma abbiamo un suggerimento, aggiungiamolo
        if (!$hasYear && $hintYear !== null && !preg_match('/\b\d{4}\b/', $dateStr)) {
            if (preg_match('/^\d{1,2}-\d{1,2}$/', $dateStr)) {
                $dateStr .= '-' . $hintYear;
            } elseif (preg_match('/^\d{1,2}\s+[a-z]+$/', $dateStr)) {
                $dateStr .= ' ' . $hintYear;
            }
        }

        // Proviamo a ricomporre con l'orario se presente
        $fullStr = $dateStr . ($timeStr ? ' ' . $timeStr : '');
        $ts = strtotime($fullStr);

        if ($ts === false) {
            // Fallback solo data
            $ts = strtotime($dateStr);
        }

        if ($ts === false) {
            // Caso DD-MM senza anno
            $partsArr = explode('-', $dateStr);
            if (count($partsArr) === 2 && is_numeric($partsArr[0]) && is_numeric($partsArr[1])) {
                $dateStr .= '-' . ($hintYear ?? date('Y'));
                $ts = strtotime($dateStr);
            }
        }

        return $ts !== false ? $ts : 0;
    }

    /** Formatta una stringa data in modo leggibile (es: 17 OTTOBRE) */
    public function formatDate(?string $val, ?int $hintYear = null): string
    {
        $timestamp = $this->parseToTimestamp($val, $hintYear);
        if ($timestamp === 0) {
            return (string)$val;
        }
        
        $day = date('j', $timestamp);
        $monthNum = (int)date('n', $timestamp);
        
        $months = [
            1 => 'gennaio', 2 => 'febbraio', 3 => 'marzo', 4 => 'aprile',
            5 => 'maggio', 6 => 'giugno', 7 => 'luglio', 8 => 'agosto',
            9 => 'settembre', 10 => 'ottobre', 11 => 'novembre', 12 => 'dicembre'
        ];
        
        $monthName = $months[$monthNum] ?? '';
        
        return $day . ' ' . $monthName;
    }

    /** Formatta l'orario se presente */
    public function formatTime(?string $val): string
    {
        if (!$val || trim($val) === '') return '';
        
        // 1) Normalizza orari: trasforma 17_30 in 17:30
        $clean = preg_replace('/(\d{1,2})_(\d{2})/', '$1:$2', strtolower(trim($val)));

        // 2) Se c'è un separatore " - " o " – ", l'orario è dopo
        $parts = preg_split('/\s*[-–—]\s*/', $clean);
        $timePart = (count($parts) > 1) ? $parts[1] : $clean;

        // Se la parte oraria sembra ancora contenere una data, proviamo a pulirla
        // (es: "09/10/2025 17:30-19:30" -> "17:30-19:30")
        $timePart = preg_replace('/^\d{1,2}[\/\-\.]\d{1,2}([\/\-\.]\d{2,4})?\s+/', '', $timePart);

        // 3) Normalizza range orari (es: 17:30-19:30 diventa 17:30 - 19:30)
        // ma prima assicuriamoci che i separatori interni siano ":"
        $timePart = preg_replace('/(\d{1,2})[:\.](?=\d{2})/', '$1:', $timePart);
        
        if (preg_match('/(\d{1,2}:\d{2})\s*[-–—]\s*(\d{1,2}:\d{2})/', $timePart, $m)) {
            return $m[1] . ' - ' . $m[2];
        }

        // Caso orario singolo
        if (preg_match('/(\d{1,2}:\d{2})/', $timePart, $m)) {
            return $m[1];
        }

        return '';
    }

    /** Campi filtrabili (alias) da usare nel template */
    public function filterableFields(): array
    {
        if (empty($this->filters)) {
            $this->buildAliases(); // assicura popolazione
        }
        return $this->filters;
    }

    /** Valori UNICI tokenizzati per un alias filtrabile (ordinati) */
    public function filterValues(string $field): array
    {
        if (self::$filtersIndexCache !== null && isset(self::$filtersIndexCache[$field])) {
            return self::$filtersIndexCache[$field];
        }

        $values = [];
        foreach ($this->parseCsvRows() as $row) {
            foreach ($this->tokenize($row[$field] ?? '') as $token) {
                $values[] = $token;
            }
        }
        // dedup + sort naturale case-insensitive
        $values = array_values(array_unique($values));
        sort($values, SORT_NATURAL | SORT_FLAG_CASE);

        self::$filtersIndexCache[$field] = $values;
        return $values;
    }

    public function availableMonths(): array
    {
        if (empty($this->rolesIndex)) {
            $this->buildAliases();
        }

        $months = [];
        $dateAlias = $this->rolesIndex['date'] ?? null;
        $stickyYear = (int)date('Y');

        foreach ($this->parseCsvRows() as $assoc) {
            $rawDateOriginal = $dateAlias ? ($assoc[$dateAlias] ?? '') : (string)$this->fieldByRole($assoc, 'date');
            if (preg_match('/\b(\d{4})\b/', $rawDateOriginal, $matches)) {
                $stickyYear = (int)$matches[1];
            }
            $dates = $this->splitDates($rawDateOriginal);
            foreach ($dates as $d) {
                $ts = $this->parseToTimestamp($d, $stickyYear);
                if ($ts > 0) {
                    $key = date('Y-m', $ts);
                    if (!isset($months[$key])) {
                        $months[$key] = [
                            'key'   => $key,
                            'label' => $this->formatMonthLabel($ts)
                        ];
                    }
                }
            }
        }
        ksort($months);
        return array_values($months);
    }

    protected function formatMonthLabel(int $ts): string
    {
        $m = (int)date('n', $ts);
        $months = [
            1 => 'gennaio', 2 => 'febbraio', 3 => 'marzo', 4 => 'aprile',
            5 => 'maggio', 6 => 'giugno', 7 => 'luglio', 8 => 'agosto',
            9 => 'settembre', 10 => 'ottobre', 11 => 'novembre', 12 => 'dicembre'
        ];
        return ($months[$m] ?? '') . ' ' . date('Y', $ts);
    }

    /* ===== Parser generator (senza fallback; whitelist opzionale) ===== */
    protected function parseCsvRows(callable $onHeaders = null): \Generator
    {
        $csvBody = $this->fetchCsvBody();
        if (!$csvBody) {
            if ($onHeaders) $onHeaders([]);
            if (false) yield [];
            return;
        }

        $defaults       = $this->sheetDefaults();
        $aliases        = $this->buildAliases();
        $includeOnly    = (bool) $defaults['includeOnly'];
        $allowedAliases = $includeOnly ? array_values($aliases) : [];
        $mustKeep       = ['slug','template','titolo','nodo','title','name'];

        $stream = fopen('php://temp', 'r+');
        fwrite($stream, $csvBody);
        rewind($stream);

        $headersRaw = fgetcsv($stream, 0, ',');
        if (!$headersRaw || count($headersRaw) === 0) {
            fclose($stream);
            if ($onHeaders) $onHeaders([]);
            if (false) yield [];
            return;
        }

        $headersSlug = array_map(fn($h) => Str::slug(trim((string)$h)), $headersRaw);
        $keys = array_map(fn($k) => $aliases[$k] ?? $k, $headersSlug);

        if ($onHeaders) $onHeaders($keys);

        while (($row = fgetcsv($stream, 0, ',')) !== false) {
            if ($row === [null] || $row === [] || (count($row) === 1 && trim((string)$row[0]) === '')) continue;

            if (count($row) < count($keys))      $row = array_pad($row, count($keys), '');
            elseif (count($row) > count($keys))  $row = array_slice($row, 0, count($keys));

            // mappa chiave => valore (post-alias), nessun fallback qui
            $assoc = [];
            foreach ($keys as $i => $key) {
                $assoc[$key] = trim((string)($row[$i] ?? ''));
            }

            // whitelist per ridurre payload
            if ($includeOnly && !empty($allowedAliases)) {
                $filtered = array_intersect_key($assoc, array_flip($allowedAliases));
                foreach ($mustKeep as $mk) {
                    if (array_key_exists($mk, $assoc)) $filtered[$mk] = $assoc[$mk];
                }
                $assoc = $filtered;
            }

            yield $assoc;
        }

        fclose($stream);
    }

    /* ===== Conteggio totale righe (post-filtri se già calcolato) ===== */
public function totalRows(): int
{
    if (self::$rowCountCache !== null) {
        return self::$rowCountCache;
    }

    // Leggi i filtri attivi dal querystring, limitandoti agli alias marcati filtrabili
    $filterParam = $_GET['filter'] ?? [];
    $activeFilters = [];
    if (is_array($filterParam)) {
        foreach ($filterParam as $alias => $valueCsv) {
            $alias = \Kirby\Toolkit\Str::slug((string)$alias);
            if (!in_array($alias, $this->filterableFields(), true)) {
                continue;
            }
            $vals = array_values(array_unique(array_filter(array_map('trim', explode(',', (string)$valueCsv)))));
            $vals = array_map(fn($v) => \Kirby\Toolkit\Str::slug($v), $vals);
            if (!empty($vals)) {
                $activeFilters[$alias] = $vals;
            }
        }
    }
    $hasActiveFilters = !empty($activeFilters);

    // Conta le righe del CSV applicando gli stessi criteri di filtro di children()
    $count = 0;
    $dateAlias = $this->rolesIndex['date'] ?? null;
    $todayStart = strtotime('today');
    $stickyYear = (int)date('Y');

    foreach ($this->parseCsvRows() as $assoc) {
        $rawDateOriginal = $dateAlias ? ($assoc[$dateAlias] ?? '') : (string)$this->fieldByRole($assoc, 'date');
        if (preg_match('/\b(\d{4})\b/', $rawDateOriginal, $matches)) {
            $stickyYear = (int)$matches[1];
        }

        if ($hasActiveFilters) {
            $ok = true;
            foreach ($activeFilters as $alias => $requiredSlugs) {
                $rowTokensSlugs = array_map(
                    fn($t) => \Kirby\Toolkit\Str::slug($t),
                    $this->tokenize($assoc[$alias] ?? '')
                );
                if (!empty(array_diff($requiredSlugs, $rowTokensSlugs))) {
                    $ok = false;
                    break;
                }
            }
            if (!$ok) continue;
        }

        // Se abbiamo un campo data, contiamo ogni occorrenza splittata che non sia nel passato
        if ($dateAlias && !empty($assoc[$dateAlias])) {
            $dates = $this->splitDates($assoc[$dateAlias]);
            foreach ($dates as $d) {
                $ts = $this->parseToTimestamp($d, $stickyYear);
                // Se non è una data valida (0) o è da oggi in poi, contiamo
                if ($ts === 0 || $ts >= $todayStart) {
                    $count++;
                }
            }
        } else {
            $count++;
        }
    }

    self::$rowCountCache = $count;
    return $count;
}

    /* ===== Children: item O(1) + listing single-scan con multi-filtri (AND) ===== */
    public function children(): Pages
    {
        $defaults = $this->sheetDefaults();
        $fallback = $defaults['fallback'];

        // helper: primo non vuoto (NON usa fallback)
        $firstNonEmpty = function (...$vals) {
            foreach ($vals as $v) {
                if (isset($v) && trim((string)$v) !== '') return $v;
            }
            return null;
        };

        // helper: crea riga base slug
        $getBaseSlug = function(array $assoc) use ($firstNonEmpty) {
            $base = $firstNonEmpty(
                $assoc['slug']  ?? null,
                $assoc['titolo']?? $assoc['title'] ?? null,
                $assoc['nodo']  ?? null,
                $assoc['name']  ?? null
            );
            if ($base === null) $base = substr(md5(json_encode($assoc)), 0, 10);
            return Str::slug((string)$base);
        };

        // helper: crea child data-associata
        $makeChild = function(array $assoc, string $slug, string $fallback) {
            $content = $assoc;
            foreach ($content as $k => $v) {
                if (trim((string)$v) === '') $content[$k] = $fallback;
            }
            if (!isset($content['titolo']) || trim((string)$content['titolo']) === '') {
                $content['titolo'] = $fallback;
            }

            $tpl = $content['template'] ?? 'calendar-item-from-csv';
            return [
                'slug'     => $slug,
                'num'      => 0,
                'template' => $tpl,
                'model'    => $tpl,
                'content'  => $content,
            ];
        };

        $childSlugRequested = $this->requestedChildSlug();

        // Lettura filtri
        $filterParam = $_GET['filter'] ?? [];
        $activeFilters = []; 
        if (is_array($filterParam)) {
            foreach ($filterParam as $alias => $valueCsv) {
                $alias = Str::slug((string)$alias);
                if (!in_array($alias, $this->filterableFields(), true)) continue;
                $vals = array_values(array_unique(array_filter(array_map('trim', explode(',', (string)$valueCsv)))));
                $vals = array_map(fn($v) => Str::slug($v), $vals);
                if (!empty($vals)) $activeFilters[$alias] = $vals;
            }
        }

        $monthRequested = get('month');
        $hasActiveFilters = (!empty($activeFilters) || $monthRequested) && !$childSlugRequested;

        $csvBody = $this->fetchCsvBody();
        if (!$csvBody) return new Pages([]);
        
        $hash   = md5($csvBody);
        $cache  = $this->kirby()->cache('sheet');
        $mapKey = 'csvmap:' . $hash;

        // In ITEM mode, se abbiamo la cache cerchiamo il match esatto o parziale (base slug)
        if ($childSlugRequested) {
            $map = self::$rowMapCache ?? $cache->get($mapKey) ?? [];
            if (isset($map[$childSlugRequested])) {
                $child = $makeChild($map[$childSlugRequested], $childSlugRequested, $fallback);
                return Pages::factory([$child], $this);
            }
            // Fallback: cerca se è un base slug
            foreach ($map as $s => $cont) {
                if (strpos($s, $childSlugRequested) === 0) {
                     $child = $makeChild($cont, $childSlugRequested, $fallback);
                     return Pages::factory([$child], $this);
                }
            }
        }

        // Listing o Item (fallback scan)
        $allItems = [];
        $map      = []; 
        $fidx     = [];
        $dateAlias = $this->rolesIndex['date'] ?? null;
        $stickyYear = (int)date('Y');
        $todayStart = strtotime('today');
        $next30Days = $todayStart + (30 * 86400);

        foreach ($this->parseCsvRows() as $assoc) {
            $rawDateOriginal = $dateAlias ? ($assoc[$dateAlias] ?? '') : (string)$this->fieldByRole($assoc, 'date');
            if (preg_match('/\b(\d{4})\b/', $rawDateOriginal, $matches)) {
                $stickyYear = (int)$matches[1];
            }

            if (!$childSlugRequested) {
                foreach ($this->filterableFields() as $ff) {
                    foreach ($this->tokenize($assoc[$ff] ?? '') as $token) {
                        $fidx[$ff][] = $token;
                    }
                }
            }

            if ($hasActiveFilters) {
                $ok = true;
                foreach ($activeFilters as $alias => $requiredSlugs) {
                    $rowTokensSlugs = array_map(fn($t) => Str::slug($t), $this->tokenize($assoc[$alias] ?? ''));
                    if (!empty(array_diff($requiredSlugs, $rowTokensSlugs))) {
                        $ok = false;
                        break;
                    }
                }
                if (!$ok) continue;
            }

            $baseSlug = $getBaseSlug($assoc);
            $dates = $this->splitDates($rawDateOriginal);
            if (empty($dates)) $dates = [$rawDateOriginal];

            foreach ($dates as $index => $d) {
                $ts = $this->parseToTimestamp($d, $stickyYear);

                // Filtro per Mese (se richiesto)
                if ($monthRequested) {
                    if (date('Y-m', $ts) !== $monthRequested) {
                        continue;
                    }
                } else {
                    // Temporalizzazione standard: prossimi 30 giorni
                    // (applicata se non c'è un filtro mese, anche se ci sono altri filtri attivi)
                    if ($ts < $todayStart || $ts > $next30Days) {
                        continue;
                    }
                }

                $slug = $baseSlug;
                if (count($dates) > 1) {
                    $dateSlugPart = $ts > 0 ? date('Ymd', $ts) : 'occurrence-' . ($index + 1);
                    $slug .= '-' . $dateSlugPart;
                }
                
                $child = $makeChild($assoc, $slug, $fallback);
                if ($dateAlias) {
                    $child['content'][$dateAlias . '_all'] = $rawDateOriginal;
                    $child['content'][$dateAlias] = $d;
                }
                
                // Salviamo nella mappa per redirect/item mode
                $map[$slug] = $child['content'];
                if ($slug !== $baseSlug && !isset($map[$baseSlug])) {
                    $map[$baseSlug] = $child['content'];
                }

                if ($childSlugRequested === $slug || $childSlugRequested === $baseSlug) {
                    $finalChild = $makeChild($assoc, $childSlugRequested, $fallback);
                    return Pages::factory([ $finalChild ], $this);
                }

                if (!$childSlugRequested) {
                    $child['_date_ts'] = $ts;
                    $child['content']['base_slug'] = $baseSlug;
                    $allItems[] = $child;
                }
            }
        }

        if ($childSlugRequested) return new Pages([]);

        usort($allItems, function($a, $b) {
            $tsA = $a['_date_ts'] ?? 0;
            $tsB = $b['_date_ts'] ?? 0;
            $dayA = floor($tsA / 86400) * 86400;
            $dayB = floor($tsB / 86400) * 86400;
            if ($dayA != $dayB) return $dayA <=> $dayB;
            return $tsA <=> $tsB; 
        });

        self::$rowCountCache = count($allItems);

        foreach ($fidx as $k => $arr) {
            $arr = array_values(array_unique($arr));
            sort($arr, SORT_NATURAL | SORT_FLAG_CASE);
            $fidx[$k] = $arr;
        }
        self::$filtersIndexCache = $fidx;

        $cache->set($mapKey, $map, $defaults['ttl']);
        self::$rowMapCache = $map;

        return empty($allItems) ? new Pages([]) : Pages::factory($allItems, $this);
    }

    /* ===== Pool COMPLETO per la ricerca (senza limiti/filtri) ===== */
    public function searchPool(): Pages
    {
        // per-request cache: se già creato in questa richiesta, riusa
        if (self::$searchPoolCache instanceof Pages) {
            return self::$searchPoolCache;
        }

        $defaults = $this->sheetDefaults();
        $fallback = $defaults['fallback'];

        // helper locali (replicano logica children, ma senza limit/filtri)
        $firstNonEmpty = function (...$vals) {
            foreach ($vals as $v) {
                if (isset($v) && trim((string)$v) !== '') return $v;
            }
            return null;
        };

        $allItems = [];
        $dateAlias = $this->rolesIndex['date'] ?? null;
        $stickyYear = (int)date('Y');

        foreach ($this->parseCsvRows() as $assoc) {
            $rawDateOriginal = $dateAlias ? ($assoc[$dateAlias] ?? '') : (string)$this->fieldByRole($assoc, 'date');
            if (preg_match('/\b(\d{4})\b/', $rawDateOriginal, $matches)) {
                $stickyYear = (int)$matches[1];
            }

            $base = $firstNonEmpty(
                $assoc['slug']  ?? null,
                $assoc['titolo']?? $assoc['title'] ?? null,
                $assoc['nodo']  ?? null,
                $assoc['name']  ?? null
            );
            if ($base === null) $base = substr(md5(json_encode($assoc)), 0, 10);
            $baseSlug = Str::slug((string)$base);

            $dates = $this->splitDates($rawDateOriginal);
            if (empty($dates)) $dates = [$rawDateOriginal];

            foreach ($dates as $index => $d) {
                $slug = $baseSlug;
                if (count($dates) > 1) {
                    $dateTs = $this->parseToTimestamp($d, $stickyYear);
                    $dateSlugPart = $dateTs > 0 ? date('Ymd', $dateTs) : 'occurrence-' . ($index + 1);
                    $slug .= '-' . $dateSlugPart;
                }

                $content = $assoc;
                foreach ($content as $k => $v) {
                    if (trim((string)$v) === '') $content[$k] = $fallback;
                }
                if ($dateAlias) $content[$dateAlias] = $d;

                $tpl = $content['template'] ?? 'calendar-item-from-csv';
                $allItems[] = [
                    'slug'     => $slug,
                    'num'      => 0,
                    'template' => $tpl,
                    'model'    => $tpl,
                    'content'  => $content,
                ];
            }
        }

        self::$searchPoolCache = empty($allItems) ? new Pages([]) : Pages::factory($allItems, $this);
        return self::$searchPoolCache;
    }
}
