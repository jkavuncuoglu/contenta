<?php

declare(strict_types=1);

namespace App\Domains\ContentManagement\Comments\Services;

use App\Domains\ContentManagement\Posts\Models\Comment;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CommentsService implements CommentsServiceContract
{
    /**
     * Get paginated comments with filters
     *
     * @param  array<string, mixed>  $filters
     * @return LengthAwarePaginator<array-key, mixed>
     */
    public function getPaginatedComments(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = Comment::with(['post:id,title,slug'])
            ->select([
                'id', 'post_id', 'parent_id', 'author_name', 'author_email',
                'content', 'status', 'created_at', 'updated_at',
            ])
            ->latest();

        // Apply filters
        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['search']) && is_string($filters['search'])) {
            $search = '%'.$filters['search'].'%';
            $query->where(function ($q) use ($search) {
                $q->where('content', 'LIKE', $search)
                    ->orWhere('author_name', 'LIKE', $search)
                    ->orWhere('author_email', 'LIKE', $search);
            });
        }

        if (! empty($filters['post_id'])) {
            $query->where('post_id', $filters['post_id']);
        }

        return $query->paginate($perPage)
            ->through(function ($comment) {
                return [
                    'id' => $comment->id,
                    'post_id' => $comment->post_id,
                    'post_title' => $comment->post->title ?? 'Unknown Post',
                    'post_slug' => $comment->post->slug ?? '',
                    'parent_id' => $comment->parent_id,
                    'author_name' => $comment->author_name,
                    'author_email' => $comment->author_email,
                    'content' => $comment->content,
                    'content_excerpt' => Str::limit($comment->content, 100),
                    'status' => $comment->status,
                    'created_at' => $comment->created_at?->format('M j, Y H:i'),
                    'updated_at' => $comment->updated_at?->format('M j, Y H:i'),
                    'is_reply' => ! is_null($comment->parent_id),
                ];
            });
    }

    /**
     * Get comment by ID
     */
    public function getCommentById(int $id): ?Comment
    {
        return Comment::with(['post:id,title,slug', 'parent', 'replies'])
            ->find($id);
    }

    /**
     * Update comment status
     */
    public function updateStatus(int $id, string $status): bool
    {
        $comment = Comment::find($id);
        if (! $comment) {
            return false;
        }

        return $comment->update(['status' => $status]);
    }

    /**
     * Bulk update comment statuses
     *
     * @param  array<int>  $ids
     */
    public function bulkUpdateStatus(array $ids, string $status): int
    {
        return Comment::whereIn('id', $ids)->update(['status' => $status]);
    }

    /**
     * Delete comment
     */
    public function deleteComment(int $id): bool
    {
        $comment = Comment::find($id);
        if (! $comment) {
            return false;
        }

        return (bool) $comment->delete();
    }

    /**
     * Get comment statistics
     *
     * @return array<string, mixed>
     */
    public function getStatistics(): array
    {
        $stats = Comment::select([
            DB::raw('COUNT(*) as total'),
            DB::raw('SUM(CASE WHEN status = "pending" THEN 1 ELSE 0 END) as pending'),
            DB::raw('SUM(CASE WHEN status = "approved" THEN 1 ELSE 0 END) as approved'),
            DB::raw('SUM(CASE WHEN status = "spam" THEN 1 ELSE 0 END) as spam'),
            DB::raw('SUM(CASE WHEN status = "trash" THEN 1 ELSE 0 END) as trash'),
        ])->first();

        return [
            'total' => $stats->total ?? 0,
            'pending' => $stats->pending ?? 0,
            'approved' => $stats->approved ?? 0,
            'spam' => $stats->spam ?? 0,
            'trash' => $stats->trash ?? 0,
        ];
    }

    /**
     * Get comments for a specific post
     *
     * @return array<int, mixed>
     */
    public function getPostComments(int $postId, string $status = 'approved'): array
    {
        return Comment::where('post_id', $postId)
            ->where('status', $status)
            ->whereNull('parent_id') // Only top-level comments
            ->with(['replies' => function ($query) use ($status) {
                $query->where('status', $status)->orderBy('created_at');
            }])
            ->orderBy('created_at')
            ->get()
            ->map(function (Comment $comment) {
                return [
                    'id' => $comment->id,
                    'author_name' => $comment->author_name,
                    'author_email' => $comment->author_email,
                    'content' => $comment->content,
                    'created_at' => $comment->created_at?->format('M j, Y H:i'),
                    'replies_count' => $comment->replies->count(),
                    'replies' => $comment->replies->map(function (Comment $reply) {
                        return [
                            'id' => $reply->id,
                            'author_name' => $reply->author_name,
                            'content' => $reply->content,
                            'created_at' => $reply->created_at?->format('M j, Y H:i'),
                        ];
                    })->toArray(),
                ];
            })
            ->toArray();
    }
}
