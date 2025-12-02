<?php

declare(strict_types=1);

namespace App\Domains\ContentStorage\Exceptions;

/**
 * Exception thrown when content cannot be written to storage
 */
class WriteException extends StorageException
{
    /**
     * Create a new write exception
     *
     * @param string $message Error message
     * @param int $code Error code
     * @param \Throwable|null $previous Previous exception
     */
    public function __construct(string $message = 'Failed to write content', int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    /**
     * Create exception for write permission error
     *
     * @param string $path Content path
     * @return static
     */
    public static function permissionDenied(string $path): static
    {
        return new static("Permission denied writing to: {$path}");
    }

    /**
     * Create exception for disk space issues
     *
     * @param string $path Content path
     * @return static
     */
    public static function insufficientSpace(string $path): static
    {
        return new static("Insufficient storage space to write: {$path}");
    }

    /**
     * Create exception for path validation failure
     *
     * @param string $path Invalid path
     * @param string $reason Validation error reason
     * @return static
     */
    public static function invalidPath(string $path, string $reason): static
    {
        return new static("Invalid path {$path}: {$reason}");
    }

    /**
     * Create exception for path collision
     *
     * @param string $path Conflicting path
     * @return static
     */
    public static function pathExists(string $path): static
    {
        return new static("Content already exists at: {$path}");
    }

    /**
     * Create exception for network/API failures
     *
     * @param string $driver Driver name
     * @param string $reason Failure reason
     * @return static
     */
    public static function networkFailure(string $driver, string $reason): static
    {
        return new static("Network error writing to {$driver}: {$reason}");
    }
}
