<?php

declare(strict_types=1);

namespace App\Domains\SocialMedia\Jobs;

use App\Domains\SocialMedia\Services\SchedulerServiceContract;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Job to process daily scheduled social posts.
 *
 * Runs daily at the configured time (e.g., 9:00 AM).
 * Processes all queue entries for accounts with auto_post_mode='scheduled'
 * that are due to be posted.
 */
class ProcessDailyScheduledPosts implements ShouldQueue
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
    public function handle(SchedulerServiceContract $scheduler): void
    {
        Log::info('ProcessDailyScheduledPosts job started');

        try {
            $count = $scheduler->processScheduledQueue();

            Log::info('ProcessDailyScheduledPosts job completed', [
                'count' => $count,
            ]);
        } catch (\Exception $e) {
            Log::error('ProcessDailyScheduledPosts job failed', [
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
        Log::error('ProcessDailyScheduledPosts job failed permanently', [
            'error' => $exception->getMessage(),
        ]);
    }
}
