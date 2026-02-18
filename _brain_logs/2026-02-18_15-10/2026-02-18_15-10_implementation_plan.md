# Implement Custom Roles and Permissions

The goal is to allow administrators to assign specific editing permissions to users. An admin can select which pages and "collections" (parent pages including children) a user can edit.

## Proposed Changes

### [Component] User Blueprints

#### [NEW] [editor.yml](file:///Users/ff3300/Desktop/SITI/k5-base/site/blueprints/users/editor.yml)
Create a new user role "editor" with fields to manage their permissions.

- Add a `permissions` tab (visible only to admins if possible, otherwise we will rely on admin-only editing of user profiles).
- Field `allowed_pages`: List of specific pages the user can edit.
- Field `allowed_collections`: List of parent pages where the user can edit everything inside.

#### [MODIFY] [default.yml](file:///Users/ff3300/Desktop/SITI/k5-base/site/blueprints/users/default.yml)
Ensure the default blueprint is clean or add common fields.

---

### [Component] Permissions Plugin

#### [NEW] [index.php](file:///Users/ff3300/Desktop/SITI/k5-base/site/plugins/custom-permissions/index.php)
Create a plugin to handle the permission logic.

- **User Method**: `hasPagePermission($page)`
  - Returns `true` if user is admin.
  - Returns `true` if `$page->uuid()` is in `allowed_pages`.
  - Returns `true` if any parent of `$page` (including self) has UUID in `allowed_collections`.
  - Returns `false` otherwise.
- **Hooks**:
  - `page.update:before`
  - `page.delete:before`
  - `page.create:before`
  - `page.changeStatus:before`
  - `page.changeSlug:before`
  - `page.changeTitle:before`
  - Each hook will check `user()->hasPagePermission($page)` and throw a `PermissionsException` if false.

## Verification Plan

### Automated Tests
- I will attempt to run `composer test` if available, but since this is a CMS setup, I'll focus on manual verification.

### Manual Verification
1. **Admin Setup**:
   - Create a new user with the `editor` role.
   - Assign specific pages (e.g., "Contatti") to this user.
   - Assign a "collection" (e.g., "Progetti") to this user.
2. **Editor Testing**:
   - Log in as the new editor.
   - Try to edit "Contatti" -> Should work.
   - Try to edit a page inside "Progetti" -> Should work.
   - Try to edit "Home" (not assigned) -> Should fail with an error message in the Panel.
   - Try to delete a page not assigned -> Should fail.
3. **Admin Exclusivity**:
   - Verify that the editor cannot change their own permissions (only admin can edit users).
