<?php

declare(strict_types=1);

namespace App\Domains\Media\Tests\UnitTests;

use App\Domains\Media\Models\MediaHolder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\HasMedia;
use Tests\TestCase;

class MediaHolderTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_media_holder_implements_has_media_interface(): void
    {
        // Arrange & Act
        $mediaHolder = new MediaHolder;

        // Assert
        $this->assertInstanceOf(HasMedia::class, $mediaHolder);
    }

    public function test_media_holder_has_fillable_attributes(): void
    {
        // Arrange & Act
        $mediaHolder = MediaHolder::create([
            'name' => 'Test Holder',
            'description' => 'Test Description',
        ]);

        // Assert
        $this->assertEquals('Test Holder', $mediaHolder->name);
        $this->assertEquals('Test Description', $mediaHolder->description);
    }

    public function test_media_holder_can_add_media_to_default_collection(): void
    {
        // Arrange
        $mediaHolder = MediaHolder::create(['name' => 'Test', 'description' => 'Test']);
        $file = UploadedFile::fake()->image('test.jpg');

        // Act
        $media = $mediaHolder->addMedia($file)->toMediaCollection('default');

        // Assert
        $this->assertNotNull($media);
        $this->assertEquals('default', $media->collection_name);
    }

    public function test_media_holder_can_add_media_to_uploads_collection(): void
    {
        // Arrange
        $mediaHolder = MediaHolder::create(['name' => 'Test', 'description' => 'Test']);
        $file = UploadedFile::fake()->image('upload.jpg');

        // Act
        $media = $mediaHolder->addMedia($file)->toMediaCollection('uploads');

        // Assert
        $this->assertNotNull($media);
        $this->assertEquals('uploads', $media->collection_name);
    }

    public function test_media_holder_images_collection_accepts_jpeg(): void
    {
        // Arrange
        $mediaHolder = MediaHolder::create(['name' => 'Test', 'description' => 'Test']);
        $file = UploadedFile::fake()->image('test.jpg');

        // Act
        $media = $mediaHolder->addMedia($file)->toMediaCollection('images');

        // Assert
        $this->assertNotNull($media);
        $this->assertEquals('images', $media->collection_name);
    }

    public function test_media_holder_images_collection_accepts_png(): void
    {
        // Arrange
        $mediaHolder = MediaHolder::create(['name' => 'Test', 'description' => 'Test']);
        $file = UploadedFile::fake()->image('test.png');

        // Act
        $media = $mediaHolder->addMedia($file)->toMediaCollection('images');

        // Assert
        $this->assertNotNull($media);
        $this->assertEquals('images', $media->collection_name);
    }

    public function test_media_holder_can_have_multiple_media_items(): void
    {
        // Arrange
        $mediaHolder = MediaHolder::create(['name' => 'Test', 'description' => 'Test']);
        $file1 = UploadedFile::fake()->image('test1.jpg');
        $file2 = UploadedFile::fake()->image('test2.jpg');

        // Act
        $mediaHolder->addMedia($file1)->toMediaCollection('default');
        $mediaHolder->addMedia($file2)->toMediaCollection('default');

        // Assert
        $this->assertCount(2, $mediaHolder->getMedia('default'));
    }

    public function test_media_holder_uses_correct_table_name(): void
    {
        // Arrange & Act
        $mediaHolder = new MediaHolder;

        // Assert
        $this->assertEquals('media_holders', $mediaHolder->getTable());
    }

    public function test_media_holder_can_get_all_media(): void
    {
        // Arrange
        $mediaHolder = MediaHolder::create(['name' => 'Test', 'description' => 'Test']);
        $file1 = UploadedFile::fake()->image('test1.jpg');
        $file2 = UploadedFile::fake()->image('test2.jpg');
        $file3 = UploadedFile::fake()->image('test3.jpg');

        // Act
        $mediaHolder->addMedia($file1)->toMediaCollection('uploads');
        $mediaHolder->addMedia($file2)->toMediaCollection('default');
        $mediaHolder->addMedia($file3)->toMediaCollection('images');

        // Assert
        $this->assertGreaterThanOrEqual(3, $mediaHolder->fresh()->media->count());
    }

    public function test_media_holder_can_filter_media_by_collection(): void
    {
        // Arrange
        $mediaHolder = MediaHolder::create(['name' => 'Test', 'description' => 'Test']);
        $file1 = UploadedFile::fake()->image('test1.jpg');
        $file2 = UploadedFile::fake()->image('test2.jpg');

        // Act
        $mediaHolder->addMedia($file1)->toMediaCollection('images');
        $mediaHolder->addMedia($file2)->toMediaCollection('uploads');

        // Assert
        $mediaHolder->refresh(); // Refresh the model
        $imagesMedia = $mediaHolder->getMedia('images');
        $this->assertCount(1, $imagesMedia);
        $this->assertEquals('images', $imagesMedia[0]->collection_name);
    }
}
