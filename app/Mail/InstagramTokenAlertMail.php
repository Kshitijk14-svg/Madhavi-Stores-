<?php

namespace App\Mail;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InstagramTokenAlertMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $error;
    public ?Carbon $expiresAt;

    public function __construct(string $error, ?Carbon $expiresAt = null)
    {
        $this->error     = $error;
        $this->expiresAt = $expiresAt;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'ACTION NEEDED — Instagram token refresh failing',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.instagram-token-alert',
            with: [
                'error'     => $this->error,
                'expiresAt' => $this->expiresAt,
            ],
        );
    }
}
