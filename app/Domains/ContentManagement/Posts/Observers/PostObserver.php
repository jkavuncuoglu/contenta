<?php

declare(strict_types=1);

namespace App\Domains\ContentManagement\Posts\Observers;

use App\Domains\ContentManagement\ContentStorage\ContentStorageManager;
use App\Domains\ContentManagement\Posts\Models\Post;
use Illuminate\Support\Str;

class PostObserver
{
    public function __construct(
        private ContentStorageManager $storageManager
    ) {}

    /**
     * Handle the Post "creating" event.
     */
    public function creating(Post $post): void
    {
        // Auto-generate slug if not provided
        if (empty($post->slug)) {
            $post->slug = Str::slug($post->title);
        }

        // Auto-generate storage path if using cloud storage and path not set
        if ($post->storage_driver !== 'database' && empty($post->storage_path)) {
            $post->storage_path = $post->generateStoragePath();
        }

        // Set default storage driver if not provided
        if (empty($post->storage_driver)) {
            $post->storage_driver = 'database';
        }
    }

    /**
     * Handle the Post "created" event.
     */
    public function created(Post $post): void
    {
        // Nothing to do after creation
        // Content is written via setContent() method
    }

    /**
     * Handle the Post "updating" event.
     */
    public function updating(Post $post): void
    {
        // Regenerate slug if title changed
        if ($post->isDirty('title') && empty($post->getOriginal('slug'))) {
            $post->slug = Str::slug($post->title);
        }
    }

    /**
     * Handle the Post "updated" event.
     */
    public function updated(Post $post): void
    {
        // Nothing to do after update
        // Content is written via setContent() method
    }

    /**
     * Handle the Post "deleting" event.
     */
    public function deleting(Post $post): void
    {
        // Delete content from storage if using cloud storage
        if ($post->storage_driver !== 'database' && $post->storage_path) {
            try {
                $driver = $this->storageManager->driver($post->storage_driver);
                $driver->delete($post->storage_path);

                \Log::info('Post content deleted from storage', [
                    'post_id' => $post->id,
                    'storage_driver' => $post->storage_driver,
                    'storage_path' => $post->storage_path,
                ]);

            } catch (\Exception $e) {
                \Log::error('Failed to delete post content from storage', [
                    'post_id' => $post->id,
                    'storage_driver' => $post->storage_driver,
                    'storage_path' => $post->storage_path,
                    'error' => $e->getMessage(),
                ]);

                // Don't throw exception - allow post deletion to proceed
                // The orphaned file can be cleaned up later
            }
        }
    }

    /**
     * Handle the Post "deleted" event.
     */
    public function deleted(Post $post): void
    {
        // Nothing to do after deletion
    }

    /**
     * Handle the Post "forceDeleted" event.
     */
    public function forceDeleted(Post $post): void
    {
        // Same as deleting - content already removed
    }
}
