<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\FinanceAnalyticsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FinanceLogApiController extends Controller
{
    protected FinanceAnalyticsService $analyticsService;

    public function __construct(FinanceAnalyticsService $analyticsService)
    {
        $this->analyticsService = $analyticsService;
    }

    /**
     * 1. GET /api/finance/overview
     * Ringkasan Eksekutif & Saldo Konsolidasi.
     */
    public function getOverview(Request $request): JsonResponse
    {
        $clientCode = $request->query('client_code', 'ALL');
        $fresh = $request->boolean('fresh', false);

        $data = $this->analyticsService->getOverview($clientCode, $fresh);

        return response()->json([
            'status'  => 'success',
            'success' => true,
            'data'    => $data,
        ]);
    }

    /**
     * 2. GET /api/finance/chart
     * Time-Series Inflow vs Outflow vs Net Profit untuk ApexCharts.
     */
    public function getChartData(Request $request): JsonResponse
    {
        $range = $request->query('range', $request->query('timeframe', '7d'));
        $defaultInterval = in_array(strtolower($range), ['1d', '24h', 'today', '1h']) ? 'hourly' : 'daily';
        $interval = $request->query('interval', $defaultInterval);
        $clientCode = $request->query('client_code', $request->query('web_filter', 'ALL'));
        $date = $request->query('date');
        $fresh = $request->boolean('fresh', false);

        $data = $this->analyticsService->getChart($range, $interval, $clientCode, $fresh, $date);

        return response()->json([
            'status'  => 'success',
            'success' => true,
            'data'    => $data,
        ]);
    }

    /**
     * 3. GET /api/finance/growth
     * Indikator Pertumbuhan Bisnis & Valuasi Saham.
     */
    public function getGrowthData(Request $request): JsonResponse
    {
        $clientCode = $request->query('client_code', 'ALL');
        $fresh = $request->boolean('fresh', false);

        $data = $this->analyticsService->getGrowth($clientCode, $fresh);

        return response()->json([
            'status'  => 'success',
            'success' => true,
            'data'    => $data,
        ]);
    }

    /**
     * 4. GET /api/finance/history
     * Riwayat Mutasi Multi-Web dengan Pagination & Filter.
     */
    public function getHistoryData(Request $request): JsonResponse
    {
        $params = [
            'page'         => (int) $request->query('page', 1),
            'per_page'     => (int) $request->query('per_page', 15),
            'client_code'  => $request->query('client_code', $request->query('web_filter', 'ALL')),
            'balance_type' => $request->query('balance_type'),
            'subject_type' => $request->query('subject_type'),
            'search'       => $request->query('search'),
            'start_date'   => $request->query('start_date'),
            'end_date'     => $request->query('end_date'),
        ];
        $fresh = $request->boolean('fresh', false);

        $data = $this->analyticsService->getHistory($params, $fresh);

        return response()->json([
            'status'  => 'success',
            'success' => true,
            'data'    => $data,
        ]);
    }

    /**
     * 5. POST /api/finance/sync
     * Trigger sinkronisasi manual dari CIO Finance API.
     */
    public function syncFromFinance(): JsonResponse
    {
        try {
            $result = $this->analyticsService->syncFinanceLogs();

            return response()->json([
                'status'  => 'success',
                'success' => true,
                'message' => "Sinkronisasi berhasil: {$result['synced_count']} record mutasi diperbarui.",
                'data'    => $result,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status'  => 'error',
                'success' => false,
                'message' => 'Gagal sinkronisasi: ' . $e->getMessage(),
            ], 500);
        }
    }
}
