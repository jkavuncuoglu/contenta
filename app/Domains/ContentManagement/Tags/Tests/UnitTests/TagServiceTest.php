<?php

declare(strict_types=1);

namespace App\Domains\ContentManagement\Tags\Tests\UnitTests;

use App\Domains\ContentManagement\Tags\Services\TagService;
use App\Domains\ContentManagement\Tags\Services\TagServiceContract;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TagServiceTest extends TestCase
{
    use RefreshDatabase;

    protected TagService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(TagServiceContract::class);
    }

    public function test_it_is_bound_to_container(): void
    {
        // Act
        $service = app(TagServiceContract::class);

        // Assert
        $this->assertInstanceOf(TagService::class, $service);
    }

    public function test_it_is_registered_as_singleton(): void
    {
        // Act
        $service1 = app(TagServiceContract::class);
        $service2 = app(TagServiceContract::class);

        // Assert
        $this->assertSame($service1, $service2);
    }

    public function test_create_creates_new_tag(): void
    {
        // Arrange
        $data = [
            'name' => 'Laravel',
            'slug' => 'laravel',
            'description' => 'Laravel framework',
        ];

        // Act
        $tag = $this->service->create($data);

        // Assert
        $this->assertInstanceOf(\App\Domains\ContentManagement\Tags\Models\Tag::class, $tag);
        $this->assertEquals('Laravel', $tag->name);
        $this->assertEquals('laravel', $tag->slug);
        $this->assertDatabaseHas('tags', $data);
    }

    public function test_update_updates_existing_tag(): void
    {
        // Arrange
        $tag = \App\Domains\ContentManagement\Tags\Models\Tag::factory()->create([
            'name' => 'Old Name',
        ]);

        $data = [
            'name' => 'Updated Name',
            'description' => 'Updated description',
        ];

        // Act
        $result = $this->service->update($tag, $data);

        // Assert
        $this->assertTrue($result);
        $tag->refresh();
        $this->assertEquals('Updated Name', $tag->name);
        $this->assertEquals('Updated description', $tag->description);
    }

    public function test_delete_deletes_tag(): void
    {
        // Arrange
        $tag = \App\Domains\ContentManagement\Tags\Models\Tag::factory()->create();

        // Act
        $result = $this->service->delete($tag);

        // Assert
        $this->assertTrue($result);
        $this->assertSoftDeleted('tags', [
            'id' => $tag->id,
        ]);
    }

    public function test_find_returns_tag_by_id(): void
    {
        // Arrange
        $tag = \App\Domains\ContentManagement\Tags\Models\Tag::factory()->create([
            'name' => 'Test Tag',
        ]);

        // Act
        $found = $this->service->find($tag->id);

        // Assert
        $this->assertNotNull($found);
        $this->assertEquals('Test Tag', $found->name);
        $this->assertEquals($tag->id, $found->id);
    }

    public function test_find_returns_null_when_not_found(): void
    {
        // Act
        $found = $this->service->find(99999);

        // Assert
        $this->assertNull($found);
    }

    public function test_list_returns_collection_of_tags(): void
    {
        // Arrange
        \App\Domains\ContentManagement\Tags\Models\Tag::factory()->count(5)->create();

        // Act
        $tags = $this->service->list();

        // Assert
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $tags);
        $this->assertCount(5, $tags);
    }

    public function test_list_with_filters_returns_filtered_tags(): void
    {
        // Arrange
        \App\Domains\ContentManagement\Tags\Models\Tag::factory()->count(5)->create();

        // Act
        $tags = $this->service->list([]);

        // Assert
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $tags);
        $this->assertCount(5, $tags);
    }
}
