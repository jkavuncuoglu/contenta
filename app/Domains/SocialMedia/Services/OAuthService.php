<?php

declare(strict_types=1);

namespace App\Domains\SocialMedia\Services;

use App\Domains\SocialMedia\Constants\SocialPlatform;
use App\Domains\SocialMedia\Models\SocialAccount;
use App\Domains\SocialMedia\Services\PlatformAdapters\FacebookAdapter;
use App\Domains\SocialMedia\Services\PlatformAdapters\InstagramAdapter;
use App\Domains\SocialMedia\Services\PlatformAdapters\LinkedInAdapter;
use App\Domains\SocialMedia\Services\PlatformAdapters\PinterestAdapter;
use App\Domains\SocialMedia\Services\PlatformAdapters\SocialPlatformInterface;
use App\Domains\SocialMedia\Services\PlatformAdapters\TikTokAdapter;
use App\Domains\SocialMedia\Services\PlatformAdapters\TwitterAdapter;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class OAuthService implements OAuthServiceContract
{
    /**
     * Get platform adapter instance.
     */
    public function getPlatformAdapter(string $platform): SocialPlatformInterface
    {
        return match ($platform) {
            SocialPlatform::TWITTER => app(TwitterAdapter::class),
            SocialPlatform::FACEBOOK => app(FacebookAdapter::class),
            SocialPlatform::LINKEDIN => app(LinkedInAdapter::class),
            SocialPlatform::INSTAGRAM => app(InstagramAdapter::class),
            SocialPlatform::PINTEREST => app(PinterestAdapter::class),
            SocialPlatform::TIKTOK => app(TikTokAdapter::class),
            default => throw new \InvalidArgumentException("Unsupported platform: {$platform}"),
        };
    }

    /**
     * Get OAuth authorization URL for a platform.
     */
    public function getAuthorizationUrl(string $platform): string
    {
        if (! SocialPlatform::isValid($platform)) {
            throw new \InvalidArgumentException("Invalid platform: {$platform}");
        }

        $adapter = $this->getPlatformAdapter($platform);
        $state = $this->generateState();

        // Store state in session for verification
        session([
            'oauth_state' => $state,
            'oauth_platform' => $platform,
        ]);

        $redirectUri = route('admin.social-media.oauth.callback', ['platform' => $platform]);

        return $adapter->getAuthorizationUrl($state, $redirectUri);
    }

    /**
     * Handle OAuth callback and create/update account.
     */
    public function handleCallback(string $platform, string $code, string $state): SocialAccount
    {
        // Verify state
        if (! $this->verifyState($state)) {
            throw new \Exception('Invalid OAuth state parameter. Possible CSRF attack.');
        }

        $adapter = $this->getPlatformAdapter($platform);
        $redirectUri = route('admin.social-media.oauth.callback', ['platform' => $platform]);

        // Exchange code for token
        $tokenData = $adapter->exchangeCodeForToken($code, $redirectUri);

        // Create or update social account
        $account = SocialAccount::updateOrCreate(
            [
                'platform' => $platform,
                'platform_account_id' => $tokenData['account_id'],
            ],
            [
                'platform_username' => $tokenData['username'] ?? null,
                'platform_display_name' => $tokenData['display_name'] ?? null,
                'access_token' => $tokenData['access_token'],
                'refresh_token' => $tokenData['refresh_token'] ?? null,
                'token_expires_at' => $tokenData['expires_at'] ?? null,
                'is_active' => true,
                'last_synced_at' => now(),
            ]
        );

        // Clear session state
        session()->forget(['oauth_state', 'oauth_platform']);

        Log::info('Social account connected', [
            'platform' => $platform,
            'account_id' => $account->id,
        ]);

        return $account;
    }

    /**
     * Refresh an expired access token.
     */
    public function refreshToken(SocialAccount $account): SocialAccount
    {
        if (! $account->refresh_token) {
            throw new \Exception('No refresh token available for this account.');
        }

        $adapter = $this->getPlatformAdapter($account->platform);

        try {
            $tokenData = $adapter->refreshAccessToken($account->refresh_token);

            $account->update([
                'access_token' => $tokenData['access_token'],
                'refresh_token' => $tokenData['refresh_token'] ?? $account->refresh_token,
                'token_expires_at' => $tokenData['expires_at'] ?? null,
                'last_synced_at' => now(),
            ]);

            Log::info('Token refreshed', [
                'account_id' => $account->id,
                'platform' => $account->platform,
            ]);

            return $account;
        } catch (\Exception $e) {
            Log::error('Failed to refresh token', [
                'account_id' => $account->id,
                'platform' => $account->platform,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Disconnect a social account.
     */
    public function disconnect(SocialAccount $account): bool
    {
        Log::info('Social account disconnected', [
            'platform' => $account->platform,
            'account_id' => $account->id,
        ]);

        return $account->delete();
    }

    /**
     * Verify that a connection is still valid.
     */
    public function verifyConnection(SocialAccount $account): bool
    {
        // Check if token is expired
        if ($account->isTokenExpired()) {
            return false;
        }

        // TODO: In future, can make API call to verify token is still valid
        // For now, just check token expiry
        return true;
    }

    /**
     * Generate a secure state parameter for OAuth.
     */
    public function generateState(): string
    {
        return Str::random(40);
    }

    /**
     * Verify OAuth state parameter.
     */
    public function verifyState(string $state): bool
    {
        $sessionState = session('oauth_state');

        if (! $sessionState) {
            return false;
        }

        return hash_equals($sessionState, $state);
    }
}
