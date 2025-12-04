<?php

declare(strict_types=1);

namespace App\Domains\SocialMedia\Services\PlatformAdapters;

use App\Domains\SocialMedia\Models\SocialPost;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * TikTok Adapter using TikTok Content Posting API.
 *
 * Requires:
 * - TikTok For Developers app with OAuth credentials
 * - Access token with video.upload, video.publish permissions
 * - Approved for Content Posting API (business verification required)
 *
 * Note: TikTok's API is primarily for video content.
 * Text-only posts are not supported through the API.
 */
class TikTokAdapter implements SocialPlatformInterface
{
    protected string $apiBaseUrl = 'https://open.tiktokapis.com/v2';

    /**
     * Publish video to TikTok.
     *
     * Process:
     * 1. Initialize video upload
     * 2. Upload video chunks
     * 3. Publish video
     */
    public function publishPost(SocialPost $post): array
    {
        $account = $post->socialAccount;
        $accessToken = $account->access_token;

        // TikTok requires a video
        if (empty($post->media_urls)) {
            throw new \Exception('TikTok posts require a video');
        }

        $settings = $account->platform_settings ?? [];
        $privacy = $settings['default_privacy'] ?? 'PUBLIC_TO_EVERYONE';

        try {
            // Step 1: Initialize upload
            $initResponse = Http::withToken($accessToken)
                ->post("{$this->apiBaseUrl}/post/publish/video/init/", [
                    'post_info' => [
                        'title' => mb_substr($post->content, 0, 150),
                        'privacy_level' => $privacy,
                        'disable_duet' => false,
                        'disable_comment' => false,
                        'disable_stitch' => false,
                        'video_cover_timestamp_ms' => 1000,
                    ],
                    'source_info' => [
                        'source' => 'PULL_FROM_URL',
                        'video_url' => $post->media_urls[0],
                    ],
                ]);

            if ($initResponse->failed()) {
                throw new \Exception('Failed to initialize TikTok upload: '.$initResponse->body());
            }

            $data = $initResponse->json('data');
            $publishId = $data['publish_id'];

            Log::info('TikTok video upload initialized', [
                'publish_id' => $publishId,
            ]);

            // TikTok processes the video asynchronously
            // The video will be published once processing is complete
            // We return the publish_id as the platform post ID

            return [
                'id' => $publishId,
                'permalink' => "https://www.tiktok.com/@{$account->platform_username}/video/{$publishId}",
            ];
        } catch (\Exception $e) {
            Log::error('TikTok publish failed', [
                'error' => $e->getMessage(),
                'post_id' => $post->id,
            ]);

            throw $e;
        }
    }

    /**
     * Delete video from TikTok.
     *
     * Note: TikTok API v2 does not support video deletion.
     * Users must delete manually from the TikTok app.
     */
    public function deletePost(string $platformPostId): bool
    {
        Log::warning('TikTok does not support video deletion via API', [
            'post_id' => $platformPostId,
        ]);

        return false;
    }

    /**
     * Get analytics for TikTok video.
     */
    public function getPostAnalytics(string $platformPostId): array
    {
        // Placeholder for analytics implementation
        // Requires TikTok Research API or Creator API access
        return [
            'views' => 0,
            'likes' => 0,
            'comments' => 0,
            'shares' => 0,
            'engagement_rate' => 0,
        ];
    }

    /**
     * Get OAuth authorization URL.
     */
    public function getAuthorizationUrl(string $state, string $redirectUri): string
    {
        $clientKey = config('services.tiktok.client_key');

        $params = http_build_query([
            'client_key' => $clientKey,
            'redirect_uri' => $redirectUri,
            'state' => $state,
            'scope' => 'user.info.basic,video.upload,video.publish',
            'response_type' => 'code',
        ]);

        return "https://www.tiktok.com/v2/auth/authorize/?{$params}";
    }

    /**
     * Exchange authorization code for access token.
     */
    public function exchangeCodeForToken(string $code, string $redirectUri): array
    {
        $clientKey = config('services.tiktok.client_key');
        $clientSecret = config('services.tiktok.client_secret');

        $response = Http::asForm()->post('https://open.tiktokapis.com/v2/oauth/token/', [
            'client_key' => $clientKey,
            'client_secret' => $clientSecret,
            'code' => $code,
            'grant_type' => 'authorization_code',
            'redirect_uri' => $redirectUri,
        ]);

        if ($response->failed()) {
            throw new \Exception('Failed to exchange code for token: '.$response->body());
        }

        $data = $response->json();

        // Get user info
        $userResponse = Http::withToken($data['access_token'])
            ->get('https://open.tiktokapis.com/v2/user/info/', [
                'fields' => 'open_id,union_id,avatar_url,display_name',
            ]);

        $userData = $userResponse->json('data')['user'] ?? [];

        return [
            'access_token' => $data['access_token'],
            'refresh_token' => $data['refresh_token'],
            'expires_in' => $data['expires_in'],
            'account_id' => $userData['open_id'] ?? 'tiktok_user',
            'username' => $userData['display_name'] ?? 'unknown',
        ];
    }

    /**
     * Refresh access token.
     */
    public function refreshAccessToken(string $refreshToken): array
    {
        $clientKey = config('services.tiktok.client_key');
        $clientSecret = config('services.tiktok.client_secret');

        $response = Http::asForm()->post('https://open.tiktokapis.com/v2/oauth/token/', [
            'client_key' => $clientKey,
            'client_secret' => $clientSecret,
            'grant_type' => 'refresh_token',
            'refresh_token' => $refreshToken,
        ]);

        if ($response->failed()) {
            throw new \Exception('Failed to refresh TikTok token: '.$response->body());
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
        return 2200; // Title + caption combined
    }

    public function getMediaLimit(): int
    {
        return 1; // TikTok is single-video platform
    }

    public function validateContent(string $content, array $mediaUrls = []): array
    {
        $errors = [];

        if (empty($mediaUrls)) {
            $errors[] = 'TikTok posts require a video';
        }

        if (count($mediaUrls) > $this->getMediaLimit()) {
            $errors[] = 'TikTok supports only one video per post';
        }

        if (mb_strlen($content) > $this->getCharacterLimit()) {
            $errors[] = "Content exceeds {$this->getCharacterLimit()} character limit";
        }

        // Validate video URL
        foreach ($mediaUrls as $url) {
            if (!filter_var($url, FILTER_VALIDATE_URL)) {
                $errors[] = "Invalid video URL: {$url}";
            }

            // Check if URL looks like a video
            $extension = pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION);
            if (!in_array(strtolower($extension), ['mp4', 'mov', 'avi', 'mkv'])) {
                $errors[] = 'TikTok requires a video file (mp4, mov, avi, or mkv)';
            }
        }

        return $errors;
    }

    public function getSettingsSchema(): array
    {
        return [
            'default_privacy' => [
                'type' => 'select',
                'label' => 'Default Privacy Level',
                'options' => [
                    'PUBLIC_TO_EVERYONE' => 'Public',
                    'MUTUAL_FOLLOW_FRIENDS' => 'Friends',
                    'SELF_ONLY' => 'Private',
                ],
                'default' => 'PUBLIC_TO_EVERYONE',
                'help' => 'Default privacy setting for new videos',
            ],
        ];
    }
}
