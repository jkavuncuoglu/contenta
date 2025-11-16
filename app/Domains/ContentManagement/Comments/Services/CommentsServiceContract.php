<?php

declare(strict_types=1);

namespace App\Domains\ContentManagement\Comments\Services;

use App\Domains\ContentManagement\Posts\Models\Comment;
use Illuminate\Pagination\LengthAwarePaginator;

interface CommentsServiceContract
{
    /**
     * Get paginated comments with filters
     *
     * @param  array<string, mixed>  $filters
     * @return LengthAwarePaginator<array-key, mixed>
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
     *
     * @param  array<int>  $ids
     */
    public function bulkUpdateStatus(array $ids, string $status): int;

    /**
     * Delete comment
     */
    public function deleteComment(int $id): bool;

    /**
     * Get comment statistics
     *
     * @return array<string, mixed>
     */
    public function getStatistics(): array;

    /**
     * Get comments for a specific post
     *
     * @return array<int, mixed>
     */
    public function getPostComments(int $postId, string $status = 'approved'): array;
}
