<?php

namespace App\Domains\ContentManagement\Tags\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Domains\ContentManagement\Tags\Models\Tag;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TagsApiController extends Controller
{
    /**
     * Get tags list for menu item selection
     */
    public function index(Request $request): JsonResponse
    {
        $query = Tag::query()
            ->select('id', 'name', 'slug')
            ->orderBy('name');

        // Search by name
        if ($request->has('search') && !empty($request->search)) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $tags = $query->paginate($request->get('per_page', 50));

        return response()->json([
            'data' => $tags->items(),
            'meta' => [
                'current_page' => $tags->currentPage(),
                'last_page' => $tags->lastPage(),
                'per_page' => $tags->perPage(),
                'total' => $tags->total(),
            ],
        ]);
    }
}
