<?php

declare(strict_types=1);

namespace App\Domains\Media\Services;

use App\Domains\Media\Models\Media;
use App\Domains\Media\Models\MediaHolder;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Spatie\MediaLibrary\MediaCollections\Models\Media as SpatieMedia;

class MediaService implements MediaServiceContract
{
    /**
     * Get paginated media files
     */
    public function getPaginatedMedia(int $perPage = 20): LengthAwarePaginator
    {
        return SpatieMedia::query()
            ->latest()
            ->paginate($perPage)
            ->through(function ($media) {
                return [
                    'id' => $media->id,
                    'name' => $media->name,
                    'file_name' => $media->file_name,
                    'url' => $media->getUrl(),
                    'type' => $this->getMediaType($media->mime_type),
                    'size' => $media->size,
                    'formatted_size' => $media->humanReadableSize,
                    'mime_type' => $media->mime_type,
                    'collection_name' => $media->collection_name,
                    'created_at' => $media->created_at->format('M j, Y'),
                    'model_type' => $media->model_type,
                    'model_id' => $media->model_id,
                ];
            });
    }

    /**
     * Upload a media file
     */
    public function uploadMedia(UploadedFile $file, ?string $collection = 'default'): SpatieMedia
    {
        try {
            DB::beginTransaction();

            // Create a media holder to attach the file to
            $mediaHolder = MediaHolder::create([
                'name' => $file->getClientOriginalName(),
                'description' => 'Uploaded media file',
            ]);

            $media = $mediaHolder
                ->addMedia($file)
                ->toMediaCollection($collection ?? 'uploads');

            DB::commit();

            Log::info('Media uploaded successfully', [
                'media_id' => $media->id,
                'file_name' => $media->file_name,
            ]);

            return $media;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to upload media', [
                'error' => $e->getMessage(),
                'file_name' => $file->getClientOriginalName(),
            ]);
            throw $e;
        }
    }

    /**
     * Delete a media file
     */
    public function deleteMedia(int $mediaId): bool
    {
        try {
            $media = SpatieMedia::find($mediaId);

            if (! $media) {
                return false;
            }

            $media->delete();

            Log::info('Media deleted successfully', [
                'media_id' => $mediaId,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to delete media', [
                'media_id' => $mediaId,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Get media by ID
     */
    public function getMediaById(int $mediaId): ?SpatieMedia
    {
        return SpatieMedia::find($mediaId);
    }

    /**
     * Get media by collection
     *
     * @return array<int, array<string, mixed>>
     */
    public function getMediaByCollection(string $collection): array
    {
        return SpatieMedia::where('collection_name', $collection)
            ->latest()
            ->get()
            ->map(function ($media) {
                return [
                    'id' => $media->id,
                    'name' => $media->name,
                    'url' => $media->getUrl(),
                    'type' => $this->getMediaType($media->mime_type),
                    'size' => $media->size,
                    'created_at' => $media->created_at,
                ];
            })
            ->toArray();
    }

    /**
     * Get media type from mime type
     */
    private function getMediaType(string $mimeType): string
    {
        if (str_starts_with($mimeType, 'image/')) {
            return 'image';
        }

        if (str_starts_with($mimeType, 'video/')) {
            return 'video';
        }

        if (str_starts_with($mimeType, 'audio/')) {
            return 'audio';
        }

        return 'document';
    }
}
