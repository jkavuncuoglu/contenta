<?php

namespace App\Domains\ContentManagement\Posts\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Domains\ContentManagement\Posts\Models\Post;
use App\Domains\ContentManagement\ContentStorage\ContentStorageManager;
use App\Domains\ContentManagement\ContentStorage\ValueObjects\ContentData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class PostsController extends Controller
{
    /**
     * Get available storage drivers
     */
    private function getAvailableStorageDrivers(): array
    {
        return [
            ['value' => 'database', 'label' => 'Database (Default)', 'description' => 'Store content in MySQL database'],
            ['value' => 'local', 'label' => 'Local Filesystem', 'description' => 'Store content in local storage/app directory'],
            ['value' => 's3', 'label' => 'Amazon S3', 'description' => 'Store content in AWS S3 bucket'],
            ['value' => 'azure', 'label' => 'Azure Blob Storage', 'description' => 'Store content in Azure Blob'],
            ['value' => 'gcs', 'label' => 'Google Cloud Storage', 'description' => 'Store content in GCS bucket'],
            ['value' => 'github', 'label' => 'GitHub', 'description' => 'Store content in GitHub repository'],
        ];
    }

    /**
     * Check if storage driver requires commit message
     */
    private function requiresCommitMessage(string $driver): bool
    {
        return in_array($driver, ['github', 'gitlab', 'bitbucket']);
    }

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
        return Inertia::render('admin/content/posts/Create', [
            'storageDrivers' => $this->getAvailableStorageDrivers(),
        ]);
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
                'categories' => $post->categories->map(fn($c) => [
                    'id' => $c->id,
                    'name' => $c->name,
                ])->toArray(),
                'tags' => $post->tags->map(fn($t) => [
                    'id' => $t->id,
                    'name' => $t->name,
                ])->toArray(),
                'created_at' => $post->created_at->toDateTimeString(),
                'updated_at' => $post->updated_at->toDateTimeString(),
                // ContentStorage fields
                'storage_driver' => $post->storage_driver ?? 'database',
                'storage_path' => $post->storage_path,
            ],
            'storageDrivers' => $this->getAvailableStorageDrivers(),
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
            // ContentStorage fields
            'storage_driver' => 'nullable|in:database,local,s3,azure,gcs,github,gitlab,bitbucket',
            'commit_message' => 'nullable|string|max:255',
        ]);

        // Validate commit message for Git-based storage
        if (isset($validated['storage_driver']) && $this->requiresCommitMessage($validated['storage_driver'])) {
            if (empty($validated['commit_message'])) {
                return back()
                    ->withInput()
                    ->withErrors(['commit_message' => 'Commit message is required for GitHub/GitLab/Bitbucket storage.']);
            }
        }

        DB::beginTransaction();

        try {
            // Set author_id from authenticated user (don't accept from request)
            $validated['author_id'] = Auth::id();

            // Generate slug if not provided
            if (empty($validated['slug'])) {
                $validated['slug'] = Str::slug($validated['title']);
            }

            // Set default storage driver
            if (empty($validated['storage_driver'])) {
                $validated['storage_driver'] = 'database';
            }

            // Create post
            $post = Post::create($validated);

            // If using cloud storage, write content via ContentStorage
            if ($validated['storage_driver'] !== 'database' && !empty($validated['content_markdown'])) {
                $content = new ContentData(
                    markdown: $validated['content_markdown'],
                    html: $validated['content_html'] ?? null,
                    tableOfContents: $validated['table_of_contents'] ?? null,
                );

                // Prepare metadata for cloud storage
                $metadata = [];
                if (!empty($validated['commit_message'])) {
                    $metadata['commit_message'] = $validated['commit_message'];
                }

                $post->setContent($content, $metadata);
                $post->save();
            }

            // Sync categories if provided
            if ($request->has('category_ids')) {
                $post->categories()->sync($request->input('category_ids', []));
            }

            // Sync tags if provided
            if ($request->has('tag_ids')) {
                $post->tags()->sync($request->input('tag_ids', []));
            }

            DB::commit();

            return redirect()->route('admin.posts.edit', $post->id)
                ->with('success', 'Post created successfully.');

        } catch (\Exception $e) {
            DB::rollBack();

            \Log::error('Failed to create post', [
                'error' => $e->getMessage(),
                'storage_driver' => $validated['storage_driver'] ?? 'unknown',
            ]);

            return back()
                ->withInput()
                ->withErrors(['error' => 'Failed to create post: ' . $e->getMessage()]);
        }
    }

    /**
     * Update the specified post.
     */
    public function update(Request $request, int $id)
    {
        $post = Post::findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:posts,slug,' . $id,
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
            // ContentStorage fields
            'commit_message' => 'nullable|string|max:255',
        ]);

        // Validate commit message for Git-based storage
        if ($this->requiresCommitMessage($post->storage_driver)) {
            if (empty($validated['commit_message'])) {
                return back()
                    ->withInput()
                    ->withErrors(['commit_message' => 'Commit message is required for GitHub/GitLab/Bitbucket storage.']);
            }
        }

        DB::beginTransaction();

        try {
            // Set author_id from authenticated user (don't accept from request)
            $validated['author_id'] = Auth::id();

            // Generate slug if not provided
            if (empty($validated['slug'])) {
                $validated['slug'] = Str::slug($validated['title']);
            }

            // Update post
            $post->update($validated);

            // If using cloud storage and content changed, write to ContentStorage
            if ($post->storage_driver !== 'database' && isset($validated['content_markdown'])) {
                $content = new ContentData(
                    markdown: $validated['content_markdown'],
                    html: $validated['content_html'] ?? null,
                    tableOfContents: $validated['table_of_contents'] ?? null,
                );

                // Prepare metadata for cloud storage
                $metadata = [];
                if (!empty($validated['commit_message'])) {
                    $metadata['commit_message'] = $validated['commit_message'];
                }

                $post->setContent($content, $metadata);
                $post->save();
            }

            // Sync categories if provided
            if ($request->has('category_ids')) {
                $post->categories()->sync($request->input('category_ids', []));
            }

            // Sync tags if provided
            if ($request->has('tag_ids')) {
                $post->tags()->sync($request->input('tag_ids', []));
            }

            DB::commit();

            return redirect()->route('admin.posts.edit', $post->id)
                ->with('success', 'Post updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();

            \Log::error('Failed to update post', [
                'post_id' => $post->id,
                'error' => $e->getMessage(),
                'storage_driver' => $post->storage_driver,
            ]);

            return back()
                ->withInput()
                ->withErrors(['error' => 'Failed to update post: ' . $e->getMessage()]);
        }
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

    /**
     * Get revision history for a post
     */
    public function revisions(Post $post, Request $request)
    {
        $page = is_numeric($request->input('page')) ? (int) $request->input('page') : 1;
        $perPage = is_numeric($request->input('per_page')) ? (int) $request->input('per_page') : 10;

        $revisions = $post->revisionHistory($page, $perPage);

        return response()->json([
            'revisions' => $revisions->toArray(),
            'meta' => [
                'total' => $revisions->total(),
                'current_page' => $revisions->currentPage(),
                'per_page' => $revisions->perPage(),
                'has_more' => $revisions->hasMore(),
            ],
            'supports_revisions' => $post->supportsRevisions(),
            'storage_driver' => $post->storage_driver ?? 'database',
        ]);
    }

    /**
     * Get a specific revision
     */
    public function showRevision(Post $post, string $revisionId)
    {
        $revision = $post->getRevisionById($revisionId);

        if (!$revision) {
            return response()->json([
                'error' => 'Revision not found',
            ], 404);
        }

        return response()->json([
            'revision' => $revision->toArray(),
        ]);
    }

    /**
     * Restore a specific revision
     */
    public function restoreRevision(Post $post, string $revisionId)
    {
        if (!$post->supportsRevisions()) {
            return response()->json([
                'error' => 'Revisions are not supported for this storage driver',
            ], 400);
        }

        $success = $post->restoreRevisionById($revisionId);

        if (!$success) {
            return response()->json([
                'error' => 'Failed to restore revision',
            ], 500);
        }

        // Reload the post to get the restored content
        $post->refresh();

        return response()->json([
            'success' => true,
            'message' => 'Revision restored successfully',
            'post' => [
                'id' => $post->id,
                'content_markdown' => $post->content_markdown,
                'content_html' => $post->content_html,
                'table_of_contents' => $post->table_of_contents,
            ],
        ]);
    }
}
