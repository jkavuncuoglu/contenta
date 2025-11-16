<?php

declare(strict_types=1);

namespace App\Domains\PageBuilder\Tests\UnitTests;

use App\Domains\PageBuilder\Models\Block;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BlockModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_fillable_attributes(): void
    {
        // Arrange & Act
        $block = Block::create([
            'name' => 'Test Block',
            'type' => 'text',
            'category' => 'content',
            'config_schema' => ['title' => ['type' => 'string']],
            'component_path' => 'blocks/TestBlock.vue',
            'preview_image' => '/images/preview.jpg',
            'description' => 'Test description',
            'is_active' => true,
        ]);

        // Assert
        $this->assertEquals('Test Block', $block->name);
        $this->assertEquals('text', $block->type);
        $this->assertEquals('content', $block->category);
        $this->assertEquals(['title' => ['type' => 'string']], $block->config_schema);
        $this->assertEquals('blocks/TestBlock.vue', $block->component_path);
        $this->assertEquals('/images/preview.jpg', $block->preview_image);
        $this->assertEquals('Test description', $block->description);
        $this->assertTrue($block->is_active);
    }

    public function test_config_schema_casts_to_array(): void
    {
        // Arrange & Act
        $block = Block::create([
            'name' => 'Test',
            'type' => 'test',
            'component_path' => 'blocks/Test.vue',
            'config_schema' => ['field1' => ['type' => 'string'], 'field2' => ['type' => 'number']],
        ]);

        // Assert
        $this->assertIsArray($block->config_schema);
        $this->assertArrayHasKey('field1', $block->config_schema);
        $this->assertArrayHasKey('field2', $block->config_schema);
    }

    public function test_is_active_casts_to_boolean(): void
    {
        // Arrange & Act
        $block = Block::create([
            'name' => 'Test',
            'type' => 'test',
            'component_path' => 'blocks/Test.vue',
            'is_active' => 1,
        ]);

        // Assert
        $this->assertIsBool($block->is_active);
        $this->assertTrue($block->is_active);
    }

    public function test_default_is_active_is_true(): void
    {
        // Arrange & Act
        $block = Block::create([
            'name' => 'Test',
            'type' => 'test',
            'component_path' => 'blocks/Test.vue',
        ]);

        // Assert
        $this->assertTrue($block->is_active);
    }

    public function test_default_category_is_general(): void
    {
        // Arrange & Act
        $block = Block::create([
            'name' => 'Test',
            'type' => 'test',
            'component_path' => 'blocks/Test.vue',
        ]);

        // Assert
        $this->assertEquals('general', $block->category);
    }

    public function test_default_config_schema_is_empty_object(): void
    {
        // Arrange & Act
        $block = Block::create([
            'name' => 'Test',
            'type' => 'test',
            'component_path' => 'blocks/Test.vue',
        ]);

        // Assert
        $this->assertIsArray($block->config_schema);
        $this->assertEmpty($block->config_schema);
    }

    public function test_scope_active_filters_active_blocks(): void
    {
        // Arrange
        Block::create([
            'name' => 'Active Block',
            'type' => 'active',
            'component_path' => 'blocks/Active.vue',
            'is_active' => true,
        ]);

        Block::create([
            'name' => 'Inactive Block',
            'type' => 'inactive',
            'component_path' => 'blocks/Inactive.vue',
            'is_active' => false,
        ]);

        // Act
        $activeBlocks = Block::active()->get();

        // Assert
        $this->assertCount(1, $activeBlocks);
        $this->assertEquals('Active Block', $activeBlocks->first()->name);
    }

    public function test_scope_by_category_filters_by_category(): void
    {
        // Arrange
        Block::create([
            'name' => 'Content Block',
            'type' => 'content',
            'component_path' => 'blocks/Content.vue',
            'category' => Block::CATEGORY_CONTENT,
        ]);

        Block::create([
            'name' => 'Layout Block',
            'type' => 'layout',
            'component_path' => 'blocks/Layout.vue',
            'category' => Block::CATEGORY_LAYOUT,
        ]);

        Block::create([
            'name' => 'Media Block',
            'type' => 'media',
            'component_path' => 'blocks/Media.vue',
            'category' => Block::CATEGORY_MEDIA,
        ]);

        // Act
        $contentBlocks = Block::byCategory(Block::CATEGORY_CONTENT)->get();

        // Assert
        $this->assertCount(1, $contentBlocks);
        $this->assertEquals('Content Block', $contentBlocks->first()->name);
    }

    public function test_get_categories_returns_all_category_constants(): void
    {
        // Act
        $categories = Block::getCategories();

        // Assert
        $this->assertIsArray($categories);
        $this->assertArrayHasKey(Block::CATEGORY_GENERAL, $categories);
        $this->assertArrayHasKey(Block::CATEGORY_LAYOUT, $categories);
        $this->assertArrayHasKey(Block::CATEGORY_CONTENT, $categories);
        $this->assertArrayHasKey(Block::CATEGORY_MEDIA, $categories);
        $this->assertArrayHasKey(Block::CATEGORY_FORMS, $categories);
        $this->assertArrayHasKey(Block::CATEGORY_NAVIGATION, $categories);
        $this->assertCount(6, $categories);
    }

    public function test_default_config_accessor_returns_default_values_from_schema(): void
    {
        // Arrange
        $block = Block::create([
            'name' => 'Test',
            'type' => 'test',
            'component_path' => 'blocks/Test.vue',
            'config_schema' => [
                'title' => ['type' => 'string', 'default' => 'Default Title'],
                'count' => ['type' => 'number', 'default' => 10],
                'enabled' => ['type' => 'boolean', 'default' => true],
            ],
        ]);

        // Act
        $defaultConfig = $block->default_config;

        // Assert
        $this->assertIsArray($defaultConfig);
        $this->assertEquals('Default Title', $defaultConfig['title']);
        $this->assertEquals(10, $defaultConfig['count']);
        $this->assertTrue($defaultConfig['enabled']);
    }

    public function test_default_config_accessor_returns_null_for_fields_without_default(): void
    {
        // Arrange
        $block = Block::create([
            'name' => 'Test',
            'type' => 'test',
            'component_path' => 'blocks/Test.vue',
            'config_schema' => [
                'title' => ['type' => 'string'],
                'description' => ['type' => 'string'],
            ],
        ]);

        // Act
        $defaultConfig = $block->default_config;

        // Assert
        $this->assertNull($defaultConfig['title']);
        $this->assertNull($defaultConfig['description']);
    }

    public function test_validate_config_returns_empty_array_for_valid_config(): void
    {
        // Arrange
        $block = Block::create([
            'name' => 'Test',
            'type' => 'test',
            'component_path' => 'blocks/Test.vue',
            'config_schema' => [
                'title' => ['type' => 'string', 'required' => true],
                'count' => ['type' => 'number'],
            ],
        ]);

        $config = [
            'title' => 'Test Title',
            'count' => 5,
        ];

        // Act
        $errors = $block->validateConfig($config);

        // Assert
        $this->assertEmpty($errors);
    }

    public function test_validate_config_returns_error_for_missing_required_field(): void
    {
        // Arrange
        $block = Block::create([
            'name' => 'Test',
            'type' => 'test',
            'component_path' => 'blocks/Test.vue',
            'config_schema' => [
                'title' => ['type' => 'string', 'required' => true],
            ],
        ]);

        $config = [];

        // Act
        $errors = $block->validateConfig($config);

        // Assert
        $this->assertNotEmpty($errors);
        $this->assertArrayHasKey('title', $errors);
        $this->assertEquals('Field title is required', $errors['title']);
    }

    public function test_validate_config_validates_string_type(): void
    {
        // Arrange
        $block = Block::create([
            'name' => 'Test',
            'type' => 'test',
            'component_path' => 'blocks/Test.vue',
            'config_schema' => [
                'title' => ['type' => 'string'],
            ],
        ]);

        $config = ['title' => 123];

        // Act
        $errors = $block->validateConfig($config);

        // Assert
        $this->assertNotEmpty($errors);
        $this->assertArrayHasKey('title', $errors);
        $this->assertEquals('Field title must be a string', $errors['title']);
    }

    public function test_validate_config_validates_number_type(): void
    {
        // Arrange
        $block = Block::create([
            'name' => 'Test',
            'type' => 'test',
            'component_path' => 'blocks/Test.vue',
            'config_schema' => [
                'count' => ['type' => 'number'],
            ],
        ]);

        $config = ['count' => 'not a number'];

        // Act
        $errors = $block->validateConfig($config);

        // Assert
        $this->assertNotEmpty($errors);
        $this->assertArrayHasKey('count', $errors);
        $this->assertEquals('Field count must be a number', $errors['count']);
    }

    public function test_validate_config_validates_boolean_type(): void
    {
        // Arrange
        $block = Block::create([
            'name' => 'Test',
            'type' => 'test',
            'component_path' => 'blocks/Test.vue',
            'config_schema' => [
                'enabled' => ['type' => 'boolean'],
            ],
        ]);

        $config = ['enabled' => 'yes'];

        // Act
        $errors = $block->validateConfig($config);

        // Assert
        $this->assertNotEmpty($errors);
        $this->assertArrayHasKey('enabled', $errors);
        $this->assertEquals('Field enabled must be a boolean', $errors['enabled']);
    }

    public function test_validate_config_validates_array_type(): void
    {
        // Arrange
        $block = Block::create([
            'name' => 'Test',
            'type' => 'test',
            'component_path' => 'blocks/Test.vue',
            'config_schema' => [
                'items' => ['type' => 'array'],
            ],
        ]);

        $config = ['items' => 'not an array'];

        // Act
        $errors = $block->validateConfig($config);

        // Assert
        $this->assertNotEmpty($errors);
        $this->assertArrayHasKey('items', $errors);
        $this->assertEquals('Field items must be an array', $errors['items']);
    }

    public function test_validate_config_validates_multiple_fields(): void
    {
        // Arrange
        $block = Block::create([
            'name' => 'Test',
            'type' => 'test',
            'component_path' => 'blocks/Test.vue',
            'config_schema' => [
                'title' => ['type' => 'string', 'required' => true],
                'count' => ['type' => 'number', 'required' => true],
                'enabled' => ['type' => 'boolean'],
            ],
        ]);

        $config = [
            'count' => 'invalid',
            'enabled' => 'also invalid',
        ];

        // Act
        $errors = $block->validateConfig($config);

        // Assert
        $this->assertCount(3, $errors);
        $this->assertArrayHasKey('title', $errors);
        $this->assertArrayHasKey('count', $errors);
        $this->assertArrayHasKey('enabled', $errors);
    }

    public function test_validate_config_allows_optional_fields_to_be_missing(): void
    {
        // Arrange
        $block = Block::create([
            'name' => 'Test',
            'type' => 'test',
            'component_path' => 'blocks/Test.vue',
            'config_schema' => [
                'title' => ['type' => 'string', 'required' => true],
                'description' => ['type' => 'string'],
            ],
        ]);

        $config = ['title' => 'Test Title'];

        // Act
        $errors = $block->validateConfig($config);

        // Assert
        $this->assertEmpty($errors);
    }
}
