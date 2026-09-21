<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FinanceLogCache;
use App\Services\XenditService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class XenditWebhookController extends Controller
{
    /**
     * Handle Xendit webhook callback from Central Router or direct Xendit.
     * Route: POST /api/xendit/callback
     */
    public function handleCallback(Request $request, XenditService $xenditService): JsonResponse
    {
        $payload = $request->all();
        $token   = $request->header('x-callback-token') 
                ?? $request->header('X-Callback-Token') 
                ?? $request->header('webhook-id');

        Log::info('[XenditWebhookController] Incoming webhook request', [
            'headers' => $request->headers->all(),
            'payload' => $payload,
        ]);

        // 1. Catat ke Finance Log Cache jika terdapat transaksi Xendit (Pemasukan / Inflow atau Disbursement)
        $amount = (float) ($payload['amount'] ?? $payload['paid_amount'] ?? $payload['data']['amount'] ?? 0);
        $status = strtoupper($payload['status'] ?? $payload['data']['status'] ?? '');
        $externalId = $payload['external_id'] ?? $payload['data']['external_id'] ?? ('XENDIT-' . uniqid());
        $sourceWeb = 'XENDIT_GATEWAY';

        // Deteksi prefix web sumber jika diteruskan oleh Central Router
        if (str_starts_with((string)$externalId, 'INV-')) {
            $sourceWeb = 'CIO_INVESTOR';
        } elseif (str_starts_with((string)$externalId, 'TRX-EXP-') || str_starts_with((string)$externalId, 'SLIP-')) {
            $sourceWeb = 'WEB_SLIP_GAJI';
        } elseif (str_starts_with((string)$externalId, 'OPS-')) {
            $sourceWeb = 'CIO_OPERASIONAL';
        } elseif (str_starts_with((string)$externalId, 'KEU-')) {
            $sourceWeb = 'CIO_KEUANGAN';
        }

        $isSuccess = in_array($status, ['COMPLETED', 'PAID', 'SETTLED', 'SUCCESS']);

        if ($amount > 0 && $isSuccess) {
            $eventDesc = $payload['description'] ?? "Penerimaan pembayaran gateway Xendit ({$externalId})";
            $subjectType = (isset($payload['bank_code']) || isset($payload['disbursement_id'])) ? 'Expense' : 'Income';

            FinanceLogCache::create([
                'source_client_code' => $sourceWeb,
                'source_client_name' => ucwords(str_replace('_', ' ', strtolower($sourceWeb))),
                'event'              => 'created',
                'subject_type'       => $subjectType,
                'amount'             => $amount,
                'balance_type'       => 'xendit',
                'description'        => $eventDesc,
                'log_created_at'     => now(),
                'raw_payload'        => $payload,
            ]);

            // Hapus cache analitik agar grafik dan metrik pertumbuhan langsung diperbarui
            Cache::forget('company_growth_analytics');
        }

        // 2. Jalankan logika webhook internal
        $result = $xenditService->handleWebhook($payload, $token);

        return response()->json($result, $result['status'] ? 200 : 400);
    }
}
