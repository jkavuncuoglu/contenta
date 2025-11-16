<?php

declare(strict_types=1);

namespace App\Domains\Security\Tests\UnitTests;

use App\Domains\Security\UserManagement\Models\User;
use App\Domains\Security\UserManagement\Models\UserEmail;
use App\Domains\Security\UserManagement\Services\UserEmailService;
use App\Domains\Security\UserManagement\Services\UserEmailServiceContract;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class UserEmailServiceTest extends TestCase
{
    use RefreshDatabase;

    protected UserEmailService $service;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new UserEmailService;
        $this->user = User::factory()->create();
    }

    public function test_get_by_id_returns_user_email(): void
    {
        // Arrange
        $userEmail = UserEmail::create([
            'user_id' => $this->user->id,
            'email' => 'test@example.com',
            'is_primary' => true,
        ]);

        // Act
        $found = $this->service->getById($userEmail->id);

        // Assert
        $this->assertEquals($userEmail->id, $found->id);
    }

    public function test_get_by_user_email_returns_user_email(): void
    {
        // Arrange
        UserEmail::create([
            'user_id' => $this->user->id,
            'email' => 'test@example.com',
            'is_primary' => true,
        ]);

        // Act
        $found = $this->service->getByUserEmail('test@example.com');

        // Assert
        $this->assertEquals('test@example.com', $found->email);
    }

    public function test_get_primary_email_returns_primary_email(): void
    {
        // Arrange
        UserEmail::create([
            'user_id' => $this->user->id,
            'email' => 'secondary@example.com',
            'is_primary' => false,
        ]);
        UserEmail::create([
            'user_id' => $this->user->id,
            'email' => 'primary@example.com',
            'is_primary' => true,
        ]);

        // Act
        $primary = $this->service->getPrimaryEmail($this->user->id);

        // Assert
        $this->assertEquals('primary@example.com', $primary->email);
        $this->assertTrue($primary->is_primary);
    }

    public function test_get_by_verification_code_returns_user_email(): void
    {
        // Arrange
        UserEmail::create([
            'user_id' => $this->user->id,
            'email' => 'test@example.com',
            'is_primary' => true,
            'email_verification_code' => '123456',
        ]);

        // Act
        $found = $this->service->getByVerificationCode('123456');

        // Assert
        $this->assertEquals('test@example.com', $found->email);
        $this->assertEquals('123456', $found->email_verification_code);
    }

    public function test_mark_as_verified_updates_verification_fields(): void
    {
        // Arrange
        $userEmail = UserEmail::create([
            'user_id' => $this->user->id,
            'email' => 'test@example.com',
            'is_primary' => true,
            'verification_token' => 'token123',
            'email_verification_code' => '654321',
        ]);

        // Act
        $result = $this->service->markAsVerified($userEmail);

        // Assert
        $this->assertTrue($result);
        $userEmail->refresh();
        $this->assertNotNull($userEmail->verified_at);
        $this->assertNull($userEmail->verification_token);
        $this->assertNull($userEmail->email_verification_code);
    }

    public function test_generate_verification_token_creates_token(): void
    {
        // Arrange
        $userEmail = UserEmail::create([
            'user_id' => $this->user->id,
            'email' => 'test@example.com',
            'is_primary' => true,
        ]);

        // Act
        $token = $this->service->generateVerificationToken($userEmail);

        // Assert
        $this->assertNotEmpty($token);
        $this->assertEquals(60, strlen($token));
        $userEmail->refresh();
        $this->assertEquals($token, $userEmail->verification_token);
    }

    public function test_generate_email_verification_code_creates_6_digit_code(): void
    {
        // Arrange
        $userEmail = UserEmail::create([
            'user_id' => $this->user->id,
            'email' => 'test@example.com',
            'is_primary' => true,
        ]);

        // Act
        $code = $this->service->generateEmailVerificationCode($userEmail);

        // Assert
        $this->assertEquals(6, strlen($code));
        $this->assertMatchesRegularExpression('/^\d{6}$/', $code);
        $userEmail->refresh();
        $this->assertEquals($code, $userEmail->email_verification_code);
    }

    public function test_verify_with_code_verifies_email(): void
    {
        // Arrange
        $userEmail = UserEmail::create([
            'user_id' => $this->user->id,
            'email' => 'test@example.com',
            'is_primary' => true,
            'email_verification_code' => '123456',
        ]);

        // Act
        $result = $this->service->verifyWithCode('123456');

        // Assert
        $this->assertTrue($result);
        $userEmail->refresh();
        $this->assertNotNull($userEmail->verified_at);
    }

    public function test_make_primary_sets_email_as_primary(): void
    {
        // Arrange
        $email1 = UserEmail::create([
            'user_id' => $this->user->id,
            'email' => 'primary@example.com',
            'is_primary' => true,
            'verified_at' => now(),
        ]);
        $email2 = UserEmail::create([
            'user_id' => $this->user->id,
            'email' => 'secondary@example.com',
            'is_primary' => false,
            'verified_at' => now(),
        ]);

        // Act
        $result = $this->service->makePrimary('secondary@example.com');

        // Assert
        $this->assertTrue($result);
        $email1->refresh();
        $email2->refresh();
        $this->assertFalse($email1->is_primary);
        $this->assertTrue($email2->is_primary);
    }

    public function test_make_primary_throws_exception_for_unverified_email(): void
    {
        // Arrange
        UserEmail::create([
            'user_id' => $this->user->id,
            'email' => 'unverified@example.com',
            'is_primary' => false,
            'verified_at' => null,
        ]);

        // Assert
        $this->expectException(ValidationException::class);

        // Act
        $this->service->makePrimary('unverified@example.com');
    }

    public function test_can_be_deleted_returns_true_for_non_primary_email(): void
    {
        // Arrange
        UserEmail::create([
            'user_id' => $this->user->id,
            'email' => 'secondary@example.com',
            'is_primary' => false,
        ]);

        // Act
        $result = $this->service->canBeDeleted('secondary@example.com');

        // Assert
        $this->assertTrue($result);
    }

    public function test_can_be_deleted_returns_false_for_only_primary_email(): void
    {
        // Arrange
        UserEmail::create([
            'user_id' => $this->user->id,
            'email' => 'only@example.com',
            'is_primary' => true,
            'verified_at' => now(),
        ]);

        // Act
        $result = $this->service->canBeDeleted('only@example.com');

        // Assert
        $this->assertFalse($result);
    }

    public function test_can_be_deleted_returns_true_for_primary_with_other_verified_emails(): void
    {
        // Arrange
        UserEmail::create([
            'user_id' => $this->user->id,
            'email' => 'primary@example.com',
            'is_primary' => true,
            'verified_at' => now(),
        ]);
        UserEmail::create([
            'user_id' => $this->user->id,
            'email' => 'backup@example.com',
            'is_primary' => false,
            'verified_at' => now(),
        ]);

        // Act
        $result = $this->service->canBeDeleted('primary@example.com');

        // Assert
        $this->assertTrue($result);
    }
}
