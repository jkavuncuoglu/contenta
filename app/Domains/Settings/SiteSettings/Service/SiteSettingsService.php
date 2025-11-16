<?php

namespace App\Domains\Settings\SiteSettings\Service;

use App\Domains\Settings\Models\Setting;
use Illuminate\Support\Facades\Cache;

class SiteSettingsService implements SiteSettingsContract
{
    /**
     * Cache duration in minutes
     */
    protected int $cacheDuration = 60;

    /**
     * Get a setting value by key
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return Cache::remember("setting.{$key}", $this->cacheDuration, function () use ($key, $default) {
            $setting = Setting::where('key', $key)->first();

            if (! $setting) {
                return $default;
            }

            return $this->castValue($setting->value, $setting->type);
        });
    }

    /**
     * Set a setting value
     */
    public function set(string $key, mixed $value, string $type = 'string', string $group = 'general', ?string $description = null): Setting
    {
        $setting = Setting::updateOrCreate(
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
     * Get all settings
     *
     * @return array<string, mixed>
     */
    public function all(): array
    {
        return Cache::remember('settings.all', $this->cacheDuration, function () {
            $settings = [];
            foreach (Setting::all() as $setting) {
                $settings[$setting->key] = $this->castValue($setting->value, $setting->type);
            }

            return $settings;
        });
    }

    /**
     * Get settings by group
     *
     * @return array<string, mixed>
     */
    public function getByGroup(string $group): array
    {
        return Cache::remember("settings.group.{$group}", $this->cacheDuration, function () use ($group) {
            $settings = [];
            foreach (Setting::where('group', $group)->get() as $setting) {
                $settings[$setting->key] = $this->castValue($setting->value, $setting->type);
            }

            return $settings;
        });
    }

    /**
     * Clear settings cache
     */
    public function clearCache(): void
    {
        Cache::forget('settings.all');

        // Clear all group caches
        $groups = Setting::select('group')->distinct()->pluck('group');
        foreach ($groups as $group) {
            Cache::forget("settings.group.{$group}");
        }

        // Clear individual setting caches
        $keys = Setting::pluck('key');
        foreach ($keys as $key) {
            Cache::forget("setting.{$key}");
        }
    }

    /**
     * Cast value to the specified type
     */
    protected function castValue(mixed $value, string $type): mixed
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
}
