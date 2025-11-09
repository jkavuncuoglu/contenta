<?php

declare(strict_types=1);

namespace App\Domains\Settings\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Cache;

/**
 * @property string $key
 * @property mixed $value
 * @property string $type
 * @property string|null $description
 * @property bool $autoload
 */
class SiteSetting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'type',
        'description',
        'autoload',
    ];

    protected $casts = [
        'autoload' => 'boolean',
    ];

    /**
     * Get the typed value of the setting
     *
     * @return Attribute<mixed, string>
     */
    protected function value(): Attribute
    {
        return Attribute::make(
            get: function (string $value) {
                return match ($this->type) {
                    'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
                    'integer' => (int) $value,
                    'json' => json_decode($value, true),
                    default => $value,
                };
            },
            set: function (mixed $value) {
                return match ($this->type) {
                    'boolean' => $value ? '1' : '0',
                    'integer' => (string) $value,
                    'json' => json_encode($value),
                    default => (string) $value,
                };
            }
        );
    }

    /**
     * Boot the model
     */
    protected static function boot(): void
    {
        parent::boot();

        // Clear cache when settings are modified
        static::saved(function (SiteSetting $setting): void {
            Cache::forget('site_settings');
            Cache::forget("site_setting_{$setting->key}");
        });

        static::deleted(function (SiteSetting $setting): void {
            Cache::forget('site_settings');
            Cache::forget("site_setting_{$setting->key}");
        });
    }

    /**
     * Get a setting value by key
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        return Cache::remember("site_setting_{$key}", 3600, function () use ($key, $default) {
            $setting = static::where('key', $key)->first();
            return $setting->value ?? $default;
        });
    }

    /**
     * Set a setting value
     */
    public static function set(string $key, mixed $value, string $type = 'string'): void
    {
        static::updateOrCreate(
            ['key' => $key],
            [
                'value' => $value,
                'type' => $type,
                'autoload' => true,
            ]
        );
    }

    /**
     * Get all autoload settings
     *
     * @return array<string, mixed>
     */
    public static function getAutoloadSettings(): array
    {
        return Cache::remember('site_settings', 3600, function () {
            return static::where('autoload', true)
                ->get()
                ->mapWithKeys(function (SiteSetting $setting) {
                    return [$setting->key => $setting->value];
                })
                ->toArray();
        });
    }
}