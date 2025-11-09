<?php

declare(strict_types=1);

namespace App\Domains\Settings\Services;

use App\Domains\Settings\Models\ThemeSettings;

interface ThemeSettingsServiceContract
{
    /**
     * Get the active theme
     */
    public function getActiveTheme(): ?ThemeSettings;

    /**
     * Update theme settings
     */
    public function updateTheme(array $data): ThemeSettings;

    /**
     * Get theme colors for frontend
     */
    public function getThemeColors(): array;

    /**
     * Create default theme if none exists
     */
    public function ensureDefaultTheme(): ThemeSettings;
}
