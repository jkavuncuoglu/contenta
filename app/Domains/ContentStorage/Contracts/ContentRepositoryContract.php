<?php

declare(strict_types=1);

namespace App\Domains\ContentStorage\Contracts;

use App\Domains\ContentStorage\Models\ContentData;

/**
 * Content Repository Contract
 *
 * Defines the interface for all storage drivers (Database, Local, S3, GitHub, Azure, GCS).
 * All repositories must implement these methods to ensure consistent behavior across backends.
 */
interface ContentRepositoryContract
{
    /**
     * Read content from storage with frontmatter parsed
     *
     * @param string $path Relative path to content (e.g., "pages/about-us.md" or "pages/1")
     * @return ContentData Content with parsed frontmatter and metadata
     * @throws \App\Domains\ContentStorage\Exceptions\ReadException If content cannot be read
     */
    public function read(string $path): ContentData;

    /**
     * Write content to storage with frontmatter
     *
     * @param string $path Relative path where content should be written
     * @param ContentData $data Content with frontmatter to write
     * @return bool True on success, false on failure
     * @throws \App\Domains\ContentStorage\Exceptions\WriteException If content cannot be written
     */
    public function write(string $path, ContentData $data): bool;

    /**
     * Check if content exists at the given path
     *
     * @param string $path Relative path to check
     * @return bool True if exists, false otherwise
     */
    public function exists(string $path): bool;

    /**
     * Delete content at the given path
     *
     * @param string $path Relative path to content
     * @return bool True on success, false on failure
     * @throws \App\Domains\ContentStorage\Exceptions\WriteException If content cannot be deleted
     */
    public function delete(string $path): bool;

    /**
     * List all content in a directory
     *
     * @param string $directory Directory path (empty string for root)
     * @return array<int, string> Array of relative paths
     */
    public function list(string $directory = ''): array;

    /**
     * Test connection to storage backend
     *
     * Validates credentials and connectivity without performing actual operations.
     * Useful for connection testing in admin UI.
     *
     * @return bool True if connection successful, false otherwise
     */
    public function testConnection(): bool;

    /**
     * Get the driver name/identifier
     *
     * @return string Driver name (e.g., "database", "github", "s3", "azure", "gcs", "local")
     */
    public function getDriverName(): string;
}
