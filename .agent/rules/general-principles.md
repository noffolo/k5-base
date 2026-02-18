# General Principles

Questi principi guidano lo sviluppo e la manutenzione del progetto.

## Codice ed Efficienza
- **DRY (Don't Repeat Yourself)**: Evitare sempre la ripetizione di codice. Estrarre la logica comune in helper o trait.
- **Riutilizzo Snippet**: Preferire l'uso di snippet esistenti, rendendoli parametrici (tramite variabili passate al template) per adattarli a casi d'uso simili.
- **Eleganza e Performance**: Dare priorità a soluzioni che siano sia performanti che "pulite" nella logica. Il codice deve essere leggibile e riflettere un pensiero ordinato.
- **Context-Aware**: Ogni soluzione proposta deve essere contestualizzata rispetto alle finalità e all'architettura specifica di questo progetto.

## Architettura del Progetto
- **Approccio Non Deterministico**: Assecondare e supportare la filosofia "non-determinista" del CMS (flessibilità dei contenuti, mappatura dinamica, etc.).
- **Manutenzione Documentazione**: Ogni aggiornamento significativo (refactoring, nuove funzionalità core, cambi di struttura) deve essere riflesso nel `README.md`.

---
*Ultimo aggiornamento: 2026-01-22 14:56*
