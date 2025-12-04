<?php

declare(strict_types=1);

namespace App\Domains\SocialMedia\Observers;

use App\Domains\ContentManagement\Posts\Models\Post;
use App\Domains\SocialMedia\Jobs\AutoPostBlogPostToSocial;
use Illuminate\Support\Facades\Log;

/**
 * Observer for Post model to trigger social media auto-posting.
 */
class PostObserver
{
    /**
     * Handle the Post "updated" event.
     *
     * Detects when a post is published (status changes to 'published')
     * and dispatches the AutoPostBlogPostToSocial job.
     */
    public function updated(Post $post): void
    {
        // Check if status changed to 'published'
        if ($post->isDirty('status') && $post->status === 'published') {
            Log::info('Post published, triggering auto-post job', [
                'post_id' => $post->id,
                'post_title' => $post->title,
            ]);

            // Dispatch job to queue blog post for social media
            AutoPostBlogPostToSocial::dispatch($post);
        }
    }

    /**
     * Handle the Post "created" event.
     *
     * If a post is created with status='published', trigger auto-posting.
     */
    public function created(Post $post): void
    {
        if ($post->status === 'published') {
            Log::info('Post created as published, triggering auto-post job', [
                'post_id' => $post->id,
                'post_title' => $post->title,
            ]);

            // Dispatch job to queue blog post for social media
            AutoPostBlogPostToSocial::dispatch($post);
        }
    }
}
