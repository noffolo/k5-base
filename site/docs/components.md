# Documentazione Progetto Spazio13

## Architettura
Il progetto utilizza Kirby CMS con un setup standardizzato per garantire riusabilità e manutenibilità.

### Core Plugin (`site/plugins/non-deterministic-cms`)
Il "motore" del sito. Centralizza la logica condivisa, helper e estensioni del core.
- **Helper (`NonDeterministic\Helpers\CollectionHelper`):** Gestione collezioni, mappe, marker, scadenze e dati form.
- **Trait (`NonDeterministic\Models\PageLogicTrait`):** Fornisce metodi SEO e Layout pronti all'uso per i modelli di pagina.

### Modelli di Pagina (`site/models`)
Tutte le pagine estendono `DefaultPage` (o ne usano il trait via plugin) che fornisce:
- `$page->seoTitle()`: Titolo ottimizzato.
- `$page->seoDescription()`: Meta description pulita.
- `$page->layouts()`: Gestione dei layout Kirby.
- `$page->formData()`: Integrazione con i contatori dei form.

> [!NOTE]
> I controller ora utilizzano il `CollectionHelper` per filtrare eventi e generare mappe, eliminando la necessità di helper locali sparsi.

> [!TIP]
> Per futuri progetti, puoi copiare la cartella `site/plugins/non-deterministic-cms` e il modello `DefaultPage` per avere una base di partenza già ottimizzata.

## Componenti (Snippet)

### Layout (`site/snippets/layouts.php`)
Snippet principale per renderizzare i layout di Kirby.
- **Supporto Anchor:** Genera automaticamente la navigazione se gli anchor sono attivi.
- **Sticky Columns:** Supporta colonne sticky con offset configurabile.
- **Scadenza:** Nasconde automaticamente i blocchi se la pagina è scaduta o se i posti sono esauriti.

### Header (`site/snippets/header.php`)
Gestisce i meta tag e il caricamento degli asset (Vite).
- **SEO Dinamico:** Utilizza i metodi del modello per generare meta tag consistenti.

## SCSS & Design System
I file sono organizzati in `assets/src/sass/theme`:
- `settings/`: Variabili, colori e tipografia.
- `components/`: Stili atomici per i vari snippet (BEM).
- `base/`: Reset e stili globali.

## Pannello (UX)
Le blueprint sono organizzate per ridurre il carico cognitivo:
- **Tab Standard:** `Contenuto`, `SEO`, `Configurazione`.
- **Feedback Visivo:** Utilizzo di icone, help text e toggle per guidare l'utente.
