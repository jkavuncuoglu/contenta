<?php

declare(strict_types=1);

namespace App\Domains\Settings\Services;

use App\Domains\Settings\Models\ThemeSettings;

class ThemeSettingsService implements ThemeSettingsServiceContract
{
    /**
     * Get the active theme
     */
    public function getActiveTheme(): ?ThemeSettings
    {
        return ThemeSettings::active();
    }

    /**
     * Update theme settings
     */
    public function updateTheme(array $data): ThemeSettings
    {
        $theme = $this->getActiveTheme();

        if (!$theme) {
            $theme = $this->ensureDefaultTheme();
        }

        $theme->update($data);

        return $theme->fresh();
    }

    /**
     * Get theme colors for frontend
     */
    public function getThemeColors(): array
    {
        $theme = $this->getActiveTheme();

        if (!$theme) {
            $theme = $this->ensureDefaultTheme();
        }

        return $theme->getAllColors();
    }

    /**
     * Create default theme if none exists
     */
    public function ensureDefaultTheme(): ThemeSettings
    {
        return ThemeSettings::firstOrCreate(
            ['name' => 'default'],
            ['is_active' => true]
        );
    }
}
