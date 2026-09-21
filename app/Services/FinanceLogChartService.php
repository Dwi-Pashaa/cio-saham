<?php

namespace App\Services;

use App\Models\FinanceLogCache;
use App\Services\FinanceClient\CioFinanceApiClient;
use Carbon\Carbon;

class FinanceLogChartService
{
    protected CioFinanceApiClient $financeApi;

    public function __construct(CioFinanceApiClient $financeApi)
    {
        $this->financeApi = $financeApi;
    }

    /**
     * Generate dataset format time-series untuk ApexCharts di Dashboard.
     */
    public function getChartData(string $timeframe = '7d', ?string $webFilter = null): array
    {
        $days = match ($timeframe) {
            '1d' => 1,
            '7d' => 7,
            '30d' => 30,
            '3m' => 90,
            '1y' => 365,
            default => 7,
        };

        $categories = [];
        $inflowSeries = [];
        $outflowSeries = [];
        $netProfitSeries = [];

        $rawLogs = $this->getRawLogs();

        // Siapkan tanggal-tanggal
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $dateKey = $date->format('Y-m-d');
            $label = $days <= 7 ? $date->translatedFormat('d M') : $date->format('d/m');

            $categories[] = $label;

            $dayInflow  = 0;
            $dayOutflow = 0;

            foreach ($rawLogs as $log) {
                if ($webFilter && ($log['source_client_code'] ?? '') !== $webFilter) {
                    continue;
                }

                $logDate = isset($log['created_at']) ? Carbon::parse($log['created_at'])->format('Y-m-d') : '';
                if ($logDate === $dateKey) {
                    $type = strtolower($log['subject_type'] ?? '');
                    $amount = (float) ($log['amount'] ?? 0);

                    if (str_contains($type, 'income')) {
                        $dayInflow += $amount;
                    } elseif (str_contains($type, 'expense') || str_contains($type, 'deduct')) {
                        $dayOutflow += $amount;
                    }
                }
            }

            $inflowSeries[] = $dayInflow;
            $outflowSeries[] = $dayOutflow;
            $netProfitSeries[] = $dayInflow - $dayOutflow;
        }

        // Hitung akumulasi & metrik per web
        $webBreakdown = $this->calculateWebBreakdown($rawLogs);

        return [
            'categories' => $categories,
            'series' => [
                [
                    'name' => 'Pemasukan (Inflow)',
                    'data' => $inflowSeries,
                    'color' => '#2563eb',
                ],
                [
                    'name' => 'Pengeluaran (Outflow)',
                    'data' => $outflowSeries,
                    'color' => '#ef4444',
                ],
                [
                    'name' => 'Laba Bersih (Net Profit)',
                    'data' => $netProfitSeries,
                    'color' => '#10b981',
                ],
            ],
            'summary' => [
                'total_inflow'     => array_sum($inflowSeries),
                'total_outflow'    => array_sum($outflowSeries),
                'total_net_profit' => array_sum($netProfitSeries),
            ],
            'web_breakdown' => $webBreakdown,
        ];
    }

    /**
     * Hitung perputaran log per masing-masing web client secara riil.
     */
    protected function calculateWebBreakdown(array $logs): array
    {
        $breakdown = [];
        foreach ($logs as $log) {
            $code = $log['source_client_code'] ?? 'GENERAL';
            $name = $log['source_client_name'] ?? 'General Web';
            $amount = (float) ($log['amount'] ?? 0);
            $type = strtolower($log['subject_type'] ?? '');

            if (!isset($breakdown[$code])) {
                $breakdown[$code] = [
                    'code' => $code,
                    'name' => $name,
                    'inflow' => 0,
                    'outflow' => 0,
                    'log_count' => 0,
                ];
            }

            $breakdown[$code]['log_count']++;
            if (str_contains($type, 'income')) {
                $breakdown[$code]['inflow'] += $amount;
            } else {
                $breakdown[$code]['outflow'] += $amount;
            }
        }

        return array_values($breakdown);
    }

    /**
     * Ambil raw logs dari DB Cache atau API riil.
     */
    protected function getRawLogs(): array
    {
        $cached = FinanceLogCache::latest('log_created_at')->limit(100)->get();
        if ($cached->isNotEmpty()) {
            return $cached->map(function ($row) {
                return [
                    'source_client_code' => $row->source_client_code,
                    'source_client_name' => $row->source_client_name,
                    'subject_type'       => $row->subject_type,
                    'amount'             => $row->amount,
                    'balance_type'       => $row->balance_type,
                    'description'        => $row->description,
                    'created_at'         => $row->log_created_at,
                ];
            })->toArray();
        }

        $apiLogs = $this->financeApi->getActivityLogs();
        return $apiLogs['data'] ?? [];
    }
}
