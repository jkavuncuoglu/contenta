<?php

namespace App\Domains\ContentManagement\Posts\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Domains\ContentManagement\Posts\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
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

    public function calendar(): Response
    {
        return Inertia::render('admin/content/posts/Calendar');
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

    /**
     * Store a newly created post.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:posts,slug',
            'content_markdown' => 'nullable|string',
            'content_html' => 'nullable|string',
            'excerpt' => 'nullable|string|max:500',
            'status' => 'required|in:draft,published,scheduled',
            'published_at' => 'nullable|date',
            'featured_image' => 'nullable|string',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'custom_fields' => 'nullable|array',
        ]);

        $validated['author_id'] = Auth::id();

        // Generate slug if not provided
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['title']);
        }

        $post = Post::create($validated);

        // Sync categories if provided
        if ($request->has('category_ids')) {
            $post->categories()->sync($request->input('category_ids', []));
        }

        // Sync tags if provided
        if ($request->has('tag_ids')) {
            $post->tags()->sync($request->input('tag_ids', []));
        }

        return redirect()->route('admin.posts.edit', $post->id)
            ->with('success', 'Post created successfully.');
    }

    /**
     * Update the specified post.
     */
    public function update(Request $request, int $id)
    {
        $post = Post::findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:posts,slug',
            'content_markdown' => 'nullable|string',
            'content_html' => 'nullable|string',
            'excerpt' => 'nullable|string|max:500',
            'status' => 'required|in:draft,published,scheduled',
            'published_at' => 'nullable|date',
            'author_id' => 'required|exists:users,id',
            'featured_image' => 'nullable|string',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'custom_fields' => 'nullable|array',
        ]);

        // Generate slug if not provided
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['title']);
        }

        $post->update($validated);

        // Sync categories if provided
        if ($request->has('category_ids')) {
            $post->categories()->sync($request->input('category_ids', []));
        }

        // Sync tags if provided
        if ($request->has('tag_ids')) {
            $post->tags()->sync($request->input('tag_ids', []));
        }

        return redirect()->route('admin.posts.edit', $post->id)
            ->with('success', 'Post updated successfully.');
    }

    /**
     * Remove the specified post.
     */
    public function destroy(int $id)
    {
        $post = Post::findOrFail($id);
        $post->delete();

        return redirect()->route('admin.posts.index')
            ->with('success', 'Post deleted successfully.');
    }
}
