<?php

declare(strict_types=1);

namespace App\Domains\SocialMedia\Services;

use App\Domains\SocialMedia\Models\SocialAccount;

interface OAuthServiceContract
{
    /**
     * Get OAuth authorization URL for a platform.
     */
    public function getAuthorizationUrl(string $platform): string;

    /**
     * Handle OAuth callback and create/update account.
     */
    public function handleCallback(string $platform, string $code, string $state): SocialAccount;

    /**
     * Refresh an expired access token.
     */
    public function refreshToken(SocialAccount $account): SocialAccount;

    /**
     * Disconnect a social account.
     */
    public function disconnect(SocialAccount $account): bool;

    /**
     * Verify that a connection is still valid.
     */
    public function verifyConnection(SocialAccount $account): bool;

    /**
     * Generate a secure state parameter for OAuth.
     */
    public function generateState(): string;

    /**
     * Verify OAuth state parameter.
     */
    public function verifyState(string $state): bool;
}
