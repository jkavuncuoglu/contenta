<?php

declare(strict_types=1);

namespace App\Domains\SocialMedia\Services\PlatformAdapters;

use App\Domains\SocialMedia\Models\SocialPost;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Pinterest Adapter using Pinterest API v5.
 *
 * Requires:
 * - Pinterest App with OAuth credentials
 * - Access token with pins:read, pins:write, boards:read permissions
 * - Default board configured for posting
 */
class PinterestAdapter implements SocialPlatformInterface
{
    protected string $apiBaseUrl = 'https://api.pinterest.com/v5';

    /**
     * Publish pin to Pinterest.
     */
    public function publishPost(SocialPost $post): array
    {
        $account = $post->socialAccount;
        $accessToken = $account->access_token;

        // Get default board ID from settings
        $settings = $account->platform_settings ?? [];
        $boardId = $settings['default_board_id'] ?? null;

        if (!$boardId) {
            throw new \Exception('Default board ID not configured');
        }

        // Pinterest requires at least one image
        if (empty($post->media_urls)) {
            throw new \Exception('Pinterest pins require at least one image');
        }

        try {
            $pinData = [
                'board_id' => $boardId,
                'title' => mb_substr($post->content, 0, 100), // First 100 chars as title
                'description' => $post->content,
                'link' => $post->link_url,
                'media_source' => [
                    'source_type' => 'image_url',
                    'url' => $post->media_urls[0],
                ],
            ];

            $response = Http::withToken($accessToken)
                ->post("{$this->apiBaseUrl}/pins", $pinData);

            if ($response->failed()) {
                throw new \Exception('Failed to create Pinterest pin: '.$response->body());
            }

            $data = $response->json();

            Log::info('Pinterest pin created', [
                'pin_id' => $data['id'],
                'board_id' => $boardId,
            ]);

            return [
                'id' => $data['id'],
                'permalink' => "https://www.pinterest.com/pin/{$data['id']}/",
            ];
        } catch (\Exception $e) {
            Log::error('Pinterest publish failed', [
                'error' => $e->getMessage(),
                'post_id' => $post->id,
            ]);

            throw $e;
        }
    }

    /**
     * Delete pin from Pinterest.
     */
    public function deletePost(string $platformPostId): bool
    {
        try {
            $response = Http::withToken(config('services.pinterest.access_token'))
                ->delete("{$this->apiBaseUrl}/pins/{$platformPostId}");

            if ($response->successful()) {
                Log::info('Pinterest pin deleted', ['pin_id' => $platformPostId]);

                return true;
            }

            return false;
        } catch (\Exception $e) {
            Log::error('Pinterest delete failed', [
                'pin_id' => $platformPostId,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Get analytics for Pinterest pin.
     */
    public function getPostAnalytics(string $platformPostId): array
    {
        // Placeholder for analytics implementation
        // Requires additional API calls to Pinterest Analytics API
        return [
            'impressions' => 0,
            'saves' => 0,
            'clicks' => 0,
            'engagement_rate' => 0,
        ];
    }

    /**
     * Get OAuth authorization URL.
     */
    public function getAuthorizationUrl(string $state, string $redirectUri): string
    {
        $clientId = config('services.pinterest.client_id');

        $params = http_build_query([
            'client_id' => $clientId,
            'redirect_uri' => $redirectUri,
            'state' => $state,
            'scope' => 'pins:read,pins:write,boards:read',
            'response_type' => 'code',
        ]);

        return "https://www.pinterest.com/oauth/?{$params}";
    }

    /**
     * Exchange authorization code for access token.
     */
    public function exchangeCodeForToken(string $code, string $redirectUri): array
    {
        $clientId = config('services.pinterest.client_id');
        $clientSecret = config('services.pinterest.client_secret');

        $response = Http::asForm()->post('https://api.pinterest.com/v5/oauth/token', [
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => $redirectUri,
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
        ]);

        if ($response->failed()) {
            throw new \Exception('Failed to exchange code for token: '.$response->body());
        }

        $data = $response->json();

        // Get user info
        $userResponse = Http::withToken($data['access_token'])
            ->get('https://api.pinterest.com/v5/user_account');

        $userData = $userResponse->json();

        return [
            'access_token' => $data['access_token'],
            'refresh_token' => $data['refresh_token'],
            'expires_in' => $data['expires_in'],
            'account_id' => $userData['id'] ?? 'pinterest_user',
            'username' => $userData['username'] ?? 'unknown',
        ];
    }

    /**
     * Refresh access token.
     */
    public function refreshAccessToken(string $refreshToken): array
    {
        $clientId = config('services.pinterest.client_id');
        $clientSecret = config('services.pinterest.client_secret');

        $response = Http::asForm()->post('https://api.pinterest.com/v5/oauth/token', [
            'grant_type' => 'refresh_token',
            'refresh_token' => $refreshToken,
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
        ]);

        if ($response->failed()) {
            throw new \Exception('Failed to refresh Pinterest token: '.$response->body());
        }

        $data = $response->json();

        return [
            'access_token' => $data['access_token'],
            'refresh_token' => $data['refresh_token'],
            'expires_in' => $data['expires_in'],
        ];
    }

    public function getCharacterLimit(): int
    {
        return 500;
    }

    public function getMediaLimit(): int
    {
        return 5; // Pinterest supports up to 5 images in Idea Pins
    }

    public function validateContent(string $content, array $mediaUrls = []): array
    {
        $errors = [];

        if (empty($mediaUrls)) {
            $errors[] = 'Pinterest pins require at least one image';
        }

        if (count($mediaUrls) > $this->getMediaLimit()) {
            $errors[] = "Pinterest supports up to {$this->getMediaLimit()} images";
        }

        if (mb_strlen($content) > $this->getCharacterLimit()) {
            $errors[] = "Description exceeds {$this->getCharacterLimit()} character limit";
        }

        // Validate image URLs
        foreach ($mediaUrls as $url) {
            if (!filter_var($url, FILTER_VALIDATE_URL)) {
                $errors[] = "Invalid image URL: {$url}";
            }
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
                'help' => 'The board where pins will be posted. Find this in your Pinterest account.',
            ],
        ];
    }
}
