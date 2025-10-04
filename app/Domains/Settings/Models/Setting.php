<?php

namespace App\Domains\Settings\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'key', 'value', 'type', 'group', 'description'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Cache duration in minutes
     */
    protected static $cacheDuration = 60;

    /**
     * Get a setting by key
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function get($key, $default = null)
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
     *
     * @param string $key
     * @param mixed $value
     * @param string $type
     * @param string $group
     * @param string|null $description
     * @return Setting
     */
    public static function set($key, $value, $type = 'string', $group = 'general', $description = null)
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
     * @return array
     */
    public static function allSettings()
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
     * @param string $group
     * @return array
     */
    public static function getByGroup($group)
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
     *
     * @param mixed $value
     * @param string $type
     * @return mixed
     */
    protected static function castValue($value, $type)
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
    public static function clearCache()
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
