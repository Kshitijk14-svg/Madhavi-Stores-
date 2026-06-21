<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class OtpMail extends Mailable
{

    public string $otp;
    public string $purpose;

    public function __construct(string $otp, string $purpose)
    {
        $this->otp     = $otp;
        $this->purpose = $purpose;
    }

    public function envelope(): Envelope
    {
        $subject = $this->purpose === 'reset'
            ? 'Your Password Reset Code — Madhavi Stores'
            : 'Verify Your Email — Madhavi Stores';

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.otp');
    }
}
