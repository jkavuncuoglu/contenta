# Content Storage: Multi-Backend Usage Guide

## Quick Start

Contenta CMS now supports multiple storage backends for your content:
- **Database** - Traditional database storage (current default)
- **Local Filesystem** - Store content as markdown files
- **Cloud Storage** - AWS S3, GitHub, Azure, GCS *(coming in Phase 4)*

## Migration Commands

### Preview Migration (Dry Run)

Check what will be migrated without making changes:

```bash
./vendor/bin/sail artisan content:migrate pages database local --dry-run
```

**Output:**
```
Content Migration
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
Content Type: pages
From: database
To: local
Mode: Synchronous
🔍 DRY RUN MODE - No actual changes will be made
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

Analyzing migration...
Found 1 items to migrate

Sample items (first 5):
  • pages/12

✓ Dry run completed. No changes were made.
```

### Synchronous Migration

Migrate content with a real-time progress bar:

```bash
./vendor/bin/sail artisan content:migrate pages database local
```

**Output:**
```
Content Migration
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
Content Type: pages
From: database
To: local
Mode: Synchronous
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

 Do you want to proceed with the migration? (yes/no) [yes]:
 > yes

Starting migration...
Migration ID: 1

 1/1 [============================] 100%

✓ Migration completed
Migrated: 1
Failed: 0
Duration: 2 seconds
```

### Asynchronous Migration

Run migration in background queue (recommended for large content sets):

```bash
./vendor/bin/sail artisan content:migrate posts database local --async
```

**Output:**
```
Content Migration
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
Content Type: posts
From: database
To: local
Mode: Async (Queue)
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

Starting migration...
Migration ID: 2

✓ Migration job dispatched to queue
Monitor progress: php artisan content:migration-status 2
```

### Migration with Verification

Verify content integrity after migration:

```bash
./vendor/bin/sail artisan content:migrate pages database local --verify
```

Verification compares SHA-256 hashes of source and destination content.

### Migration with Source Deletion

Delete source content after successful migration (use with caution):

```bash
./vendor/bin/sail artisan content:migrate pages database local --delete-source
```

⚠️ **Warning:** This will permanently delete content from the source after migration. Always verify migration success first!

### Skip Confirmation

For automated scripts, skip the confirmation prompt:

```bash
./vendor/bin/sail artisan content:migrate pages database local --force
```

### Combined Options

```bash
# Async migration with verification and source deletion
./vendor/bin/sail artisan content:migrate posts database local \
    --async \
    --verify \
    --delete-source \
    --force
```

## Storage Drivers

### Database Driver

**When to use:**
- Default setup
- Traditional CMS workflow
- Need database queries/relationships
- Admin panel editing

**Path Format:** `pages/123` or `posts/456` (uses model ID)

### Local Filesystem Driver

**When to use:**
- Git-based workflow
- Static site generation
- Version control for content
- Markdown editing in IDE

**Path Format:** Configurable with tokens

**Default Patterns:**
- Pages: `pages/{slug}.md`
- Posts: `posts/{year}/{month}/{slug}.md`

**Example Files:**
```
storage/content/
├── pages/
│   ├── about-us.md
│   ├── contact.md
│   └── privacy-policy.md
└── posts/
    └── 2025/
        └── 12/
            ├── hello-world.md
            ├── getting-started.md
            └── tips-and-tricks.md
```

### AWS S3 Driver ✅

**When to use:**
- Global content distribution via CDN
- Scalable cloud storage
- Multi-region redundancy
- Need S3 ecosystem integration

**Path Format:** Configurable with prefix

**Configuration:**
```php
// Settings required
's3_key'      => 'your-aws-access-key'
's3_secret'   => 'your-aws-secret-key'  // Encrypted
's3_region'   => 'us-east-1'            // Default: us-east-1
's3_bucket'   => 'your-bucket-name'
's3_prefix'   => 'content'              // Optional prefix
```

**Example S3 Structure:**
```
s3://your-bucket/
└── content/               (prefix)
    ├── pages/
    │   ├── about-us.md
    │   └── contact.md
    └── posts/
        └── 2025/
            └── 12/
                └── hello-world.md
```

**Features:**
- ✅ Direct uploads/downloads
- ✅ Metadata storage (content hash)
- ✅ Connection testing
- ✅ Prefix support for organization
- ✅ Automatic markdown content-type
- ✅ Full migration support

## File Format

When using filesystem storage, content is stored as markdown with YAML frontmatter:

```markdown
---
title: "About Us"
slug: about-us
status: published
meta_title: "About Our Company"
meta_description: "Learn more about our mission and values"
meta_keywords: company, about, mission
template: default
language: en
---

# About Us

This is the content of the about page.

## Our Mission

We strive to...
```

## Path Pattern Tokens

Customize file organization with these tokens:

| Token | Description | Example |
|-------|-------------|---------|
| `{type}` | Content type | `pages` or `posts` |
| `{id}` | Database ID | `123` |
| `{slug}` | URL-friendly slug | `hello-world` |
| `{year}` | 4-digit year | `2025` |
| `{month}` | 2-digit month | `12` |
| `{day}` | 2-digit day | `02` |
| `{author_id}` | Author ID | `1` |
| `{status}` | Content status | `draft`, `published` |

**Example Patterns:**

```
# By slug only
pages/{slug}.md
→ pages/about-us.md

# By year/month/slug (good for blogs)
posts/{year}/{month}/{slug}.md
→ posts/2025/12/hello-world.md

# By year/month/day/slug (daily posts)
posts/{year}/{month}/{day}/{slug}.md
→ posts/2025/12/02/daily-update.md

# By status/slug (drafts separate)
pages/{status}/{slug}.md
→ pages/draft/work-in-progress.md
→ pages/published/about-us.md

# By author/year/slug (multi-author blog)
posts/{author_id}/{year}/{slug}.md
→ posts/1/2025/my-first-post.md
```

## Migration Workflow Examples

### Scenario 1: Database to Git Workflow

**Goal:** Move content from database to files for version control

```bash
# 1. Preview the migration
./vendor/bin/sail artisan content:migrate pages database local --dry-run

# 2. Migrate pages (keep database as backup)
./vendor/bin/sail artisan content:migrate pages database local

# 3. Verify files were created
ls -la storage/content/pages/

# 4. Migrate posts
./vendor/bin/sail artisan content:migrate posts database local

# 5. Add to git
cd storage/content
git init
git add .
git commit -m "Initial content migration"
```

### Scenario 2: Testing Local Storage

**Goal:** Try filesystem storage without losing database content

```bash
# 1. Migrate to local (keep database)
./vendor/bin/sail artisan content:migrate pages database local

# 2. Test local storage
# Edit files in storage/content/pages/

# 3. If you don't like it, rollback
./vendor/bin/sail artisan content:migrate pages local database --delete-source
```

### Scenario 3: Backup Before Major Update

**Goal:** Create a backup before making changes

```bash
# 1. Backup to local filesystem
./vendor/bin/sail artisan content:migrate pages database local

# 2. Make your changes in database
# ...

# 3. If something goes wrong, restore
./vendor/bin/sail artisan content:migrate pages local database --force
```

### Scenario 4: Large Site Migration

**Goal:** Migrate 10,000+ posts without blocking

```bash
# 1. Dry run to check count
./vendor/bin/sail artisan content:migrate posts database local --dry-run

# 2. Start async migration
./vendor/bin/sail artisan content:migrate posts database local --async

# 3. Monitor queue
./vendor/bin/sail artisan queue:work --queue=content-migrations

# 4. Check migration status
./vendor/bin/sail artisan tinker --execute="
    \$migration = App\Domains\ContentStorage\Models\ContentMigration::latest()->first();
    echo 'Status: ' . \$migration->status . PHP_EOL;
    echo 'Progress: ' . \$migration->getProgress() . '%' . PHP_EOL;
    echo 'Migrated: ' . \$migration->migrated_items . '/' . \$migration->total_items . PHP_EOL;
"
```

### Scenario 5: Migrate to AWS S3 ✅

**Goal:** Move content to S3 for global CDN distribution

```bash
# 1. Configure S3 credentials (in admin panel or tinker)
./vendor/bin/sail artisan tinker --execute="
    use App\Domains\Settings\Models\Setting;
    Setting::set('content_storage', 's3_key', 'AKIAIOSFODNN7EXAMPLE');
    Setting::setEncrypted('content_storage', 's3_secret', 'wJalrXUtnFEMI/K7MDENG/bPxRfiCYEXAMPLEKEY');
    Setting::set('content_storage', 's3_region', 'us-east-1');
    Setting::set('content_storage', 's3_bucket', 'my-contenta-bucket');
    Setting::set('content_storage', 's3_prefix', 'content');
"

# 2. Test S3 connection
./vendor/bin/sail artisan tinker --execute="
    use App\Domains\ContentStorage\Services\ContentStorageManager;
    \$storage = app(ContentStorageManager::class);
    \$canConnect = \$storage->testDriver('s3');
    echo \$canConnect ? 'S3 connection successful!' : 'S3 connection failed!';
"

# 3. Preview migration
./vendor/bin/sail artisan content:migrate pages database s3 --dry-run

# 4. Migrate to S3 (async for large content)
./vendor/bin/sail artisan content:migrate pages database s3 --async

# 5. Verify content in S3
# Check AWS S3 console at: s3://my-contenta-bucket/content/pages/

# 6. Update driver setting to use S3 by default
./vendor/bin/sail artisan tinker --execute="
    use App\Domains\Settings\Models\Setting;
    Setting::set('content_storage', 'pages_storage_driver', 's3');
"
```

### Scenario 6: S3 to Database (Rollback)

**Goal:** Roll back from S3 to database if needed

```bash
# 1. Migrate back to database
./vendor/bin/sail artisan content:migrate pages s3 database --verify

# 2. Verify all content migrated
# Check migration status

# 3. Delete from S3 after verification (optional)
./vendor/bin/sail artisan content:migrate pages s3 database --delete-source --force
```

## Programmatic Usage

Use the ContentStorageManager in your code:

```php
use App\Domains\ContentStorage\Services\ContentStorageManager;
use App\Domains\ContentStorage\Models\ContentData;

// Get repository for content type
$storage = app(ContentStorageManager::class);
$repository = $storage->forContentType('pages');

// Read content
$content = $repository->read('pages/about-us.md');
echo $content->frontmatter['title']; // "About Us"
echo $content->content; // Markdown content

// Write content
$contentData = new ContentData(
    content: '# New Page',
    frontmatter: [
        'title' => 'New Page',
        'slug' => 'new-page',
        'status' => 'draft',
    ]
);
$repository->write('pages/new-page.md', $contentData);

// Check if exists
if ($repository->exists('pages/about-us.md')) {
    echo "Page exists!";
}

// List all content
$files = $repository->list();
foreach ($files as $path) {
    echo "Found: $path\n";
}

// Delete content
$repository->delete('pages/old-page.md');
```

## Troubleshooting

### Migration Failed

Check the error log:

```bash
./vendor/bin/sail artisan tinker --execute="
    \$migration = App\Domains\ContentStorage\Models\ContentMigration::latest()->first();
    print_r(\$migration->error_log);
"
```

### Files Not Created

Check permissions:

```bash
ls -la storage/content/
chmod -R 755 storage/content/
```

### Queue Not Processing

Start the queue worker:

```bash
./vendor/bin/sail artisan queue:work --queue=content-migrations
```

Or use Horizon:

```bash
./vendor/bin/sail artisan horizon
```

### Rollback Migration

If you need to undo a migration:

```php
use App\Domains\ContentStorage\Services\MigrationService;

$migrationService = app(MigrationService::class);
$originalMigration = App\Domains\ContentStorage\Models\ContentMigration::find(1);

// Creates reverse migration and executes it
$rollback = $migrationService->rollbackMigration($originalMigration);
```

## Best Practices

### ✅ Do

- **Always dry-run first** to preview changes
- **Verify migrations** on important content
- **Keep database backup** before deleting source
- **Use async** for large migrations (>100 items)
- **Monitor queue** when using async
- **Test on staging** before production

### ❌ Don't

- **Don't skip dry-run** on production
- **Don't delete source** without verification
- **Don't run multiple migrations** simultaneously for same content type
- **Don't modify content** during active migration
- **Don't stop queue worker** during async migration

## Migration Status Reference

| Status | Description |
|--------|-------------|
| `pending` | Migration created but not started |
| `running` | Migration in progress |
| `completed` | Migration finished successfully |
| `failed` | Migration encountered errors |

## Performance Tips

### For Large Migrations

1. **Use async mode:**
   ```bash
   --async
   ```

2. **Run during off-peak hours:**
   ```bash
   # Schedule for 2 AM
   0 2 * * * cd /var/www && ./vendor/bin/sail artisan content:migrate posts database local --async --force
   ```

3. **Monitor resources:**
   ```bash
   ./vendor/bin/sail exec mysql top
   ```

4. **Chunk verification:**
   ```bash
   # Verify sample instead of all
   --verify  # Default: 10 random items
   ```

## Cloud Storage

### AWS S3 ✅ Available Now!

**Features:**
- Global CDN distribution
- High availability and durability (99.999999999%)
- Scalable storage
- Regional deployment options
- Metadata support for content hashing

**Setup:**
```bash
# Configure in Settings model or Admin UI
's3_key'      => 'AKIAIOSFODNN7EXAMPLE'
's3_secret'   => 'wJalrXUtnFEMI/K7MDENG/bPxRfiCYEXAMPLEKEY'
's3_region'   => 'us-east-1'
's3_bucket'   => 'my-contenta-bucket'
's3_prefix'   => 'content'  # Optional
```

**Migration:**
```bash
# Migrate to S3
./vendor/bin/sail artisan content:migrate pages database s3 --async --verify

# Migrate from S3
./vendor/bin/sail artisan content:migrate pages s3 database
```

### Coming Soon

- **GitHub** - Git-based workflow, version control (Phase 4)
- **Azure Blob** - Microsoft Azure integration (Phase 4)
- **Google Cloud Storage** - Google Cloud integration (Phase 4)

## Support

For issues or questions:
- GitHub: https://github.com/anthropics/contenta/issues
- Documentation: [TASK_MULTI_STORAGE_BACKEND.md](./TASK_MULTI_STORAGE_BACKEND.md)

---

**Generated with Claude Code**
**Version:** 1.0.0
**Last Updated:** 2025-12-02
