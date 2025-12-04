<?php

declare(strict_types=1);

namespace App\Domains\SocialMedia\Constants;

class OAuthScopes
{
    /**
     * Twitter/X OAuth scopes.
     */
    public const TWITTER = [
        'tweet.read',
        'tweet.write',
        'users.read',
        'offline.access',  // For refresh token
    ];

    /**
     * Facebook OAuth scopes.
     */
    public const FACEBOOK = [
        'pages_show_list',
        'pages_read_engagement',
        'pages_manage_posts',
        'pages_read_user_content',
    ];

    /**
     * LinkedIn OAuth scopes.
     */
    public const LINKEDIN = [
        'r_liteprofile',
        'r_emailaddress',
        'w_member_social',
        'r_organization_social',
        'w_organization_social',
    ];

    /**
     * Instagram OAuth scopes (via Facebook Graph API).
     */
    public const INSTAGRAM = [
        'instagram_basic',
        'instagram_content_publish',
        'pages_show_list',
        'pages_read_engagement',
    ];

    /**
     * Pinterest OAuth scopes.
     */
    public const PINTEREST = [
        'user_accounts:read',
        'pins:read',
        'pins:write',
        'boards:read',
        'boards:write',
    ];

    /**
     * TikTok OAuth scopes.
     */
    public const TIKTOK = [
        'user.info.basic',
        'video.publish',
        'video.upload',
    ];

    /**
     * Get scopes for a specific platform.
     */
    public static function forPlatform(string $platform): array
    {
        return match ($platform) {
            SocialPlatform::TWITTER => self::TWITTER,
            SocialPlatform::FACEBOOK => self::FACEBOOK,
            SocialPlatform::LINKEDIN => self::LINKEDIN,
            SocialPlatform::INSTAGRAM => self::INSTAGRAM,
            SocialPlatform::PINTEREST => self::PINTEREST,
            SocialPlatform::TIKTOK => self::TIKTOK,
            default => [],
        };
    }

    /**
     * Get scope string (space-separated) for platform.
     */
    public static function toString(string $platform): string
    {
        return implode(' ', self::forPlatform($platform));
    }
}
