<?php

declare(strict_types=1);

namespace App\Domains\SocialMedia\Services\PlatformAdapters;

use App\Domains\SocialMedia\Models\SocialPost;

/**
 * TikTok Adapter.
 *
 * Note: This is a placeholder implementation.
 */
class TikTokAdapter implements SocialPlatformInterface
{
    public function publishPost(SocialPost $post): array
    {
        throw new \Exception('TikTok publishing not yet implemented');
    }

    public function deletePost(string $platformPostId): bool
    {
        throw new \Exception('TikTok delete not yet implemented');
    }

    public function getPostAnalytics(string $platformPostId): array
    {
        return [];
    }

    public function getAuthorizationUrl(string $state, string $redirectUri): string
    {
        throw new \Exception('TikTok OAuth not yet implemented');
    }

    public function exchangeCodeForToken(string $code, string $redirectUri): array
    {
        throw new \Exception('TikTok OAuth not yet implemented');
    }

    public function refreshAccessToken(string $refreshToken): array
    {
        throw new \Exception('TikTok token refresh not yet implemented');
    }

    public function getCharacterLimit(): int
    {
        return 2200;
    }

    public function getMediaLimit(): int
    {
        return 1; // TikTok is primarily video
    }

    public function validateContent(string $content, array $mediaUrls = []): array
    {
        $errors = [];

        if (empty($mediaUrls)) {
            $errors[] = 'TikTok posts require a video';
        }

        if (mb_strlen($content) > $this->getCharacterLimit()) {
            $errors[] = "Content exceeds {$this->getCharacterLimit()} character limit";
        }

        return $errors;
    }

    public function getSettingsSchema(): array
    {
        return [
            'default_privacy' => [
                'type' => 'select',
                'label' => 'Default Privacy',
                'options' => ['PUBLIC', 'FRIENDS', 'PRIVATE'],
                'default' => 'PUBLIC',
            ],
        ];
    }
}
