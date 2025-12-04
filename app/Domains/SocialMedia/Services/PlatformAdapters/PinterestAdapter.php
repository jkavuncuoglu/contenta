<?php

declare(strict_types=1);

namespace App\Domains\SocialMedia\Services\PlatformAdapters;

use App\Domains\SocialMedia\Models\SocialPost;

/**
 * Pinterest Adapter.
 *
 * Note: This is a placeholder implementation.
 */
class PinterestAdapter implements SocialPlatformInterface
{
    public function publishPost(SocialPost $post): array
    {
        throw new \Exception('Pinterest publishing not yet implemented');
    }

    public function deletePost(string $platformPostId): bool
    {
        throw new \Exception('Pinterest delete not yet implemented');
    }

    public function getPostAnalytics(string $platformPostId): array
    {
        return [];
    }

    public function getAuthorizationUrl(string $state, string $redirectUri): string
    {
        throw new \Exception('Pinterest OAuth not yet implemented');
    }

    public function exchangeCodeForToken(string $code, string $redirectUri): array
    {
        throw new \Exception('Pinterest OAuth not yet implemented');
    }

    public function refreshAccessToken(string $refreshToken): array
    {
        throw new \Exception('Pinterest token refresh not yet implemented');
    }

    public function getCharacterLimit(): int
    {
        return 500;
    }

    public function getMediaLimit(): int
    {
        return 5;
    }

    public function validateContent(string $content, array $mediaUrls = []): array
    {
        $errors = [];

        if (empty($mediaUrls)) {
            $errors[] = 'Pinterest pins require at least one image';
        }

        if (mb_strlen($content) > $this->getCharacterLimit()) {
            $errors[] = "Content exceeds {$this->getCharacterLimit()} character limit";
        }

        return $errors;
    }

    public function getSettingsSchema(): array
    {
        return [
            'default_board_id' => [
                'type' => 'text',
                'label' => 'Default Board ID',
                'required' => true,
            ],
        ];
    }
}
