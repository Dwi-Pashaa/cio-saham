<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\Transfer;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class XenditService
{
    /**
     * Dapatkan Secret Key Xendit dari Database Settings atau file .env
     */
    public function getSecretKey(): ?string
    {
        $setting = Setting::first();
        return $setting?->xendit_secret_key ?: config('services.xendit.secret_key') ?: env('XENDIT_SECRET_KEY');
    }

    /**
     * Dapatkan Webhook Verification Token Xendit
     */
    public function getWebhookToken(): ?string
    {
        $setting = Setting::first();
        return $setting?->xendit_webhook_token ?: config('services.xendit.webhook_token') ?: env('XENDIT_WEBHOOK_TOKEN');
    }

    /**
     * Eksekusi Payout / Disbursement ke Rekening Bank Investor.
     * Standar Central Router Multi-Website Prefix: INV-DISB-{id}-{timestamp}
     *
     * @param Transfer    $transfer          Data transaksi transfer
     * @param User        $investor          Data user investor penerima
     * @param float       $netAmount         Nominal bersih yang dikirim (setelah dipotong biaya admin)
     * @param string|null $bankCode          Kode bank tujuan (misal: BCA, BNI, BRI, MANDIRI)
     * @param string|null $accountNumber     Nomor rekening tujuan
     * @param string|null $accountHolderName Nama pemilik rekening
     * @return array{status: bool, message: string, disbursement_id?: string, external_id?: string, data?: mixed}
     */
    public function createPayout(
        Transfer $transfer,
        User $investor,
        float $netAmount,
        ?string $bankCode = null,
        ?string $accountNumber = null,
        ?string $accountHolderName = null
    ): array {
        $secretKey = $this->getSecretKey();

        if (empty($secretKey)) {
            Log::warning('[XenditService] XENDIT_SECRET_KEY belum diisi. Menggunakan mode simulasi / mock sukses.');
            
            // Mode simulasi / sandbox jika secret key belum diisi admin
            $mockExternalId = 'INV-DISB-' . $transfer->id . '-' . time();
            $mockDisbId = 'disb_' . bin2hex(random_bytes(10));
            
            return [
                'status'          => true,
                'message'         => 'Simulasi Payout Xendit Berhasil (Kredensial belum aktif)',
                'disbursement_id' => $mockDisbId,
                'external_id'     => $mockExternalId,
                'is_simulated'    => true,
                'data'            => [
                    'id'          => $mockDisbId,
                    'external_id' => $mockExternalId,
                    'amount'      => (int) $netAmount,
                    'status'      => 'COMPLETED',
                ],
            ];
        }

        $refCode = !empty($transfer->code) ? preg_replace('/[^A-Za-z0-9]/', '', $transfer->code) : (!empty($transfer->id) ? (string)$transfer->id : time());
        $externalId = 'INV-DISB-' . $refCode . '-' . time();
        $cleanBankCode = strtoupper(trim($bankCode ?? $transfer->payment_method ?? 'BCA'));
        $cleanAccountNumber = preg_replace('/\D/', '', (string) ($accountNumber ?: '1234567890'));
        $cleanHolderName = trim(preg_replace('/[^a-zA-Z0-9\s\.\,\'\-]/', '', (string) ($accountHolderName ?: $investor->name)));

        $payload = [
            'external_id'           => $externalId,
            'amount'                => (int) $netAmount,
            'bank_code'             => $cleanBankCode,
            'account_holder_name'   => $cleanHolderName ?: $investor->name,
            'account_number'        => $cleanAccountNumber ?: '1234567890',
            'description'           => "Bagi hasil dividen investor: {$investor->name} (" . ($transfer->code ?: 'INV-TRF') . ")",
        ];

        try {
            $response = Http::withBasicAuth($secretKey, '')
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept'       => 'application/json',
                ])
                ->timeout(20)
                ->post('https://api.xendit.co/disbursements', $payload);

            $resData = $response->json();

            if ($response->successful()) {
                Log::info("[XenditService] Disbursement payout berhasil dibuat: {$externalId}", [
                    'response' => $resData,
                ]);

                return [
                    'status'          => true,
                    'message'         => 'Disbursement Xendit berhasil diproses',
                    'disbursement_id' => $resData['id'] ?? null,
                    'external_id'     => $externalId,
                    'data'            => $resData,
                ];
            }

            $errorMsg = $resData['message'] ?? $response->body();
            Log::error("[XenditService] Gagal memproses payout Xendit: {$errorMsg}", [
                'status'  => $response->status(),
                'payload' => $payload,
            ]);

            return [
                'status'  => false,
                'message' => 'Gagal memproses payout Xendit: ' . $errorMsg,
                'data'    => $resData,
            ];

        } catch (Exception $e) {
            Log::error('[XenditService] Exception saat memproses payout Xendit: ' . $e->getMessage(), [
                'external_id' => $externalId,
            ]);

            return [
                'status'  => false,
                'message' => 'Terjadi kesalahan sistem saat menghubungi server Xendit: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Handle Webhook Callback dari Xendit / Central Router.
     * Standar Central Router Multi-Website Prefix: INV-
     */
    public function handleWebhook(array $payload, ?string $incomingToken): array
    {
        $configuredToken = $this->getWebhookToken();

        if (!empty($configuredToken) && !empty($incomingToken) && $incomingToken !== $configuredToken) {
            Log::warning('[XenditService] Webhook token mismatch. Incoming: ' . $incomingToken . ' vs Configured: ' . $configuredToken);
            return ['status' => false, 'message' => 'Invalid webhook verification token.'];
        }

        $externalId = $payload['external_id'] ?? $payload['data']['external_id'] ?? null;
        $status     = strtoupper($payload['status'] ?? $payload['data']['status'] ?? '');
        $disbId     = $payload['id'] ?? $payload['data']['id'] ?? null;

        // Validasi prefix router multi-website
        if ($externalId && !str_starts_with((string)$externalId, 'INV-')) {
            Log::info("[XenditService] Webhook event skipped: external_id {$externalId} bukan untuk modul Investor (prefix INV-).");
            return ['status' => true, 'message' => 'Event ignored: non-investor prefix'];
        }

        // Cari transaksi Transfer terkait
        $transfer = null;
        if ($disbId) {
            $transfer = Transfer::where('xendit_disbursement_id', $disbId)->first();
        }

        if (!$transfer && $externalId) {
            $transfer = Transfer::where('finance_reference_id', $externalId)
                ->orWhere('code', $externalId)
                ->first();

            if (!$transfer && preg_match('/(?:INV-)?(?:DISB-)?([A-Za-z0-9\-]+?)(?:-\d+)?$/i', $externalId, $matches)) {
                $rawCode = $matches[1];
                $transfer = Transfer::where('code', $rawCode)
                    ->orWhere('code', 'INV-TRF' . preg_replace('/[^0-9]/', '', $rawCode))
                    ->orWhere('id', (int) preg_replace('/[^0-9]/', '', $rawCode))
                    ->first();
            }

            if (!$transfer && preg_match('/(?:INV-)?(?:DISB|TRF)-(\d+)/i', $externalId, $matches)) {
                $transferId = (int) $matches[1];
                $transfer = Transfer::find($transferId);
            }
        }

        if (!$transfer) {
            Log::warning("[XenditService] Transfer record not found for external_id: {$externalId}, disbursement_id: {$disbId}");
            return ['status' => true, 'message' => 'Transfer record not found in Investor system'];
        }

        // Cek status sukses payout
        $isSuccess = in_array($status, ['COMPLETED', 'SUCCESS', 'PAID', 'SETTLED']);
        $isFailed  = in_array($status, ['FAILED', 'REJECTED', 'CANCELLED']);

        if ($isSuccess) {
            $transfer->update([
                'status'                 => 'success',
                'xendit_status'          => 'COMPLETED',
                'confirmation_date'      => now(),
                'xendit_disbursement_id' => $disbId ?: $transfer->xendit_disbursement_id,
            ]);

            Log::info("[XenditService] Transfer #{$transfer->id} ({$transfer->code}) berhasil dilunasi via Xendit Webhook.");

            // Kirim notifikasi WhatsApp / Email otomatis ke Investor setelah webhook callback sukses diterima
            try {
                $setting = Setting::first();
                if (($setting->notification_channel ?? 'whatsapp') !== 'none') {
                    $qontakService = app(MekariQontakService::class);
                    $freshTransfer = $transfer->fresh(['investor.investor', 'investor.investors', 'admin']);
                    $notifResult = $qontakService->dispatchNotification($freshTransfer);
                    Log::info("[XenditService] Notifikasi WhatsApp/Email berhasil dikirim via Webhook untuk Transfer #{$transfer->id}: " . json_encode($notifResult));
                }
            } catch (\Throwable $e) {
                Log::error("[XenditService] Gagal mengirim notifikasi via Webhook untuk Transfer #{$transfer->id}: " . $e->getMessage());
            }

            return ['status' => true, 'message' => 'Transfer marked as success via webhook and notification dispatched'];
        }

        if ($isFailed) {
            $transfer->update([
                'status'        => 'failed',
                'xendit_status' => 'FAILED',
            ]);

            // Auto-Refund ke Saldo Xendit di Finance API jika transfer gagal di callback
            try {
                if ($transfer->balance_type === 'xendit' && $transfer->amount > 0) {
                    $refId = 'INV-REFUND-' . $transfer->id . '-' . time();
                    app(CioFinanceService::class)->refundBalance(
                        $transfer->amount,
                        'xendit',
                        $refId,
                        "Rollback transfer gagal Xendit: {$transfer->code}",
                        $payload['failure_code'] ?? 'Xendit payout failed via webhook'
                    );
                    Log::info("[XenditService] Auto-refund saldo Xendit berhasil dieksekusi untuk Transfer #{$transfer->id}");
                }
            } catch (Exception $e) {
                Log::error('[XenditService] Gagal auto-refund saat webhook failed: ' . $e->getMessage());
            }

            Log::warning("[XenditService] Transfer #{$transfer->id} dinyatakan GAGAL oleh Xendit Webhook.");
            return ['status' => true, 'message' => 'Transfer marked as failed via webhook'];
        }

        return ['status' => true, 'message' => 'Webhook received and processed'];
    }

    /**
     * Daftar Standar Bank & E-Wallet Resmi yang Didukung oleh Xendit Disbursement Indonesia.
     * Dikelompokkan berdasarkan kategori agar rapi dan mudah dicari.
     *
     * @return array<string, array<int, array{code: string, name: string, type: string}>>
     */
    public static function getSupportedBanks(): array
    {
        return [
            'Bank Terpopuler' => [
                ['code' => 'BCA', 'name' => 'Bank Central Asia (BCA)', 'type' => 'bank'],
                ['code' => 'MANDIRI', 'name' => 'Bank Mandiri', 'type' => 'bank'],
                ['code' => 'BRI', 'name' => 'Bank Rakyat Indonesia (BRI)', 'type' => 'bank'],
                ['code' => 'BNI', 'name' => 'Bank Negara Indonesia (BNI)', 'type' => 'bank'],
                ['code' => 'BSI', 'name' => 'Bank Syariah Indonesia (BSI)', 'type' => 'bank'],
                ['code' => 'CIMB', 'name' => 'Bank CIMB Niaga', 'type' => 'bank'],
                ['code' => 'PERMATA', 'name' => 'Bank Permata', 'type' => 'bank'],
                ['code' => 'DANAMON', 'name' => 'Bank Danamon', 'type' => 'bank'],
                ['code' => 'BTN', 'name' => 'Bank Tabungan Negara (BTN)', 'type' => 'bank'],
            ],
            'Bank Digital & Fintech' => [
                ['code' => 'JAGO', 'name' => 'Bank Jago', 'type' => 'bank'],
                ['code' => 'SEABANK', 'name' => 'SeaBank Indonesia', 'type' => 'bank'],
                ['code' => 'BTPN', 'name' => 'Bank BTPN / Jenius', 'type' => 'bank'],
                ['code' => 'NEO', 'name' => 'Bank Neo Commerce (BNC)', 'type' => 'bank'],
                ['code' => 'ALLO', 'name' => 'Allo Bank Indonesia', 'type' => 'bank'],
                ['code' => 'SAQU', 'name' => 'Bank Saqu (Bank Jasa Jakarta)', 'type' => 'bank'],
                ['code' => 'SUPERBANK', 'name' => 'Superbank', 'type' => 'bank'],
                ['code' => 'KROM', 'name' => 'Krom Bank', 'type' => 'bank'],
                ['code' => 'ALADIN', 'name' => 'Bank Aladin Syariah', 'type' => 'bank'],
            ],
            'Bank Nasional & Swasta Lainnya' => [
                ['code' => 'PANIN', 'name' => 'Bank Panin', 'type' => 'bank'],
                ['code' => 'MAYBANK', 'name' => 'Maybank Indonesia', 'type' => 'bank'],
                ['code' => 'OCBC', 'name' => 'Bank OCBC NISP', 'type' => 'bank'],
                ['code' => 'MEGA', 'name' => 'Bank Mega', 'type' => 'bank'],
                ['code' => 'SINARMAS', 'name' => 'Bank Sinarmas', 'type' => 'bank'],
                ['code' => 'UOB', 'name' => 'Bank UOB Indonesia', 'type' => 'bank'],
                ['code' => 'DBS', 'name' => 'Bank DBS Indonesia', 'type' => 'bank'],
                ['code' => 'COMMONWEALTH', 'name' => 'Bank Commonwealth', 'type' => 'bank'],
                ['code' => 'BUKOPIN', 'name' => 'KB Bukopin', 'type' => 'bank'],
                ['code' => 'MUAMALAT', 'name' => 'Bank Muamalat Indonesia', 'type' => 'bank'],
                ['code' => 'MNC', 'name' => 'Bank MNC Internasional', 'type' => 'bank'],
                ['code' => 'NOBU', 'name' => 'Bank Nationalnobu', 'type' => 'bank'],
                ['code' => 'VICTORIA', 'name' => 'Bank Victoria International', 'type' => 'bank'],
                ['code' => 'ARTHA', 'name' => 'Bank Artha Graha Internasional', 'type' => 'bank'],
                ['code' => 'BUMI_ARTA', 'name' => 'Bank Bumi Arta', 'type' => 'bank'],
                ['code' => 'CAPITAL', 'name' => 'Bank Capital Indonesia', 'type' => 'bank'],
                ['code' => 'GANESHA', 'name' => 'Bank Ganesha', 'type' => 'bank'],
                ['code' => 'INA', 'name' => 'Bank Ina Perdana', 'type' => 'bank'],
                ['code' => 'INDEX', 'name' => 'Bank Index Selindo', 'type' => 'bank'],
                ['code' => 'MASPION', 'name' => 'Bank Maspion Indonesia', 'type' => 'bank'],
                ['code' => 'MAYAPADA', 'name' => 'Bank Mayapada', 'type' => 'bank'],
                ['code' => 'MESTIKA', 'name' => 'Bank Mestika Dharma', 'type' => 'bank'],
                ['code' => 'SHINHAN', 'name' => 'Bank Shinhan Indonesia', 'type' => 'bank'],
                ['code' => 'WOORI', 'name' => 'Bank Woori Saudara', 'type' => 'bank'],
                ['code' => 'HSBC', 'name' => 'HSBC Indonesia', 'type' => 'bank'],
                ['code' => 'CITIBANK', 'name' => 'Citibank Indonesia', 'type' => 'bank'],
                ['code' => 'STANDARD_CHARTERED', 'name' => 'Standard Chartered Bank', 'type' => 'bank'],
                ['code' => 'CCB', 'name' => 'China Construction Bank Indonesia', 'type' => 'bank'],
                ['code' => 'ICBC', 'name' => 'ICBC Indonesia', 'type' => 'bank'],
            ],
            'Bank Pembangunan Daerah (BPD)' => [
                ['code' => 'BJB', 'name' => 'Bank BJB (Jabar Banten)', 'type' => 'bank'],
                ['code' => 'JATENG', 'name' => 'Bank Jateng', 'type' => 'bank'],
                ['code' => 'JATIM', 'name' => 'Bank Jatim', 'type' => 'bank'],
                ['code' => 'DKI', 'name' => 'Bank DKI', 'type' => 'bank'],
                ['code' => 'BPD_DIY', 'name' => 'Bank BPD DIY', 'type' => 'bank'],
                ['code' => 'BALI', 'name' => 'Bank BPD Bali', 'type' => 'bank'],
                ['code' => 'SUMUT', 'name' => 'Bank Sumut', 'type' => 'bank'],
                ['code' => 'NAGARI', 'name' => 'Bank Nagari (Sumbar)', 'type' => 'bank'],
                ['code' => 'RIAU', 'name' => 'Bank Riau Kepri Syariah', 'type' => 'bank'],
                ['code' => 'SUMSEL_BABEL', 'name' => 'Bank Sumsel Babel', 'type' => 'bank'],
                ['code' => 'LAMPUNG', 'name' => 'Bank Lampung', 'type' => 'bank'],
                ['code' => 'JAMBI', 'name' => 'Bank Jambi', 'type' => 'bank'],
                ['code' => 'BENGKULU', 'name' => 'Bank Bengkulu', 'type' => 'bank'],
                ['code' => 'ACEH', 'name' => 'Bank Aceh Syariah', 'type' => 'bank'],
                ['code' => 'KALBAR', 'name' => 'Bank Kalbar', 'type' => 'bank'],
                ['code' => 'KALSEL', 'name' => 'Bank Kalsel', 'type' => 'bank'],
                ['code' => 'KALTENG', 'name' => 'Bank Kalteng', 'type' => 'bank'],
                ['code' => 'KALTIM', 'name' => 'Bank Kaltimtara', 'type' => 'bank'],
                ['code' => 'SULSELBAR', 'name' => 'Bank Sulselbar', 'type' => 'bank'],
                ['code' => 'SULUT', 'name' => 'Bank SulutGo', 'type' => 'bank'],
                ['code' => 'SULTENG', 'name' => 'Bank Sulteng', 'type' => 'bank'],
                ['code' => 'SULTRA', 'name' => 'Bank Sultra', 'type' => 'bank'],
                ['code' => 'NTB_SYARIAH', 'name' => 'Bank NTB Syariah', 'type' => 'bank'],
                ['code' => 'NTT', 'name' => 'Bank NTT', 'type' => 'bank'],
                ['code' => 'MALUKU', 'name' => 'Bank Maluku Malut', 'type' => 'bank'],
                ['code' => 'PAPUA', 'name' => 'Bank Papua', 'type' => 'bank'],
            ],
            'E-Wallet (Dompet Digital)' => [
                ['code' => 'GOPAY', 'name' => 'GoPay', 'type' => 'ewallet'],
                ['code' => 'OVO', 'name' => 'OVO', 'type' => 'ewallet'],
                ['code' => 'DANA', 'name' => 'DANA', 'type' => 'ewallet'],
                ['code' => 'SHOPEEPAY', 'name' => 'ShopeePay', 'type' => 'ewallet'],
                ['code' => 'LINKAJA', 'name' => 'LinkAja', 'type' => 'ewallet'],
                ['code' => 'ASTRA', 'name' => 'AstraPay', 'type' => 'ewallet'],
            ],
        ];
    }

    /**
     * Dapatkan daftar bank yang dikelompokkan (grouped) untuk view Select2.
     */
    public function getAvailableBanksGrouped(): array
    {
        return self::getSupportedBanks();
    }

    /**
     * Dapatkan daftar seluruh bank dalam format flat array.
     */
    public function getAllBanksFlat(): array
    {
        $result = [];
        foreach (self::getSupportedBanks() as $group => $banks) {
            foreach ($banks as $bank) {
                $result[] = $bank;
            }
        }
        return $result;
    }
}
