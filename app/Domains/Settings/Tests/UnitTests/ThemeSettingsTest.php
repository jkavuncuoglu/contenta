<?php

declare(strict_types=1);

namespace App\Domains\Settings\Tests\UnitTests;

use App\Domains\Settings\Models\ThemeSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ThemeSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_theme_settings_has_fillable_attributes(): void
    {
        // Act
        $theme = ThemeSettings::create([
            'name' => 'custom',
            'is_active' => true,
            'light_primary' => '#FF0000',
            'dark_primary' => '#000000',
        ]);

        // Assert
        $this->assertEquals('custom', $theme->name);
        $this->assertTrue($theme->is_active);
        $this->assertEquals('#FF0000', $theme->light_primary);
        $this->assertEquals('#000000', $theme->dark_primary);
    }

    public function test_is_active_casts_to_boolean(): void
    {
        // Arrange
        $theme = ThemeSettings::create([
            'name' => 'test',
            'is_active' => 1,
        ]);

        // Act
        $isActive = $theme->is_active;

        // Assert
        $this->assertIsBool($isActive);
        $this->assertTrue($isActive);
    }

    public function test_active_returns_active_theme(): void
    {
        // Arrange
        ThemeSettings::create(['name' => 'inactive', 'is_active' => false]);
        ThemeSettings::create(['name' => 'active', 'is_active' => true]);

        // Act
        $theme = ThemeSettings::active();

        // Assert
        $this->assertNotNull($theme);
        $this->assertEquals('active', $theme->name);
    }

    public function test_active_returns_null_when_no_active_theme(): void
    {
        // Arrange
        ThemeSettings::create(['name' => 'inactive', 'is_active' => false]);

        // Act
        $theme = ThemeSettings::active();

        // Assert
        $this->assertNull($theme);
    }

    public function test_get_light_colors_returns_light_theme_colors(): void
    {
        // Arrange
        $theme = ThemeSettings::create([
            'name' => 'test',
            'is_active' => true,
            'light_primary' => '#FFFFFF',
            'light_secondary' => '#EEEEEE',
            'light_accent' => '#DDDDDD',
            'light_background' => '#CCCCCC',
            'light_surface' => '#BBBBBB',
            'light_text' => '#AAAAAA',
            'light_text_secondary' => '#999999',
        ]);

        // Act
        $colors = $theme->getLightColors();

        // Assert
        $this->assertIsArray($colors);
        $this->assertEquals('#FFFFFF', $colors['primary']);
        $this->assertEquals('#EEEEEE', $colors['secondary']);
        $this->assertEquals('#DDDDDD', $colors['accent']);
        $this->assertEquals('#CCCCCC', $colors['background']);
        $this->assertEquals('#BBBBBB', $colors['surface']);
        $this->assertEquals('#AAAAAA', $colors['text']);
        $this->assertEquals('#999999', $colors['textSecondary']);
    }

    public function test_get_dark_colors_returns_dark_theme_colors(): void
    {
        // Arrange
        $theme = ThemeSettings::create([
            'name' => 'test',
            'is_active' => true,
            'dark_primary' => '#000000',
            'dark_secondary' => '#111111',
            'dark_accent' => '#222222',
            'dark_background' => '#333333',
            'dark_surface' => '#444444',
            'dark_text' => '#555555',
            'dark_text_secondary' => '#666666',
        ]);

        // Act
        $colors = $theme->getDarkColors();

        // Assert
        $this->assertIsArray($colors);
        $this->assertEquals('#000000', $colors['primary']);
        $this->assertEquals('#111111', $colors['secondary']);
        $this->assertEquals('#222222', $colors['accent']);
        $this->assertEquals('#333333', $colors['background']);
        $this->assertEquals('#444444', $colors['surface']);
        $this->assertEquals('#555555', $colors['text']);
        $this->assertEquals('#666666', $colors['textSecondary']);
    }

    public function test_get_all_colors_returns_both_light_and_dark_colors(): void
    {
        // Arrange
        $theme = ThemeSettings::create([
            'name' => 'test',
            'is_active' => true,
            'light_primary' => '#FFFFFF',
            'dark_primary' => '#000000',
        ]);

        // Act
        $colors = $theme->getAllColors();

        // Assert
        $this->assertIsArray($colors);
        $this->assertArrayHasKey('light', $colors);
        $this->assertArrayHasKey('dark', $colors);
        $this->assertEquals('#FFFFFF', $colors['light']['primary']);
        $this->assertEquals('#000000', $colors['dark']['primary']);
    }

    public function test_get_light_colors_handles_null_values(): void
    {
        // Arrange
        $theme = ThemeSettings::create([
            'name' => 'test',
            'is_active' => true,
        ]);

        // Act
        $colors = $theme->getLightColors();

        // Assert
        $this->assertNull($colors['primary']);
        $this->assertNull($colors['secondary']);
    }

    public function test_theme_can_be_updated(): void
    {
        // Arrange
        $theme = ThemeSettings::create([
            'name' => 'test',
            'is_active' => true,
            'light_primary' => '#FFFFFF',
        ]);

        // Act
        $theme->update(['light_primary' => '#000000']);

        // Assert
        $this->assertEquals('#000000', $theme->fresh()->light_primary);
    }
}
