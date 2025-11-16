<?php

namespace App\Domains\Settings\SiteSettings\Service;

use App\Domains\Settings\Models\Setting;

interface SiteSettingsContract
{
    /**
     * Get a setting value by key
     */
    public function get(string $key, mixed $default = null): mixed;

    /**
     * Set a setting value
     */
    public function set(string $key, mixed $value, string $type = 'string', string $group = 'general', ?string $description = null): Setting;

    /**
     * Get all settings
     *
     * @return array<string, mixed>
     */
    public function all(): array;

    /**
     * Get settings by group
     *
     * @return array<string, mixed>
     */
    public function getByGroup(string $group): array;

    /**
     * Clear settings cache
     */
    public function clearCache(): void;
}
