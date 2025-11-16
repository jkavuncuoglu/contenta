<?php

declare(strict_types=1);

namespace App\Domains\PageBuilder\Tests\UnitTests;

use App\Domains\PageBuilder\Models\Layout;
use App\Domains\PageBuilder\Models\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LayoutModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_fillable_attributes(): void
    {
        // Arrange & Act
        $layout = Layout::create([
            'name' => 'Test Layout',
            'slug' => 'test-layout',
            'structure' => ['areas' => ['header', 'content', 'footer']],
            'description' => 'Test description',
            'is_active' => true,
        ]);

        // Assert
        $this->assertEquals('Test Layout', $layout->name);
        $this->assertEquals('test-layout', $layout->slug);
        $this->assertEquals(['areas' => ['header', 'content', 'footer']], $layout->structure);
        $this->assertEquals('Test description', $layout->description);
        $this->assertTrue($layout->is_active);
    }

    public function test_structure_casts_to_array(): void
    {
        // Arrange & Act
        $layout = Layout::create([
            'name' => 'Test',
            'slug' => 'test',
            'structure' => ['areas' => ['main'], 'settings' => ['foo' => 'bar']],
        ]);

        // Assert
        $this->assertIsArray($layout->structure);
        $this->assertEquals(['areas' => ['main'], 'settings' => ['foo' => 'bar']], $layout->structure);
    }

    public function test_is_active_casts_to_boolean(): void
    {
        // Arrange & Act
        $layout = Layout::create([
            'name' => 'Test',
            'slug' => 'test',
            'is_active' => 1,
        ]);

        // Assert
        $this->assertIsBool($layout->is_active);
        $this->assertTrue($layout->is_active);
    }

    public function test_default_is_active_is_true(): void
    {
        // Arrange & Act
        $layout = Layout::create([
            'name' => 'Test',
            'slug' => 'test',
        ]);

        // Assert
        $this->assertTrue($layout->is_active);
    }

    public function test_default_structure_has_default_areas(): void
    {
        // Arrange & Act
        $layout = Layout::create([
            'name' => 'Test',
            'slug' => 'test',
        ]);

        // Assert
        $this->assertIsArray($layout->structure);
        $this->assertArrayHasKey('areas', $layout->structure);
        $this->assertEquals(['header', 'main', 'footer'], $layout->structure['areas']);
    }

    public function test_pages_relationship_returns_associated_pages(): void
    {
        // Arrange
        $layout = Layout::create([
            'name' => 'Test Layout',
            'slug' => 'test-layout',
        ]);

        Page::create([
            'layout_id' => $layout->id,
            'title' => 'Page 1',
            'slug' => 'page-1',
            'content' => ['blocks' => []],
        ]);

        Page::create([
            'layout_id' => $layout->id,
            'title' => 'Page 2',
            'slug' => 'page-2',
            'content' => ['blocks' => []],
        ]);

        // Act
        $pages = $layout->pages;

        // Assert
        $this->assertCount(2, $pages);
        $this->assertInstanceOf(Page::class, $pages->first());
    }

    public function test_scope_active_filters_active_layouts(): void
    {
        // Arrange
        Layout::create([
            'name' => 'Active Layout',
            'slug' => 'active',
            'is_active' => true,
        ]);

        Layout::create([
            'name' => 'Inactive Layout',
            'slug' => 'inactive',
            'is_active' => false,
        ]);

        // Act
        $activeLayouts = Layout::active()->get();

        // Assert
        $this->assertCount(1, $activeLayouts);
        $this->assertEquals('Active Layout', $activeLayouts->first()->name);
    }

    public function test_scope_active_returns_multiple_active_layouts(): void
    {
        // Arrange
        Layout::create([
            'name' => 'Active 1',
            'slug' => 'active-1',
            'is_active' => true,
        ]);

        Layout::create([
            'name' => 'Active 2',
            'slug' => 'active-2',
            'is_active' => true,
        ]);

        Layout::create([
            'name' => 'Inactive',
            'slug' => 'inactive',
            'is_active' => false,
        ]);

        // Act
        $activeLayouts = Layout::active()->get();

        // Assert
        $this->assertCount(2, $activeLayouts);
    }

    public function test_areas_accessor_returns_areas_from_structure(): void
    {
        // Arrange
        $layout = Layout::create([
            'name' => 'Test',
            'slug' => 'test',
            'structure' => ['areas' => ['header', 'sidebar', 'content', 'footer']],
        ]);

        // Act
        $areas = $layout->areas;

        // Assert
        $this->assertIsArray($areas);
        $this->assertEquals(['header', 'sidebar', 'content', 'footer'], $areas);
    }

    public function test_areas_accessor_returns_main_as_default_when_no_areas_defined(): void
    {
        // Arrange
        $layout = Layout::create([
            'name' => 'Test',
            'slug' => 'test',
            'structure' => ['settings' => ['foo' => 'bar']],
        ]);

        // Act
        $areas = $layout->areas;

        // Assert
        $this->assertEquals(['main'], $areas);
    }

    public function test_has_area_returns_true_when_area_exists(): void
    {
        // Arrange
        $layout = Layout::create([
            'name' => 'Test',
            'slug' => 'test',
            'structure' => ['areas' => ['header', 'main', 'footer']],
        ]);

        // Act & Assert
        $this->assertTrue($layout->hasArea('header'));
        $this->assertTrue($layout->hasArea('main'));
        $this->assertTrue($layout->hasArea('footer'));
    }

    public function test_has_area_returns_false_when_area_does_not_exist(): void
    {
        // Arrange
        $layout = Layout::create([
            'name' => 'Test',
            'slug' => 'test',
            'structure' => ['areas' => ['header', 'main']],
        ]);

        // Act & Assert
        $this->assertFalse($layout->hasArea('sidebar'));
        $this->assertFalse($layout->hasArea('footer'));
    }

    public function test_config_accessor_returns_configuration_array(): void
    {
        // Arrange
        $layout = Layout::create([
            'name' => 'Test',
            'slug' => 'test',
            'structure' => [
                'areas' => ['header', 'main', 'footer'],
                'settings' => ['theme' => 'dark', 'width' => 'full'],
                'css_classes' => ['container', 'layout-default'],
            ],
        ]);

        // Act
        $config = $layout->config;

        // Assert
        $this->assertIsArray($config);
        $this->assertArrayHasKey('areas', $config);
        $this->assertArrayHasKey('settings', $config);
        $this->assertArrayHasKey('css_classes', $config);
        $this->assertEquals(['header', 'main', 'footer'], $config['areas']);
        $this->assertEquals(['theme' => 'dark', 'width' => 'full'], $config['settings']);
        $this->assertEquals(['container', 'layout-default'], $config['css_classes']);
    }

    public function test_config_accessor_returns_empty_arrays_for_missing_structure_keys(): void
    {
        // Arrange
        $layout = Layout::create([
            'name' => 'Test',
            'slug' => 'test',
            'structure' => ['areas' => ['main']],
        ]);

        // Act
        $config = $layout->config;

        // Assert
        $this->assertIsArray($config);
        $this->assertEquals(['main'], $config['areas']);
        $this->assertEquals([], $config['settings']);
        $this->assertEquals([], $config['css_classes']);
    }

    public function test_config_accessor_uses_main_as_default_area_when_not_defined(): void
    {
        // Arrange
        $layout = Layout::create([
            'name' => 'Test',
            'slug' => 'test',
            'structure' => ['settings' => ['foo' => 'bar']],
        ]);

        // Act
        $config = $layout->config;

        // Assert
        $this->assertEquals(['main'], $config['areas']);
    }
}
