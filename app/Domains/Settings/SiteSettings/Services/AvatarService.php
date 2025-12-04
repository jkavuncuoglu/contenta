<?php

namespace App\Domains\Settings\SiteSettings\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AvatarService
{
    /**
     * Process and store an avatar upload.
     *
     * @return string The public URL of the stored avatar
     */
    public function processUpload(UploadedFile $file): string
    {
        // Generate a unique filename
        $filename = Str::uuid().'.'.$file->getClientOriginalExtension();

        // Store the file
        $path = $file->storeAs('avatars', $filename, 'public');

        // Ensure path is not false
        if ($path === false) {
            throw new \RuntimeException('Failed to store avatar file');
        }

        // Return the public URL
        return Storage::disk('public')->url($path);
    }

    /**
     * Delete an avatar file if it's not a URL.
     */
    public function deleteIfLocal(?string $avatar): void
    {
        if (! $avatar || filter_var($avatar, FILTER_VALIDATE_URL)) {
            return;
        }

        // Extract path from URL if needed
        $path = str_replace(Storage::disk('public')->url(''), '', $avatar);

        Storage::disk('public')->delete($path);
    }

    /**
     * Validate if a URL is from a known avatar service.
     */
    public function isValidAvatarUrl(string $url): bool
    {
        if (! filter_var($url, FILTER_VALIDATE_URL)) {
            return false;
        }

        $allowedDomains = [
            'gravatar.com',
            'www.gravatar.com',
            'secure.gravatar.com',
            'ui-avatars.com',
            'api.dicebear.com',
            'avatars.githubusercontent.com',
            'imgur.com',
            'i.imgur.com',
        ];

        $host = parse_url($url, PHP_URL_HOST);

        // Allow any avatar service or public image
        return $host !== false && (
            in_array($host, $allowedDomains) ||
            preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $url)
        );
    }
}
