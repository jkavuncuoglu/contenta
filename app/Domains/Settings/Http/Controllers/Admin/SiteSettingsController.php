<?php

declare(strict_types=1);

namespace App\Domains\Settings\Http\Controllers\Admin;

use App\Domains\Settings\Services\SiteSettingsServiceContract;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SiteSettingsController extends Controller
{
    public function __construct(
        private readonly SiteSettingsServiceContract $siteSettingsService
    ) {}

    /**
     * Display the site settings
     */
    public function index(): Response
    {
        $settings = $this->siteSettingsService->getAllSettings();
        $languages = $this->siteSettingsService->getAvailableLanguages();
        $timezones = $this->siteSettingsService->getAvailableTimezones();
        $userRoles = $this->siteSettingsService->getAvailableUserRoles();
        $pages = $this->siteSettingsService->getAvailablePages();

        return Inertia::render('admin/settings/site/Index', [
            'settings' => $settings,
            'options' => [
                'languages' => $languages,
                'timezones' => $timezones,
                'userRoles' => $userRoles,
                'pages' => $pages,
            ],
        ]);
    }

    /**
     * Update the site settings
     */
    public function update(Request $request): \Illuminate\Http\RedirectResponse
    {
        $request->validate([
            'site_title' => 'required|string|max:255',
            'site_tagline' => 'nullable|string|max:255',
            'site_url' => 'required|url|max:255',
            'admin_email' => 'required|email|max:255',
            'users_can_register' => 'boolean',
            'default_user_role' => 'required|string|max:50',
            'site_language' => 'required|string|max:10',
            'timezone' => 'required|string|max:50',
            'landing_page' => 'nullable|string|max:50',
            'google_analytics_id' => 'nullable|string|max:100',
            'google_tag_manager_id' => 'nullable|string|max:100',
            'facebook_pixel_id' => 'nullable|string|max:100',
            'analytics_enabled' => 'boolean',
            'cookie_consent_enabled' => 'boolean',
        ]);

        $updated = $this->siteSettingsService->updateSettings($request->only([
            'site_title',
            'site_tagline',
            'site_url',
            'admin_email',
            'users_can_register',
            'default_user_role',
            'site_language',
            'timezone',
            'landing_page',
            'google_analytics_id',
            'google_tag_manager_id',
            'facebook_pixel_id',
            'analytics_enabled',
            'cookie_consent_enabled',
        ]));

        if (! $updated) {
            return redirect()->route('admin.settings.site.index')
                ->with('error', 'Failed to update site settings');
        }

        return redirect()->route('admin.settings.site.index')
            ->with('success', 'Site settings updated successfully');
    }
}
