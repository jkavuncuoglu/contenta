<?php

declare(strict_types=1);

namespace App\Domains\SocialMedia\Services\PlatformAdapters;

use App\Domains\SocialMedia\Constants\OAuthScopes;
use App\Domains\SocialMedia\Models\SocialPost;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TwitterAdapter implements SocialPlatformInterface
{
    protected string $apiBaseUrl = 'https://api.twitter.com/2';
    protected string $authBaseUrl = 'https://twitter.com/i/oauth2/authorize';
    protected string $tokenUrl = 'https://api.twitter.com/2/oauth2/token';

    /**
     * Publish a post to Twitter.
     */
    public function publishPost(SocialPost $post): array
    {
        $account = $post->socialAccount;

        try {
            $response = Http::withToken($account->access_token)
                ->post("{$this->apiBaseUrl}/tweets", [
                    'text' => $post->content,
                ]);

            if ($response->failed()) {
                throw new \Exception('Twitter API error: '.$response->body());
            }

            $data = $response->json();

            return [
                'id' => $data['data']['id'],
                'permalink' => "https://twitter.com/{$account->platform_username}/status/{$data['data']['id']}",
            ];
        } catch (\Exception $e) {
            Log::error('Twitter publish failed', [
                'post_id' => $post->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Delete a post from Twitter.
     */
    public function deletePost(string $platformPostId): bool
    {
        // Twitter API v2 delete endpoint
        try {
            $response = Http::delete("{$this->apiBaseUrl}/tweets/{$platformPostId}");

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('Twitter delete failed', [
                'tweet_id' => $platformPostId,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Get analytics for a tweet.
     */
    public function getPostAnalytics(string $platformPostId): array
    {
        // Twitter API v2 metrics endpoint
        try {
            $response = Http::get("{$this->apiBaseUrl}/tweets/{$platformPostId}", [
                'tweet.fields' => 'public_metrics',
            ]);

            if ($response->failed()) {
                return [];
            }

            $data = $response->json();
            $metrics = $data['data']['public_metrics'] ?? [];

            return [
                'likes' => $metrics['like_count'] ?? 0,
                'shares' => $metrics['retweet_count'] ?? 0,
                'comments' => $metrics['reply_count'] ?? 0,
                'impressions' => $metrics['impression_count'] ?? 0,
                'platform_metrics' => [
                    'retweets' => $metrics['retweet_count'] ?? 0,
                    'quote_tweets' => $metrics['quote_count'] ?? 0,
                ],
            ];
        } catch (\Exception $e) {
            Log::error('Twitter analytics failed', [
                'tweet_id' => $platformPostId,
                'error' => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Get OAuth authorization URL.
     */
    public function getAuthorizationUrl(string $state, string $redirectUri): string
    {
        $clientId = config('services.twitter.client_id');

        if (! $clientId) {
            throw new \Exception('Twitter client ID not configured');
        }

        $params = http_build_query([
            'response_type' => 'code',
            'client_id' => $clientId,
            'redirect_uri' => $redirectUri,
            'scope' => OAuthScopes::toString('twitter'),
            'state' => $state,
            'code_challenge' => 'challenge', // PKCE - should be properly implemented
            'code_challenge_method' => 'plain',
        ]);

        return "{$this->authBaseUrl}?{$params}";
    }

    /**
     * Exchange authorization code for access token.
     */
    public function exchangeCodeForToken(string $code, string $redirectUri): array
    {
        $clientId = config('services.twitter.client_id');
        $clientSecret = config('services.twitter.client_secret');

        $response = Http::asForm()->post($this->tokenUrl, [
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => $redirectUri,
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'code_verifier' => 'challenge', // PKCE
        ]);

        if ($response->failed()) {
            throw new \Exception('Twitter token exchange failed: '.$response->body());
        }

        $data = $response->json();

        // Get user info
        $userResponse = Http::withToken($data['access_token'])
            ->get("{$this->apiBaseUrl}/users/me");

        $userData = $userResponse->json()['data'] ?? [];

        return [
            'access_token' => $data['access_token'],
            'refresh_token' => $data['refresh_token'] ?? null,
            'expires_at' => isset($data['expires_in']) ? now()->addSeconds($data['expires_in']) : null,
            'account_id' => $userData['id'] ?? '',
            'username' => $userData['username'] ?? null,
            'display_name' => $userData['name'] ?? null,
        ];
    }

    /**
     * Refresh an expired access token.
     */
    public function refreshAccessToken(string $refreshToken): array
    {
        $clientId = config('services.twitter.client_id');
        $clientSecret = config('services.twitter.client_secret');

        $response = Http::asForm()->post($this->tokenUrl, [
            'grant_type' => 'refresh_token',
            'refresh_token' => $refreshToken,
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
        ]);

        if ($response->failed()) {
            throw new \Exception('Twitter token refresh failed: '.$response->body());
        }

        $data = $response->json();

        return [
            'access_token' => $data['access_token'],
            'refresh_token' => $data['refresh_token'] ?? $refreshToken,
            'expires_at' => isset($data['expires_in']) ? now()->addSeconds($data['expires_in']) : null,
        ];
    }

    /**
     * Get character limit for Twitter.
     */
    public function getCharacterLimit(): int
    {
        return 280;
    }

    /**
     * Get media limit for Twitter.
     */
    public function getMediaLimit(): int
    {
        return 4;
    }

    /**
     * Validate post content before publishing.
     */
    public function validateContent(string $content, array $mediaUrls = []): array
    {
        $errors = [];

        if (mb_strlen($content) > $this->getCharacterLimit()) {
            $errors[] = "Content exceeds {$this->getCharacterLimit()} character limit";
        }

        if (count($mediaUrls) > $this->getMediaLimit()) {
            $errors[] = "Maximum {$this->getMediaLimit()} media attachments allowed";
        }

        if (empty($content) && empty($mediaUrls)) {
            $errors[] = 'Content or media is required';
        }

        return $errors;
    }

    /**
     * Get platform-specific settings schema.
     */
    public function getSettingsSchema(): array
    {
        return [
            'hashtag_strategy' => [
                'type' => 'select',
                'label' => 'Hashtag Strategy',
                'options' => ['auto', 'manual', 'none'],
                'default' => 'auto',
            ],
            'default_hashtags' => [
                'type' => 'tags',
                'label' => 'Default Hashtags',
                'default' => [],
            ],
            'shorten_links' => [
                'type' => 'boolean',
                'label' => 'Shorten Links',
                'default' => true,
            ],
        ];
    }
}
