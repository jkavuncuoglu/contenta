<?php

declare(strict_types=1);

namespace App\Domains\ContentManagement\Categories\Tests\UnitTests;

use App\Domains\ContentManagement\Categories\Services\CategoryService;
use App\Domains\ContentManagement\Categories\Services\CategoryServiceContract;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryServiceTest extends TestCase
{
    use RefreshDatabase;

    protected CategoryService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(CategoryServiceContract::class);
    }

    public function test_it_is_bound_to_container(): void
    {
        // Act
        $service = app(CategoryServiceContract::class);

        // Assert
        $this->assertInstanceOf(CategoryService::class, $service);
    }

    public function test_it_is_registered_as_singleton(): void
    {
        // Act
        $service1 = app(CategoryServiceContract::class);
        $service2 = app(CategoryServiceContract::class);

        // Assert
        $this->assertSame($service1, $service2);
    }

    public function test_get_all_categories_returns_collection(): void
    {
        // Arrange
        \App\Domains\ContentManagement\Categories\Models\Category::factory()->count(5)->create();

        // Act
        $categories = $this->service->getAllCategories();

        // Assert
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $categories);
        $this->assertCount(5, $categories);
    }

    public function test_find_category_by_id_returns_category(): void
    {
        // Arrange
        $category = \App\Domains\ContentManagement\Categories\Models\Category::factory()->create([
            'name' => 'Test Category',
        ]);

        // Act
        $found = $this->service->findCategoryById($category->id);

        // Assert
        $this->assertNotNull($found);
        $this->assertEquals('Test Category', $found->name);
        $this->assertEquals($category->id, $found->id);
    }

    public function test_find_category_by_id_returns_null_when_not_found(): void
    {
        // Act
        $found = $this->service->findCategoryById(99999);

        // Assert
        $this->assertNull($found);
    }

    public function test_create_category_creates_new_category(): void
    {
        // Arrange
        $data = [
            'name' => 'New Category',
            'slug' => 'new-category',
            'description' => 'Category description',
        ];

        // Act
        $category = $this->service->createCategory($data);

        // Assert
        $this->assertInstanceOf(\App\Domains\ContentManagement\Categories\Models\Category::class, $category);
        $this->assertEquals('New Category', $category->name);
        $this->assertEquals('new-category', $category->slug);
        $this->assertDatabaseHas('categories', $data);
    }

    public function test_update_category_updates_existing_category(): void
    {
        // Arrange
        $category = \App\Domains\ContentManagement\Categories\Models\Category::factory()->create([
            'name' => 'Old Name',
        ]);

        $data = [
            'name' => 'Updated Name',
            'description' => 'Updated description',
        ];

        // Act
        $updated = $this->service->updateCategory($category->id, $data);

        // Assert
        $this->assertEquals('Updated Name', $updated->name);
        $this->assertEquals('Updated description', $updated->description);
        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'Updated Name',
        ]);
    }

    public function test_update_category_throws_exception_when_not_found(): void
    {
        // Assert
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        // Act
        $this->service->updateCategory(99999, ['name' => 'Test']);
    }

    public function test_delete_category_deletes_category(): void
    {
        // Arrange
        $category = \App\Domains\ContentManagement\Categories\Models\Category::factory()->create();

        // Act
        $result = $this->service->deleteCategory($category->id);

        // Assert
        $this->assertTrue($result);
        $this->assertSoftDeleted('categories', [
            'id' => $category->id,
        ]);
    }

    public function test_delete_category_throws_exception_when_not_found(): void
    {
        // Assert
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        // Act
        $this->service->deleteCategory(99999);
    }
}
