<?php

namespace App\Domains\Settings\SiteSettings\Service;

interface SiteSettingsContract
{
    /**
     * Get a setting value by key
     */
    public function get(string $key, $default = null);

    /**
     * Set a setting value
     */
    public function set(string $key, $value, string $type = 'string', string $group = 'general', ?string $description = null);

    /**
     * Get all settings
     */
    public function all(): array;

    /**
     * Get settings by group
     */
    public function getByGroup(string $group): array;

    /**
     * Clear settings cache
     */
    public function clearCache(): void;
}
