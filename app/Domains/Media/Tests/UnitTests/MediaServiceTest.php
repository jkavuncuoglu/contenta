<?php

declare(strict_types=1);

namespace App\Domains\Media\Tests\UnitTests;

use App\Domains\Media\Models\MediaHolder;
use App\Domains\Media\Services\MediaService;
use App\Domains\Media\Services\MediaServiceContract;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Tests\TestCase;

class MediaServiceTest extends TestCase
{
    use RefreshDatabase;

    protected MediaService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(MediaServiceContract::class);
        Storage::fake('public');
    }

    public function test_it_can_get_paginated_media(): void
    {
        // Arrange
        $mediaHolder = MediaHolder::create(['name' => 'Test Holder', 'description' => 'Test']);
        $file = UploadedFile::fake()->image('test.jpg');
        $mediaHolder->addMedia($file)->toMediaCollection('test');

        // Act
        $result = $this->service->getPaginatedMedia(10);

        // Assert
        $this->assertGreaterThan(0, $result->total());
    }

    public function test_it_can_upload_media(): void
    {
        // Arrange
        $file = UploadedFile::fake()->image('upload.jpg');

        // Act
        $media = $this->service->uploadMedia($file, 'test-collection');

        // Assert
        $this->assertInstanceOf(Media::class, $media);
        $this->assertEquals('test-collection', $media->collection_name);
    }

    public function test_it_can_delete_media(): void
    {
        // Arrange
        $mediaHolder = MediaHolder::create(['name' => 'Test Holder', 'description' => 'Test']);
        $file = UploadedFile::fake()->image('delete.jpg');
        $media = $mediaHolder->addMedia($file)->toMediaCollection('test');

        // Act
        $result = $this->service->deleteMedia($media->id);

        // Assert
        $this->assertTrue($result);
        $this->assertDatabaseMissing('media', ['id' => $media->id]);
    }

    public function test_it_can_get_media_by_id(): void
    {
        // Arrange
        $mediaHolder = MediaHolder::create(['name' => 'Test Holder', 'description' => 'Test']);
        $file = UploadedFile::fake()->image('test.jpg');
        $media = $mediaHolder->addMedia($file)->toMediaCollection('test');

        // Act
        $result = $this->service->getMediaById($media->id);

        // Assert
        $this->assertNotNull($result);
        $this->assertEquals($media->id, $result->id);
    }

    public function test_it_can_get_media_by_collection(): void
    {
        // Arrange
        $mediaHolder = MediaHolder::create(['name' => 'Test Holder', 'description' => 'Test']);
        $file = UploadedFile::fake()->image('test.jpg');
        $mediaHolder->addMedia($file)->toMediaCollection('test-collection');

        // Act
        $result = $this->service->getMediaByCollection('test-collection');

        // Assert
        $this->assertCount(1, $result);
    }

    public function test_it_returns_null_for_non_existent_media_id(): void
    {
        // Act
        $result = $this->service->getMediaById(99999);

        // Assert
        $this->assertNull($result);
    }

    public function test_it_returns_false_when_deleting_non_existent_media(): void
    {
        // Act
        $result = $this->service->deleteMedia(99999);

        // Assert
        $this->assertFalse($result);
    }

    public function test_it_returns_empty_array_for_non_existent_collection(): void
    {
        // Act
        $result = $this->service->getMediaByCollection('non-existent');

        // Assert
        $this->assertIsArray($result);
        $this->assertCount(0, $result);
    }

    public function test_upload_media_uses_default_collection_when_null(): void
    {
        // Arrange
        $file = UploadedFile::fake()->image('test.jpg');

        // Act
        $media = $this->service->uploadMedia($file, null);

        // Assert
        $this->assertInstanceOf(Media::class, $media);
        $this->assertEquals('uploads', $media->collection_name);
    }

    public function test_upload_media_creates_media_holder(): void
    {
        // Arrange
        $file = UploadedFile::fake()->image('test.jpg');

        // Act
        $media = $this->service->uploadMedia($file, 'test');

        // Assert
        $this->assertDatabaseHas('media_holders', [
            'name' => 'test.jpg',
        ]);
    }

    public function test_get_paginated_media_includes_all_required_fields(): void
    {
        // Arrange
        $mediaHolder = MediaHolder::create(['name' => 'Test Holder', 'description' => 'Test']);
        $file = UploadedFile::fake()->image('test.jpg');
        $mediaHolder->addMedia($file)->toMediaCollection('test');

        // Act
        $result = $this->service->getPaginatedMedia(10);

        // Assert
        $this->assertNotEmpty($result->items());
        $firstItem = $result->items()[0];
        $this->assertArrayHasKey('id', $firstItem);
        $this->assertArrayHasKey('name', $firstItem);
        $this->assertArrayHasKey('file_name', $firstItem);
        $this->assertArrayHasKey('url', $firstItem);
        $this->assertArrayHasKey('type', $firstItem);
        $this->assertArrayHasKey('size', $firstItem);
        $this->assertArrayHasKey('formatted_size', $firstItem);
        $this->assertArrayHasKey('mime_type', $firstItem);
        $this->assertArrayHasKey('collection_name', $firstItem);
        $this->assertArrayHasKey('created_at', $firstItem);
        $this->assertArrayHasKey('model_type', $firstItem);
        $this->assertArrayHasKey('model_id', $firstItem);
    }

    public function test_get_media_by_collection_includes_required_fields(): void
    {
        // Arrange
        $mediaHolder = MediaHolder::create(['name' => 'Test Holder', 'description' => 'Test']);
        $file = UploadedFile::fake()->image('test.jpg');
        $mediaHolder->addMedia($file)->toMediaCollection('test');

        // Act
        $result = $this->service->getMediaByCollection('test');

        // Assert
        $this->assertNotEmpty($result);
        $firstItem = $result[0];
        $this->assertArrayHasKey('id', $firstItem);
        $this->assertArrayHasKey('name', $firstItem);
        $this->assertArrayHasKey('url', $firstItem);
        $this->assertArrayHasKey('type', $firstItem);
        $this->assertArrayHasKey('size', $firstItem);
        $this->assertArrayHasKey('created_at', $firstItem);
    }

    public function test_get_media_type_returns_image_for_image_mime_types(): void
    {
        // Arrange
        $mediaHolder = MediaHolder::create(['name' => 'Test Holder', 'description' => 'Test']);
        $file = UploadedFile::fake()->image('test.jpg');
        $media = $mediaHolder->addMedia($file)->toMediaCollection('test');

        // Act
        $result = $this->service->getPaginatedMedia(10);

        // Assert
        $firstItem = $result->items()[0];
        $this->assertEquals('image', $firstItem['type']);
    }

    public function test_get_media_type_returns_document_for_pdf(): void
    {
        // Arrange
        $mediaHolder = MediaHolder::create(['name' => 'Test Holder', 'description' => 'Test']);
        $file = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');
        $media = $mediaHolder->addMedia($file)->toMediaCollection('test');

        // Act
        $result = $this->service->getPaginatedMedia(10);

        // Assert
        $firstItem = $result->items()[0];
        $this->assertEquals('document', $firstItem['type']);
    }

    public function test_paginated_media_respects_per_page_parameter(): void
    {
        // Arrange
        $mediaHolder = MediaHolder::create(['name' => 'Test Holder', 'description' => 'Test']);
        for ($i = 0; $i < 25; $i++) {
            $file = UploadedFile::fake()->image("test{$i}.jpg");
            $mediaHolder->addMedia($file)->toMediaCollection('test');
        }

        // Act
        $result = $this->service->getPaginatedMedia(10);

        // Assert
        $this->assertEquals(10, $result->perPage());
        $this->assertEquals(25, $result->total());
    }

    public function test_paginated_media_orders_by_latest(): void
    {
        // Arrange
        $mediaHolder = MediaHolder::create(['name' => 'Test Holder', 'description' => 'Test']);
        $file1 = UploadedFile::fake()->image('first.jpg');
        $media1 = $mediaHolder->addMedia($file1)->toMediaCollection('test');

        sleep(1); // Ensure different timestamps

        $file2 = UploadedFile::fake()->image('second.jpg');
        $media2 = $mediaHolder->addMedia($file2)->toMediaCollection('test');

        // Act
        $result = $this->service->getPaginatedMedia(10);

        // Assert
        $items = $result->items();
        $this->assertCount(2, $items);
        $this->assertEquals($media2->id, $items[0]['id']);
        $this->assertEquals($media1->id, $items[1]['id']);
    }

    public function test_get_media_by_collection_orders_by_latest(): void
    {
        // Arrange
        $mediaHolder = MediaHolder::create(['name' => 'Test Holder', 'description' => 'Test']);
        $file1 = UploadedFile::fake()->image('first.jpg');
        $media1 = $mediaHolder->addMedia($file1)->toMediaCollection('test');

        sleep(1); // Ensure different timestamps

        $file2 = UploadedFile::fake()->image('second.jpg');
        $media2 = $mediaHolder->addMedia($file2)->toMediaCollection('test');

        // Act
        $result = $this->service->getMediaByCollection('test');

        // Assert
        $this->assertCount(2, $result);
        $this->assertEquals($media2->id, $result[0]['id']);
        $this->assertEquals($media1->id, $result[1]['id']);
    }

    public function test_upload_media_handles_different_file_types(): void
    {
        // Arrange
        $imageFile = UploadedFile::fake()->image('image.jpg');
        $pngFile = UploadedFile::fake()->image('image.png');

        // Act
        $jpgMedia = $this->service->uploadMedia($imageFile, 'uploads');
        $pngMedia = $this->service->uploadMedia($pngFile, 'uploads');

        // Assert
        $this->assertInstanceOf(Media::class, $jpgMedia);
        $this->assertInstanceOf(Media::class, $pngMedia);
        $this->assertEquals('uploads', $jpgMedia->collection_name);
        $this->assertEquals('uploads', $pngMedia->collection_name);
    }

    public function test_get_media_by_collection_filters_correctly(): void
    {
        // Arrange
        $mediaHolder = MediaHolder::create(['name' => 'Test Holder', 'description' => 'Test']);
        $file1 = UploadedFile::fake()->image('image1.jpg');
        $mediaHolder->addMedia($file1)->toMediaCollection('collection1');

        $file2 = UploadedFile::fake()->image('image2.jpg');
        $mediaHolder->addMedia($file2)->toMediaCollection('collection2');

        // Act
        $result1 = $this->service->getMediaByCollection('collection1');
        $result2 = $this->service->getMediaByCollection('collection2');

        // Assert
        $this->assertCount(1, $result1);
        $this->assertCount(1, $result2);
        $this->assertNotEquals($result1[0]['id'], $result2[0]['id']);
    }

    public function test_upload_media_logs_success(): void
    {
        // Arrange
        $file = UploadedFile::fake()->image('test.jpg');

        // Mock Log to capture success message
        Log::shouldReceive('info')->once()->withArgs(function ($message, $context) {
            return str_contains($message, 'Media uploaded successfully') &&
                   isset($context['media_id']) &&
                   isset($context['file_name']);
        });

        // Act
        $media = $this->service->uploadMedia($file, 'test');

        // Assert
        $this->assertInstanceOf(Media::class, $media);
    }

    public function test_delete_media_logs_success(): void
    {
        // Arrange
        $mediaHolder = MediaHolder::create(['name' => 'Test', 'description' => 'Test']);
        $file = UploadedFile::fake()->image('test.jpg');
        $media = $mediaHolder->addMedia($file)->toMediaCollection('test');

        // Mock Log to capture success message
        Log::shouldReceive('info')->once()->withArgs(function ($message, $context) use ($media) {
            return str_contains($message, 'Media deleted successfully') &&
                   isset($context['media_id']) &&
                   $context['media_id'] === $media->id;
        });

        // Act
        $result = $this->service->deleteMedia($media->id);

        // Assert
        $this->assertTrue($result);
    }
}
