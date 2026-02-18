# Fix Panel Logs Shell (Explicit Layout)

The previous attempt to fix the missing shell by following core "minimalist" methodology did not work. It seems that for custom areas, Kirby might need an explicit `layout` property to trigger the "inside" Panel shell (sidebar/header).

## Proposed Changes

### Panel Logs Plugin

#### [MODIFY] [index.php](file:///Users/ff3300/Desktop/SITI/k5-base/site/plugins/panel-logs/index.php)
- Add `'layout' => 'inside'` to the top-level area definition.
- Add `'layout' => 'inside'` to the view action response.
- Keep the component name as `panel-logs-view`.

#### [MODIFY] [index.js](file:///Users/ff3300/Desktop/SITI/k5-base/site/plugins/panel-logs/index.js)
- Ensure `<k-view>` has `layout="inside"`.
- (Optional) Ensure the component naming is consistent.

## Verification Plan

### Manual Verification
- Reload the Panel.
- Navigate to "Logs attività".
- Verify that the sidebar and header are NOW visible.
