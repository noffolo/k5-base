# Lighthouse Optimization Plan

Address performance, accessibility, and best practice issues identified in the Lighthouse report.

## Proposed Changes

### Assets & Swiper Optimization
#### [MODIFY] [scripts.js](file:///Users/ff3300/Desktop/SITI/k5-base/assets/src/js/scripts.js)
- Unify Swiper initialization logic.
- Support generic `.swiper` containers (used by `slider.php` block).
- Optimize Swiper imports to reduce bundle size if possible.
- Remove redundant skeleton logic if not needed.

#### [MODIFY] [slider.php](file:///Users/ff3300/Desktop/SITI/k5-base/site/plugins/block-factory/snippets/blocks/slider.php)
- Remove inline `<script>` tags that cause `ReferenceError: Swiper is not defined`.
- Ensure the `.swiper` class is present for discovery by `scripts.js`.

### Performance & LCP
#### [MODIFY] [header.php](file:///Users/ff3300/Desktop/SITI/k5-base/site/snippets/header.php)
- Add a dynamic preload hint for the LCP image if a slider exists on the page.

#### [MODIFY] [image.php](file:///Users/ff3300/Desktop/SITI/k5-base/site/snippets/image.php)
- Add support for `fetchpriority="high"` and disabling lazy loading for above-the-fold images.

### Accessibility & Structure
#### [MODIFY] [menu.php](file:///Users/ff3300/Desktop/SITI/k5-base/site/snippets/menu.php)
- Ensure the `<nav>` landmark is used correctly.
- Add `<header role="banner">` wrapper around the navigation.

#### [MODIFY] [layouts.php](file:///Users/ff3300/Desktop/SITI/k5-base/site/snippets/layouts.php)
- Check and fix heading levels within layout blocks to ensure sequential descending order.

#### [MODIFY] [block-slide-info.php](file:///Users/ff3300/Desktop/SITI/k5-base/site/snippets/block-slide-info.php)
- Change `<h2>` to `<h3>` or `<div>` with appropriate styling to fix heading hierarchy in sliders.

## Verification Plan

### Automated Tests
- Run `npm run build` to ensure assets compile correctly.
- Check browser console for `ReferenceError` using the browser subagent.

### Manual Verification
- Verify that sliders still work correctly.
- Check the generated HTML for the `<main>` landmark and heading hierarchy.
- Confirm contrast improvements in the browser.
