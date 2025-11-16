<?php

declare(strict_types=1);

namespace App\Domains\Settings\Tests\UnitTests;

use App\Domains\Settings\Models\Setting;
use App\Domains\Settings\Services\SiteSettingsService;
use App\Domains\Settings\Services\SiteSettingsServiceContract;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SiteSettingsServiceTest extends TestCase
{
    use RefreshDatabase;

    protected SiteSettingsService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(SiteSettingsServiceContract::class);
    }

    public function test_get_all_settings_returns_site_group_settings(): void
    {
        // Arrange
        Setting::set('site', 'site_name', 'Test Site', 'string');
        Setting::set('site', 'site_description', 'Test Description', 'string');

        // Act
        $settings = $this->service->getAllSettings();

        // Assert
        $this->assertIsArray($settings);
        $this->assertArrayHasKey('site_name', $settings);
        $this->assertArrayHasKey('site_description', $settings);
    }

    public function test_get_setting_returns_specific_setting_value(): void
    {
        // Arrange
        Setting::set('site', 'site_name', 'Test Site', 'string');

        // Act
        $value = $this->service->getSetting('site_name');

        // Assert
        $this->assertEquals('Test Site', $value);
    }

    public function test_get_setting_returns_default_when_not_found(): void
    {
        // Act
        $value = $this->service->getSetting('nonexistent', 'default_value');

        // Assert
        $this->assertEquals('default_value', $value);
    }

    public function test_update_settings_creates_new_settings(): void
    {
        // Arrange
        $settings = [
            'name' => 'My Site',
            'description' => 'Site Description',
        ];

        // Act
        $result = $this->service->updateSettings($settings);

        // Assert
        $this->assertTrue($result);
        $this->assertDatabaseHas('settings', [
            'group' => 'site',
            'key' => 'site_name',
            'value' => 'My Site',
        ]);
    }

    public function test_update_settings_adds_site_prefix(): void
    {
        // Arrange
        $settings = ['name' => 'My Site'];

        // Act
        $this->service->updateSettings($settings);

        // Assert
        $this->assertDatabaseHas('settings', [
            'group' => 'site',
            'key' => 'site_name',
        ]);
    }

    public function test_update_settings_preserves_existing_site_prefix(): void
    {
        // Arrange
        $settings = ['site_name' => 'My Site'];

        // Act
        $this->service->updateSettings($settings);

        // Assert
        $this->assertDatabaseHas('settings', [
            'group' => 'site',
            'key' => 'site_name',
        ]);
    }

    public function test_update_settings_handles_multiple_settings(): void
    {
        // Arrange
        $settings = [
            'name' => 'My Site',
            'description' => 'Description',
            'email' => 'test@example.com',
        ];

        // Act
        $result = $this->service->updateSettings($settings);

        // Assert
        $this->assertTrue($result);
        $this->assertDatabaseHas('settings', ['key' => 'site_name']);
        $this->assertDatabaseHas('settings', ['key' => 'site_description']);
        $this->assertDatabaseHas('settings', ['key' => 'site_email']);
    }

    public function test_update_settings_with_boolean_value(): void
    {
        // Arrange
        $settings = ['enabled' => true];

        // Act
        $result = $this->service->updateSettings($settings);

        // Assert
        $this->assertTrue($result);
        $setting = Setting::where('key', 'site_enabled')->first();
        $this->assertEquals('boolean', $setting->type);
    }

    public function test_update_settings_with_integer_value(): void
    {
        // Arrange
        $settings = ['count' => 123];

        // Act
        $result = $this->service->updateSettings($settings);

        // Assert
        $this->assertTrue($result);
        $setting = Setting::where('key', 'site_count')->first();
        $this->assertEquals('integer', $setting->type);
    }

    public function test_update_settings_with_string_value(): void
    {
        // Arrange
        $settings = ['title' => 'Site Title'];

        // Act
        $result = $this->service->updateSettings($settings);

        // Assert
        $this->assertTrue($result);
        $setting = Setting::where('key', 'site_title')->first();
        $this->assertEquals('string', $setting->type);
    }

    public function test_get_available_languages_returns_language_list(): void
    {
        // Act
        $languages = $this->service->getAvailableLanguages();

        // Assert
        $this->assertIsArray($languages);
        $this->assertArrayHasKey('en_US', $languages);
        $this->assertArrayHasKey('fr_FR', $languages);
        $this->assertEquals('English (United States)', $languages['en_US']);
    }

    public function test_get_available_timezones_returns_timezone_list(): void
    {
        // Act
        $timezones = $this->service->getAvailableTimezones();

        // Assert
        $this->assertIsArray($timezones);
        $this->assertNotEmpty($timezones);
        $this->assertArrayHasKey('UTC', $timezones);
        $this->assertArrayHasKey('America/New_York', $timezones);
    }

    public function test_get_available_timezones_includes_offset(): void
    {
        // Act
        $timezones = $this->service->getAvailableTimezones();

        // Assert
        foreach ($timezones as $timezone => $display) {
            $this->assertStringContainsString('UTC', $display);
            $this->assertStringContainsString($timezone, $display);
        }
    }

    public function test_get_available_user_roles_returns_roles_from_database(): void
    {
        // Arrange
        Role::create(['name' => 'admin']);
        Role::create(['name' => 'editor']);

        // Act
        $roles = $this->service->getAvailableUserRoles();

        // Assert
        $this->assertIsArray($roles);
        $this->assertArrayHasKey('admin', $roles);
        $this->assertArrayHasKey('editor', $roles);
    }

    public function test_get_available_user_roles_returns_array(): void
    {
        // Act
        $roles = $this->service->getAvailableUserRoles();

        // Assert
        $this->assertIsArray($roles);
    }

    public function test_get_available_pages_returns_blog_option(): void
    {
        // Act
        $pages = $this->service->getAvailablePages();

        // Assert
        $this->assertIsArray($pages);
        $this->assertArrayHasKey('blog', $pages);
        $this->assertEquals('Blog (Latest Posts)', $pages['blog']);
    }
}
