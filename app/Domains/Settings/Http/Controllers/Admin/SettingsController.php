<?php

namespace App\Domains\Settings\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Domains\Settings\Http\Requests\Admin\UpdateSettingsRequest;
use App\Domains\Settings\Settings;
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
                'blog_slug' => Settings::get('blog_slug', 'blog'),
                'primary_landing_page' => Settings::get('primary_landing_page', 'blog'),
            ],
            'analytics' => [
                'google_analytics_id' => Settings::get('google_analytics_id'),
                'google_tag_manager_id' => Settings::get('google_tag_manager_id'),
            ],
            'security' => [
                'recaptcha_enabled' => Settings::get('recaptcha_enabled', false),
                'recaptcha_site_key' => Settings::get('recaptcha_site_key'),
                'honeypot_enabled' => Settings::get('honeypot_enabled', false),
                'honeypot_field_name' => Settings::get('honeypot_field_name', 'honeypot'),
                'honeypot_timer_field_name' => Settings::get('honeypot_timer_field_name', 'honeypot_timer'),
                'honeypot_minimum_time' => Settings::get('honeypot_minimum_time', 3),
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
            Settings::set('blog_slug', $settings['blog']['blog_slug'], 'string', 'blog', 'The URL slug for the blog section');
            Settings::set('primary_landing_page', $settings['blog']['primary_landing_page'], 'string', 'blog', 'The primary landing page for the site');
        }

        // Update analytics settings
        if (isset($settings['analytics'])) {
            Settings::set('google_analytics_id', $settings['analytics']['google_analytics_id'], 'string', 'analytics', 'Google Analytics tracking ID');
            Settings::set('google_tag_manager_id', $settings['analytics']['google_tag_manager_id'], 'string', 'analytics', 'Google Tag Manager container ID');
        }

        // Update security settings
        if (isset($settings['security'])) {
            $security = $settings['security'];

            // reCAPTCHA settings
            Settings::set('recaptcha_enabled', $security['recaptcha_enabled'] ?? false, 'boolean', 'security', 'Enable/disable reCAPTCHA');
            if (isset($security['recaptcha_site_key'])) {
                Settings::set('recaptcha_site_key', $security['recaptcha_site_key'], 'string', 'security', 'Google reCAPTCHA site key');
            }

            // Honeypot settings
            Settings::set('honeypot_enabled', $security['honeypot_enabled'] ?? false, 'boolean', 'security', 'Enable/disable honeypot spam protection');
            Settings::set('honeypot_field_name', $security['honeypot_field_name'] ?? 'honeypot', 'string', 'security', 'Honeypot field name for forms');
            Settings::set('honeypot_timer_field_name', $security['honeypot_timer_field_name'] ?? 'honeypot_timer', 'string', 'security', 'Honeypot timer field name for forms');
            Settings::set('honeypot_minimum_time', $security['honeypot_minimum_time'] ?? 3, 'integer', 'security', 'Minimum time in seconds for form submission to be valid');
        }

        // Clear all settings cache
        Settings::clearCache();

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
}
