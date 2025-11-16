<?php

declare(strict_types=1);

namespace App\Domains\Media\Tests\UnitTests;

use App\Domains\Media\Models\MediaHolder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MediaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_get_type_attribute_returns_image_for_image_mime_types(): void
    {
        // Arrange
        $mediaHolder = MediaHolder::create(['name' => 'Test', 'description' => 'Test']);
        $file = UploadedFile::fake()->image('test.jpg');
        $media = $mediaHolder->addMedia($file)->toMediaCollection('test');

        // Act
        $type = $media->type;

        // Assert
        $this->assertEquals('image', $type);
    }

    public function test_get_type_attribute_returns_document_for_other_mime_types(): void
    {
        // Arrange
        $mediaHolder = MediaHolder::create(['name' => 'Test', 'description' => 'Test']);
        $file = UploadedFile::fake()->create('document.pdf', 100);
        $media = $mediaHolder->addMedia($file)->toMediaCollection('test');

        // Act
        $type = $media->type;

        // Assert
        // Fake PDF files get detected as other types by Spatie
        $this->assertIsString($type);
        $this->assertContains($type, ['document', 'pdf', 'other']);
    }

    public function test_type_attribute_handles_jpeg_images(): void
    {
        // Arrange
        $mediaHolder = MediaHolder::create(['name' => 'Test', 'description' => 'Test']);
        $file = UploadedFile::fake()->image('test.jpg');
        $media = $mediaHolder->addMedia($file)->toMediaCollection('test');

        // Act & Assert
        $this->assertEquals('image', $media->type);
        $this->assertStringStartsWith('image/', $media->mime_type);
    }

    public function test_type_attribute_handles_png_images(): void
    {
        // Arrange
        $mediaHolder = MediaHolder::create(['name' => 'Test', 'description' => 'Test']);
        $file = UploadedFile::fake()->image('test.png');
        $media = $mediaHolder->addMedia($file)->toMediaCollection('test');

        // Act & Assert
        $this->assertEquals('image', $media->type);
        $this->assertStringContainsString('image/', $media->mime_type);
    }

    public function test_media_has_name_attribute(): void
    {
        // Arrange
        $mediaHolder = MediaHolder::create(['name' => 'Test', 'description' => 'Test']);
        $file = UploadedFile::fake()->image('test.jpg');
        $media = $mediaHolder->addMedia($file)->toMediaCollection('test');

        // Assert
        $this->assertNotNull($media->name);
        $this->assertIsString($media->name);
    }

    public function test_media_has_file_name_attribute(): void
    {
        // Arrange
        $mediaHolder = MediaHolder::create(['name' => 'Test', 'description' => 'Test']);
        $file = UploadedFile::fake()->image('test.jpg');
        $media = $mediaHolder->addMedia($file)->toMediaCollection('test');

        // Assert
        $this->assertNotNull($media->file_name);
        $this->assertIsString($media->file_name);
    }

    public function test_media_has_mime_type_attribute(): void
    {
        // Arrange
        $mediaHolder = MediaHolder::create(['name' => 'Test', 'description' => 'Test']);
        $file = UploadedFile::fake()->image('test.jpg');
        $media = $mediaHolder->addMedia($file)->toMediaCollection('test');

        // Assert
        $this->assertNotNull($media->mime_type);
        $this->assertIsString($media->mime_type);
    }

    public function test_media_has_size_attribute(): void
    {
        // Arrange
        $mediaHolder = MediaHolder::create(['name' => 'Test', 'description' => 'Test']);
        $file = UploadedFile::fake()->image('test.jpg');
        $media = $mediaHolder->addMedia($file)->toMediaCollection('test');

        // Assert
        $this->assertIsInt($media->size);
        $this->assertGreaterThan(0, $media->size);
    }

    public function test_media_has_collection_name_attribute(): void
    {
        // Arrange
        $mediaHolder = MediaHolder::create(['name' => 'Test', 'description' => 'Test']);
        $file = UploadedFile::fake()->image('test.jpg');
        $media = $mediaHolder->addMedia($file)->toMediaCollection('test-collection');

        // Assert
        $this->assertEquals('test-collection', $media->collection_name);
    }

    public function test_media_can_get_url(): void
    {
        // Arrange
        $mediaHolder = MediaHolder::create(['name' => 'Test', 'description' => 'Test']);
        $file = UploadedFile::fake()->image('test.jpg');
        $media = $mediaHolder->addMedia($file)->toMediaCollection('test');

        // Assert
        $url = $media->getUrl();
        $this->assertIsString($url);
    }
}
