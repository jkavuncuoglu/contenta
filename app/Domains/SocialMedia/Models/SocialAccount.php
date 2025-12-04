<?php

declare(strict_types=1);

namespace App\Domains\SocialMedia\Models;

use App\Domains\SocialMedia\Constants\SocialPlatform;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class SocialAccount extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    protected $fillable = [
        'platform',
        'platform_account_id',
        'platform_username',
        'platform_display_name',
        'access_token',
        'refresh_token',
        'token_expires_at',
        'auto_post_enabled',
        'auto_post_mode',
        'scheduled_post_time',
        'platform_settings',
        'last_synced_at',
        'is_active',
    ];

    protected $casts = [
        'token_expires_at' => 'datetime',
        'auto_post_enabled' => 'boolean',
        'platform_settings' => 'array',
        'last_synced_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    protected $hidden = [
        'access_token',
        'refresh_token',
    ];

    /**
     * Get all social posts for this account.
     */
    public function socialPosts(): HasMany
    {
        return $this->hasMany(SocialPost::class);
    }

    /**
     * Get all queue entries for this account.
     */
    public function queueEntries(): HasMany
    {
        return $this->hasMany(BlogPostSocialQueue::class);
    }

    /**
     * Encrypt the access token when setting.
     */
    public function setAccessTokenAttribute(?string $value): void
    {
        $this->attributes['access_token'] = $value ? encrypt($value) : null;
    }

    /**
     * Decrypt the access token when getting.
     */
    public function getAccessTokenAttribute(?string $value): ?string
    {
        if (! $value) {
            return null;
        }

        try {
            return decrypt($value);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Encrypt the refresh token when setting.
     */
    public function setRefreshTokenAttribute(?string $value): void
    {
        $this->attributes['refresh_token'] = $value ? encrypt($value) : null;
    }

    /**
     * Decrypt the refresh token when getting.
     */
    public function getRefreshTokenAttribute(?string $value): ?string
    {
        if (! $value) {
            return null;
        }

        try {
            return decrypt($value);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Scope to filter by platform.
     */
    public function scopePlatform($query, string $platform)
    {
        return $query->where('platform', $platform);
    }

    /**
     * Scope to get active accounts.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get accounts with auto-posting enabled.
     */
    public function scopeAutoPostEnabled($query)
    {
        return $query->where('auto_post_enabled', true);
    }

    /**
     * Scope to get accounts with immediate auto-post mode.
     */
    public function scopeImmediateMode($query)
    {
        return $query->where('auto_post_mode', 'immediate');
    }

    /**
     * Scope to get accounts with scheduled auto-post mode.
     */
    public function scopeScheduledMode($query)
    {
        return $query->where('auto_post_mode', 'scheduled');
    }

    /**
     * Scope to get accounts with expiring tokens.
     */
    public function scopeTokenExpiring($query, int $hours = 1)
    {
        return $query->where('token_expires_at', '<=', now()->addHours($hours))
            ->whereNotNull('token_expires_at')
            ->where('is_active', true);
    }

    /**
     * Check if token is expired.
     */
    public function isTokenExpired(): bool
    {
        if (! $this->token_expires_at) {
            return false;
        }

        return $this->token_expires_at->isPast();
    }

    /**
     * Check if token is expiring soon (within 1 hour).
     */
    public function isTokenExpiringSoon(): bool
    {
        if (! $this->token_expires_at) {
            return false;
        }

        return $this->token_expires_at->isBefore(now()->addHour());
    }

    /**
     * Get platform display name.
     */
    public function getPlatformNameAttribute(): string
    {
        return SocialPlatform::getName($this->platform);
    }

    /**
     * Get platform icon.
     */
    public function getPlatformIconAttribute(): string
    {
        return SocialPlatform::getIcon($this->platform);
    }

    /**
     * Get platform color.
     */
    public function getPlatformColorAttribute(): string
    {
        return SocialPlatform::getColor($this->platform);
    }

    /**
     * Get activity log options.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'platform',
                'platform_username',
                'auto_post_enabled',
                'auto_post_mode',
                'scheduled_post_time',
                'is_active',
            ])
            ->logOnlyDirty();
    }
}
