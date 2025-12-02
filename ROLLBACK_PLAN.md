# Rollback Plan: Markdown-Based Pages & Posts Refactor

**Version:** 1.0
**Date:** November 28, 2025
**Branch:** `update_pages_and_post_editor`

---

## Overview

This document provides a comprehensive rollback plan for the markdown-based pages and posts refactor. If issues arise after deployment, follow these steps to safely revert to the legacy page builder system.

---

## Table of Contents

1. [Quick Rollback](#quick-rollback)
2. [Detailed Rollback Steps](#detailed-rollback-steps)
3. [Data Migration Rollback](#data-migration-rollback)
4. [Verification Steps](#verification-steps)
5. [Known Issues & Workarounds](#known-issues--workarounds)

---

## Quick Rollback

### Emergency Rollback (< 5 minutes)

If you need to rollback immediately:

```bash
# 1. Switch to previous git commit/tag
git checkout <previous-stable-tag>

# 2. Restore database from backup
./vendor/bin/sail artisan db:restore --backup=<backup-name>

# 3. Rebuild frontend assets
npm run build

# 4. Clear all caches
./vendor/bin/sail artisan optimize:clear
```

---

## Detailed Rollback Steps

### Step 1: Database Backup

**BEFORE starting rollback**, ensure you have a recent backup:

```bash
# Create a backup of current state
./vendor/bin/sail artisan db:backup --name=before-rollback-$(date +%Y%m%d-%H%M%S)
```

### Step 2: Revert Database Migrations

Rollback the migrations that were added for markdown support:

```bash
# Rollback the drop legacy tables migration
./vendor/bin/sail artisan migrate:rollback --step=1

# This will restore:
# - pagebuilder_blocks table
# - pagebuilder_layouts table
# - layout_id column in pages table
# - data column in pages table
# - content_type column in pages table
```

### Step 3: Restore Legacy Files

**Option A: Git Revert (Recommended)**

```bash
# Revert to commit before the refactor
git log --oneline | grep "Markdown"  # Find the commit hash
git revert <commit-hash> --no-commit

# Review changes
git status
git diff

# Commit the revert
git commit -m "Rollback: Revert markdown-based pages refactor"
```

**Option B: Restore Specific Files**

If you need to selectively restore files:

```bash
# Checkout legacy files from a previous commit
git checkout <previous-commit> -- \
    app/Domains/PageBuilder/Http/Controllers/Admin/BlockController.php \
    app/Domains/PageBuilder/Http/Controllers/Admin/LayoutController.php \
    app/Domains/PageBuilder/Http/Controllers/Admin/PageBuilderController.php \
    app/Domains/PageBuilder/Services/PageBuilderService.php \
    app/Domains/PageBuilder/Services/PageBuilderServiceContract.php \
    app/Domains/PageBuilder/Models/Block.php \
    app/Domains/PageBuilder/Models/Layout.php \
    app/Domains/PageBuilder/PageBuilderFacade.php
```

### Step 4: Restore Vue Components

```bash
# Restore legacy Vue components
git checkout <previous-commit> -- \
    resources/js/components/pagebuilder/ \
    resources/js/pages/Admin/PageBuilder/Blocks/ \
    resources/js/pages/Admin/PageBuilder/Layouts/ \
    resources/js/pages/Admin/PageBuilder/Create.vue \
    resources/js/pages/Admin/PageBuilder/Index.vue \
    resources/js/pages/Admin/PageBuilder/Edit.vue \
    resources/js/composables/usePageBuilderApi.ts \
    resources/js/routes/admin/page-builder/blocks/ \
    resources/js/routes/admin/page-builder/layouts/ \
    resources/js/actions/App/Domains/PageBuilder/
```

### Step 5: Restore Routes

```bash
# Restore legacy routes
git checkout <previous-commit> -- routes/admin/pagebuilder.php
```

### Step 6: Update Service Provider

```bash
# Restore PageBuilderServiceProvider
git checkout <previous-commit> -- app/Domains/PageBuilder/PageBuilderServiceProvider.php
```

### Step 7: Restore Database Seeders

```bash
# Restore legacy seeders
git checkout <previous-commit> -- \
    database/seeders/PageBuilderSeeder.php \
    database/seeders/PageBuilderBlocksSeeder.php
```

### Step 8: Rebuild Frontend

```bash
# Install any missing dependencies
npm install

# Rebuild frontend assets
npm run build
```

### Step 9: Clear Caches

```bash
# Clear all application caches
./vendor/bin/sail artisan optimize:clear
./vendor/bin/sail composer dump-autoload
```

### Step 10: Restore Posts Editor

If posts were migrated to use MarkdownPageEditor:

```bash
# Restore legacy posts editor
git checkout <previous-commit> -- \
    resources/js/pages/admin/content/posts/Create.vue \
    resources/js/pages/admin/content/posts/Edit.vue
```

---

## Data Migration Rollback

### Reverting Migrated Pages

If pages were migrated from legacy builder to markdown:

```bash
# Run the reverse migration command (if implemented)
./vendor/bin/sail artisan page:revert-from-markdown

# Or manually via SQL (with caution!)
```

**Manual SQL Rollback Example:**

```sql
-- WARNING: This will delete markdown content!
-- Only run if you have backups!

-- Restore content_type to 'legacy'
UPDATE pagebuilder_pages
SET content_type = 'legacy'
WHERE content_type = 'markdown';

-- Clear markdown fields
UPDATE pagebuilder_pages
SET markdown_content = NULL,
    layout_template = NULL
WHERE content_type = 'legacy';

-- Note: You'll need to restore the 'data' field from backups
-- as it was likely cleared during migration
```

### Restoring from Database Backup

If you have a database backup from before the migration:

```bash
# Option 1: Full restore (will lose all data since backup)
./vendor/bin/sail artisan db:restore --backup=<backup-name>

# Option 2: Selective table restore (MySQL example)
mysql -u root -p contenta < backup_pagebuilder_pages.sql
mysql -u root -p contenta < backup_pagebuilder_blocks.sql
mysql -u root -p contenta < backup_pagebuilder_layouts.sql
```

---

## Verification Steps

After completing the rollback, verify everything works:

### 1. Check Database Schema

```bash
./vendor/bin/sail artisan migrate:status

# Verify these tables exist:
# - pagebuilder_blocks
# - pagebuilder_layouts
# - pagebuilder_pages (with layout_id and data columns)
```

### 2. Test Page Builder UI

1. Navigate to `/admin/page-builder`
2. Verify you can see the page list
3. Click "Create Page"
4. Verify the legacy drag-and-drop builder loads
5. Add a block and verify it works
6. Save and verify the page is created

### 3. Test Blocks Management

1. Navigate to `/admin/page-builder/blocks`
2. Verify blocks list loads
3. Try creating a new block
4. Verify block appears in the builder

### 4. Test Layouts Management

1. Navigate to `/admin/page-builder/layouts`
2. Verify layouts list loads
3. Try creating a new layout

### 5. Test Posts Editor

1. Navigate to `/admin/posts/create`
2. Verify the editor loads correctly
3. Check if it's using MdEditor or MarkdownPageEditor
4. Create a test post

### 6. Check Frontend

1. Visit a public page
2. Verify it renders correctly
3. Check browser console for errors

---

## Known Issues & Workarounds

### Issue 1: Missing Blocks After Rollback

**Symptom:** Block library is empty after rollback

**Solution:**
```bash
# Re-seed blocks
./vendor/bin/sail artisan db:seed --class=PageBuilderBlocksSeeder
```

### Issue 2: Pages Show as Blank

**Symptom:** Existing pages appear blank in the builder

**Cause:** The `data` column was cleared during migration

**Solution:** Restore from database backup or manually reconstruct pages

### Issue 3: Layout Dropdown Empty

**Symptom:** No layouts available when creating pages

**Solution:**
```bash
# Re-seed layouts
./vendor/bin/sail artisan db:seed --class=PageBuilderSeeder
```

### Issue 4: Frontend Assets Not Loading

**Symptom:** 404 errors for JS/CSS files

**Solution:**
```bash
# Rebuild assets
npm run build

# Check if public/build directory exists
ls -la public/build/

# If missing, run build again or check Vite config
```

### Issue 5: Service Container Errors

**Symptom:** "Target class [PageBuilderServiceContract] does not exist"

**Cause:** ServiceProvider not properly restored

**Solution:**
```bash
# Verify PageBuilderServiceProvider is registered
grep "PageBuilderServiceProvider" bootstrap/providers.php

# Clear config cache
./vendor/bin/sail artisan config:clear
./vendor/bin/sail composer dump-autoload
```

---

## Partial Rollback Scenarios

### Scenario 1: Keep Markdown for Posts, Rollback Pages

If you want to keep markdown editor for posts but rollback pages:

```bash
# Rollback only page-related migrations
./vendor/bin/sail artisan migrate:rollback --step=1

# Restore only page builder components
git checkout <previous-commit> -- \
    resources/js/pages/Admin/PageBuilder/ \
    app/Domains/PageBuilder/Http/Controllers/Admin/

# Keep post editor as-is (don't restore)
```

### Scenario 2: Rollback Frontend Only

If the backend works but frontend has issues:

```bash
# Restore Vue components
git checkout <previous-commit> -- resources/js/

# Rebuild
npm run build
```

### Scenario 3: Rollback Backend Only

If frontend works but backend has issues:

```bash
# Rollback migrations
./vendor/bin/sail artisan migrate:rollback --step=1

# Restore backend files
git checkout <previous-commit> -- \
    app/Domains/PageBuilder/ \
    routes/admin/pagebuilder.php
```

---

## Post-Rollback Cleanup

After successful rollback:

1. **Remove Markdown-Specific Files:**
   ```bash
   # Remove markdown-specific files that aren't needed
   rm -f SHORTCUT_SYNTAX_SPEC.md
   rm -f MIGRATION_EXAMPLE.md
   rm -f documentation/MARKDOWN_EDITOR_GUIDE.md
   rm -rf app/Domains/ContentManagement/Services/ShortcodeParser/
   ```

2. **Update Documentation:**
   ```bash
   # Restore old section in USER_MANUAL.md
   git checkout <previous-commit> -- documentation/USER_MANUAL.md
   ```

3. **Clear Unused Migrations:**
   ```bash
   # Remove markdown migration files
   rm database/migrations/*_add_markdown_support_*.php
   rm database/migrations/*_drop_legacy_pagebuilder_tables.php
   ```

---

## Prevention & Best Practices

### Before Future Migrations

1. **Always backup before major changes:**
   ```bash
   ./vendor/bin/sail artisan db:backup --name=before-<feature-name>
   ```

2. **Test in staging first:**
   - Deploy to staging environment
   - Run full test suite
   - Manually test all features
   - Let QA team verify
   - Only then deploy to production

3. **Create a data export:**
   ```bash
   # Export current page data
   ./vendor/bin/sail artisan page:export --output=backup/pages.json
   ```

4. **Feature flags:**
   Consider using feature flags for major refactors:
   ```php
   if (config('features.markdown_editor')) {
       // Use new markdown editor
   } else {
       // Use legacy page builder
   }
   ```

### Monitoring After Deployment

1. **Check error logs:**
   ```bash
   tail -f storage/logs/laravel.log
   ```

2. **Monitor user complaints:**
   - Set up error tracking (Sentry, Bugsnag)
   - Monitor support tickets
   - Watch analytics for drop in page creation

3. **Database monitoring:**
   - Check for slow queries
   - Monitor database size changes
   - Watch for errors in migrations

---

## Emergency Contacts

**Development Team:**
- Lead Developer: [Name] - [Email]
- Backend Developer: [Name] - [Email]
- Frontend Developer: [Name] - [Email]

**DevOps:**
- DevOps Lead: [Name] - [Email]

**Escalation Path:**
1. Try rollback following this guide
2. Contact Lead Developer
3. If database issues, contact DevOps
4. If critical, page management team

---

## Rollback Checklist

Use this checklist when performing a rollback:

- [ ] Notified team about rollback
- [ ] Created current state database backup
- [ ] Stopped queue workers
- [ ] Put site in maintenance mode (if needed)
- [ ] Rolled back database migrations
- [ ] Restored PHP files from git
- [ ] Restored Vue components from git
- [ ] Restored routes from git
- [ ] Rebuilt frontend assets (`npm run build`)
- [ ] Cleared all caches
- [ ] Restarted queue workers
- [ ] Tested page builder UI
- [ ] Tested blocks management
- [ ] Tested layouts management
- [ ] Tested posts editor
- [ ] Checked frontend rendering
- [ ] Verified no errors in logs
- [ ] Took site out of maintenance mode
- [ ] Monitored for 30 minutes post-rollback
- [ ] Documented rollback in incident log
- [ ] Scheduled post-mortem meeting

---

## Appendix A: File Manifest

Files added/modified in this refactor (for reference):

### Added Files

**Backend:**
- `app/Domains/ContentManagement/Services/ShortcodeParser/` (entire directory)
- `app/Domains/PageBuilder/Services/MarkdownRenderService.php`
- `app/Domains/PageBuilder/Services/MarkdownRenderServiceContract.php`
- `app/Domains/PageBuilder/Console/Commands/MigratePagesToMarkdown.php`
- `database/migrations/2025_11_27_120000_add_markdown_support_to_pagebuilder_pages_table.php`
- `database/migrations/2025_11_27_120001_add_markdown_support_to_pagebuilder_page_revisions_table.php`
- `database/migrations/2025_11_28_000000_drop_legacy_pagebuilder_tables.php`

**Frontend:**
- `resources/js/components/PageBuilder/MarkdownPageEditor.vue`
- `resources/js/components/PageBuilder/ShortcodesLibrary.vue`
- `resources/views/layouts/default.blade.php`
- `resources/views/layouts/full-width.blade.php`
- `resources/views/layouts/sidebar-left.blade.php`
- `resources/views/layouts/sidebar-right.blade.php`

**Documentation:**
- `SHORTCUT_SYNTAX_SPEC.md`
- `MIGRATION_EXAMPLE.md`
- `documentation/MARKDOWN_EDITOR_GUIDE.md`
- `CLAUDE.md`
- `ROLLBACK_PLAN.md` (this file)

**Tests:**
- `tests/Unit/ShortcodeParser/TokenizerBasicTest.php`
- `tests/Unit/ShortcodeParser/TokenizerAttributesTest.php`
- `tests/Unit/ShortcodeParser/TokenizerContentTest.php`
- `tests/Unit/ShortcodeParser/TokenizerErrorsTest.php`
- `tests/Unit/ShortcodeParser/ParserBasicTest.php`
- `tests/Unit/ShortcodeParser/ParserAdvancedTest.php`
- `tests/Unit/ShortcodeParser/HtmlRendererBasicTest.php`
- `tests/Unit/ShortcodeParser/HtmlRendererBlocksTest.php`
- `tests/Unit/ShortcodeParser/ShortcodeParserServiceTest.php`

### Deleted Files

**Backend:**
- `app/Domains/PageBuilder/Http/Controllers/Admin/BlockController.php`
- `app/Domains/PageBuilder/Http/Controllers/Admin/LayoutController.php`
- `app/Domains/PageBuilder/Http/Controllers/Admin/PageBuilderController.php`
- `app/Domains/PageBuilder/Services/PageBuilderService.php`
- `app/Domains/PageBuilder/Services/PageBuilderServiceContract.php`
- `app/Domains/PageBuilder/Models/Block.php`
- `app/Domains/PageBuilder/Models/Layout.php`
- `app/Domains/PageBuilder/PageBuilderFacade.php`
- `app/Domains/PageBuilder/Tests/` (entire directory)
- `database/seeders/PageBuilderSeeder.php`
- `database/seeders/PageBuilderBlocksSeeder.php`

**Frontend:**
- `resources/js/components/pagebuilder/` (entire directory)
- `resources/js/pages/Admin/PageBuilder/Blocks/` (entire directory)
- `resources/js/pages/Admin/PageBuilder/Layouts/` (entire directory)
- `resources/js/pages/Admin/PageBuilder/Create.vue`
- `resources/js/pages/Admin/PageBuilder/Index.vue`
- `resources/js/composables/usePageBuilderApi.ts`
- `resources/js/routes/admin/page-builder/blocks/` (entire directory)
- `resources/js/routes/admin/page-builder/layouts/` (entire directory)
- `resources/js/actions/App/Domains/PageBuilder/` (entire directory)

### Modified Files

**Backend:**
- `app/Domains/PageBuilder/Http/Controllers/Admin/PageController.php`
- `app/Domains/PageBuilder/Models/Page.php`
- `app/Domains/PageBuilder/Models/PageRevision.php`
- `app/Domains/PageBuilder/PageBuilderServiceProvider.php`
- `routes/admin/pagebuilder.php`

**Frontend:**
- `resources/js/pages/Admin/PageBuilder/Edit.vue` (completely rewritten)
- `resources/js/pages/admin/content/posts/Create.vue`
- `resources/js/pages/admin/content/posts/Edit.vue`

**Documentation:**
- `documentation/USER_MANUAL.md` (Section 8 updated)

---

**Document Version:** 1.0
**Last Updated:** November 28, 2025
**Maintained By:** Development Team
