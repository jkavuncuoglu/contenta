<?php

declare(strict_types=1);

namespace App\Domains\SocialMedia\Services;

use App\Domains\ContentManagement\Posts\Models\Post;
use App\Domains\SocialMedia\Models\SocialAccount;
use App\Domains\SocialMedia\Models\SocialPost;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface SocialMediaServiceContract
{
    /**
     * Create a new social post.
     */
    public function createPost(array $data): SocialPost;

    /**
     * Update an existing social post.
     */
    public function updatePost(SocialPost $post, array $data): SocialPost;

    /**
     * Delete a social post.
     */
    public function deletePost(SocialPost $post): bool;

    /**
     * Schedule a post for publishing.
     */
    public function schedulePost(SocialPost $post, DateTimeInterface $scheduledAt): SocialPost;

    /**
     * Publish a post immediately.
     */
    public function publishPost(SocialPost $post): SocialPost;

    /**
     * Publish all posts that are due.
     */
    public function publishDuePosts(): array;

    /**
     * Check for scheduling conflicts.
     */
    public function checkConflicts(SocialAccount $account, DateTimeInterface $scheduledAt): array;

    /**
     * Generate post content from blog post.
     */
    public function generatePostFromBlog(Post $blogPost, SocialAccount $account): string;

    /**
     * Get posts by account with optional filters.
     */
    public function getPostsByAccount(SocialAccount $account, array $filters = []): Collection;

    /**
     * Get scheduled posts (paginated).
     */
    public function getScheduledPosts(int $perPage = 20): LengthAwarePaginator;

    /**
     * Get posts for calendar (within date range).
     */
    public function getCalendarPosts(DateTimeInterface $start, DateTimeInterface $end, array $filters = []): array;

    /**
     * Cancel a scheduled post.
     */
    public function cancelPost(SocialPost $post): SocialPost;

    /**
     * Retry a failed post.
     */
    public function retryPost(SocialPost $post): SocialPost;
}
