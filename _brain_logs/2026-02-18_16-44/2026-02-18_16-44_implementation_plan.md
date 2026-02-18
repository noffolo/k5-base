# Implementazione Sezione "Logs" nel Panel

Introduzione di una nuova area nel pannello di controllo per monitorare le modifiche alle pagine (creazione, modifica, eliminazione, cambio stato/titolo/slug), con possibilità di svuotare lo storico.

## Proposta di Modifiche

### Plugin `panel-logs` [NEW]
Creeremo un nuovo plugin locale in `site/plugins/panel-logs` per gestire tutta la logica.

#### [NEW] `index.php` (file:///Users/ff3300/Desktop/SITI/k5-base/site/plugins/panel-logs/index.php)
- Registrazione degli **hooks** per intercettare:
  - `page.create:after`
  - `page.update:after`
  - `page.delete:after`
  - `page.changeStatus:after`
  - `page.changeTitle:after`
  - `page.changeSlug:after`
- Registrazione dell'**area** "logs" (Label: "Logs attività") per la sidebar.
- Definizione di una rotta API per lo svuotamento dei log.
- Funzione helper per scrivere i log in `site/logs/panel_changes.json`.

### Archiviazione Log
- I log saranno salvati in formato JSON in `site/logs/panel_changes.json`.
- Ogni voce conterrà: `date`, `user`, `action`, `page_title`, `page_id`, `parent_title`.

## Raffinamento UI e CSS (Nativo)
- **Layout Panel**: Rimozione di `<k-inside>` (che sembra causare conflitti o sovrapposizioni) e utilizzo di `<k-view>` come root. Verificheremo se il Panel avvolge automaticamente l'Area.
- **Tabella Logs**: Correzione del formato dei dati per `<k-table>` e rimozione di CSS personalizzato che causa spazi bianchi eccessivi.
- **Integrazione**: Assicureremo che il caricamento avvenga tramite le rotte standard del Panel per preservare la shell (sidebar).

## Piano di Verifica

### Test Automatizzati
Non sono previsti test automatizzati essendo una modifica prevalentemente di UI/Hooks del Panel, ma verificheremo tramite logs il corretto funzionamento delle chiamate.

### Verifica Manuale
1. **Logging**:
   - Effettuare una modifica a una pagina esistente (es. cambiare un testo).
   - Creare una nuova pagina.
   - Cambiare lo stato di una pagina.
   - Verificare che il file `site/logs/panel_changes.json` si aggiorni correttamente.
2. **Visualizzazione**:
   - Accedere alla nuova sezione "Logs" nella sidebar.
   - Verificare che la tabella mostri correttamente i dati (Chi, Cosa, Quando, Dove).
3. **Svuotamento**:
   - Cliccare sul pulsante "Svuota Logs".
   - Verificare che la tabella si svuoti e il file JSON venga resettato.
4. **Permessi**:
   - Verificare che la sezione sia visibile solo agli admin (se possibile testare con un utente editor se configurato).
