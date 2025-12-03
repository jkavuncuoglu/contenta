<?php

declare(strict_types=1);

namespace App\Domains\ContentManagement\ContentStorage\Jobs;

use App\Domains\ContentManagement\ContentStorage\Models\ContentMigration;
use App\Domains\ContentManagement\ContentStorage\Services\MigrationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

/**
 * Migrate Content Job
 *
 * Handles background content migration between storage backends.
 * Queued job with progress tracking and error handling.
 */
class MigrateContentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Content Migration record
     */
    public ContentMigration $migration;

    /**
     * Delete source content after migration
     */
    public bool $deleteSource;

    /**
     * Number of times the job may be attempted
     */
    public int $tries = 3;

    /**
     * Number of seconds the job can run before timing out
     */
    public int $timeout = 3600; // 1 hour

    /**
     * Create a new job instance
     *
     * @param ContentMigration $migration Migration record
     * @param bool $deleteSource Delete source content after successful migration
     */
    public function __construct(ContentMigration $migration, bool $deleteSource = false)
    {
        $this->migration = $migration;
        $this->deleteSource = $deleteSource;

        // Set queue name based on content type
        $this->onQueue('content-migrations');
    }

    /**
     * Execute the job
     */
    public function handle(MigrationService $migrationService): void
    {
        Log::info("Migration job started", [
            'migration_id' => $this->migration->id,
            'job_id' => $this->job?->uuid(),
        ]);

        try {
            // Execute migration
            $migrationService->executeMigration($this->migration, $this->deleteSource);

            // Send success notification (if configured)
            $this->sendSuccessNotification();

            Log::info("Migration job completed successfully", [
                'migration_id' => $this->migration->id,
                'migrated' => $this->migration->migrated_items,
                'failed' => $this->migration->failed_items,
            ]);
        } catch (\Exception $e) {
            Log::error("Migration job failed", [
                'migration_id' => $this->migration->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Send failure notification (if configured)
            $this->sendFailureNotification($e);

            throw $e;
        }
    }

    /**
     * Handle a job failure
     */
    public function failed(\Throwable $exception): void
    {
        // Mark migration as failed if all retries exhausted
        $this->migration->markAsFailed($exception->getMessage());

        Log::error("Migration job failed permanently", [
            'migration_id' => $this->migration->id,
            'error' => $exception->getMessage(),
            'attempts' => $this->attempts(),
        ]);

        // Send failure notification
        $this->sendFailureNotification($exception);
    }

    /**
     * Get the tags that should be assigned to the job
     *
     * @return array<int, string>
     */
    public function tags(): array
    {
        return [
            'content-migration',
            "migration:{$this->migration->id}",
            "content-type:{$this->migration->content_type}",
            "from:{$this->migration->from_driver}",
            "to:{$this->migration->to_driver}",
        ];
    }

    /**
     * Send success notification
     */
    private function sendSuccessNotification(): void
    {
        // TODO: Implement notification to user/admin
        // For now, just log
        Log::info("Migration completed notification", [
            'migration_id' => $this->migration->id,
            'summary' => $this->migration->getSummary(),
        ]);
    }

    /**
     * Send failure notification
     */
    private function sendFailureNotification(\Throwable $exception): void
    {
        // TODO: Implement notification to user/admin
        // For now, just log
        Log::error("Migration failed notification", [
            'migration_id' => $this->migration->id,
            'error' => $exception->getMessage(),
        ]);
    }

    /**
     * Calculate the number of seconds to wait before retrying the job
     */
    public function backoff(): array
    {
        // Exponential backoff: 1 minute, 5 minutes, 15 minutes
        return [60, 300, 900];
    }

    /**
     * Determine if the job should be retried
     */
    public function shouldRetry(\Throwable $exception): bool
    {
        // Don't retry if migration was cancelled
        if ($this->migration->isFailed() &&
            str_contains($this->migration->error_log[0]['reason'] ?? '', 'Cancelled')) {
            return false;
        }

        return true;
    }
}
