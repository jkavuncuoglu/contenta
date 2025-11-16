<?php

declare(strict_types=1);

namespace Tests\Feature\Media;

use App\Domains\Media\Models\MediaHolder;
use App\Domains\Media\Services\MediaServiceContract;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MediaControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        Storage::fake('public');
    }

    public function test_index_displays_paginated_media(): void
    {
        // Arrange
        $mediaHolder = MediaHolder::create(['name' => 'Test Holder', 'description' => 'Test']);
        $file = UploadedFile::fake()->image('test.jpg');
        $mediaHolder->addMedia($file)->toMediaCollection('test');

        // Act
        $response = $this->actingAs($this->user)->get(route('admin.media.index'));

        // Assert
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('admin/Media')
            ->has('media')
            ->has('pagination')
        );
    }

    public function test_index_respects_per_page_parameter(): void
    {
        // Arrange
        $mediaHolder = MediaHolder::create(['name' => 'Test Holder', 'description' => 'Test']);
        for ($i = 0; $i < 30; $i++) {
            $file = UploadedFile::fake()->image("test{$i}.jpg");
            $mediaHolder->addMedia($file)->toMediaCollection('test');
        }

        // Act
        $response = $this->actingAs($this->user)->get(route('admin.media.index', ['per_page' => 10]));

        // Assert
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->where('pagination.per_page', 10)
            ->where('pagination.total', 30)
        );
    }

    public function test_store_uploads_media_file_successfully(): void
    {
        // Arrange
        $file = UploadedFile::fake()->image('upload.jpg');

        // Act
        $response = $this->actingAs($this->user)->post(route('admin.media.store'), [
            'file' => $file,
            'collection' => 'test-collection',
        ]);

        // Assert
        $response->assertRedirect(route('admin.media.index'));
        $response->assertSessionHas('success', 'Media uploaded successfully');
        $this->assertDatabaseHas('media', [
            'collection_name' => 'test-collection',
        ]);
    }

    public function test_store_uses_default_collection_when_not_provided(): void
    {
        // Arrange
        $file = UploadedFile::fake()->image('upload.jpg');

        // Act
        $response = $this->actingAs($this->user)->post(route('admin.media.store'), [
            'file' => $file,
        ]);

        // Assert
        $response->assertRedirect(route('admin.media.index'));
        $this->assertDatabaseHas('media', [
            'collection_name' => 'uploads',
        ]);
    }

    public function test_store_validates_required_file(): void
    {
        // Act
        $response = $this->actingAs($this->user)->post(route('admin.media.store'), [
            'collection' => 'test',
        ]);

        // Assert
        $response->assertSessionHasErrors(['file']);
    }

    public function test_store_validates_file_size(): void
    {
        // Arrange
        $file = UploadedFile::fake()->create('large.pdf', 11000); // 11MB

        // Act
        $response = $this->actingAs($this->user)->post(route('admin.media.store'), [
            'file' => $file,
        ]);

        // Assert
        $response->assertSessionHasErrors(['file']);
    }

    public function test_store_validates_collection_is_string(): void
    {
        // Arrange
        $file = UploadedFile::fake()->image('test.jpg');

        // Act
        $response = $this->actingAs($this->user)->post(route('admin.media.store'), [
            'file' => $file,
            'collection' => 123, // Invalid: number instead of string
        ]);

        // Assert
        $response->assertSessionHasErrors(['collection']);
    }

    public function test_show_returns_media_details(): void
    {
        // Arrange
        $mediaHolder = MediaHolder::create(['name' => 'Test Holder', 'description' => 'Test']);
        $file = UploadedFile::fake()->image('test.jpg');
        $media = $mediaHolder->addMedia($file)->toMediaCollection('test');

        // Act
        $response = $this->actingAs($this->user)->get(route('admin.media.show', $media->id));

        // Assert
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'media' => [
                'id' => $media->id,
                'name' => $media->name,
                'file_name' => $media->file_name,
                'collection_name' => 'test',
            ],
        ]);
    }

    public function test_show_returns_404_for_non_existent_media(): void
    {
        // Act
        $response = $this->actingAs($this->user)->get(route('admin.media.show', 99999));

        // Assert
        $response->assertStatus(404);
        $response->assertJson([
            'success' => false,
            'message' => 'Media not found',
        ]);
    }

    public function test_show_includes_all_required_fields(): void
    {
        // Arrange
        $mediaHolder = MediaHolder::create(['name' => 'Test Holder', 'description' => 'Test']);
        $file = UploadedFile::fake()->image('test.jpg');
        $media = $mediaHolder->addMedia($file)->toMediaCollection('test');

        // Act
        $response = $this->actingAs($this->user)->get(route('admin.media.show', $media->id));

        // Assert
        $response->assertJsonStructure([
            'success',
            'media' => [
                'id',
                'name',
                'file_name',
                'url',
                'type',
                'size',
                'formatted_size',
                'mime_type',
                'collection_name',
                'created_at',
            ],
        ]);
    }

    public function test_show_returns_image_type_for_image_files(): void
    {
        // Arrange
        $mediaHolder = MediaHolder::create(['name' => 'Test Holder', 'description' => 'Test']);
        $file = UploadedFile::fake()->image('test.jpg');
        $media = $mediaHolder->addMedia($file)->toMediaCollection('test');

        // Act
        $response = $this->actingAs($this->user)->get(route('admin.media.show', $media->id));

        // Assert
        $response->assertJson([
            'media' => [
                'type' => 'image',
            ],
        ]);
    }

    public function test_show_returns_document_type_for_pdf_files(): void
    {
        // Arrange
        $mediaHolder = MediaHolder::create(['name' => 'Test Holder', 'description' => 'Test']);
        $file = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');
        $media = $mediaHolder->addMedia($file)->toMediaCollection('test');

        // Act
        $response = $this->actingAs($this->user)->get(route('admin.media.show', $media->id));

        // Assert
        $response->assertJson([
            'media' => [
                'type' => 'document',
            ],
        ]);
    }

    public function test_destroy_deletes_media_successfully(): void
    {
        // Arrange
        $mediaHolder = MediaHolder::create(['name' => 'Test Holder', 'description' => 'Test']);
        $file = UploadedFile::fake()->image('delete.jpg');
        $media = $mediaHolder->addMedia($file)->toMediaCollection('test');

        // Act
        $response = $this->actingAs($this->user)->delete(route('admin.media.destroy', $media->id));

        // Assert
        $response->assertRedirect(route('admin.media.index'));
        $response->assertSessionHas('success', 'Media deleted successfully');
        $this->assertDatabaseMissing('media', ['id' => $media->id]);
    }

    public function test_destroy_returns_error_for_non_existent_media(): void
    {
        // Act
        $response = $this->actingAs($this->user)->delete(route('admin.media.destroy', 99999));

        // Assert
        $response->assertRedirect(route('admin.media.index'));
        $response->assertSessionHas('error', 'Media not found or could not be deleted');
    }

    public function test_collection_returns_media_by_collection(): void
    {
        // Arrange
        $mediaHolder = MediaHolder::create(['name' => 'Test Holder', 'description' => 'Test']);
        $file1 = UploadedFile::fake()->image('image1.jpg');
        $mediaHolder->addMedia($file1)->toMediaCollection('collection1');

        $file2 = UploadedFile::fake()->image('image2.jpg');
        $mediaHolder->addMedia($file2)->toMediaCollection('collection2');

        // Act
        $response = $this->actingAs($this->user)->get(route('admin.media.collection', 'collection1'));

        // Assert
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);
        $response->assertJsonCount(1, 'media');
    }

    public function test_collection_returns_empty_array_for_non_existent_collection(): void
    {
        // Act
        $response = $this->actingAs($this->user)->get(route('admin.media.collection', 'non-existent'));

        // Assert
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'media' => [],
        ]);
    }

    public function test_collection_filters_correctly_by_collection_name(): void
    {
        // Arrange
        $mediaHolder = MediaHolder::create(['name' => 'Test Holder', 'description' => 'Test']);
        $file1 = UploadedFile::fake()->image('image1.jpg');
        $media1 = $mediaHolder->addMedia($file1)->toMediaCollection('collection1');

        $file2 = UploadedFile::fake()->image('image2.jpg');
        $mediaHolder->addMedia($file2)->toMediaCollection('collection2');

        // Act
        $response = $this->actingAs($this->user)->get(route('admin.media.collection', 'collection1'));

        // Assert
        $data = $response->json('media');
        $this->assertEquals($media1->id, $data[0]['id']);
    }

    public function test_guest_cannot_access_media_index(): void
    {
        // Act
        $response = $this->get(route('admin.media.index'));

        // Assert
        $response->assertRedirect(route('login'));
    }

    public function test_guest_cannot_upload_media(): void
    {
        // Arrange
        $file = UploadedFile::fake()->image('test.jpg');

        // Act
        $response = $this->post(route('admin.media.store'), [
            'file' => $file,
        ]);

        // Assert
        $response->assertRedirect(route('login'));
    }

    public function test_guest_cannot_delete_media(): void
    {
        // Arrange
        $mediaHolder = MediaHolder::create(['name' => 'Test Holder', 'description' => 'Test']);
        $file = UploadedFile::fake()->image('test.jpg');
        $media = $mediaHolder->addMedia($file)->toMediaCollection('test');

        // Act
        $response = $this->delete(route('admin.media.destroy', $media->id));

        // Assert
        $response->assertRedirect(route('login'));
    }

    public function test_store_handles_upload_exception_gracefully(): void
    {
        // Arrange
        $file = UploadedFile::fake()->image('test.jpg');
        $service = $this->mock(MediaServiceContract::class);
        $service->shouldReceive('uploadMedia')
            ->andThrow(new \Exception('Upload failed'));

        // Act
        $response = $this->actingAs($this->user)->post(route('admin.media.store'), [
            'file' => $file,
        ]);

        // Assert
        $response->assertRedirect(route('admin.media.index'));
        $response->assertSessionHas('error');
    }
}
