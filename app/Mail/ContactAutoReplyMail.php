<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * お問い合わせ送信者への自動返信メール。
 */
class ContactAutoReplyMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public array $data) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '【laiweb-dash】お問い合わせを受け付けました',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.contact-auto-reply',
            with: ['data' => $this->data],
        );
    }
}
