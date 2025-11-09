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
        if ($request->has('search') && !empty($request->search)) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        // Filter by status
        if ($request->has('status') && !empty($request->status)) {
            $query->where('status', $request->status);
        }

        $posts = $query->paginate($request->get('per_page', 50));

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
