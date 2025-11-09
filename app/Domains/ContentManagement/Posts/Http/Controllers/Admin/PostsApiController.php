<?php

namespace App\Domains\ContentManagement\Posts\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Domains\ContentManagement\Posts\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PostsApiController extends Controller
{
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
        if ($request->has('search') && is_string($search) && !empty($search)) {
            $query->where('title', 'like', '%' . $search . '%');
        }

        // Filter by status
        $status = $request->input('status');
        if ($request->has('status') && is_string($status) && !empty($status)) {
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
}
