<?php

namespace App\Services;

use App\Models\Setting;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FonnteService
{
    /**
     * Dapatkan Token API Fonnte dari Database Settings atau file config/env.
     */
    public function getToken(): ?string
    {
        $setting = Setting::first();
        return $setting?->fonnte_token ?: config('services.fonnte.token') ?: env('FONNTE_TOKEN');
    }

    /**
     * Dapatkan endpoint API Fonnte.
     */
    public function getEndpoint(): string
    {
        return config('services.fonnte.endpoint') ?: 'https://api.fonnte.com/send';
    }

    /**
     * Normalisasi nomor telepon WhatsApp (e.g., 0812 -> 62812) atau daftar nomor dipisah koma.
     */
    public function formatPhoneNumber(string|array $phone): string
    {
        if (is_array($phone)) {
            $phoneList = $phone;
        } else {
            $phoneList = explode(',', $phone);
        }

        $formatted = [];
        foreach ($phoneList as $item) {
            $cleaned = preg_replace('/[^0-9]/', '', (string)$item);
            if (empty($cleaned)) continue;
            if (str_starts_with($cleaned, '0')) {
                $cleaned = '62' . substr($cleaned, 1);
            } elseif (str_starts_with($cleaned, '8')) {
                $cleaned = '62' . $cleaned;
            }
            if (strlen($cleaned) >= 8) {
                $formatted[] = $cleaned;
            }
        }

        return implode(',', array_unique($formatted));
    }

    /**
     * Kirim pesan teks WhatsApp (mendukung lampiran file lokal via multipart atau URL publik).
     *
     * @param string      $target        Nomor tujuan WhatsApp (mendukung multi-tujuan koma)
     * @param string      $message       Teks pesan WhatsApp
     * @param string|null $fileUrl       URL publik file/gambar (opsional)
     * @param string|null $filename      Nama file lampiran (opsional)
     * @param string|null $localFilePath Path fisik file lokal di server (opsional)
     * @return array{status: bool, message: string, response?: mixed}
     */
    public function sendMessage(
        string $target,
        string $message,
        ?string $fileUrl = null,
        ?string $filename = null,
        ?string $localFilePath = null
    ): array {
        $token = $this->getToken();

        if (empty($token)) {
            Log::warning('[FonnteService] Token Fonnte belum dikonfigurasi di Settings atau .env.');
            return [
                'status'  => false,
                'message' => 'Token API Fonnte belum dikonfigurasi.',
            ];
        }

        $formattedTarget = $this->formatPhoneNumber($target);
        if (empty($formattedTarget)) {
            Log::warning('[FonnteService] Nomor target WhatsApp tidak valid: ' . $target);
            return [
                'status'  => false,
                'message' => 'Nomor WhatsApp tujuan tidak valid.',
            ];
        }

        $payload = [
            'target'      => $formattedTarget,
            'message'     => $message,
            'countryCode' => '62',
        ];

        // Resolusi file lokal: periksa apakah localFilePath diberikan atau fileUrl merujuk ke storage lokal
        $resolvedLocalPath = null;
        if (!empty($localFilePath) && file_exists($localFilePath)) {
            $resolvedLocalPath = $localFilePath;
        } elseif (!empty($fileUrl)) {
            if (file_exists($fileUrl)) {
                $resolvedLocalPath = $fileUrl;
            } elseif (str_contains($fileUrl, '/storage/')) {
                $storageRel = explode('/storage/', $fileUrl)[1] ?? null;
                if ($storageRel && file_exists(storage_path('app/public/' . $storageRel))) {
                    $resolvedLocalPath = storage_path('app/public/' . $storageRel);
                }
            }
        }

        $http = Http::withHeaders([
            'Authorization' => $token,
        ])->timeout(30);

        // Jika ada file fisik lokal di server, unggah langsung via multipart 'file'
        if ($resolvedLocalPath && file_exists($resolvedLocalPath)) {
            $attachName = $filename ?: basename($resolvedLocalPath);
            $http->attach('file', file_get_contents($resolvedLocalPath), $attachName);
        } elseif (!empty($fileUrl)) {
            // Hanya kirimkan parameter 'url' jika benar-benar URL publik internet
            $isLocalUrl = preg_match('/localhost|127\.0\.0\.1|::1|\.local|\.test/i', $fileUrl);
            if (!$isLocalUrl && filter_var($fileUrl, FILTER_VALIDATE_URL)) {
                $payload['url'] = $fileUrl;
                if (!empty($filename)) {
                    $payload['filename'] = $filename;
                }
            }
        }

        try {
            $response = $http->post($this->getEndpoint(), $payload);
            $resData = $response->json();

            $isSuccess = $response->successful() && (
                ($resData['status'] ?? false) === true 
                || ($resData['status'] ?? 0) === 1 
                || ($resData['status'] ?? '') === 'true'
            );

            if ($isSuccess) {
                Log::info("[FonnteService] Pesan WhatsApp berhasil dikirim ke {$formattedTarget}", [
                    'response' => $resData,
                ]);

                return [
                    'status'   => true,
                    'message'  => 'Pesan WhatsApp berhasil dikirim via Fonnte.',
                    'response' => $resData,
                ];
            }

            $errMsg = $resData['reason'] ?? $resData['message'] ?? $response->body();
            Log::error("[FonnteService] Fonnte menolak pengiriman ke {$formattedTarget}: {$errMsg}", [
                'status_code' => $response->status(),
                'payload'     => $payload,
                'response'    => $resData,
            ]);

            return [
                'status'   => false,
                'message'  => 'Gagal mengirim pesan via Fonnte: ' . $errMsg,
                'response' => $resData,
            ];

        } catch (Exception $e) {
            Log::error('[FonnteService] Exception saat menghubungi API Fonnte: ' . $e->getMessage(), [
                'target' => $formattedTarget,
            ]);

            return [
                'status'  => false,
                'message' => 'Terjadi kesalahan sistem saat menghubungi server Fonnte: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Test kirim pesan WhatsApp untuk verifikasi koneksi Fonnte.
     */
    public function testConnection(string $target): array
    {
        $testMsg = "TEST KONEKSI GATEWAY WHATSAPP PT CIO NETWORK\n\n"
                 . "Halo, ini adalah pesan uji coba dari sistem *CIO Saham*.\n"
                 . "Token Fonnte & nomor tujuan Anda telah terhubung dengan sukses!\n\n"
                 . "Waktu: " . now()->format('d/m/Y H:i:s') . " WIB";

        return $this->sendMessage($target, $testMsg);
    }
}
