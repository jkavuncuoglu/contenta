<?php

declare(strict_types=1);

namespace App\Domains\SocialMedia\Services;

use App\Domains\SocialMedia\Models\SocialAccount;
use App\Domains\SocialMedia\Models\SocialAnalytics;
use App\Domains\SocialMedia\Models\SocialPost;
use App\Models\User;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Collection;

interface AnalyticsServiceContract
{
    /**
     * Sync analytics for a specific post.
     */
    public function syncPostAnalytics(SocialPost $post): SocialAnalytics;

    /**
     * Sync analytics for all posts in an account.
     */
    public function syncAccountAnalytics(SocialAccount $account): int;

    /**
     * Get account summary (aggregated metrics).
     */
    public function getAccountSummary(SocialAccount $account, DateTimeInterface $start, DateTimeInterface $end): array;

    /**
     * Get top performing posts.
     */
    public function getTopPosts(SocialAccount $account, int $limit = 10): Collection;

    /**
     * Get platform comparison for a user.
     */
    public function getPlatformComparison(User $user): array;

    /**
     * Get analytics trends over time.
     */
    public function getTrends(SocialAccount $account, DateTimeInterface $start, DateTimeInterface $end): array;
}
