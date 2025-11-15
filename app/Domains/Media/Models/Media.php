<?php

declare(strict_types=1);

namespace App\Domains\Media\Models;

use Spatie\MediaLibrary\MediaCollections\Models\Media as BaseMedia;

class Media extends BaseMedia
{
    /**
     * Get the media type based on mime type
     */
    public function getTypeAttribute(): string
    {
        if (str_starts_with($this->mime_type, 'image/')) {
            return 'image';
        }

        if (str_starts_with($this->mime_type, 'video/')) {
            return 'video';
        }

        if (str_starts_with($this->mime_type, 'audio/')) {
            return 'audio';
        }

        return 'document';
    }

    /**
     * Get formatted file size
     */
    public function getFormattedSizeAttribute(): string
    {
        return $this->humanReadableSize;
    }

    /**
     * Get the URL for the media file
     */
    public function getUrlAttribute(): string
    {
        return $this->getUrl();
    }
}
