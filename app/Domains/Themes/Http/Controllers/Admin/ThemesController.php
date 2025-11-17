<?php

declare(strict_types=1);

namespace App\Domains\Themes\Http\Controllers\Admin;

use App\Domains\Themes\Services\ThemeServiceContract;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ThemesController extends Controller
{
    public function __construct(
        private ThemeServiceContract $themeService
    ) {}

    /**
     * Display a listing of themes
     */
    public function index(): Response
    {
        $themes = $this->themeService->getAllThemes();

        return Inertia::render('admin/appearance/themes/Index', [
            'themes' => $themes->map(function ($theme) {
                return [
                    'id' => $theme->id,
                    'name' => $theme->name,
                    'display_name' => $theme->display_name,
                    'description' => $theme->description,
                    'version' => $theme->version,
                    'author' => $theme->author,
                    'screenshot' => $theme->getScreenshotUrl(),
                    'is_active' => $theme->is_active,
                    'metadata' => $theme->metadata,
                ];
            }),
        ]);
    }

    /**
     * Activate a theme
     */
    public function activate(int $id): RedirectResponse
    {
        try {
            $this->themeService->activateTheme($id);

            return redirect()
                ->route('admin.themes.index')
                ->with('success', 'Theme activated successfully!');
        } catch (\Exception $e) {
            return redirect()
                ->route('admin.themes.index')
                ->with('error', 'Failed to activate theme: '.$e->getMessage());
        }
    }

    /**
     * Install a theme from uploaded ZIP
     */
    public function install(Request $request): RedirectResponse
    {
        $request->validate([
            'theme' => 'required|file|mimes:zip|max:10240', // 10MB max
        ]);

        try {
            $theme = $this->themeService->installTheme($request->file('theme'));

            return redirect()
                ->route('admin.themes.index')
                ->with('success', "Theme '{$theme->display_name}' installed successfully!");
        } catch (\Exception $e) {
            return redirect()
                ->route('admin.themes.index')
                ->with('error', 'Failed to install theme: '.$e->getMessage());
        }
    }

    /**
     * Uninstall a theme
     */
    public function uninstall(int $id): RedirectResponse
    {
        try {
            $this->themeService->uninstallTheme($id);

            return redirect()
                ->route('admin.themes.index')
                ->with('success', 'Theme uninstalled successfully!');
        } catch (\Exception $e) {
            return redirect()
                ->route('admin.themes.index')
                ->with('error', 'Failed to uninstall theme: '.$e->getMessage());
        }
    }

    /**
     * Scan for new themes
     */
    public function scan(): RedirectResponse
    {
        try {
            $themes = $this->themeService->scanThemes();

            return redirect()
                ->route('admin.themes.index')
                ->with('success', 'Found '.count($themes).' theme(s).');
        } catch (\Exception $e) {
            return redirect()
                ->route('admin.themes.index')
                ->with('error', 'Failed to scan themes: '.$e->getMessage());
        }
    }
}
