<?php

namespace App\Domains\ContentManagement\Posts\Http\Controllers\Admin;

use App\Domains\ContentManagement\Posts\Models\Post;
use App\Http\Controllers\Controller;
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
        $post = Post::with(['author', 'categories', 'tags'])
            ->findOrFail($id);

        return Inertia::render('admin/content/posts/Edit', [
            'post' => [
                'id' => $post->id,
                'title' => $post->title,
                'slug' => $post->slug,
                'content_markdown' => $post->content_markdown,
                'content_html' => $post->content_html,
                'excerpt' => $post->excerpt,
                'status' => $post->status,
                'published_at' => $post->published_at?->toDateTimeString(),
                'author_id' => $post->author_id,
                'author' => $post->author ? [
                    'id' => $post->author->id,
                    'name' => $post->author->name ?? $post->author->username,
                ] : null,
                'categories' => $post->categories->map(fn ($c) => [
                    'id' => $c->id,
                    'name' => $c->name,
                ])->toArray(),
                'tags' => $post->tags->map(fn ($t) => [
                    'id' => $t->id,
                    'name' => $t->name,
                ])->toArray(),
                'created_at' => $post->created_at->toDateTimeString(),
                'updated_at' => $post->updated_at->toDateTimeString(),
            ],
        ]);
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
            'table_of_contents' => 'nullable|array',
            'excerpt' => 'nullable|string|max:500',
            'status' => 'required|in:draft,published,scheduled,private',
            'published_at' => 'nullable|date',
            'featured_image' => 'nullable|string',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'custom_fields' => 'nullable|array',
        ]);

        // Set author_id from authenticated user (don't accept from request)
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
            'slug' => 'nullable|string|max:255|unique:posts,slug,'.$id,
            'content_markdown' => 'nullable|string',
            'content_html' => 'nullable|string',
            'table_of_contents' => 'nullable|array',
            'excerpt' => 'nullable|string|max:500',
            'status' => 'required|in:draft,published,scheduled,private',
            'published_at' => 'nullable|date',
            'featured_image' => 'nullable|string',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'custom_fields' => 'nullable|array',
        ]);

        // Set author_id from authenticated user (don't accept from request)
        $validated['author_id'] = Auth::id();

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
