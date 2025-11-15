<?php

namespace App\Domains\ContentManagement\Categories\Http\Controllers\Admin;

use App\Domains\ContentManagement\Categories\Models\Category;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoriesApiController extends Controller
{
    /**
     * Get categories list for menu item selection
     */
    public function index(Request $request): JsonResponse
    {
        $query = Category::query()
            ->select('id', 'name', 'slug')
            ->orderBy('name');

        // Search by name
        if ($request->has('search') && ! empty($request->search)) {
            $query->where('name', 'like', '%'.$request->search.'%');
        }

        $categories = $query->paginate($request->get('per_page', 50));

        return response()->json([
            'data' => $categories->items(),
            'meta' => [
                'current_page' => $categories->currentPage(),
                'last_page' => $categories->lastPage(),
                'per_page' => $categories->perPage(),
                'total' => $categories->total(),
            ],
        ]);
    }
}
