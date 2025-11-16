<?php

declare(strict_types=1);

namespace App\Domains\PageBuilder\Tests\UnitTests;

use App\Domains\PageBuilder\Models\Block;
use App\Domains\PageBuilder\Models\Layout;
use App\Domains\PageBuilder\Models\Page;
use App\Domains\PageBuilder\Services\PageBuilderService;
use App\Domains\PageBuilder\Services\PageBuilderServiceContract;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PageBuilderServiceTest extends TestCase
{
    use RefreshDatabase;

    protected PageBuilderService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(PageBuilderServiceContract::class);
    }

    public function test_it_can_get_paginated_pages(): void
    {
        // Arrange
        Page::factory()->count(5)->create();

        // Act
        $result = $this->service->getPaginatedPages(10);

        // Assert
        $this->assertEquals(5, $result->total());
    }

    public function test_it_can_create_page(): void
    {
        // Arrange
        $data = [
            'title' => 'Test Page',
            'layout_id' => Layout::factory()->create()->id,
        ];

        // Act
        $page = $this->service->createPage($data);

        // Assert
        $this->assertInstanceOf(Page::class, $page);
        $this->assertEquals('Test Page', $page->title);
        $this->assertEquals('test-page', $page->slug);
    }

    public function test_it_can_update_page(): void
    {
        // Arrange
        $page = Page::factory()->create(['title' => 'Original']);

        // Act
        $updated = $this->service->updatePage($page, ['title' => 'Updated']);

        // Assert
        $this->assertEquals('Updated', $updated->title);
        $this->assertEquals('updated', $updated->slug);
    }

    public function test_it_can_delete_page(): void
    {
        // Arrange
        $page = Page::factory()->create();

        // Act
        $result = $this->service->deletePage($page);

        // Assert
        $this->assertTrue($result);
        $this->assertSoftDeleted('pagebuilder_pages', ['id' => $page->id]);
    }

    public function test_it_can_publish_page(): void
    {
        // Arrange
        $page = Page::factory()->create(['status' => Page::STATUS_DRAFT]);

        // Act
        $published = $this->service->publishPage($page);

        // Assert
        $this->assertTrue($published->isPublished());
        $this->assertNotNull($published->published_at);
    }

    public function test_it_can_unpublish_page(): void
    {
        // Arrange
        $page = Page::factory()->create(['status' => Page::STATUS_PUBLISHED]);

        // Act
        $unpublished = $this->service->unpublishPage($page);

        // Assert
        $this->assertFalse($unpublished->isPublished());
    }

    public function test_it_can_duplicate_page(): void
    {
        // Arrange
        $original = Page::factory()->create(['title' => 'Original']);

        // Act
        $duplicate = $this->service->duplicatePage($original, 'Duplicate');

        // Assert
        $this->assertEquals('Duplicate', $duplicate->title);
        $this->assertEquals('duplicate', $duplicate->slug);
        $this->assertFalse($duplicate->isPublished());
    }

    public function test_it_can_get_page_by_slug(): void
    {
        // Arrange
        Page::factory()->create(['slug' => 'test-slug', 'status' => Page::STATUS_PUBLISHED]);

        // Act
        $page = $this->service->getPageBySlug('test-slug');

        // Assert
        $this->assertInstanceOf(Page::class, $page);
        $this->assertEquals('test-slug', $page->slug);
    }

    public function test_it_can_get_all_layouts(): void
    {
        // Arrange
        Layout::factory()->count(3)->create();

        // Act
        $layouts = $this->service->getAllLayouts();

        // Assert
        $this->assertCount(3, $layouts);
    }

    public function test_it_can_get_active_blocks(): void
    {
        // Arrange
        Block::factory()->count(2)->create(['is_active' => true]);
        Block::factory()->create(['is_active' => false]);

        // Act
        $blocks = $this->service->getActiveBlocks();

        // Assert
        $this->assertCount(2, $blocks);
    }

    // Sad Path Tests

    public function test_it_returns_empty_paginated_pages_when_none_exist(): void
    {
        // Act
        $result = $this->service->getPaginatedPages(10);

        // Assert
        $this->assertEquals(0, $result->total());
        $this->assertCount(0, $result->items());
    }

    public function test_it_returns_null_when_page_slug_not_found(): void
    {
        // Act
        $page = $this->service->getPageBySlug('non-existent-slug');

        // Assert
        $this->assertNull($page);
    }

    public function test_it_returns_empty_collection_when_no_layouts_exist(): void
    {
        // Act
        $layouts = $this->service->getAllLayouts();

        // Assert
        $this->assertCount(0, $layouts);
    }

    public function test_it_returns_empty_collection_when_no_active_blocks_exist(): void
    {
        // Arrange
        Block::factory()->count(3)->create(['is_active' => false]);

        // Act
        $blocks = $this->service->getActiveBlocks();

        // Assert
        $this->assertCount(0, $blocks);
    }

    public function test_it_generates_unique_slug_when_creating_page_with_duplicate_title(): void
    {
        // Arrange
        Page::factory()->create(['title' => 'Test Page', 'slug' => 'test-page']);
        $layout = Layout::factory()->create();

        // Act
        $page = $this->service->createPage([
            'title' => 'Test Page',
            'layout_id' => $layout->id,
        ]);

        // Assert
        $this->assertNotEquals('test-page', $page->slug);
        $this->assertStringStartsWith('test-page', $page->slug);
    }

    public function test_it_preserves_existing_slug_when_updating_page_without_title_change(): void
    {
        // Arrange
        $page = Page::factory()->create(['title' => 'Original', 'slug' => 'custom-slug']);

        // Act
        $updated = $this->service->updatePage($page, ['status' => Page::STATUS_PUBLISHED]);

        // Assert
        $this->assertEquals('custom-slug', $updated->slug);
    }

    public function test_duplicate_page_creates_draft_regardless_of_original_status(): void
    {
        // Arrange
        $original = Page::factory()->create([
            'title' => 'Published Page',
            'status' => Page::STATUS_PUBLISHED,
            'published_at' => now(),
        ]);

        // Act
        $duplicate = $this->service->duplicatePage($original, 'Duplicate');

        // Assert
        $this->assertEquals(Page::STATUS_DRAFT, $duplicate->status);
        $this->assertNull($duplicate->published_at);
    }

    public function test_unpublish_page_clears_published_at_timestamp(): void
    {
        // Arrange
        $page = Page::factory()->create([
            'status' => Page::STATUS_PUBLISHED,
            'published_at' => now(),
        ]);

        // Act
        $unpublished = $this->service->unpublishPage($page);

        // Assert
        $this->assertNull($unpublished->published_at);
    }
}
