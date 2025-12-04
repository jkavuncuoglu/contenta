<?php

declare(strict_types=1);

namespace App\Domains\Media\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

interface MediaServiceContract
{
    /**
     * Get paginated media files
     */
    public function getPaginatedMedia(int $perPage = 20): LengthAwarePaginator;

    /**
     * Upload a media file
     */
    public function uploadMedia(UploadedFile $file, ?string $collection = null): Media;

    /**
     * Delete a media file
     */
    public function deleteMedia(int $mediaId): bool;

    /**
     * Get media by ID
     */
    public function getMediaById(int $mediaId): ?Media;

    /**
     * Get media by collection
     *
     * @return array<int, array<string, mixed>>
     */
    public function getMediaByCollection(string $collection): array;
}
