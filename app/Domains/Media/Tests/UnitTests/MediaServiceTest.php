<?php

declare(strict_types=1);

namespace App\Domains\Media\Tests\UnitTests;

use App\Domains\Media\Models\MediaHolder;
use App\Domains\Media\Services\MediaService;
use App\Domains\Media\Services\MediaServiceContract;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
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
}
