<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class TestMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $name;
    public ?string $resetLink;

    /**
     * Create a new message instance.
     */
    public function __construct(string $name, ?string $resetLink = null)
    {
        $this->name = $name;
        $this->resetLink = $resetLink;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Reset Your Password'
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'email.test-mail',
            with: [
                'name' => $this->name,
                'resetLink' => $this->resetLink,
            ]
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        return [];
    }
}
