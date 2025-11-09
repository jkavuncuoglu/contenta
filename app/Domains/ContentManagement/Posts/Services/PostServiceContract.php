<?php

declare(strict_types=1);

namespace App\Domains\ContentManagement\Posts\Services;

use App\Domains\ContentManagement\Posts\Models\Post;
use Illuminate\Pagination\LengthAwarePaginator;

interface PostServiceContract
{
    /**
     * Get paginated posts
     */
    public function getPaginatedPosts(int $perPage = 20, ?string $status = null): LengthAwarePaginator;

    /**
     * Create a new post
     */
    public function createPost(array $data): Post;

    /**
     * Update a post
     */
    public function updatePost(Post $post, array $data): Post;

    /**
     * Delete a post
     */
    public function deletePost(Post $post): bool;

    /**
     * Publish a post
     */
    public function publishPost(Post $post): Post;

    /**
     * Unpublish a post
     */
    public function unpublishPost(Post $post): Post;

    /**
     * Schedule a post
     */
    public function schedulePost(Post $post, \DateTimeInterface $publishAt): Post;

    /**
     * Get post by slug
     */
    public function getPostBySlug(string $slug): ?Post;

    /**
     * Duplicate a post
     */
    public function duplicatePost(Post $post, string $newTitle): Post;

    /**
     * Get published posts
     */
    public function getPublishedPosts(int $perPage = 20): LengthAwarePaginator;

    /**
     * Get draft posts
     */
    public function getDraftPosts(int $perPage = 20): LengthAwarePaginator;

    /**
     * Attach categories to post
     */
    public function attachCategories(Post $post, array $categoryIds): Post;

    /**
     * Attach tags to post
     */
    public function attachTags(Post $post, array $tagIds): Post;
}
