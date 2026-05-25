<?php

namespace App\Mail;

use App\Models\ReportSchedule;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MonthlyReportMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param  ReportSchedule  $schedule  メール送信元の配信予約
     * @param  string  $pdfBinary  PDF バイナリ
     * @param  string  $filename  PDF ファイル名
     */
    public function __construct(
        public readonly ReportSchedule $schedule,
        public readonly string $pdfBinary,
        public readonly string $filename,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            to: array_map(fn ($email) => new Address($email), $this->schedule->recipientList()),
            subject: $this->schedule->subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.monthly-report',
            with: [
                'body' => $this->schedule->body ?? '',
                'storeName' => $this->schedule->store->name,
                'companyName' => $this->schedule->store->company->name,
            ],
        );
    }

    public function attachments(): array
    {
        return [
            Attachment::fromData(fn () => $this->pdfBinary, $this->filename)
                ->withMime('application/pdf'),
        ];
    }
}
