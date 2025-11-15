<?php

declare(strict_types=1);

namespace App\Domains\ContentManagement\Categories\Tests\UnitTests;

use App\Domains\ContentManagement\Categories\Models\Category;
use App\Domains\ContentManagement\Posts\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_has_fillable_attributes(): void
    {
        // Arrange
        $data = [
            'name' => 'Technology',
            'slug' => 'technology',
            'description' => 'Tech articles',
            'parent_id' => null,
            'is_featured' => true,
            'sort_order' => 1,
            'meta_title' => 'Tech Meta',
            'meta_description' => 'Tech meta description',
        ];

        // Act
        $category = Category::create($data);

        // Assert
        $this->assertEquals('Technology', $category->name);
        $this->assertEquals('technology', $category->slug);
        $this->assertEquals('Tech articles', $category->description);
        $this->assertTrue($category->is_featured);
        $this->assertEquals(1, $category->sort_order);
    }

    public function test_it_belongs_to_parent_category(): void
    {
        // Arrange
        $parent = Category::factory()->create(['name' => 'Parent']);
        $child = Category::factory()->create([
            'name' => 'Child',
            'parent_id' => $parent->id,
        ]);

        // Act
        $result = $child->parent;

        // Assert
        $this->assertInstanceOf(Category::class, $result);
        $this->assertEquals('Parent', $result->name);
    }

    public function test_it_has_many_children(): void
    {
        // Arrange
        $parent = Category::factory()->create();
        Category::factory()->count(3)->create(['parent_id' => $parent->id]);

        // Act
        $children = $parent->children;

        // Assert
        $this->assertCount(3, $children);
    }

    public function test_it_belongs_to_many_posts(): void
    {
        // Arrange
        $category = Category::factory()->create();
        $posts = Post::factory()->count(3)->create();
        $category->posts()->attach($posts->pluck('id'));

        // Act
        $result = $category->posts;

        // Assert
        $this->assertCount(3, $result);
    }

    public function test_featured_scope_returns_only_featured_categories(): void
    {
        // Arrange
        Category::factory()->count(2)->create(['is_featured' => true]);
        Category::factory()->count(3)->create(['is_featured' => false]);

        // Act
        $featured = Category::featured()->get();

        // Assert
        $this->assertCount(2, $featured);
    }

    public function test_parent_scope_returns_only_top_level_categories(): void
    {
        // Arrange
        Category::factory()->count(2)->create(['parent_id' => null]);
        $parent = Category::factory()->create();
        Category::factory()->count(3)->create(['parent_id' => $parent->id]);

        // Act
        $parents = Category::query()->parent()->get();

        // Assert
        $this->assertCount(3, $parents);
    }

    public function test_ordered_scope_orders_by_sort_order_and_name(): void
    {
        // Arrange
        Category::factory()->create(['name' => 'C', 'sort_order' => 1]);
        Category::factory()->create(['name' => 'A', 'sort_order' => 1]);
        Category::factory()->create(['name' => 'B', 'sort_order' => 2]);

        // Act
        $ordered = Category::ordered()->get();

        // Assert
        $this->assertEquals('A', $ordered[0]->name);
        $this->assertEquals('C', $ordered[1]->name);
        $this->assertEquals('B', $ordered[2]->name);
    }

    public function test_get_full_path_attribute_returns_hierarchical_path(): void
    {
        // Arrange
        $grandparent = Category::factory()->create(['name' => 'Grandparent']);
        $parent = Category::factory()->create([
            'name' => 'Parent',
            'parent_id' => $grandparent->id,
        ]);
        $child = Category::factory()->create([
            'name' => 'Child',
            'parent_id' => $parent->id,
        ]);

        // Act
        $path = $child->getFullPathAttribute();

        // Assert
        $this->assertEquals('Grandparent > Parent > Child', $path);
    }

    public function test_get_depth_attribute_returns_correct_depth(): void
    {
        // Arrange
        $grandparent = Category::factory()->create(['name' => 'Grandparent']);
        $parent = Category::factory()->create([
            'name' => 'Parent',
            'parent_id' => $grandparent->id,
        ]);
        $child = Category::factory()->create([
            'name' => 'Child',
            'parent_id' => $parent->id,
        ]);

        // Act & Assert
        $this->assertEquals(0, $grandparent->getDepthAttribute());
        $this->assertEquals(1, $parent->getDepthAttribute());
        $this->assertEquals(2, $child->getDepthAttribute());
    }

    public function test_get_all_children_returns_nested_children(): void
    {
        // Arrange
        $parent = Category::factory()->create(['name' => 'Parent']);
        $child1 = Category::factory()->create([
            'name' => 'Child 1',
            'parent_id' => $parent->id,
        ]);
        $child2 = Category::factory()->create([
            'name' => 'Child 2',
            'parent_id' => $parent->id,
        ]);
        Category::factory()->create([
            'name' => 'Grandchild',
            'parent_id' => $child1->id,
        ]);

        // Act
        $allChildren = $parent->getAllChildren();

        // Assert
        $this->assertCount(3, $allChildren);
    }

    public function test_it_uses_soft_deletes(): void
    {
        // Arrange
        $category = Category::factory()->create();
        $id = $category->id;

        // Act
        $category->delete();

        // Assert
        $this->assertSoftDeleted('categories', ['id' => $id]);
        $this->assertNotNull(Category::withTrashed()->find($id)->deleted_at);
    }
}
