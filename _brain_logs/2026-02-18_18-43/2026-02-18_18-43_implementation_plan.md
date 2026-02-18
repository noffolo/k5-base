# Fix Panel Interface for Logs View

The goal is to ensure the Panel shell (sidebar, header, breadcrumbs) is correctly rendered for the "Logs attività" area.

## Current Issue
The logs are recorded and displayed, but the view lacks the standard Kirby Panel shell.

## Proposed Changes

### Research & Diagnosis
1. **Compare with Core**: Look at how Kirby core defines areas (e.g., users, system) to see if `layout: inside` is sufficient or if the structure of the returned array in `action` needs adjustment.
2. **Check Vue Component**: Verify if `k-view` with `layout="inside"` is the correct way to declare the component, or if Kirby handles it based on the PHP registration.

### [MODIFY] [index.php](file:///Users/ff3300/Desktop/SITI/k5-base/site/plugins/panel-logs/index.php)
- Remove redundant `'layout' => 'inside'` from the area and view action roots.
- Move `'layout' => 'inside'` into the `props` array if necessary, though `index.js` already has it on `<k-view>`.
- Ensure the `link` and `pattern` are consistent.

### [MODIFY] [index.js](file:///Users/ff3300/Desktop/SITI/k5-base/site/plugins/panel-logs/index.js) (Optional)
- Verify if `k-view` needs anything else to trigger the shell rendering.

## Verification Plan

### Manual Verification
- Navigate to `/panel/panel-logs` and confirm the sidebar and header are present.
- Verify breadcrumbs work correctly.
