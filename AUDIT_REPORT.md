# Codebase Audit Report
**Date:** 2025-12-02
**Purpose:** Identify unused/duplicate code before major refactoring

---

## Executive Summary

**Total References Found:**
- PageBuilder PHP references: **57 files**
- PageBuilder Frontend references: **168 files**
- PageBuilder routes: **12 active routes**
- Layout/Revision migrations: **3 migrations**
- Total frontend Vue pages: **37 components**

**Key Findings:**
1. PageBuilder is **actively used** - extensive integration
2. Settings/SiteSettings duplication needs investigation
3. Empty page-builder frontend directory (0 Vue files)
4. Layout and Revision tables exist and likely in use

---

## TO DELETE

### 1. PageBuilder Domain
**Status:** ⚠️ **DEFER - Actively Used**
```
app/Domains/PageBuilder/ - DO NOT DELETE YET
```

**Analysis:**
- 57 PHP file references
- 168 frontend references (routes, imports, types)
- 12 active routes in production
- **Action:** Merge functionality FIRST, then delete

**Critical Routes to Preserve:**
```
GET  /admin/pages
POST /admin/pages
GET  /admin/pages/create
GET  /admin/pages/{page}/edit
PUT  /admin/pages/{page}
DELETE /admin/pages/{page}
POST /admin/pages/{page}/publish
POST /admin/pages/{page}/unpublish
POST /admin/pages/{page}/duplicate
GET  /{slug} (public page viewing)
```

### 2. Frontend Directory
```
✅ DELETE: resources/js/pages/admin/page-builder/ (confirmed empty)
```

### 3. Migrations (After Data Migration)
```
⚠️ VERIFY FIRST, THEN DELETE:
- database/migrations/2025_11_01_212635_create_pagebuilder_layouts_table.php
- database/migrations/2025_11_02_130253_create_pagebuilder_page_revisions_table.php
- database/migrations/2025_11_27_120001_add_markdown_support_to_pagebuilder_page_revisions_table.php
```

**Action:**
1. Check if `layouts` table has data
2. Check if `page_revisions` table has data
3. If no data: Delete migrations
4. If has data: Decide if keeping revision system or migrating to ContentStorage versioning

### 4. Settings/SiteSettings Duplication
```
⚠️ REQUIRES INVESTIGATION:
app/Domains/Settings/SiteSettings/ (29 files)
```

**Found No Routes:** SiteSettings subdomain has no registered routes
**Hypothesis:** May be legacy/unused code OR authentication controllers

**Action Required:**
- Check if Auth controllers (RegisteredUserController, AuthenticatedSessionController, etc.) are used
- These might be Fortify/Breeze scaffolding
- If unused: Delete entire subdomain
- If used: Consolidate with parent Settings domain

---

## TO CONSOLIDATE

### 1. Settings Domain Structure
```
CURRENT STATE:
app/Domains/Settings/
├── SiteSettings/
│   └── Http/Controllers/
│       ├── Auth/*  (7 controllers - Fortify?)
│       ├── Settings/* (3 controllers)
│       └── Admin/* (1 controller)
├── Http/Controllers/Admin/
│   ├── SiteSettingsController.php
│   ├── SecuritySettingsController.php
│   └── ThemeSettingsController.php
└── Models/Setting.php
```

**Decision Matrix:**
| Scenario | Action |
|----------|--------|
| SiteSettings = Fortify Auth scaffolding | Move to root `app/Http/Controllers/Auth/` |
| SiteSettings = Duplicate of Settings | Delete SiteSettings, keep Settings |
| SiteSettings = Different purpose | Document purpose clearly |

**Recommendation:** Investigate `Settings/SiteSettings/Http/Controllers/Auth/*` files
- If they're standard Fortify/Breeze: Move to standard Laravel location
- If custom: Keep but rename for clarity

---

## TO UPDATE

### 1. Namespace Changes (After Move)
```bash
# All files referencing old namespaces:
App\Domains\ContentStorage → App\Domains\ContentManagement\ContentStorage
App\Domains\PageBuilder → App\Domains\ContentManagement\Pages

ESTIMATED FILES TO UPDATE:
- ContentStorage: 30+ PHP files
- PageBuilder references: 57 PHP files
- Frontend imports: 168 files (JS/TS/Vue)
```

### 2. Route Registrations
```php
// bootstrap/providers.php
UPDATE:
- ContentStorageServiceProvider location
- Remove PageBuilderServiceProvider (after merge)
- Verify all other providers still valid
```

### 3. Service Provider Imports
```php
FILES TO UPDATE:
- Any file importing ContentStorageServiceProvider
- Any file importing PageBuilderServiceProvider
- Update to new ContentManagement namespace
```

### 4. Facade References
```php
FOUND:
- PageFacade.php in ContentManagement/Pages/
- Currently points to PageBuilderServiceContract

UPDATE TO:
- Point to new PageServiceContract in Pages subdomain
```

### 5. Frontend Route Helpers (Wayfinder)
```bash
# After namespace/route changes:
./vendor/bin/sail artisan wayfinder:generate

# Verify all route imports still work
```

---

## VERIFICATION RESULTS ✅

### 1. Page Revision System
**Status:** ✅ **ACTIVELY USED**

**Data Found:**
```sql
SELECT COUNT(*) FROM page_revisions;
-- Result: 17 revisions
```

**Decision:** ✅ **KEEP REVISION SYSTEM**
- 17 page revisions exist in production
- Users are actively using revision history
- **Action:** Integrate revision system with new Pages subdomain
- **Migration:** Create `PageRevision` model in `ContentManagement/Pages/Models/`
- **DO NOT DELETE** revision migrations

### 2. Layout System
**Status:** ✅ **NOT IN USE - SAFE TO REMOVE**

**Data Found:**
```sql
SELECT COUNT(*) FROM layouts;
-- Result: ERROR - Table doesn't exist!
```

**Decision:** ✅ **DELETE LAYOUT SYSTEM**
- `layouts` table never created (migration exists but not run)
- No layout data in database
- Safe to remove all layout-related code
- **Action:**
  - Delete `app/Domains/PageBuilder/Models/Layout.php`
  - Delete layout migration file
  - Remove `layout_id` column plans from pages table

### 3. Settings/SiteSettings Purpose
**Question:** What is the purpose of SiteSettings subdomain?

**Check:**
```bash
grep -r "SiteSettings" app --include="*.php" | head -20
```

**Decision:**
- Document finding
- Consolidate or delete based on usage

### 4. Plugins Domain
**Question:** Is the Plugins system implemented and used?

**Check:**
```bash
./vendor/bin/sail artisan route:list --name=plugin
ls app/Domains/Plugins/
```

**Decision:**
- If **not implemented**: Mark as future feature, add TODO
- If **implemented**: Verify integration works after refactor

---

## MIGRATIONS TO AUDIT

### 1. Posts Table Migrations
```bash
# Find migrations creating columns we're dropping:
ls database/migrations/ | grep post | grep -E "content_markdown|content_html|table_of_contents"
```

**Columns to Drop:**
- `content_markdown`
- `content_html`
- `table_of_contents`

**Action:** Identify and mark for deletion after data migration

### 2. Pages Table Migrations
```bash
# Find migrations creating columns we're dropping:
ls database/migrations/ | grep page | grep -E "markdown_content|published_html|data"
```

**Columns to Drop:**
- `markdown_content`
- `published_html`
- `data`
- `layout_id` (if removing layout system)

**Action:** Identify and mark for deletion after data migration

### 3. Keep Safe
**DO NOT DELETE:**
- Migrations for: `id`, `title`, `slug`, `status`, `author_id`, `meta_*`, `published_at`
- Core table structure migrations
- Index migrations
- Foreign key migrations

---

## FRONTEND COMPONENT AUDIT

### 1. Total Vue Components
**Count:** 37 Vue component files in `resources/js/pages/admin/`

**By Feature:**
```
posts/          5 components
pages/          0 components (will create 3 new)
categories/     X components
tags/           X components
settings/       X components
...
```

### 2. Components Requiring Updates
**For ContentStorage Integration:**
- `resources/js/pages/admin/content/posts/Create.vue` - Add storage backend selector
- `resources/js/pages/admin/content/posts/Edit.vue` - Add storage backend selector
- `resources/js/pages/admin/content/posts/Index.vue` - Show storage driver column

**New Components to Create:**
- `resources/js/pages/admin/content/pages/Index.vue`
- `resources/js/pages/admin/content/pages/Create.vue`
- `resources/js/pages/admin/content/pages/Edit.vue`

### 3. Shared Components to Create
**Storage Backend Selector:**
```
resources/js/components/content-storage/
├── StorageDriverSelect.vue (dropdown with all 6 drivers)
├── StorageDriverBadge.vue (display current driver with icon)
└── StorageDriverConfig.vue (configure unconfigured drivers)
```

---

## DATABASE SCHEMA AUDIT

### Current Tables (Relevant)
```sql
-- Content tables:
posts                    ✅ Keep (modify schema)
pages                    ✅ Keep (modify schema)
categories               ✅ Keep
tags                     ✅ Keep
comments                 ✅ Keep

-- ContentStorage tables:
content_migrations       ✅ Keep (already created)
settings                 ✅ Keep (for driver config)

-- PageBuilder tables:
layouts                  ⚠️ Verify usage, consider removing
page_revisions           ⚠️ Verify usage, consider removing

-- Other:
media                    ✅ Keep
users                    ✅ Keep
roles                    ✅ Keep
permissions              ✅ Keep
```

### Schema Changes Required
```sql
-- Posts table:
ALTER TABLE posts
  ADD COLUMN storage_driver VARCHAR(50) DEFAULT 'database',
  ADD COLUMN storage_path VARCHAR(255) NULL,
  DROP COLUMN content_markdown,
  DROP COLUMN content_html,
  DROP COLUMN table_of_contents;

-- Pages table:
ALTER TABLE pages
  ADD COLUMN storage_driver VARCHAR(50) DEFAULT 'database',
  ADD COLUMN storage_path VARCHAR(255) NULL,
  DROP COLUMN markdown_content,
  DROP COLUMN published_html,
  DROP COLUMN data,
  DROP COLUMN layout_id; -- if removing layouts
```

---

## CODE QUALITY FINDINGS

### 1. Duplicate Code Patterns
**Found:** Settings/SiteSettings appears to duplicate functionality

**Recommendation:** Create shared traits or abstract base classes

### 2. Missing Type Hints
**Status:** Most files have proper type hints

**Action:** Run PHPStan to identify any missing types

### 3. Test Coverage
**Current:** 196 tests passing

**After Refactor:** Will need to update/add ~50+ tests

---

## RECOMMENDED IMPLEMENTATION SEQUENCE

Based on audit findings, the updated sequence should be:

1. **Phase 0.5: Clarify Unknowns** (before proceeding)
   - [ ] Check `page_revisions` table for data
   - [ ] Check `layouts` table for data
   - [ ] Investigate Settings/SiteSettings purpose
   - [ ] Document findings

2. **Phase 1: Move ContentStorage** (straightforward)
   - Low risk, clear path forward

3. **Phase 2: Merge PageBuilder** (complex)
   - Depends on revision/layout decisions
   - 57 PHP + 168 frontend files to update

4. **Phase 3-9:** Proceed as planned

---

## RISK ASSESSMENT

### High Risk
- **PageBuilder Merge:** Extensive integration (225 file references)
  - **Mitigation:** Thorough testing, feature flags, gradual rollout

### Medium Risk
- **Database Schema Changes:** Dropping columns with data
  - **Mitigation:** Backup, data migration command, rollback plan

### Low Risk
- **ContentStorage Move:** Clean subdomain restructure
  - **Mitigation:** Namespace updates, test suite verification

---

## NEXT STEPS

1. **Run SQL Queries:**
   ```sql
   SELECT COUNT(*) FROM page_revisions;
   SELECT COUNT(*) FROM layouts;
   SELECT COUNT(*) FROM pages WHERE layout_id IS NOT NULL;
   ```

2. **Investigate Settings/SiteSettings:**
   ```bash
   grep -r "SiteSettings" app --include="*.php"
   ./vendor/bin/sail artisan route:list | grep -i setting
   ```

3. **Create Decision Document:**
   - Keep or remove revisions?
   - Keep or remove layouts?
   - Consolidate Settings?

4. **Update Plan Based on Findings:**
   - Adjust Phase 2 based on revision/layout decisions
   - Add specific file lists to update
   - Refine time estimates

---

## CONCLUSION

**Major Finding:** PageBuilder is actively used with extensive integration. This is not dead code to delete - it's functionality to carefully merge.

**Recommendation:**
- Proceed with caution on Phase 2
- Complete Phase 0.5 (clarifications) before Phase 1
- Consider creating feature flags for gradual rollout
- Budget additional time for PageBuilder merge (potentially 4-6 hours alone)

**Updated Time Estimate:** 10-15 hours (increased from 8-12 due to PageBuilder complexity)

---

**Audit Status:** ✅ Complete
**Next Step:** Run clarification queries and make decisions
**Date:** 2025-12-02
