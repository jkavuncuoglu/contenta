<?php

declare(strict_types=1);

namespace App\Domains\ContentManagement\ContentStorage\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

/**
 * Content Migration Model
 *
 * Tracks content migration operations between storage backends.
 * Used for progress tracking, error logging, and rollback support.
 *
 * @property int $id
 * @property string $content_type
 * @property string $from_driver
 * @property string $to_driver
 * @property string $status
 * @property int $total_items
 * @property int $migrated_items
 * @property int $failed_items
 * @property array|null $error_log
 * @property \Illuminate\Support\Carbon|null $started_at
 * @property \Illuminate\Support\Carbon|null $completed_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class ContentMigration extends Model
{
    protected $table = 'content_migrations';

    protected $fillable = [
        'content_type',
        'from_driver',
        'to_driver',
        'status',
        'total_items',
        'migrated_items',
        'failed_items',
        'error_log',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'total_items' => 'integer',
        'migrated_items' => 'integer',
        'failed_items' => 'integer',
        'error_log' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_RUNNING = 'running';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';

    /**
     * Scope for pending migrations
     *
     * @param Builder<ContentMigration> $query
     * @return Builder<ContentMigration>
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope for running migrations
     *
     * @param Builder<ContentMigration> $query
     * @return Builder<ContentMigration>
     */
    public function scopeRunning(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_RUNNING);
    }

    /**
     * Scope for completed migrations
     *
     * @param Builder<ContentMigration> $query
     * @return Builder<ContentMigration>
     */
    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    /**
     * Scope for failed migrations
     *
     * @param Builder<ContentMigration> $query
     * @return Builder<ContentMigration>
     */
    public function scopeFailed(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_FAILED);
    }

    /**
     * Scope for specific content type
     *
     * @param Builder<ContentMigration> $query
     * @param string $contentType
     * @return Builder<ContentMigration>
     */
    public function scopeForContentType(Builder $query, string $contentType): Builder
    {
        return $query->where('content_type', $contentType);
    }

    /**
     * Check if migration is pending
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if migration is running
     */
    public function isRunning(): bool
    {
        return $this->status === self::STATUS_RUNNING;
    }

    /**
     * Check if migration is completed
     */
    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Check if migration failed
     */
    public function isFailed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }

    /**
     * Mark migration as started
     */
    public function markAsStarted(): void
    {
        $this->update([
            'status' => self::STATUS_RUNNING,
            'started_at' => now(),
        ]);
    }

    /**
     * Mark migration as completed
     */
    public function markAsCompleted(): void
    {
        $this->update([
            'status' => self::STATUS_COMPLETED,
            'completed_at' => now(),
        ]);
    }

    /**
     * Mark migration as failed
     *
     * @param string $reason Failure reason
     */
    public function markAsFailed(string $reason): void
    {
        $errorLog = $this->error_log ?? [];
        $errorLog[] = [
            'timestamp' => now()->toIso8601String(),
            'reason' => $reason,
        ];

        $this->update([
            'status' => self::STATUS_FAILED,
            'error_log' => $errorLog,
            'completed_at' => now(),
        ]);
    }

    /**
     * Increment migrated items counter
     *
     * @param int $count Number of items migrated
     */
    public function incrementMigrated(int $count = 1): void
    {
        $this->increment('migrated_items', $count);
    }

    /**
     * Increment failed items counter
     *
     * @param int $count Number of items failed
     * @param array<string, mixed>|null $errorDetails Error details
     */
    public function incrementFailed(int $count = 1, ?array $errorDetails = null): void
    {
        $this->increment('failed_items', $count);

        if ($errorDetails) {
            $errorLog = $this->error_log ?? [];
            $errorLog[] = array_merge([
                'timestamp' => now()->toIso8601String(),
            ], $errorDetails);

            $this->update(['error_log' => $errorLog]);
        }
    }

    /**
     * Get progress percentage
     *
     * @return float Progress (0-100)
     */
    public function getProgressPercentage(): float
    {
        if ($this->total_items === 0) {
            return 0.0;
        }

        $processed = $this->migrated_items + $this->failed_items;

        return round(($processed / $this->total_items) * 100, 2);
    }

    /**
     * Get progress percentage as integer
     *
     * @return int Progress (0-100)
     */
    public function getProgress(): int
    {
        return (int) $this->getProgressPercentage();
    }

    /**
     * Get migration duration in seconds
     *
     * @return int|null Duration in seconds, or null if not started/completed
     */
    public function getDuration(): ?int
    {
        if (! $this->started_at) {
            return null;
        }

        $endTime = $this->completed_at ?? now();

        return $this->started_at->diffInSeconds($endTime);
    }

    /**
     * Get estimated time remaining in seconds
     *
     * @return int|null Estimated seconds remaining, or null if cannot calculate
     */
    public function getEstimatedTimeRemaining(): ?int
    {
        if (! $this->started_at || $this->migrated_items === 0 || $this->total_items === 0) {
            return null;
        }

        $elapsed = $this->started_at->diffInSeconds(now());
        $timePerItem = $elapsed / $this->migrated_items;
        $remaining = $this->total_items - $this->migrated_items - $this->failed_items;

        return (int) round($remaining * $timePerItem);
    }

    /**
     * Get migration summary
     *
     * @return array<string, mixed>
     */
    public function getSummary(): array
    {
        return [
            'id' => $this->id,
            'content_type' => $this->content_type,
            'from' => $this->from_driver,
            'to' => $this->to_driver,
            'status' => $this->status,
            'progress' => [
                'total' => $this->total_items,
                'migrated' => $this->migrated_items,
                'failed' => $this->failed_items,
                'percentage' => $this->getProgressPercentage(),
            ],
            'duration' => $this->getDuration(),
            'estimated_remaining' => $this->getEstimatedTimeRemaining(),
            'started_at' => $this->started_at?->toIso8601String(),
            'completed_at' => $this->completed_at?->toIso8601String(),
            'has_errors' => $this->failed_items > 0,
        ];
    }

    /**
     * Get available statuses
     *
     * @return array<string, string>
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_PENDING => 'Pending',
            self::STATUS_RUNNING => 'Running',
            self::STATUS_COMPLETED => 'Completed',
            self::STATUS_FAILED => 'Failed',
        ];
    }
}
