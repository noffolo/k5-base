# Optimize Website based on Lighthouse Audit

Optimize the website's accessibility and performance based on the provided audit report.

## User Review Required

> [!IMPORTANT]
> The changes involve removing hardcoded CDN links for Mapbox and Swiper in favor of the existing Vite-managed dynamic imports. This ensures libraries are only loaded when needed.

## Proposed Changes

### Accessibility Landmarks
Add the `<main>` tag to ensure correct document structure for screen readers.

#### [MODIFY] [default.php](file:///Users/ff3300/Desktop/SITI/k5-base/site/templates/default.php)
- Wrap content between menu and footer snippets in `<main id="main" class="main">`.

#### [MODIFY] [calendar-from-csv.php](file:///Users/ff3300/Desktop/SITI/k5-base/site/templates/calendar-from-csv.php)
- Update `<main class="wrap">` to `<main id="main" class="main wrap">` for consistency.

#### [MODIFY] [search.php](file:///Users/ff3300/Desktop/SITI/k5-base/site/templates/search.php)
- Wrap content between menu and footer snippets in `<main id="main" class="main">`.

#### [MODIFY] [sede.php](file:///Users/ff3300/Desktop/SITI/k5-base/site/templates/sede.php)
- Wrap content between menu and footer snippets in `<main id="main" class="main">`.

#### [MODIFY] [sedi.php](file:///Users/ff3300/Desktop/SITI/k5-base/site/templates/sedi.php)
- Wrap content between menu and footer snippets in `<main id="main" class="main">`.

---

### Asset Optimization

#### [MODIFY] [sedi.php](file:///Users/ff3300/Desktop/SITI/k5-base/site/templates/sedi.php)
- Remove hardcoded Mapbox CSS/JS links (lines 13-14). The site already dynamic loads these via `scripts.js`.

#### [MODIFY] [map.php](file:///Users/ff3300/Desktop/SITI/k5-base/site/plugins/block-factory/snippets/blocks/map.php)
- Remove hardcoded Leaflet and Mapbox CSS/JS links (lines 44-47).

#### [MODIFY] [scripts.js](file:///Users/ff3300/Desktop/SITI/k5-base/assets/src/js/scripts.js)
- Move jQuery initialization into a dynamic bucket if possible, or ensure it's not blocking. Actually, it's used for mobile nav so it must stay for now, but ensure it's not duplicated.

## Verification Plan

### Automated Tests
- Run `npm run build` to verify there are no compilation errors after removing hardcoded links.
- Verify that assets are correctly linked in the generated HTML via `view_file` on a sample rendered page (if possible, otherwise manual).

### Manual Verification
- **Accessibility**: Inspect the DOM using a browser console to ensure the `<main>` landmark is present on all relevant pages (`/`, `/search`, `/sedi`, `/sedi/sede-name`).
- **Performance**: Use the browser network tab to ensure Mapbox and Swiper are ONLY loaded when a map or slider is present on the page.
- **Functionality**:
    - Verify the mobile menu still works (requires jQuery).
    - Verify that maps still load on the "Sedi" and "Sede" pages.
    - Verify that sliders still work on pages with carousel components.
