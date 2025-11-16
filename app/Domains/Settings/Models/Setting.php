<?php

declare(strict_types=1);

namespace App\Domains\Settings\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * @property string $group
 * @property string $key
 * @property mixed $value
 * @property string $type
 * @property string|null $description
 * @property bool $autoload
 *
 * @use HasFactory<\Database\Factories\SettingFactory>
 */
class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'group',
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
     * Type casting for the value attribute
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
                    'float' => (float) $value,
                    'json' => json_decode($value, true),
                    'array' => json_decode($value, true),
                    default => $value,
                };
            },
            set: function (mixed $value) {
                return match ($this->type) {
                    'boolean' => $value ? '1' : '0',
                    'integer' => (string) (int) $value,
                    'float' => (string) (float) $value,
                    'json', 'array' => json_encode($value),
                    default => (string) $value,
                };
            }
        );
    }

    /**
     * Get a setting value with caching
     */
    public static function get(string $group, string $key, mixed $default = null): mixed
    {
        $cacheKey = "settings.{$group}.{$key}";

        return Cache::remember($cacheKey, 3600, function () use ($group, $key, $default) {
            $setting = static::where('group', $group)
                ->where('key', $key)
                ->first();

            return $setting ? $setting->value : $default;
        });
    }

    /**
     * Set a setting value
     */
    public static function set(string $group, string $key, mixed $value, string $type = 'string', ?string $description = null): void
    {
        $setting = static::updateOrCreate(
            ['group' => $group, 'key' => $key],
            [
                'value' => $value,
                'type' => $type,
                'description' => $description,
                'autoload' => true,
            ]
        );

        // Clear cache
        $cacheKey = "settings.{$group}.{$key}";
        Cache::forget($cacheKey);
        Cache::forget("settings.group.{$group}");
    }

    /**
     * Get all settings for a group
     *
     * @return array<string, array<string, mixed>>
     */
    public static function getGroup(string $group): array
    {
        $cacheKey = "settings.group.{$group}";

        return Cache::remember($cacheKey, 3600, function () use ($group) {
            return static::where('group', $group)
                ->get()
                ->mapWithKeys(function (Setting $setting) {
                    return [$setting->key => [
                        'value' => $setting->value,
                        'type' => $setting->type,
                        'description' => $setting->description,
                    ]];
                })
                ->toArray();
        });
    }

    /**
     * Get multiple settings efficiently
     *
     * @param  array<string, string|array<int, string>>  $settings
     * @return array<string, array<string, mixed>>
     */
    public static function getMultiple(array $settings): array
    {
        $result = [];
        $uncached = [];

        // Check cache first
        foreach ($settings as $group => $keys) {
            if (! is_array($keys)) {
                $keys = [$keys];
            }

            foreach ($keys as $key) {
                $cacheKey = "settings.{$group}.{$key}";
                $cached = Cache::get($cacheKey);

                if ($cached !== null) {
                    $result[$group][$key] = $cached;
                } else {
                    $uncached[] = ['group' => $group, 'key' => $key];
                }
            }
        }

        // Fetch uncached settings
        if (! empty($uncached)) {
            $groupKeys = [];
            foreach ($uncached as $item) {
                $groupKeys[$item['group']][] = $item['key'];
            }

            foreach ($groupKeys as $group => $keys) {
                $dbSettings = static::where('group', $group)
                    ->whereIn('key', $keys)
                    ->get();

                foreach ($dbSettings as $setting) {
                    $value = $setting->value;
                    $cacheKey = "settings.{$setting->group}.{$setting->key}";
                    Cache::put($cacheKey, $value, 3600);
                    $result[$setting->group][$setting->key] = $value;
                }
            }
        }

        return $result;
    }

    /**
     * Boot method to handle cache invalidation
     */
    protected static function booted(): void
    {
        static::saved(function ($setting) {
            $cacheKey = "settings.{$setting->group}.{$setting->key}";
            Cache::forget($cacheKey);
            Cache::forget("settings.group.{$setting->group}");
        });

        static::deleted(function ($setting) {
            $cacheKey = "settings.{$setting->group}.{$setting->key}";
            Cache::forget($cacheKey);
            Cache::forget("settings.group.{$setting->group}");
        });
    }
}
