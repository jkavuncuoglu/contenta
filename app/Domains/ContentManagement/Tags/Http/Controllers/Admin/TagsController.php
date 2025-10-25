<?php

namespace App\Domains\ContentManagement\Tags\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Domains\ContentManagement\Tags\Models\Tag;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TagsController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->input('per_page', 15);
        $page = (int) $request->input('page', 1);

        $paginator = Tag::withCount('posts')
            ->orderBy('name')
            ->paginate($perPage, ['*'], 'page', $page);

        $tags = $paginator->through(function ($t) {
            return [
                'id' => $t->id,
                'name' => $t->name,
                'slug' => $t->slug,
                'posts_count' => $t->posts_count ?? 0,
                'created_at' => optional($t->created_at)->toDateTimeString(),
            ];
        });

        return Inertia::render('admin/content/tags/Index', [
            'tags' => $paginator->items(), // Pass only the array of tag items
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ]);
    }

    public function create()
    {
        return Inertia::render('admin/content/tags/Create');
    }

    public function edit($id)
    {
        return Inertia::render('admin/content/tags/Edit', ['id' => $id]);
    }
}
