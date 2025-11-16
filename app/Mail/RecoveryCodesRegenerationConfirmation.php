<?php

namespace App\Mail;

use App\Domains\Security\UserManagement\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RecoveryCodesRegenerationConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public User $user,
        public string $token
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Recovery Codes Regeneration Confirmation - '.config('app.name'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $confirmationUrl = route('user.settings.security.two-factor.recovery-codes.confirm', ['token' => $this->token]);

        return new Content(
            view: 'emails.recovery-codes-regeneration-confirmation',
            with: [
                'user' => $this->user,
                'confirmationUrl' => $confirmationUrl,
                'expiresAt' => now()->addHour()->format('M j, Y \a\t g:i A'),
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
