<?php

declare(strict_types=1);

namespace App\Domains\Navigation\Tests\UnitTests;

use App\Domains\Navigation\Models\Menu;
use App\Domains\Navigation\Models\MenuItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MenuModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_items_relationship_returns_all_menu_items(): void
    {
        // Arrange
        $menu = Menu::create(['name' => 'Test Menu', 'slug' => 'test-menu']);
        MenuItem::create(['menu_id' => $menu->id, 'title' => 'Item 1', 'order' => 1]);
        MenuItem::create(['menu_id' => $menu->id, 'title' => 'Item 2', 'order' => 2]);
        MenuItem::create(['menu_id' => $menu->id, 'title' => 'Item 3', 'order' => 3]);

        // Act
        $items = $menu->items;

        // Assert
        $this->assertCount(3, $items);
    }

    public function test_items_relationship_orders_by_order_column(): void
    {
        // Arrange
        $menu = Menu::create(['name' => 'Test Menu', 'slug' => 'test-menu']);
        MenuItem::create(['menu_id' => $menu->id, 'order' => 3, 'title' => 'Third']);
        MenuItem::create(['menu_id' => $menu->id, 'order' => 1, 'title' => 'First']);
        MenuItem::create(['menu_id' => $menu->id, 'order' => 2, 'title' => 'Second']);

        // Act
        $items = $menu->items;

        // Assert
        $this->assertEquals('First', $items[0]->title);
        $this->assertEquals('Second', $items[1]->title);
        $this->assertEquals('Third', $items[2]->title);
    }

    public function test_root_items_returns_only_items_without_parent(): void
    {
        // Arrange
        $menu = Menu::create(['name' => 'Test Menu', 'slug' => 'test-menu']);
        $rootItem = MenuItem::create(['menu_id' => $menu->id, 'title' => 'Root', 'parent_id' => null]);
        MenuItem::create(['menu_id' => $menu->id, 'title' => 'Child', 'parent_id' => $rootItem->id]);

        // Act
        $rootItems = $menu->rootItems;

        // Assert
        $this->assertCount(1, $rootItems);
        $this->assertEquals($rootItem->id, $rootItems->first()->id);
    }

    public function test_get_structure_returns_nested_array(): void
    {
        // Arrange
        $menu = Menu::create(['name' => 'Test Menu', 'slug' => 'test-menu']);
        $rootItem = MenuItem::create([
            'menu_id' => $menu->id,
            'parent_id' => null,
            'title' => 'Root Item',
            'order' => 1,
        ]);
        MenuItem::create([
            'menu_id' => $menu->id,
            'parent_id' => $rootItem->id,
            'title' => 'Child Item',
            'order' => 1,
        ]);

        // Act
        $structure = $menu->getStructure();

        // Assert
        $this->assertIsArray($structure);
        $this->assertCount(1, $structure);
        $this->assertEquals('Root Item', $structure[0]['title']);
        $this->assertIsArray($structure[0]['children']);
        $this->assertCount(1, $structure[0]['children']);
        $this->assertEquals('Child Item', $structure[0]['children'][0]['title']);
    }

    public function test_duplicate_creates_new_menu_with_same_attributes(): void
    {
        // Arrange
        $originalMenu = Menu::create([
            'name' => 'Original Menu',
            'slug' => 'original-menu',
            'description' => 'Test Description',
            'location' => 'header',
            'is_active' => true,
        ]);

        // Act
        $duplicatedMenu = $originalMenu->duplicate('Duplicated Menu');

        // Assert
        $this->assertInstanceOf(Menu::class, $duplicatedMenu);
        $this->assertEquals('Duplicated Menu', $duplicatedMenu->name);
        $this->assertEquals('duplicated-menu', $duplicatedMenu->slug);
        $this->assertEquals('Test Description', $duplicatedMenu->description);
        $this->assertEquals('header', $duplicatedMenu->location);
        $this->assertTrue($duplicatedMenu->is_active);
        $this->assertNotEquals($originalMenu->id, $duplicatedMenu->id);
    }

    public function test_duplicate_copies_all_menu_items(): void
    {
        // Arrange
        $originalMenu = Menu::create(['name' => 'Original', 'slug' => 'original']);
        MenuItem::create(['menu_id' => $originalMenu->id, 'title' => 'Item 1']);
        MenuItem::create(['menu_id' => $originalMenu->id, 'title' => 'Item 2']);
        MenuItem::create(['menu_id' => $originalMenu->id, 'title' => 'Item 3']);

        // Act
        $duplicatedMenu = $originalMenu->duplicate('Duplicated Menu');

        // Assert
        $this->assertCount(3, $duplicatedMenu->items);
    }

    public function test_duplicate_preserves_parent_child_relationships(): void
    {
        // Arrange
        $originalMenu = Menu::create(['name' => 'Original', 'slug' => 'original']);
        $parentItem = MenuItem::create([
            'menu_id' => $originalMenu->id,
            'parent_id' => null,
            'title' => 'Parent',
        ]);
        MenuItem::create([
            'menu_id' => $originalMenu->id,
            'parent_id' => $parentItem->id,
            'title' => 'Child',
        ]);

        // Act
        $duplicatedMenu = $originalMenu->duplicate('Duplicated Menu');

        // Assert
        $duplicatedItems = $duplicatedMenu->items()->orderBy('id')->get();
        $this->assertCount(2, $duplicatedItems);

        $newParent = $duplicatedItems->where('title', 'Parent')->first();
        $newChild = $duplicatedItems->where('title', 'Child')->first();

        $this->assertNull($newParent->parent_id);
        $this->assertEquals($newParent->id, $newChild->parent_id);
    }

    public function test_casts_settings_to_array(): void
    {
        // Arrange & Act
        $menu = Menu::create([
            'name' => 'Test',
            'slug' => 'test',
            'settings' => ['color' => 'blue', 'size' => 'large'],
        ]);

        // Assert
        $this->assertIsArray($menu->settings);
        $this->assertEquals('blue', $menu->settings['color']);
        $this->assertEquals('large', $menu->settings['size']);
    }

    public function test_casts_is_active_to_boolean(): void
    {
        // Arrange & Act
        $menu = Menu::create(['name' => 'Test', 'slug' => 'test', 'is_active' => 1]);

        // Assert
        $this->assertIsBool($menu->is_active);
        $this->assertTrue($menu->is_active);
    }

    public function test_soft_deletes_menu(): void
    {
        // Arrange
        $menu = Menu::create(['name' => 'Test', 'slug' => 'test']);
        $menuId = $menu->id;

        // Act
        $menu->delete();

        // Assert
        $this->assertDatabaseHas('menus', ['id' => $menuId]);
        $this->assertSoftDeleted('menus', ['id' => $menuId]);
    }
}
