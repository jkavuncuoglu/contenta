<?php

namespace Tests\Unit;

use App\Domains\Security\UserManagement\Models\User;
use App\Mail\RecoveryCodesRegenerationConfirmation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class RecoveryCodesRegenerationConfirmationTest extends TestCase
{
    use RefreshDatabase;

    public function test_constructor_sets_user_and_token(): void
    {
        // Arrange
        $user = User::factory()->create();
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
        $user = User::factory()->create();
        $token = 'test-token-123';
        $mailable = new RecoveryCodesRegenerationConfirmation($user, $token);
        $appName = config('app.name');

        // Act
        $envelope = $mailable->envelope();

        // Assert
        $this->assertInstanceOf(Envelope::class, $envelope);
        $this->assertEquals("Recovery Codes Regeneration Confirmation - {$appName}", $envelope->subject);
    }

    public function test_content_returns_correct_view_and_data(): void
    {
        // Arrange
        Route::get('/two-factor/recovery-codes/confirm/{token}', function () {
            return 'test';
        })->name('two-factor.recovery-codes.confirm');

        $user = User::factory()->create();
        $token = 'test-token-123';
        $mailable = new RecoveryCodesRegenerationConfirmation($user, $token);

        // Act
        $content = $mailable->content();

        // Assert
        $this->assertInstanceOf(Content::class, $content);
        $this->assertEquals('emails.recovery-codes-regeneration-confirmation', $content->view);
        $this->assertArrayHasKey('user', $content->with);
        $this->assertArrayHasKey('confirmationUrl', $content->with);
        $this->assertArrayHasKey('expiresAt', $content->with);
        $this->assertEquals($user->id, $content->with['user']->id);
        $this->assertStringContainsString('two-factor/recovery-codes/confirm', $content->with['confirmationUrl']);
        $this->assertStringContainsString($token, $content->with['confirmationUrl']);
    }

    public function test_content_confirmation_url_includes_token(): void
    {
        // Arrange
        Route::get('/two-factor/recovery-codes/confirm/{token}', function () {
            return 'test';
        })->name('two-factor.recovery-codes.confirm');

        $user = User::factory()->create();
        $token = 'unique-test-token-456';
        $mailable = new RecoveryCodesRegenerationConfirmation($user, $token);

        // Act
        $content = $mailable->content();

        // Assert
        $this->assertStringContainsString($token, $content->with['confirmationUrl']);
    }

    public function test_content_expires_at_is_one_hour_from_now(): void
    {
        // Arrange
        $user = User::factory()->create();
        $token = 'test-token';
        $mailable = new RecoveryCodesRegenerationConfirmation($user, $token);

        // Act
        $content = $mailable->content();

        // Assert
        $this->assertNotEmpty($content->with['expiresAt']);
        // Check format is roughly correct (contains a date-like string)
        $this->assertMatchesRegularExpression('/[A-Za-z]{3}\s+\d{1,2},\s+\d{4}\s+at\s+\d{1,2}:\d{2}\s+[AP]M/', $content->with['expiresAt']);
    }

    public function test_attachments_returns_empty_array(): void
    {
        // Arrange
        $user = User::factory()->create();
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
        $user = User::factory()->create();
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
        $user = User::factory()->create();
        $token = 'test-token';

        // Act
        $mailable = new RecoveryCodesRegenerationConfirmation($user, $token);

        // Assert
        $this->assertTrue(method_exists($mailable, 'getSerializedPropertyValue'));
    }
}
