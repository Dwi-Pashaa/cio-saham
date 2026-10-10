@extends('layouts.app')

@section('pretitle', 'PORTAL EKUITAS & KEUANGAN')
@section('title', 'Dashboard')
@section('subtitle', 'Ringkasan posisi modal saham, saldo kas operasional, dan inventaris aset perusahaan.')

@section('actions')
    <div class="d-flex align-items-center gap-2">
        <span class="badge bg-white text-muted border py-1.5 px-2.5 font-monospace d-none d-md-inline-flex align-items-center gap-1.5 shadow-none" style="font-size: 0.76rem; border-color: #e2e8f0 !important;">
            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 7a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v12a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12z" /><path d="M16 3v4" /><path d="M8 3v4" /><path d="M4 11h16" /></svg>
            {{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}
        </span>
        <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-1" title="Muat Ulang Data">
            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M20 11a8.1 8.1 0 0 0 -15.5 -2m-.5 -5v5h5" /><path d="M4 13a8.1 8.1 0 0 0 15.5 2m.5 5v-5h-5" /></svg>
            <span>Segarkan</span>
        </a>
        @can('bagikan ke tabungan')
            <button type="button" class="btn btn-primary btn-sm d-inline-flex align-items-center gap-1.5 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalBagikanTabungan">
                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M14 8v-2a2 2 0 0 0 -2 -2h-7a2 2 0 0 0 -2 2v12a2 2 0 0 0 2 2h7a2 2 0 0 0 2 -2v-2" /><path d="M9 12h12l-3 -3" /><path d="M18 15l3 -3" /></svg>
                <span>Bagikan ke Tabungan</span>
            </button>
        @endcan
    </div>
@endsection

@push('css')
<style>
    /* Executive Metric KPI Cards */
    .pro-kpi-card {
        background: #ffffff;
        border: 1px solid #e6e9ef;
        border-radius: 14px;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04), 0 1px 2px rgba(15, 23, 42, 0.02);
        padding: 1.25rem 1.35rem;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        height: 100%;
        position: relative;
        overflow: hidden;
        transition: all 0.22s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .pro-kpi-card::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: var(--card-accent, #206bc4);
        opacity: 0.9;
    }
    .pro-kpi-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 24px -4px rgba(15, 23, 42, 0.07), 0 4px 6px -2px rgba(15, 23, 42, 0.02);
        border-color: #cbd5e1;
    }
    .pro-kpi-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 0.65rem;
    }
    .pro-kpi-label {
        font-size: 0.71rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: #64748b;
        margin-bottom: 0.35rem;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .pro-kpi-dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: var(--card-accent, #206bc4);
    }
    .pro-kpi-value {
        font-size: clamp(1.25rem, 1.7vw, 1.5rem);
        font-weight: 800;
        color: #0f172a;
        letter-spacing: -0.025em;
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        margin: 0;
        line-height: 1.15;
    }
    .pro-kpi-icon {
        width: 40px;
        height: 40px;
        border-radius: 11px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: var(--card-accent-soft, rgba(32, 107, 196, 0.08));
        color: var(--card-accent, #206bc4);
        flex-shrink: 0;
        border: 1px solid var(--card-accent-border, transparent);
    }
    .pro-kpi-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        padding-top: 0.85rem;
        margin-top: 0.85rem;
        border-top: 1px solid #f1f5f9;
        font-size: 0.74rem;
    }
    .pro-kpi-badge {
        font-size: 0.7rem;
        font-weight: 700;
        padding: 3px 7px;
        border-radius: 6px;
    }
    .pro-kpi-subtext {
        font-size: 0.72rem;
        color: #64748b;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .btn-pro-action {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        font-size: 0.73rem;
        font-weight: 600;
        padding: 4px 10px;
        border-radius: 7px;
        border: 1px solid #e2e8f0;
        background: #ffffff;
        color: #334155;
        text-decoration: none;
        transition: all 0.18s ease;
        white-space: nowrap;
        cursor: pointer;
    }
    .btn-pro-action:hover {
        background: #f8fafc;
        border-color: #cbd5e1;
        color: #0f172a;
        transform: translateY(-1px);
    }
    .btn-pro-action-primary {
        background: #206bc4;
        border-color: #206bc4;
        color: #ffffff;
    }
    .btn-pro-action-primary:hover {
        background: #1a569d;
        border-color: #1a569d;
        color: #ffffff;
    }

    /* Executive Quick Bar */
    .pro-quick-bar {
        background: #ffffff;
        border: 1px solid #e6e9ef;
        border-radius: 14px;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
        padding: 0.95rem 1.25rem;
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
    }
    .btn-quick-nav {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 13px;
        font-size: 0.77rem;
        font-weight: 600;
        border-radius: 8px;
        border: 1px solid #e2e8f0;
        background: #ffffff;
        color: #334155;
        text-decoration: none;
        transition: all 0.18s ease;
    }
    .btn-quick-nav:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(15, 23, 42, 0.06);
        color: #0f172a;
    }
    .btn-quick-nav.nav-income:hover { border-color: #10b981; color: #047857; background: rgba(16, 185, 129, 0.04); }
    .btn-quick-nav.nav-outcome:hover { border-color: #ef4444; color: #b91c1c; background: rgba(239, 68, 68, 0.04); }
    .btn-quick-nav.nav-saving:hover { border-color: #0ea5e9; color: #0369a1; background: rgba(14, 165, 233, 0.04); }
    .btn-quick-nav.nav-asset:hover { border-color: #206bc4; color: #1d4ed8; background: rgba(32, 107, 196, 0.04); }

    /* Tables & Card Polish */
    .pro-card {
        background: #ffffff;
        border: 1px solid #e6e9ef;
        border-radius: 14px;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
        overflow: hidden;
    }
    .pro-table-header {
        background: #f8fafc;
        border-bottom: 1px solid #eef1f5;
    }
    .pro-table-header th {
        font-size: 0.69rem !important;
        font-weight: 700 !important;
        letter-spacing: 0.05em !important;
        text-transform: uppercase !important;
        color: #64748b !important;
        padding: 10px 14px !important;
    }
    .pro-table tbody tr {
        transition: background 0.15s ease;
        border-bottom: 1px solid #f1f5f9;
    }
    .pro-table tbody tr:hover {
        background: #f8fafc;
    }
    .pro-table tbody td {
        padding: 10px 14px !important;
        vertical-align: middle;
    }
    .pro-detail-btn {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        font-size: 0.74rem;
        font-weight: 600;
        padding: 4px 10px;
        border-radius: 6px;
        border: 1px solid #e2e8f0;
        background: #ffffff;
        color: #475569;
        text-decoration: none;
        transition: all 0.15s ease;
    }
    .pro-detail-btn:hover {
        background: #f1f5f9;
        border-color: #cbd5e1;
        color: #0f172a;
    }
</style>
@endpush

@section('content')
    @php
        $totalInvestment = (float) ($equitySummary['total_investment'] ?? 0);
        $totalShares     = (int) ($equitySummary['total_shares'] ?? 0);
        $balanceColor    = $currentCashBalance >= 0 ? '#10b981' : '#ef4444';
        $balanceSoft     = $currentCashBalance >= 0 ? 'rgba(16, 185, 129, 0.08)' : 'rgba(239, 68, 68, 0.08)';
        $balanceBadgeTxt = $currentCashBalance >= 0 ? 'Kas Aktif' : 'Defisit';
        $balanceBadgeCls = $currentCashBalance >= 0 ? 'bg-success-lt text-success' : 'bg-danger-lt text-danger';
    @endphp

    {{-- Grid 4 Kartu Metrik Utama (Executive Grade) --}}
    <div class="row row-cards g-3">
        {{-- 1. Card Total Saldo Saat Ini --}}
        <div class="{{ auth()->user()?->can('lihat tabungan') ? 'col-12 col-sm-6 col-xl-3' : 'col-12 col-md-4' }}">
            <div class="pro-kpi-card" style="--card-accent: {{ $balanceColor }}; --card-accent-soft: {{ $balanceSoft }}; --card-accent-border: {{ $balanceColor }}33;">
                <div>
                    <div class="pro-kpi-header">
                        <div>
                            <div class="pro-kpi-label">
                                <span class="pro-kpi-dot"></span>
                                Total Saldo Kas
                            </div>
                            <h3 class="pro-kpi-value">Rp {{ number_format($currentCashBalance, 0, ',', '.') }}</h3>
                        </div>
                        <div class="pro-kpi-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M17 8v-3a1 1 0 0 0 -1 -1h-10a2 2 0 0 0 0 4h12a1 1 0 0 1 1 1v3m0 4v3a1 1 0 0 1 -1 1h-12a2 2 0 0 1 -2 -2v-12" /><path d="M20 12v4h-4a2 2 0 0 1 0 -4h4" /></svg>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-1.5 small">
                        <span class="pro-kpi-badge {{ $balanceBadgeCls }}">{{ $balanceBadgeTxt }}</span>
                        <span class="pro-kpi-subtext">Masuk: Rp {{ number_format($totalCashIncome, 0, ',', '.') }} • Keluar: Rp {{ number_format($totalCashOutcome, 0, ',', '.') }}</span>
                    </div>
                </div>
                <div class="pro-kpi-footer">
                    <span class="text-muted small" style="font-size: 0.72rem;">Sisa Operasional</span>
                    @can('bagikan ke tabungan')
                        <button type="button" class="btn-pro-action btn-pro-action-primary" data-bs-toggle="modal" data-bs-target="#modalBagikanTabungan" id="btn-open-bagikan-tabungan">
                            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M14 8v-2a2 2 0 0 0 -2 -2h-7a2 2 0 0 0 -2 2v12a2 2 0 0 0 2 2h7a2 2 0 0 0 2 -2v-2" /><path d="M9 12h12l-3 -3" /><path d="M18 15l3 -3" /></svg>
                            Bagikan ke Tabungan
                        </button>
                    @endcan
                </div>
            </div>
        </div>

        {{-- 2. Card Saldo Tabungan Saat Ini --}}
        @can('lihat tabungan')
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="pro-kpi-card" style="--card-accent: #0ea5e9; --card-accent-soft: rgba(14, 165, 233, 0.08); --card-accent-border: rgba(14, 165, 233, 0.2);">
                    <div>
                        <div class="pro-kpi-header">
                            <div>
                                <div class="pro-kpi-label">
                                    <span class="pro-kpi-dot"></span>
                                    Saldo Tabungan
                                </div>
                                <h3 class="pro-kpi-value text-azure">Rp {{ number_format($totalSavingsBalance, 0, ',', '.') }}</h3>
                            </div>
                            <div class="pro-kpi-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M17 8v-3a1 1 0 0 0 -1 -1h-10a2 2 0 0 0 0 4h12a1 1 0 0 1 1 1v3m0 4v3a1 1 0 0 1 -1 1h-12a2 2 0 0 1 -2 -2v-12" /><path d="M20 12v4h-4a2 2 0 0 1 0 -4h4" /></svg>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-1.5 small">
                            <span class="pro-kpi-badge bg-azure-lt text-azure">Kas Tabungan</span>
                            <span class="pro-kpi-subtext">{{ number_format($totalSavingsCount, 0, ',', '.') }} Kali Alokasi • Kas Terpisah</span>
                        </div>
                    </div>
                    <div class="pro-kpi-footer">
                        <span class="text-muted small" style="font-size: 0.72rem;">Dana Simpanan</span>
                        <a href="#savings-history-section" class="btn-pro-action">
                            Lihat Log
                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l14 0" /><path d="M13 18l6 -6" /><path d="M13 6l6 6" /></svg>
                        </a>
                    </div>
                </div>
            </div>
        @endcan

        {{-- 3. Card Total Modal Saham --}}
        <div class="{{ auth()->user()?->can('lihat tabungan') ? 'col-12 col-sm-6 col-xl-3' : 'col-12 col-md-4' }}">
            <div class="pro-kpi-card" style="--card-accent: #f59e0b; --card-accent-soft: rgba(245, 158, 11, 0.08); --card-accent-border: rgba(245, 158, 11, 0.2);">
                <div>
                    <div class="pro-kpi-header">
                        <div>
                            <div class="pro-kpi-label">
                                <span class="pro-kpi-dot"></span>
                                Total Modal Saham
                            </div>
                            <h3 class="pro-kpi-value text-dark">Rp {{ number_format($totalInvestment, 0, ',', '.') }}</h3>
                        </div>
                        <div class="pro-kpi-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M15 15m-3 0a3 3 0 1 0 6 0a3 3 0 1 0 -6 0" /><path d="M13 17.5v4.5l2 -1.5l2 1.5v-4.5" /><path d="M10 19h-5a2 2 0 0 1 -2 -2v-10a2 2 0 0 1 2 -2h14a2 2 0 0 1 2 2v10a2 2 0 0 1 -1 1.73" /><path d="M6 9l12 0" /><path d="M6 12l3 0" /><path d="M6 15l2 0" /></svg>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-1.5 small">
                        <span class="pro-kpi-badge bg-warning-lt text-warning">Ekuitas</span>
                        <span class="pro-kpi-subtext">{{ number_format($totalShares, 0, ',', '.') }} Lembar Saham Beredar</span>
                    </div>
                </div>
                <div class="pro-kpi-footer">
                    <span class="text-muted small" style="font-size: 0.72rem;">Portofolio Investor</span>
                    @can('lihat investor')
                        <a href="{{ route('shareholders.index') }}" class="btn-pro-action">
                            Kelola Saham
                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l14 0" /><path d="M13 18l6 -6" /><path d="M13 6l6 6" /></svg>
                        </a>
                    @else
                        <span class="text-muted small">&mdash;</span>
                    @endcan
                </div>
            </div>
        </div>

        {{-- 4. Card Total Asset --}}
        <div class="{{ auth()->user()?->can('lihat tabungan') ? 'col-12 col-sm-6 col-xl-3' : 'col-12 col-md-4' }}">
            <div class="pro-kpi-card" style="--card-accent: #206bc4; --card-accent-soft: rgba(32, 107, 196, 0.08); --card-accent-border: rgba(32, 107, 196, 0.2);">
                <div>
                    <div class="pro-kpi-header">
                        <div>
                            <div class="pro-kpi-label">
                                <span class="pro-kpi-dot"></span>
                                Total Nilai Asset
                            </div>
                            <h3 class="pro-kpi-value text-dark">Rp {{ number_format($totalAssetValue, 0, ',', '.') }}</h3>
                        </div>
                        <div class="pro-kpi-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 3l8 4.5l0 9l-8 4.5l-8 -4.5l0 -9l8 -4.5" /><path d="M12 12l8 -4.5" /><path d="M12 12l0 9" /><path d="M12 12l-8 -4.5" /></svg>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-1.5 small">
                        <span class="pro-kpi-badge bg-primary-lt text-primary">Inventaris</span>
                        <span class="pro-kpi-subtext">{{ number_format($totalAssetCount, 0, ',', '.') }} Unit Aset Terdaftar</span>
                    </div>
                </div>
                <div class="pro-kpi-footer">
                    <span class="text-muted small" style="font-size: 0.72rem;">Inventaris Fisik</span>
                    @can('lihat aset')
                        <a href="{{ route('assets.index') }}" class="btn-pro-action">
                            Daftar Aset
                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l14 0" /><path d="M13 18l6 -6" /><path d="M13 6l6 6" /></svg>
                        </a>
                    @else
                        <span class="text-muted small">&mdash;</span>
                    @endcan
                </div>
            </div>
        </div>
    </div>

    {{-- Grafik Tren Finansial & Asset --}}
    @include('components.finance-trend-chart', [
        'chartData' => $chartData
    ])

    {{-- Akses Cepat Transaksi Kas & Inventaris (Executive Bar) --}}
    <div class="mt-3">
        <div class="pro-quick-bar">
            <div class="d-flex align-items-center gap-2.5">
                <span class="avatar bg-primary-lt text-primary rounded-circle" style="width: 38px; height: 38px;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 21l18 0" /><path d="M3 10l18 0" /><path d="M5 6l7 -3l7 3" /><path d="M4 10l0 11" /><path d="M20 10l0 11" /><path d="M8 14l0 3" /><path d="M12 14l0 3" /><path d="M16 14l0 3" /></svg>
                </span>
                <div>
                    <h4 class="mb-0 fw-bold text-dark fs-4">Akses Cepat Transaksi</h4>
                    <div class="text-muted small" style="font-size: 0.74rem;">Pencatatan mutasi kas masuk, pengeluaran kas, alokasi tabungan, dan aset</div>
                </div>
            </div>
            <div class="d-flex flex-wrap align-items-center gap-2">
                @can('lihat pemasukan')
                    <a href="{{ route('cash-incomes.index') }}" class="btn-quick-nav nav-income">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M17 7l-10 10" /><path d="M17 17l-10 0" /><path d="M17 17l0 -10" /></svg>
                        Pemasukan Kas
                    </a>
                @endcan
                @can('lihat pengeluaran')
                    <a href="{{ route('cash-outcomes.index') }}" class="btn-quick-nav nav-outcome">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M7 17l10 -10" /><path d="M7 7l10 0" /><path d="M17 17l0 -10" /></svg>
                        Pengeluaran Kas
                    </a>
                @endcan
                @can('lihat tabungan')
                    <a href="#savings-history-section" class="btn-quick-nav nav-saving">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M17 8v-3a1 1 0 0 0 -1 -1h-10a2 2 0 0 0 0 4h12a1 1 0 0 1 1 1v3m0 4v3a1 1 0 0 1 -1 1h-12a2 2 0 0 1 -2 -2v-12" /><path d="M20 12v4h-4a2 2 0 0 1 0 -4h4" /></svg>
                        Log Tabungan
                    </a>
                @endcan
                @can('lihat aset')
                    <a href="{{ route('assets.index') }}" class="btn-quick-nav nav-asset">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 3l8 4.5l0 9l-8 4.5l-8 -4.5l0 -9l8 -4.5" /></svg>
                        Inventaris Aset
                    </a>
                @endcan
            </div>
        </div>
    </div>

    {{-- Rincian Keuangan & Struktur Kepemilikan Saham --}}
    <div class="row g-3 mt-0 pt-3">
        {{-- Rincian Ringkasan Keuangan (Pemasukan, Pengeluaran, Asset) --}}
        <div class="col-xl-6 col-lg-6">
            <div class="pro-card h-100 d-flex flex-column">
                <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2 py-2 px-3 bg-white border-bottom" style="min-height: 57px;">
                    <div>
                        <h3 class="card-title fw-bold mb-0 text-dark fs-4">Rincian Keuangan &amp; Aset</h3>
                        <p class="text-muted small mb-0" style="font-size: 0.74rem;">
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
                    <form method="GET" action="{{ route('dashboard') }}" class="d-flex align-items-center gap-1.5" id="finance-summary-filter">
                        <div class="input-group input-group-sm" style="width: auto;">
                            <span class="input-group-text bg-white border-end-0 py-1 px-2 text-muted">
                                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 7a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v12a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12z" /><path d="M16 3v4" /><path d="M8 3v4" /><path d="M4 11h16" /></svg>
                            </span>
                            <input type="date" name="start_date" value="{{ $startDate }}" class="form-control form-control-sm border-start-0 ps-1" style="width: 125px;" id="summary-start-date" aria-label="Tanggal mulai">
                        </div>
                        <span class="text-muted small">&ndash;</span>
                        <div class="input-group input-group-sm" style="width: auto;">
                            <input type="date" name="end_date" value="{{ $endDate }}" class="form-control form-control-sm" style="width: 125px;" id="summary-end-date" aria-label="Tanggal akhir">
                        </div>
                        <button type="submit" class="btn btn-sm btn-primary px-2 py-1 d-inline-flex align-items-center" title="Terapkan filter" id="summary-filter-submit">
                            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 10m-7 0a7 7 0 1 0 14 0a7 7 0 1 0 -14 0" /><path d="M21 21l-6 -6" /></svg>
                        </button>
                        @if($startDate || $endDate)
                            <a href="{{ route('dashboard') }}" class="btn btn-sm btn-outline-secondary px-2 py-1" title="Reset filter" id="summary-filter-reset">
                                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M18 6l-12 12" /><path d="M6 6l12 12" /></svg>
                            </a>
                        @endif
                    </form>
                </div>
                <div class="table-responsive flex-grow-1">
                    <table class="table table-vcenter pro-table mb-0">
                        <thead class="pro-table-header">
                            <tr>
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
                                            <span class="avatar avatar-sm bg-{{ $row['color'] }}-lt text-{{ $row['color'] }} rounded" style="width: 32px; height: 32px;">
                                                @if($row['key'] === 'income')
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M17 7l-10 10" /><path d="M16 17h-9v-9" /></svg>
                                                @elseif($row['key'] === 'outcome')
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M7 17l10 -10" /><path d="M8 7l9 0l0 9" /></svg>
                                                @elseif($row['key'] === 'saving')
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M17 8v-3a1 1 0 0 0 -1 -1h-10a2 2 0 0 0 0 4h12a1 1 0 0 1 1 1v3m0 4v3a1 1 0 0 1 -1 1h-12a2 2 0 0 1 -2 -2v-12" /><path d="M20 12v4h-4a2 2 0 0 1 0 -4h4" /></svg>
                                                @else
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 3l8 4.5l0 9l-8 4.5l-8 -4.5l0 -9l8 -4.5" /><path d="M12 12l8 -4.5" /><path d="M12 12l0 9" /><path d="M12 12l-8 -4.5" /></svg>
                                                @endif
                                            </span>
                                            <div>
                                                <div class="fw-bold text-dark" style="font-size: 0.84rem;">{{ $row['label'] }}</div>
                                                <div class="text-muted" style="font-size: 0.71rem;">{{ $row['desc'] }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center py-2">
                                        <span class="badge bg-{{ $row['color'] }}-lt fw-bold font-monospace" style="font-size: 0.71rem; padding: 3px 6px;">
                                            {{ number_format($row['count'], 0, ',', '.') }} {{ $row['unit'] }}
                                        </span>
                                    </td>
                                    <td class="text-end fw-bold font-monospace text-{{ $row['color'] }} py-2" style="white-space: nowrap; font-size: 0.84rem;">
                                        Rp {{ number_format($row['total'], 0, ',', '.') }}
                                    </td>
                                    <td class="text-center pe-3 py-2">
                                        @can($row['permission'])
                                            @if($row['key'] === 'saving')
                                                <a href="#savings-history-section" class="pro-detail-btn" id="summary-detail-{{ $row['key'] }}">
                                                    <span>Detail</span>
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l14 0" /><path d="M13 18l6 -6" /><path d="M13 6l6 6" /></svg>
                                                </a>
                                            @else
                                                <a href="{{ route($row['route']) }}" class="pro-detail-btn" id="summary-detail-{{ $row['key'] }}">
                                                    <span>Detail</span>
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l14 0" /><path d="M13 18l6 -6" /><path d="M13 6l6 6" /></svg>
                                                </a>
                                            @endif
                                        @else
                                            <span class="text-muted small">&mdash;</span>
                                        @endcan
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-light fw-bold border-top">
                            {{-- 1. Saldo Kas Aktif --}}
                            <tr>
                                <td class="ps-3 py-2" colspan="2">
                                    <div class="d-flex align-items-center gap-1.5">
                                        <span class="badge bg-success-lt p-1 rounded-circle">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M17 8v-3a1 1 0 0 0 -1 -1h-10a2 2 0 0 0 0 4h12a1 1 0 0 1 1 1v3m0 4v3a1 1 0 0 1 -1 1h-12a2 2 0 0 1 -2 -2v-12" /><path d="M20 12v4h-4a2 2 0 0 1 0 -4h4" /></svg>
                                        </span>
                                        <span class="text-uppercase small text-dark" style="font-size: 0.74rem; letter-spacing: .04em;">Total Saldo Kas</span>
                                    </div>
                                    <div class="text-muted fw-normal ps-3 ms-1" style="font-size: 0.7rem;">Pemasukan Bersih &minus; Pengeluaran &minus; Tabungan</div>
                                </td>
                                <td class="text-end font-monospace py-2 {{ $financeSummary['cash_balance'] >= 0 ? 'text-success' : 'text-danger' }}" style="white-space: nowrap; font-size: 0.86rem;">
                                    {{ $financeSummary['cash_balance'] < 0 ? '-' : '' }}Rp {{ number_format(abs($financeSummary['cash_balance']), 0, ',', '.') }}
                                </td>
                                <td class="pe-3"></td>
                            </tr>
                            {{-- 2. Saldo Tabungan --}}
                            <tr class="border-top border-light-subtle">
                                <td class="ps-3 py-2" colspan="2">
                                    <div class="d-flex align-items-center gap-1.5">
                                        <span class="badge bg-azure-lt p-1 rounded-circle">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M17 8v-3a1 1 0 0 0 -1 -1h-10a2 2 0 0 0 0 4h12a1 1 0 0 1 1 1v3m0 4v3a1 1 0 0 1 -1 1h-12a2 2 0 0 1 -2 -2v-12" /><path d="M20 12v4h-4a2 2 0 0 1 0 -4h4" /></svg>
                                        </span>
                                        <span class="text-uppercase small text-dark" style="font-size: 0.74rem; letter-spacing: .04em;">Saldo Tabungan</span>
                                    </div>
                                    <div class="text-muted fw-normal ps-3 ms-1" style="font-size: 0.7rem;">Kas dialokasikan ke pos rekening simpanan</div>
                                </td>
                                <td class="text-end font-monospace py-2 text-azure" style="white-space: nowrap; font-size: 0.86rem;">
                                    Rp {{ number_format($financeSummary['saving_balance'] ?? $totalSavingsBalance, 0, ',', '.') }}
                                </td>
                                <td class="pe-3"></td>
                            </tr>
                            {{-- 3. Total Saldo Asset --}}
                            <tr class="border-top border-light-subtle">
                                <td class="ps-3 py-2" colspan="2">
                                    <div class="d-flex align-items-center gap-1.5">
                                        <span class="badge bg-primary-lt p-1 rounded-circle">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 3l8 4.5l0 9l-8 4.5l-8 -4.5l0 -9l8 -4.5" /></svg>
                                        </span>
                                        <span class="text-uppercase small text-dark" style="font-size: 0.74rem; letter-spacing: .04em;">Total Nilai Asset</span>
                                    </div>
                                    <div class="text-muted fw-normal ps-3 ms-1" style="font-size: 0.7rem;">Akumulasi nilai seluruh inventaris perolehan aset</div>
                                </td>
                                <td class="text-end font-monospace py-2 text-primary" style="white-space: nowrap; font-size: 0.86rem;">
                                    Rp {{ number_format($financeSummary['asset_balance'] ?? $totalAssetValue, 0, ',', '.') }}
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
            <div class="pro-card h-100 d-flex flex-column">
                <div class="card-header d-flex justify-content-between align-items-center py-2 px-3 bg-white border-bottom" style="min-height: 57px;">
                    <div>
                        <h3 class="card-title fw-bold mb-0 text-dark fs-4">Struktur Kepemilikan Saham</h3>
                        <p class="text-muted small mb-0" style="font-size: 0.74rem;">Distribusi portofolio investor utama</p>
                    </div>
                    @can('lihat investor')
                        <a href="{{ route('shareholders.index') }}" class="pro-detail-btn" id="shareholders-see-all">
                            <span>Lihat Semua</span>
                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l14 0" /><path d="M13 18l6 -6" /><path d="M13 6l6 6" /></svg>
                        </a>
                    @endcan
                </div>
                <div class="table-responsive flex-grow-1">
                    <table class="table table-vcenter pro-table mb-0">
                        <thead class="pro-table-header">
                            <tr>
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
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="avatar avatar-sm bg-purple-lt text-purple rounded-circle fw-bold" style="width: 32px; height: 32px; font-size: 0.75rem;">
                                                {{ strtoupper(substr($sh->name, 0, 2)) }}
                                            </span>
                                            <div>
                                                <a href="{{ route('shareholders.show', $sh->id) }}" class="text-dark fw-bold d-block text-decoration-none" style="font-size: 0.84rem;">
                                                    {{ $sh->name }}
                                                </a>
                                                <div class="text-muted small font-monospace" style="font-size: 0.7rem;">NIK: {{ $sh->id_card_number }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center py-2">
                                        <span class="badge bg-purple-lt fw-bold font-monospace" style="font-size: 0.71rem; padding: 3px 6px;">
                                            {{ (int) $sh->active_holdings_count }} Saham
                                        </span>
                                    </td>
                                    <td class="text-end fw-bold font-monospace text-dark py-2" style="white-space: nowrap; font-size: 0.82rem;">
                                        {{ number_format((int) $sh->active_shares_sum, 0, ',', '.') }} <span class="text-muted fw-normal" style="font-size: 0.72rem;">Lembar</span>
                                    </td>
                                    <td class="text-end pe-3 py-2" style="white-space: nowrap;">
                                        <span class="badge bg-success-lt text-success fw-bold font-monospace" style="font-size: 0.73rem; padding: 3px 6px;">
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

    {{-- Log History Pembagian Tabungan (Paling Bawah Dashboard) --}}
    @can('lihat tabungan')
        <div class="pro-card mt-3" id="savings-history-section">
            <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2 py-3 px-3 bg-white border-bottom">
                <div class="d-flex align-items-center gap-2.5">
                    <span class="avatar bg-azure-lt text-azure rounded-circle" style="width: 38px; height: 38px;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M17 8v-3a1 1 0 0 0 -1 -1h-10a2 2 0 0 0 0 4h12a1 1 0 0 1 1 1v3m0 4v3a1 1 0 0 1 -1 1h-12a2 2 0 0 1 -2 -2v-12" /><path d="M20 12v4h-4a2 2 0 0 1 0 -4h4" /></svg>
                    </span>
                    <div>
                        <h3 class="card-title fw-bold mb-0 text-dark fs-4">Riwayat Log Pembagian Tabungan</h3>
                        <div class="text-muted small" style="font-size: 0.74rem;">Catatan mutasi pemotongan kas aktif operasional yang dialokasikan ke rekening tabungan</div>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-azure-lt font-monospace fw-bold px-2.5 py-1.5" style="font-size: 0.76rem;">
                        Akumulasi: Rp {{ number_format($totalSavingsBalance, 0, ',', '.') }}
                    </span>
                    @can('bagikan ke tabungan')
                        <button type="button" class="btn btn-sm btn-primary d-inline-flex align-items-center gap-1.5 px-3 py-1.5 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalBagikanTabungan" style="font-size: 0.78rem; border-radius: 7px;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M14 8v-2a2 2 0 0 0 -2 -2h-7a2 2 0 0 0 -2 2v12a2 2 0 0 0 2 2h7a2 2 0 0 0 2 -2v-2" /><path d="M9 12h12l-3 -3" /><path d="M18 15l3 -3" /></svg>
                            Bagikan ke Tabungan
                        </button>
                    @endcan
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-vcenter pro-table mb-0">
                    <thead class="pro-table-header">
                        <tr>
                            <th class="ps-3 py-2">No. Transaksi &amp; Tanggal</th>
                            <th class="py-2">Penerima &amp; Catatan</th>
                            <th class="py-2">Bank &amp; Rekening</th>
                            <th class="text-end py-2">Nominal Tabungan</th>
                            <th class="text-center py-2">Dicatat Oleh</th>
                            <th class="text-center py-2">Bukti</th>
                            <th class="text-center pe-3 py-2">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($savingsLogs as $log)
                            <tr>
                                <td class="ps-3 py-2">
                                    <span class="badge bg-azure-lt font-monospace fw-bold" style="font-size: 0.72rem;">{{ $log->transaction_number }}</span>
                                    <div class="text-muted small mt-0.5 font-monospace" style="font-size: 0.72rem;">
                                        {{ $log->transaction_date ? $log->transaction_date->format('d M Y') : '-' }}
                                    </div>
                                </td>
                                <td class="py-2">
                                    <strong class="text-dark d-block" style="font-size: 0.84rem;">{{ $log->recipient_name }}</strong>
                                    <div class="text-muted small text-truncate" style="max-width: 260px; font-size: 0.72rem;" title="{{ $log->notes ?: '-' }}">
                                        {{ $log->notes ?: '-' }}
                                    </div>
                                </td>
                                <td class="py-2">
                                    <span class="badge bg-light text-dark border font-monospace fw-semibold" style="font-size: 0.73rem;">{{ $log->bank_name }}</span>
                                    @if($log->account_number)
                                        <div class="text-muted small font-monospace mt-0.5" style="font-size: 0.72rem;">No. {{ $log->account_number }}</div>
                                    @else
                                        <div class="text-muted small mt-0.5" style="font-size: 0.72rem;"><em>Tanpa nomor rekening</em></div>
                                    @endif
                                </td>
                                <td class="text-end fw-bold font-monospace text-azure py-2" style="white-space: nowrap; font-size: 0.85rem;">
                                    Rp {{ number_format($log->amount, 0, ',', '.') }}
                                </td>
                                <td class="text-center py-2">
                                    <span class="badge bg-secondary-lt fw-normal" style="font-size: 0.72rem;">
                                        {{ $log->creator ? $log->creator->name : 'Sistem' }}
                                    </span>
                                </td>
                                <td class="text-center py-2">
                                    @if($log->proof_file)
                                        <a href="{{ $log->proof_url }}" target="_blank" class="btn btn-sm btn-outline-primary px-2 py-0.5 d-inline-flex align-items-center gap-1" style="font-size: 0.72rem;" title="Buka File Bukti">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M15 8h.01" /><path d="M3 6a3 3 0 0 1 3 -3h12a3 3 0 0 1 3 3v12a3 3 0 0 1 -3 3h-12a3 3 0 0 1 -3 -3v-12z" /><path d="M3 16l5 -5c.928 -.893 2.072 -.893 3 0l5 5" /><path d="M14 14l1 -1c.928 -.893 2.072 -.893 3 0l3 3" /></svg>
                                            Lihat
                                        </a>
                                    @else
                                        <span class="text-muted small">&mdash;</span>
                                    @endif
                                </td>
                                <td class="text-center pe-3 py-2">
                                    <div class="d-flex justify-content-center align-items-center gap-1">
                                        <button type="button" class="btn btn-sm btn-outline-info btn-show-saving px-2 py-1" data-id="{{ $log->id }}" title="Lihat Slip Rincian" style="font-size: 0.72rem;">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M14 3v4a1 1 0 0 0 1 1h4" /><path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z" /><path d="M9 17h6" /><path d="M9 13h6" /></svg>
                                            Detail
                                        </button>
                                        @can('hapus tabungan')
                                            <button type="button" class="btn btn-sm btn-outline-danger btn-delete-saving px-2 py-1" data-id="{{ $log->id }}" data-ref="{{ $log->transaction_number }}" data-amount="Rp {{ number_format($log->amount, 0, ',', '.') }}" title="Hapus Alokasi" style="font-size: 0.72rem;">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 7l16 0" /><path d="M10 11l0 6" /><path d="M14 11l0 6" /><path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" /><path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" /></svg>
                                            </button>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">
                                    <div class="d-flex flex-column align-items-center justify-content-center py-2">
                                        <span class="avatar bg-light text-muted rounded-circle mb-2" style="width: 44px; height: 44px;">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M17 8v-3a1 1 0 0 0 -1 -1h-10a2 2 0 0 0 0 4h12a1 1 0 0 1 1 1v3m0 4v3a1 1 0 0 1 -1 1h-12a2 2 0 0 1 -2 -2v-12" /><path d="M20 12v4h-4a2 2 0 0 1 0 -4h4" /></svg>
                                        </span>
                                        <p class="mb-1 fw-semibold text-dark">Belum ada riwayat pembagian ke tabungan</p>
                                        <small class="text-muted">Klik tombol "Bagikan ke Tabungan" untuk mengalokasikan saldo kas aktif ke rekening tabungan.</small>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($savingsLogs->hasPages())
                <div class="card-footer bg-white border-top py-2 px-3 d-flex justify-content-between align-items-center">
                    <div class="text-muted small">
                        Menampilkan {{ $savingsLogs->firstItem() }} s/d {{ $savingsLogs->lastItem() }} dari {{ $savingsLogs->total() }} log tabungan
                    </div>
                    <div>
                        {{ $savingsLogs->links() }}
                    </div>
                </div>
            @endif
        </div>
    @endcan

    {{-- Modal Bagikan ke Tabungan --}}
    @can('bagikan ke tabungan')
        <div class="modal fade" id="modalBagikanTabungan" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content border-0 shadow-lg" style="border-radius: 14px; overflow: hidden;">
                    <form action="{{ route('cash-savings.store') }}" method="POST" enctype="multipart/form-data" id="formBagikanTabungan">
                        @csrf
                        <div class="modal-header bg-white border-bottom py-3 px-4 d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-3">
                                <div class="avatar bg-azure-lt text-azure rounded-circle" style="width: 40px; height: 40px;">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M17 8v-3a1 1 0 0 0 -1 -1h-10a2 2 0 0 0 0 4h12a1 1 0 0 1 1 1v3m0 4v3a1 1 0 0 1 -1 1h-12a2 2 0 0 1 -2 -2v-12" /><path d="M20 12v4h-4a2 2 0 0 1 0 -4h4" /></svg>
                                </div>
                                <div>
                                    <h4 class="modal-title fw-bold text-dark mb-0">Bagikan Saldo ke Tabungan</h4>
                                    <p class="text-muted small mb-0 mt-0.5">Alokasikan sebagian saldo kas aktif operasional ke rekening tabungan terpisah</p>
                                </div>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body p-4 bg-white">
                            {{-- Info Banner Saldo Kas Tersedia --}}
                            <div class="p-3 mb-3 border rounded-3 d-flex align-items-center justify-content-between" style="background: linear-gradient(135deg, rgba(32, 107, 196, 0.06), rgba(32, 107, 196, 0.02)); border-color: rgba(32, 107, 196, 0.2) !important;">
                                <div class="d-flex align-items-center gap-2.5">
                                    <span class="badge bg-primary text-white p-2 rounded-circle">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0" /><path d="M12 8l.01 0" /><path d="M11 12l1 0l0 4l1 0" /></svg>
                                    </span>
                                    <div>
                                        <div class="small text-muted" style="font-size: 0.75rem;">Sisa Saldo Kas Aktif Saat Ini</div>
                                        <div class="fw-bold font-monospace text-primary fs-3" id="display_max_balance" data-max="{{ $currentCashBalance }}">
                                            Rp {{ number_format($currentCashBalance, 0, ',', '.') }}
                                        </div>
                                    </div>
                                </div>
                                <span class="badge bg-azure-lt font-monospace text-azure fw-bold">Limit Alokasi</span>
                            </div>

                            <div class="row g-3">
                                @if(!$isAdmin && !empty($defaultSavingsAccount['is_configured']))
                                    {{-- KONDISI BUKAN ADMIN: Data rekening otomatis terisi dan terkunci (Tersimpan) --}}
                                    <input type="hidden" name="recipient_name" value="{{ $defaultSavingsAccount['recipient_name'] }}">
                                    <input type="hidden" name="bank_name" value="{{ $defaultSavingsAccount['bank_name'] }}">
                                    <input type="hidden" name="account_number" value="{{ $defaultSavingsAccount['account_number'] }}">

                                    <div class="col-12">
                                        <div class="border rounded-3 p-3 bg-azure-lt border-azure-subtle shadow-xs">
                                            <div class="d-flex align-items-center justify-content-between mb-2 pb-1 border-bottom border-azure-subtle">
                                                <div class="d-flex align-items-center gap-2">
                                                    <span class="badge bg-azure text-white p-1 rounded-circle">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l5 5l10 -10" /></svg>
                                                    </span>
                                                    <span class="fw-bold text-dark" style="font-size: 0.82rem;">Rekening Tabungan Tujuan (Otomatis &amp; Tersimpan)</span>
                                                </div>
                                                <span class="badge bg-azure text-white font-monospace" style="font-size: 0.7rem;">Tersimpan</span>
                                            </div>
                                            <div class="row g-2">
                                                <div class="col-md-5">
                                                    <div class="text-muted small" style="font-size: 0.72rem;">Nama Pemilik / Penerima Tabungan:</div>
                                                    <div class="fw-bold text-dark fs-4">{{ $defaultSavingsAccount['recipient_name'] }}</div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="text-muted small" style="font-size: 0.72rem;">Bank Tujuan:</div>
                                                    <span class="badge bg-white text-dark border font-monospace px-2.5 py-1 fw-bold fs-4">{{ $defaultSavingsAccount['bank_name'] }}</span>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="text-muted small" style="font-size: 0.72rem;">Nomor Rekening:</div>
                                                    <div class="font-monospace fw-bold text-dark fs-4">{{ $defaultSavingsAccount['account_number'] ?: '-' }}</div>
                                                </div>
                                            </div>
                                            <div class="text-muted small mt-2 pt-2 border-top border-azure-subtle d-flex align-items-center gap-1" style="font-size: 0.72rem;">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0" /><path d="M12 8l.01 0" /><path d="M11 12l1 0l0 4l1 0" /></svg>
                                                <span>Rekening tujuan tabungan telah ditetapkan. Silakan <strong>langsung masukkan nominal alokasi tabungan</strong> di bawah.</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label required fw-semibold text-dark">Tanggal Alokasi</label>
                                        <input type="date" name="transaction_date" class="form-control font-monospace" value="{{ date('Y-m-d') }}" required>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold text-dark">Catatan / Pos Tabungan</label>
                                        <input type="text" name="notes" class="form-control" placeholder="Contoh: Dana cadangan ekspansi, kas darurat, dll">
                                    </div>
                                @else
                                    {{-- KONDISI ADMIN (Bisa Ubah-Ubah) ATAU Rekening Belum Pernah Tersimpan --}}
                                    @if($isAdmin)
                                        <div class="col-12">
                                            <div class="alert alert-info py-2 px-3 mb-1 d-flex align-items-center justify-content-between" style="border-radius: 8px; font-size: 0.76rem;">
                                                <div class="d-flex align-items-center gap-2">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0" /><path d="M12 9h.01" /><path d="M11 12h1v4h1" /></svg>
                                                    <span><strong>Mode Administrator:</strong> Data rekening terisi otomatis dan dapat Anda ubah kapan saja. Perubahan akan disimpan untuk alokasi berikutnya.</span>
                                                </div>
                                                <span class="badge bg-primary text-white font-monospace">Bisa Ubah</span>
                                            </div>
                                        </div>
                                    @endif

                                    <div class="col-md-6">
                                        <label class="form-label required fw-semibold text-dark">Tanggal Alokasi</label>
                                        <input type="date" name="transaction_date" class="form-control font-monospace" value="{{ date('Y-m-d') }}" required>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label required fw-semibold text-dark">Nama Penerima / Rekening Tabungan</label>
                                        <input type="text" name="recipient_name" class="form-control" required placeholder="Contoh: Rekening Tabungan PT CIO / Yoga" value="{{ old('recipient_name', $defaultSavingsAccount['recipient_name'] ?? '') }}">
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label required fw-semibold text-dark">Pilihan Bank</label>
                                        <select name="bank_name" id="saving_bank_select" class="form-select" required>
                                            <option value="">-- Pilih Bank Tujuan --</option>
                                            @php
                                                $selectedBank = old('bank_name', $defaultSavingsAccount['bank_name'] ?? '');
                                                $isPresetBank = false;
                                            @endphp
                                            @if(isset($banksGrouped))
                                                @foreach($banksGrouped as $category => $banks)
                                                    <optgroup label="{{ $category }}">
                                                        @foreach($banks as $b)
                                                            @php
                                                                if ($selectedBank === $b['name']) {
                                                                    $isPresetBank = true;
                                                                }
                                                            @endphp
                                                            <option value="{{ $b['name'] }}" {{ $selectedBank === $b['name'] ? 'selected' : '' }}>{{ $b['name'] }}</option>
                                                        @endforeach
                                                    </optgroup>
                                                @endforeach
                                            @endif
                                            <option value="other" {{ (!$isPresetBank && !empty($selectedBank)) ? 'selected' : '' }}>Bank Lainnya (Input Manual)...</option>
                                        </select>
                                        <div id="saving_other_bank_wrapper" class="mt-2" style="{{ (!$isPresetBank && !empty($selectedBank)) ? 'display: block;' : 'display: none;' }}">
                                            <input type="text" id="saving_other_bank_input" class="form-control" placeholder="Ketik nama bank tujuan..." value="{{ (!$isPresetBank && !empty($selectedBank)) ? $selectedBank : '' }}">
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold text-dark">Nomor Rekening (Opsional)</label>
                                        <input type="text" name="account_number" class="form-control font-monospace" placeholder="Contoh: 7128912345" value="{{ old('account_number', $defaultSavingsAccount['account_number'] ?? '') }}">
                                    </div>
                                @endif

                                <div class="col-12">
                                    <label class="form-label required fw-semibold text-dark d-flex justify-content-between align-items-center">
                                        <span>Nominal yang Dialokasikan ke Tabungan (Rp)</span>
                                        <span class="text-muted small" style="font-size: 0.72rem;">Otomatis memotong Saldo Kas</span>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light fw-bold text-azure">Rp</span>
                                        <input type="text" name="amount" id="saving_amount_input" class="form-control font-monospace fw-bold fs-3 text-azure" required placeholder="0">
                                    </div>
                                    {{-- Quick Percent Buttons --}}
                                    <div class="d-flex align-items-center gap-1.5 mt-2">
                                        <span class="text-muted small me-1" style="font-size: 0.72rem;">Cepat:</span>
                                        <button type="button" class="btn btn-sm btn-outline-secondary btn-quick-pct py-0.5 px-2" data-pct="0.10" style="font-size: 0.72rem;">10%</button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary btn-quick-pct py-0.5 px-2" data-pct="0.25" style="font-size: 0.72rem;">25%</button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary btn-quick-pct py-0.5 px-2" data-pct="0.50" style="font-size: 0.72rem;">50%</button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary btn-quick-pct py-0.5 px-2" data-pct="0.75" style="font-size: 0.72rem;">75%</button>
                                        <button type="button" class="btn btn-sm btn-outline-primary btn-quick-pct py-0.5 px-2" data-pct="1.00" style="font-size: 0.72rem;">100% (Semua Kas)</button>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold text-dark">Bukti Transfer / Slip Setoran (Opsional)</label>
                                    <input type="file" name="proof_file" class="form-control" accept=".jpg,.jpeg,.png,.webp,.pdf">
                                    <small class="text-muted" style="font-size: 0.7rem;">Maksimal 5MB (JPG, PNG, WEBP, PDF)</small>
                                </div>

                                @if($isAdmin || empty($defaultSavingsAccount['is_configured']))
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold text-dark">Catatan / Pos Tabungan</label>
                                        <input type="text" name="notes" class="form-control" placeholder="Contoh: Dana cadangan ekspansi, kas darurat, dll">
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="modal-footer bg-light py-2.5 px-4 d-flex justify-content-between">
                            <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-1.5 px-4" id="btn_submit_saving">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l5 5l10 -10" /></svg>
                                <span>Simpan &amp; Potong Saldo Kas</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endcan

    {{-- Modal Detail Slip Alokasi Tabungan --}}
    <div class="modal fade" id="modalShowSaving" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-md">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 14px; overflow: hidden;">
                <div class="modal-header bg-white border-bottom py-3 px-4 d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2.5">
                        <div class="avatar bg-azure-lt text-azure rounded-circle" style="width: 36px; height: 36px;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M14 3v4a1 1 0 0 0 1 1h4" /><path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z" /></svg>
                        </div>
                        <div>
                            <h4 class="modal-title fw-bold text-dark mb-0">Slip Bukti Alokasi Tabungan</h4>
                            <p class="text-muted small mb-0 font-monospace" id="show_saving_ref">-</p>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4 bg-light-subtle">
                    <div class="card border-0 shadow-sm p-3 mb-3 bg-white" style="border-radius: 10px;">
                        <div class="text-muted small mb-1">Nominal yang Dialokasikan</div>
                        <div class="fw-bold font-monospace text-azure fs-2 mb-2" id="show_saving_amount">Rp 0</div>
                        <div class="d-flex align-items-center gap-1.5">
                            <span class="badge bg-azure-lt fw-bold font-monospace">Tabungan Terpisah</span>
                            <span class="text-muted small" id="show_saving_date">-</span>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm p-3 bg-white" style="border-radius: 10px;">
                        <div class="row g-2.5 small">
                            <div class="col-5 text-muted">Nama Penerima</div>
                            <div class="col-7 fw-bold text-dark" id="show_saving_recipient">-</div>

                            <div class="col-5 text-muted">Bank Tujuan</div>
                            <div class="col-7 fw-semibold text-dark" id="show_saving_bank">-</div>

                            <div class="col-5 text-muted">Nomor Rekening</div>
                            <div class="col-7 font-monospace text-dark" id="show_saving_account">-</div>

                            <div class="col-5 text-muted">Dicatat Oleh</div>
                            <div class="col-7 text-dark" id="show_saving_creator">-</div>

                            <div class="col-5 text-muted">Waktu Catat</div>
                            <div class="col-7 text-muted" id="show_saving_created_at">-</div>

                            <div class="col-5 text-muted">Catatan</div>
                            <div class="col-7 text-dark" id="show_saving_notes">-</div>
                        </div>
                    </div>

                    <div class="mt-3" id="show_saving_proof_wrapper" style="display: none;">
                        <label class="form-label small fw-semibold text-muted mb-1">Lampiran Bukti Transfer</label>
                        <div class="border rounded p-2 text-center bg-white">
                            <img src="" id="show_saving_proof_img" class="img-fluid rounded mb-2" style="max-height: 240px; display: none;" alt="Bukti Transfer">
                            <a href="#" id="show_saving_proof_link" target="_blank" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2 -2v-2" /><path d="M7 11l5 5l5 -5" /><path d="M12 4l0 12" /></svg>
                                Buka File Lampiran Lengkap
                            </a>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-white py-2.5 px-4 d-flex justify-content-end border-top">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Helper Format Rupiah
            function formatRupiah(angka) {
                var number_string = angka.replace(/[^,\d]/g, '').toString(),
                    split = number_string.split(','),
                    sisa = split[0].length % 3,
                    rupiah = split[0].substr(0, sisa),
                    ribuan = split[0].substr(sisa).match(/\d{3}/gi);

                if (ribuan) {
                    var separator = sisa ? '.' : '';
                    rupiah += separator + ribuan.join('.');
                }

                return split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
            }

            // Input Rupiah listener
            var amountInput = document.getElementById('saving_amount_input');
            if (amountInput) {
                amountInput.addEventListener('keyup', function (e) {
                    this.value = formatRupiah(this.value);
                });
            }

            // Quick Percent Buttons
            var maxBalance = parseFloat(document.getElementById('display_max_balance')?.getAttribute('data-max') || 0);
            var pctButtons = document.querySelectorAll('.btn-quick-pct');
            pctButtons.forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var pct = parseFloat(this.getAttribute('data-pct') || 0);
                    if (maxBalance <= 0) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Saldo Kas Kosong / Defisit',
                            text: 'Total saldo kas aktif saat ini tidak mencukupi untuk dialokasikan.',
                        });
                        return;
                    }
                    var calculated = Math.floor(maxBalance * pct);
                    if (amountInput) {
                        amountInput.value = formatRupiah(calculated.toString());
                    }
                });
            });

            // Toggle Bank Lainnya
            var bankSelect = document.getElementById('saving_bank_select');
            var otherBankWrapper = document.getElementById('saving_other_bank_wrapper');
            var otherBankInput = document.getElementById('saving_other_bank_input');
            if (bankSelect && otherBankWrapper) {
                bankSelect.addEventListener('change', function () {
                    if (this.value === 'other') {
                        otherBankWrapper.style.display = 'block';
                        if (otherBankInput) otherBankInput.setAttribute('required', 'required');
                    } else {
                        otherBankWrapper.style.display = 'none';
                        if (otherBankInput) otherBankInput.removeAttribute('required');
                    }
                });
            }

            // Auto-focus input nominal saat modal dibuka
            var modalBagikanEl = document.getElementById('modalBagikanTabungan');
            if (modalBagikanEl) {
                modalBagikanEl.addEventListener('shown.bs.modal', function () {
                    if (amountInput) {
                        amountInput.focus();
                        amountInput.select();
                    }
                });
            }

            // AJAX Submit Form Bagikan Tabungan
            var formBagikan = document.getElementById('formBagikanTabungan');
            if (formBagikan) {
                formBagikan.addEventListener('submit', function (e) {
                    e.preventDefault();

                    var submitBtn = document.getElementById('btn_submit_saving');
                    var rawVal = (amountInput.value || '').replace(/\./g, '').replace(/,/g, '.');
                    var nominal = parseFloat(rawVal) || 0;

                    if (nominal <= 0) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Nominal Tidak Valid',
                            text: 'Masukkan nominal alokasi tabungan yang lebih besar dari Rp 0.',
                        });
                        return;
                    }

                    if (nominal > maxBalance) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Melebihi Saldo Kas!',
                            text: 'Nominal alokasi (Rp ' + formatRupiah(nominal.toString()) + ') melebihi sisa saldo kas aktif (Rp ' + formatRupiah(maxBalance.toString()) + ').',
                        });
                        return;
                    }

                    // SweetAlert Konfirmasi
                    Swal.fire({
                        title: 'Konfirmasi Alokasi Tabungan',
                        html: 'Apakah Anda yakin ingin memotong saldo kas aktif sebesar <strong>Rp ' + formatRupiah(nominal.toString()) + '</strong> untuk dialokasikan ke rekening tabungan?',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, Alokasikan Sekarang',
                        cancelButtonText: 'Batal',
                        confirmButtonColor: '#206bc4',
                        reverseButtons: true
                    }).then(function (result) {
                        if (result.isConfirmed) {
                            var formData = new FormData(formBagikan);
                            if (bankSelect && bankSelect.value === 'other' && otherBankInput && otherBankInput.value.trim() !== '') {
                                formData.set('bank_name', otherBankInput.value.trim());
                            }

                            if (submitBtn) {
                                submitBtn.disabled = true;
                                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...';
                            }

                            fetch(formBagikan.action, {
                                method: 'POST',
                                body: formData,
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest'
                                }
                            })
                            .then(function (res) {
                                return res.json().then(function (data) {
                                    return { status: res.status, data: data };
                                });
                            })
                            .then(function (response) {
                                if (submitBtn) {
                                    submitBtn.disabled = false;
                                    submitBtn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l5 5l10 -10" /></svg> Simpan &amp; Potong Saldo Kas';
                                }

                                if (response.status === 200 || response.data.status === 'success') {
                                    var modalEl = document.getElementById('modalBagikanTabungan');
                                    var modalInstance = bootstrap.Modal.getInstance(modalEl);
                                    if (modalInstance) modalInstance.hide();

                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Berhasil!',
                                        text: response.data.message || 'Alokasi saldo ke tabungan berhasil disimpan.',
                                        timer: 1600,
                                        showConfirmButton: false
                                    }).then(function () {
                                        window.location.reload();
                                    });
                                } else {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Gagal Menyimpan',
                                        text: response.data.message || 'Terjadi kesalahan validasi data.',
                                    });
                                }
                            })
                            .catch(function (err) {
                                if (submitBtn) {
                                    submitBtn.disabled = false;
                                    submitBtn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l5 5l10 -10" /></svg> Simpan &amp; Potong Saldo Kas';
                                }
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Kesalahan Sistem',
                                    text: 'Gagal menghubungkan ke server. Silakan coba kembali.',
                                });
                            });
                        }
                    });
                });
            }

            // Detail Slip Transaksi Tabungan
            document.querySelectorAll('.btn-show-saving').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var id = this.getAttribute('data-id');
                    fetch('/cash-savings/' + id, {
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    })
                    .then(function (res) { return res.json(); })
                    .then(function (res) {
                        if (res.status === 'success' && res.data) {
                            var d = res.data;
                            document.getElementById('show_saving_ref').innerText = d.transaction_number;
                            document.getElementById('show_saving_amount').innerText = d.formatted_amount;
                            document.getElementById('show_saving_date').innerText = d.transaction_date;
                            document.getElementById('show_saving_recipient').innerText = d.recipient_name;
                            document.getElementById('show_saving_bank').innerText = d.bank_name;
                            document.getElementById('show_saving_account').innerText = d.account_number;
                            document.getElementById('show_saving_creator').innerText = d.creator_name;
                            document.getElementById('show_saving_created_at').innerText = d.created_at;
                            document.getElementById('show_saving_notes').innerText = d.notes;

                            var proofWrap = document.getElementById('show_saving_proof_wrapper');
                            var proofImg = document.getElementById('show_saving_proof_img');
                            var proofLink = document.getElementById('show_saving_proof_link');

                            if (d.proof_url) {
                                proofWrap.style.display = 'block';
                                proofLink.href = d.proof_url;
                                if (d.proof_file && (d.proof_file.endsWith('.pdf') || d.proof_file.endsWith('.PDF'))) {
                                    proofImg.style.display = 'none';
                                } else {
                                    proofImg.src = d.proof_url;
                                    proofImg.style.display = 'inline-block';
                                }
                            } else {
                                proofWrap.style.display = 'none';
                            }

                            var modalShow = new bootstrap.Modal(document.getElementById('modalShowSaving'));
                            modalShow.show();
                        }
                    })
                    .catch(function () {
                        Swal.fire({ icon: 'error', title: 'Gagal', text: 'Tidak dapat memuat detail slip alokasi tabungan.' });
                    });
                });
            });

            // Hapus Transaksi Tabungan
            document.querySelectorAll('.btn-delete-saving').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var id = this.getAttribute('data-id');
                    var ref = this.getAttribute('data-ref');
                    var amountStr = this.getAttribute('data-amount');

                    Swal.fire({
                        title: 'Hapus Alokasi Tabungan?',
                        html: 'Anda akan menghapus data alokasi <strong>' + ref + '</strong> sebesar <strong>' + amountStr + '</strong>.<br><br><span class="text-danger fw-semibold">Saldo kas aktif operasional akan otomatis dikembalikan sebesar nominal ini.</span>',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, Hapus & Kembalikan Saldo',
                        cancelButtonText: 'Batal',
                        confirmButtonColor: '#d63939',
                        reverseButtons: true
                    }).then(function (result) {
                        if (result.isConfirmed) {
                            fetch('/cash-savings/' + id, {
                                method: 'DELETE',
                                headers: {
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                    'X-Requested-With': 'XMLHttpRequest'
                                }
                            })
                            .then(function (res) { return res.json(); })
                            .then(function (data) {
                                if (data.status === 'success') {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Berhasil Dihapus',
                                        text: data.message,
                                        timer: 1500,
                                        showConfirmButton: false
                                    }).then(function () {
                                        window.location.reload();
                                    });
                                } else {
                                    Swal.fire({ icon: 'error', title: 'Gagal', text: data.message || 'Gagal menghapus data alokasi.' });
                                }
                            })
                            .catch(function () {
                                Swal.fire({ icon: 'error', title: 'Kesalahan', text: 'Gagal memproses penghapusan ke server.' });
                            });
                        }
                    });
                });
            });
        });
    </script>
    @endpush
@endsection