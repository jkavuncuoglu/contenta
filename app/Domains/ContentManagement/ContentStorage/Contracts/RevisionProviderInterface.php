<?php

declare(strict_types=1);

namespace App\Domains\ContentManagement\ContentStorage\Contracts;

use App\Domains\ContentManagement\ContentStorage\ValueObjects\Revision;
use App\Domains\ContentManagement\ContentStorage\ValueObjects\RevisionCollection;

interface RevisionProviderInterface
{
    /**
     * Check if this storage driver supports revisions
     */
    public function supportsRevisions(): bool;

    /**
     * Get paginated revision history
     *
     * @param string $storagePath Path to the content file
     * @param int $page Page number (1-indexed)
     * @param int $perPage Items per page (default: 10)
     * @return RevisionCollection Collection of revisions with pagination info
     */
    public function getRevisions(string $storagePath, int $page = 1, int $perPage = 10): RevisionCollection;

    /**
     * Get a specific revision by ID/version
     *
     * @param string $storagePath Path to the content file
     * @param string $revisionId Version ID, commit hash, or timestamp
     * @return Revision|null The revision or null if not found
     */
    public function getRevision(string $storagePath, string $revisionId): ?Revision;

    /**
     * Restore a specific revision (make it current)
     *
     * @param string $storagePath Path to the content file
     * @param string $revisionId Version ID or commit hash
     * @return bool True if restored successfully
     */
    public function restoreRevision(string $storagePath, string $revisionId): bool;

    /**
     * Get the latest revision
     *
     * @param string $storagePath Path to the content file
     * @return Revision|null The latest revision or null if none exist
     */
    public function getLatestRevision(string $storagePath): ?Revision;
}
