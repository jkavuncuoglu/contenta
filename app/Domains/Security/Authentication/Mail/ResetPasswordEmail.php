<?php

namespace App\Domains\Security\Authentication\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ResetPasswordEmail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        private $id,
        private $email,
        private $verificationUrl,
        private $verificationCode
    ) {
        //
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Reset your password',
            from: config('mail.from.address')
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.forgot-password',
            with: [
                'id' => $this->id,
                'email' => $this->email,
                'verificationUrl' => $this->verificationUrl,
                'verificationCode' => $this->verificationCode,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
