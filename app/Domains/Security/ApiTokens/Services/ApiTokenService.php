<?php

namespace App\Domains\Security\ApiTokens\Services;

use App\Domains\Security\UserManagement\Models\User;
use Illuminate\Support\Collection;
use Laravel\Sanctum\NewAccessToken;
use Laravel\Sanctum\PersonalAccessToken;

class ApiTokenService
{
    /**
     * Get all API tokens for a user.
     */
    public function getTokens(User $user): Collection
    {
        return $user->tokens()->orderBy('created_at', 'desc')->get();
    }

    /**
     * Create a new API token for the user.
     */
    public function createToken(User $user, string $name, array $abilities = ['*']): NewAccessToken
    {
        return $user->createToken($name, $abilities);
    }

    /**
     * Delete a specific token.
     */
    public function deleteToken(User $user, string $tokenId): bool
    {
        return $user->tokens()->where('id', $tokenId)->delete() > 0;
    }

    /**
     * Delete all tokens for the user.
     */
    public function deleteAllTokens(User $user): int
    {
        return $user->tokens()->delete();
    }

    /**
     * Update token abilities.
     */
    public function updateTokenAbilities(User $user, string $tokenId, array $abilities): bool
    {
        $token = $user->tokens()->find($tokenId);

        if (!$token) {
            return false;
        }

        $token->abilities = $abilities;
        return $token->save();
    }

    /**
     * Get a specific token by ID.
     */
    public function getToken(User $user, string $tokenId): ?PersonalAccessToken
    {
        return $user->tokens()->find($tokenId);
    }

    /**
     * Update token name.
     */
    public function updateTokenName(User $user, string $tokenId, string $name): bool
    {
        $token = $user->tokens()->find($tokenId);

        if (!$token) {
            return false;
        }

        $token->name = $name;
        return $token->save();
    }

    /**
     * Check if user has reached the maximum number of tokens.
     */
    public function hasReachedMaxTokens(User $user, int $maxTokens = 10): bool
    {
        return $user->tokens()->count() >= $maxTokens;
    }
}


