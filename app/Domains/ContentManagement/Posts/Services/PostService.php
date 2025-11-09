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
     */
    public function createPost(array $data): Post
    {
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['title']);
        }

        if (empty($data['status'])) {
            $data['status'] = 'draft';
        }

        return Post::create($data);
    }

    /**
     * Update a post
     */
    public function updatePost(Post $post, array $data): Post
    {
        if (isset($data['title']) && empty($data['slug'])) {
            $data['slug'] = Str::slug($data['title']);
        }

        $post->update($data);
        return $post->fresh();
    }

    /**
     * Delete a post
     */
    public function deletePost(Post $post): bool
    {
        return $post->delete();
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

        return $post->fresh();
    }

    /**
     * Unpublish a post
     */
    public function unpublishPost(Post $post): Post
    {
        $post->update([
            'status' => 'draft',
        ]);

        return $post->fresh();
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

        return $post->fresh();
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
        $newPost->save();

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
     */
    public function attachCategories(Post $post, array $categoryIds): Post
    {
        $post->categories()->sync($categoryIds);
        return $post->fresh();
    }

    /**
     * Attach tags to post
     */
    public function attachTags(Post $post, array $tagIds): Post
    {
        $post->tags()->sync($tagIds);
        return $post->fresh();
    }
}
