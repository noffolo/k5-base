# Walkthrough: Lighthouse Optimizations

Applied accessibility and performance optimizations to improve the overall quality of the website's frontend.

## Changes Made

### Accessibility Landmarks
Added the `<main>` landmark to ensure screen readers can easily identify the primary content of each page.
- **Modified Templates**: `default.php`, `calendar-from-csv.php`, `calendar-item-from-csv.php`, `search.php`, `sede.php`, `sedi.php`.
- **Implementation**: Wrapped the core content areas between the site header/menu and footer snippets in `<main id="main" class="main">`.

### Performance Optimizations (Asset Loading)
Optimized how external libraries (Mapbox, Leaflet) are loaded to reduce redundant requests and minimize pre-rendering overhead.
- **Removed Hardcoded CDN Links**: Eliminated static `<link>` and `<script>` tags for Mapbox/Leaflet from `sedi.php` and the custom map block.
- **Dynamic Initialization**: Updated the map block and `sedi.php` template to wait for the `mapbox-ready` event triggered by the Vite-managed `scripts.js`. This ensures libraries are only loaded on pages that actually require them.

## Verification Results

### Build Verification
- **Command**: `npm run build`
- **Result**: Successfully compiled without errors. The build process confirmed that all local SCSS and JS assets are correctly bundled.

### Manual Inspection
- Verified that the `<main>` tag is present in the DOM for:
    - Homepage (`default.php`)
    - Search results (`search.php`)
    - Sedi overview (`sedi.php`)
    - Sede single page (`sede.php`)
    - Calendar items (`calendar-item-from-csv.php`)
- Confirmed that Mapbox/Leaflet are no longer blocking the main thread from the start if not needed.
