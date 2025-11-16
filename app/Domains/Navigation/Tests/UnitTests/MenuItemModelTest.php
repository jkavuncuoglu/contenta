<?php

declare(strict_types=1);

namespace App\Domains\Navigation\Tests\UnitTests;

use App\Domains\Navigation\Models\Menu;
use App\Domains\Navigation\Models\MenuItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MenuItemModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_menu_relationship_returns_parent_menu(): void
    {
        // Arrange
        $menu = Menu::factory()->create();
        $item = MenuItem::factory()->create(['menu_id' => $menu->id]);

        // Act
        $relatedMenu = $item->menu;

        // Assert
        $this->assertInstanceOf(Menu::class, $relatedMenu);
        $this->assertEquals($menu->id, $relatedMenu->id);
    }

    public function test_parent_relationship_returns_parent_item(): void
    {
        // Arrange
        $menu = Menu::factory()->create();
        $parentItem = MenuItem::factory()->create(['menu_id' => $menu->id]);
        $childItem = MenuItem::factory()->create([
            'menu_id' => $menu->id,
            'parent_id' => $parentItem->id,
        ]);

        // Act
        $parent = $childItem->parent;

        // Assert
        $this->assertInstanceOf(MenuItem::class, $parent);
        $this->assertEquals($parentItem->id, $parent->id);
    }

    public function test_children_relationship_returns_child_items(): void
    {
        // Arrange
        $menu = Menu::factory()->create();
        $parentItem = MenuItem::factory()->create(['menu_id' => $menu->id]);
        $child1 = MenuItem::factory()->create([
            'menu_id' => $menu->id,
            'parent_id' => $parentItem->id,
            'order' => 2,
        ]);
        $child2 = MenuItem::factory()->create([
            'menu_id' => $menu->id,
            'parent_id' => $parentItem->id,
            'order' => 1,
        ]);

        // Act
        $children = $parentItem->children;

        // Assert
        $this->assertCount(2, $children);
        $this->assertEquals($child2->id, $children[0]->id); // Ordered by order column
        $this->assertEquals($child1->id, $children[1]->id);
    }

    public function test_get_resolved_url_returns_direct_url_when_set(): void
    {
        // Arrange
        $item = MenuItem::factory()->create(['url' => 'https://example.com']);

        // Act
        $url = $item->getResolvedUrl();

        // Assert
        $this->assertEquals('https://example.com', $url);
    }

    public function test_get_resolved_url_resolves_page_type(): void
    {
        // Arrange
        $item = MenuItem::factory()->create([
            'url' => null,
            'object_type' => 'page',
            'object_id' => '123',
        ]);

        // Act
        $url = $item->getResolvedUrl();

        // Assert
        $this->assertEquals('/page/123', $url);
    }

    public function test_get_resolved_url_resolves_post_type(): void
    {
        // Arrange
        $item = MenuItem::factory()->create([
            'url' => null,
            'object_type' => 'post',
            'object_id' => '456',
        ]);

        // Act
        $url = $item->getResolvedUrl();

        // Assert
        $this->assertEquals('/post/456', $url);
    }

    public function test_get_resolved_url_resolves_category_type(): void
    {
        // Arrange
        $item = MenuItem::factory()->create([
            'url' => null,
            'object_type' => 'category',
            'object_id' => '789',
        ]);

        // Act
        $url = $item->getResolvedUrl();

        // Assert
        $this->assertEquals('/category/789', $url);
    }

    public function test_get_resolved_url_resolves_tag_type(): void
    {
        // Arrange
        $item = MenuItem::factory()->create([
            'url' => null,
            'object_type' => 'tag',
            'object_id' => '111',
        ]);

        // Act
        $url = $item->getResolvedUrl();

        // Assert
        $this->assertEquals('/tag/111', $url);
    }

    public function test_get_resolved_url_returns_null_for_unknown_type(): void
    {
        // Arrange
        $item = MenuItem::factory()->create([
            'url' => null,
            'object_type' => 'unknown',
            'object_id' => '222',
        ]);

        // Act
        $url = $item->getResolvedUrl();

        // Assert
        $this->assertNull($url);
    }

    public function test_get_resolved_url_returns_null_when_no_url_or_object(): void
    {
        // Arrange
        $item = MenuItem::factory()->create([
            'url' => null,
            'object_type' => null,
            'object_id' => null,
        ]);

        // Act
        $url = $item->getResolvedUrl();

        // Assert
        $this->assertNull($url);
    }

    public function test_to_tree_returns_array_with_correct_structure(): void
    {
        // Arrange
        $menu = Menu::factory()->create();
        $item = MenuItem::factory()->create([
            'menu_id' => $menu->id,
            'title' => 'Test Item',
            'url' => '/test',
            'type' => 'custom',
            'target' => '_blank',
            'css_classes' => 'test-class',
            'icon' => 'icon-test',
            'is_visible' => true,
            'attributes' => ['data-test' => 'value'],
            'metadata' => ['key' => 'value'],
        ]);

        // Act
        $tree = $item->toTree();

        // Assert
        $this->assertIsArray($tree);
        $this->assertEquals($item->id, $tree['id']);
        $this->assertEquals('Test Item', $tree['title']);
        $this->assertEquals('/test', $tree['url']);
        $this->assertEquals('custom', $tree['type']);
        $this->assertEquals('_blank', $tree['target']);
        $this->assertEquals('test-class', $tree['css_classes']);
        $this->assertEquals('icon-test', $tree['icon']);
        $this->assertTrue($tree['is_visible']);
        $this->assertEquals(['data-test' => 'value'], $tree['attributes']);
        $this->assertEquals(['key' => 'value'], $tree['metadata']);
        $this->assertIsArray($tree['children']);
    }

    public function test_to_tree_includes_children(): void
    {
        // Arrange
        $menu = Menu::factory()->create();
        $parentItem = MenuItem::factory()->create([
            'menu_id' => $menu->id,
            'title' => 'Parent',
        ]);
        $childItem = MenuItem::factory()->create([
            'menu_id' => $menu->id,
            'parent_id' => $parentItem->id,
            'title' => 'Child',
        ]);

        // Act
        $tree = $parentItem->fresh(['children'])->toTree();

        // Assert
        $this->assertCount(1, $tree['children']);
        $this->assertEquals('Child', $tree['children'][0]['title']);
    }

    public function test_reorder_updates_order_and_parent(): void
    {
        // Arrange
        $menu = Menu::factory()->create();
        $item1 = MenuItem::factory()->create(['menu_id' => $menu->id, 'order' => 0]);
        $item2 = MenuItem::factory()->create(['menu_id' => $menu->id, 'order' => 1]);
        $item3 = MenuItem::factory()->create(['menu_id' => $menu->id, 'order' => 2]);

        $items = [
            ['id' => $item3->id, 'children' => []],
            ['id' => $item1->id, 'children' => []],
            ['id' => $item2->id, 'children' => []],
        ];

        // Act
        MenuItem::reorder($items);

        // Assert
        $item1->refresh();
        $item2->refresh();
        $item3->refresh();

        $this->assertEquals(1, $item1->order);
        $this->assertEquals(2, $item2->order);
        $this->assertEquals(0, $item3->order);
    }

    public function test_reorder_handles_nested_children(): void
    {
        // Arrange
        $menu = Menu::factory()->create();
        $parent = MenuItem::factory()->create(['menu_id' => $menu->id]);
        $child = MenuItem::factory()->create(['menu_id' => $menu->id, 'parent_id' => null]);

        $items = [
            [
                'id' => $parent->id,
                'children' => [
                    ['id' => $child->id, 'children' => []],
                ],
            ],
        ];

        // Act
        MenuItem::reorder($items);

        // Assert
        $parent->refresh();
        $child->refresh();

        $this->assertEquals(0, $parent->order);
        $this->assertNull($parent->parent_id);
        $this->assertEquals(0, $child->order);
        $this->assertEquals($parent->id, $child->parent_id);
    }

    public function test_casts_attributes_to_array(): void
    {
        // Arrange & Act
        $item = MenuItem::factory()->create([
            'attributes' => ['class' => 'test', 'data-id' => '123'],
        ]);

        // Assert
        $this->assertIsArray($item->attributes);
        $this->assertEquals('test', $item->attributes['class']);
    }

    public function test_casts_metadata_to_array(): void
    {
        // Arrange & Act
        $item = MenuItem::factory()->create([
            'metadata' => ['author' => 'John', 'category' => 'main'],
        ]);

        // Assert
        $this->assertIsArray($item->metadata);
        $this->assertEquals('John', $item->metadata['author']);
    }

    public function test_casts_is_visible_to_boolean(): void
    {
        // Arrange & Act
        $item = MenuItem::factory()->create(['is_visible' => 1]);

        // Assert
        $this->assertIsBool($item->is_visible);
        $this->assertTrue($item->is_visible);
    }

    public function test_casts_order_to_integer(): void
    {
        // Arrange & Act
        $item = MenuItem::factory()->create(['order' => '5']);

        // Assert
        $this->assertIsInt($item->order);
        $this->assertEquals(5, $item->order);
    }

    public function test_soft_deletes_menu_item(): void
    {
        // Arrange
        $item = MenuItem::factory()->create();
        $itemId = $item->id;

        // Act
        $item->delete();

        // Assert
        $this->assertDatabaseHas('menu_items', ['id' => $itemId]);
        $this->assertSoftDeleted('menu_items', ['id' => $itemId]);
    }
}
