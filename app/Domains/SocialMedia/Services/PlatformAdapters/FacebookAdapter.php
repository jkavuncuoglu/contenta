<?php

declare(strict_types=1);

namespace App\Domains\SocialMedia\Services\PlatformAdapters;

use App\Domains\SocialMedia\Constants\OAuthScopes;
use App\Domains\SocialMedia\Models\SocialPost;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FacebookAdapter implements SocialPlatformInterface
{
    protected string $apiBaseUrl = 'https://graph.facebook.com/v18.0';
    protected string $authBaseUrl = 'https://www.facebook.com/v18.0/dialog/oauth';
    protected string $tokenUrl = 'https://graph.facebook.com/v18.0/oauth/access_token';

    /**
     * Publish a post to Facebook.
     */
    public function publishPost(SocialPost $post): array
    {
        $account = $post->socialAccount;
        $pageId = $account->platform_settings['facebook_page_id'] ?? null;

        if (! $pageId) {
            throw new \Exception('Facebook page ID not configured');
        }

        try {
            $response = Http::post("{$this->apiBaseUrl}/{$pageId}/feed", [
                'message' => $post->content,
                'link' => $post->link_url,
                'access_token' => $account->access_token,
            ]);

            if ($response->failed()) {
                throw new \Exception('Facebook API error: '.$response->body());
            }

            $data = $response->json();

            return [
                'id' => $data['id'],
                'permalink' => "https://facebook.com/{$data['id']}",
            ];
        } catch (\Exception $e) {
            Log::error('Facebook publish failed', [
                'post_id' => $post->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Delete a post from Facebook.
     */
    public function deletePost(string $platformPostId): bool
    {
        try {
            $response = Http::delete("{$this->apiBaseUrl}/{$platformPostId}");

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('Facebook delete failed', [
                'post_id' => $platformPostId,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Get analytics for a Facebook post.
     */
    public function getPostAnalytics(string $platformPostId): array
    {
        try {
            $response = Http::get("{$this->apiBaseUrl}/{$platformPostId}", [
                'fields' => 'reactions.summary(true),shares,comments.summary(true),insights.metric(post_impressions,post_engaged_users)',
            ]);

            if ($response->failed()) {
                return [];
            }

            $data = $response->json();

            return [
                'likes' => $data['reactions']['summary']['total_count'] ?? 0,
                'shares' => $data['shares']['count'] ?? 0,
                'comments' => $data['comments']['summary']['total_count'] ?? 0,
                'impressions' => $this->extractInsightValue($data['insights']['data'] ?? [], 'post_impressions'),
                'reach' => $this->extractInsightValue($data['insights']['data'] ?? [], 'post_engaged_users'),
                'platform_metrics' => [
                    'reactions_breakdown' => $data['reactions']['data'] ?? [],
                ],
            ];
        } catch (\Exception $e) {
            Log::error('Facebook analytics failed', [
                'post_id' => $platformPostId,
                'error' => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Extract insight value from Facebook insights data.
     */
    protected function extractInsightValue(array $insights, string $metric): int
    {
        foreach ($insights as $insight) {
            if ($insight['name'] === $metric) {
                return $insight['values'][0]['value'] ?? 0;
            }
        }

        return 0;
    }

    /**
     * Get OAuth authorization URL.
     */
    public function getAuthorizationUrl(string $state, string $redirectUri): string
    {
        $clientId = config('services.facebook.client_id');

        if (! $clientId) {
            throw new \Exception('Facebook client ID not configured');
        }

        $params = http_build_query([
            'client_id' => $clientId,
            'redirect_uri' => $redirectUri,
            'scope' => OAuthScopes::toString('facebook'),
            'state' => $state,
            'response_type' => 'code',
        ]);

        return "{$this->authBaseUrl}?{$params}";
    }

    /**
     * Exchange authorization code for access token.
     */
    public function exchangeCodeForToken(string $code, string $redirectUri): array
    {
        $clientId = config('services.facebook.client_id');
        $clientSecret = config('services.facebook.client_secret');

        $response = Http::get($this->tokenUrl, [
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'redirect_uri' => $redirectUri,
            'code' => $code,
        ]);

        if ($response->failed()) {
            throw new \Exception('Facebook token exchange failed: '.$response->body());
        }

        $data = $response->json();

        // Get user info
        $userResponse = Http::get("{$this->apiBaseUrl}/me", [
            'fields' => 'id,name',
            'access_token' => $data['access_token'],
        ]);

        $userData = $userResponse->json();

        // Exchange for long-lived token
        $longLivedResponse = Http::get("{$this->apiBaseUrl}/oauth/access_token", [
            'grant_type' => 'fb_exchange_token',
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'fb_exchange_token' => $data['access_token'],
        ]);

        $longLivedData = $longLivedResponse->json();

        return [
            'access_token' => $longLivedData['access_token'] ?? $data['access_token'],
            'refresh_token' => null, // Facebook uses long-lived tokens
            'expires_at' => isset($longLivedData['expires_in']) ? now()->addSeconds($longLivedData['expires_in']) : now()->addDays(60),
            'account_id' => $userData['id'],
            'username' => null,
            'display_name' => $userData['name'] ?? null,
        ];
    }

    /**
     * Refresh an expired access token.
     */
    public function refreshAccessToken(string $refreshToken): array
    {
        // Facebook uses long-lived tokens that don't need refresh
        // If token expires, user needs to re-authenticate
        throw new \Exception('Facebook tokens must be re-authenticated when expired');
    }

    /**
     * Get character limit for Facebook.
     */
    public function getCharacterLimit(): int
    {
        return 63206;
    }

    /**
     * Get media limit for Facebook.
     */
    public function getMediaLimit(): int
    {
        return 10;
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

        return $errors;
    }

    /**
     * Get platform-specific settings schema.
     */
    public function getSettingsSchema(): array
    {
        return [
            'facebook_page_id' => [
                'type' => 'text',
                'label' => 'Facebook Page ID',
                'required' => true,
            ],
            'default_hashtags' => [
                'type' => 'tags',
                'label' => 'Default Hashtags',
                'default' => [],
            ],
        ];
    }
}
