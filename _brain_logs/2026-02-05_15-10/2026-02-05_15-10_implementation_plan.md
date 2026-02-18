# Renaming Project to k5-base

This plan outlines the steps to remove references to "Spazio13" in configuration files and transform this instance into a "k5-base".

## User Review Required

> [!IMPORTANT]
> This will remove the link to the `FF3300-studio/k5-spazio13` GitHub repository. You will need to add a new remote manually if you want to link it to a new repository.

> [!NOTE]
> As requested, I will **not** touch the `content` folder and I will **not** delete `_brain_logs`.
>
> I will create a backup of the folder `/Users/ff3300/Desktop/SITI/k5-spazio13` as `/Users/ff3300/Desktop/SITI/k5-spazio13-backup` before proceeding.

## Proposed Changes

### Backup

- Create a full copy of the current project directory to `../k5-spazio13-backup`.

### Metadata and Configuration

#### [MODIFY] [composer.json](file:///Users/ff3300/Desktop/SITI/k5-spazio13/composer.json)
- Rename project from `ff3300/k5-spazio13` to `ff3300/k5-base`.

#### [MODIFY] [package.json](file:///Users/ff3300/Desktop/SITI/k5-spazio13/package.json)
- Rename project from `k5_spazio13` to `k5_base`.

### Git Environment

- Remove the `origin` remote pointing to `https://github.com/FF3300-studio/k5-spazio13.git`.

## Verification Plan

### Automated Tests
- Run `grep -ri "spazio13" .` (excluding `vendor`, `content`, `_brain_logs`, and `.git`) to ensure no other references remain.

### Manual Verification
- Verify the project name in `composer.json` and `package.json`.
- Run `git remote -v` to confirm `origin` is removed.
