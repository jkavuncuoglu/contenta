<?php

declare(strict_types=1);

namespace App\Domains\SocialMedia\Constants;

class SocialPlatform
{
    public const TWITTER = 'twitter';

    public const FACEBOOK = 'facebook';

    public const LINKEDIN = 'linkedin';

    public const INSTAGRAM = 'instagram';

    public const PINTEREST = 'pinterest';

    public const TIKTOK = 'tiktok';

    /**
     * Get all available platforms.
     */
    public static function all(): array
    {
        return [
            self::TWITTER,
            self::FACEBOOK,
            self::LINKEDIN,
            self::INSTAGRAM,
            self::PINTEREST,
            self::TIKTOK,
        ];
    }

    /**
     * Get platform display names.
     */
    public static function names(): array
    {
        return [
            self::TWITTER => 'Twitter/X',
            self::FACEBOOK => 'Facebook',
            self::LINKEDIN => 'LinkedIn',
            self::INSTAGRAM => 'Instagram',
            self::PINTEREST => 'Pinterest',
            self::TIKTOK => 'TikTok',
        ];
    }

    /**
     * Get platform icons (Iconify format).
     */
    public static function icons(): array
    {
        return [
            self::TWITTER => 'mdi:twitter',
            self::FACEBOOK => 'mdi:facebook',
            self::LINKEDIN => 'mdi:linkedin',
            self::INSTAGRAM => 'mdi:instagram',
            self::PINTEREST => 'mdi:pinterest',
            self::TIKTOK => 'ic:baseline-tiktok',
        ];
    }

    /**
     * Get platform colors (for UI).
     */
    public static function colors(): array
    {
        return [
            self::TWITTER => '#1DA1F2',
            self::FACEBOOK => '#1877F2',
            self::LINKEDIN => '#0A66C2',
            self::INSTAGRAM => '#E4405F',
            self::PINTEREST => '#E60023',
            self::TIKTOK => '#000000',
        ];
    }

    /**
     * Check if platform is valid.
     */
    public static function isValid(string $platform): bool
    {
        return in_array($platform, self::all(), true);
    }

    /**
     * Get platform display name.
     */
    public static function getName(string $platform): string
    {
        return self::names()[$platform] ?? $platform;
    }

    /**
     * Get platform icon.
     */
    public static function getIcon(string $platform): string
    {
        return self::icons()[$platform] ?? 'mdi:link';
    }

    /**
     * Get platform color.
     */
    public static function getColor(string $platform): string
    {
        return self::colors()[$platform] ?? '#000000';
    }
}
