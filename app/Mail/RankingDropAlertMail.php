<?php

namespace App\Mail;

use App\Models\Alert;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RankingDropAlertMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Alert $alert,
        public readonly array $triggers,
    ) {}

    public function envelope(): Envelope
    {
        $store = $this->alert->store;
        return new Envelope(
            from: new Address(
                config('mail.report_from.address'),
                config('mail.report_from.name'),
            ),
            replyTo: [new Address(
                config('mail.report_reply_to.address'),
                config('mail.report_reply_to.name'),
            )],
            to: array_map(fn ($e) => new Address($e), $this->alert->recipientList()),
            subject: "【laiweb-dash】順位アラート - {$store->name} ({$this->alert->name})",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.ranking-alert',
            with: [
                'alertName' => $this->alert->name,
                'alertType' => Alert::TYPES[$this->alert->alert_type] ?? $this->alert->alert_type,
                'threshold' => $this->alert->threshold,
                'storeName' => $this->alert->store->name,
                'companyName' => $this->alert->store->company->name,
                'triggers' => $this->triggers,
                'adminUrl' => url('/admin/stores/' . $this->alert->store_id . '/rankings'),
            ],
        );
    }
}
