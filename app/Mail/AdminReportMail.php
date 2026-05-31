<?php

namespace App\Mail;

use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdminReportMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public array $report,
        public User $admin,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'AutoGest — '.$this->report['title'],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.admin-report',
            with: [
                'title' => $this->report['title'],
                'adminName' => $this->admin->name,
                'summary' => $this->report['summary'],
                'filtersLabel' => $this->report['filters_label'],
                'generatedAt' => now()->format('d/m/Y H:i'),
            ],
        );
    }

    public function attachments(): array
    {
        $pdf = Pdf::loadView('admin.reports.pdf', [
            'title' => $this->report['title'],
            'summary' => $this->report['summary'],
            'columns' => $this->report['columns'],
            'rows' => $this->report['rows'],
            'filtersLabel' => $this->report['filters_label'],
            'generatedAt' => now()->format('d/m/Y H:i'),
        ]);

        $filename = 'reporte-'.$this->report['type'].'-'.now()->format('Y-m-d').'.pdf';

        return [
            Attachment::fromData(fn () => $pdf->output(), $filename)
                ->withMime('application/pdf'),
        ];
    }
}
