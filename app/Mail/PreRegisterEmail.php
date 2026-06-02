<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PreRegisterEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $temporaryPassword;
    public $verificationCode;

    public function __construct($user, $temporaryPassword, $verificationCode)
    {
        $this->user = $user;
        $this->temporaryPassword = $temporaryPassword;
        $this->verificationCode = $verificationCode;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Acceso temporal a OFFICIUM',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.preRegisterEmail',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
