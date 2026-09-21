<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;
use App\Models\Shareholder;
use App\Services\FinanceAnalyticsService;
use App\Services\ShareholderService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    protected FinanceAnalyticsService $analyticsService;
    protected ShareholderService $shareholderService;

    public function __construct(
        FinanceAnalyticsService $analyticsService,
        ShareholderService $shareholderService
    ) {
        $this->analyticsService   = $analyticsService;
        $this->shareholderService = $shareholderService;
    }

    public function index(Request $request)
    {
        $user = auth()->user();

        $date = $request->query('date');
        $timeframe = $date ? '1d' : $request->query('timeframe', '7d');
        $webFilter = $request->query('web_filter', 'ALL');
        $interval = in_array(strtolower($timeframe), ['1d', '24h', 'today', '1h']) ? 'hourly' : $request->query('interval', 'daily');

        // Pertumbuhan bisnis & valuasi saham dari CIO Finance
        $growthAnalytics = $this->analyticsService->getGrowth('ALL');
        $equitySummary   = $this->shareholderService->getEquitySummary();

        // Dataset Grafik Realtime Time-Series (Income, Outcome, Keuntungan) dari CIO Finance
        $chartData = $this->analyticsService->getChart($timeframe, $interval, $webFilter, false, $date);

        // 0. JIKA PENGGUNA BUKAN PENGELOLA/ADMIN (Berdasarkan Permission 'view-shareholders')
        //    -> Tampilkan Dashboard Portofolio Pemegang Saham Pribadi dengan Grafik Realtime Finansial
        if ($user && (!$user->can('view-shareholders') || $request->query('view') === 'investor')) {
            $shareholder = Shareholder::with(['holdings' => function ($q) {
                $q->where('status', 'active')->orderBy('id', 'asc');
            }])
            ->where('user_id', $user->id)
            ->orWhere('email', $user->email)
            ->first();

            if (!$shareholder) {
                $shareholder = Shareholder::with(['holdings' => function ($q) {
                    $q->where('status', 'active')->orderBy('id', 'asc');
                }])
                ->where('name', 'like', '%' . $user->name . '%')
                ->first();
            }

            // Hitung metrik kepemilikan saham pribadi pemegang saham ini
            $totalShares      = $shareholder ? (int) $shareholder->total_shares : 0;
            $totalInvestment  = $shareholder ? (float) $shareholder->total_investment : 0;
            $totalPercentage  = $shareholder ? (float) $shareholder->total_percentage : 0;
            $portfoliosCount  = $shareholder ? $shareholder->holdings->count() : 0;

            $companyValuation = (float) ($growthAnalytics['estimated_company_valuation'] ?? 3203779690);
            $myValuation      = $companyValuation * ($totalPercentage / 100);

            // Riwayat Mutasi & Transaksi Multi-Web Terkini
            $historyData = $this->analyticsService->getHistory([
                'client_code' => $webFilter,
                'per_page'    => 50,
            ]);
            $recentLogs = $historyData['items'] ?? [];

            return view('pages.shareholders.dashboard', compact(
                'shareholder',
                'user',
                'growthAnalytics',
                'equitySummary',
                'chartData',
                'recentLogs',
                'timeframe',
                'webFilter',
                'date',
                'totalShares',
                'totalInvestment',
                'totalPercentage',
                'portfoliosCount',
                'companyValuation',
                'myValuation'
            ));
        }

        // 1. Ringkasan Eksekutif & Saldo Konsolidasi dari CIO Finance
        $overviewData = $this->analyticsService->getOverview('ALL');

        // 2. Indikator Pertumbuhan Bisnis & Valuasi Saham dari CIO Finance
        $growthAnalytics = $this->analyticsService->getGrowth('ALL');

        // 3. Dataset Grafik Time-Series Multi-Web ApexCharts dari CIO Finance
        $interval = in_array(strtolower($timeframe), ['1d', '24h', 'today', '1h']) ? 'hourly' : $request->query('interval', 'daily');
        $chartData = $this->analyticsService->getChart($timeframe, $interval, $webFilter, false, $date);

        // 4. Riwayat Mutasi & Transaksi Multi-Web Terkini
        $historyData = $this->analyticsService->getHistory([
            'client_code' => $webFilter,
            'per_page'    => 50,
        ]);
        $recentLogs = $historyData['items'] ?? [];

        // 5. Ringkasan Ekuitas Pemegang Saham (Lokal)
        $equitySummary = $this->shareholderService->getEquitySummary();
        $topShareholders = Shareholder::with('activeHoldings')
            ->where('status', 'active')
            ->take(5)
            ->get();

        // 6. Rincian Arus Log per Unit Web Berdasarkan Filter Rentang Tanggal (Default: Hari ini / Now)
        $startDate = $request->query('start_date', \Carbon\Carbon::now()->format('Y-m-d'));
        $endDate   = $request->query('end_date', \Carbon\Carbon::now()->format('Y-m-d'));
        $webBreakdown = $this->analyticsService->getWebBreakdownRange($startDate, $endDate);

        return view('pages.dashboard', compact(
            'overviewData',
            'growthAnalytics',
            'chartData',
            'equitySummary',
            'topShareholders',
            'recentLogs',
            'timeframe',
            'webFilter',
            'date',
            'startDate',
            'endDate',
            'webBreakdown'
        ));
    }
}
