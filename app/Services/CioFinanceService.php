<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Exception;

class CioFinanceService
{
    protected ?string $baseUrl;
    protected ?string $clientId;
    protected ?string $keyId;
    protected ?string $secretKey;
    protected int $timeout;

    public function __construct()
    {
        $this->baseUrl   = rtrim(config('services.cio_finance.base_url') ?? '', '/');
        $this->clientId  = config('services.cio_finance.client_id');
        $this->keyId     = config('services.cio_finance.key_id');
        $this->secretKey = config('services.cio_finance.secret_key');
        $this->timeout   = (int) (config('services.cio_finance.timeout') ?? 5);
    }

    /**
     * Memeriksa apakah konfigurasi API Finance sudah terisi lengkap.
     */
    public function isConfigured(): bool
    {
        return !empty($this->baseUrl) &&
               !empty($this->clientId) &&
               !empty($this->keyId) &&
               !empty($this->secretKey);
    }

    /**
     * Membuat HMAC-SHA256 Headers sesuai spesifikasi API Finance CIO.
     */
    public function generateHeaders(string $method, string $path, array $body = []): array
    {
        $timestamp = (string) time();
        $nonce     = bin2hex(random_bytes(16));
        $bodyJson  = !empty($body) ? json_encode($body) : '';
        $bodyHash  = hash('sha256', $bodyJson);
        $cleanPath = '/' . ltrim($path, '/');

        // Canonical String sesuai HmacSignatureService server:
        // [METHOD, PATH_WITH_QUERY, CLIENT_ID, KEY_ID, TIMESTAMP, NONCE, SHA256_BODY_HASH]
        $canonical = implode("\n", [
            strtoupper($method),
            $cleanPath,
            (string) $this->clientId,
            (string) $this->keyId,
            $timestamp,
            $nonce,
            $bodyHash,
        ]);

        $signature = base64_encode(hash_hmac('sha256', $canonical, (string) ($this->secretKey ?? ''), true));

        return [
            'Content-Type' => 'application/json',
            'Accept'       => 'application/json',
            'X-Client-ID'  => (string) $this->clientId,
            'X-Key-ID'     => (string) $this->keyId,
            'X-Timestamp'  => $timestamp,
            'X-Nonce'      => $nonce,
            'X-Signature'  => $signature,
        ];
    }

    /**
     * Mengambil data Saldo (Manual & Xendit) serta status channel dari API Finance CIO.
     *
     * @param bool $fresh Jika true, bypass cache.
     * @return array
     */
    public function getBalance(bool $fresh = false): array
    {
        $defaultFallback = [
            'is_connected'   => false,
            'status'         => 'not_configured',
            'message'        => 'API Finance belum dikonfigurasi',
            'data'           => [
                'client_code'    => '-',
                'client_name'    => '-',
                'balance'        => '0.00',
                'balance_manual' => '0.00',
                'balance_xendit' => '0.00',
                'total_balance'  => '0.00',
                'channel_status' => [
                    'manual' => false,
                    'xendit' => false,
                ],
                'retrieved_at'   => null,
            ],
            'error_detail'   => null,
        ];

        if (!$this->isConfigured()) {
            return $defaultFallback;
        }

        $cacheKey = 'cio_finance_balance_data';

        if (!$fresh) {
            try {
                if (Cache::has($cacheKey)) {
                    return Cache::get($cacheKey);
                }
            } catch (\Throwable $e) {
                // Ignore cache read failures and fallback to live fetch
            }
        }

        $path = '/api/v1/balance';
        $url  = $this->baseUrl . $path;

        try {
            $headers = $this->generateHeaders('GET', $path);

            $response = Http::timeout($this->timeout)
                ->withHeaders($headers)
                ->get($url);

            if ($response->successful()) {
                $resData = $response->json();
                $data = $resData['data'] ?? [];

                $result = [
                    'is_connected' => true,
                    'status'       => 'success',
                    'message'      => $resData['message'] ?? 'Berhasil mengambil data saldo',
                    'data'         => [
                        'client_code'    => $data['client_code'] ?? '-',
                        'client_name'    => $data['client_name'] ?? '-',
                        'balance'        => (string) ($data['balance'] ?? '0.00'),
                        'balance_manual' => (string) ($data['balance_manual'] ?? '0.00'),
                        'balance_xendit' => (string) ($data['balance_xendit'] ?? '0.00'),
                        'total_balance'  => (string) ($data['total_balance'] ?? ($data['balance'] ?? '0.00')),
                        'channel_status' => [
                            'manual' => (bool) ($data['channel_status']['manual'] ?? false),
                            'xendit' => (bool) ($data['channel_status']['xendit'] ?? false),
                        ],
                        'retrieved_at'   => $data['retrieved_at'] ?? now()->toIso8601String(),
                    ],
                    'error_detail' => null,
                ];

                // Cache selama 30 detik untuk respon cepat
                try {
                    Cache::put($cacheKey, $result, 30);
                } catch (\Throwable $e) {
                    // Ignore cache write failures
                }

                return $result;
            }

            Log::warning('CIO Finance API responded with error', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);

            return [
                'is_connected' => false,
                'status'       => 'error',
                'message'      => 'Gagal memuat saldo dari server Finance (HTTP ' . $response->status() . ')',
                'data'         => $defaultFallback['data'],
                'error_detail' => $response->json() ?? $response->body(),
            ];

        } catch (Exception $e) {
            Log::error('Error connecting to CIO Finance API', [
                'error' => $e->getMessage(),
            ]);

            return [
                'is_connected' => false,
                'status'       => 'offline',
                'message'      => 'Server API Finance tidak dapat dihubungi',
                'data'         => $defaultFallback['data'],
                'error_detail' => $e->getMessage(),
            ];
        }
    }

    /**
     * Memotong saldo di API Finance CIO (Saldo Manual atau Saldo Xendit).
     *
     * @param float       $amount       Nominal yang akan dipotong
     * @param string      $balanceType  'manual' atau 'xendit'
     * @param string      $referenceId  ID Referensi ber-prefix INV-
     * @param string      $description  Deskripsi pemotongan saldo
     * @param string|null $category     Kategori pengeluaran (default: 'Dividen')
     * @param string|null $note         Catatan tambahan
     * @return array{success: bool, message: string, data?: mixed, error_code?: string}
     */
    public function deductBalance(
        float $amount,
        string $balanceType,
        string $referenceId,
        string $description,
        ?string $category = 'Dividen',
        ?string $note = null
    ): array {
        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'message' => 'API Finance belum dikonfigurasi pada .env.',
            ];
        }

        $path = '/api/v1/balance/deduct';
        $url  = $this->baseUrl . $path;

        // Pastikan reference ID memiliki prefix INV- untuk standarisasi Multi-Web
        if (!str_starts_with($referenceId, 'INV-')) {
            $referenceId = 'INV-' . $referenceId;
        }

        $body = [
            'amount'       => (float) $amount,
            'balance_type' => strtolower($balanceType),
            'reference_id' => $referenceId,
            'description'  => $description,
            'category'     => $category ?? 'Dividen',
            'note'         => $note ?? 'Distribusi bagi hasil dividen investor',
        ];

        try {
            $headers = $this->generateHeaders('POST', $path, $body);

            $response = Http::timeout($this->timeout)
                ->withHeaders($headers)
                ->post($url, $body);

            $resJson = $response->json();

            if ($response->successful() && (($resJson['status'] ?? '') === 'success' || ($resJson['success'] ?? false) === true)) {
                // Invalidate balance cache agar pembacaan saldo berikutnya akurat
                try {
                    Cache::forget('cio_finance_balance_data');
                } catch (\Throwable $e) {}

                Log::info('Berhasil memotong saldo Finance CIO', [
                    'reference_id' => $referenceId,
                    'balance_type' => $balanceType,
                    'amount'       => $amount,
                    'data'         => $resJson['data'] ?? [],
                ]);

                return [
                    'success' => true,
                    'message' => $resJson['message'] ?? 'Saldo berhasil dipotong',
                    'data'    => $resJson['data'] ?? [],
                ];
            }

            $errorMsg = $resJson['message'] ?? ('Gagal memotong saldo (HTTP ' . $response->status() . ')');
            Log::warning('Gagal memotong saldo Finance CIO', [
                'status'   => $response->status(),
                'response' => $resJson ?? $response->body(),
            ]);

            return [
                'success'    => false,
                'message'    => $errorMsg,
                'error_code' => $resJson['error_code'] ?? 'DEDUCT_FAILED',
                'data'       => $resJson['data'] ?? null,
            ];

        } catch (Exception $e) {
            Log::error('Exception saat memotong saldo Finance CIO: ' . $e->getMessage(), [
                'reference_id' => $referenceId,
            ]);

            return [
                'success' => false,
                'message' => 'Terjadi kesalahan sistem saat memotong saldo Finance: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Mengembalikan (Refund) saldo ke API Finance CIO jika transfer/transaksi gagal.
     *
     * @param float  $amount       Nominal yang dikembalikan
     * @param string $balanceType  'manual' atau 'xendit'
     * @param string $referenceId  ID Referensi Refund ber-prefix INV-
     * @param string $description  Deskripsi pengembalian dana
     * @param string $reason       Alasan rollback
     * @return array{success: bool, message: string, data?: mixed}
     */
    public function refundBalance(
        float $amount,
        string $balanceType,
        string $referenceId,
        string $description,
        string $reason
    ): array {
        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'message' => 'API Finance belum dikonfigurasi.',
            ];
        }

        $path = '/api/v1/balance/refund';
        $url  = $this->baseUrl . $path;

        if (!str_starts_with($referenceId, 'INV-')) {
            $referenceId = 'INV-' . $referenceId;
        }

        $body = [
            'amount'       => (float) $amount,
            'balance_type' => strtolower($balanceType),
            'reference_id' => $referenceId,
            'description'  => $description,
            'reason'       => $reason,
        ];

        try {
            $headers = $this->generateHeaders('POST', $path, $body);

            $response = Http::timeout($this->timeout)
                ->withHeaders($headers)
                ->post($url, $body);

            $resJson = $response->json();

            if ($response->successful() && (($resJson['status'] ?? '') === 'success' || ($resJson['success'] ?? false) === true)) {
                try {
                    Cache::forget('cio_finance_balance_data');
                } catch (\Throwable $e) {}

                Log::info('Berhasil mengembalikan saldo Finance CIO (Auto-Refund)', [
                    'reference_id' => $referenceId,
                    'balance_type' => $balanceType,
                    'amount'       => $amount,
                ]);

                return [
                    'success' => true,
                    'message' => $resJson['message'] ?? 'Saldo berhasil dikembalikan',
                    'data'    => $resJson['data'] ?? [],
                ];
            }

            Log::error('Gagal refund saldo Finance CIO', [
                'status'   => $response->status(),
                'response' => $resJson ?? $response->body(),
            ]);

            return [
                'success' => false,
                'message' => $resJson['message'] ?? ('Gagal refund saldo (HTTP ' . $response->status() . ')'),
            ];

        } catch (Exception $e) {
            Log::error('Exception saat refund saldo Finance CIO: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Terjadi kesalahan sistem saat refund saldo: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Mengirim log riwayat aktivitas transaksi ke endpoint /api/v1/history di Server Finance CIO.
     *
     * @param array $payload
     * @return array{success: bool, message: string, data?: mixed}
     */
    public function recordHistory(array $payload): array
    {
        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'message' => 'API Finance belum dikonfigurasi pada .env.',
            ];
        }

        $path = '/api/v1/history';
        $url  = $this->baseUrl . $path;

        try {
            $headers = $this->generateHeaders('POST', $path, $payload);

            $response = Http::timeout($this->timeout)
                ->withHeaders($headers)
                ->post($url, $payload);

            $resJson = $response->json();

            if ($response->successful() && (($resJson['status'] ?? '') === 'success' || ($resJson['success'] ?? false) === true)) {
                Log::info('Berhasil mencatat log aktivitas transaksi ke Finance CIO (/api/v1/history)', [
                    'subject_external_id' => $payload['subject_external_id'] ?? null,
                    'event'               => $payload['event'] ?? 'created',
                    'data'                => $resJson['data'] ?? [],
                ]);

                return [
                    'success' => true,
                    'message' => $resJson['message'] ?? 'Activity log created successfully',
                    'data'    => $resJson['data'] ?? [],
                ];
            }

            Log::warning('Gagal mencatat log aktivitas ke Finance CIO', [
                'status'   => $response->status(),
                'response' => $resJson ?? $response->body(),
            ]);

            return [
                'success' => false,
                'message' => $resJson['message'] ?? ('Gagal mencatat log aktivitas (HTTP ' . $response->status() . ')'),
            ];

        } catch (Exception $e) {
            Log::error('Exception saat mencatat log aktivitas ke Finance CIO: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Terjadi kesalahan sistem saat mencatat log aktivitas: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Mencatat log riwayat transfer dividen lengkap ke endpoint /api/v1/history Server Finance CIO.
     * Format deskripsi detail: Transfer ke siapa, sebesar apa, ke rekening mana, dan untuk apa isi pesannya.
     *
     * @param \App\Models\Transfer $transfer
     * @return array
     */
    public function recordTransferHistory(\App\Models\Transfer $transfer): array
    {
        $transfer->loadMissing(['investor.investors', 'investor.investor', 'admin']);
        $investor = $transfer->investor;

        $investorName      = $investor?->name ?? 'Investor';
        $netFormatted      = 'Rp ' . number_format($transfer->amount, 0, ',', '.');
        $grossFormatted    = 'Rp ' . number_format($transfer->gross_amount ?: $transfer->amount, 0, ',', '.');
        $adminFeeFormatted = 'Rp ' . number_format($transfer->admin_fee ?: 0, 0, ',', '.');
        $balanceType       = ucfirst($transfer->balance_type ?: 'Manual');
        $note              = $transfer->notes ?: 'Distribusi bagi hasil dividen';
        $paymentMethod     = $transfer->payment_method ?: '-';

        $firstInv      = $investor?->investors?->first() ?? $investor?->investor;
        $accountNumber = $firstInv?->party_1_account_number ?? '-';

        // Deskripsi yang sangat jelas dan terperinci sesuai instruksi
        $description = sprintf(
            "Penyaluran bagi hasil dividen kepada %s sebesar %s (Kotor: %s, Biaya Admin: %s) melalui %s [No. Rek/HP: %s]. Sumber: Saldo %s. Catatan: %s",
            $investorName,
            $netFormatted,
            $grossFormatted,
            $adminFeeFormatted,
            $paymentMethod,
            $accountNumber,
            $balanceType,
            $note
        );

        $payload = [
            'event'               => 'created',
            'subject_type'        => 'Expense',
            'subject_external_id' => $transfer->code ?: ('INV-TRF-' . $transfer->id),
            'description'         => $description,
            'properties'          => [
                'client_name'            => 'Web Investor',
                'investor_id'            => $investor?->id,
                'investor_name'          => $investorName,
                'investor_email'         => $investor?->email,
                'investor_phone'         => $investor?->phone,
                'net_amount'             => (float) $transfer->amount,
                'gross_amount'           => (float) ($transfer->gross_amount ?: $transfer->amount),
                'admin_fee'              => (float) ($transfer->admin_fee ?: 0),
                'payment_method'         => $paymentMethod,
                'account_number'         => $accountNumber,
                'balance_type'           => strtolower($transfer->balance_type ?: 'manual'),
                'transfer_date'          => $transfer->transfer_date ? \Carbon\Carbon::parse($transfer->transfer_date)->format('Y-m-d') : now()->format('Y-m-d'),
                'notes'                  => $note,
                'reference_code'         => $transfer->code,
                'xendit_disbursement_id' => $transfer->xendit_disbursement_id,
                'xendit_status'          => $transfer->xendit_status,
                'status'                 => $transfer->status ?: 'success',
                'admin_name'             => $transfer->admin?->name ?? 'Admin',
            ],
        ];

        return $this->recordHistory($payload);
    }
}
