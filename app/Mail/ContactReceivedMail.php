<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * お問い合わせ受信時に運営宛に送るメール。
 */
class ContactReceivedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public array $data) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '【laiweb-dash お問い合わせ】' . $this->data['name'],
            replyTo: [$this->data['email']],
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.contact-received',
            with: ['data' => $this->data],
        );
    }
}
