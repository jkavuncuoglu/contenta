<?php

declare(strict_types=1);

namespace App\Domains\SocialMedia\Models;

use App\Domains\ContentManagement\Posts\Models\Post;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BlogPostSocialQueue extends Model
{
    use HasFactory;

    protected $table = 'blog_post_social_queue';

    protected $fillable = [
        'blog_post_id',
        'social_account_id',
        'status',
        'scheduled_for',
        'generated_content',
        'social_post_id',
        'has_manual_override',
    ];

    protected $casts = [
        'scheduled_for' => 'datetime',
        'has_manual_override' => 'boolean',
    ];

    /**
     * Get the blog post.
     */
    public function blogPost(): BelongsTo
    {
        return $this->belongsTo(Post::class, 'blog_post_id');
    }

    /**
     * Get the social account.
     */
    public function socialAccount(): BelongsTo
    {
        return $this->belongsTo(SocialAccount::class);
    }

    /**
     * Get the created social post (after processing).
     */
    public function socialPost(): BelongsTo
    {
        return $this->belongsTo(SocialPost::class);
    }

    /**
     * Scope to get pending queue entries.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope to get scheduled queue entries.
     */
    public function scopeScheduled($query)
    {
        return $query->where('status', 'scheduled');
    }

    /**
     * Scope to get posted queue entries.
     */
    public function scopePosted($query)
    {
        return $query->where('status', 'posted');
    }

    /**
     * Scope to get entries due for processing.
     */
    public function scopeDueForProcessing($query)
    {
        return $query->whereIn('status', ['pending', 'scheduled'])
            ->where('scheduled_for', '<=', now())
            ->where('has_manual_override', false);
    }

    /**
     * Scope to filter by platform.
     */
    public function scopePlatform($query, string $platform)
    {
        return $query->whereHas('socialAccount', function ($q) use ($platform) {
            $q->where('platform', $platform);
        });
    }

    /**
     * Check if entry is pending.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if entry is posted.
     */
    public function isPosted(): bool
    {
        return $this->status === 'posted';
    }

    /**
     * Check if has manual override.
     */
    public function hasManualOverride(): bool
    {
        return $this->has_manual_override;
    }
}
