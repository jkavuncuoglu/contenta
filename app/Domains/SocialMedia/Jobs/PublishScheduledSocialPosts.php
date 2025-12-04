<?php

declare(strict_types=1);

namespace App\Domains\SocialMedia\Jobs;

use App\Domains\SocialMedia\Services\SocialMediaServiceContract;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Job to publish all social posts that are due to be published.
 *
 * Runs every minute via Laravel scheduler.
 * Finds all posts with status='scheduled' and scheduled_at <= now()
 * and attempts to publish them to their respective platforms.
 */
class PublishScheduledSocialPosts implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     */
    public int $backoff = 60;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(SocialMediaServiceContract $service): void
    {
        Log::info('PublishScheduledSocialPosts job started');

        try {
            $publishedPosts = $service->publishDuePosts();

            Log::info('PublishScheduledSocialPosts job completed', [
                'count' => count($publishedPosts),
                'post_ids' => array_map(fn ($p) => $p->id, $publishedPosts),
            ]);
        } catch (\Exception $e) {
            Log::error('PublishScheduledSocialPosts job failed', [
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
        Log::error('PublishScheduledSocialPosts job failed permanently', [
            'error' => $exception->getMessage(),
        ]);
    }
}
