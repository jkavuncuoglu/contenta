<?php

declare(strict_types=1);

namespace App\Domains\SocialMedia\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SocialAnalytics extends Model
{
    use HasFactory;

    protected $fillable = [
        'social_post_id',
        'likes',
        'shares',
        'comments',
        'reach',
        'impressions',
        'clicks',
        'saves',
        'engagement_rate',
        'platform_metrics',
        'synced_at',
    ];

    protected $casts = [
        'likes' => 'integer',
        'shares' => 'integer',
        'comments' => 'integer',
        'reach' => 'integer',
        'impressions' => 'integer',
        'clicks' => 'integer',
        'saves' => 'integer',
        'engagement_rate' => 'decimal:2',
        'platform_metrics' => 'array',
        'synced_at' => 'datetime',
    ];

    /**
     * Get the social post.
     */
    public function socialPost(): BelongsTo
    {
        return $this->belongsTo(SocialPost::class);
    }

    /**
     * Calculate engagement rate.
     */
    public function calculateEngagementRate(): float
    {
        if ($this->impressions === 0) {
            return 0.0;
        }

        $engagements = $this->likes + $this->shares + $this->comments + $this->clicks;

        return round(($engagements / $this->impressions) * 100, 2);
    }

    /**
     * Get total engagements.
     */
    public function getTotalEngagementsAttribute(): int
    {
        return $this->likes + $this->shares + $this->comments + $this->clicks + $this->saves;
    }

    /**
     * Scope to get analytics within date range.
     */
    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('synced_at', [$startDate, $endDate]);
    }

    /**
     * Scope to get latest analytics per post.
     */
    public function scopeLatest($query)
    {
        return $query->orderBy('synced_at', 'desc');
    }
}
