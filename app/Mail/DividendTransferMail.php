<?php

namespace App\Mail;

use App\Models\Setting;
use App\Models\Transfer;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DividendTransferMail extends Mailable
{
    use Queueable, SerializesModels;

    public $transfer;
    public ?Setting $setting;

    /**
     * Create a new message instance.
     */
    public function __construct($transfer)
    {
        $this->transfer = $transfer;
        $this->setting  = Setting::first();
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $companyName = $this->setting->company_name ?? 'PT CIO Network Solution';
        $code        = $this->transfer->code ?: ('INV-TRF-' . $this->transfer->id);

        $fromAddress = config('mail.from.address', 'finance@cionetwork.id');
        $fromName    = config('mail.from.name', config('app.name', 'PT CIO Network Solution'));

        return new Envelope(
            from: new Address($fromAddress, $fromName),
            subject: "💰 Bukti Pembagian Dividen Saham [{$code}] - {$companyName}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $appUrl = config('app.url') ?: url('/');
        $dashboardUrl = rtrim($appUrl, '/') . '/dashboard';

        return new Content(
            view: 'emails.dividend_transfer',
            with: [
                'transfer'     => $this->transfer,
                'setting'      => $this->setting,
                'dashboardUrl' => $dashboardUrl,
            ],
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
