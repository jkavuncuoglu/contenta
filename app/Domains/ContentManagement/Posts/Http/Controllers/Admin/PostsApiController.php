<?php

namespace App\Domains\ContentManagement\Posts\Http\Controllers\Admin;

use App\Domains\ContentManagement\Posts\Models\Post;
use App\Domains\ContentManagement\Posts\Services\PostServiceContract;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PostsApiController extends Controller
{
    public function __construct(
        protected PostServiceContract $postService
    ) {}

    /**
     * Get posts list for menu item selection
     */
    public function index(Request $request): JsonResponse
    {
        $query = Post::query()
            ->select('id', 'title', 'slug', 'status', 'created_at')
            ->orderBy('created_at', 'desc');

        // Search by title
        $search = $request->input('search');
        if ($request->has('search') && is_string($search) && ! empty($search)) {
            $query->where('title', 'like', '%'.$search.'%');
        }

        // Filter by status
        $status = $request->input('status');
        if ($request->has('status') && is_string($status) && ! empty($status)) {
            $query->where('status', $status);
        }

        $perPageInput = $request->input('per_page', 50);
        $perPage = is_numeric($perPageInput) ? (int) $perPageInput : 50;
        $posts = $query->paginate($perPage);

        return response()->json([
            'data' => $posts->items(),
            'meta' => [
                'current_page' => $posts->currentPage(),
                'last_page' => $posts->lastPage(),
                'per_page' => $posts->perPage(),
                'total' => $posts->total(),
            ],
        ]);
    }

    /**
     * Get posts for calendar view
     */
    public function calendar(Request $request): JsonResponse
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        if (! $startDate || ! $endDate) {
            return response()->json([
                'message' => 'start_date and end_date are required',
            ], 422);
        }

        try {
            $start = new \DateTime($startDate);
            $end = new \DateTime($endDate);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Invalid date format',
            ], 422);
        }

        $posts = $this->postService->getCalendarPosts($start, $end);

        // Transform posts for calendar display
        $calendarData = array_map(function (Post $post) {
            return [
                'id' => $post->id,
                'title' => $post->title,
                'slug' => $post->slug,
                'status' => $post->status,
                'published_at' => $post->published_at?->toISOString(),
                'author' => [
                    'id' => $post->author?->id,
                    'name' => $post->author?->name,
                ],
                'categories' => $post->categories->map(fn ($cat) => [
                    'id' => $cat->id,
                    'name' => $cat->name,
                ]),
            ];
        }, $posts);

        return response()->json([
            'data' => $calendarData,
        ]);
    }

    /**
     * Get scheduled posts
     */
    public function scheduled(Request $request): JsonResponse
    {
        $perPageInput = $request->input('per_page', 20);
        $perPage = is_numeric($perPageInput) ? (int) $perPageInput : 20;

        $posts = $this->postService->getScheduledPosts($perPage);

        return response()->json([
            'data' => $posts->items(),
            'meta' => [
                'current_page' => $posts->currentPage(),
                'last_page' => $posts->lastPage(),
                'per_page' => $posts->perPage(),
                'total' => $posts->total(),
            ],
        ]);
    }

    /**
     * Reschedule a post
     */
    public function reschedule(Request $request, int $id): JsonResponse
    {
        $post = Post::findOrFail($id);

        $request->validate([
            'published_at' => 'required|date|after:now',
        ]);

        $publishAt = new \DateTime($request->input('published_at'));
        $updatedPost = $this->postService->schedulePost($post, $publishAt);

        return response()->json([
            'message' => 'Post rescheduled successfully',
            'data' => [
                'id' => $updatedPost->id,
                'title' => $updatedPost->title,
                'status' => $updatedPost->status,
                'published_at' => $updatedPost->published_at?->toISOString(),
            ],
        ]);
    }

    /**
     * Get archived posts
     */
    public function archived(Request $request): JsonResponse
    {
        $perPageInput = $request->input('per_page', 20);
        $perPage = is_numeric($perPageInput) ? (int) $perPageInput : 20;

        $posts = $this->postService->getArchivedPosts($perPage);

        return response()->json([
            'data' => $posts->items(),
            'meta' => [
                'current_page' => $posts->currentPage(),
                'last_page' => $posts->lastPage(),
                'per_page' => $posts->perPage(),
                'total' => $posts->total(),
            ],
        ]);
    }

    /**
     * Restore archived post
     */
    public function restore(int $id): JsonResponse
    {
        $post = $this->postService->restorePost($id);

        return response()->json([
            'message' => 'Post restored successfully',
            'data' => [
                'id' => $post->id,
                'title' => $post->title,
                'status' => $post->status,
            ],
        ]);
    }

    /**
     * Change post status
     */
    public function changeStatus(Request $request, int $id): JsonResponse
    {
        $post = Post::findOrFail($id);

        $request->validate([
            'status' => 'required|in:draft,scheduled,published',
        ]);

        $status = $request->input('status');
        assert(is_string($status));

        $updatedPost = $this->postService->changeStatus($post, $status);

        return response()->json([
            'message' => 'Post status updated successfully',
            'data' => [
                'id' => $updatedPost->id,
                'title' => $updatedPost->title,
                'status' => $updatedPost->status,
                'published_at' => $updatedPost->published_at?->toISOString(),
            ],
        ]);
    }
}
