<?php

namespace App\Domains\ContentManagement\Tags\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Domains\ContentManagement\Tags\Models\Tag;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TagsController extends Controller
{
    public function index(Request $request): Response
    {
        $perPage = $request->integer('per_page', 15);
        $page = $request->integer('page', 1);

        $paginator = Tag::withCount('posts')
            ->orderBy('name')
            ->paginate($perPage, ['*'], 'page', $page);

        $tags = $paginator->through(function (Tag $t) {
            return [
                'id' => $t->id,
                'name' => $t->name,
                'slug' => $t->slug,
                'posts_count' => $t->posts_count ?? 0,
                'created_at' => $t->created_at?->toDateTimeString(),
            ];
        });

        return Inertia::render('admin/content/tags/Index', [
            'tags' => $tags,
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/content/tags/Create');
    }

    public function edit(int $id): Response
    {
        return Inertia::render('admin/content/tags/Edit', ['id' => $id]);
    }
}
