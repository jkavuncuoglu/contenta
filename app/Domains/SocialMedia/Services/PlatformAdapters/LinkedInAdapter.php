<?php

declare(strict_types=1);

namespace App\Domains\SocialMedia\Services\PlatformAdapters;

use App\Domains\SocialMedia\Constants\OAuthScopes;
use App\Domains\SocialMedia\Models\SocialPost;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LinkedInAdapter implements SocialPlatformInterface
{
    protected string $apiBaseUrl = 'https://api.linkedin.com/v2';
    protected string $authBaseUrl = 'https://www.linkedin.com/oauth/v2/authorization';
    protected string $tokenUrl = 'https://www.linkedin.com/oauth/v2/accessToken';

    /**
     * Publish a post to LinkedIn.
     */
    public function publishPost(SocialPost $post): array
    {
        $account = $post->socialAccount;

        try {
            // Get user URN
            $profileResponse = Http::withToken($account->access_token)
                ->get("{$this->apiBaseUrl}/me");

            $profileId = $profileResponse->json()['id'] ?? null;

            if (! $profileId) {
                throw new \Exception('Could not retrieve LinkedIn profile ID');
            }

            $authorUrn = "urn:li:person:{$profileId}";

            // Create post
            $response = Http::withToken($account->access_token)
                ->post("{$this->apiBaseUrl}/ugcPosts", [
                    'author' => $authorUrn,
                    'lifecycleState' => 'PUBLISHED',
                    'specificContent' => [
                        'com.linkedin.ugc.ShareContent' => [
                            'shareCommentary' => [
                                'text' => $post->content,
                            ],
                            'shareMediaCategory' => 'NONE',
                        ],
                    ],
                    'visibility' => [
                        'com.linkedin.ugc.MemberNetworkVisibility' => 'PUBLIC',
                    ],
                ]);

            if ($response->failed()) {
                throw new \Exception('LinkedIn API error: '.$response->body());
            }

            $data = $response->json();
            $postId = basename($data['id']);

            return [
                'id' => $postId,
                'permalink' => "https://www.linkedin.com/feed/update/{$data['id']}",
            ];
        } catch (\Exception $e) {
            Log::error('LinkedIn publish failed', [
                'post_id' => $post->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Delete a post from LinkedIn.
     */
    public function deletePost(string $platformPostId): bool
    {
        try {
            $urn = "urn:li:share:{$platformPostId}";
            $response = Http::delete("{$this->apiBaseUrl}/ugcPosts/{$urn}");

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('LinkedIn delete failed', [
                'post_id' => $platformPostId,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Get analytics for a LinkedIn post.
     */
    public function getPostAnalytics(string $platformPostId): array
    {
        try {
            // LinkedIn analytics require additional permissions
            // This is a placeholder - actual implementation depends on permissions
            return [
                'likes' => 0,
                'shares' => 0,
                'comments' => 0,
                'impressions' => 0,
                'platform_metrics' => [],
            ];
        } catch (\Exception $e) {
            Log::error('LinkedIn analytics failed', [
                'post_id' => $platformPostId,
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
        $clientId = config('services.linkedin.client_id');

        if (! $clientId) {
            throw new \Exception('LinkedIn client ID not configured');
        }

        $params = http_build_query([
            'response_type' => 'code',
            'client_id' => $clientId,
            'redirect_uri' => $redirectUri,
            'scope' => OAuthScopes::toString('linkedin'),
            'state' => $state,
        ]);

        return "{$this->authBaseUrl}?{$params}";
    }

    /**
     * Exchange authorization code for access token.
     */
    public function exchangeCodeForToken(string $code, string $redirectUri): array
    {
        $clientId = config('services.linkedin.client_id');
        $clientSecret = config('services.linkedin.client_secret');

        $response = Http::asForm()->post($this->tokenUrl, [
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => $redirectUri,
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
        ]);

        if ($response->failed()) {
            throw new \Exception('LinkedIn token exchange failed: '.$response->body());
        }

        $data = $response->json();

        // Get user info
        $userResponse = Http::withToken($data['access_token'])
            ->get("{$this->apiBaseUrl}/me");

        $userData = $userResponse->json();

        return [
            'access_token' => $data['access_token'],
            'refresh_token' => $data['refresh_token'] ?? null,
            'expires_at' => isset($data['expires_in']) ? now()->addSeconds($data['expires_in']) : null,
            'account_id' => $userData['id'] ?? '',
            'username' => null,
            'display_name' => $userData['localizedFirstName'].' '.$userData['localizedLastName'] ?? null,
        ];
    }

    /**
     * Refresh an expired access token.
     */
    public function refreshAccessToken(string $refreshToken): array
    {
        $clientId = config('services.linkedin.client_id');
        $clientSecret = config('services.linkedin.client_secret');

        $response = Http::asForm()->post($this->tokenUrl, [
            'grant_type' => 'refresh_token',
            'refresh_token' => $refreshToken,
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
        ]);

        if ($response->failed()) {
            throw new \Exception('LinkedIn token refresh failed: '.$response->body());
        }

        $data = $response->json();

        return [
            'access_token' => $data['access_token'],
            'refresh_token' => $data['refresh_token'] ?? $refreshToken,
            'expires_at' => isset($data['expires_in']) ? now()->addSeconds($data['expires_in']) : null,
        ];
    }

    /**
     * Get character limit for LinkedIn.
     */
    public function getCharacterLimit(): int
    {
        return 3000;
    }

    /**
     * Get media limit for LinkedIn.
     */
    public function getMediaLimit(): int
    {
        return 9;
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

        if (empty($content)) {
            $errors[] = 'Content is required';
        }

        return $errors;
    }

    /**
     * Get platform-specific settings schema.
     */
    public function getSettingsSchema(): array
    {
        return [
            'linkedin_company_id' => [
                'type' => 'text',
                'label' => 'LinkedIn Company ID (optional)',
                'required' => false,
            ],
            'default_hashtags' => [
                'type' => 'tags',
                'label' => 'Default Hashtags',
                'default' => [],
            ],
        ];
    }
}
