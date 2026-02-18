# Fix Panel Interface for Logs View

The goal is to ensure the Panel shell (sidebar, header, breadcrumbs) is correctly rendered for the "Logs attività" area.

## Current Issue
The logs are recorded and displayed, but the view lacks the standard Kirby Panel shell.

## Proposed Changes

### Research & Diagnosis
1. **Compare with Core**: Look at how Kirby core defines areas (e.g., users, system) to see if `layout: inside` is sufficient or if the structure of the returned array in `action` needs adjustment.
2. **Check Vue Component**: Verify if `k-view` with `layout="inside"` is the correct way to declare the component, or if Kirby handles it based on the PHP registration.

### [MODIFY] [index.php](file:///Users/ff3300/Desktop/SITI/k5-base/site/plugins/panel-logs/index.php)
- Rename the component to `k-panel-logs-view` to follow the naming convention found in the docs (`k-*-view`).

### [MODIFY] [index.js](file:///Users/ff3300/Desktop/SITI/k5-base/site/plugins/panel-logs/index.js)
- Update component registration to `k-panel-logs-view`.
- Replace `<k-view>` with `<k-panel-inside>`.
- Remove `layout="inside"` and `slot="header"` as they seem unnecessary with `<k-panel-inside>`.

## Verification Plan

### Manual Verification
- Navigate to `/panel/panel-logs` and confirm the sidebar and header are present.
- Verify breadcrumbs work correctly.
