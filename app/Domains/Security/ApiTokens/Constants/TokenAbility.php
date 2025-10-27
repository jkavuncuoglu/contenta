<?php

namespace App\Domains\Security\ApiTokens\Constants;

class TokenAbility
{
    public const READ = 'read';
    public const WRITE = 'write';
    public const DELETE = 'delete';

    public static function all(): array
    {
        return [
            self::READ => 'Read access to resources',
            self::WRITE => 'Create and update resources',
            self::DELETE => 'Delete resources',
        ];
    }

    public static function values(): array
    {
        return array_keys(self::all());
    }

    public static function labels(): array
    {
        return array_values(self::all());
    }
}

