<?php

namespace App\Http\Middleware;

use App\Domains\Navigation\Models\Menu;
use App\Domains\Settings\Models\Setting;
use App\Domains\Settings\Models\ThemeSettings;
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
            'themeColors' => $this->getThemeColors(),
            'navigation' => $this->getNavigation(),
        ];
    }

    /**
     * Get analytics settings for frontend
     *
     * @return array<string, mixed>
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
                    'site_cookie_consent_enabled',
                ],
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

    /**
     * Get theme colors for frontend
     *
     * @return array<string, mixed>
     */
    private function getThemeColors(): array
    {
        try {
            $theme = ThemeSettings::active();

            return $theme ? $theme->getAllColors() : [];
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get navigation menu for frontend
     *
     * @return array<string, mixed>
     */
    private function getNavigation(): array
    {
        try {
            $primaryMenu = Menu::where('location', 'primary')
                ->where('is_active', true)
                ->first();

            $footerMenu = Menu::where('location', 'footer')
                ->where('is_active', true)
                ->first();

            return [
                'primary' => $primaryMenu ? $primaryMenu->getStructure() : $this->getDefaultPrimaryNav(),
                'footer' => $footerMenu ? $this->formatFooterNav($footerMenu) : $this->getDefaultFooterNav(),
            ];
        } catch (\Exception $e) {
            return [
                'primary' => $this->getDefaultPrimaryNav(),
                'footer' => $this->getDefaultFooterNav(),
            ];
        }
    }

    /**
     * Format footer navigation into sections
     *
     * @return array<int, array<string, mixed>>
     */
    private function formatFooterNav(Menu $menu): array
    {
        $sections = [];
        $rootItems = $menu->rootItems()->with('children')->get();

        foreach ($rootItems as $root) {
            $sections[] = [
                'title' => $root->title,
                'links' => $root->children->map(fn ($child) => [
                    'id' => $child->id,
                    'title' => $child->title,
                    'url' => $child->getResolvedUrl() ?: $child->url,
                    'target' => $child->target,
                ])->toArray(),
            ];
        }

        return $sections;
    }

    /**
     * Get default primary navigation
     *
     * @return array<int, array<string, mixed>>
     */
    private function getDefaultPrimaryNav(): array
    {
        return [
            ['id' => 1, 'title' => 'Home', 'url' => '/', 'target' => '_self', 'children' => []],
            ['id' => 2, 'title' => 'Blog', 'url' => '/blog', 'target' => '_self', 'children' => []],
            ['id' => 3, 'title' => 'About', 'url' => '/about', 'target' => '_self', 'children' => []],
            ['id' => 4, 'title' => 'Contact', 'url' => '/contact', 'target' => '_self', 'children' => []],
        ];
    }

    /**
     * Get default footer navigation
     *
     * @return array<int, array<string, mixed>>
     */
    private function getDefaultFooterNav(): array
    {
        return [
            [
                'title' => 'Company',
                'links' => [
                    ['id' => 1, 'title' => 'About Us', 'url' => '/about', 'target' => '_self'],
                    ['id' => 2, 'title' => 'Blog', 'url' => '/blog', 'target' => '_self'],
                    ['id' => 3, 'title' => 'Contact', 'url' => '/contact', 'target' => '_self'],
                ],
            ],
            [
                'title' => 'Resources',
                'links' => [
                    ['id' => 4, 'title' => 'Documentation', 'url' => '/docs', 'target' => '_self'],
                    ['id' => 5, 'title' => 'Help Center', 'url' => '/help', 'target' => '_self'],
                    ['id' => 6, 'title' => 'Support', 'url' => '/support', 'target' => '_self'],
                ],
            ],
            [
                'title' => 'Legal',
                'links' => [
                    ['id' => 7, 'title' => 'Privacy Policy', 'url' => '/privacy', 'target' => '_self'],
                    ['id' => 8, 'title' => 'Terms of Service', 'url' => '/terms', 'target' => '_self'],
                    ['id' => 9, 'title' => 'Cookie Policy', 'url' => '/cookies', 'target' => '_self'],
                ],
            ],
        ];
    }
}
