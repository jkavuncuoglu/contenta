<?php

declare(strict_types=1);

namespace App\Domains\SocialMedia\Services;

use App\Domains\ContentManagement\Posts\Models\Post;
use App\Domains\SocialMedia\Models\BlogPostSocialQueue;

interface SchedulerServiceContract
{
    /**
     * Queue a blog post for auto-posting to all enabled accounts.
     */
    public function queueBlogPost(Post $blogPost): array;

    /**
     * Process immediate queue (post immediately).
     */
    public function processImmediateQueue(): int;

    /**
     * Process scheduled queue (post at scheduled time).
     */
    public function processScheduledQueue(?string $time = null): int;

    /**
     * Get conflicts for a blog post.
     */
    public function getBlogPostConflicts(Post $blogPost): array;

    /**
     * Remove a blog post from the queue.
     */
    public function removeFromQueue(BlogPostSocialQueue $queueEntry): bool;

    /**
     * Check if a blog post is already queued.
     */
    public function isQueued(Post $blogPost, int $accountId): bool;
}
