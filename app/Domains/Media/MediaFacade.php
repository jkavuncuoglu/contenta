<?php

declare(strict_types=1);

namespace App\Domains\Media;

use App\Domains\Media\Services\MediaServiceContract;
use Illuminate\Support\Facades\Facade;

/**
 * @method static \Illuminate\Pagination\LengthAwarePaginator getPaginatedMedia(int $perPage = 20)
 * @method static \Spatie\MediaLibrary\MediaCollections\Models\Media uploadMedia(\Illuminate\Http\UploadedFile $file, ?string $collection = null)
 * @method static bool deleteMedia(int $mediaId)
 * @method static \Spatie\MediaLibrary\MediaCollections\Models\Media|null getMediaById(int $mediaId)
 * @method static array<int, array<string, mixed>> getMediaByCollection(string $collection)
 *
 * @see \App\Domains\Media\Services\MediaService
 */
class MediaFacade extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return MediaServiceContract::class;
    }
}