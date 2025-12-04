<?php

declare(strict_types=1);

namespace App\Domains\SocialMedia\Services\PlatformAdapters;

use App\Domains\SocialMedia\Models\SocialPost;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Instagram Adapter (via Facebook Graph API).
 *
 * Instagram Business API uses Facebook Graph API for posting.
 * Requires:
 * - Facebook App with Instagram permissions
 * - Instagram Business Account linked to Facebook Page
 * - Access token with instagram_basic, instagram_content_publish permissions
 */
class InstagramAdapter implements SocialPlatformInterface
{
    protected string $apiBaseUrl = 'https://graph.facebook.com/v18.0';

    /**
     * Publish post to Instagram.
     *
     * Process:
     * 1. Create media container
     * 2. Publish media container
     */
    public function publishPost(SocialPost $post): array
    {
        $account = $post->socialAccount;
        $accessToken = $account->access_token;

        // Get Instagram Business Account ID from settings
        $settings = $account->platform_settings ?? [];
        $igUserId = $settings['instagram_business_account_id'] ?? null;

        if (!$igUserId) {
            throw new \Exception('Instagram Business Account ID not configured');
        }

        // Instagram requires at least one media
        if (empty($post->media_urls)) {
            throw new \Exception('Instagram posts require at least one image or video');
        }

        try {
            // Step 1: Create media container
            $containerData = [
                'image_url' => $post->media_urls[0], // Instagram API v18.0 supports single image
                'caption' => $post->content,
                'access_token' => $accessToken,
            ];

            $containerResponse = Http::post("{$this->apiBaseUrl}/{$igUserId}/media", $containerData);

            if ($containerResponse->failed()) {
                throw new \Exception('Failed to create Instagram media container: '.$containerResponse->body());
            }

            $containerId = $containerResponse->json('id');

            // Step 2: Publish media container
            $publishResponse = Http::post("{$this->apiBaseUrl}/{$igUserId}/media_publish", [
                'creation_id' => $containerId,
                'access_token' => $accessToken,
            ]);

            if ($publishResponse->failed()) {
                throw new \Exception('Failed to publish Instagram post: '.$publishResponse->body());
            }

            $mediaId = $publishResponse->json('id');

            Log::info('Instagram post published', [
                'media_id' => $mediaId,
                'ig_user_id' => $igUserId,
            ]);

            return [
                'id' => $mediaId,
                'permalink' => "https://www.instagram.com/p/{$mediaId}/",
            ];
        } catch (\Exception $e) {
            Log::error('Instagram publish failed', [
                'error' => $e->getMessage(),
                'post_id' => $post->id,
            ]);

            throw $e;
        }
    }

    /**
     * Delete post from Instagram.
     */
    public function deletePost(string $platformPostId): bool
    {
        // Instagram Graph API does not support deleting posts programmatically
        // This is a platform limitation
        Log::warning('Instagram does not support post deletion via API', [
            'post_id' => $platformPostId,
        ]);

        return false;
    }

    /**
     * Get analytics for Instagram post.
     */
    public function getPostAnalytics(string $platformPostId): array
    {
        // Placeholder for analytics implementation
        // Requires instagram_manage_insights permission
        return [
            'likes' => 0,
            'comments' => 0,
            'reach' => 0,
            'impressions' => 0,
            'saves' => 0,
            'engagement_rate' => 0,
        ];
    }

    /**
     * Get OAuth authorization URL.
     *
     * Instagram uses Facebook Login with Instagram permissions.
     */
    public function getAuthorizationUrl(string $state, string $redirectUri): string
    {
        $clientId = config('services.instagram.client_id');

        $params = http_build_query([
            'client_id' => $clientId,
            'redirect_uri' => $redirectUri,
            'state' => $state,
            'scope' => 'instagram_basic,instagram_content_publish,pages_show_list,pages_read_engagement',
            'response_type' => 'code',
        ]);

        return "https://www.facebook.com/v18.0/dialog/oauth?{$params}";
    }

    /**
     * Exchange authorization code for access token.
     */
    public function exchangeCodeForToken(string $code, string $redirectUri): array
    {
        $clientId = config('services.instagram.client_id');
        $clientSecret = config('services.instagram.client_secret');

        // Step 1: Get short-lived token from Facebook
        $response = Http::get('https://graph.facebook.com/v18.0/oauth/access_token', [
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'code' => $code,
            'redirect_uri' => $redirectUri,
        ]);

        if ($response->failed()) {
            throw new \Exception('Failed to exchange code for token: '.$response->body());
        }

        $data = $response->json();
        $shortLivedToken = $data['access_token'];

        // Step 2: Exchange short-lived token for long-lived token
        $longLivedResponse = Http::get('https://graph.facebook.com/v18.0/oauth/access_token', [
            'grant_type' => 'fb_exchange_token',
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'fb_exchange_token' => $shortLivedToken,
        ]);

        if ($longLivedResponse->failed()) {
            throw new \Exception('Failed to get long-lived token: '.$longLivedResponse->body());
        }

        $longLivedData = $longLivedResponse->json();

        // Step 3: Get Instagram Business Account ID
        // This requires the user to select their Facebook Page
        // For now, we'll return the token and let the user configure the IG account ID manually

        return [
            'access_token' => $longLivedData['access_token'],
            'refresh_token' => null, // Instagram uses long-lived tokens (60 days)
            'expires_in' => $longLivedData['expires_in'] ?? 5184000, // 60 days default
            'account_id' => 'instagram_user', // Placeholder - user must configure
            'username' => 'unknown', // Requires additional API call
        ];
    }

    /**
     * Refresh access token.
     *
     * Instagram long-lived tokens last 60 days and can be refreshed.
     */
    public function refreshAccessToken(string $refreshToken): array
    {
        // Instagram uses long-lived tokens that can be refreshed before expiry
        // The refreshToken parameter here is actually the current access token
        $clientId = config('services.instagram.client_id');
        $clientSecret = config('services.instagram.client_secret');

        $response = Http::get('https://graph.facebook.com/v18.0/oauth/access_token', [
            'grant_type' => 'fb_exchange_token',
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'fb_exchange_token' => $refreshToken,
        ]);

        if ($response->failed()) {
            throw new \Exception('Failed to refresh Instagram token: '.$response->body());
        }

        $data = $response->json();

        return [
            'access_token' => $data['access_token'],
            'expires_in' => $data['expires_in'] ?? 5184000,
        ];
    }

    public function getCharacterLimit(): int
    {
        return 2200;
    }

    public function getMediaLimit(): int
    {
        return 10; // Carousel posts support up to 10 items
    }

    public function validateContent(string $content, array $mediaUrls = []): array
    {
        $errors = [];

        if (empty($mediaUrls)) {
            $errors[] = 'Instagram posts require at least one image or video';
        }

        if (count($mediaUrls) > $this->getMediaLimit()) {
            $errors[] = "Instagram supports up to {$this->getMediaLimit()} media items";
        }

        if (mb_strlen($content) > $this->getCharacterLimit()) {
            $errors[] = "Caption exceeds {$this->getCharacterLimit()} character limit";
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
                'help' => 'Found in Facebook Business Manager. Required for posting.',
            ],
        ];
    }
}
