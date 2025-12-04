<?php

declare(strict_types=1);

namespace App\Domains\SocialMedia\Services;

use App\Domains\SocialMedia\Constants\PostStatus;
use App\Domains\SocialMedia\Models\SocialAccount;
use App\Domains\SocialMedia\Models\SocialPost;
use App\Domains\SocialMedia\Services\PlatformAdapters\SocialPlatformInterface;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SocialMediaService implements SocialMediaServiceContract
{
    public function __construct(
        protected OAuthServiceContract $oauthService
    ) {}

    /**
     * Create a new social media post.
     */
    public function createPost(array $data): SocialPost
    {
        $account = SocialAccount::findOrFail($data['social_account_id']);

        // Get platform adapter for validation
        $adapter = $this->getPlatformAdapter($account->platform);

        // Validate content
        $errors = $adapter->validateContent(
            $data['content'],
            $data['media_urls'] ?? []
        );

        if (! empty($errors)) {
            throw new \InvalidArgumentException('Content validation failed: '.implode(', ', $errors));
        }

        $post = SocialPost::create([
            'social_account_id' => $data['social_account_id'],
            'user_id' => $data['user_id'],
            'source_type' => $data['source_type'] ?? 'manual',
            'source_blog_post_id' => $data['source_blog_post_id'] ?? null,
            'content' => $data['content'],
            'media_urls' => $data['media_urls'] ?? null,
            'link_url' => $data['link_url'] ?? null,
            'status' => $data['status'] ?? PostStatus::DRAFT,
            'scheduled_at' => isset($data['scheduled_at']) ? Carbon::parse($data['scheduled_at']) : null,
        ]);

        Log::info('Social post created', [
            'post_id' => $post->id,
            'account_id' => $account->id,
            'platform' => $account->platform,
            'status' => $post->status,
        ]);

        return $post;
    }

    /**
     * Update an existing social media post.
     */
    public function updatePost(SocialPost $post, array $data): SocialPost
    {
        // Only allow updates if post is draft or scheduled
        if (! in_array($post->status, [PostStatus::DRAFT, PostStatus::SCHEDULED])) {
            throw new \InvalidArgumentException('Cannot update post with status: '.$post->status);
        }

        // Validate content if changed
        if (isset($data['content'])) {
            $adapter = $this->getPlatformAdapter($post->socialAccount->platform);
            $errors = $adapter->validateContent(
                $data['content'],
                $data['media_urls'] ?? $post->media_urls ?? []
            );

            if (! empty($errors)) {
                throw new \InvalidArgumentException('Content validation failed: '.implode(', ', $errors));
            }
        }

        $post->update([
            'content' => $data['content'] ?? $post->content,
            'media_urls' => $data['media_urls'] ?? $post->media_urls,
            'link_url' => $data['link_url'] ?? $post->link_url,
            'status' => $data['status'] ?? $post->status,
            'scheduled_at' => isset($data['scheduled_at']) ? Carbon::parse($data['scheduled_at']) : $post->scheduled_at,
        ]);

        Log::info('Social post updated', [
            'post_id' => $post->id,
            'status' => $post->status,
        ]);

        return $post->fresh();
    }

    /**
     * Delete a social media post.
     */
    public function deletePost(SocialPost $post): bool
    {
        // If published, try to delete from platform
        if ($post->status === PostStatus::PUBLISHED && $post->platform_post_id) {
            try {
                $adapter = $this->getPlatformAdapter($post->socialAccount->platform);
                $adapter->deletePost($post->platform_post_id);
            } catch (\Exception $e) {
                Log::error('Failed to delete post from platform', [
                    'post_id' => $post->id,
                    'platform_post_id' => $post->platform_post_id,
                    'error' => $e->getMessage(),
                ]);
                // Continue with local deletion even if platform deletion fails
            }
        }

        Log::info('Social post deleted', [
            'post_id' => $post->id,
            'status' => $post->status,
        ]);

        return $post->delete();
    }

    /**
     * Schedule a post for future publishing.
     */
    public function schedulePost(SocialPost $post, \DateTimeInterface $scheduledAt): SocialPost
    {
        if ($post->status !== PostStatus::DRAFT) {
            throw new \InvalidArgumentException('Can only schedule draft posts');
        }

        $post->update([
            'status' => PostStatus::SCHEDULED,
            'scheduled_at' => $scheduledAt,
        ]);

        Log::info('Social post scheduled', [
            'post_id' => $post->id,
            'scheduled_at' => $scheduledAt->format('Y-m-d H:i:s'),
        ]);

        return $post->fresh();
    }

    /**
     * Publish a post immediately.
     */
    public function publishPost(SocialPost $post): SocialPost
    {
        if (! in_array($post->status, [PostStatus::DRAFT, PostStatus::SCHEDULED, PostStatus::FAILED])) {
            throw new \InvalidArgumentException('Cannot publish post with status: '.$post->status);
        }

        DB::beginTransaction();

        try {
            // Update status to publishing
            $post->update(['status' => PostStatus::PUBLISHING]);

            // Get platform adapter
            $adapter = $this->getPlatformAdapter($post->socialAccount->platform);

            // Publish to platform
            $result = $adapter->publishPost($post);

            // Update post with platform details
            $post->update([
                'status' => PostStatus::PUBLISHED,
                'platform_post_id' => $result['id'],
                'platform_permalink' => $result['permalink'],
                'published_at' => now(),
                'error_message' => null,
                'retry_count' => 0,
            ]);

            DB::commit();

            Log::info('Social post published', [
                'post_id' => $post->id,
                'platform_post_id' => $result['id'],
                'platform' => $post->socialAccount->platform,
            ]);

            return $post->fresh();
        } catch (\Exception $e) {
            DB::rollBack();

            // Update with error
            $post->update([
                'status' => PostStatus::FAILED,
                'error_message' => $e->getMessage(),
                'retry_count' => $post->retry_count + 1,
            ]);

            Log::error('Failed to publish social post', [
                'post_id' => $post->id,
                'error' => $e->getMessage(),
                'retry_count' => $post->retry_count,
            ]);

            throw $e;
        }
    }

    /**
     * Publish all due posts (scheduled_at <= now, status = scheduled).
     */
    public function publishDuePosts(): array
    {
        $duePosts = SocialPost::where('status', PostStatus::SCHEDULED)
            ->where('scheduled_at', '<=', now())
            ->with('socialAccount')
            ->get();

        $published = [];

        foreach ($duePosts as $post) {
            try {
                $this->publishPost($post);
                $published[] = $post;
            } catch (\Exception $e) {
                // Error already logged in publishPost
                continue;
            }
        }

        return $published;
    }

    /**
     * Check for scheduling conflicts.
     */
    public function checkConflicts(SocialAccount $account, \DateTimeInterface $scheduledAt): array
    {
        $windowStart = Carbon::instance($scheduledAt)->subMinutes(15);
        $windowEnd = Carbon::instance($scheduledAt)->addMinutes(15);

        // Check existing scheduled posts
        $existingPosts = SocialPost::where('social_account_id', $account->id)
            ->where('status', PostStatus::SCHEDULED)
            ->whereBetween('scheduled_at', [$windowStart, $windowEnd])
            ->get();

        return [
            'has_conflicts' => $existingPosts->isNotEmpty(),
            'existing_posts' => $existingPosts,
        ];
    }

    /**
     * Generate post content from blog post.
     */
    public function generatePostFromBlog($blogPost, SocialAccount $account): string
    {
        // Simple template for now
        $title = $blogPost->title;
        $url = url("/blog/{$blogPost->slug}");

        // Get character limit for platform
        $adapter = $this->getPlatformAdapter($account->platform);
        $limit = $adapter->getCharacterLimit();

        // Generate content with link
        $content = "{$title}\n\n{$url}";

        // Truncate if needed
        if (mb_strlen($content) > $limit) {
            $maxTitleLength = $limit - mb_strlen($url) - 10; // Leave room for URL and ellipsis
            $title = mb_substr($title, 0, $maxTitleLength).'...';
            $content = "{$title}\n\n{$url}";
        }

        return $content;
    }

    /**
     * Get posts by account with filters.
     */
    public function getPostsByAccount(SocialAccount $account, array $filters = []): Collection
    {
        $query = $account->socialPosts();

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['source_type'])) {
            $query->where('source_type', $filters['source_type']);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    /**
     * Get scheduled posts with pagination.
     */
    public function getScheduledPosts(int $perPage = 20): LengthAwarePaginator
    {
        return SocialPost::where('status', PostStatus::SCHEDULED)
            ->with('socialAccount')
            ->orderBy('scheduled_at', 'asc')
            ->paginate($perPage);
    }

    /**
     * Get posts for calendar view.
     */
    public function getCalendarPosts(\DateTimeInterface $start, \DateTimeInterface $end, array $filters = []): array
    {
        $query = SocialPost::whereBetween('scheduled_at', [$start, $end])
            ->with('socialAccount');

        if (isset($filters['platform'])) {
            $query->whereHas('socialAccount', fn ($q) => $q->where('platform', $filters['platform']));
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->orderBy('scheduled_at', 'asc')
            ->get()
            ->map(function ($post) {
                return [
                    'id' => $post->id,
                    'type' => 'social',
                    'platform' => $post->socialAccount->platform,
                    'title' => \Illuminate\Support\Str::limit($post->content, 50),
                    'status' => $post->status,
                    'date' => $post->scheduled_at?->toIso8601String(),
                    'url' => route('admin.social-media.posts.edit', $post->id),
                ];
            })
            ->toArray();
    }

    /**
     * Get platform adapter instance.
     */
    protected function getPlatformAdapter(string $platform): SocialPlatformInterface
    {
        return $this->oauthService->getPlatformAdapter($platform);
    }
}
