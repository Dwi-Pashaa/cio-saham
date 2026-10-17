<?php

namespace App\Mail;

use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CashNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $type;
    public string $mailSubject;
    public string $title;
    public array $details;
    public ?float $previousBalance;
    public ?float $currentBalance;
    public ?string $proofUrl;
    public ?string $notes;
    public ?string $localFilePath;
    public ?Setting $setting;

    /**
     * Create a new message instance.
     *
     * @param string      $type            'income' | 'outcome' | 'saving' | 'asset'
     * @param string      $mailSubject     Subject email
     * @param string      $title           Judul utama di dalam template
     * @param array       $details         List rincian transaksi [Label => Value]
     * @param float|null  $previousBalance Saldo kas sebelum transaksi
     * @param float|null  $currentBalance  Saldo kas sesudah transaksi
     * @param string|null $proofUrl        URL publik gambar / dokumen bukti
     * @param string|null $notes           Catatan transaksi
     * @param string|null $localFilePath   Path file lokal untuk lampiran email
     */
    public function __construct(
        string  $type,
        string  $mailSubject,
        string  $title,
        array   $details = [],
        ?float  $previousBalance = null,
        ?float  $currentBalance = null,
        ?string $proofUrl = null,
        ?string $notes = null,
        ?string $localFilePath = null
    ) {
        $this->type            = $type;
        $this->mailSubject     = $mailSubject;
        $this->title           = $title;
        $this->details         = $details;
        $this->previousBalance = $previousBalance;
        $this->currentBalance  = $currentBalance;
        $this->proofUrl        = $proofUrl;
        $this->notes           = $notes;
        $this->localFilePath   = $localFilePath;
        $this->setting         = Setting::first();
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $fromAddress = config('mail.from.address', 'finance@cionetwork.id');
        $fromName    = config('mail.from.name', config('app.name', 'PT CIO Network Solution'));

        return new Envelope(
            from: new Address($fromAddress, $fromName),
            subject: $this->mailSubject,
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
            view: 'emails.cash_notification',
            with: [
                'type'            => $this->type,
                'title'           => $this->title,
                'details'         => $this->details,
                'previousBalance' => $this->previousBalance,
                'currentBalance'  => $this->currentBalance,
                'proofUrl'        => $this->proofUrl,
                'notes'           => $this->notes,
                'setting'         => $this->setting,
                'dashboardUrl'    => $dashboardUrl,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        if (!empty($this->localFilePath) && file_exists($this->localFilePath)) {
            try {
                return [
                    Attachment::fromPath($this->localFilePath)
                        ->as(basename($this->localFilePath)),
                ];
            } catch (\Throwable $e) {
                // Jangan gagalkan pengiriman email jika attachment bermasalah
                return [];
            }
        }

        return [];
    }
}
