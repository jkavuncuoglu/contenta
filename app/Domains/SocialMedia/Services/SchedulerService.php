<?php

declare(strict_types=1);

namespace App\Domains\SocialMedia\Services;

use App\Domains\ContentManagement\Posts\Models\Post;
use App\Domains\SocialMedia\Constants\PostStatus;
use App\Domains\SocialMedia\Models\BlogPostSocialQueue;
use App\Domains\SocialMedia\Models\SocialAccount;
use App\Domains\SocialMedia\Models\SocialPost;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Service for managing social media scheduling and auto-posting queue.
 */
class SchedulerService implements SchedulerServiceContract
{
    public function __construct(
        protected SocialMediaServiceContract $socialMediaService
    ) {}

    /**
     * Queue a blog post for auto-posting to all enabled social accounts.
     */
    public function queueBlogPost(Post $blogPost): array
    {
        $accounts = SocialAccount::where('auto_post_enabled', true)
            ->where('is_active', true)
            ->get();

        $queueEntries = [];

        foreach ($accounts as $account) {
            // Skip if already queued
            if ($this->isQueued($blogPost, $account->id)) {
                continue;
            }

            // Calculate scheduled time based on mode
            $scheduledFor = $this->calculateScheduledTime($account);

            // Generate preview content
            $generatedContent = $this->socialMediaService->generatePostFromBlog($blogPost, $account);

            $queueEntry = BlogPostSocialQueue::create([
                'blog_post_id' => $blogPost->id,
                'social_account_id' => $account->id,
                'status' => 'pending',
                'scheduled_for' => $scheduledFor,
                'generated_content' => $generatedContent,
            ]);

            $queueEntries[] = $queueEntry;

            Log::info('Queued blog post for social media', [
                'blog_post_id' => $blogPost->id,
                'social_account_id' => $account->id,
                'platform' => $account->platform,
                'scheduled_for' => $scheduledFor->toIso8601String(),
                'mode' => $account->auto_post_mode,
            ]);
        }

        return $queueEntries;
    }

    /**
     * Process immediate queue (post immediately).
     */
    public function processImmediateQueue(): int
    {
        $queueEntries = BlogPostSocialQueue::where('status', 'pending')
            ->whereHas('socialAccount', function ($query) {
                $query->where('auto_post_mode', 'immediate')
                    ->where('is_active', true);
            })
            ->where('scheduled_for', '<=', now())
            ->with(['blogPost', 'socialAccount'])
            ->get();

        $count = 0;

        foreach ($queueEntries as $entry) {
            if ($this->createSocialPostFromQueue($entry)) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Process scheduled queue (post at scheduled time).
     */
    public function processScheduledQueue(?string $time = null): int
    {
        $targetTime = $time ? Carbon::parse($time) : now();

        $queueEntries = BlogPostSocialQueue::where('status', 'pending')
            ->whereHas('socialAccount', function ($query) {
                $query->where('auto_post_mode', 'scheduled')
                    ->where('is_active', true);
            })
            ->where('scheduled_for', '<=', $targetTime)
            ->with(['blogPost', 'socialAccount'])
            ->get();

        $count = 0;

        foreach ($queueEntries as $entry) {
            if ($this->createSocialPostFromQueue($entry)) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Get conflicts for a blog post.
     */
    public function getBlogPostConflicts(Post $blogPost): array
    {
        $conflicts = [];

        $accounts = SocialAccount::where('auto_post_enabled', true)
            ->where('is_active', true)
            ->get();

        foreach ($accounts as $account) {
            $scheduledFor = $this->calculateScheduledTime($account);

            // Check for manual posts within 15-minute window
            $conflictingPosts = $this->socialMediaService->checkConflicts($account, $scheduledFor);

            if ($conflictingPosts['has_conflicts']) {
                $conflicts[] = [
                    'account' => $account,
                    'scheduled_for' => $scheduledFor,
                    'existing_posts' => $conflictingPosts['existing_posts'],
                ];
            }
        }

        return $conflicts;
    }

    /**
     * Remove a blog post from the queue (manual override).
     */
    public function removeFromQueue(BlogPostSocialQueue $queueEntry): bool
    {
        return $queueEntry->update([
            'status' => 'cancelled',
            'has_manual_override' => true,
        ]);
    }

    /**
     * Check if a blog post is already queued for an account.
     */
    public function isQueued(Post $blogPost, int $accountId): bool
    {
        return BlogPostSocialQueue::where('blog_post_id', $blogPost->id)
            ->where('social_account_id', $accountId)
            ->whereIn('status', ['pending', 'scheduled', 'posted'])
            ->exists();
    }

    /**
     * Calculate scheduled time based on account's auto-post mode.
     */
    protected function calculateScheduledTime(SocialAccount $account): Carbon
    {
        if ($account->auto_post_mode === 'immediate') {
            return now();
        }

        // Scheduled mode: find next occurrence of scheduled_post_time
        $scheduledTime = $account->scheduled_post_time ?? '09:00:00';
        [$hour, $minute] = explode(':', $scheduledTime);

        $nextOccurrence = now()->setTime((int) $hour, (int) $minute, 0);

        // If time has already passed today, schedule for tomorrow
        if ($nextOccurrence->isPast()) {
            $nextOccurrence->addDay();
        }

        return $nextOccurrence;
    }

    /**
     * Create a SocialPost from a queue entry.
     */
    protected function createSocialPostFromQueue(BlogPostSocialQueue $entry): bool
    {
        DB::beginTransaction();

        try {
            // Create social post
            $socialPost = SocialPost::create([
                'social_account_id' => $entry->social_account_id,
                'user_id' => $entry->blogPost->user_id,
                'source_type' => 'auto_blog_post',
                'source_blog_post_id' => $entry->blog_post_id,
                'content' => $entry->generated_content,
                'media_urls' => [],
                'link_url' => route('posts.show', $entry->blogPost->slug),
                'status' => PostStatus::SCHEDULED,
                'scheduled_at' => now(), // Publish immediately
            ]);

            // Update queue entry
            $entry->update([
                'status' => 'posted',
                'social_post_id' => $socialPost->id,
            ]);

            DB::commit();

            Log::info('Created social post from queue', [
                'queue_entry_id' => $entry->id,
                'social_post_id' => $socialPost->id,
                'blog_post_id' => $entry->blog_post_id,
                'platform' => $entry->socialAccount->platform,
            ]);

            return true;
        } catch (\Exception $e) {
            DB::rollBack();

            // Mark queue entry as failed
            $entry->update([
                'status' => 'failed',
            ]);

            Log::error('Failed to create social post from queue', [
                'queue_entry_id' => $entry->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }
}
