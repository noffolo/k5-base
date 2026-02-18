# Implementation Plan - Kirby CMS Update to v5.2.3

Update the project from Kirby 5.1 to Kirby 5.2.3 (latest stable version) and update all Composer-managed and manual plugins. The update aims to maintain all custom logic while benefiting from security fixes and performance improvements.

## Proposed Changes

### [Kirby Core & Dependencies]

- **[MODIFY] [composer.json](file:///Users/ff3300/Desktop/SITI/k5-spazio13/composer.json)**: The current version constraint `^5.1` allows update to `5.2.3`. I will run `composer update getkirby/cms` to perform the update.

### [Plugins]

- **[MODIFY] [site/plugins/](file:///Users/ff3300/Desktop/SITI/k5-spazio13/site/plugins/)**: I will check and update manual plugins if updates are available.
  - `kirby-form-block-suite`: Verify if a version compatible with v5.2 is available and update.
  - `locator`: Managed by Composer, will be updated via `composer update sylvainjule/locator`.

---

## Verification Plan

### Automated Tests
- No existing automated tests were found in the project root or standard locations.

### Manual Verification
1. **Kirby Panel**:
   - Access the Panel and verify that all sections load correctly.
   - Check the `non-deterministic-cms` dashboard if it exists.
   - Verify that form management still works.
2. **Frontend Logic**:
   - **Calendar**: Verify that `/calendar-from-csv` (or the respective URL) correctly parses and displays events from the CSV source.
   - **Sedi**: Verify that the map and list of locations are correctly generated.
   - **Form submission**: Test a form submission to ensure `kirby-form-block-suite` is functional.
3. **Logs**:
   - Monitor `error_log` for any PHP warnings or errors after the update.
