<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewUserPasswordMail extends Mailable
{
    use Queueable, SerializesModels;

    public $userName;
    public $password;

    public function __construct($userName, $password)
    {
        $this->userName = $userName;
        $this->password = $password;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Tu cuenta ha sido creada en Cacao San José',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.new-user-password',
        );
    }
}
