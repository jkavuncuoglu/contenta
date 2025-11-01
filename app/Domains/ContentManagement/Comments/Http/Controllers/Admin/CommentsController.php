<?php

declare(strict_types=1);

namespace App\Domains\ContentManagement\Comments\Http\Controllers\Admin;

use App\Domains\ContentManagement\Comments\Services\CommentsServiceContract;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class CommentsController extends Controller
{
    public function __construct(
        private readonly CommentsServiceContract $commentsService
    ) {}

    /**
     * Display a listing of comments
     */
    public function index(Request $request): Response
    {
        $filters = $request->only(['status', 'search', 'post_id']);
        $perPage = (int) $request->get('per_page', 20);

        $comments = $this->commentsService->getPaginatedComments($filters, $perPage);
        $statistics = $this->commentsService->getStatistics();

        return Inertia::render('admin/content/Comments', [
            'comments' => $comments->items(),
            'pagination' => [
                'current_page' => $comments->currentPage(),
                'last_page' => $comments->lastPage(),
                'per_page' => $comments->perPage(),
                'total' => $comments->total(),
                'from' => $comments->firstItem(),
                'to' => $comments->lastItem(),
            ],
            'filters' => $filters,
            'statistics' => $statistics,
        ]);
    }

    /**
     * Display the specified comment
     */
    public function show(int $id)
    {
        $comment = $this->commentsService->getCommentById($id);

        if (!$comment) {
            return response()->json([
                'success' => false,
                'message' => 'Comment not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'comment' => [
                'id' => $comment->id,
                'post_id' => $comment->post_id,
                'post_title' => $comment->post->title ?? 'Unknown Post',
                'parent_id' => $comment->parent_id,
                'author_name' => $comment->author_name,
                'author_email' => $comment->author_email,
                'author_url' => $comment->author_url,
                'author_ip' => $comment->author_ip,
                'content' => $comment->content,
                'status' => $comment->status,
                'created_at' => $comment->created_at->format('M j, Y H:i'),
                'updated_at' => $comment->updated_at->format('M j, Y H:i'),
                'replies_count' => $comment->replies->count(),
                'parent' => $comment->parent ? [
                    'id' => $comment->parent->id,
                    'author_name' => $comment->parent->author_name,
                    'content' => Str::limit($comment->parent->content, 100),
                ] : null,
            ],
        ]);
    }

    /**
     * Update comment status
     */
    public function updateStatus(Request $request, int $id)
    {
        $request->validate([
            'status' => 'required|in:pending,approved,spam,trash',
        ]);

        $updated = $this->commentsService->updateStatus($id, $request->status);

        if (!$updated) {
            return redirect()->route('admin.comments.index')
                ->with('error', 'Comment not found');
        }

        return redirect()->route('admin.comments.index')
            ->with('success', 'Comment status updated successfully');
    }

    /**
     * Bulk update comment statuses
     */
    public function bulkUpdateStatus(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:comments,id',
            'status' => 'required|in:pending,approved,spam,trash',
        ]);

        $updated = $this->commentsService->bulkUpdateStatus($request->ids, $request->status);

        return redirect()->route('admin.comments.index')
            ->with('success', "Updated {$updated} comments successfully");
    }

    /**
     * Remove the specified comment
     */
    public function destroy(int $id)
    {
        $deleted = $this->commentsService->deleteComment($id);

        if (!$deleted) {
            return redirect()->route('admin.comments.index')
                ->with('error', 'Comment not found');
        }

        return redirect()->route('admin.comments.index')
            ->with('success', 'Comment deleted successfully');
    }
}