<?php

namespace App\Services;

use App\Models\FinanceLogCache;
use App\Services\FinanceClient\CioFinanceApiClient;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class FinanceAnalyticsService
{
    protected CioFinanceApiClient $apiClient;
    protected ShareholderService $shareholderService;

    public function __construct(CioFinanceApiClient $apiClient, ShareholderService $shareholderService)
    {
        $this->apiClient          = $apiClient;
        $this->shareholderService = $shareholderService;
    }

    /**
     * Ambil data Ringkasan Eksekutif & Saldo Konsolidasi (dengan cache 5 menit).
     */
    public function getOverview(string $clientCode = 'ALL', bool $fresh = false): array
    {
        $cacheKey = 'cio_finance_overview_' . md5($clientCode);

        if (!$fresh && Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $res = $this->apiClient->getOverview($clientCode);
        $data = $res['data'] ?? [];

        // Gabungkan dengan ringkasan ekuitas modal saham lokal
        $equity = $this->shareholderService->getEquitySummary();
        $data['equity_summary'] = $equity;

        try {
            $ttl = (!empty($res['success'])) ? 300 : 30;
            Cache::put($cacheKey, $data, $ttl);
        } catch (\Throwable $e) {}

        return $data;
    }

    /**
     * Ambil data Time-Series Grafik Arus Kas Inflow vs Outflow vs Net Profit (dengan cache 5 menit).
     */
    public function getChart(string $range = '7d', string $interval = 'daily', string $clientCode = 'ALL', bool $fresh = false, ?string $date = null): array
    {
        $dateKey = $date ? str_replace('-', '', $date) : 'TODAY';
        $cacheKey = 'cio_finance_chart_' . md5("{$range}_{$interval}_{$clientCode}_{$dateKey}");

        if (!$fresh && Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $res = $this->apiClient->getChart($range, $interval, $clientCode, $date);
        $data = $res['data'] ?? [];

        // Jika web_breakdown kosong dari response API pusat, hitung dari database log lokal
        if (empty($data['web_breakdown'])) {
            $data['web_breakdown'] = $this->getWebBreakdown($range, $date);
        }

        try {
            $ttl = (!empty($res['success'])) ? 300 : 30;
            Cache::put($cacheKey, $data, $ttl);
        } catch (\Throwable $e) {}

        return $data;
    }

    /**
     * Hitung ringkasan akumulasi Inflow, Outflow, Keuntungan (Net Profit), dan Event per Unit Web
     * berdasarkan rentang tanggal (start_date s/d end_date, default: hari ini / now).
     */
    public function getWebBreakdownRange(?string $startDate = null, ?string $endDate = null): array
    {
        $start = $startDate ? Carbon::parse($startDate)->startOfDay() : Carbon::now()->startOfDay();
        $end   = $endDate ? Carbon::parse($endDate)->endOfDay() : Carbon::now()->endOfDay();

        $logs = FinanceLogCache::whereBetween('log_created_at', [$start, $end])->get();

        $knownNames = [
            'CIO_FINANCE'     => 'CIO Finance',
            'WEB_SLIP_GAJI'   => 'Web Slip Gaji',
            'XENDIT'          => 'Gateway Xendit',
            'CIO_OPERASIONAL' => 'CIO Operasional',
            'CIO_INVESTOR'    => 'CIO Investor Portal',
        ];

        $webData = [];
        foreach ($knownNames as $c => $n) {
            $webData[$c] = [
                'code'          => $c,
                'name'          => $n,
                'inflow'        => 0.0,
                'outflow'       => 0.0,
                'net_profit'    => 0.0,
                'log_count'     => 0,
            ];
        }

        foreach ($logs as $log) {
            $code = strtoupper($log->source_client_code ?: 'GENERAL');
            $name = $knownNames[$code] ?? ($log->source_client_name ?: $code);
            $amount = (float) ($log->amount ?? 0);
            $type = strtolower($log->subject_type ?? '');
            $event = strtolower($log->event ?? ($log->raw_payload['event'] ?? 'created'));

            if (!isset($webData[$code])) {
                $webData[$code] = [
                    'code'          => $code,
                    'name'          => $name,
                    'inflow'        => 0.0,
                    'outflow'       => 0.0,
                    'net_profit'    => 0.0,
                    'log_count'     => 0,
                ];
            }

            if ($event === 'deleted' || $event === 'updated') {
                continue;
            }

            $webData[$code]['log_count']++;

            if (str_contains($type, 'income')) {
                $webData[$code]['inflow'] += $amount;
            } elseif (str_contains($type, 'expense') || str_contains($type, 'deduct')) {
                $webData[$code]['outflow'] += $amount;
            }
        }

        $webList = [];
        foreach ($webData as $w) {
            $w['net_profit'] = $w['inflow'] - $w['outflow'];
            $webList[] = $w;
        }

        usort($webList, function ($a, $b) {
            $volA = $a['inflow'] + $a['outflow'] + ($a['log_count'] * 1000);
            $volB = $b['inflow'] + $b['outflow'] + ($b['log_count'] * 1000);
            return $volB <=> $volA;
        });

        return $webList;
    }

    /**
     * Hitung ringkasan akumulasi Inflow, Outflow, dan Event per Unit Web
     */
    public function getWebBreakdown(?string $range = null, ?string $date = null): array
    {
        if ($date) {
            return $this->getWebBreakdownRange($date, $date);
        }

        $days = match ($range) {
            '1d', '24h', 'today' => 1,
            '7d' => 7,
            '30d', '1m' => 30,
            '3m' => 90,
            '1y' => 365,
            default => 7,
        };

        $startDate = Carbon::now()->subDays($days - 1)->startOfDay();
        $logs = FinanceLogCache::where('log_created_at', '>=', $startDate)->get();

        if ($logs->isEmpty()) {
            $logs = FinanceLogCache::all();
        }

        $knownNames = [
            'CIO_FINANCE'     => 'CIO Finance',
            'WEB_SLIP_GAJI'   => 'Web Slip Gaji',
            'XENDIT'          => 'Gateway Xendit',
            'CIO_OPERASIONAL' => 'CIO Operasional',
            'CIO_INVESTOR'    => 'CIO Investor Portal',
        ];

        $webData = [];
        foreach ($knownNames as $c => $n) {
            $webData[$c] = [
                'code'          => $c,
                'name'          => $n,
                'inflow'        => 0.0,
                'outflow'       => 0.0,
                'total_inflow'  => 0.0,
                'total_outflow' => 0.0,
                'net_profit'    => 0.0,
                'log_count'     => 0,
            ];
        }

        foreach ($logs as $log) {
            $code = strtoupper($log->source_client_code ?: 'GENERAL');
            $name = $knownNames[$code] ?? ($log->source_client_name ?: $code);
            $amount = (float) ($log->amount ?? 0);
            $type = strtolower($log->subject_type ?? '');
            $event = strtolower($log->event ?? ($log->raw_payload['event'] ?? 'created'));

            if (!isset($webData[$code])) {
                $webData[$code] = [
                    'code'          => $code,
                    'name'          => $name,
                    'inflow'        => 0.0,
                    'outflow'       => 0.0,
                    'total_inflow'  => 0.0,
                    'total_outflow' => 0.0,
                    'net_profit'    => 0.0,
                    'log_count'     => 0,
                ];
            }

            if ($event === 'deleted' || $event === 'updated') {
                continue;
            }

            $webData[$code]['log_count']++;

            if (str_contains($type, 'income')) {
                $webData[$code]['inflow'] += $amount;
                $webData[$code]['total_inflow'] += $amount;
            } elseif (str_contains($type, 'expense') || str_contains($type, 'deduct')) {
                $webData[$code]['outflow'] += $amount;
                $webData[$code]['total_outflow'] += $amount;
            }
        }

        $webList = [];
        foreach ($webData as $w) {
            $w['net_profit'] = $w['total_inflow'] - $w['total_outflow'];
            $webList[] = $w;
        }

        usort($webList, function ($a, $b) {
            $volA = $a['total_inflow'] + $a['total_outflow'] + ($a['log_count'] * 1000);
            $volB = $b['total_inflow'] + $b['total_outflow'] + ($b['log_count'] * 1000);
            return $volB <=> $volA;
        });

        return $webList;
    }

    /**
     * Ambil data laporan harian lengkap mencakup rincian pos transaksi uang masuk/keluar per unit web
     * serta pentotalan konsolidasi harian.
     */
    public function getDailyDetailedReportData(?string $date = null, bool $fallbackToAllIfEmpty = false): array
    {
        $targetDate = $date ?: Carbon::now()->format('Y-m-d');
        $logs = FinanceLogCache::whereDate('log_created_at', $targetDate)->get();

        // Fallback seluruh log hanya jika diminta (misal demo/preview khusus)
        if ($logs->isEmpty() && $fallbackToAllIfEmpty) {
            $logs = FinanceLogCache::all();
        }

        $knownNames = [
            'CIO_FINANCE'     => 'CIO Finance',
            'WEB_SLIP_GAJI'   => 'Web Slip Gaji',
            'XENDIT'          => 'Gateway Xendit',
            'CIO_OPERASIONAL' => 'CIO Operasional',
            'CIO_INVESTOR'    => 'CIO Investor Portal',
        ];

        $webData = [];
        foreach ($knownNames as $c => $n) {
            $webData[$c] = [
                'code'          => $c,
                'name'          => $n,
                'inflow_items'  => [],
                'outflow_items' => [],
                'total_inflow'  => 0.0,
                'total_outflow' => 0.0,
                'net_profit'    => 0.0,
                'log_count'     => 0,
            ];
        }

        foreach ($logs as $log) {
            $code = strtoupper($log->source_client_code ?: 'GENERAL');
            $name = $knownNames[$code] ?? ($log->source_client_name ?: $code);
            $amount = (float) ($log->amount ?? 0);
            $type = strtolower($log->subject_type ?? '');
            $event = strtolower($log->event ?? ($log->raw_payload['event'] ?? 'created'));
            $props = $log->raw_payload['properties'] ?? [];
            $desc = $log->description ?: '';

            if (!isset($webData[$code])) {
                $webData[$code] = [
                    'code'          => $code,
                    'name'          => $name,
                    'inflow_items'  => [],
                    'outflow_items' => [],
                    'total_inflow'  => 0.0,
                    'total_outflow' => 0.0,
                    'net_profit'    => 0.0,
                    'log_count'     => 0,
                ];
            }

            if ($event === 'deleted' || $event === 'updated') {
                continue;
            }

            // Normalisasi judul deskripsi item
            $itemDesc = $desc;
            if (!empty($props['attributes']['description'])) {
                $itemDesc = $props['attributes']['description'];
            } elseif (!empty($props['note'])) {
                $itemDesc = $props['note'];
            } elseif (!empty($props['attributes']['source'])) {
                $itemDesc = $props['attributes']['source'];
            } elseif (!empty($props['attributes']['payee'])) {
                $itemDesc = $props['attributes']['payee'];
            }

            if (empty($itemDesc) || $itemDesc === 'created') {
                $itemDesc = (!empty($props['attributes']['source'])) 
                    ? $props['attributes']['source'] 
                    : ((!empty($props['attributes']['payee'])) ? $props['attributes']['payee'] : ($log->subject_type ?? 'Transaksi'));
            }

            // Potong whitespace berlebih jika ada
            $itemDesc = trim(preg_replace('/\s+/', ' ', $itemDesc));

            $webData[$code]['log_count']++;

            if (str_contains($type, 'income')) {
                $webData[$code]['total_inflow'] += $amount;
                $webData[$code]['inflow_items'][] = [
                    'title'  => $itemDesc,
                    'amount' => $amount,
                    'time'   => $log->log_created_at ? $log->log_created_at->format('H:i') : '-',
                ];
            } elseif (str_contains($type, 'expense') || str_contains($type, 'deduct')) {
                $webData[$code]['total_outflow'] += $amount;
                $webData[$code]['outflow_items'][] = [
                    'title'  => $itemDesc,
                    'amount' => $amount,
                    'time'   => $log->log_created_at ? $log->log_created_at->format('H:i') : '-',
                ];
            }
        }

        $webList = [];
        $totalInflow = 0.0;
        $totalOutflow = 0.0;

        foreach ($webData as $w) {
            $totalInflow += $w['total_inflow'];
            $totalOutflow += $w['total_outflow'];
        }

        // Jika log lokal kosong tapi ada data di Chart API pusat untuk tanggal ini, gunakan data analitik API
        if ($totalInflow == 0 && $totalOutflow == 0) {
            try {
                $chartRes = $this->apiClient->getChart('1d', 'hourly', 'ALL', $targetDate);
                $chartData = $chartRes['data'] ?? [];
                $summary = $chartData['summary'] ?? [];
                $chartInflow = (float) ($summary['total_inflow'] ?? 0);
                $chartOutflow = (float) ($summary['total_outflow'] ?? 0);

                if ($chartInflow > 0 || $chartOutflow > 0) {
                    $categories = $chartData['categories'] ?? [];
                    $inflowSeries = [];
                    $outflowSeries = [];

                    foreach (($chartData['series'] ?? []) as $s) {
                        $sName = strtolower($s['name'] ?? '');
                        if (str_contains($sName, 'pemasukan') || str_contains($sName, 'inflow')) {
                            $inflowSeries = $s['data'] ?? [];
                        } elseif (str_contains($sName, 'pengeluaran') || str_contains($sName, 'outflow')) {
                            $outflowSeries = $s['data'] ?? [];
                        }
                    }

                    $inflowItems = [];
                    foreach ($inflowSeries as $idx => $val) {
                        $amt = (float) $val;
                        if ($amt > 0) {
                            $timeStr = $categories[$idx] ?? sprintf('%02d:00', $idx);
                            $inflowItems[] = [
                                'title'  => 'Penerimaan Transaksi Gateway (QRIS / Channel)',
                                'amount' => $amt,
                                'time'   => $timeStr,
                            ];
                        }
                    }

                    $outflowItems = [];
                    foreach ($outflowSeries as $idx => $val) {
                        $amt = (float) $val;
                        if ($amt > 0) {
                            $timeStr = $categories[$idx] ?? sprintf('%02d:00', $idx);
                            $outflowItems[] = [
                                'title'  => 'Biaya Operasional / Pengeluaran Terjadwal',
                                'amount' => $amt,
                                'time'   => $timeStr,
                            ];
                        }
                    }

                    $webData['XENDIT']['inflow_items']  = $inflowItems;
                    $webData['XENDIT']['outflow_items'] = $outflowItems;
                    $webData['XENDIT']['total_inflow']  = $chartInflow;
                    $webData['XENDIT']['total_outflow'] = $chartOutflow;
                    $webData['XENDIT']['net_profit']    = $chartInflow - $chartOutflow;
                    $webData['XENDIT']['log_count']     = count($inflowItems) + count($outflowItems);
                }
            } catch (\Throwable $e) {
                Log::warning("Gagal mengambil fallback live chart API: " . $e->getMessage());
            }
        }

        $webList = [];
        $totalInflow = 0.0;
        $totalOutflow = 0.0;

        foreach ($webData as $w) {
            $w['net_profit'] = $w['total_inflow'] - $w['total_outflow'];
            $totalInflow += $w['total_inflow'];
            $totalOutflow += $w['total_outflow'];
            $webList[] = $w;
        }

        usort($webList, function ($a, $b) {
            $volA = $a['total_inflow'] + $a['total_outflow'] + ($a['log_count'] * 1000);
            $volB = $b['total_inflow'] + $b['total_outflow'] + ($b['log_count'] * 1000);
            return $volB <=> $volA;
        });

        $netProfit = $totalInflow - $totalOutflow;
        $marginPct = $totalInflow > 0 ? ($netProfit / $totalInflow) * 100 : 0.0;

        return [
            'date'              => $targetDate,
            'total_inflow'      => $totalInflow,
            'total_outflow'     => $totalOutflow,
            'net_profit'        => $netProfit,
            'profit_margin_pct' => $marginPct,
            'web_breakdown'     => $webList,
        ];
    }

    /**
     * Ambil Indikator Pertumbuhan Bisnis & Valuasi Saham (dengan cache 5 menit).
     */
    public function getGrowth(string $clientCode = 'ALL', bool $fresh = false): array
    {
        $cacheKey = 'cio_finance_growth_' . md5($clientCode);

        if (!$fresh && Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $res = $this->apiClient->getGrowth($clientCode);
        $data = $res['data'] ?? [];

        try {
            $ttl = (!empty($res['success'])) ? 300 : 30;
            Cache::put($cacheKey, $data, $ttl);
        } catch (\Throwable $e) {}

        return $data;
    }

    /**
     * Ambil Riwayat Log Transaksi Multi-Web langsung dari Database.
     */
    public function getHistory(array $params = [], bool $fresh = false): array
    {
        $clientCode = $params['client_code'] ?? 'ALL';
        $search = $params['search'] ?? null;

        if ($clientCode === 'ALL') {
            // Ambil SEMUA log internal (Slip Gaji, Keuangan, Operasional) langsung dari database
            $internalQuery = FinanceLogCache::where('source_client_code', '!=', 'XENDIT')
                ->latest('log_created_at');
            
            // Ambil log Xendit terbaru langsung dari database
            $xenditQuery = FinanceLogCache::where('source_client_code', 'XENDIT')
                ->latest('log_created_at')
                ->limit(50);

            if (!empty($search)) {
                $internalQuery->where(function ($q) use ($search) {
                    $q->where('description', 'like', "%{$search}%")
                      ->orWhere('source_client_code', 'like', "%{$search}%");
                });
                $xenditQuery->where(function ($q) use ($search) {
                    $q->where('description', 'like', "%{$search}%")
                      ->orWhere('source_client_code', 'like', "%{$search}%");
                });
            }

            $internalItems = $internalQuery->get();
            $xenditItems = $xenditQuery->get();

            // Gabungkan kedua koleksi langsung dari database
            $allItems = $internalItems->merge($xenditItems);

            return [
                'items' => $allItems->toArray(),
                'pagination' => [
                    'current_page' => 1,
                    'last_page'    => 1,
                    'per_page'     => $allItems->count(),
                    'total'        => FinanceLogCache::count(),
                ],
            ];
        } else {
            $cachedQuery = FinanceLogCache::latest('log_created_at')
                ->where('source_client_code', $clientCode);

            if (!empty($search)) {
                $cachedQuery->where(function ($q) use ($search) {
                    $q->where('description', 'like', "%{$search}%")
                      ->orWhere('source_client_code', 'like', "%{$search}%");
                });
            }

            $perPage = (int) ($params['per_page'] ?? 50);
            $localPaginated = $cachedQuery->paginate($perPage);

            return [
                'items' => $localPaginated->items(),
                'pagination' => [
                    'current_page' => $localPaginated->currentPage(),
                    'last_page'    => $localPaginated->lastPage(),
                    'per_page'     => $localPaginated->perPage(),
                    'total'        => $localPaginated->total(),
                ],
            ];
        }
    }

    /**
     * Sinkronisasi paksa dari CIO Finance API ke database lokal FinanceLogCache.
     * Mencegah data ganda (duplikat): jika record sudah ada dan tidak ada perubahan data, tidak akan dibuat ulang.
     */
    public function syncFinanceLogs(): array
    {
        $syncedCount = 0;
        $insertedCount = 0;
        $updatedCount = 0;
        $unchangedCount = 0;
        $page = 1;
        $lastPage = 1;

        do {
            $historyRes = $this->apiClient->getHistory([
                'client_code' => 'ALL',
                'per_page'    => 50,
                'page'        => $page,
            ]);

            $items = $historyRes['data']['items'] ?? [];
            $pagination = $historyRes['data']['pagination'] ?? [];
            $lastPage = (int) ($pagination['last_page'] ?? 1);

            if (empty($items)) {
                break;
            }

            foreach ($items as $item) {
                $rawAmount = (float) ($item['amount'] ?? 0);
                $props = $item['properties'] ?? [];
                
                // Normalisasi amount jika di root 0 tapi ada di properties
                $amount = $rawAmount;
                if ($amount == 0) {
                    if (!empty($props['attributes']['amount'])) {
                        $amount = (float) $props['attributes']['amount'];
                    } elseif (!empty($props['old']['amount'])) {
                        $amount = (float) $props['old']['amount'];
                    }
                }

                $sourceCode = strtoupper($item['source_client_code'] ?? 'GENERAL');
                $createdAt = isset($item['created_at']) ? Carbon::parse($item['created_at'])->format('Y-m-d H:i:s') : Carbon::now()->format('Y-m-d H:i:s');
                $event = $item['event'] ?? 'created';
                $subjectType = $item['subject_type'] ?? 'General';
                $description = trim($item['description'] ?? '');
                $clientName = $item['source_client_name'] ?? ($sourceCode === 'XENDIT' ? 'Xendit Gateway' : 'General Web');
                $balanceType = $item['balance_type'] ?? ($sourceCode === 'XENDIT' ? 'xendit' : 'manual');

                // Cari apakah record ini sudah ada sebelumnya (berdasarkan client, waktu log, dan deskripsi/amount)
                $existing = FinanceLogCache::where('source_client_code', $sourceCode)
                    ->where('log_created_at', $createdAt)
                    ->where(function ($q) use ($description, $subjectType, $amount) {
                        if (!empty($description)) {
                            $q->where('description', $description);
                        } else {
                            $q->where('subject_type', $subjectType)->where('amount', $amount);
                        }
                    })
                    ->first();

                if ($existing) {
                    // Cek apakah ada perubahan data. Jika data sama persis, lewati dan jangan gandakan
                    $needsUpdate = false;
                    if ((float)$existing->amount !== (float)$amount) {
                        $existing->amount = $amount;
                        $needsUpdate = true;
                    }
                    if ($existing->event !== $event) {
                        $existing->event = $event;
                        $needsUpdate = true;
                    }
                    if ($existing->subject_type !== $subjectType) {
                        $existing->subject_type = $subjectType;
                        $needsUpdate = true;
                    }
                    if ($existing->source_client_name !== $clientName) {
                        $existing->source_client_name = $clientName;
                        $needsUpdate = true;
                    }
                    if ($existing->balance_type !== $balanceType) {
                        $existing->balance_type = $balanceType;
                        $needsUpdate = true;
                    }

                    if ($needsUpdate) {
                        $existing->raw_payload = $item;
                        $existing->save();
                        $updatedCount++;
                    } else {
                        $unchangedCount++;
                    }
                } else {
                    // Record baru belum pernah ada, buat record baru
                    FinanceLogCache::create([
                        'source_client_code' => $sourceCode,
                        'source_client_name' => $clientName,
                        'event'              => $event,
                        'subject_type'       => $subjectType,
                        'amount'             => $amount,
                        'balance_type'       => $balanceType,
                        'description'        => $description,
                        'log_created_at'     => $createdAt,
                        'raw_payload'        => $item,
                    ]);
                    $insertedCount++;
                }

                $syncedCount++;
            }

            $page++;
        } while ($page <= $lastPage && $page <= 50);

        // Flush caches
        Cache::forget('cio_finance_overview_ALL');
        Cache::forget('cio_finance_growth_ALL');

        return [
            'success'         => true,
            'synced_count'    => $syncedCount,
            'inserted_count'  => $insertedCount,
            'updated_count'   => $updatedCount,
            'unchanged_count' => $unchangedCount,
        ];
    }
}
