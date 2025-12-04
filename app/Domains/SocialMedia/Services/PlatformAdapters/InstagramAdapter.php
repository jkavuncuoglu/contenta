<?php

declare(strict_types=1);

namespace App\Domains\SocialMedia\Services\PlatformAdapters;

use App\Domains\SocialMedia\Models\SocialPost;

/**
 * Instagram Adapter (via Facebook Graph API).
 *
 * Note: This is a placeholder implementation.
 * Full implementation requires Facebook Business integration.
 */
class InstagramAdapter implements SocialPlatformInterface
{
    public function publishPost(SocialPost $post): array
    {
        throw new \Exception('Instagram publishing not yet implemented');
    }

    public function deletePost(string $platformPostId): bool
    {
        throw new \Exception('Instagram delete not yet implemented');
    }

    public function getPostAnalytics(string $platformPostId): array
    {
        return [];
    }

    public function getAuthorizationUrl(string $state, string $redirectUri): string
    {
        throw new \Exception('Instagram OAuth not yet implemented');
    }

    public function exchangeCodeForToken(string $code, string $redirectUri): array
    {
        throw new \Exception('Instagram OAuth not yet implemented');
    }

    public function refreshAccessToken(string $refreshToken): array
    {
        throw new \Exception('Instagram token refresh not yet implemented');
    }

    public function getCharacterLimit(): int
    {
        return 2200;
    }

    public function getMediaLimit(): int
    {
        return 10;
    }

    public function validateContent(string $content, array $mediaUrls = []): array
    {
        $errors = [];

        if (empty($mediaUrls)) {
            $errors[] = 'Instagram posts require at least one image or video';
        }

        if (mb_strlen($content) > $this->getCharacterLimit()) {
            $errors[] = "Content exceeds {$this->getCharacterLimit()} character limit";
        }

        return $errors;
    }

    public function getSettingsSchema(): array
    {
        return [
            'instagram_business_account_id' => [
                'type' => 'text',
                'label' => 'Instagram Business Account ID',
                'required' => true,
            ],
        ];
    }
}
