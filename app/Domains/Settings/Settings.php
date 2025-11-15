<?php

namespace App\Domains\Settings;

use Illuminate\Support\Facades\Facade;

/**
 * @method static mixed get(string $key, mixed $default = null)
 * @method static \App\Domains\Settings\Models\Setting set(string $key, mixed $value, string $type = 'string', string $group = 'general', ?string $description = null)
 * @method static array<string, mixed> all()
 * @method static array<string, mixed> group(string $group)
 * @method static void clearCache()
 *
 * @see \App\Domains\Settings\SiteSettings\Service\SiteSettingsContract
 */
class Settings extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'settings';
    }
}
