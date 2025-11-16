<?php

declare(strict_types=1);

namespace App\Domains\Security\Tests\UnitTests;

use App\Domains\Security\ApiTokens\Services\ApiTokenService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\PersonalAccessToken;
use Tests\TestCase;

class ApiTokenServiceTest extends TestCase
{
    use RefreshDatabase;

    protected ApiTokenService $service;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ApiTokenService;
        $this->user = User::factory()->create();
    }

    public function test_get_tokens_returns_user_tokens(): void
    {
        // Arrange
        $this->user->createToken('Token 1');
        $this->user->createToken('Token 2');

        // Act
        $tokens = $this->service->getTokens($this->user);

        // Assert
        $this->assertCount(2, $tokens);
    }

    public function test_get_tokens_orders_by_created_at_desc(): void
    {
        // Arrange
        $token1 = $this->user->createToken('First Token');
        sleep(1);
        $token2 = $this->user->createToken('Second Token');

        // Act
        $tokens = $this->service->getTokens($this->user);

        // Assert
        $this->assertEquals('Second Token', $tokens->first()->name);
        $this->assertEquals('First Token', $tokens->last()->name);
    }

    public function test_create_token_creates_new_token(): void
    {
        // Act
        $newToken = $this->service->createToken($this->user, 'Test Token');

        // Assert
        $this->assertNotNull($newToken);
        $this->assertNotNull($newToken->plainTextToken);
        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_id' => $this->user->id,
            'name' => 'Test Token',
        ]);
    }

    public function test_create_token_with_specific_abilities(): void
    {
        // Act
        $newToken = $this->service->createToken($this->user, 'Limited Token', ['read', 'write']);

        // Assert
        $token = PersonalAccessToken::find($newToken->accessToken->id);
        $this->assertEquals(['read', 'write'], $token->abilities);
    }

    public function test_create_token_with_default_abilities(): void
    {
        // Act
        $newToken = $this->service->createToken($this->user, 'Default Token');

        // Assert
        $token = PersonalAccessToken::find($newToken->accessToken->id);
        $this->assertEquals(['*'], $token->abilities);
    }

    public function test_delete_token_removes_token(): void
    {
        // Arrange
        $newToken = $this->user->createToken('Token to Delete');
        $tokenId = (string) $newToken->accessToken->id;

        // Act
        $result = $this->service->deleteToken($this->user, $tokenId);

        // Assert
        $this->assertTrue($result);
        $this->assertDatabaseMissing('personal_access_tokens', [
            'id' => $tokenId,
        ]);
    }

    public function test_delete_token_returns_false_for_nonexistent_token(): void
    {
        // Act
        $result = $this->service->deleteToken($this->user, '999999');

        // Assert
        $this->assertFalse($result);
    }

    public function test_delete_all_tokens_removes_all_user_tokens(): void
    {
        // Arrange
        $this->user->createToken('Token 1');
        $this->user->createToken('Token 2');
        $this->user->createToken('Token 3');

        // Act
        $count = $this->service->deleteAllTokens($this->user);

        // Assert
        $this->assertEquals(3, $count);
        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $this->user->id,
        ]);
    }

    public function test_update_token_abilities_updates_abilities(): void
    {
        // Arrange
        $newToken = $this->user->createToken('Test Token', ['read']);
        $tokenId = (string) $newToken->accessToken->id;

        // Act
        $result = $this->service->updateTokenAbilities($this->user, $tokenId, ['read', 'write', 'delete']);

        // Assert
        $this->assertTrue($result);
        $token = PersonalAccessToken::find($tokenId);
        $this->assertEquals(['read', 'write', 'delete'], $token->abilities);
    }

    public function test_update_token_abilities_returns_false_for_nonexistent_token(): void
    {
        // Act
        $result = $this->service->updateTokenAbilities($this->user, '999999', ['read']);

        // Assert
        $this->assertFalse($result);
    }

    public function test_get_token_returns_specific_token(): void
    {
        // Arrange
        $newToken = $this->user->createToken('Specific Token');
        $tokenId = (string) $newToken->accessToken->id;

        // Act
        $token = $this->service->getToken($this->user, $tokenId);

        // Assert
        $this->assertNotNull($token);
        $this->assertEquals('Specific Token', $token->name);
    }

    public function test_get_token_returns_null_for_nonexistent_token(): void
    {
        // Act
        $token = $this->service->getToken($this->user, '999999');

        // Assert
        $this->assertNull($token);
    }

    public function test_update_token_name_updates_name(): void
    {
        // Arrange
        $newToken = $this->user->createToken('Old Name');
        $tokenId = (string) $newToken->accessToken->id;

        // Act
        $result = $this->service->updateTokenName($this->user, $tokenId, 'New Name');

        // Assert
        $this->assertTrue($result);
        $token = PersonalAccessToken::find($tokenId);
        $this->assertEquals('New Name', $token->name);
    }

    public function test_update_token_name_returns_false_for_nonexistent_token(): void
    {
        // Act
        $result = $this->service->updateTokenName($this->user, '999999', 'New Name');

        // Assert
        $this->assertFalse($result);
    }

    public function test_has_reached_max_tokens_returns_false_when_below_limit(): void
    {
        // Arrange
        $this->user->createToken('Token 1');
        $this->user->createToken('Token 2');

        // Act
        $result = $this->service->hasReachedMaxTokens($this->user, 10);

        // Assert
        $this->assertFalse($result);
    }

    public function test_has_reached_max_tokens_returns_true_when_at_limit(): void
    {
        // Arrange
        for ($i = 0; $i < 10; $i++) {
            $this->user->createToken("Token {$i}");
        }

        // Act
        $result = $this->service->hasReachedMaxTokens($this->user, 10);

        // Assert
        $this->assertTrue($result);
    }

    public function test_has_reached_max_tokens_returns_true_when_above_limit(): void
    {
        // Arrange
        for ($i = 0; $i < 11; $i++) {
            $this->user->createToken("Token {$i}");
        }

        // Act
        $result = $this->service->hasReachedMaxTokens($this->user, 10);

        // Assert
        $this->assertTrue($result);
    }
}
