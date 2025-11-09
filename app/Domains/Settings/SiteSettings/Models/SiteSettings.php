<?php

namespace App\Domains\Settings\SiteSettings\Models;

use App\Domains\Settings\Models\Setting;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * @property string $key
 * @property mixed $value
 * @property string $type
 * @property string $group
 * @property string|null $description
 */
class SiteSettings extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'key', 'value', 'type', 'group', 'description'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Cache duration in minutes
     */
    protected static int $cacheDuration = 60;

    /**
     * Get a setting by key
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        return Cache::remember("setting.{$key}", self::$cacheDuration, function () use ($key, $default) {
            $setting = self::where('key', $key)->first();

            if (!$setting) {
                return $default;
            }

            return self::castValue($setting->value, $setting->type);
        });
    }

    /**
     * Set a setting value
     */
    public static function set(string $key, mixed $value, string $type = 'string', string $group = 'general', ?string $description = null): SiteSettings
    {
        $setting = self::updateOrCreate(
            ['key' => $key],
            [
                'value' => is_array($value) ? json_encode($value) : $value,
                'type' => $type,
                'group' => $group,
                'description' => $description,
            ]
        );

        // Clear the cache for this key
        Cache::forget("setting.{$key}");

        return $setting;
    }

    /**
     * Get all settings as a key-value array
     *
     * @return array<string, mixed>
     */
    public static function allSettings(): array
    {
        return Cache::remember('settings.all', self::$cacheDuration, function () {
            $settings = [];
            foreach (self::all() as $setting) {
                $settings[$setting->key] = self::castValue($setting->value, $setting->type);
            }
            return $settings;
        });
    }

    /**
     * Get settings by group
     *
     * @return array<string, mixed>
     */
    public static function getByGroup(string $group): array
    {
        return Cache::remember("settings.group.{$group}", self::$cacheDuration, function () use ($group) {
            $settings = [];
            foreach (self::where('group', $group)->get() as $setting) {
                $settings[$setting->key] = self::castValue($setting->value, $setting->type);
            }
            return $settings;
        });
    }

    /**
     * Cast value to the specified type
     */
    protected static function castValue(mixed $value, string $type): mixed
    {
        if (is_null($value)) {
            return null;
        }

        switch ($type) {
            case 'boolean':
                return filter_var($value, FILTER_VALIDATE_BOOLEAN);
            case 'integer':
                return (int) $value;
            case 'float':
                return (float) $value;
            case 'array':
            case 'json':
                return json_decode($value, true) ?: [];
            default:
                return $value;
        }
    }

    /**
     * Clear all settings cache
     */
    public static function clearCache(): void
    {
        Cache::forget('settings.all');

        // Clear all group caches
        $groups = self::select('group')->distinct()->pluck('group');
        foreach ($groups as $group) {
            Cache::forget("settings.group.{$group}");
        }

        // Clear individual setting caches
        $keys = self::pluck('key');
        foreach ($keys as $key) {
            Cache::forget("setting.{$key}");
        }
    }
}
