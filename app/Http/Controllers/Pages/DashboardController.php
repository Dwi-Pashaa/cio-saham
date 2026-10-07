<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\CashIncome;
use App\Models\CashOutcome;
use App\Models\Shareholder;
use App\Services\ShareholderService;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    protected ShareholderService $shareholderService;

    public function __construct(ShareholderService $shareholderService)
    {
        $this->shareholderService = $shareholderService;
    }

    public function index(Request $request)
    {
        $user = auth()->user();
        $equitySummary = $this->shareholderService->getEquitySummary();

        // Metrik Saldo Kas Terkini (Total Income - Total Outcome)
        $totalCashIncome    = (float) CashIncome::sum('net_amount');
        $totalCashOutcome   = (float) CashOutcome::sum('total_amount');
        $currentCashBalance = $totalCashIncome - $totalCashOutcome;

        // Metrik Total Asset (Murni akumulasi harga seluruh aset tanpa pengurangan apapun)
        $totalAssetValue    = (float) Asset::sum('price');
        $totalAssetCount    = (int) Asset::count();

        // Data Grafik Finansial & Asset (30 hari terakhir sebagai default)
        $chartData = $this->getFinanceChartData('30d');

        // Rincian Ringkasan Keuangan (Pemasukan, Pengeluaran, Asset) + filter tanggal opsional
        $startDate = $request->query('start_date');
        $endDate   = $request->query('end_date');
        $financeSummary = $this->getFinanceSummary($startDate, $endDate);

        // Struktur Kepemilikan Saham (Top 5 berdasarkan total lembar aktif)
        $topShareholders = Shareholder::where('status', 'active')
            ->withCount('activeHoldings')
            ->withSum('activeHoldings as active_shares_sum', 'total_shares')
            ->withSum('activeHoldings as active_percentage_sum', 'percentage_share')
            ->orderByDesc('active_shares_sum')
            ->take(5)
            ->get();

        if ($user && (!$user->can('lihat dashboard perusahaan') || $request->query('view') === 'investor')) {
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

            return view('pages.shareholders.dashboard', compact(
                'shareholder',
                'user',
                'equitySummary',
                'totalShares',
                'totalInvestment',
                'totalPercentage',
                'portfoliosCount',
                'totalCashIncome',
                'totalCashOutcome',
                'currentCashBalance',
                'totalAssetValue',
                'totalAssetCount',
                'chartData'
            ));
        }

        return view('pages.dashboard', compact(
            'equitySummary',
            'totalCashIncome',
            'totalCashOutcome',
            'currentCashBalance',
            'totalAssetValue',
            'totalAssetCount',
            'chartData',
            'financeSummary',
            'topShareholders',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Ringkasan total Pemasukan, Pengeluaran, dan Asset (opsional difilter rentang tanggal).
     */
    protected function getFinanceSummary(?string $startDate, ?string $endDate): array
    {
        $income  = CashIncome::query();
        $outcome = CashOutcome::query();
        $asset   = Asset::query();

        if ($startDate) {
            $income->whereDate('transaction_date', '>=', $startDate);
            $outcome->whereDate('transaction_date', '>=', $startDate);
            $asset->whereRaw('DATE(COALESCE(purchase_date, created_at)) >= ?', [$startDate]);
        }
        if ($endDate) {
            $income->whereDate('transaction_date', '<=', $endDate);
            $outcome->whereDate('transaction_date', '<=', $endDate);
            $asset->whereRaw('DATE(COALESCE(purchase_date, created_at)) <= ?', [$endDate]);
        }

        $incomeTotal  = (float) (clone $income)->sum('net_amount');
        $outcomeTotal = (float) (clone $outcome)->sum('total_amount');
        $assetTotal   = (float) (clone $asset)->sum('price');

        return [
            'rows' => [
                [
                    'key'        => 'income',
                    'label'      => 'Pemasukan',
                    'desc'       => 'Total kas masuk bersih',
                    'total'      => $incomeTotal,
                    'count'      => (int) $income->count(),
                    'unit'       => 'Transaksi',
                    'route'      => 'cash-incomes.index',
                    'permission' => 'lihat pemasukan',
                    'color'      => 'success',
                ],
                [
                    'key'        => 'outcome',
                    'label'      => 'Pengeluaran',
                    'desc'       => 'Total kas keluar termasuk biaya admin',
                    'total'      => $outcomeTotal,
                    'count'      => (int) $outcome->count(),
                    'unit'       => 'Transaksi',
                    'route'      => 'cash-outcomes.index',
                    'permission' => 'lihat pengeluaran',
                    'color'      => 'danger',
                ],
                [
                    'key'        => 'asset',
                    'label'      => 'Asset',
                    'desc'       => 'Total nilai perolehan aset',
                    'total'      => $assetTotal,
                    'count'      => (int) $asset->count(),
                    'unit'       => 'Unit',
                    'route'      => 'assets.index',
                    'permission' => 'lihat aset',
                    'color'      => 'primary',
                ],
            ],
            'cash_balance' => $incomeTotal - $outcomeTotal,
            'grand_total'  => $incomeTotal + $outcomeTotal + $assetTotal,
        ];
    }

    /**
     * Endpoint AJAX untuk filter range grafik interaktif.
     */
    public function chartData(Request $request)
    {
        $range = (string) $request->query('range', '30d');
        return response()->json($this->getFinanceChartData($range));
    }

    /**
     * Menghasilkan data deret waktu untuk 3 garis:
     * - Hijau: Pemasukan (CashIncome)
     * - Merah: Pengeluaran (CashOutcome)
     * - Biru : Asset (Asset)
     */
    public function getFinanceChartData(string $range = '30d'): array
    {
        $categories  = [];
        $incomeData  = [];
        $outcomeData = [];
        $assetData   = [];

        $now = Carbon::now();

        if ($range === '7d' || $range === '30d' || $range === 'this_month') {
            if ($range === '7d') {
                $startDate = $now->copy()->subDays(6)->startOfDay();
                $endDate   = $now->copy()->endOfDay();
            } elseif ($range === 'this_month') {
                $startDate = $now->copy()->startOfMonth()->startOfDay();
                $endDate   = $now->copy()->endOfDay();
            } else { // 30d
                $startDate = $now->copy()->subDays(29)->startOfDay();
                $endDate   = $now->copy()->endOfDay();
            }

            $period   = CarbonPeriod::create($startDate, $endDate);
            $startStr = $startDate->toDateString();
            $endStr   = $endDate->toDateString();

            $incomes = CashIncome::whereBetween('transaction_date', [$startStr, $endStr])
                ->selectRaw('DATE(transaction_date) as t_date, SUM(net_amount) as total')
                ->groupBy('t_date')
                ->pluck('total', 't_date')
                ->all();

            $outcomes = CashOutcome::whereBetween('transaction_date', [$startStr, $endStr])
                ->selectRaw('DATE(transaction_date) as t_date, SUM(total_amount) as total')
                ->groupBy('t_date')
                ->pluck('total', 't_date')
                ->all();

            $assets = Asset::selectRaw('DATE(COALESCE(purchase_date, created_at)) as t_date, SUM(price) as total')
                ->whereRaw('DATE(COALESCE(purchase_date, created_at)) BETWEEN ? AND ?', [$startStr, $endStr])
                ->groupBy('t_date')
                ->pluck('total', 't_date')
                ->all();

            foreach ($period as $dt) {
                $dateKey = $dt->format('Y-m-d');
                $categories[]  = $dt->translatedFormat('d M');
                $incomeData[]  = (float) ($incomes[$dateKey] ?? 0);
                $outcomeData[] = (float) ($outcomes[$dateKey] ?? 0);
                $assetData[]   = (float) ($assets[$dateKey] ?? 0);
            }
        } elseif ($range === 'year') {
            $startOfYear = $now->copy()->startOfYear()->toDateString();
            $endOfYear   = $now->copy()->endOfYear()->toDateString();

            $incomes = CashIncome::whereBetween('transaction_date', [$startOfYear, $endOfYear])
                ->selectRaw('MONTH(transaction_date) as m_idx, SUM(net_amount) as total')
                ->groupBy('m_idx')
                ->pluck('total', 'm_idx')
                ->all();

            $outcomes = CashOutcome::whereBetween('transaction_date', [$startOfYear, $endOfYear])
                ->selectRaw('MONTH(transaction_date) as m_idx, SUM(total_amount) as total')
                ->groupBy('m_idx')
                ->pluck('total', 'm_idx')
                ->all();

            $assets = Asset::selectRaw('MONTH(COALESCE(purchase_date, created_at)) as m_idx, SUM(price) as total')
                ->whereRaw('DATE(COALESCE(purchase_date, created_at)) BETWEEN ? AND ?', [$startOfYear, $endOfYear])
                ->groupBy('m_idx')
                ->pluck('total', 'm_idx')
                ->all();

            $monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
            for ($m = 1; $m <= 12; $m++) {
                $categories[]  = $monthNames[$m - 1];
                $incomeData[]  = (float) ($incomes[$m] ?? 0);
                $outcomeData[] = (float) ($outcomes[$m] ?? 0);
                $assetData[]   = (float) ($assets[$m] ?? 0);
            }
        }

        return [
            'range'      => $range,
            'categories' => $categories,
            'series'     => [
                [
                    'name'  => 'Pemasukan',
                    'color' => '#10b981', // Hijau
                    'data'  => $incomeData,
                ],
                [
                    'name'  => 'Pengeluaran',
                    'color' => '#ef4444', // Merah
                    'data'  => $outcomeData,
                ],
                [
                    'name'  => 'Asset',
                    'color' => '#206bc4', // Biru
                    'data'  => $assetData,
                ],
            ],
            'summary' => [
                'total_income'  => array_sum($incomeData),
                'total_outcome' => array_sum($outcomeData),
                'total_asset'   => array_sum($assetData),
            ],
        ];
    }
}
