<?php

declare(strict_types=1);

namespace App\Domains\Settings\Services;

interface SiteSettingsServiceContract
{
    /**
     * Get all site settings
     */
    public function getAllSettings(): array;

    /**
     * Get a specific setting
     */
    public function getSetting(string $key, mixed $default = null): mixed;

    /**
     * Update multiple settings
     */
    public function updateSettings(array $settings): bool;

    /**
     * Get available languages
     */
    public function getAvailableLanguages(): array;

    /**
     * Get available timezones
     */
    public function getAvailableTimezones(): array;

    /**
     * Get available user roles
     */
    public function getAvailableUserRoles(): array;

    /**
     * Get available pages for landing page selection
     */
    public function getAvailablePages(): array;
}