<?php

declare(strict_types=1);

namespace App\Domains\Settings\Tests\UnitTests;

use App\Domains\Settings\Models\ThemeSettings;
use App\Domains\Settings\Services\ThemeSettingsService;
use App\Domains\Settings\Services\ThemeSettingsServiceContract;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ThemeSettingsServiceTest extends TestCase
{
    use RefreshDatabase;

    protected ThemeSettingsService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(ThemeSettingsServiceContract::class);
    }

    public function test_get_active_theme_returns_active_theme(): void
    {
        // Arrange
        ThemeSettings::create(['name' => 'default', 'is_active' => true]);

        // Act
        $theme = $this->service->getActiveTheme();

        // Assert
        $this->assertInstanceOf(ThemeSettings::class, $theme);
        $this->assertEquals('default', $theme->name);
        $this->assertTrue($theme->is_active);
    }

    public function test_get_active_theme_returns_null_when_no_active_theme(): void
    {
        // Act
        $theme = $this->service->getActiveTheme();

        // Assert
        $this->assertNull($theme);
    }

    public function test_update_theme_updates_existing_theme(): void
    {
        // Arrange
        ThemeSettings::create([
            'name' => 'default',
            'is_active' => true,
            'light_primary' => '#000000',
        ]);

        // Act
        $theme = $this->service->updateTheme([
            'light_primary' => '#FF0000',
        ]);

        // Assert
        $this->assertEquals('#FF0000', $theme->light_primary);
    }

    public function test_update_theme_creates_default_theme_when_none_exists(): void
    {
        // Act
        $theme = $this->service->updateTheme([
            'light_primary' => '#FF0000',
        ]);

        // Assert
        $this->assertInstanceOf(ThemeSettings::class, $theme);
        $this->assertEquals('default', $theme->name);
        $this->assertEquals('#FF0000', $theme->light_primary);
        $this->assertTrue($theme->is_active);
    }

    public function test_get_theme_colors_returns_all_colors(): void
    {
        // Arrange
        ThemeSettings::create([
            'name' => 'default',
            'is_active' => true,
            'light_primary' => '#FFFFFF',
            'dark_primary' => '#000000',
        ]);

        // Act
        $colors = $this->service->getThemeColors();

        // Assert
        $this->assertIsArray($colors);
        $this->assertArrayHasKey('light', $colors);
        $this->assertArrayHasKey('dark', $colors);
        $this->assertEquals('#FFFFFF', $colors['light']['primary']);
        $this->assertEquals('#000000', $colors['dark']['primary']);
    }

    public function test_get_theme_colors_creates_default_theme_when_none_exists(): void
    {
        // Act
        $colors = $this->service->getThemeColors();

        // Assert
        $this->assertIsArray($colors);
        $this->assertDatabaseHas('theme_settings', [
            'name' => 'default',
            'is_active' => true,
        ]);
    }

    public function test_ensure_default_theme_creates_theme(): void
    {
        // Act
        $theme = $this->service->ensureDefaultTheme();

        // Assert
        $this->assertInstanceOf(ThemeSettings::class, $theme);
        $this->assertEquals('default', $theme->name);
        $this->assertTrue($theme->is_active);
    }

    public function test_ensure_default_theme_returns_existing_theme(): void
    {
        // Arrange
        $existing = ThemeSettings::create(['name' => 'default', 'is_active' => true]);

        // Act
        $theme = $this->service->ensureDefaultTheme();

        // Assert
        $this->assertEquals($existing->id, $theme->id);
    }
}
