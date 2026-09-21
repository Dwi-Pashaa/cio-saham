<?php

namespace App\Mail;

use App\Models\Setting;
use App\Models\Shareholder;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DailyFinancialReportMail extends Mailable
{
    use Queueable, SerializesModels;

    public Shareholder $shareholder;
    public array $dailyData;
    public float $personalProfit;
    public ?Setting $setting;

    /**
     * Create a new message instance.
     */
    public function __construct(Shareholder $shareholder, array $dailyData, float $personalProfit)
    {
        $this->shareholder    = $shareholder->loadMissing(['holdings', 'user']);
        $this->dailyData      = $dailyData;
        $this->personalProfit = $personalProfit;
        $this->setting        = Setting::first();
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $companyName = $this->setting->company_name ?? 'CIO Network Solution';
        $reportDate  = !empty($this->dailyData['date']) 
            ? Carbon::parse($this->dailyData['date'])->translatedFormat('d F Y') 
            : Carbon::now()->translatedFormat('d F Y');

        $fromAddress = config('mail.from.address', 'finance@cionetworksolution.com');
        $fromName    = config('mail.from.name', config('app.name', 'CIO Investor Portal'));

        return new Envelope(
            from: new Address($fromAddress, $fromName),
            subject: "📊 Laporan Finansial Harian [{$reportDate}] - {$companyName}",
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
            view: 'emails.daily_financial_report',
            with: [
                'shareholder'    => $this->shareholder,
                'dailyData'      => $this->dailyData,
                'personalProfit' => $this->personalProfit,
                'setting'        => $this->setting,
                'dashboardUrl'   => $dashboardUrl,
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
        // Graceful fallback jika library dompdf belum terinstall di server hosting
        if (!class_exists('\Barryvdh\DomPDF\Facade\Pdf')) {
            return [];
        }

        try {
            $reportDate = !empty($this->dailyData['date']) 
                ? Carbon::parse($this->dailyData['date'])->format('Y-m-d') 
                : Carbon::now()->format('Y-m-d');
                
            $safeName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $this->shareholder->name ?? 'Investor');
            $fileName = "Laporan_Finansial_Harian_{$reportDate}_{$safeName}.pdf";

            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.daily_financial_report', [
                'shareholder'    => $this->shareholder,
                'dailyData'      => $this->dailyData,
                'personalProfit' => $this->personalProfit,
                'setting'        => $this->setting,
            ])->setPaper('a4', 'portrait');

            return [
                \Illuminate\Mail\Mailables\Attachment::fromData(
                    fn () => $pdf->output(),
                    $fileName
                )->withMime('application/pdf'),
            ];
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Gagal membuat attachment PDF laporan: " . $e->getMessage());
            return [];
        }
    }
}
