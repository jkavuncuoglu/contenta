<?php

declare(strict_types=1);

namespace App\Domains\SocialMedia\Services\PlatformAdapters;

use App\Domains\SocialMedia\Models\SocialPost;

interface SocialPlatformInterface
{
    /**
     * Publish a post to the platform.
     *
     * @return array ['id' => platform_post_id, 'permalink' => url]
     */
    public function publishPost(SocialPost $post): array;

    /**
     * Delete a post from the platform.
     */
    public function deletePost(string $platformPostId): bool;

    /**
     * Get analytics for a post.
     */
    public function getPostAnalytics(string $platformPostId): array;

    /**
     * Get OAuth authorization URL.
     */
    public function getAuthorizationUrl(string $state, string $redirectUri): string;

    /**
     * Exchange authorization code for access token.
     */
    public function exchangeCodeForToken(string $code, string $redirectUri): array;

    /**
     * Refresh an expired access token.
     */
    public function refreshAccessToken(string $refreshToken): array;

    /**
     * Get character limit for this platform.
     */
    public function getCharacterLimit(): int;

    /**
     * Get media limit for this platform.
     */
    public function getMediaLimit(): int;

    /**
     * Validate post content before publishing.
     *
     * @return array Array of validation errors (empty if valid)
     */
    public function validateContent(string $content, array $mediaUrls = []): array;

    /**
     * Get platform-specific settings schema.
     */
    public function getSettingsSchema(): array;
}
