<?php

namespace App\Services;

use App\Models\Transfer;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MekariQontakService
{
    protected string $baseUrl;
    protected ?string $apiToken;
    protected ?string $clientId;
    protected ?string $clientSecret;
    protected ?string $channelIntegrationId;
    protected ?string $templateId;
    protected ?string $otpTemplateId;
    protected bool $enabled;

    public function __construct()
    {
        $this->baseUrl              = rtrim(config('services.qontak.base_url', 'https://api.mekari.com/qontak/chat'), '/');
        $this->apiToken             = config('services.qontak.api_token') ?: env('QONTAK_API_TOKEN');
        $this->clientId             = config('services.qontak.client_id');
        $this->clientSecret         = config('services.qontak.client_secret');
        $this->channelIntegrationId = config('services.qontak.channel_integration_id');
        $this->templateId           = config('services.qontak.template_id');
        $this->otpTemplateId        = config('services.qontak.otp_template_id') ?: env('QONTAK_OTP_TEMPLATE_ID');
        $this->enabled              = (bool) config('services.qontak.enabled', true);
    }

    // -------------------------------------------------------------------------
    // Mekari Authentication (HMAC-SHA256 & Direct API Token)
    // -------------------------------------------------------------------------

    /**
     * Get accurate GMT Date synchronized with Mekari / standard NTP web servers
     * to prevent clock-skew errors (401 Unauthorized caused by out-of-sync local clock).
     */
    protected function getServerDate(): string
    {
        // 1. Coba ambil dari WorldTimeAPI (mengembalikan waktu UTC nyata dari internet)
        try {
            $ch = curl_init('http://worldtimeapi.org/api/timezone/Etc/UTC');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 3);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0');
            $json = curl_exec($ch);
            curl_close($ch);

            if ($json) {
                $data = json_decode($json, true);
                if (!empty($data['unixtime'])) {
                    $realGmt = gmdate('D, d M Y H:i:s \G\M\T', (int) $data['unixtime']);
                    Log::info("Mekari Real Time diperoleh via WorldTimeAPI: [{$realGmt}]");
                    return $realGmt;
                }
            }
        } catch (\Throwable $e) {
            Log::warning('WorldTimeAPI error: ' . $e->getMessage());
        }

        // 2. Coba via TimeAPI.io fallback
        try {
            $ch = curl_init('https://timeapi.io/api/time/current/zone?timeZone=UTC');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 3);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $json = curl_exec($ch);
            curl_close($ch);

            if ($json) {
                $data = json_decode($json, true);
                if (!empty($data['dateTime'])) {
                    $realGmt = gmdate('D, d M Y H:i:s \G\M\T', strtotime($data['dateTime']));
                    Log::info("Mekari Real Time diperoleh via TimeAPI: [{$realGmt}]");
                    return $realGmt;
                }
            }
        } catch (\Throwable $e) {
            Log::warning('TimeAPI error: ' . $e->getMessage());
        }

        return gmdate('D, d M Y H:i:s \G\M\T');
    }

    /**
     * Generate headers for Mekari API request.
     * Supports both Mekari Developer HMAC-SHA256 signature and direct Bearer API Token.
     */
    protected function getMekariHeaders(string $method, string $fullUrl): array
    {
        // 1. Jika ada QONTAK_API_TOKEN atau token Bearer langsung di .env
        $bearerToken = $this->apiToken ?: config('services.qontak.api_token') ?: env('QONTAK_API_TOKEN');
        if (!empty($bearerToken)) {
            return [
                'Authorization' => 'Bearer ' . $bearerToken,
                'Accept'        => 'application/json',
                'Content-Type'  => 'application/json',
            ];
        }

        // 2. Mekari Developer Center HMAC-SHA256 Header
        $parsedUrl     = parse_url($fullUrl);
        $path          = $parsedUrl['path'] ?? '/';
        $query         = isset($parsedUrl['query']) ? '?' . $parsedUrl['query'] : '';
        $pathWithQuery = $path . $query;

        // Gunakan waktu yang tersinkronisasi presisi dengan server Mekari
        $datetime    = $this->getServerDate();
        $requestLine = strtoupper($method) . " {$pathWithQuery} HTTP/1.1";
        $payload     = "date: {$datetime}\n{$requestLine}";

        $rawHmac   = hash_hmac('sha256', $payload, (string) $this->clientSecret, true);
        $signature = base64_encode($rawHmac);

        $authHeader = sprintf(
            'hmac username="%s", algorithm="hmac-sha256", headers="date request-line", signature="%s"',
            $this->clientId,
            $signature
        );

        return [
            'Date'          => $datetime,
            'Authorization' => $authHeader,
            'Accept'        => 'application/json',
            'Content-Type'  => 'application/json',
        ];
    }

    // -------------------------------------------------------------------------
    // Format Nomor Telepon
    // -------------------------------------------------------------------------

    /**
     * Format nomor telepon Indonesia ke format internasional (62xxx).
     */
    public function formatPhoneNumber(?string $phone): ?string
    {
        if (empty($phone)) {
            return null;
        }

        $cleaned = preg_replace('/\D/', '', $phone);

        if (str_starts_with($cleaned, '0')) {
            $cleaned = '62' . substr($cleaned, 1);
        } elseif (str_starts_with($cleaned, '8')) {
            $cleaned = '62' . $cleaned;
        }

        return $cleaned;
    }

    // -------------------------------------------------------------------------
    // Kirim Notifikasi Dividen ke Investor
    // -------------------------------------------------------------------------

    /**
     * Kirim notifikasi WhatsApp distribusi dividen ke investor.
     *
     * Template message parameters:
     *   {{1}} Nama Investor
     *   {{2}} Nominal Transfer  (Rp xxx)
     *   {{3}} Bank / Metode
     *   {{4}} Tanggal Transfer
     *   {{5}} Kode Transaksi / No. Referensi
     *   {{6}} Catatan / Periode
     *   {{7}} Link Invoice Publik (Aman Ber-Token)
     *
     * @param Transfer $transfer
     * @return array{success: bool, message: string, data?: mixed}
     */
    public function sendDividendNotification(Transfer $transfer): array
    {
        $transfer->loadMissing(['investor', 'admin']);
        $investor = $transfer->investor;

        if (!$investor) {
            return [
                'success' => false,
                'message' => 'Data investor tidak ditemukan untuk transfer ini.',
            ];
        }

        $formattedPhone = $this->formatPhoneNumber($investor->phone);
        if (empty($formattedPhone)) {
            return [
                'success' => false,
                'message' => "Investor {$investor->name} belum memiliki nomor WhatsApp terdaftar. Tambahkan nomor di profil investor.",
            ];
        }

        $formattedAmount = 'Rp ' . number_format($transfer->amount, 0, ',', '.');
        $transferDate    = Carbon::parse($transfer->transfer_date)->translatedFormat('d F Y');
        $notes           = $transfer->notes ?: 'Distribusi Dividen Bagi Hasil';
        $code            = $transfer->code ?: ('TRF-' . $transfer->id);
        $invoiceUrl      = $transfer->getInvoiceUrl();

        $parameters = [
            'body' => [
                ['key' => '1', 'value' => 'name',        'value_text' => $investor->name],
                ['key' => '2', 'value' => 'amount',      'value_text' => $formattedAmount],
                ['key' => '3', 'value' => 'bank',        'value_text' => $transfer->payment_method],
                ['key' => '4', 'value' => 'date',        'value_text' => $transferDate],
                ['key' => '5', 'value' => 'code',        'value_text' => $code],
                ['key' => '6', 'value' => 'notes',       'value_text' => $notes],
                ['key' => '7', 'value' => 'invoice_url', 'value_text' => $invoiceUrl],
            ],
        ];

        return $this->sendWhatsAppTemplate(
            toNumber: $formattedPhone,
            toName:   $investor->name,
            parameters: $parameters,
        );
    }

    /**
     * Kirim notifikasi Email dividen ke investor.
     *
     * @param Transfer $transfer
     * @return array{success: bool, message: string}
     */
    public function sendEmailDividendNotification(Transfer $transfer): array
    {
        $transfer->loadMissing(['investor', 'admin']);
        $investor = $transfer->investor;

        if (!$investor || empty($investor->email)) {
            return [
                'success' => false,
                'message' => 'Email investor tidak ditemukan.',
            ];
        }

        $formattedAmount = 'Rp ' . number_format($transfer->amount, 0, ',', '.');
        $invoiceUrl      = $transfer->getInvoiceUrl();
        $code            = $transfer->code ?: ('INV-TRF-' . $transfer->id);

        try {
            // Mengirim notifikasi email HTML responsif & profesional
            \Illuminate\Support\Facades\Mail::to($investor->email)
                ->send(new \App\Mail\DividendTransferMail($transfer));

            Log::info("Notifikasi email HTML dividen berhasil dikirim ke {$investor->email}");

            return [
                'success' => true,
                'message' => "Email notifikasi dividen berhasil dikirim ke {$investor->email}.",
            ];
        } catch (\Throwable $e) {
            Log::error('Gagal mengirim email dividen: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal mengirim email dividen: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Dispatch notifikasi dividen sesuai saluran di Pengaturan Sistem (WhatsApp, Email, Both).
     *
     * @param Transfer    $transfer
     * @param string|null $channelOverride 'whatsapp' | 'email' | 'both' | 'none'
     * @return array{success: bool, message: string, wa?: mixed, email?: mixed}
     */
    public function dispatchNotification(Transfer $transfer, ?string $channelOverride = null): array
    {
        $setting = \App\Models\Setting::first();
        $channel = $channelOverride ?? $setting?->notification_channel ?? 'whatsapp';

        if ($channel === 'none') {
            return ['success' => true, 'message' => 'Notifikasi dinonaktifkan di pengaturan sistem.'];
        }

        $results = [];
        $messages = [];

        if (in_array($channel, ['whatsapp', 'both'])) {
            $waResult = $this->sendDividendNotification($transfer);
            $results['wa'] = $waResult;
            $messages[] = $waResult['message'];
        }

        if (in_array($channel, ['email', 'both'])) {
            $emailResult = $this->sendEmailDividendNotification($transfer);
            $results['email'] = $emailResult;
            $messages[] = $emailResult['message'];
        }

        return [
            'success'  => true,
            'message'  => implode(' | ', $messages),
            'channels' => $results,
        ];
    }

    // -------------------------------------------------------------------------
    // Core: Kirim Template WA via Mekari Qontak Direct Broadcast API
    // -------------------------------------------------------------------------

    /**
     * Kirim WhatsApp template message via Mekari Qontak API.
     *
     * @param string      $toNumber   Nomor tujuan format 62xxx
     * @param string      $toName     Nama penerima
     * @param array       $parameters Template variable parameters
     * @param string|null $templateId Override template ID (opsional)
     * @param string|null $channelId  Override channel ID (opsional)
     * @return array{success: bool, message: string, data?: mixed}
     */
    public function sendWhatsAppTemplate(
        string  $toNumber,
        string  $toName,
        array   $parameters = [],
        ?string $templateId = null,
        ?string $channelId  = null
    ): array {
        if (!$this->enabled) {
            Log::info("Qontak WhatsApp dinonaktifkan. Simulasi sukses untuk {$toNumber}.");
            return [
                'success'   => true,
                'message'   => 'Fitur WhatsApp Gateway dinonaktifkan di .env (QONTAK_ENABLED=false). Simulasi sukses.',
                'simulated' => true,
            ];
        }

        $template = $templateId ?? $this->templateId;
        $channel  = $channelId  ?? $this->channelIntegrationId;

        if (empty($template)) {
            return [
                'success' => false,
                'message' => 'QONTAK_TEMPLATE_ID belum dikonfigurasi di .env.',
            ];
        }

        if (empty($channel)) {
            return [
                'success' => false,
                'message' => 'QONTAK_CHANNEL_INTEGRATION_ID belum dikonfigurasi di .env.',
            ];
        }

        $apiToken = $this->apiToken ?: env('QONTAK_API_TOKEN');
        if (empty($apiToken) && (empty($this->clientId) || empty($this->clientSecret))) {
            return [
                'success' => false,
                'message' => 'Kredensial Qontak (QONTAK_API_TOKEN atau CLIENT_ID & CLIENT_SECRET) belum dikonfigurasi di .env.',
            ];
        }

        // Jika menggunakan Bearer API Token (dari Qontak Omnichannel), gunakan endpoint resmi service-chat.qontak.com
        // Jika menggunakan HMAC (Mekari Developers), gunakan api.mekari.com/qontak/chat
        $endpoint = !empty($apiToken)
            ? 'https://service-chat.qontak.com/api/open/v1/broadcasts/whatsapp/direct'
            : "{$this->baseUrl}/v1/broadcasts/whatsapp/direct";

        $payload = [
            'to_number'               => $toNumber,
            'to_name'                 => $toName,
            'message_template_id'     => $template,
            'channel_integration_id'  => $channel,
            'language'                => ['code' => 'id'],
            'parameters'              => $parameters,
        ];

        try {
            $headers = $this->getMekariHeaders('POST', $endpoint);

            $response = Http::withHeaders($headers)
                ->timeout(15)
                ->post($endpoint, $payload);

            if ($response->successful()) {
                Log::info("Qontak WA berhasil dikirim ke {$toNumber}", [
                    'response' => $response->json(),
                ]);

                return [
                    'success' => true,
                    'message' => "Pesan WhatsApp berhasil dikirim ke {$toName} ({$toNumber}).",
                    'data'    => $response->json(),
                ];
            }

            Log::error('Qontak API error: ' . $response->body(), [
                'endpoint' => $endpoint,
                'headers'  => $headers,
                'payload'  => $payload,
                'status'   => $response->status(),
            ]);

            return [
                'success' => false,
                'message' => 'Gagal mengirim pesan via Mekari Qontak (HTTP ' . $response->status() . '): ' . $response->body(),
                'status'  => $response->status(),
            ];
        } catch (\Throwable $e) {
            Log::error('Qontak Exception: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'message' => 'Terjadi kesalahan sistem saat menghubungi Mekari Qontak: ' . $e->getMessage(),
            ];
        }
    }

    // -------------------------------------------------------------------------
    // Kirim Notifikasi Kode OTP Reset Password ke User (Meta Authentication)
    // -------------------------------------------------------------------------

    /**
     * Kirim pesan WhatsApp OTP menggunakan Template Authentication Meta.
     *
     * @param User   $user
     * @param string $otpCode
     * @return array{success: bool, message: string, data?: mixed}
     */
    public function sendOtpNotification(User $user, string $otpCode): array
    {
        $phone = $user->phone;
        if (empty($phone)) {
            return [
                'success' => false,
                'message' => 'Nomor WhatsApp pengguna belum terdaftar.',
            ];
        }

        $formattedPhone = $this->formatPhoneNumber($phone);
        if (empty($formattedPhone)) {
            return [
                'success' => false,
                'message' => 'Format nomor WhatsApp pengguna tidak valid.',
            ];
        }

        $templateId = $this->otpTemplateId ?: $this->templateId;
        if (empty($templateId)) {
            return [
                'success' => false,
                'message' => 'QONTAK_OTP_TEMPLATE_ID belum dikonfigurasi di .env.',
            ];
        }

        // Parameter Meta Authentication (Copy Code / URL) Template
        $parameters = [
            'body' => [
                ['key' => '1', 'value' => 'otp_code', 'value_text' => $otpCode],
            ],
            'buttons' => [
                [
                    'index' => '0',
                    'type'  => 'url',
                    'value' => $otpCode,
                ],
            ],
        ];

        return $this->sendWhatsAppTemplate(
            toNumber:   $formattedPhone,
            toName:     $user->name ?: $user->username,
            parameters: $parameters,
            templateId: $templateId,
        );
    }
}

