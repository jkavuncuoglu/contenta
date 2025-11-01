<?php

declare(strict_types=1);

namespace App\Domains\ContentManagement\Comments\Services;

use App\Domains\ContentManagement\Posts\Models\Comment;
use Illuminate\Pagination\LengthAwarePaginator;

interface CommentsServiceContract
{
    /**
     * Get paginated comments with filters
     */
    public function getPaginatedComments(array $filters = [], int $perPage = 20): LengthAwarePaginator;

    /**
     * Get comment by ID
     */
    public function getCommentById(int $id): ?Comment;

    /**
     * Update comment status
     */
    public function updateStatus(int $id, string $status): bool;

    /**
     * Bulk update comment statuses
     */
    public function bulkUpdateStatus(array $ids, string $status): int;

    /**
     * Delete comment
     */
    public function deleteComment(int $id): bool;

    /**
     * Get comment statistics
     */
    public function getStatistics(): array;

    /**
     * Get comments for a specific post
     */
    public function getPostComments(int $postId, string $status = 'approved'): array;
}