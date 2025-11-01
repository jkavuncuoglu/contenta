<?php

declare(strict_types=1);

namespace App\Domains\Settings\Http\Controllers\Admin;

use App\Domains\Settings\Models\Setting;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SecuritySettingsController extends Controller
{
    /**
     * Display the security settings
     */
    public function index(): Response
    {
        $settings = Setting::getGroup('security');

        return Inertia::render('admin/settings/security/Index', [
            'settings' => $settings,
            'options' => [
                'recaptchaVersions' => [
                    'v2' => 'reCAPTCHA v2 (Checkbox)',
                    'v3' => 'reCAPTCHA v3 (Invisible)',
                ],
                'honeypotFieldNames' => [
                    'hp_field' => 'hp_field (default)',
                    'website' => 'website',
                    'url' => 'url',
                    'homepage' => 'homepage',
                    'company_website' => 'company_website',
                ],
                'minimumTimes' => [
                    1 => '1 second',
                    2 => '2 seconds',
                    3 => '3 seconds (recommended)',
                    5 => '5 seconds',
                    10 => '10 seconds',
                ],
            ],
        ]);
    }

    /**
     * Update the security settings
     */
    public function update(Request $request)
    {
        $request->validate([
            // General security
            'security_enabled' => 'boolean',
            'security_rate_limiting' => 'boolean',
            'security_csrf_protection' => 'boolean',
            'security_ip_blocking' => 'boolean',

            // Honeypot settings
            'honeypot_enabled' => 'boolean',
            'honeypot_field_name' => 'required|string|max:50|alpha_dash',
            'honeypot_minimum_time' => 'required|integer|min:1|max:60',
            'honeypot_timer_enabled' => 'boolean',
            'honeypot_input_field' => 'required|string|max:50|alpha_dash',

            // reCAPTCHA settings
            'recaptcha_enabled' => 'boolean',
            'recaptcha_site_key' => 'nullable|string|max:100',
            'recaptcha_secret_key' => 'nullable|string|max:100',
            'recaptcha_version' => 'required|string|in:v2,v3',
            'recaptcha_threshold' => 'required|numeric|between:0,1',
        ]);

        $updated = $this->updateSecuritySettings($request->only([
            'security_enabled',
            'security_rate_limiting',
            'security_csrf_protection',
            'security_ip_blocking',
            'honeypot_enabled',
            'honeypot_field_name',
            'honeypot_minimum_time',
            'honeypot_timer_enabled',
            'honeypot_input_field',
            'recaptcha_enabled',
            'recaptcha_site_key',
            'recaptcha_secret_key',
            'recaptcha_version',
            'recaptcha_threshold',
        ]));

        if (!$updated) {
            return redirect()->route('admin.settings.security.index')
                ->with('error', 'Failed to update security settings');
        }

        return redirect()->route('admin.settings.security.index')
            ->with('success', 'Security settings updated successfully');
    }

    /**
     * Update security settings in the database
     */
    private function updateSecuritySettings(array $settings): bool
    {
        try {
            foreach ($settings as $key => $value) {
                Setting::set(
                    'security',
                    $key,
                    $value,
                    $this->inferType($value),
                    ucfirst(str_replace('_', ' ', $key))
                );
            }

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Infer the type of a value
     */
    private function inferType(mixed $value): string
    {
        return match (true) {
            is_bool($value) => 'boolean',
            is_int($value) => 'integer',
            is_float($value) => 'float',
            is_array($value) => 'json',
            default => 'string',
        };
    }
}