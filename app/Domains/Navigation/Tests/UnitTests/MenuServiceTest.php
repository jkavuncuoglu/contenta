<?php

declare(strict_types=1);

namespace App\Domains\Navigation\Tests\UnitTests;

use App\Domains\Navigation\Models\Menu;
use App\Domains\Navigation\Models\MenuItem;
use App\Domains\Navigation\Services\MenuService;
use App\Domains\Navigation\Services\MenuServiceContract;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MenuServiceTest extends TestCase
{
    use RefreshDatabase;

    protected MenuService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(MenuServiceContract::class);
    }

    
    public function test_it_can_create_menu(): void
    {
        // Arrange
        $data = [
            'name' => 'Test Menu',
            'description' => 'A test menu',
            'location' => 'primary',
        ];

        // Act
        $menu = $this->service->createMenu($data);

        // Assert
        $this->assertInstanceOf(Menu::class, $menu);
        $this->assertEquals('Test Menu', $menu->name);
        $this->assertEquals('test-menu', $menu->slug);
    }

    
    public function test_it_can_update_menu(): void
    {
        // Arrange
        $menu = Menu::create(['name' => 'Original', 'slug' => 'original']);

        // Act
        $updated = $this->service->updateMenu($menu, ['name' => 'Updated']);

        // Assert
        $this->assertEquals('Updated', $updated->name);
        $this->assertEquals('updated', $updated->slug);
    }

    
    public function test_it_can_delete_menu(): void
    {
        // Arrange
        $menu = Menu::create(['name' => 'To Delete', 'slug' => 'to-delete']);

        // Act
        $result = $this->service->deleteMenu($menu);

        // Assert
        $this->assertTrue($result);
        $this->assertDatabaseMissing('menus', ['id' => $menu->id]);
    }

    
    public function test_it_can_create_menu_item(): void
    {
        // Arrange
        $menu = Menu::create(['name' => 'Test Menu', 'slug' => 'test-menu']);
        $data = [
            'title' => 'Home',
            'url' => '/',
            'target' => '_self',
        ];

        // Act
        $item = $this->service->createMenuItem($menu, $data);

        // Assert
        $this->assertInstanceOf(MenuItem::class, $item);
        $this->assertEquals('Home', $item->title);
        $this->assertEquals($menu->id, $item->menu_id);
    }


    public function test_it_can_get_available_locations(): void
    {
        // Act
        $locations = $this->service->getAvailableLocations();

        // Assert
        $this->assertArrayHasKey('primary', $locations);
        $this->assertArrayHasKey('footer', $locations);
        $this->assertCount(4, $locations);
    }

    
    public function test_it_can_get_menu_by_location(): void
    {
        // Arrange
        Menu::create([
            'name' => 'Primary Menu',
            'slug' => 'primary',
            'location' => 'primary',
            'is_active' => true,
        ]);

        // Act
        $menu = $this->service->getMenuByLocation('primary');

        // Assert
        $this->assertInstanceOf(Menu::class, $menu);
        $this->assertEquals('primary', $menu->location);
    }


    public function test_it_can_export_menu(): void
    {
        // Arrange
        $menu = Menu::create([
            'name' => 'Export Menu',
            'slug' => 'export',
            'description' => 'Test export',
            'location' => 'primary',
        ]);

        // Act
        $exported = $this->service->exportMenu($menu);

        // Assert
        $this->assertEquals('Export Menu', $exported['name']);
        $this->assertArrayHasKey('items', $exported);
        $this->assertArrayHasKey('settings', $exported);
    }
}
