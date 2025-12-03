# Phase 2.6: Cloud Storage Editor Integration

**Created:** 2025-12-03
**Status:** Planned
**Priority:** High
**Estimated Duration:** 5-7 hours

---

## Overview

Make the post and page editor capable of creating and updating files directly in cloud storage (S3, Azure, GCS) or GitHub when selected as the storage backend. This includes:

- ✅ Direct file uploads to cloud storage
- ✅ Real-time content syncing
- ✅ Automatic commit creation for GitHub
- ✅ Preview before publishing
- ✅ Validation before saving
- ✅ Error handling and rollback

---

## Architecture Design

### Editor-to-Storage Flow

```
┌─────────────────────────────────────────────────────────┐
│                    Editor Component                      │
│  (Create.vue / Edit.vue)                                │
│                                                          │
│  [Markdown Editor] → [Validate] → [Save/Publish]       │
│         ↓                ↓              ↓               │
│    Auto-save      Shortcode      Storage API            │
│    (draft)        Validation     (S3/GitHub/etc)        │
└─────────────────────────────────────────────────────────┘
                           ↓
┌─────────────────────────────────────────────────────────┐
│              Storage Backend (Polymorphic)              │
├─────────────────────────────────────────────────────────┤
│                                                         │
│  Database/Local:  → Save to DB/filesystem              │
│  S3/Azure/GCS:    → PUT object to cloud bucket         │
│  GitHub:          → Create/update file via commit      │
│                                                         │
└─────────────────────────────────────────────────────────┘
```

---

## Implementation Plan

### 1. Update ContentStorage Repositories

Add `write()` method optimization for direct cloud storage:

#### 1.1 S3Repository

**File:** `app/Domains/ContentManagement/ContentStorage/Repositories/S3Repository.php`

```php
public function write(string $path, string $content, array $metadata = []): bool
{
    try {
        $this->client->putObject([
            'Bucket' => $this->config['bucket'],
            'Key' => $path,
            'Body' => $content,
            'ContentType' => 'text/markdown',
            'Metadata' => array_merge([
                'created-by' => auth()->user()?->name ?? 'system',
                'created-at' => now()->toIso8601String(),
            ], $metadata),
            'ServerSideEncryption' => $this->config['encryption'] ?? 'AES256',
            // Enable versioning metadata
            'StorageClass' => $this->config['storage_class'] ?? 'STANDARD',
        ]);

        Log::info('Content written to S3', [
            'bucket' => $this->config['bucket'],
            'path' => $path,
            'size' => strlen($content),
        ]);

        return true;

    } catch (S3Exception $e) {
        Log::error('Failed to write to S3', [
            'bucket' => $this->config['bucket'],
            'path' => $path,
            'error' => $e->getMessage(),
            'code' => $e->getAwsErrorCode(),
        ]);

        throw new StorageException(
            "Failed to write to S3: {$e->getAwsErrorMessage()}",
            $e->getStatusCode(),
            $e
        );
    }
}

/**
 * Check if bucket has versioning enabled
 */
public function isBucketVersioningEnabled(): bool
{
    try {
        $result = $this->client->getBucketVersioning([
            'Bucket' => $this->config['bucket'],
        ]);

        return ($result['Status'] ?? '') === 'Enabled';

    } catch (S3Exception $e) {
        Log::warning('Failed to check bucket versioning', [
            'bucket' => $this->config['bucket'],
            'error' => $e->getMessage(),
        ]);

        return false;
    }
}
```

#### 1.2 AzureBlobRepository

**File:** `app/Domains/ContentManagement/ContentStorage/Repositories/AzureBlobRepository.php`

```php
public function write(string $path, string $content, array $metadata = []): bool
{
    try {
        $blobClient = $this->getContainerClient()->getBlobClient($path);

        $blobClient->upload($content, [
            'metadata' => array_merge([
                'created_by' => auth()->user()?->name ?? 'system',
                'created_at' => now()->toIso8601String(),
                'content_type' => 'text/markdown',
            ], $metadata),
            'blobContentType' => 'text/markdown',
        ]);

        Log::info('Content written to Azure Blob', [
            'container' => $this->config['container'],
            'path' => $path,
            'size' => strlen($content),
        ]);

        return true;

    } catch (\Exception $e) {
        Log::error('Failed to write to Azure Blob', [
            'container' => $this->config['container'],
            'path' => $path,
            'error' => $e->getMessage(),
        ]);

        throw new StorageException(
            "Failed to write to Azure Blob: {$e->getMessage()}",
            0,
            $e
        );
    }
}

/**
 * Check if container has versioning enabled
 */
public function isVersioningEnabled(): bool
{
    try {
        $properties = $this->getContainerClient()->getProperties();
        return $properties->getIsVersioningEnabled() ?? false;

    } catch (\Exception $e) {
        Log::warning('Failed to check versioning', [
            'container' => $this->config['container'],
            'error' => $e->getMessage(),
        ]);

        return false;
    }
}
```

#### 1.3 GCSRepository

**File:** `app/Domains/ContentManagement/ContentStorage/Repositories/GCSRepository.php`

```php
public function write(string $path, string $content, array $metadata = []): bool
{
    try {
        $bucket = $this->client->bucket($this->config['bucket']);

        $object = $bucket->upload($content, [
            'name' => $path,
            'metadata' => array_merge([
                'created-by' => auth()->user()?->name ?? 'system',
                'created-at' => now()->toIso8601String(),
            ], $metadata),
            'predefinedAcl' => $this->config['acl'] ?? 'private',
            'contentType' => 'text/markdown',
        ]);

        Log::info('Content written to GCS', [
            'bucket' => $this->config['bucket'],
            'path' => $path,
            'size' => strlen($content),
        ]);

        return true;

    } catch (\Exception $e) {
        Log::error('Failed to write to GCS', [
            'bucket' => $this->config['bucket'],
            'path' => $path,
            'error' => $e->getMessage(),
        ]);

        throw new StorageException(
            "Failed to write to GCS: {$e->getMessage()}",
            0,
            $e
        );
    }
}

/**
 * Check if bucket has versioning enabled
 */
public function isVersioningEnabled(): bool
{
    try {
        $bucket = $this->client->bucket($this->config['bucket']);
        $info = $bucket->info();

        return $info['versioning']['enabled'] ?? false;

    } catch (\Exception $e) {
        Log::warning('Failed to check versioning', [
            'bucket' => $this->config['bucket'],
            'error' => $e->getMessage(),
        ]);

        return false;
    }
}
```

#### 1.4 GitHubRepository

**File:** `app/Domains/ContentManagement/ContentStorage/Repositories/GithubRepository.php`

```php
public function write(string $path, string $content, array $metadata = []): bool
{
    try {
        $owner = $this->config['owner'];
        $repo = $this->config['repo'];
        $branch = $this->config['branch'] ?? 'main';

        // Check if file exists to get SHA for update
        $sha = null;
        try {
            $existingFile = $this->client->repo()->contents()->show(
                $owner,
                $repo,
                $path,
                $branch
            );
            $sha = $existingFile['sha'];
        } catch (\Exception $e) {
            // File doesn't exist, creating new
            $sha = null;
        }

        // Commit message
        $message = $metadata['commit_message'] ?? ($sha
            ? "Update {$path}"
            : "Create {$path}");

        $author = [
            'name' => auth()->user()?->name ?? $this->config['author_name'] ?? 'Contenta CMS',
            'email' => auth()->user()?->email ?? $this->config['author_email'] ?? 'noreply@contenta.local',
        ];

        // Create or update file
        $result = $this->client->repo()->contents()->createOrUpdate(
            $owner,
            $repo,
            $path,
            [
                'message' => $message,
                'content' => base64_encode($content),
                'branch' => $branch,
                'sha' => $sha,
                'author' => $author,
                'committer' => $author,
            ]
        );

        Log::info('Content written to GitHub', [
            'repo' => "{$owner}/{$repo}",
            'path' => $path,
            'commit' => $result['commit']['sha'] ?? 'unknown',
            'size' => strlen($content),
        ]);

        return true;

    } catch (\Exception $e) {
        Log::error('Failed to write to GitHub', [
            'repo' => "{$owner}/{$repo}",
            'path' => $path,
            'error' => $e->getMessage(),
        ]);

        throw new StorageException(
            "Failed to write to GitHub: {$e->getMessage()}",
            0,
            $e
        );
    }
}

/**
 * Delete a file from GitHub
 */
public function delete(string $path): bool
{
    try {
        $owner = $this->config['owner'];
        $repo = $this->config['repo'];
        $branch = $this->config['branch'] ?? 'main';

        // Get current file SHA
        $file = $this->client->repo()->contents()->show(
            $owner,
            $repo,
            $path,
            $branch
        );

        // Delete file
        $this->client->repo()->contents()->remove(
            $owner,
            $repo,
            $path,
            [
                'message' => "Delete {$path}",
                'sha' => $file['sha'],
                'branch' => $branch,
            ]
        );

        Log::info('Content deleted from GitHub', [
            'repo' => "{$owner}/{$repo}",
            'path' => $path,
        ]);

        return true;

    } catch (\Exception $e) {
        Log::error('Failed to delete from GitHub', [
            'repo' => "{$owner}/{$repo}",
            'path' => $path,
            'error' => $e->getMessage(),
        ]);

        return false;
    }
}
```

---

### 2. Update PageService and PostService

Add storage-aware save methods:

**File:** `app/Domains/ContentManagement/Pages/Services/PageService.php`

```php
public function createPage(array $data): Page
{
    DB::beginTransaction();

    try {
        // Generate storage path if not provided
        if (!isset($data['storage_path'])) {
            $data['storage_path'] = $this->generateStoragePath($data['title']);
        }

        // Create page record
        $page = Page::create([
            'title' => $data['title'],
            'slug' => $data['slug'] ?? Str::slug($data['title']),
            'storage_driver' => $data['storage_driver'] ?? 'database',
            'storage_path' => $data['storage_path'],
            'status' => $data['status'] ?? 'draft',
            'author_id' => auth()->id(),
            'parent_id' => $data['parent_id'] ?? null,
            'template' => $data['template'] ?? 'default',
        ]);

        // Write content to storage
        $content = new ContentData(
            markdown: $data['content_markdown'],
            html: null, // Will be generated on publish
        );

        $storage = $this->storageManager->driver($page->storage_driver);

        // Add metadata for Git-based storage
        $metadata = [];
        if (in_array($page->storage_driver, ['github', 'gitlab', 'bitbucket'])) {
            $metadata['commit_message'] = "Create page: {$page->title}";
        }

        $storage->write($page->storage_path, json_encode($content), $metadata);

        DB::commit();

        event(new PageCreated($page));

        return $page->fresh();

    } catch (\Exception $e) {
        DB::rollBack();

        Log::error('Failed to create page', [
            'title' => $data['title'] ?? 'unknown',
            'storage_driver' => $data['storage_driver'] ?? 'unknown',
            'error' => $e->getMessage(),
        ]);

        throw new \RuntimeException("Failed to create page: {$e->getMessage()}", 0, $e);
    }
}

public function updatePage(Page $page, array $data): Page
{
    DB::beginTransaction();

    try {
        // Update page attributes
        $page->update([
            'title' => $data['title'] ?? $page->title,
            'slug' => $data['slug'] ?? $page->slug,
            'status' => $data['status'] ?? $page->status,
            'template' => $data['template'] ?? $page->template,
            'parent_id' => $data['parent_id'] ?? $page->parent_id,
        ]);

        // Update content in storage if provided
        if (isset($data['content_markdown'])) {
            $content = new ContentData(
                markdown: $data['content_markdown'],
                html: null,
            );

            $storage = $this->storageManager->driver($page->storage_driver);

            // Add metadata for Git-based storage
            $metadata = [];
            if (in_array($page->storage_driver, ['github', 'gitlab', 'bitbucket'])) {
                $metadata['commit_message'] = $data['commit_message'] ?? "Update page: {$page->title}";
            }

            $storage->write($page->storage_path, json_encode($content), $metadata);
        }

        DB::commit();

        event(new PageUpdated($page));

        return $page->fresh();

    } catch (\Exception $e) {
        DB::rollBack();

        Log::error('Failed to update page', [
            'page_id' => $page->id,
            'error' => $e->getMessage(),
        ]);

        throw new \RuntimeException("Failed to update page: {$e->getMessage()}", 0, $e);
    }
}

private function generateStoragePath(string $title): string
{
    $slug = Str::slug($title);
    $date = now()->format('Y/m');

    return "pages/{$date}/{$slug}.md";
}
```

---

### 3. Update Controllers for Cloud Storage

**File:** `app/Domains/ContentManagement/Pages/Http/Controllers/Admin/PagesController.php`

```php
public function store(Request $request): RedirectResponse
{
    $validated = $request->validate([
        'title' => 'required|string|max:255',
        'slug' => 'nullable|string|unique:pages,slug',
        'content_markdown' => 'required|string',
        'storage_driver' => 'required|in:database,local,s3,azure,gcs,github,gitlab,bitbucket',
        'storage_path' => 'nullable|string',
        'status' => 'in:draft,published,archived',
        'template' => 'nullable|string',
        'parent_id' => 'nullable|exists:pages,id',
        'commit_message' => 'nullable|string|max:255', // For Git-based storage
    ]);

    try {
        // Validate markdown content (shortcodes)
        $validationResult = $this->validateContent($validated['content_markdown']);

        if (!$validationResult['valid']) {
            return back()
                ->withInput()
                ->with('error', 'Content validation failed: ' . implode(', ', array_column($validationResult['errors'], 'message')));
        }

        $page = $this->pageService->createPage($validated);

        return redirect()
            ->route('admin.pages.edit', $page)
            ->with('success', 'Page created successfully!');

    } catch (StorageException $e) {
        return back()
            ->withInput()
            ->with('error', 'Storage error: ' . $e->getMessage());

    } catch (\Exception $e) {
        Log::error('Page creation failed', [
            'title' => $validated['title'],
            'error' => $e->getMessage(),
        ]);

        return back()
            ->withInput()
            ->with('error', 'Failed to create page. Please try again.');
    }
}

public function update(Request $request, Page $page): RedirectResponse
{
    $validated = $request->validate([
        'title' => 'required|string|max:255',
        'slug' => ['required', 'string', Rule::unique('pages', 'slug')->ignore($page->id)],
        'content_markdown' => 'required|string',
        'status' => 'in:draft,published,archived',
        'template' => 'nullable|string',
        'parent_id' => 'nullable|exists:pages,id',
        'commit_message' => 'nullable|string|max:255', // For Git-based storage
    ]);

    try {
        // Validate markdown content (shortcodes)
        $validationResult = $this->validateContent($validated['content_markdown']);

        if (!$validationResult['valid']) {
            return back()
                ->withInput()
                ->with('error', 'Content validation failed: ' . implode(', ', array_column($validationResult['errors'], 'message')));
        }

        $this->pageService->updatePage($page, $validated);

        return back()->with('success', 'Page updated successfully!');

    } catch (StorageException $e) {
        return back()
            ->withInput()
            ->with('error', 'Storage error: ' . $e->getMessage());

    } catch (\Exception $e) {
        Log::error('Page update failed', [
            'page_id' => $page->id,
            'error' => $e->getMessage(),
        ]);

        return back()
            ->withInput()
            ->with('error', 'Failed to update page. Please try again.');
    }
}

/**
 * Auto-save draft (AJAX endpoint)
 */
public function autosave(Request $request, Page $page): JsonResponse
{
    try {
        $validated = $request->validate([
            'content_markdown' => 'required|string',
        ]);

        // Only update if status is draft
        if ($page->status !== 'draft') {
            return response()->json([
                'success' => false,
                'message' => 'Auto-save only works for draft pages',
            ], 400);
        }

        $this->pageService->updatePage($page, [
            'content_markdown' => $validated['content_markdown'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Draft saved',
            'timestamp' => now()->toIso8601String(),
        ]);

    } catch (\Exception $e) {
        Log::error('Auto-save failed', [
            'page_id' => $page->id,
            'error' => $e->getMessage(),
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Auto-save failed',
        ], 500);
    }
}

private function validateContent(string $markdown): array
{
    try {
        $this->markdownRenderService->render($markdown);

        return ['valid' => true, 'errors' => []];

    } catch (RenderException $e) {
        return [
            'valid' => false,
            'errors' => [[
                'type' => 'render',
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'column' => $e->getColumn(),
            ]],
        ];

    } catch (ParseException $e) {
        return [
            'valid' => false,
            'errors' => [[
                'type' => 'parse',
                'message' => $e->getMessage(),
                'line' => $e->getSourceLine(),
                'column' => $e->getSourceColumn(),
            ]],
        ];

    } catch (\Exception $e) {
        return [
            'valid' => false,
            'errors' => [[
                'type' => 'unknown',
                'message' => $e->getMessage(),
                'line' => null,
                'column' => null,
            ]],
        ];
    }
}
```

---

### 4. Add Auto-Save Functionality

**File:** `resources/js/composables/useAutoSave.ts`

```typescript
import { ref, watch, onBeforeUnmount } from 'vue';
import { debounce } from 'lodash-es';
import { router } from '@inertiajs/vue3';

interface AutoSaveOptions {
  url: string;
  interval?: number; // milliseconds
  enabled?: boolean;
}

export function useAutoSave(content: Ref<string>, options: AutoSaveOptions) {
  const lastSaved = ref<Date | null>(null);
  const isSaving = ref(false);
  const saveError = ref<string | null>(null);
  const enabled = ref(options.enabled ?? true);

  const save = async () => {
    if (!enabled.value || isSaving.value) return;

    isSaving.value = true;
    saveError.value = null;

    try {
      const response = await fetch(options.url, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '',
        },
        body: JSON.stringify({
          content_markdown: content.value,
        }),
      });

      if (!response.ok) {
        throw new Error('Auto-save failed');
      }

      const data = await response.json();
      lastSaved.value = new Date(data.timestamp);

    } catch (error) {
      console.error('Auto-save error:', error);
      saveError.value = 'Failed to auto-save';
    } finally {
      isSaving.value = false;
    }
  };

  // Debounced save function
  const debouncedSave = debounce(save, options.interval ?? 5000);

  // Watch content changes
  const stopWatcher = watch(content, () => {
    if (enabled.value) {
      debouncedSave();
    }
  });

  // Cleanup
  onBeforeUnmount(() => {
    stopWatcher();
    debouncedSave.cancel();
  });

  return {
    lastSaved,
    isSaving,
    saveError,
    enabled,
    save: debouncedSave,
  };
}
```

---

### 5. Update Editor Component

**File:** `resources/js/pages/Admin/Pages/Edit.vue`

```vue
<script setup lang="ts">
import { ref, computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { useAutoSave } from '@/composables/useAutoSave';
import { useShortcodeValidation } from '@/composables/useShortcodeValidation';

interface Props {
  page: {
    id: number;
    title: string;
    slug: string;
    content_markdown: string;
    storage_driver: string;
    storage_path: string;
    status: string;
    template: string | null;
    parent_id: number | null;
  };
  storageDrivers: Array<{ value: string; label: string }>;
}

const props = defineProps<Props>();

const form = useForm({
  title: props.page.title,
  slug: props.page.slug,
  content_markdown: props.page.content_markdown,
  status: props.page.status,
  template: props.page.template,
  parent_id: props.page.parent_id,
  commit_message: '', // For Git-based storage
});

const contentRef = computed(() => form.content_markdown);

// Auto-save for drafts
const autoSaveEnabled = computed(() => props.page.status === 'draft');

const { lastSaved, isSaving, saveError } = useAutoSave(contentRef, {
  url: `/admin/pages/${props.page.id}/autosave`,
  interval: 5000, // 5 seconds
  enabled: autoSaveEnabled.value,
});

// Validation
const { isValid, errors, isValidating } = useShortcodeValidation(
  contentRef,
  `/admin/pages/validate`
);

// Commit message required for Git-based storage
const isGitBased = computed(() =>
  ['github', 'gitlab', 'bitbucket'].includes(props.page.storage_driver)
);

const canSave = computed(() =>
  isValid.value &&
  !isValidating.value &&
  (!isGitBased.value || form.commit_message.length > 0)
);

const submit = () => {
  if (!canSave.value) {
    alert('Please fix validation errors and provide a commit message if required.');
    return;
  }

  form.put(`/admin/pages/${props.page.id}`);
};

const publish = () => {
  if (!canSave.value) {
    alert('Please fix validation errors before publishing.');
    return;
  }

  form.post(`/admin/pages/${props.page.id}/publish`);
};
</script>

<template>
  <div class="editor-container">
    <div class="editor-header">
      <h1>Edit Page</h1>

      <!-- Auto-save indicator -->
      <div v-if="autoSaveEnabled" class="auto-save-status">
        <span v-if="isSaving" class="saving">Saving...</span>
        <span v-else-if="lastSaved" class="saved">
          Saved {{ lastSaved.toLocaleTimeString() }}
        </span>
        <span v-else-if="saveError" class="error">{{ saveError }}</span>
      </div>

      <!-- Storage info -->
      <div class="storage-info">
        <span class="storage-driver">{{ page.storage_driver }}</span>
        <span class="storage-path">{{ page.storage_path }}</span>
      </div>
    </div>

    <form @submit.prevent="submit" class="editor-form">
      <!-- Title -->
      <div class="form-group">
        <label for="title">Title</label>
        <input
          id="title"
          v-model="form.title"
          type="text"
          required
        />
      </div>

      <!-- Slug -->
      <div class="form-group">
        <label for="slug">Slug</label>
        <input
          id="slug"
          v-model="form.slug"
          type="text"
          required
        />
      </div>

      <!-- Commit Message (for Git-based storage) -->
      <div v-if="isGitBased" class="form-group">
        <label for="commit_message">
          Commit Message <span class="required">*</span>
        </label>
        <input
          id="commit_message"
          v-model="form.commit_message"
          type="text"
          placeholder="Update page content"
          required
        />
        <p class="help-text">
          Required for {{ page.storage_driver }} storage
        </p>
      </div>

      <!-- Content Editor -->
      <div class="form-group full-width">
        <label for="content">Content</label>
        <textarea
          id="content"
          v-model="form.content_markdown"
          rows="20"
          class="markdown-editor"
        ></textarea>
      </div>

      <!-- Validation Errors -->
      <div v-if="errors.length > 0" class="validation-errors">
        <h3>Validation Errors</h3>
        <ul>
          <li v-for="(error, index) in errors" :key="index">
            <span class="error-type">{{ error.type }}</span>
            <span class="error-message">{{ error.message }}</span>
            <span v-if="error.line" class="error-location">
              Line {{ error.line }}{{ error.column ? `:${error.column}` : '' }}
            </span>
          </li>
        </ul>
      </div>

      <!-- Actions -->
      <div class="form-actions">
        <button
          type="submit"
          :disabled="!canSave || form.processing"
          class="btn-save"
        >
          {{ form.processing ? 'Saving...' : 'Save' }}
        </button>

        <button
          type="button"
          @click="publish"
          :disabled="!canSave || form.processing || page.status === 'published'"
          class="btn-publish"
        >
          Publish
        </button>
      </div>
    </form>
  </div>
</template>

<style scoped>
.editor-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 2rem;
}

.editor-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
}

.auto-save-status {
  font-size: 0.875rem;
}

.saving {
  color: #f59e0b;
}

.saved {
  color: #10b981;
}

.error {
  color: #ef4444;
}

.storage-info {
  display: flex;
  gap: 0.5rem;
  font-size: 0.875rem;
}

.storage-driver {
  padding: 0.25rem 0.75rem;
  background: #3b82f6;
  color: white;
  border-radius: 4px;
}

.storage-path {
  color: #6b7280;
  font-family: monospace;
}

.form-group {
  margin-bottom: 1.5rem;
}

.form-group label {
  display: block;
  font-weight: 600;
  margin-bottom: 0.5rem;
}

.required {
  color: #ef4444;
}

.help-text {
  font-size: 0.875rem;
  color: #6b7280;
  margin-top: 0.25rem;
}

.form-group input,
.form-group textarea {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  font-size: 1rem;
}

.markdown-editor {
  font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
  font-size: 0.875rem;
  line-height: 1.6;
}

.validation-errors {
  background: #fef2f2;
  border: 1px solid #fecaca;
  border-radius: 6px;
  padding: 1rem;
  margin-bottom: 1.5rem;
}

.validation-errors h3 {
  color: #dc2626;
  margin-bottom: 0.75rem;
}

.validation-errors ul {
  list-style: none;
  padding: 0;
}

.validation-errors li {
  display: flex;
  gap: 0.5rem;
  margin-bottom: 0.5rem;
  font-size: 0.875rem;
}

.error-type {
  padding: 0.125rem 0.5rem;
  background: #dc2626;
  color: white;
  border-radius: 4px;
  font-weight: 600;
  text-transform: uppercase;
  font-size: 0.75rem;
}

.error-message {
  flex: 1;
  color: #991b1b;
}

.error-location {
  color: #6b7280;
  font-family: monospace;
}

.form-actions {
  display: flex;
  gap: 1rem;
}

.btn-save,
.btn-publish {
  padding: 0.75rem 1.5rem;
  border-radius: 6px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-save {
  background: #3b82f6;
  color: white;
  border: none;
}

.btn-save:hover:not(:disabled) {
  background: #2563eb;
}

.btn-publish {
  background: #10b981;
  color: white;
  border: none;
}

.btn-publish:hover:not(:disabled) {
  background: #059669;
}

button:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}
</style>
```

---

## Testing Plan

### Unit Tests

1. **Storage Repository Tests**
   - Test S3Repository.write() creates object
   - Test AzureBlobRepository.write() uploads blob
   - Test GCSRepository.write() uploads object
   - Test GitHubRepository.write() creates commit

2. **PageService Tests**
   - Test createPage() with each storage driver
   - Test updatePage() writes to storage
   - Test error handling and rollback

### Integration Tests

1. **S3 Integration**
   - Create page with S3 storage
   - Verify object exists in bucket
   - Update page content
   - Verify new version in S3

2. **GitHub Integration**
   - Create page with GitHub storage
   - Verify commit exists
   - Update page
   - Verify new commit with commit message

3. **Editor Auto-Save**
   - Load page in editor
   - Make changes
   - Wait 5 seconds
   - Verify auto-save triggered
   - Verify storage updated

---

## Success Metrics

- ✅ All 6 storage drivers support direct write
- ✅ Auto-save triggers within 5 seconds
- ✅ GitHub commits have proper messages
- ✅ S3/Azure/GCS uploads succeed
- ✅ Validation prevents invalid content
- ✅ Rollback on storage failure
- ✅ 100% test coverage

---

## Security Considerations

- Validate all content before writing
- Sanitize commit messages
- Check storage permissions before write
- Rate limit auto-save requests
- Log all storage operations
- Encrypt sensitive metadata

---

## Next Steps

After completing Phase 2.5 and 2.6:
1. Continue with Phase 3 (Posts Integration)
2. Implement Phase 4 (Database Migrations)
3. Update Phase 5 (Controllers)
4. Complete Phase 6 (Frontend)
