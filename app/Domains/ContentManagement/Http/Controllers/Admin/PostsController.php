<?php

namespace App\Domains\ContentManagement\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Domains\ContentManagement\Models\Post;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PostsController extends Controller
{
    /**
     * Display a paginated listing of the posts.
     */
    public function index(Request $request)
    {
        $perPage = (int) $request->input('per_page', 15);
        $page = (int) $request->input('page', 1);

        $paginator = Post::with('author')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);

        $posts = $paginator->through(function ($p) {
            return [
                'id' => $p->id,
                'title' => $p->title,
                'excerpt' => $p->excerpt,
                'status' => $p->status,
                'created_at' => optional($p->created_at)->toDateTimeString(),
                'author' => $p->author ? ['name' => $p->author->name] : null,
            ];
        });

        return Inertia::render('admin/posts/Index', [
            'posts' => $posts,
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
        return Inertia::render('admin/posts/Create');
    }

    public function edit($id)
    {
        return Inertia::render('admin/posts/Edit', ['id' => $id]);
    }

    public function show($id)
    {
        return Inertia::render('admin/posts/Show', ['id' => $id]);
    }

    // Optionally you could add store/update/destroy methods here to handle form submissions
}

