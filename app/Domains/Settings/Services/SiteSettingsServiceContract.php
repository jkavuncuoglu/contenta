<?php

declare(strict_types=1);

namespace App\Domains\Settings\Services;

interface SiteSettingsServiceContract
{
    /**
     * Get all site settings
     *
     * @return array<string, array<string, mixed>>
     */
    public function getAllSettings(): array;

    /**
     * Get a specific setting
     */
    public function getSetting(string $key, mixed $default = null): mixed;

    /**
     * Update multiple settings
     *
     * @param  array<string, mixed>  $settings
     */
    public function updateSettings(array $settings): bool;

    /**
     * Get available languages
     *
     * @return array<string, string>
     */
    public function getAvailableLanguages(): array;

    /**
     * Get available timezones
     *
     * @return array<string, string>
     */
    public function getAvailableTimezones(): array;

    /**
     * Get available user roles
     *
     * @return array<string, string>
     */
    public function getAvailableUserRoles(): array;

    /**
     * Get available pages for landing page selection
     *
     * @return array<int|string, string>
     */
    public function getAvailablePages(): array;
}
