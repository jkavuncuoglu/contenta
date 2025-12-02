<?php

declare(strict_types=1);

namespace App\Domains\ContentStorage\Exceptions;

/**
 * Exception thrown when content cannot be read from storage
 */
class ReadException extends StorageException
{
    /**
     * Create a new read exception
     *
     * @param string $message Error message
     * @param int $code Error code
     * @param \Throwable|null $previous Previous exception
     */
    public function __construct(string $message = 'Failed to read content', int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    /**
     * Create exception for missing content
     *
     * @param string $path Content path
     * @return static
     */
    public static function notFound(string $path): static
    {
        return new static("Content not found at path: {$path}");
    }

    /**
     * Create exception for invalid frontmatter
     *
     * @param string $path Content path
     * @param string $reason Parsing error reason
     * @return static
     */
    public static function invalidFrontmatter(string $path, string $reason): static
    {
        return new static("Invalid frontmatter in {$path}: {$reason}");
    }

    /**
     * Create exception for read permission error
     *
     * @param string $path Content path
     * @return static
     */
    public static function permissionDenied(string $path): static
    {
        return new static("Permission denied reading: {$path}");
    }

    /**
     * Create exception for corrupted content
     *
     * @param string $path Content path
     * @param string $reason Corruption reason
     * @return static
     */
    public static function corrupted(string $path, string $reason): static
    {
        return new static("Content corrupted at {$path}: {$reason}");
    }

    /**
     * Create exception for general read failure
     *
     * @param string $path Content path
     * @param string $reason Failure reason
     * @return static
     */
    public static function failed(string $path, string $reason): static
    {
        return new static("Failed to read {$path}: {$reason}");
    }
}
