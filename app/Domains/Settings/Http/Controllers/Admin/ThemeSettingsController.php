<?php

declare(strict_types=1);

namespace App\Domains\Settings\Http\Controllers\Admin;

use App\Domains\Settings\Services\ThemeSettingsServiceContract;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ThemeSettingsController
{
    public function __construct(
        private ThemeSettingsServiceContract $themeService
    ) {}

    /**
     * Show theme settings page
     */
    public function index(): Response
    {
        return Inertia::render('Admin/Settings/ThemeSettings', [
            'theme' => $this->themeService->getActiveTheme(),
        ]);
    }

    /**
     * Get current theme colors
     */
    public function show(): JsonResponse
    {
        return response()->json([
            'colors' => $this->themeService->getThemeColors(),
        ]);
    }

    /**
     * Update theme settings
     */
    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'light_primary' => 'nullable|string|max:7',
            'light_secondary' => 'nullable|string|max:7',
            'light_accent' => 'nullable|string|max:7',
            'light_background' => 'nullable|string|max:7',
            'light_surface' => 'nullable|string|max:7',
            'light_text' => 'nullable|string|max:7',
            'light_text_secondary' => 'nullable|string|max:7',
            'dark_primary' => 'nullable|string|max:7',
            'dark_secondary' => 'nullable|string|max:7',
            'dark_accent' => 'nullable|string|max:7',
            'dark_background' => 'nullable|string|max:7',
            'dark_surface' => 'nullable|string|max:7',
            'dark_text' => 'nullable|string|max:7',
            'dark_text_secondary' => 'nullable|string|max:7',
        ]);

        $theme = $this->themeService->updateTheme($validated);

        return response()->json([
            'message' => 'Theme updated successfully',
            'theme' => $theme,
        ]);
    }
}
