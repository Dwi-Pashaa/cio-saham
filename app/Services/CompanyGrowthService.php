<?php

namespace App\Services;

use App\Models\FinanceLogCache;
use App\Models\ShareHolding;
use App\Services\FinanceClient\CioFinanceApiClient;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class CompanyGrowthService
{
    protected CioFinanceApiClient $financeApi;

    public function __construct(CioFinanceApiClient $financeApi)
    {
        $this->financeApi = $financeApi;
    }

    /**
     * Hitung & kembalikan analisis lengkap kesehatan & pertumbuhan perusahaan.
     */
    public function getGrowthAnalytics(): array
    {
        return Cache::remember('company_growth_analytics', 60, function () {
            // 1. Ambil data log multi-web riil
            $logs = $this->getConsolidatedLogs();

            // 2. Hitung performa bulan ini vs bulan lalu
            $currentMonth = Carbon::now()->startOfMonth();
            $prevMonth    = Carbon::now()->subMonth()->startOfMonth();

            $currentInflow  = 0;
            $currentOutflow = 0;
            $prevInflow     = 0;
            $prevOutflow    = 0;

            foreach ($logs as $log) {
                $createdAt = isset($log['created_at']) ? Carbon::parse($log['created_at']) : Carbon::now();
                $type = strtolower($log['subject_type'] ?? '');
                $amount = (float) ($log['amount'] ?? 0);

                if ($createdAt->greaterThanOrEqualTo($currentMonth)) {
                    if (str_contains($type, 'income')) {
                        $currentInflow += $amount;
                    } elseif (str_contains($type, 'expense') || str_contains($type, 'deduct')) {
                        $currentOutflow += $amount;
                    }
                } elseif ($createdAt->greaterThanOrEqualTo($prevMonth) && $createdAt->lessThan($currentMonth)) {
                    if (str_contains($type, 'income')) {
                        $prevInflow += $amount;
                    } elseif (str_contains($type, 'expense') || str_contains($type, 'deduct')) {
                        $prevOutflow += $amount;
                    }
                }
            }

            $currentNetProfit = $currentInflow - $currentOutflow;
            $prevNetProfit    = $prevInflow - $prevOutflow;

            // 3. Hitung Growth Rate MoM (%)
            $revenueGrowthRate = $prevInflow > 0
                ? round((($currentInflow - $prevInflow) / $prevInflow) * 100, 2)
                : ($currentInflow > 0 ? 100 : 0);

            $profitGrowthRate = $prevNetProfit > 0
                ? round((($currentNetProfit - $prevNetProfit) / $prevNetProfit) * 100, 2)
                : 0;

            // 4. Profit Margin (%)
            $profitMargin = $currentInflow > 0
                ? round(($currentNetProfit / $currentInflow) * 100, 2)
                : 0;

            // 5. Total Ekuitas Saham
            $totalShares = (int) ShareHolding::where('status', 'active')->sum('total_shares');
            $totalInvested = (float) ShareHolding::where('status', 'active')->sum('total_investment');

            // Valuasi perusahaan terkini = Modal Dasar Terdaftar + Akumulasi Laba Bersih Tahunan
            $currentValuation = $totalInvested + ($currentNetProfit > 0 ? ($currentNetProfit * 12) : 0);
            $bookValuePerShare = $totalShares > 0 ? round($currentValuation / $totalShares, 2) : 10000;
            $valuationGrowth = $totalInvested > 0 ? round((($currentValuation - $totalInvested) / $totalInvested) * 100, 2) : 0;

            // 6. Tentukan Status & Skor Pertumbuhan Perusahaan
            $growthStatus = $this->determineGrowthStatus($revenueGrowthRate, $profitGrowthRate, $profitMargin, $currentInflow);
            $healthScore  = $this->calculateHealthScore($revenueGrowthRate, $profitMargin, $currentNetProfit, $currentInflow);

            return [
                'status'               => $growthStatus['status'],
                'status_label'         => $growthStatus['label'],
                'status_badge_class'   => $growthStatus['badge_class'],
                'status_icon'          => $growthStatus['icon'],
                'status_description'   => $growthStatus['description'],
                'health_score'         => $healthScore,
                'revenue_growth_rate'  => $revenueGrowthRate,
                'profit_growth_rate'   => $profitGrowthRate,
                'profit_margin'        => $profitMargin,
                'current_inflow'       => $currentInflow,
                'current_outflow'      => $currentOutflow,
                'current_net_profit'   => $currentNetProfit,
                'total_shares'         => $totalShares,
                'initial_capital'      => $totalInvested,
                'current_valuation'    => $currentValuation,
                'valuation_growth'     => $valuationGrowth,
                'book_value_per_share' => $bookValuePerShare,
            ];
        });
    }

    /**
     * Mengambil log gabungan dari cache database atau API riil.
     */
    protected function getConsolidatedLogs(): array
    {
        $cached = FinanceLogCache::latest('log_created_at')->limit(50)->get();
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

        $apiResult = $this->financeApi->getActivityLogs();
        return $apiResult['data'] ?? [];
    }

    /**
     * Tentukan status pertumbuhan bisnis secara dinamis.
     */
    protected function determineGrowthStatus(float $revenueGrowth, float $profitGrowth, float $margin, float $currentInflow): array
    {
        if ($currentInflow === 0.0) {
            return [
                'status' => 'stable',
                'label' => 'Perusahaan Siap Beroperasi (Fase Awal / Standby)',
                'badge_class' => 'growth-badge-steady',
                'icon' => '🌱',
                'description' => 'Modal ekuitas terdaftar dan siap mengakumulasi transaksi arus kas dari web operasional, Xendit gateway, dan client unit lainnya.',
            ];
        }

        if ($revenueGrowth >= 15 && $margin >= 20) {
            return [
                'status' => 'high_growth',
                'label' => 'Perusahaan Sedang Bertumbuh Pesat (High Growth)',
                'badge_class' => 'growth-badge-bullish',
                'icon' => '🚀',
                'description' => "Perusahaan mengalami ekspansi positif dengan laju pertumbuhan pendapatan +{$revenueGrowth}% MoM dan margin laba bersih {$margin}%.",
            ];
        } elseif ($revenueGrowth > 0 && $margin > 0) {
            return [
                'status' => 'steady_growth',
                'label' => 'Perusahaan Bertumbuh Stabil (Steady Growth)',
                'badge_class' => 'growth-badge-steady',
                'icon' => '📈',
                'description' => "Kinerja keuangan positif dengan kenaikan laba MoM +{$profitGrowth}% dan arus kas operasional terjaga sehat.",
            ];
        } elseif ($revenueGrowth >= -5) {
            return [
                'status' => 'stable',
                'label' => 'Perusahaan Kondisi Stabil (Consolidation)',
                'badge_class' => 'growth-badge-stable',
                'icon' => '⚖️',
                'description' => "Kondisi arus kas dan perputaran operasional dalam kondisi seimbang di fase konsolidasi bisnis.",
            ];
        } else {
            return [
                'status' => 'declining',
                'label' => 'Perlu Perhatian (Contraction / Slowdown)',
                'badge_class' => 'growth-badge-declining',
                'icon' => '🔻',
                'description' => "Laju pertumbuhan mengalami perlambatan ({$revenueGrowth}% MoM). Diperlukan optimasi efisiensi biaya.",
            ];
        }
    }

    /**
     * Hitung Skor Kesehatan Usaha (0 - 100).
     */
    protected function calculateHealthScore(float $growthRate, float $margin, float $netProfit, float $currentInflow): int
    {
        if ($currentInflow === 0.0) {
            return 80; // Baseline sehat untuk fase awal modal disetor
        }

        $score = 60;
        if ($growthRate > 20) $score += 20;
        elseif ($growthRate > 5) $score += 10;
        elseif ($growthRate < 0) $score -= 15;

        if ($margin > 30) $score += 15;
        elseif ($margin > 10) $score += 10;
        elseif ($margin < 0) $score -= 20;

        if ($netProfit > 0) $score += 5;

        return max(10, min(100, $score));
    }
}
