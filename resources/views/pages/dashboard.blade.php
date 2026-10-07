@extends('layouts.app')

@section('pretitle', 'PORTAL EKUITAS & KEUANGAN')
@section('title', 'Dashboard')
@section('subtitle', 'Ringkasan posisi modal saham dan saldo kas operasional perusahaan.')

@section('content')
    @php
        $totalInvestment = (float) ($equitySummary['total_investment'] ?? 0);
        $totalShares     = (int) ($equitySummary['total_shares'] ?? 0);
        $balanceColor    = $currentCashBalance >= 0 ? 'success' : 'danger';
        $balanceBadge    = $currentCashBalance >= 0 ? 'Kas Aktif' : 'Defisit';
    @endphp

    {{-- Grid 3 Kartu Metrik Utama --}}
    <div class="row row-cards g-3">
        {{-- 1. Card Total Saldo Saat Ini (Total Income - Total Outcome) --}}
        <div class="col-12 col-md-4">
            @include('components.finance-metric-card', [
                'title' => 'Total Saldo Saat Ini',
                'value' => 'Rp ' . number_format($currentCashBalance, 0, ',', '.'),
                'subtitle' => 'Masuk: Rp ' . number_format($totalCashIncome, 0, ',', '.') . ' • Keluar: Rp ' . number_format($totalCashOutcome, 0, ',', '.'),
                'badgeText' => $balanceBadge,
                'badgeType' => $balanceColor,
                'icon' => '<svg xmlns="http://www.w3.org/2000/svg" class="icon" width="22" height="22" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M17 8v-3a1 1 0 0 0 -1 -1h-10a2 2 0 0 0 0 4h12a1 1 0 0 1 1 1v3m0 4v3a1 1 0 0 1 -1 1h-12a2 2 0 0 1 -2 -2v-12" /><path d="M20 12v4h-4a2 2 0 0 1 0 -4h4" /></svg>',
                'iconBg' => $balanceColor,
            ])
        </div>

        {{-- 2. Card Total Modal Saham --}}
        <div class="col-12 col-md-4">
            @include('components.finance-metric-card', [
                'title' => 'Total Modal Saham',
                'value' => 'Rp ' . number_format($totalInvestment, 0, ',', '.'),
                'subtitle' => number_format($totalShares, 0, ',', '.') . ' Lembar Beredar',
                'badgeText' => 'Ekuitas Terdaftar',
                'badgeType' => 'warning',
                'icon' => '<svg xmlns="http://www.w3.org/2000/svg" class="icon" width="22" height="22" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M15 15m-3 0a3 3 0 1 0 6 0a3 3 0 1 0 -6 0" /><path d="M13 17.5v4.5l2 -1.5l2 1.5v-4.5" /><path d="M10 19h-5a2 2 0 0 1 -2 -2v-10a2 2 0 0 1 2 -2h14a2 2 0 0 1 2 2v10a2 2 0 0 1 -1 1.73" /><path d="M6 9l12 0" /><path d="M6 12l3 0" /><path d="M6 15l2 0" /></svg>',
                'iconBg' => 'warning',
            ])
        </div>

        {{-- 3. Card Total Asset (Akumulasi Nilai Seluruh Aset Tanpa Pengurangan) --}}
        <div class="col-12 col-md-4">
            @include('components.finance-metric-card', [
                'title' => 'Total Asset',
                'value' => 'Rp ' . number_format($totalAssetValue, 0, ',', '.'),
                'subtitle' => number_format($totalAssetCount, 0, ',', '.') . ' Unit Aset Terdaftar',
                'badgeText' => 'Inventaris Aset',
                'badgeType' => 'azure',
                'icon' => '<svg xmlns="http://www.w3.org/2000/svg" class="icon" width="22" height="22" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 3l8 4.5l0 9l-8 4.5l-8 -4.5l0 -9l8 -4.5" /><path d="M12 12l8 -4.5" /><path d="M12 12l0 9" /><path d="M12 12l-8 -4.5" /></svg>',
                'iconBg' => 'azure',
            ])
        </div>
    </div>

    {{-- Grafik Tren Finansial & Asset: 3 Line (Hijau Pemasukan, Merah Pengeluaran, Biru Asset) --}}
    @include('components.finance-trend-chart', [
        'chartData' => $chartData
    ])

    {{-- Akses Cepat Transaksi Kas & Inventaris --}}
    <div class="mt-3">
        <div class="card border-0 shadow-sm bg-white" style="border-radius: 12px;">
            <div class="card-body p-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
                <div class="d-flex align-items-center gap-2.5">
                    <span class="avatar bg-primary-lt text-primary rounded-circle" style="width: 40px; height: 40px;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 21l18 0" /><path d="M3 10l18 0" /><path d="M5 6l7 -3l7 3" /><path d="M4 10l0 11" /><path d="M20 10l0 11" /><path d="M8 14l0 3" /><path d="M12 14l0 3" /><path d="M16 14l0 3" /></svg>
                    </span>
                    <div>
                        <h4 class="mb-0 fw-bold text-dark fs-4">Akses Cepat Transaksi Kas & Aset</h4>
                        <div class="text-muted small" style="font-size: 0.75rem;">Pencatatan mutasi kas masuk, pengeluaran kas, serta inventaris aset operasional</div>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    @can('lihat pemasukan')
                        <a href="{{ route('cash-incomes.index') }}" class="btn btn-outline-primary btn-sm px-3">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="me-1"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M17 7l-10 10" /><path d="M17 17l-10 0" /><path d="M17 17l0 -10" /></svg>
                            Pemasukan Kas
                        </a>
                    @endcan
                    @can('lihat pengeluaran')
                        <a href="{{ route('cash-outcomes.index') }}" class="btn btn-outline-danger btn-sm px-3">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="me-1"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M7 17l10 -10" /><path d="M7 7l10 0" /><path d="M17 17l0 -10" /></svg>
                            Pengeluaran Kas
                        </a>
                    @endcan
                    @can('lihat aset')
                        <a href="{{ route('assets.index') }}" class="btn btn-outline-secondary btn-sm px-3">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="me-1"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 3l8 4.5l0 9l-8 4.5l-8 -4.5l0 -9l8 -4.5" /></svg>
                            Inventaris Aset
                        </a>
                    @endcan
                </div>
            </div>
        </div>
    </div>

    {{-- Rincian Keuangan & Struktur Kepemilikan Saham --}}
    <div class="row g-3 mt-0 pt-3">
        {{-- Rincian Ringkasan Keuangan (Pemasukan, Pengeluaran, Asset) --}}
        <div class="col-xl-6 col-lg-6">
            <div class="card border shadow-sm h-100 d-flex flex-column" style="border-radius: 12px; overflow: hidden;">
                <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2 py-2 px-3 bg-white border-bottom" style="min-height: 57px;">
                    <div>
                        <h3 class="card-title fw-bold mb-0 text-dark fs-4">Rincian Keuangan &amp; Aset</h3>
                        <p class="text-muted small mb-0" style="font-size: 0.76rem;">
                            Periode:
                            <span class="fw-semibold text-primary">
                                @if($startDate || $endDate)
                                    {{ $startDate ? \Carbon\Carbon::parse($startDate)->translatedFormat('d M Y') : 'Awal' }}
                                    &ndash;
                                    {{ $endDate ? \Carbon\Carbon::parse($endDate)->translatedFormat('d M Y') : 'Sekarang' }}
                                @else
                                    Semua Periode
                                @endif
                            </span>
                        </p>
                    </div>
                    <form method="GET" action="{{ route('dashboard') }}" class="d-flex align-items-center gap-1" id="finance-summary-filter">
                        <input type="date" name="start_date" value="{{ $startDate }}" class="form-control form-control-sm" style="width: 135px;" id="summary-start-date" aria-label="Tanggal mulai">
                        <span class="text-muted">&ndash;</span>
                        <input type="date" name="end_date" value="{{ $endDate }}" class="form-control form-control-sm" style="width: 135px;" id="summary-end-date" aria-label="Tanggal akhir">
                        <button type="submit" class="btn btn-sm btn-primary btn-icon" title="Terapkan filter" id="summary-filter-submit">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 10m-7 0a7 7 0 1 0 14 0a7 7 0 1 0 -14 0" /><path d="M21 21l-6 -6" /></svg>
                        </button>
                        @if($startDate || $endDate)
                            <a href="{{ route('dashboard') }}" class="btn btn-sm btn-outline-secondary btn-icon" title="Reset filter" id="summary-filter-reset">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M18 6l-12 12" /><path d="M6 6l12 12" /></svg>
                            </a>
                        @endif
                    </form>
                </div>
                <div class="table-responsive flex-grow-1">
                    <table class="table table-vcenter card-table table-hover mb-0">
                        <thead>
                            <tr class="bg-light-subtle text-muted text-uppercase" style="font-size: 0.68rem; letter-spacing: 0.04em;">
                                <th class="ps-3 py-2">Kategori</th>
                                <th class="text-center py-2">Jumlah Data</th>
                                <th class="text-end py-2">Total Nilai</th>
                                <th class="text-center pe-3 py-2">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($financeSummary['rows'] as $row)
                                <tr>
                                    <td class="ps-3 py-2">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="avatar avatar-sm bg-{{ $row['color'] }}-lt text-{{ $row['color'] }} rounded">
                                                @if($row['key'] === 'income')
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M17 7l-10 10" /><path d="M16 17h-9v-9" /></svg>
                                                @elseif($row['key'] === 'outcome')
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M7 17l10 -10" /><path d="M8 7l9 0l0 9" /></svg>
                                                @else
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 3l8 4.5l0 9l-8 4.5l-8 -4.5l0 -9l8 -4.5" /><path d="M12 12l8 -4.5" /><path d="M12 12l0 9" /><path d="M12 12l-8 -4.5" /></svg>
                                                @endif
                                            </span>
                                            <div>
                                                <div class="fw-bold text-dark" style="font-size: 0.84rem;">{{ $row['label'] }}</div>
                                                <div class="text-muted" style="font-size: 0.72rem;">{{ $row['desc'] }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center py-2">
                                        <span class="badge bg-{{ $row['color'] }}-lt fw-bold font-monospace" style="font-size: 0.72rem; padding: 3px 6px;">
                                            {{ number_format($row['count'], 0, ',', '.') }} {{ $row['unit'] }}
                                        </span>
                                    </td>
                                    <td class="text-end fw-bold font-monospace text-{{ $row['color'] }} py-2" style="white-space: nowrap; font-size: 0.84rem;">
                                        Rp {{ number_format($row['total'], 0, ',', '.') }}
                                    </td>
                                    <td class="text-center pe-3 py-2">
                                        @can($row['permission'])
                                            <a href="{{ route($row['route']) }}" class="btn btn-sm btn-outline-{{ $row['color'] }} d-inline-flex align-items-center gap-1 px-2 py-1" style="font-size: 0.75rem;" id="summary-detail-{{ $row['key'] }}">
                                                Detail
                                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l14 0" /><path d="M13 18l6 -6" /><path d="M13 6l6 6" /></svg>
                                            </a>
                                        @else
                                            <span class="text-muted small">&mdash;</span>
                                        @endcan
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-light fw-bold border-top">
                            <tr>
                                <td class="ps-3 py-2" colspan="2">
                                    <span class="text-uppercase small text-dark" style="font-size: 0.74rem; letter-spacing: .04em;">Saldo Kas</span>
                                    <div class="text-muted fw-normal" style="font-size: 0.7rem;">Pemasukan &minus; Pengeluaran</div>
                                </td>
                                <td class="text-end font-monospace py-2 {{ $financeSummary['cash_balance'] >= 0 ? 'text-success' : 'text-danger' }}" style="white-space: nowrap; font-size: 0.86rem;">
                                    {{ $financeSummary['cash_balance'] < 0 ? '-' : '' }}Rp {{ number_format(abs($financeSummary['cash_balance']), 0, ',', '.') }}
                                </td>
                                <td class="pe-3"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        {{-- Struktur Kepemilikan Saham --}}
        <div class="col-xl-6 col-lg-6">
            <div class="card border shadow-sm h-100 d-flex flex-column" style="border-radius: 12px; overflow: hidden;">
                <div class="card-header d-flex justify-content-between align-items-center py-2 px-3 bg-white border-bottom" style="min-height: 57px;">
                    <div>
                        <h3 class="card-title fw-bold mb-0 text-dark fs-4">Struktur Kepemilikan Saham</h3>
                        <p class="text-muted small mb-0" style="font-size: 0.76rem;">Distribusi portofolio investor utama</p>
                    </div>
                    @can('lihat investor')
                        <a href="{{ route('shareholders.index') }}" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1 px-2 py-1" style="font-size: 0.78rem;" id="shareholders-see-all">
                            <span>Lihat Semua</span>
                            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l14 0" /><path d="M13 18l6 -6" /><path d="M13 6l6 6" /></svg>
                        </a>
                    @endcan
                </div>
                <div class="table-responsive flex-grow-1">
                    <table class="table table-vcenter card-table table-hover mb-0">
                        <thead>
                            <tr class="bg-light-subtle text-muted text-uppercase" style="font-size: 0.68rem; letter-spacing: 0.04em;">
                                <th class="ps-3 py-2">Pemilik Saham</th>
                                <th class="text-center py-2">Portofolio</th>
                                <th class="text-end py-2">Total Lembar</th>
                                <th class="text-end pe-3 py-2">Porsi (%)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topShareholders as $sh)
                                <tr>
                                    <td class="ps-3 py-2">
                                        <a href="{{ route('shareholders.show', $sh->id) }}" class="text-reset fw-bold d-block text-decoration-none" style="font-size: 0.84rem;">
                                            {{ $sh->name }}
                                        </a>
                                        <div class="text-muted small" style="font-size: 0.72rem;">NIK: <span class="font-monospace">{{ $sh->id_card_number }}</span></div>
                                    </td>
                                    <td class="text-center py-2">
                                        <span class="badge bg-purple-lt fw-bold font-monospace" style="font-size: 0.72rem; padding: 3px 6px;">
                                            {{ (int) $sh->active_holdings_count }} Saham
                                        </span>
                                    </td>
                                    <td class="text-end fw-bold font-monospace text-dark py-2" style="white-space: nowrap; font-size: 0.82rem;">
                                        {{ number_format((int) $sh->active_shares_sum, 0, ',', '.') }} <span class="text-muted fw-normal" style="font-size: 0.75rem;">Lembar</span>
                                    </td>
                                    <td class="text-end pe-3 py-2" style="white-space: nowrap;">
                                        <span class="badge bg-success-lt text-success fw-bold font-monospace" style="font-size: 0.74rem; padding: 3px 6px;">
                                            {{ number_format((float) $sh->active_percentage_sum, 2) }}%
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">
                                        <em>Belum ada data pemegang saham terdaftar.</em>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection