<?php

namespace App\Http\Middleware;

use App\Domains\Settings\Models\Setting;
use Illuminate\Foundation\Inspiring;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        [$message, $author] = str(Inspiring::quotes()->random())->explode('-');

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'quote' => ['message' => trim($message), 'author' => trim($author)],
            'auth' => [
                'user' => $request->user(),
            ],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
            'analytics' => $this->getAnalyticsSettings(),
        ];
    }

    /**
     * Get analytics settings for frontend
     */
    private function getAnalyticsSettings(): array
    {
        try {
            $settings = Setting::getMultiple([
                'site' => [
                    'site_analytics_enabled',
                    'site_google_analytics_id',
                    'site_google_tag_manager_id',
                    'site_facebook_pixel_id',
                    'site_cookie_consent_enabled'
                ]
            ]);

            return [
                'enabled' => $settings['site']['site_analytics_enabled'] ?? false,
                'googleAnalyticsId' => $settings['site']['site_google_analytics_id'] ?? '',
                'googleTagManagerId' => $settings['site']['site_google_tag_manager_id'] ?? '',
                'facebookPixelId' => $settings['site']['site_facebook_pixel_id'] ?? '',
                'cookieConsentEnabled' => $settings['site']['site_cookie_consent_enabled'] ?? true,
            ];
        } catch (\Exception $e) {
            return [
                'enabled' => false,
                'googleAnalyticsId' => '',
                'googleTagManagerId' => '',
                'facebookPixelId' => '',
                'cookieConsentEnabled' => true,
            ];
        }
    }
}
