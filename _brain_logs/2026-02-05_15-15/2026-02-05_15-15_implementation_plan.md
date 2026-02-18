# Proposal for Project Improvements

This document outlines a series of interventions aimed at optimizing the "k5-base" boilerplate for performance, maintainability, and reusability.

## Proposed Changes

### ⚡ Rapidità di Caricamento (Loading Speed)

| Intervento | Causa | Risultato Atteso |
| :--- | :--- | :--- |
| **Bundling Asset Locali** | Dipendenze come Leaflet e Swiper sono caricate da CDN esterni in `header.php`. | Riduzione DNS lookup, maggiore affidabilità (niente downtime esterni), caricamento ottimizzato via Vite. |
| **Asset Hashing Automatico** | Attualmente il CSS è richiamato via URL statico (`css.css`). | Risoluzione definitiva dei problemi di cache per gli utenti (cache-busting perfetto ad ogni build). |
| **Ottimizzazione Immagini** | Utilizzo di `thumb()` con formati WebP/AVIF nel template. | Riduzione del peso delle immagini (LCP) fino al 60%, migliorando il punteggio Core Web Vitals. |

---

### 🧱 Riusabilità e Mantenibilità (Code Quality)

| Intervento | Causa | Risultato Atteso |
| :--- | :--- | :--- |
| **Standardizzazione Trait** | Alcune logiche di controllo (scadenze, layout) sono negli snippet. | Centralizzazione in `PageLogicTrait`, rendendo i template puramente visuali e la logica testabile. |
| **Blueprint Inheritance** | Molti YAML definiscono Tab SEO e Config simili. | Utilizzo massiccio di `extends` per gestire globalmente i metadati e le opzioni di sistema. |
| **Test Automatizzati** | Assenza di suite di test per le logiche di importazione CSV. | Prevenzione di regressioni durante l'aggiornamento dei plugin o del core di Kirby. |

---

### 🧹 Minimalismo (Clean Code)

| Intervento | Causa | Risultato Atteso |
| :--- | :--- | :--- |
| **Dependency Audit** | `package.json` contiene dipendenze forse ereditate e non usate. | Codebase più snella, build più veloci e riduzione di potenziali vulnerabilità. |
| **Console log cleanup** | Possibili residui di debug in script JS compilati. | Frontend più pulito e professionale. |

---

### 📁 Predisposizione per Progetti Diversi (Boilerplate)

| Intervento | Causa | Risultato Atteso |
| :--- | :--- | :--- |
| **Supporto .env** | Configurazioni specifiche (DB, API, Debug) sono nel `config.php`. | Setup di nuovi progetti in pochi secondi senza toccare il codice core. |
| **Janitor CLI** | Azioni ripetitive (pulizia cache, sync dati) fatte manualmente. | Automazione di task amministrativi direttamente dal Panel o terminale. |

---

### 🔍 Miglioramento SEO

| Intervento | Causa | Risultato Atteso |
| :--- | :--- | :--- |
| **Sitemap Dinamica** | Attualmente non automatizzata per i figli virtuali o CSV. | Indicizzazione completa e automatica di tutti i contenuti generati dinamicamente. |
| **Schema.org Breadcrumbs** | Mancano dati strutturati per la gerarchia della pagina. | Migliore visualizzazione nei risultati di Google (Rich Snippets). |
| **Auto-Keywords** | Generazione automatica di keyword dai blocchi di testo se vuote. | SEO "passiva" efficace anche con data entry minimo. |

## Verification Plan

### Automated Tests
1. **Performance Audit**: Run Lighthouse/PageSpeed via CLI to compare Before/After metrics for LCP and FCP.
2. **Link Checker**: Verify that all local assets (Leaflet/Swiper) load correctly after bundling.
3. **Sitemap Validation**: Use `xmllint` or online validators to check the generated sitemap.

### Manual Verification
1. **Cache Busting**: Update a CSS file, build, and verify that the browser loads the new version without hard refresh.
2. **SEO Check**: Inspect the `<head>` of various page types (standard, collection, virtual) for consistent JSON-LD.
3. **Panel UX**: Verify that the "extends" in blueprints don't break the existing tab structure.
