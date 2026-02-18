# Proposal for Frontend Refactoring (Phase 8)

This phase aims to improve code quality, maintainability, and visual performance by cleaning up the frontend layer.

## Proposed Changes

### 🎨 SCSS Reorganization
- **Modular Structure**: Move layout-specific files (header, footer) from `components/` to `layout/`.
- **Logic Separation**: Move inline styles from PHP snippets into dedicated SCSS component files.
- **Consistency**: Audit variables usage and ensure a clean `@use`/`@forward` hierarchy.

### 🧱 Snippet Refactoring
- **[MODIFY] [calendar-item-from-csv.php](file:///Users/ff3300/Desktop/SITI/k5-base/site/snippets/calendar-item-from-csv.php)**: Remove all inline styles and move them to `_calendar-item.scss`.
- **[MODIFY] [layouts.php](file:///Users/ff3300/Desktop/SITI/k5-base/site/snippets/layouts.php)**: Extract anchor and row styles into `_layouts.scss`.
- **[MODIFY] [image.php](file:///Users/ff3300/Desktop/SITI/k5-base/site/snippets/image.php)**: Clean up dynamic styles and move static ones to CSS.

### 🧹 Minimalism & Elegance
- Eliminate redundant CSS rules.
- Improve PHP snippet readability by reducing nested logic and using helper methods from `PageLogicTrait`.

## Verification Plan

### Manual Verification
1. **Visual Consistency**: Compare pages before and after refactoring to ensure no breaking changes in layout.
2. **Bundle Size**: Verify that moving styles from inline to the main bundle doesn't negatively impact performance (thanks to PurgeCSS).
3. **Code Quality**: Peer review of the new SCSS structure.
