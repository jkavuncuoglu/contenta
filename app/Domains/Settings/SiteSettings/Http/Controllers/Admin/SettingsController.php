<?php

namespace App\Domains\Settings\SiteSettings\Http\Controllers\Admin;

use App\Domains\Settings\Http\Controllers\Admin\SiteSettings;
use App\Domains\Settings\SiteSettings\Http\Requests\Admin\UpdateSettingsRequest;
use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class SettingsController extends Controller
{
    /**
     * Display the settings page
     *
     * @return Response
     */
    public function index(): Response
    {
        $settings = [
            'blog' => [
                'blog_slug' => SiteSettings::get('blog_slug', 'blog'),
                'primary_landing_page' => SiteSettings::get('primary_landing_page', 'blog'),
            ],
            'analytics' => [
                'google_analytics_id' => SiteSettings::get('google_analytics_id'),
                'google_tag_manager_id' => SiteSettings::get('google_tag_manager_id'),
            ],
            'security' => [
                'recaptcha_enabled' => SiteSettings::get('recaptcha_enabled', false),
                'recaptcha_site_key' => SiteSettings::get('recaptcha_site_key'),
                'honeypot_enabled' => SiteSettings::get('honeypot_enabled', false),
                'honeypot_field_name' => SiteSettings::get('honeypot_field_name', 'honeypot'),
                'honeypot_timer_field_name' => SiteSettings::get('honeypot_timer_field_name', 'honeypot_timer'),
                'honeypot_minimum_time' => SiteSettings::get('honeypot_minimum_time', 3),
            ]
        ];

        return Inertia::render('admin/Settings', [
            'settings' => $settings
        ]);
    }

    /**
     * Update settings
     *
     * @param UpdateSettingsRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(UpdateSettingsRequest $request)
    {
        $settings = $request->validated();

        // Update blog settings
        if (isset($settings['blog'])) {
            SiteSettings::set('blog_slug', $settings['blog']['blog_slug'], 'string', 'blog', 'The URL slug for the blog section');
            SiteSettings::set('primary_landing_page', $settings['blog']['primary_landing_page'], 'string', 'blog', 'The primary landing page for the site');
        }

        // Update analytics settings
        if (isset($settings['analytics'])) {
            SiteSettings::set('google_analytics_id', $settings['analytics']['google_analytics_id'], 'string', 'analytics', 'Google Analytics tracking ID');
            SiteSettings::set('google_tag_manager_id', $settings['analytics']['google_tag_manager_id'], 'string', 'analytics', 'Google Tag Manager container ID');
        }

        // Update security settings
        if (isset($settings['security'])) {
            $security = $settings['security'];

            // reCAPTCHA settings
            SiteSettings::set('recaptcha_enabled', $security['recaptcha_enabled'] ?? false, 'boolean', 'security', 'Enable/disable reCAPTCHA');
            if (isset($security['recaptcha_site_key'])) {
                SiteSettings::set('recaptcha_site_key', $security['recaptcha_site_key'], 'string', 'security', 'Google reCAPTCHA site key');
            }

            // Honeypot settings
            SiteSettings::set('honeypot_enabled', $security['honeypot_enabled'] ?? false, 'boolean', 'security', 'Enable/disable honeypot spam protection');
            SiteSettings::set('honeypot_field_name', $security['honeypot_field_name'] ?? 'honeypot', 'string', 'security', 'Honeypot field name for forms');
            SiteSettings::set('honeypot_timer_field_name', $security['honeypot_timer_field_name'] ?? 'honeypot_timer', 'string', 'security', 'Honeypot timer field name for forms');
            SiteSettings::set('honeypot_minimum_time', $security['honeypot_minimum_time'] ?? 3, 'integer', 'security', 'Minimum time in seconds for form submission to be valid');
        }

        // Clear all settings cache
        SiteSettings::clearCache();

        return redirect()->back()->with('success', 'Settings updated successfully');
    }

    /**
     * Get available page options for primary landing page
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPageOptions()
    {
        // This would typically come from your pages module/database
        $pages = [
            ['value' => 'blog', 'label' => 'Blog'],
            // Add other pages from your database here
        ];

        return response()->json($pages);
    }

    /**
     * Display the site settings page
     *
     * @return Response
     */
    public function site(): Response
    {
        // You can customize the data returned here
        return Inertia::render('admin/SettingsSite', [
            'settings' => []
        ]);
    }

    /**
     * Display the security settings page
     *
     * @return Response
     */
    public function security(): Response
    {
        // You can customize the data returned here
        return Inertia::render('admin/SettingsSecurity', [
            'settings' => []
        ]);
    }

    /**
     * Display the users settings page
     *
     * @return Response
     */
    public function users(): Response
    {
        // You can customize the data returned here
        return Inertia::render('admin/SettingsUsers', [
            'settings' => []
        ]);
    }
}
