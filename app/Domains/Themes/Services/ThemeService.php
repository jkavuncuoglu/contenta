<?php

declare(strict_types=1);

namespace App\Domains\Themes\Services;

use App\Domains\Themes\Models\Theme;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use ZipArchive;

class ThemeService implements ThemeServiceContract
{
    /**
     * Get all themes
     *
     * @return Collection<int, Theme>
     */
    public function getAllThemes(): Collection
    {
        return Theme::orderBy('display_name')->get();
    }

    /**
     * Get active theme
     */
    public function getActiveTheme(): ?Theme
    {
        return Theme::getActive();
    }

    /**
     * Activate a theme
     */
    public function activateTheme(int $themeId): Theme
    {
        $theme = Theme::findOrFail($themeId);
        $theme->activate();

        return $theme->fresh();
    }

    /**
     * Install a theme from uploaded zip file
     */
    public function installTheme(UploadedFile $file): Theme
    {
        $zip = new ZipArchive;
        $tempPath = storage_path('app/temp/'.Str::random(40));

        if ($zip->open($file->getPathname()) === true) {
            $zip->extractTo($tempPath);
            $zip->close();
        } else {
            throw new \Exception('Failed to extract theme zip file');
        }

        // Find theme.json in extracted files
        $themeJsonPath = $this->findThemeJson($tempPath);

        if (! $themeJsonPath) {
            File::deleteDirectory($tempPath);
            throw new \Exception('Invalid theme: theme.json not found');
        }

        $themeDir = dirname($themeJsonPath);
        $metadata = json_decode(file_get_contents($themeJsonPath), true);

        if (! $metadata || ! isset($metadata['name'])) {
            File::deleteDirectory($tempPath);
            throw new \Exception('Invalid theme.json: missing required fields');
        }

        // Move theme to themes directory
        $themesPath = storage_path('app/themes');
        if (! File::exists($themesPath)) {
            File::makeDirectory($themesPath, 0755, true);
        }

        $finalPath = $themesPath.'/'.$metadata['name'];

        if (File::exists($finalPath)) {
            File::deleteDirectory($tempPath);
            throw new \Exception('Theme already exists: '.$metadata['name']);
        }

        File::moveDirectory($themeDir, $finalPath);
        File::deleteDirectory($tempPath);

        // Create theme record
        $theme = Theme::create([
            'name' => $metadata['name'],
            'display_name' => $metadata['display_name'] ?? $metadata['name'],
            'description' => $metadata['description'] ?? null,
            'version' => $metadata['version'] ?? '1.0.0',
            'author' => $metadata['author'] ?? null,
            'screenshot' => $metadata['screenshot'] ?? 'screenshot.png',
            'path' => $metadata['name'],
            'metadata' => $metadata,
        ]);

        return $theme;
    }

    /**
     * Uninstall a theme
     */
    public function uninstallTheme(int $themeId): bool
    {
        $theme = Theme::findOrFail($themeId);

        if ($theme->is_active) {
            throw new \Exception('Cannot uninstall active theme');
        }

        $themePath = $theme->getFullPath();

        if (File::exists($themePath)) {
            File::deleteDirectory($themePath);
        }

        return (bool) $theme->delete();
    }

    /**
     * Scan and register themes from storage directory
     *
     * @return Collection<int, Theme>
     */
    public function scanThemes(): Collection
    {
        $themesPath = storage_path('app/themes');

        if (! File::exists($themesPath)) {
            File::makeDirectory($themesPath, 0755, true);

            return new Collection;
        }

        $directories = File::directories($themesPath);
        $discoveredThemes = new Collection;

        foreach ($directories as $directory) {
            $themeName = basename($directory);
            $themeJsonPath = $directory.'/theme.json';

            if (! File::exists($themeJsonPath)) {
                continue;
            }

            $metadata = json_decode(file_get_contents($themeJsonPath), true);

            if (! $metadata || ! isset($metadata['name'])) {
                continue;
            }

            // Check if theme already exists in database
            $existingTheme = Theme::where('name', $themeName)->first();

            if ($existingTheme) {
                // Update metadata if version changed
                if ($existingTheme->version !== ($metadata['version'] ?? '1.0.0')) {
                    $existingTheme->update([
                        'version' => $metadata['version'] ?? '1.0.0',
                        'metadata' => $metadata,
                    ]);
                }
                $discoveredThemes->push($existingTheme);

                continue;
            }

            // Create new theme record
            $theme = Theme::create([
                'name' => $themeName,
                'display_name' => $metadata['display_name'] ?? $themeName,
                'description' => $metadata['description'] ?? null,
                'version' => $metadata['version'] ?? '1.0.0',
                'author' => $metadata['author'] ?? null,
                'screenshot' => $metadata['screenshot'] ?? 'screenshot.png',
                'path' => $themeName,
                'metadata' => $metadata,
            ]);

            $discoveredThemes->push($theme);
        }

        return $discoveredThemes;
    }

    /**
     * Get theme by name
     */
    public function getThemeByName(string $name): ?Theme
    {
        return Theme::where('name', $name)->first();
    }

    /**
     * Validate theme structure
     */
    public function validateTheme(string $path): bool
    {
        // Check if theme.json exists
        if (! File::exists($path.'/theme.json')) {
            return false;
        }

        // Check if views directory exists
        if (! File::exists($path.'/views')) {
            return false;
        }

        // Validate theme.json content
        $metadata = json_decode(file_get_contents($path.'/theme.json'), true);

        if (! $metadata || ! isset($metadata['name'])) {
            return false;
        }

        return true;
    }

    /**
     * Get theme metadata from theme.json
     *
     * @return array<string, mixed>
     */
    public function getThemeMetadata(string $path): array
    {
        $themeJsonPath = $path.'/theme.json';

        if (! File::exists($themeJsonPath)) {
            return [];
        }

        $metadata = json_decode(file_get_contents($themeJsonPath), true);

        return $metadata ?? [];
    }

    /**
     * Find theme.json in extracted directory
     */
    private function findThemeJson(string $directory): ?string
    {
        $files = File::allFiles($directory);

        foreach ($files as $file) {
            if ($file->getFilename() === 'theme.json') {
                return $file->getPathname();
            }
        }

        return null;
    }
}
