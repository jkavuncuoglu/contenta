<?php

namespace App\Domains\Settings;

use Illuminate\Support\Facades\Facade;

/**
 * @method static mixed get(string $key, $default = null)
 * @method static \App\Domains\Settings\Models\Setting set(string $key, $value, string $type = 'string', string $group = 'general', ?string $description = null)
 * @method static array all()
 * @method static array group(string $group)
 * @method static void clearCache()
 *
 * @see \App\Domains\Settings\Contracts\SettingsContract
 */
class Settings extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'settings';
    }
}
