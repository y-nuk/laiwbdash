<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ClientInvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public User $user) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '【laiweb-dash】' . config('app.name') . ' へのご招待',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.client-invitation',
            with: [
                'user' => $this->user,
                'acceptUrl' => route('invitation.show', ['token' => $this->user->invitation_token]),
                'companyName' => $this->user->company?->name,
            ],
        );
    }
}
