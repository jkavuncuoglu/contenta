<?php

declare(strict_types=1);

namespace App\Domains\ContentManagement\Posts\Services;

use App\Domains\ContentManagement\Posts\Models\Post;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class PostService implements PostServiceContract
{
    /**
     * Get paginated posts
     *
     * @return LengthAwarePaginator<int, Post>
     */
    public function getPaginatedPosts(int $perPage = 20, ?string $status = null): LengthAwarePaginator
    {
        $query = Post::query()
            ->with(['categories', 'tags', 'author']);

        if ($status) {
            $query->where('status', $status);
        }

        return $query->latest()->paginate($perPage);
    }

    /**
     * Create a new post
     *
     * @param  array<string, mixed>  $data
     */
    public function createPost(array $data): Post
    {
        if (empty($data['slug']) && isset($data['title']) && is_string($data['title'])) {
            $data['slug'] = Str::slug($data['title']);
        }

        if (empty($data['status'])) {
            $data['status'] = 'draft';
        }

        return Post::create($data);
    }

    /**
     * Update a post
     *
     * @param  array<string, mixed>  $data
     */
    public function updatePost(Post $post, array $data): Post
    {
        if (isset($data['title']) && is_string($data['title']) && empty($data['slug'])) {
            $data['slug'] = Str::slug($data['title']);
        }

        $post->update($data);
        $freshPost = $post->fresh();
        assert($freshPost instanceof Post);

        return $freshPost;
    }

    /**
     * Delete a post
     */
    public function deletePost(Post $post): bool
    {
        return (bool) $post->delete();
    }

    /**
     * Publish a post
     */
    public function publishPost(Post $post): Post
    {
        $post->update([
            'status' => 'published',
            'published_at' => now(),
        ]);

        $freshPost = $post->fresh();
        assert($freshPost instanceof Post);

        return $freshPost;
    }

    /**
     * Unpublish a post
     */
    public function unpublishPost(Post $post): Post
    {
        $post->update([
            'status' => 'draft',
        ]);

        $freshPost = $post->fresh();
        assert($freshPost instanceof Post);

        return $freshPost;
    }

    /**
     * Schedule a post
     */
    public function schedulePost(Post $post, \DateTimeInterface $publishAt): Post
    {
        $post->update([
            'status' => 'scheduled',
            'published_at' => $publishAt,
        ]);

        $freshPost = $post->fresh();
        assert($freshPost instanceof Post);

        return $freshPost;
    }

    /**
     * Get post by slug
     */
    public function getPostBySlug(string $slug): ?Post
    {
        return Post::where('slug', $slug)
            ->where('status', 'published')
            ->first();
    }

    /**
     * Duplicate a post
     */
    public function duplicatePost(Post $post, string $newTitle): Post
    {
        $newPost = $post->replicate();
        $newPost->title = $newTitle;
        $newPost->slug = Str::slug($newTitle);
        $newPost->status = 'draft';
        $newPost->published_at = null;

        // Generate new storage path if using cloud storage
        if ($newPost->storage_driver !== 'database') {
            $newPost->storage_path = $newPost->generateStoragePath();
        }

        $newPost->save();

        // Copy content to new location if using cloud storage
        if ($post->storage_driver !== 'database' && $post->storage_path) {
            $content = $post->getContent();
            if ($content) {
                $newPost->setContent($content);
                $newPost->save();
            }
        }

        // Copy relationships
        if ($post->categories()->exists()) {
            $newPost->categories()->attach($post->categories->pluck('id'));
        }

        if ($post->tags()->exists()) {
            $newPost->tags()->attach($post->tags->pluck('id'));
        }

        return $newPost;
    }

    /**
     * Get published posts
     *
     * @return LengthAwarePaginator<int, Post>
     */
    public function getPublishedPosts(int $perPage = 20): LengthAwarePaginator
    {
        return Post::query()
            ->with(['categories', 'tags', 'author'])
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->latest('published_at')
            ->paginate($perPage);
    }

    /**
     * Get draft posts
     *
     * @return LengthAwarePaginator<int, Post>
     */
    public function getDraftPosts(int $perPage = 20): LengthAwarePaginator
    {
        return Post::query()
            ->with(['categories', 'tags', 'author'])
            ->where('status', 'draft')
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Attach categories to post
     *
     * @param  array<int, int>  $categoryIds
     */
    public function attachCategories(Post $post, array $categoryIds): Post
    {
        $post->categories()->sync($categoryIds);
        $freshPost = $post->fresh();
        assert($freshPost instanceof Post);

        return $freshPost;
    }

    /**
     * Attach tags to post
     *
     * @param  array<int, int>  $tagIds
     */
    public function attachTags(Post $post, array $tagIds): Post
    {
        $post->tags()->sync($tagIds);
        $freshPost = $post->fresh();
        assert($freshPost instanceof Post);

        return $freshPost;
    }

    /**
     * Get posts for calendar view (by date range)
     *
     * @return array<int, Post>
     */
    public function getCalendarPosts(\DateTimeInterface $startDate, \DateTimeInterface $endDate): array
    {
        return Post::query()
            ->with(['categories', 'tags', 'author'])
            ->whereBetween('published_at', [$startDate, $endDate])
            ->whereIn('status', ['published', 'scheduled'])
            ->orderBy('published_at')
            ->get()
            ->all();
    }

    /**
     * Get scheduled posts
     *
     * @return LengthAwarePaginator<int, Post>
     */
    public function getScheduledPosts(int $perPage = 20): LengthAwarePaginator
    {
        return Post::query()
            ->with(['categories', 'tags', 'author'])
            ->where('status', 'scheduled')
            ->whereNotNull('published_at')
            ->orderBy('published_at')
            ->paginate($perPage);
    }

    /**
     * Publish posts that are due to be published
     *
     * @return array<int, Post>
     */
    public function publishDuePosts(): array
    {
        $duePosts = Post::query()
            ->where('status', 'scheduled')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->get();

        $published = [];

        foreach ($duePosts as $post) {
            $post->update([
                'status' => 'published',
            ]);
            $published[] = $post;
        }

        return $published;
    }

    /**
     * Get posts by status
     *
     * @return LengthAwarePaginator<int, Post>
     */
    public function getPostsByStatus(string $status, int $perPage = 20): LengthAwarePaginator
    {
        return Post::query()
            ->with(['categories', 'tags', 'author'])
            ->where('status', $status)
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Get archived posts
     *
     * @return LengthAwarePaginator<int, Post>
     */
    public function getArchivedPosts(int $perPage = 20): LengthAwarePaginator
    {
        return Post::onlyTrashed()
            ->with(['categories', 'tags', 'author'])
            ->latest('deleted_at')
            ->paginate($perPage);
    }

    /**
     * Restore archived post
     */
    public function restorePost(int $postId): Post
    {
        $post = Post::onlyTrashed()->findOrFail($postId);
        $post->restore();

        // Set status to draft when restoring
        $post->update(['status' => 'draft']);

        $freshPost = $post->fresh();
        assert($freshPost instanceof Post);

        return $freshPost;
    }

    /**
     * Change post status
     */
    public function changeStatus(Post $post, string $status): Post
    {
        $updateData = ['status' => $status];

        // If publishing, set published_at if not already set
        if ($status === 'published' && ! $post->published_at) {
            $updateData['published_at'] = now();
        }

        $post->update($updateData);

        $freshPost = $post->fresh();
        assert($freshPost instanceof Post);

        return $freshPost;
    }
}
