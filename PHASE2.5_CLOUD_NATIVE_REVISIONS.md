# Phase 2.5: Cloud-Native Revision System

**Created:** 2025-12-03
**Status:** Planned
**Priority:** High
**Estimated Duration:** 4-6 hours

---

## Overview

Replace database-driven revision system with a cloud-native approach that leverages native versioning capabilities of storage backends:
- **S3/Azure/GCS:** Use bucket versioning/history (when enabled)
- **GitHub:** Use commit history as revisions
- **Database/Local:** Keep existing database revision system

This approach provides:
- ✅ Automatic revision tracking at storage layer
- ✅ Reduced database bloat
- ✅ Native cloud provider tools for revision management
- ✅ True source-of-truth storage
- ✅ Better scalability

---

## Architecture Design

### Storage-Aware Revision Strategy

```
┌─────────────────────────────────────────────────────┐
│          Revision Interface (Polymorphic)           │
├─────────────────────────────────────────────────────┤
│                                                     │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────┐ │
│  │   Database   │  │    Cloud     │  │  GitHub  │ │
│  │   Revisions  │  │   Versioning │  │  Commits │ │
│  └──────────────┘  └──────────────┘  └──────────┘ │
│                                                     │
│  Used by:          Used by:          Used by:      │
│  - Database        - S3              - GitHub      │
│  - Local           - Azure Blob      - GitLab      │
│                    - GCS             - Bitbucket   │
└─────────────────────────────────────────────────────┘
```

---

## Implementation Plan

### 1. Create Revision Contracts

**File:** `app/Domains/ContentManagement/ContentStorage/Contracts/RevisionProviderInterface.php`

```php
interface RevisionProviderInterface
{
    /**
     * Check if this storage driver supports revisions
     */
    public function supportsRevisions(): bool;

    /**
     * Get paginated revision history
     *
     * @param string $storagePath
     * @param int $page Page number (1-indexed)
     * @param int $perPage Items per page (default: 10)
     * @return RevisionCollection
     */
    public function getRevisions(string $storagePath, int $page = 1, int $perPage = 10): RevisionCollection;

    /**
     * Get a specific revision by ID/version
     *
     * @param string $storagePath
     * @param string $revisionId Version ID, commit hash, or timestamp
     * @return Revision|null
     */
    public function getRevision(string $storagePath, string $revisionId): ?Revision;

    /**
     * Restore a specific revision (make it current)
     *
     * @param string $storagePath
     * @param string $revisionId
     * @return bool
     */
    public function restoreRevision(string $storagePath, string $revisionId): bool;

    /**
     * Get the latest revision
     *
     * @param string $storagePath
     * @return Revision|null
     */
    public function getLatestRevision(string $storagePath): ?Revision;
}
```

---

### 2. Create Revision Value Objects

**File:** `app/Domains/ContentManagement/ContentStorage/ValueObjects/Revision.php`

```php
readonly class Revision
{
    public function __construct(
        public string $id,              // Version ID, commit hash, or DB ID
        public string $content,         // The actual content at this revision
        public ?string $message,        // Commit message or change description
        public ?string $author,         // Author name
        public ?string $authorEmail,    // Author email
        public Carbon $timestamp,       // When this revision was created
        public ?string $metadata,       // JSON metadata (size, hash, etc)
        public bool $isCurrent,         // Is this the current version?
    ) {}

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'content' => $this->content,
            'message' => $this->message,
            'author' => $this->author,
            'author_email' => $this->authorEmail,
            'timestamp' => $this->timestamp->toIso8601String(),
            'metadata' => $this->metadata ? json_decode($this->metadata, true) : null,
            'is_current' => $this->isCurrent,
        ];
    }
}

class RevisionCollection implements \IteratorAggregate, \Countable
{
    public function __construct(
        private array $revisions,
        private int $total,
        private int $currentPage,
        private int $perPage,
        private bool $hasMore,
    ) {}

    public function getRevisions(): array { return $this->revisions; }
    public function getTotal(): int { return $this->total; }
    public function getCurrentPage(): int { return $this->currentPage; }
    public function getPerPage(): int { return $this->perPage; }
    public function hasMore(): bool { return $this->hasMore; }

    public function toArray(): array
    {
        return [
            'data' => array_map(fn($r) => $r->toArray(), $this->revisions),
            'meta' => [
                'total' => $this->total,
                'current_page' => $this->currentPage,
                'per_page' => $this->perPage,
                'has_more' => $this->hasMore,
            ],
        ];
    }

    public function getIterator(): \Traversable { return new \ArrayIterator($this->revisions); }
    public function count(): int { return count($this->revisions); }
}
```

---

### 3. Implement Storage-Specific Revision Providers

#### 3.1 Database Revision Provider

**File:** `app/Domains/ContentManagement/ContentStorage/RevisionProviders/DatabaseRevisionProvider.php`

```php
class DatabaseRevisionProvider implements RevisionProviderInterface
{
    public function __construct(
        private Page|Post $model
    ) {}

    public function supportsRevisions(): bool
    {
        return true;
    }

    public function getRevisions(string $storagePath, int $page = 1, int $perPage = 10): RevisionCollection
    {
        // Query page_revisions or post_revisions table
        $query = $this->model->revisions()
            ->orderByDesc('created_at');

        $total = $query->count();
        $revisions = $query->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->get()
            ->map(fn($rev) => new Revision(
                id: (string) $rev->id,
                content: $rev->content_markdown ?? '',
                message: $rev->change_summary ?? 'Revision',
                author: $rev->author?->name,
                authorEmail: $rev->author?->email,
                timestamp: $rev->created_at,
                metadata: json_encode(['size' => strlen($rev->content_markdown ?? '')]),
                isCurrent: false,
            ))->toArray();

        return new RevisionCollection(
            revisions: $revisions,
            total: $total,
            currentPage: $page,
            perPage: $perPage,
            hasMore: $total > ($page * $perPage),
        );
    }

    public function getRevision(string $storagePath, string $revisionId): ?Revision
    {
        $rev = $this->model->revisions()->find($revisionId);
        if (!$rev) return null;

        return new Revision(
            id: (string) $rev->id,
            content: $rev->content_markdown ?? '',
            message: $rev->change_summary ?? 'Revision',
            author: $rev->author?->name,
            authorEmail: $rev->author?->email,
            timestamp: $rev->created_at,
            metadata: json_encode(['size' => strlen($rev->content_markdown ?? '')]),
            isCurrent: false,
        );
    }

    public function restoreRevision(string $storagePath, string $revisionId): bool
    {
        $revision = $this->getRevision($storagePath, $revisionId);
        if (!$revision) return false;

        // Update the model with revision content
        $this->model->update([
            'content_markdown' => $revision->content,
        ]);

        return true;
    }

    public function getLatestRevision(string $storagePath): ?Revision
    {
        $rev = $this->model->revisions()->latest()->first();
        if (!$rev) return null;

        return $this->getRevision($storagePath, (string) $rev->id);
    }
}
```

#### 3.2 S3 Revision Provider

**File:** `app/Domains/ContentManagement/ContentStorage/RevisionProviders/S3RevisionProvider.php`

```php
class S3RevisionProvider implements RevisionProviderInterface
{
    public function __construct(
        private S3Repository $repository
    ) {}

    public function supportsRevisions(): bool
    {
        // Check if bucket has versioning enabled
        return $this->repository->isBucketVersioningEnabled();
    }

    public function getRevisions(string $storagePath, int $page = 1, int $perPage = 10): RevisionCollection
    {
        try {
            // Use S3 ListObjectVersions API
            $result = $this->repository->getClient()->listObjectVersions([
                'Bucket' => $this->repository->getBucket(),
                'Prefix' => $storagePath,
                'MaxKeys' => $perPage,
                'KeyMarker' => $page > 1 ? $this->getPageMarker($page) : null,
            ]);

            $versions = $result['Versions'] ?? [];
            $revisions = [];

            foreach ($versions as $version) {
                // Fetch the actual content for each version
                $content = $this->repository->getClient()->getObject([
                    'Bucket' => $this->repository->getBucket(),
                    'Key' => $storagePath,
                    'VersionId' => $version['VersionId'],
                ])['Body']->getContents();

                $revisions[] = new Revision(
                    id: $version['VersionId'],
                    content: $content,
                    message: 'S3 Version',
                    author: $version['Owner']['DisplayName'] ?? 'Unknown',
                    authorEmail: null,
                    timestamp: Carbon::parse($version['LastModified']),
                    metadata: json_encode([
                        'size' => $version['Size'],
                        'etag' => $version['ETag'],
                        'storage_class' => $version['StorageClass'] ?? 'STANDARD',
                    ]),
                    isCurrent: $version['IsLatest'] ?? false,
                );
            }

            return new RevisionCollection(
                revisions: $revisions,
                total: count($versions), // S3 doesn't provide total count
                currentPage: $page,
                perPage: $perPage,
                hasMore: $result['IsTruncated'] ?? false,
            );

        } catch (\Exception $e) {
            Log::error('Failed to fetch S3 revisions', [
                'path' => $storagePath,
                'error' => $e->getMessage(),
            ]);

            return new RevisionCollection([], 0, $page, $perPage, false);
        }
    }

    public function getRevision(string $storagePath, string $revisionId): ?Revision
    {
        try {
            $result = $this->repository->getClient()->getObject([
                'Bucket' => $this->repository->getBucket(),
                'Key' => $storagePath,
                'VersionId' => $revisionId,
            ]);

            $metadata = $this->repository->getClient()->headObject([
                'Bucket' => $this->repository->getBucket(),
                'Key' => $storagePath,
                'VersionId' => $revisionId,
            ]);

            return new Revision(
                id: $revisionId,
                content: $result['Body']->getContents(),
                message: 'S3 Version',
                author: $metadata['Metadata']['author'] ?? 'Unknown',
                authorEmail: null,
                timestamp: Carbon::parse($metadata['LastModified']),
                metadata: json_encode([
                    'size' => $metadata['ContentLength'],
                    'etag' => $metadata['ETag'],
                    'content_type' => $metadata['ContentType'],
                ]),
                isCurrent: false,
            );

        } catch (\Exception $e) {
            Log::error('Failed to fetch S3 revision', [
                'path' => $storagePath,
                'version_id' => $revisionId,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    public function restoreRevision(string $storagePath, string $revisionId): bool
    {
        try {
            // Copy the old version to become the current version
            $this->repository->getClient()->copyObject([
                'Bucket' => $this->repository->getBucket(),
                'CopySource' => urlencode($this->repository->getBucket() . '/' . $storagePath) . '?versionId=' . $revisionId,
                'Key' => $storagePath,
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error('Failed to restore S3 revision', [
                'path' => $storagePath,
                'version_id' => $revisionId,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    public function getLatestRevision(string $storagePath): ?Revision
    {
        $revisions = $this->getRevisions($storagePath, 1, 1);
        return $revisions->count() > 0 ? $revisions->getRevisions()[0] : null;
    }

    private function getPageMarker(int $page): ?string
    {
        // Implement pagination marker logic
        // This would need to be cached or stored
        return null;
    }
}
```

#### 3.3 GitHub Revision Provider

**File:** `app/Domains/ContentManagement/ContentStorage/RevisionProviders/GitHubRevisionProvider.php`

```php
class GitHubRevisionProvider implements RevisionProviderInterface
{
    public function __construct(
        private GithubRepository $repository
    ) {}

    public function supportsRevisions(): bool
    {
        return true; // GitHub always has commit history
    }

    public function getRevisions(string $storagePath, int $page = 1, int $perPage = 10): RevisionCollection
    {
        try {
            // Use GitHub Commits API
            $commits = $this->repository->getClient()->repo()->commits()->all(
                $this->repository->getOwner(),
                $this->repository->getRepo(),
                [
                    'path' => $storagePath,
                    'page' => $page,
                    'per_page' => $perPage,
                ]
            );

            $revisions = [];

            foreach ($commits as $commit) {
                // Fetch file content at this commit
                $content = $this->repository->read($storagePath, $commit['sha']);

                $revisions[] = new Revision(
                    id: $commit['sha'],
                    content: $content,
                    message: $commit['commit']['message'],
                    author: $commit['commit']['author']['name'],
                    authorEmail: $commit['commit']['author']['email'],
                    timestamp: Carbon::parse($commit['commit']['author']['date']),
                    metadata: json_encode([
                        'sha' => $commit['sha'],
                        'url' => $commit['html_url'],
                        'parents' => $commit['parents'],
                    ]),
                    isCurrent: false,
                );
            }

            // Mark first revision as current
            if (count($revisions) > 0 && $page === 1) {
                $revisions[0] = new Revision(
                    ...(array) $revisions[0],
                    isCurrent: true,
                );
            }

            return new RevisionCollection(
                revisions: $revisions,
                total: count($commits), // GitHub doesn't provide total in list
                currentPage: $page,
                perPage: $perPage,
                hasMore: count($commits) === $perPage,
            );

        } catch (\Exception $e) {
            Log::error('Failed to fetch GitHub revisions', [
                'path' => $storagePath,
                'error' => $e->getMessage(),
            ]);

            return new RevisionCollection([], 0, $page, $perPage, false);
        }
    }

    public function getRevision(string $storagePath, string $revisionId): ?Revision
    {
        try {
            // Fetch commit details
            $commit = $this->repository->getClient()->repo()->commits()->show(
                $this->repository->getOwner(),
                $this->repository->getRepo(),
                $revisionId
            );

            // Fetch file content at this commit
            $content = $this->repository->read($storagePath, $revisionId);

            return new Revision(
                id: $commit['sha'],
                content: $content,
                message: $commit['commit']['message'],
                author: $commit['commit']['author']['name'],
                authorEmail: $commit['commit']['author']['email'],
                timestamp: Carbon::parse($commit['commit']['author']['date']),
                metadata: json_encode([
                    'sha' => $commit['sha'],
                    'url' => $commit['html_url'],
                    'parents' => $commit['parents'],
                    'stats' => $commit['stats'],
                ]),
                isCurrent: false,
            );

        } catch (\Exception $e) {
            Log::error('Failed to fetch GitHub revision', [
                'path' => $storagePath,
                'sha' => $revisionId,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    public function restoreRevision(string $storagePath, string $revisionId): bool
    {
        try {
            // Get the content from the old commit
            $content = $this->repository->read($storagePath, $revisionId);

            // Get the old commit details for the message
            $oldCommit = $this->getRevision($storagePath, $revisionId);

            // Write it as a new commit (revert)
            $this->repository->write(
                $storagePath,
                $content,
                "Restore to revision: {$oldCommit->message}"
            );

            return true;

        } catch (\Exception $e) {
            Log::error('Failed to restore GitHub revision', [
                'path' => $storagePath,
                'sha' => $revisionId,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    public function getLatestRevision(string $storagePath): ?Revision
    {
        $revisions = $this->getRevisions($storagePath, 1, 1);
        return $revisions->count() > 0 ? $revisions->getRevisions()[0] : null;
    }
}
```

#### 3.4 Azure Blob Revision Provider

Similar to S3, implement using Azure Blob Versioning API.

#### 3.5 GCS Revision Provider

Similar to S3, implement using GCS Object Versioning API.

---

### 4. Create Revision Provider Factory

**File:** `app/Domains/ContentManagement/ContentStorage/Factories/RevisionProviderFactory.php`

```php
class RevisionProviderFactory
{
    public function __construct(
        private ContentStorageManager $storageManager
    ) {}

    public function make(string $driver, Page|Post $model): RevisionProviderInterface
    {
        return match($driver) {
            'database', 'local' => new DatabaseRevisionProvider($model),
            's3' => new S3RevisionProvider($this->storageManager->driver('s3')),
            'azure' => new AzureBlobRevisionProvider($this->storageManager->driver('azure')),
            'gcs' => new GCSRevisionProvider($this->storageManager->driver('gcs')),
            'github' => new GitHubRevisionProvider($this->storageManager->driver('github')),
            'gitlab' => new GitLabRevisionProvider($this->storageManager->driver('gitlab')),
            default => throw new \InvalidArgumentException("Unsupported storage driver: {$driver}"),
        };
    }
}
```

---

### 5. Update Page/Post Models

Add revision accessor methods:

```php
// In Page.php and Post.php

public function getRevisionProvider(): RevisionProviderInterface
{
    $factory = app(RevisionProviderFactory::class);
    return $factory->make($this->storage_driver, $this);
}

public function revisionHistory(int $page = 1, int $perPage = 10): RevisionCollection
{
    return $this->getRevisionProvider()->getRevisions($this->storage_path, $page, $perPage);
}

public function getRevision(string $revisionId): ?Revision
{
    return $this->getRevisionProvider()->getRevision($this->storage_path, $revisionId);
}

public function restoreRevision(string $revisionId): bool
{
    return $this->getRevisionProvider()->restoreRevision($this->storage_path, $revisionId);
}
```

---

### 6. Create Controller Endpoints

**Add to PagesController and PostsController:**

```php
/**
 * Get paginated revision history
 */
public function revisions(Page $page, Request $request): JsonResponse
{
    $page_num = $request->integer('page', 1);
    $per_page = $request->integer('per_page', 10);

    $revisions = $page->revisionHistory($page_num, $per_page);

    return response()->json($revisions->toArray());
}

/**
 * Get a specific revision
 */
public function showRevision(Page $page, string $revisionId): JsonResponse
{
    $revision = $page->getRevision($revisionId);

    if (!$revision) {
        return response()->json(['error' => 'Revision not found'], 404);
    }

    return response()->json($revision->toArray());
}

/**
 * Restore a specific revision
 */
public function restoreRevision(Page $page, string $revisionId): RedirectResponse
{
    if (!$page->restoreRevision($revisionId)) {
        return back()->with('error', 'Failed to restore revision');
    }

    return back()->with('success', 'Revision restored successfully');
}
```

---

### 7. Add Routes

```php
// In routes/admin/pages.php

Route::middleware(['auth', 'verified'])->prefix('admin/pages')->name('admin.pages.')->group(function () {
    // ... existing routes

    // Revision management
    Route::get('{page}/revisions', [PagesController::class, 'revisions'])->name('revisions');
    Route::get('{page}/revisions/{revisionId}', [PagesController::class, 'showRevision'])->name('revisions.show');
    Route::post('{page}/revisions/{revisionId}/restore', [PagesController::class, 'restoreRevision'])->name('revisions.restore');
});
```

---

### 8. Create Frontend Component

**File:** `resources/js/components/RevisionHistory.vue`

```vue
<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';

interface Revision {
  id: string;
  content: string;
  message: string | null;
  author: string | null;
  author_email: string | null;
  timestamp: string;
  metadata: any;
  is_current: boolean;
}

interface Props {
  pageId: number;
  storageDriver: string;
}

const props = defineProps<Props>();

const revisions = ref<Revision[]>([]);
const currentPage = ref(1);
const hasMore = ref(false);
const loading = ref(false);
const selectedRevision = ref<Revision | null>(null);
const showPreview = ref(false);

const loadRevisions = async (page: number = 1) => {
  loading.value = true;

  try {
    const response = await fetch(`/admin/pages/${props.pageId}/revisions?page=${page}&per_page=10`);
    const data = await response.json();

    if (page === 1) {
      revisions.value = data.data;
    } else {
      revisions.value.push(...data.data);
    }

    currentPage.value = data.meta.current_page;
    hasMore.value = data.meta.has_more;
  } catch (error) {
    console.error('Failed to load revisions:', error);
  } finally {
    loading.value = false;
  }
};

const previewRevision = async (revision: Revision) => {
  selectedRevision.value = revision;
  showPreview.value = true;
};

const restoreRevision = (revisionId: string) => {
  if (!confirm('Are you sure you want to restore this revision? This will create a new version with this content.')) {
    return;
  }

  router.post(`/admin/pages/${props.pageId}/revisions/${revisionId}/restore`);
};

const loadMore = () => {
  if (!loading.value && hasMore.value) {
    loadRevisions(currentPage.value + 1);
  }
};

onMounted(() => {
  loadRevisions();
});
</script>

<template>
  <div class="revision-history">
    <div class="revision-header">
      <h3>Revision History</h3>
      <span class="storage-badge">{{ storageDriver }}</span>
    </div>

    <div class="revision-list">
      <div
        v-for="revision in revisions"
        :key="revision.id"
        class="revision-item"
        :class="{ 'is-current': revision.is_current }"
      >
        <div class="revision-meta">
          <div class="revision-timestamp">
            {{ new Date(revision.timestamp).toLocaleString() }}
          </div>
          <div v-if="revision.author" class="revision-author">
            {{ revision.author }}
            <span v-if="revision.author_email" class="author-email">
              ({{ revision.author_email }})
            </span>
          </div>
          <div v-if="revision.message" class="revision-message">
            {{ revision.message }}
          </div>
          <div v-if="revision.is_current" class="current-badge">
            Current Version
          </div>
        </div>

        <div class="revision-actions">
          <button
            @click="previewRevision(revision)"
            class="btn-preview"
          >
            Preview
          </button>
          <button
            v-if="!revision.is_current"
            @click="restoreRevision(revision.id)"
            class="btn-restore"
          >
            Restore
          </button>
        </div>
      </div>

      <button
        v-if="hasMore"
        @click="loadMore"
        :disabled="loading"
        class="btn-load-more"
      >
        {{ loading ? 'Loading...' : 'Load More' }}
      </button>
    </div>

    <!-- Preview Modal -->
    <div v-if="showPreview && selectedRevision" class="revision-preview-modal">
      <div class="modal-overlay" @click="showPreview = false"></div>
      <div class="modal-content">
        <div class="modal-header">
          <h3>Revision Preview</h3>
          <button @click="showPreview = false" class="btn-close">&times;</button>
        </div>
        <div class="modal-body">
          <div class="preview-meta">
            <strong>Date:</strong> {{ new Date(selectedRevision.timestamp).toLocaleString() }}<br>
            <strong v-if="selectedRevision.author">Author:</strong> {{ selectedRevision.author }}<br>
            <strong v-if="selectedRevision.message">Message:</strong> {{ selectedRevision.message }}
          </div>
          <div class="preview-content">
            <pre>{{ selectedRevision.content }}</pre>
          </div>
        </div>
        <div class="modal-footer">
          <button @click="showPreview = false" class="btn-cancel">Close</button>
          <button
            v-if="!selectedRevision.is_current"
            @click="restoreRevision(selectedRevision.id)"
            class="btn-restore"
          >
            Restore This Version
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.revision-history {
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  padding: 1.5rem;
  background: white;
}

.revision-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}

.storage-badge {
  padding: 0.25rem 0.75rem;
  background: #3b82f6;
  color: white;
  border-radius: 4px;
  font-size: 0.875rem;
  font-weight: 500;
}

.revision-list {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.revision-item {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  padding: 1rem;
  border: 1px solid #e5e7eb;
  border-radius: 6px;
  transition: all 0.2s;
}

.revision-item:hover {
  border-color: #3b82f6;
  box-shadow: 0 2px 4px rgba(59, 130, 246, 0.1);
}

.revision-item.is-current {
  background: #eff6ff;
  border-color: #3b82f6;
}

.revision-meta {
  flex: 1;
}

.revision-timestamp {
  font-weight: 600;
  color: #1f2937;
  margin-bottom: 0.25rem;
}

.revision-author {
  font-size: 0.875rem;
  color: #6b7280;
}

.author-email {
  color: #9ca3af;
}

.revision-message {
  margin-top: 0.5rem;
  font-size: 0.875rem;
  color: #4b5563;
  font-style: italic;
}

.current-badge {
  display: inline-block;
  margin-top: 0.5rem;
  padding: 0.25rem 0.5rem;
  background: #10b981;
  color: white;
  border-radius: 4px;
  font-size: 0.75rem;
  font-weight: 600;
}

.revision-actions {
  display: flex;
  gap: 0.5rem;
}

.btn-preview,
.btn-restore {
  padding: 0.5rem 1rem;
  border-radius: 4px;
  font-size: 0.875rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-preview {
  background: #f3f4f6;
  color: #374151;
  border: 1px solid #d1d5db;
}

.btn-preview:hover {
  background: #e5e7eb;
}

.btn-restore {
  background: #3b82f6;
  color: white;
  border: 1px solid #3b82f6;
}

.btn-restore:hover {
  background: #2563eb;
}

.btn-load-more {
  margin-top: 1rem;
  padding: 0.75rem;
  width: 100%;
  background: #f9fafb;
  border: 1px solid #e5e7eb;
  border-radius: 6px;
  font-weight: 500;
  color: #374151;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-load-more:hover:not(:disabled) {
  background: #f3f4f6;
}

.btn-load-more:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.revision-preview-modal {
  position: fixed;
  inset: 0;
  z-index: 50;
  display: flex;
  align-items: center;
  justify-content: center;
}

.modal-overlay {
  position: absolute;
  inset: 0;
  background: rgba(0, 0, 0, 0.5);
}

.modal-content {
  position: relative;
  background: white;
  border-radius: 8px;
  width: 90%;
  max-width: 800px;
  max-height: 80vh;
  display: flex;
  flex-direction: column;
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.5rem;
  border-bottom: 1px solid #e5e7eb;
}

.btn-close {
  font-size: 1.5rem;
  color: #6b7280;
  background: none;
  border: none;
  cursor: pointer;
  line-height: 1;
}

.modal-body {
  flex: 1;
  overflow-y: auto;
  padding: 1.5rem;
}

.preview-meta {
  margin-bottom: 1rem;
  padding: 1rem;
  background: #f9fafb;
  border-radius: 6px;
  font-size: 0.875rem;
  line-height: 1.6;
}

.preview-content {
  border: 1px solid #e5e7eb;
  border-radius: 6px;
  padding: 1rem;
  background: #f9fafb;
}

.preview-content pre {
  white-space: pre-wrap;
  word-wrap: break-word;
  font-family: monospace;
  font-size: 0.875rem;
  margin: 0;
}

.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 0.75rem;
  padding: 1.5rem;
  border-top: 1px solid #e5e7eb;
}

.btn-cancel {
  padding: 0.5rem 1rem;
  background: #f3f4f6;
  color: #374151;
  border: 1px solid #d1d5db;
  border-radius: 4px;
  font-weight: 500;
  cursor: pointer;
}

.btn-cancel:hover {
  background: #e5e7eb;
}
</style>
```

---

## Testing Plan

### Unit Tests

1. **RevisionProviderFactory Test**
   - Test factory creates correct provider for each driver
   - Test exception thrown for unsupported driver

2. **DatabaseRevisionProvider Test**
   - Test getRevisions() pagination
   - Test getRevision() returns correct revision
   - Test restoreRevision() updates model
   - Test getLatestRevision()

3. **S3RevisionProvider Test**
   - Mock S3 client
   - Test bucket versioning check
   - Test fetching versions from S3
   - Test restoring a version

4. **GitHubRevisionProvider Test**
   - Mock GitHub API
   - Test commit history fetching
   - Test commit content retrieval
   - Test restoring to previous commit

### Integration Tests

1. **Page Revision Integration**
   - Create page with database storage
   - Update page multiple times
   - Fetch revision history
   - Restore to previous revision
   - Verify content matches

2. **S3 Revision Integration** (requires S3 test bucket)
   - Create page with S3 storage
   - Update content multiple times
   - Verify S3 versions exist
   - Restore previous version

3. **GitHub Revision Integration** (requires test repo)
   - Create page with GitHub storage
   - Commit changes
   - Fetch commit history
   - Restore to previous commit

---

## Migration Plan

### For Existing Pages

1. No database migration needed - existing revisions stay in DB
2. New pages use cloud-native revisions based on storage_driver
3. Pages can switch storage drivers - revisions migrate accordingly

### Backward Compatibility

- Database revisions continue to work as before
- No breaking changes to existing API
- Frontend gracefully handles all revision types

---

## Performance Considerations

### Caching

- Cache revision lists for 5 minutes
- Cache individual revisions for 30 minutes
- Invalidate cache on new updates

### Optimization

- Lazy load revision content (only when previewed)
- Use pagination (10 revisions per page)
- Implement virtual scrolling for large histories

### Rate Limiting

- Limit revision API calls to 60/minute per user
- Implement exponential backoff for cloud APIs

---

## Security

### Access Control

- Only authenticated users can view revisions
- Only authors + admins can restore revisions
- Validate revision IDs before fetching

### Data Privacy

- Don't expose sensitive metadata
- Sanitize author information
- Log all restoration actions

---

## Rollback Plan

If issues arise:
1. Keep database revisions as fallback
2. Add feature flag: `ENABLE_CLOUD_NATIVE_REVISIONS`
3. Gradual rollout by storage driver
4. Monitor error rates per driver

---

## Success Metrics

- ✅ Revision history loads in <2 seconds
- ✅ All 6 storage drivers have working revision providers
- ✅ 100% test coverage for revision system
- ✅ Zero data loss during restoration
- ✅ UI handles 1000+ revisions smoothly

---

## Next Phase

After Phase 2.5 completion, proceed to **Phase 2.6: Cloud Storage Editor Integration**
