<?php

namespace Tests\Unit;

use App\Domains\Security\UserManagement\Models\User;
use App\Mail\RecoveryCodesRegenerationConfirmation;
use Database\Factories\UserFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Mail\Mailables\Envelope;
use Tests\TestCase;

class RecoveryCodesRegenerationConfirmationTest extends TestCase
{
    use RefreshDatabase;

    public function test_constructor_sets_user_and_token(): void
    {
        // Arrange
        $user = UserFactory::new()->create();
        $token = 'test-token-123';

        // Act
        $mailable = new RecoveryCodesRegenerationConfirmation($user, $token);

        // Assert
        $this->assertInstanceOf(User::class, $mailable->user);
        $this->assertEquals($user->id, $mailable->user->id);
        $this->assertEquals($token, $mailable->token);
    }

    public function test_envelope_returns_correct_subject(): void
    {
        // Arrange
        $user = UserFactory::new()->create();
        $token = 'test-token-123';
        $mailable = new RecoveryCodesRegenerationConfirmation($user, $token);
        $appName = config('app.name');

        // Act
        $envelope = $mailable->envelope();

        // Assert
        $this->assertInstanceOf(Envelope::class, $envelope);
        $this->assertEquals("Recovery Codes Regeneration Confirmation - {$appName}", $envelope->subject);
    }

    public function test_attachments_returns_empty_array(): void
    {
        // Arrange
        $user = UserFactory::new()->create();
        $token = 'test-token';
        $mailable = new RecoveryCodesRegenerationConfirmation($user, $token);

        // Act
        $attachments = $mailable->attachments();

        // Assert
        $this->assertIsArray($attachments);
        $this->assertEmpty($attachments);
    }

    public function test_mailable_uses_queueable_trait(): void
    {
        // Arrange
        $user = UserFactory::new()->create();
        $token = 'test-token';

        // Act
        $mailable = new RecoveryCodesRegenerationConfirmation($user, $token);

        // Assert
        $this->assertTrue(method_exists($mailable, 'onQueue'));
        $this->assertTrue(method_exists($mailable, 'onConnection'));
    }

    public function test_mailable_uses_serializes_models_trait(): void
    {
        // Arrange
        $user = UserFactory::new()->create();
        $token = 'test-token';

        // Act
        $mailable = new RecoveryCodesRegenerationConfirmation($user, $token);

        // Assert
        $this->assertTrue(method_exists($mailable, 'getSerializedPropertyValue'));
    }
}
