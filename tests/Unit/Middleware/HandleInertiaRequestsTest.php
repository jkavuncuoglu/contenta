<?php

namespace Tests\Unit\Middleware;

use App\Domains\Navigation\Models\Menu;
use App\Domains\Navigation\Models\MenuItem;
use App\Domains\Security\UserManagement\Models\User;
use App\Domains\Settings\Models\Setting;
use App\Domains\Settings\Models\ThemeSettings;
use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class HandleInertiaRequestsTest extends TestCase
{
    use RefreshDatabase;

    private HandleInertiaRequests $middleware;

    protected function setUp(): void
    {
        parent::setUp();
        $this->middleware = new HandleInertiaRequests;
    }

    public function test_version_returns_parent_version(): void
    {
        // Arrange
        $request = Request::create('/test');

        // Act
        $version = $this->middleware->version($request);

        // Assert - parent returns null by default
        $this->assertNull($version);
    }

    public function test_share_returns_array_with_required_keys(): void
    {
        // Arrange
        $request = Request::create('/test');

        // Act
        $shared = $this->middleware->share($request);

        // Assert
        $this->assertIsArray($shared);
        $this->assertArrayHasKey('name', $shared);
        $this->assertArrayHasKey('quote', $shared);
        $this->assertArrayHasKey('auth', $shared);
        $this->assertArrayHasKey('sidebarOpen', $shared);
        $this->assertArrayHasKey('analytics', $shared);
        $this->assertArrayHasKey('themeColors', $shared);
        $this->assertArrayHasKey('navigation', $shared);
    }

    public function test_share_includes_app_name(): void
    {
        // Arrange
        $request = Request::create('/test');
        config(['app.name' => 'Test App']);

        // Act
        $shared = $this->middleware->share($request);

        // Assert
        $this->assertEquals('Test App', $shared['name']);
    }

    public function test_share_includes_inspiring_quote(): void
    {
        // Arrange
        $request = Request::create('/test');

        // Act
        $shared = $this->middleware->share($request);

        // Assert
        $this->assertIsArray($shared['quote']);
        $this->assertArrayHasKey('message', $shared['quote']);
        $this->assertArrayHasKey('author', $shared['quote']);
        $this->assertNotEmpty($shared['quote']['message']);
        $this->assertNotEmpty($shared['quote']['author']);
    }

    public function test_share_includes_authenticated_user(): void
    {
        // Arrange
        $user = User::factory()->create();
        $request = Request::create('/test');
        $request->setUserResolver(fn () => $user);

        // Act
        $shared = $this->middleware->share($request);

        // Assert
        $this->assertIsArray($shared['auth']);
        $this->assertArrayHasKey('user', $shared['auth']);
        $this->assertInstanceOf(User::class, $shared['auth']['user']);
        $this->assertEquals($user->id, $shared['auth']['user']->id);
    }

    public function test_share_includes_null_user_when_not_authenticated(): void
    {
        // Arrange
        $request = Request::create('/test');

        // Act
        $shared = $this->middleware->share($request);

        // Assert
        $this->assertNull($shared['auth']['user']);
    }

    public function test_share_sidebar_open_defaults_to_true_when_no_cookie(): void
    {
        // Arrange
        $request = Request::create('/test');

        // Act
        $shared = $this->middleware->share($request);

        // Assert
        $this->assertTrue($shared['sidebarOpen']);
    }

    public function test_share_sidebar_open_true_when_cookie_is_true(): void
    {
        // Arrange
        $request = Request::create('/test');
        $request->cookies->set('sidebar_state', 'true');

        // Act
        $shared = $this->middleware->share($request);

        // Assert
        $this->assertTrue($shared['sidebarOpen']);
    }

    public function test_share_sidebar_open_false_when_cookie_is_false(): void
    {
        // Arrange
        $request = Request::create('/test');
        $request->cookies->set('sidebar_state', 'false');

        // Act
        $shared = $this->middleware->share($request);

        // Assert
        $this->assertFalse($shared['sidebarOpen']);
    }

    public function test_get_analytics_settings_returns_correct_structure(): void
    {
        // Arrange
        $request = Request::create('/test');
        Setting::create([
            'group' => 'site',
            'key' => 'site_analytics_enabled',
            'value' => true,
            'type' => 'boolean',
        ]);
        Setting::create([
            'group' => 'site',
            'key' => 'site_google_analytics_id',
            'value' => 'GA-123456',
            'type' => 'string',
        ]);
        Setting::create([
            'group' => 'site',
            'key' => 'site_google_tag_manager_id',
            'value' => 'GTM-123456',
            'type' => 'string',
        ]);
        Setting::create([
            'group' => 'site',
            'key' => 'site_facebook_pixel_id',
            'value' => 'FB-123456',
            'type' => 'string',
        ]);
        Setting::create([
            'group' => 'site',
            'key' => 'site_cookie_consent_enabled',
            'value' => true,
            'type' => 'boolean',
        ]);

        // Act
        $shared = $this->middleware->share($request);

        // Assert
        $this->assertIsArray($shared['analytics']);
        $this->assertArrayHasKey('enabled', $shared['analytics']);
        $this->assertArrayHasKey('googleAnalyticsId', $shared['analytics']);
        $this->assertArrayHasKey('googleTagManagerId', $shared['analytics']);
        $this->assertArrayHasKey('facebookPixelId', $shared['analytics']);
        $this->assertArrayHasKey('cookieConsentEnabled', $shared['analytics']);
        $this->assertTrue($shared['analytics']['enabled']);
        $this->assertEquals('GA-123456', $shared['analytics']['googleAnalyticsId']);
        $this->assertEquals('GTM-123456', $shared['analytics']['googleTagManagerId']);
        $this->assertEquals('FB-123456', $shared['analytics']['facebookPixelId']);
        $this->assertTrue($shared['analytics']['cookieConsentEnabled']);
    }

    public function test_get_analytics_settings_returns_defaults_on_exception(): void
    {
        // Arrange - No settings in database will cause an exception in getMultiple
        $request = Request::create('/test');

        // Act
        $shared = $this->middleware->share($request);

        // Assert
        $this->assertIsArray($shared['analytics']);
        $this->assertFalse($shared['analytics']['enabled']);
        $this->assertEquals('', $shared['analytics']['googleAnalyticsId']);
        $this->assertEquals('', $shared['analytics']['googleTagManagerId']);
        $this->assertEquals('', $shared['analytics']['facebookPixelId']);
        $this->assertTrue($shared['analytics']['cookieConsentEnabled']);
    }

    public function test_get_theme_colors_returns_colors_when_theme_exists(): void
    {
        // Arrange
        $request = Request::create('/test');
        $theme = ThemeSettings::create([
            'name' => 'Default',
            'is_active' => true,
            'colors' => [
                'primary' => '#3490dc',
                'secondary' => '#ffed4e',
            ],
        ]);

        // Act
        $shared = $this->middleware->share($request);

        // Assert
        $this->assertIsArray($shared['themeColors']);
        $this->assertNotEmpty($shared['themeColors']);
    }

    public function test_get_theme_colors_returns_empty_array_when_no_theme(): void
    {
        // Arrange
        $request = Request::create('/test');

        // Act
        $shared = $this->middleware->share($request);

        // Assert
        $this->assertIsArray($shared['themeColors']);
        $this->assertEmpty($shared['themeColors']);
    }

    public function test_get_navigation_returns_default_navigation_when_no_menus(): void
    {
        // Arrange
        $request = Request::create('/test');

        // Act
        $shared = $this->middleware->share($request);

        // Assert
        $this->assertIsArray($shared['navigation']);
        $this->assertArrayHasKey('primary', $shared['navigation']);
        $this->assertArrayHasKey('footer', $shared['navigation']);

        // Check default primary nav
        $this->assertIsArray($shared['navigation']['primary']);
        $this->assertCount(4, $shared['navigation']['primary']);
        $this->assertEquals('Home', $shared['navigation']['primary'][0]['title']);
        $this->assertEquals('/', $shared['navigation']['primary'][0]['url']);

        // Check default footer nav
        $this->assertIsArray($shared['navigation']['footer']);
        $this->assertCount(3, $shared['navigation']['footer']);
        $this->assertEquals('Company', $shared['navigation']['footer'][0]['title']);
        $this->assertIsArray($shared['navigation']['footer'][0]['links']);
    }

    public function test_get_navigation_returns_custom_navigation_when_menus_exist(): void
    {
        // Arrange
        $request = Request::create('/test');

        $primaryMenu = Menu::create([
            'name' => 'Primary Menu',
            'slug' => 'primary',
            'location' => 'primary',
            'is_active' => true,
        ]);

        MenuItem::create([
            'menu_id' => $primaryMenu->id,
            'title' => 'Custom Home',
            'url' => '/custom-home',
            'order' => 0,
        ]);

        // Act
        $shared = $this->middleware->share($request);

        // Assert
        $this->assertIsArray($shared['navigation']['primary']);
        $this->assertCount(1, $shared['navigation']['primary']);
        $this->assertEquals('Custom Home', $shared['navigation']['primary'][0]['title']);
    }

    public function test_format_footer_nav_creates_sections_from_menu(): void
    {
        // Arrange
        $request = Request::create('/test');

        $footerMenu = Menu::create([
            'name' => 'Footer Menu',
            'slug' => 'footer',
            'location' => 'footer',
            'is_active' => true,
        ]);

        $section = MenuItem::create([
            'menu_id' => $footerMenu->id,
            'title' => 'Products',
            'url' => '#',
            'order' => 0,
        ]);

        MenuItem::create([
            'menu_id' => $footerMenu->id,
            'parent_id' => $section->id,
            'title' => 'Product 1',
            'url' => '/product-1',
            'target' => '_self',
            'order' => 0,
        ]);

        MenuItem::create([
            'menu_id' => $footerMenu->id,
            'parent_id' => $section->id,
            'title' => 'Product 2',
            'url' => '/product-2',
            'target' => '_blank',
            'order' => 1,
        ]);

        // Act
        $shared = $this->middleware->share($request);

        // Assert
        $this->assertIsArray($shared['navigation']['footer']);
        $this->assertCount(1, $shared['navigation']['footer']);
        $this->assertEquals('Products', $shared['navigation']['footer'][0]['title']);
        $this->assertIsArray($shared['navigation']['footer'][0]['links']);
        $this->assertCount(2, $shared['navigation']['footer'][0]['links']);
        $this->assertEquals('Product 1', $shared['navigation']['footer'][0]['links'][0]['title']);
        $this->assertEquals('/product-1', $shared['navigation']['footer'][0]['links'][0]['url']);
        $this->assertEquals('_self', $shared['navigation']['footer'][0]['links'][0]['target']);
        $this->assertEquals('Product 2', $shared['navigation']['footer'][0]['links'][1]['title']);
        $this->assertEquals('_blank', $shared['navigation']['footer'][0]['links'][1]['target']);
    }

    public function test_get_default_primary_nav_returns_four_items(): void
    {
        // Arrange
        $request = Request::create('/test');

        // Act
        $shared = $this->middleware->share($request);

        // Assert (when no custom menu exists)
        $this->assertCount(4, $shared['navigation']['primary']);
        $this->assertEquals('Home', $shared['navigation']['primary'][0]['title']);
        $this->assertEquals('Blog', $shared['navigation']['primary'][1]['title']);
        $this->assertEquals('About', $shared['navigation']['primary'][2]['title']);
        $this->assertEquals('Contact', $shared['navigation']['primary'][3]['title']);
    }

    public function test_get_default_footer_nav_returns_three_sections(): void
    {
        // Arrange
        $request = Request::create('/test');

        // Act
        $shared = $this->middleware->share($request);

        // Assert (when no custom menu exists)
        $this->assertCount(3, $shared['navigation']['footer']);
        $this->assertEquals('Company', $shared['navigation']['footer'][0]['title']);
        $this->assertEquals('Resources', $shared['navigation']['footer'][1]['title']);
        $this->assertEquals('Legal', $shared['navigation']['footer'][2]['title']);

        // Check Company section links
        $this->assertCount(3, $shared['navigation']['footer'][0]['links']);
        $this->assertEquals('About Us', $shared['navigation']['footer'][0]['links'][0]['title']);

        // Check Resources section links
        $this->assertCount(3, $shared['navigation']['footer'][1]['links']);
        $this->assertEquals('Documentation', $shared['navigation']['footer'][1]['links'][0]['title']);

        // Check Legal section links
        $this->assertCount(3, $shared['navigation']['footer'][2]['links']);
        $this->assertEquals('Privacy Policy', $shared['navigation']['footer'][2]['links'][0]['title']);
    }

    public function test_navigation_only_returns_active_menus(): void
    {
        // Arrange
        $request = Request::create('/test');

        Menu::create([
            'name' => 'Inactive Primary',
            'slug' => 'inactive-primary',
            'location' => 'primary',
            'is_active' => false,
        ]);

        // Act
        $shared = $this->middleware->share($request);

        // Assert - Should return default nav since active menu doesn't exist
        $this->assertCount(4, $shared['navigation']['primary']);
    }

    public function test_format_footer_nav_uses_resolved_url_when_available(): void
    {
        // Arrange
        $request = Request::create('/test');

        $footerMenu = Menu::create([
            'name' => 'Footer',
            'slug' => 'footer',
            'location' => 'footer',
            'is_active' => true,
        ]);

        $section = MenuItem::create([
            'menu_id' => $footerMenu->id,
            'title' => 'Pages',
            'url' => '#',
            'order' => 0,
        ]);

        MenuItem::create([
            'menu_id' => $footerMenu->id,
            'parent_id' => $section->id,
            'title' => 'Page Link',
            'url' => null,
            'object_type' => 'page',
            'object_id' => '123',
            'order' => 0,
        ]);

        // Act
        $shared = $this->middleware->share($request);

        // Assert - Should use getResolvedUrl() which returns /page/123
        $this->assertEquals('/page/123', $shared['navigation']['footer'][0]['links'][0]['url']);
    }

    public function test_format_footer_nav_falls_back_to_url_when_no_resolved_url(): void
    {
        // Arrange
        $request = Request::create('/test');

        $footerMenu = Menu::create([
            'name' => 'Footer',
            'slug' => 'footer',
            'location' => 'footer',
            'is_active' => true,
        ]);

        $section = MenuItem::create([
            'menu_id' => $footerMenu->id,
            'title' => 'Links',
            'url' => '#',
            'order' => 0,
        ]);

        MenuItem::create([
            'menu_id' => $footerMenu->id,
            'parent_id' => $section->id,
            'title' => 'External Link',
            'url' => 'https://example.com',
            'order' => 0,
        ]);

        // Act
        $shared = $this->middleware->share($request);

        // Assert - Should use url field directly
        $this->assertEquals('https://example.com', $shared['navigation']['footer'][0]['links'][0]['url']);
    }
}
