<?php

declare(strict_types=1);

namespace App\Domains\Themes\Services;

use App\Domains\Themes\Models\Theme;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;

interface ThemeServiceContract
{
    /**
     * Get all themes
     *
     * @return Collection<int, Theme>
     */
    public function getAllThemes(): Collection;

    /**
     * Get active theme
     */
    public function getActiveTheme(): ?Theme;

    /**
     * Activate a theme
     */
    public function activateTheme(int $themeId): Theme;

    /**
     * Install a theme from uploaded zip file
     */
    public function installTheme(UploadedFile $file): Theme;

    /**
     * Uninstall a theme
     */
    public function uninstallTheme(int $themeId): bool;

    /**
     * Scan and register themes from storage directory
     *
     * @return Collection<int, Theme>
     */
    public function scanThemes(): Collection;

    /**
     * Get theme by name
     */
    public function getThemeByName(string $name): ?Theme;

    /**
     * Validate theme structure
     */
    public function validateTheme(string $path): bool;

    /**
     * Get theme metadata from theme.json
     *
     * @return array<string, mixed>
     */
    public function getThemeMetadata(string $path): array;
}
