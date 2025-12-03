<?php

declare(strict_types=1);

namespace App\Domains\ContentManagement\ContentStorage\Exceptions;

use Exception;

/**
 * Base exception for all storage-related errors
 */
class StorageException extends Exception
{
    /**
     * Create a new storage exception
     *
     * @param string $message Error message
     * @param int $code Error code
     * @param \Throwable|null $previous Previous exception
     */
    public function __construct(string $message = 'Storage operation failed', int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    /**
     * Create exception for invalid driver
     *
     * @param string $driver Driver name
     * @return static
     */
    public static function invalidDriver(string $driver): static
    {
        return new static("Invalid storage driver: {$driver}");
    }

    /**
     * Create exception for missing configuration
     *
     * @param string $key Configuration key
     * @return static
     */
    public static function missingConfiguration(string $key): static
    {
        return new static("Missing required configuration: {$key}");
    }

    /**
     * Create exception for connection failure
     *
     * @param string $driver Driver name
     * @param string $reason Failure reason
     * @return static
     */
    public static function connectionFailed(string $driver, string $reason): static
    {
        return new static("Failed to connect to {$driver}: {$reason}");
    }
}
