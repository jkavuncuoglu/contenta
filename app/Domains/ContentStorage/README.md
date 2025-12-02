# ContentStorage Domain

Multi-backend content storage system for Contenta CMS.

## Overview

This domain provides a flexible storage abstraction layer that allows content (Pages and Posts) to be stored in multiple backends:

- ✅ **Database** - Traditional database storage (implemented)
- ✅ **Local Filesystem** - Markdown files with YAML frontmatter (implemented)
- 🚧 **AWS S3** - Cloud object storage (Phase 4)
- 🚧 **GitHub** - Git-based version control (Phase 4)
- 🚧 **Azure Blob Storage** - Microsoft Azure (Phase 4)
- 🚧 **Google Cloud Storage** - Google Cloud (Phase 4)

## Quick Start

### Migrate Content

```bash
# Preview migration
./vendor/bin/sail artisan content:migrate pages database local --dry-run

# Execute migration
./vendor/bin/sail artisan content:migrate pages database local

# Async migration with verification
./vendor/bin/sail artisan content:migrate posts database local --async --verify
```

### Programmatic Usage

```php
use App\Domains\ContentStorage\Services\ContentStorageManager;

$storage = app(ContentStorageManager::class);
$repository = $storage->forContentType('pages');

// Read
$content = $repository->read('pages/about-us.md');

// Write
$repository->write('pages/test.md', new ContentData(...));

// List
$files = $repository->list();
```

## Architecture

### Directory Structure

```
ContentStorage/
├── Console/
│   └── Commands/
│       └── MigrateContentCommand.php      # CLI migration
├── Contracts/
│   └── ContentRepositoryContract.php       # Repository interface
├── Exceptions/
│   ├── MigrationException.php
│   ├── ReadException.php
│   ├── StorageException.php
│   └── WriteException.php
├── Jobs/
│   └── MigrateContentJob.php              # Queue-based migration
├── Models/
│   ├── ContentData.php                     # Value object
│   └── ContentMigration.php                # Migration tracking
├── Repositories/
│   ├── DatabaseRepository.php              # Database storage
│   └── LocalRepository.php                 # File storage
├── Services/
│   ├── ContentStorageManager.php           # Driver manager
│   ├── MigrationService.php                # Migration logic
│   └── PathPatternResolver.php             # Path patterns
├── Tests/
│   └── Unit/
│       ├── ContentDataTest.php             # 19 tests
│       ├── DatabaseRepositoryTest.php      # 20 tests
│       ├── MigrationServiceTest.php        # 19 tests
│       └── PathPatternResolverTest.php     # 27 tests
├── ContentStorageServiceProvider.php
└── README.md                               # This file
```

### Key Components

#### 1. ContentRepositoryContract

Interface that all storage drivers must implement:

```php
interface ContentRepositoryContract
{
    public function read(string $path): ContentData;
    public function write(string $path, ContentData $data): bool;
    public function exists(string $path): bool;
    public function delete(string $path): bool;
    public function list(string $directory = ''): array;
    public function testConnection(): bool;
    public function getDriverName(): string;
}
```

#### 2. ContentData

Immutable value object representing content with frontmatter:

```php
$content = new ContentData(
    content: '# Page Title',
    frontmatter: [
        'title' => 'Page Title',
        'slug' => 'page-title',
        'status' => 'published',
    ],
    hash: 'sha256...',
    size: 1024,
    modifiedAt: new DateTimeImmutable()
);
```

#### 3. ContentStorageManager

Laravel Manager pattern for driver resolution:

```php
// Get repository for specific content type
$repository = app(ContentStorageManager::class)->forContentType('pages');

// Get specific driver
$repository = app(ContentStorageManager::class)->driver('local', [
    'content_type' => 'pages'
]);
```

#### 4. PathPatternResolver

Resolves path patterns with dynamic tokens:

```php
$resolver = new PathPatternResolver();

// Pattern: posts/{year}/{month}/{slug}.md
// Result: posts/2025/12/hello-world.md
$path = $resolver->resolve('posts/{year}/{month}/{slug}.md', 'posts', $post);
```

**Available Tokens:**
- `{type}` - Content type (pages/posts)
- `{id}` - Model ID
- `{slug}` - URL slug
- `{year}` - 4-digit year
- `{month}` - 2-digit month
- `{day}` - 2-digit day
- `{author_id}` - Author ID
- `{status}` - Status (draft/published)

#### 5. MigrationService

Handles content migration between backends:

```php
$service = app(MigrationService::class);

// Start migration
$migration = $service->startMigration('pages', 'database', 'local');

// Execute
$result = $service->executeMigration($migration, deleteSource: false);

// Verify
$verification = $service->verifyMigration($migration, sampleSize: 10);

// Rollback
$rollback = $service->rollbackMigration($migration);
```

## Repositories

### DatabaseRepository

Stores content in database tables (`pages`, `posts`).

**Features:**
- Backward compatible with existing schema
- Builds frontmatter from model attributes
- Transaction support with rollback
- Soft delete support
- Creates new records on write (for migrations)

**Path Format:** `pages/123` or `posts/456`

### LocalRepository

Stores content as markdown files with YAML frontmatter.

**Features:**
- YAML frontmatter parsing
- Automatic directory creation
- Recursive file listing
- Metadata tracking (size, modified date)

**Path Format:** Configurable via PathPatternResolver

**File Example:**
```markdown
---
title: "About Us"
slug: about-us
status: published
---

# About Us

Content here...
```

## Migration System

### ContentMigration Model

Tracks migration progress:

```php
$migration->status;           // pending|running|completed|failed
$migration->total_items;      // Total items to migrate
$migration->migrated_items;   // Successfully migrated
$migration->failed_items;     // Failed to migrate
$migration->error_log;        // Array of error details
$migration->getProgress();    // Progress percentage (0-100)
$migration->getDuration();    // Duration in seconds
```

### MigrateContentJob

Queue job for background migration:

- **Queue:** `content-migrations`
- **Retry:** 3 attempts (1min, 5min, 15min backoff)
- **Timeout:** 1 hour
- **Tags:** For monitoring and filtering

### MigrateContentCommand

Artisan command for manual migration:

```bash
content:migrate <content_type> <from_driver> <to_driver> [options]

Options:
  --async          Run in background queue
  --dry-run        Preview without executing
  --delete-source  Delete source after migration
  --force          Skip confirmation
  --verify         Verify integrity after completion
```

## Testing

### Run Tests

```bash
# All ContentStorage tests
./vendor/bin/sail pest app/Domains/ContentStorage/Tests/

# Specific test file
./vendor/bin/sail pest app/Domains/ContentStorage/Tests/Unit/MigrationServiceTest.php

# With coverage
XDEBUG_MODE=coverage ./vendor/bin/sail pest app/Domains/ContentStorage/Tests/ --coverage
```

### Test Coverage

- **ContentDataTest:** 19 tests - YAML parsing, hashing, immutability
- **DatabaseRepositoryTest:** 20 tests - CRUD, frontmatter, transactions
- **PathPatternResolverTest:** 27 tests - Token resolution, validation
- **MigrationServiceTest:** 19 tests - Full migration lifecycle

**Total:** 85 tests, 238 assertions

## Configuration

### Filesystems

Added 'content' disk in `config/filesystems.php`:

```php
'content' => [
    'driver' => 'local',
    'root' => storage_path('content'),
    'visibility' => 'private',
    'throw' => true,
],
```

### Service Provider

Registered in `bootstrap/providers.php`:

```php
App\Domains\ContentStorage\ContentStorageServiceProvider::class,
```

## Security

### Path Validation

- ✅ Blocks directory traversal (`../`)
- ✅ Blocks absolute paths (`/etc/passwd`)
- ✅ Blocks dangerous characters
- ✅ Enforces length limits (255 chars)
- ✅ Component sanitization

### Database Safety

- ✅ Transaction wrapping
- ✅ Prepared statements
- ✅ Input validation
- ✅ Soft deletes

## Performance

### Optimizations

- Lazy loading of repositories
- Hash-based change detection
- Indexed database queries
- Progress tracking without N+1

### Recommendations

- Use `--async` for >100 items
- Use `--verify` with sample size
- Run during off-peak hours
- Monitor queue workers

## Roadmap

### Phase 4: Cloud Drivers (Next)
- [ ] S3Repository
- [ ] GitHubRepository
- [ ] AzureRepository
- [ ] GcsRepository

### Phase 5: Admin UI
- [ ] Storage settings page
- [ ] Driver selection UI
- [ ] Credential forms
- [ ] Migration wizard
- [ ] Progress dashboard

### Phase 6: Integration
- [ ] Integrate with Page/Post models
- [ ] Automatic migrations on driver change
- [ ] Caching layer
- [ ] Documentation

## Documentation

- **Usage Guide:** [CONTENT_STORAGE_USAGE.md](../../../CONTENT_STORAGE_USAGE.md)
- **Task Tracking:** [TASK_MULTI_STORAGE_BACKEND.md](../../../TASK_MULTI_STORAGE_BACKEND.md)

## Support

For issues or questions:
- Create an issue on GitHub
- Check test files for usage examples
- Review documentation files

---

**Version:** 1.0.0
**Status:** Phase 3 Complete ✓
**Last Updated:** 2025-12-02
