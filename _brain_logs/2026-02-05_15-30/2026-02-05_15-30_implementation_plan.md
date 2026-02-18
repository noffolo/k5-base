# Proposal for Advanced Project Improvements (Phase 2)

This second phase focuses on cutting asset weight, improving perceived performance, and enhancing the editorial experience in the Panel.

## Proposed Changes

### 🧪 Ottimizzazione Asset (Asset Weight)

| Intervento | Causa | Risultato Atteso |
| :--- | :--- | :--- |
| **PurgeCSS Configuration** | Il CSS attuale (~490KB) include gran parte di Bootstrap non utilizzato. | Riduzione del CSS del 70-80%, abbattendo i tempi di rendering iniziale. |
| **JS Code Splitting** | Swiper e Leaflet caricano sempre, anche su pagine statiche. | Caricamento "on-demand" delle librerie pesanti solo quando lo snippet le richiede. |

---

### ✨ UX & Perceived Performance

| Intervento | Causa | Risultato Atteso |
| :--- | :--- | :--- |
| **Skeleton Loaders** | Caricamento asincrono di mappe/calendari può creare salti di layout. | Percezione di velocità istantanea e layout "solido" durante il caricamento. |
| **View Transitions** | Navigazione tra viste collezione "secca". | Navigazione fluida e animata tra grid e mappa tramite API native. |

---

### 🛠 Backend & DX (Editorial Experience)

| Intervento | Causa | Risultato Atteso |
| :--- | :--- | :--- |
| **Panel Block Previews** | I blocchi nel Panel sono astratti, richiedono un "salva e guarda". | Anteprima visuale istantanea nel Panel per migliorare il flusso di lavoro editoriale. |
| **CSV Real-time Reporting** | Errori nei CSV remoti sono difficili da diagnosticare. | Diagnostica chiara nel Panel in caso di CSV malformati o irraggiungibili. |

## Verification Plan

### Automated Tests
1. **Bundle Analysis**: Compare CSS/JS bundle sizes before and after PurgeCSS/Splitting.
2. **Network Audit**: Verify that Leaflet is NOT loaded on the homepage (if no map is present).

### Manual Verification
1. **Lighthouse UX**: Test interaction to next paint (INP) and visual stability (CLS).
2. **Panel Usage**: Verify that block previews render correctly for basic components.
