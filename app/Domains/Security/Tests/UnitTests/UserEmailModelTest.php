<?php

declare(strict_types=1);

namespace App\Domains\Security\Tests\UnitTests;

use App\Domains\Security\UserManagement\Models\User;
use App\Domains\Security\UserManagement\Models\UserEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserEmailModelTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_creates_verification_token_on_creation(): void
    {
        // Act
        $userEmail = UserEmail::create([
            'user_id' => $this->user->id,
            'email' => 'test@example.com',
            'is_primary' => true,
        ]);

        // Assert
        $this->assertNotNull($userEmail->verification_token);
        $this->assertEquals(60, strlen($userEmail->verification_token));
    }

    public function test_preserves_provided_verification_token(): void
    {
        // Arrange
        $customToken = 'custom_token_12345';

        // Act
        $userEmail = UserEmail::create([
            'user_id' => $this->user->id,
            'email' => 'test@example.com',
            'is_primary' => true,
            'verification_token' => $customToken,
        ]);

        // Assert
        $this->assertEquals($customToken, $userEmail->verification_token);
    }

    public function test_ensures_only_one_primary_email_per_user(): void
    {
        // Arrange
        $email1 = UserEmail::create([
            'user_id' => $this->user->id,
            'email' => 'primary1@example.com',
            'is_primary' => true,
        ]);

        // Act
        $email2 = UserEmail::create([
            'user_id' => $this->user->id,
            'email' => 'primary2@example.com',
            'is_primary' => true,
        ]);

        // Assert
        $email1->refresh();
        $this->assertFalse($email1->is_primary);
        $this->assertTrue($email2->is_primary);
    }

    public function test_primary_email_enforcement_on_update(): void
    {
        // Arrange
        $email1 = UserEmail::create([
            'user_id' => $this->user->id,
            'email' => 'email1@example.com',
            'is_primary' => true,
        ]);
        $email2 = UserEmail::create([
            'user_id' => $this->user->id,
            'email' => 'email2@example.com',
            'is_primary' => false,
        ]);

        // Act
        $email2->is_primary = true;
        $email2->save();

        // Assert
        $email1->refresh();
        $this->assertFalse($email1->is_primary);
        $this->assertTrue($email2->is_primary);
    }

    public function test_primary_email_enforcement_does_not_affect_other_users(): void
    {
        // Arrange
        $otherUser = User::factory()->create();
        $email1 = UserEmail::create([
            'user_id' => $this->user->id,
            'email' => 'user1@example.com',
            'is_primary' => true,
        ]);
        $email2 = UserEmail::create([
            'user_id' => $otherUser->id,
            'email' => 'user2@example.com',
            'is_primary' => true,
        ]);

        // Act
        $email3 = UserEmail::create([
            'user_id' => $this->user->id,
            'email' => 'user1-new@example.com',
            'is_primary' => true,
        ]);

        // Assert
        $email1->refresh();
        $email2->refresh();
        $this->assertFalse($email1->is_primary);
        $this->assertTrue($email2->is_primary); // Should not be affected
        $this->assertTrue($email3->is_primary);
    }

    public function test_user_relationship_returns_user(): void
    {
        // Arrange
        $userEmail = UserEmail::create([
            'user_id' => $this->user->id,
            'email' => 'test@example.com',
            'is_primary' => true,
        ]);

        // Act
        $relatedUser = $userEmail->user;

        // Assert
        $this->assertInstanceOf(User::class, $relatedUser);
        $this->assertEquals($this->user->id, $relatedUser->id);
    }

    public function test_has_verified_email_returns_true_when_verified(): void
    {
        // Arrange
        $userEmail = UserEmail::create([
            'user_id' => $this->user->id,
            'email' => 'test@example.com',
            'is_primary' => true,
            'verified_at' => now(),
        ]);

        // Act & Assert
        $this->assertTrue($userEmail->hasVerifiedEmail());
    }

    public function test_has_verified_email_returns_false_when_not_verified(): void
    {
        // Arrange
        $userEmail = UserEmail::create([
            'user_id' => $this->user->id,
            'email' => 'test@example.com',
            'is_primary' => true,
            'verified_at' => null,
        ]);

        // Act & Assert
        $this->assertFalse($userEmail->hasVerifiedEmail());
    }

    public function test_verify_email_with_valid_hash(): void
    {
        // Arrange
        $userEmail = UserEmail::create([
            'user_id' => $this->user->id,
            'email' => 'test@example.com',
            'is_primary' => true,
        ]);
        $token = $userEmail->verification_token;

        // Act
        $result = $userEmail->verifyEmail($token);

        // Assert
        $this->assertEquals('success', $result['status']);
        $this->assertEquals('Email verified successfully', $result['message']);
        $userEmail->refresh();
        $this->assertNotNull($userEmail->verified_at);
    }

    public function test_verify_email_with_invalid_hash(): void
    {
        // Arrange
        $userEmail = UserEmail::create([
            'user_id' => $this->user->id,
            'email' => 'test@example.com',
            'is_primary' => true,
        ]);

        // Act
        $result = $userEmail->verifyEmail('invalid_hash');

        // Assert
        $this->assertEquals('error', $result['status']);
        $this->assertEquals('Invalid verification hash', $result['message']);
        $userEmail->refresh();
        $this->assertNull($userEmail->verified_at);
    }

    public function test_send_email_verification_notification_returns_success(): void
    {
        // Arrange
        $userEmail = UserEmail::create([
            'user_id' => $this->user->id,
            'email' => 'test@example.com',
            'is_primary' => true,
        ]);

        // Act
        $result = $userEmail->sendEmailVerificationNotification();

        // Assert
        $this->assertIsArray($result);
        $this->assertEquals('success', $result['status']);
        $this->assertEquals('Verification email sent', $result['message']);
    }

    public function test_verified_at_cast_to_datetime(): void
    {
        // Arrange
        $now = now();
        $userEmail = UserEmail::create([
            'user_id' => $this->user->id,
            'email' => 'test@example.com',
            'is_primary' => true,
            'verified_at' => $now,
        ]);

        // Act
        $verifiedAt = $userEmail->verified_at;

        // Assert
        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $verifiedAt);
        $this->assertEquals($now->timestamp, $verifiedAt->timestamp);
    }

    public function test_is_primary_cast_to_boolean(): void
    {
        // Arrange
        $userEmail = UserEmail::create([
            'user_id' => $this->user->id,
            'email' => 'test@example.com',
            'is_primary' => 1,
        ]);

        // Act
        $isPrimary = $userEmail->is_primary;

        // Assert
        $this->assertIsBool($isPrimary);
        $this->assertTrue($isPrimary);
    }
}
