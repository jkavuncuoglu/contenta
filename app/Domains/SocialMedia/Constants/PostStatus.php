<?php

declare(strict_types=1);

namespace App\Domains\SocialMedia\Constants;

class PostStatus
{
    public const DRAFT = 'draft';

    public const SCHEDULED = 'scheduled';

    public const PUBLISHING = 'publishing';

    public const PUBLISHED = 'published';

    public const FAILED = 'failed';

    public const CANCELLED = 'cancelled';

    /**
     * Get all statuses.
     */
    public static function all(): array
    {
        return [
            self::DRAFT,
            self::SCHEDULED,
            self::PUBLISHING,
            self::PUBLISHED,
            self::FAILED,
            self::CANCELLED,
        ];
    }

    /**
     * Get status display names.
     */
    public static function names(): array
    {
        return [
            self::DRAFT => 'Draft',
            self::SCHEDULED => 'Scheduled',
            self::PUBLISHING => 'Publishing',
            self::PUBLISHED => 'Published',
            self::FAILED => 'Failed',
            self::CANCELLED => 'Cancelled',
        ];
    }

    /**
     * Get status colors (for UI).
     */
    public static function colors(): array
    {
        return [
            self::DRAFT => 'gray',
            self::SCHEDULED => 'yellow',
            self::PUBLISHING => 'blue',
            self::PUBLISHED => 'green',
            self::FAILED => 'red',
            self::CANCELLED => 'gray',
        ];
    }

    /**
     * Check if status is valid.
     */
    public static function isValid(string $status): bool
    {
        return in_array($status, self::all(), true);
    }

    /**
     * Get status display name.
     */
    public static function getName(string $status): string
    {
        return self::names()[$status] ?? $status;
    }

    /**
     * Get status color.
     */
    public static function getColor(string $status): string
    {
        return self::colors()[$status] ?? 'gray';
    }

    /**
     * Check if status is final (published or failed).
     */
    public static function isFinal(string $status): bool
    {
        return in_array($status, [self::PUBLISHED, self::FAILED, self::CANCELLED], true);
    }

    /**
     * Check if status is pending (draft or scheduled).
     */
    public static function isPending(string $status): bool
    {
        return in_array($status, [self::DRAFT, self::SCHEDULED], true);
    }
}
