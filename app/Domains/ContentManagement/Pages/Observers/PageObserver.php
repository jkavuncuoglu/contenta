<?php

declare(strict_types=1);

namespace App\Domains\ContentManagement\Pages\Observers;

use App\Domains\ContentManagement\Pages\Models\Page;
use App\Domains\ContentManagement\Pages\Models\PageRevision;
use Illuminate\Support\Str;

class PageObserver
{
    /**
     * Handle the Page "creating" event
     */
    public function creating(Page $page): void
    {
        // Generate slug if not provided
        if (empty($page->slug)) {
            $page->slug = Str::slug($page->title);
        }

        // Set storage path if not provided
        if (empty($page->storage_path)) {
            $page->storage_path = $page->generateStoragePath();
        }
    }

    /**
     * Handle the Page "updating" event
     */
    public function updating(Page $page): void
    {
        // Create revision before update
        if ($page->isDirty(['title', 'slug', 'storage_path', 'storage_driver'])) {
            $this->createRevision($page);
        }
    }

    /**
     * Handle the Page "deleting" event
     */
    public function deleting(Page $page): void
    {
        // Delete content from storage
        if ($page->storage_path && $page->storage_driver) {
            try {
                $storage = app(\App\Domains\ContentManagement\ContentStorage\Services\ContentStorageManager::class)
                    ->driver($page->storage_driver);
                $storage->delete($page->storage_path);
            } catch (\Exception $e) {
                \Log::warning('Failed to delete page content from storage', [
                    'page_id' => $page->id,
                    'storage_path' => $page->storage_path,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Create a revision of the page
     */
    protected function createRevision(Page $page): void
    {
        $latestRevision = $page->revisions()->first();
        $revisionNumber = $latestRevision ? $latestRevision->revision_number + 1 : 1;

        PageRevision::create([
            'page_id' => $page->id,
            'revision_number' => $revisionNumber,
            'title' => $page->getOriginal('title'),
            'slug' => $page->getOriginal('slug'),
            'storage_driver' => $page->getOriginal('storage_driver'),
            'storage_path' => $page->getOriginal('storage_path'),
            'meta_title' => $page->getOriginal('meta_title'),
            'meta_description' => $page->getOriginal('meta_description'),
            'created_by' => auth()->id(),
        ]);
    }
}
