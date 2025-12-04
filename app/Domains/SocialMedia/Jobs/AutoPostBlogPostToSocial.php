<?php

declare(strict_types=1);

namespace App\Domains\SocialMedia\Jobs;

use App\Domains\ContentManagement\Posts\Models\Post;
use App\Domains\SocialMedia\Services\SchedulerServiceContract;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Job to auto-post a blog post to social media.
 *
 * Triggered when a blog post is published.
 * Queues the blog post for all accounts with auto_post_enabled=true,
 * then immediately processes the immediate queue.
 */
class AutoPostBlogPostToSocial implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     */
    public int $backoff = 30;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Post $blogPost
    ) {}

    /**
     * Execute the job.
     */
    public function handle(SchedulerServiceContract $scheduler): void
    {
        Log::info('AutoPostBlogPostToSocial job started', [
            'blog_post_id' => $this->blogPost->id,
            'blog_post_title' => $this->blogPost->title,
        ]);

        try {
            // Queue the blog post for all enabled accounts
            $queueEntries = $scheduler->queueBlogPost($this->blogPost);

            Log::info('Blog post queued for social media', [
                'blog_post_id' => $this->blogPost->id,
                'queue_entries_count' => count($queueEntries),
            ]);

            // Process immediate queue (accounts with auto_post_mode='immediate')
            $processedCount = $scheduler->processImmediateQueue();

            Log::info('Immediate queue processed', [
                'blog_post_id' => $this->blogPost->id,
                'processed_count' => $processedCount,
            ]);

            Log::info('AutoPostBlogPostToSocial job completed', [
                'blog_post_id' => $this->blogPost->id,
                'queued' => count($queueEntries),
                'processed_immediately' => $processedCount,
            ]);
        } catch (\Exception $e) {
            Log::error('AutoPostBlogPostToSocial job failed', [
                'blog_post_id' => $this->blogPost->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('AutoPostBlogPostToSocial job failed permanently', [
            'blog_post_id' => $this->blogPost->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
