<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Queue\SerializesModels;
use Carbon\Carbon;

class MonthlyBillingSummaryMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $monthYear;
    public string $readableMonth;
    public $bills;
    public array $summary;
    public string $adminDashboardUrl;
    public string $generatedAt;

    public function __construct(string $monthYear, $bills, array $summary)
    {
        $this->monthYear = $monthYear;
        $this->bills = $bills;
        $this->summary = $summary;

        try {
            $this->readableMonth = Carbon::createFromFormat('m-Y', $monthYear)->format('F Y');
        } catch (\Throwable $e) {
            $this->readableMonth = $monthYear;
        }

        $this->generatedAt = Carbon::now()->timezone('Asia/Kolkata')->format('d M Y, h:i A');
        $this->adminDashboardUrl = rtrim(env('FRONTEND_URL', 'http://localhost:5173'), '/') . '/admin/dashboard';
    }

    public function envelope(): Envelope
    {
        $fromAddress = config('mail.from.address', 'billing@crownoragroup.co.in');
        $fromName = config('mail.from.name', 'Crownora Group');

        return new Envelope(
            from: new Address($fromAddress, $fromName),
            subject: "🍽️ Crownora Group - Monthly Customer Bills Statement ({$this->readableMonth})",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.monthly_billing_summary',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}

