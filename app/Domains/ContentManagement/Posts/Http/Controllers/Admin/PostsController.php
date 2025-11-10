<?php

namespace App\Domains\ContentManagement\Posts\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Domains\ContentManagement\Posts\Models\Post;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PostsController extends Controller
{
    /**
     * Display a paginated listing of the posts.
     */
    public function index(Request $request): Response
    {
        $perPage = is_numeric($request->input('per_page')) ? (int) $request->input('per_page') : 15;
        $page = is_numeric($request->input('page')) ? (int) $request->input('page') : 1;

        $paginator = Post::with('author')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);

        $posts = $paginator->through(function ($p) {
            return [
                'id' => $p->id,
                'title' => $p->title,
                'excerpt' => $p->excerpt,
                'status' => $p->status,
                'created_at' => $p->created_at?->toDateTimeString(),
                'author' => $p->author ? ['name' => $p->author->name] : null,
            ];
        });

        return Inertia::render('admin/content/posts/Index', [
            'posts' => $posts,
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
        return Inertia::render('admin/content/posts/Create');
    }

    public function edit(int $id): Response
    {
        return Inertia::render('admin/content/posts/Edit', ['id' => $id]);
    }

    public function show(int $id): Response
    {
        return Inertia::render('admin/content/posts/Show', ['id' => $id]);
    }
}
