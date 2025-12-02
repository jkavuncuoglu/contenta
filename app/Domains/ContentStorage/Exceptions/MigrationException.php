<?php

declare(strict_types=1);

namespace App\Domains\ContentStorage\Exceptions;

/**
 * Exception thrown when content migration fails
 */
class MigrationException extends StorageException
{
    /**
     * Create a new migration exception
     *
     * @param string $message Error message
     * @param int $code Error code
     * @param \Throwable|null $previous Previous exception
     */
    public function __construct(string $message = 'Content migration failed', int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    /**
     * Create exception for migration already in progress
     *
     * @param string $contentType Content type being migrated
     * @return static
     */
    public static function alreadyRunning(string $contentType): static
    {
        return new static("Migration already in progress for: {$contentType}");
    }

    /**
     * Create exception for invalid source/destination
     *
     * @param string $from Source driver
     * @param string $to Destination driver
     * @return static
     */
    public static function invalidDriverCombination(string $from, string $to): static
    {
        return new static("Cannot migrate from {$from} to {$to}");
    }

    /**
     * Create exception for migration not found
     *
     * @param int $migrationId Migration ID
     * @return static
     */
    public static function notFound(int $migrationId): static
    {
        return new static("Migration not found: {$migrationId}");
    }

    /**
     * Create exception for migration interruption
     *
     * @param string $reason Interruption reason
     * @return static
     */
    public static function interrupted(string $reason): static
    {
        return new static("Migration interrupted: {$reason}");
    }

    /**
     * Create exception for partial migration failure
     *
     * @param int $failed Number of failed items
     * @param int $total Total items
     * @return static
     */
    public static function partialFailure(int $failed, int $total): static
    {
        return new static("Migration partially failed: {$failed} of {$total} items failed");
    }
}
