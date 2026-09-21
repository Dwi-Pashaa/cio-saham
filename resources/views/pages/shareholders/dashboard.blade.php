@extends('layouts.app')

@section('title', 'Portofolio Saham Saya')
@section('pretitle', 'INVESTOR PORTAL')
@section('subtitle', 'Ringkasan Kepemilikan Saham & Kinerja Finansial Realtime')

@section('actions')
    <div class="d-none d-md-flex align-items-center gap-2">
        <button type="button" class="btn btn-outline-primary btn-sm d-inline-flex align-items-center gap-1.5" onclick="if(window.refreshFinanceChart) { window.refreshFinanceChart(); } else { window.location.reload(); }">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 11a8.1 8.1 0 0 0 -15.5 -2m-.5 -4v4h4" /><path d="M4 13a8.1 8.1 0 0 0 15.5 2m.5 4v-4h-4" /></svg>
            <span>Segarkan Data</span>
        </button>
        <span class="badge bg-success-lt text-success fw-bold d-inline-flex align-items-center gap-1 py-1.5 px-2.5">
            <span class="pulse-live-dot" style="width: 6px; height: 6px;"></span> Investor Terverifikasi
        </span>
    </div>
@endsection

@section('content')
    {{-- =====================================================================
         A. NATIVE ANDROID FINTECH APP VIEW (Khusus Mobile: < 768px)
         ===================================================================== --}}
    <div class="android-dashboard-view d-md-none">
        {{-- 1. Hero Wallet Card --}}
        <div class="android-hero-wallet mb-3">
            <div class="android-wallet-card">
                <div class="android-wallet-bg-pattern"></div>
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="android-wallet-label">
                        <span class="pulse-live-dot me-1.5" style="width: 6px; height: 6px;"></span>
                        TOTAL MODAL INVESTASI SAYA
                    </span>
                    <button type="button" class="android-wallet-eye-btn" id="toggle-balance-btn" onclick="toggleWalletBalance()" title="Sembunyikan/Tampilkan Nilai">
                        <span id="eye-icon-open">
                            {{-- Tabler: eye --}}
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 12a2 2 0 1 0 4 0a2 2 0 0 0 -4 0" /><path d="M21 12c-2.4 4 -5.4 6 -9 6c-3.6 0 -6.6 -2 -9 -6c2.4 -4 5.4 -6 9 -6c3.6 0 6.6 2 9 6" /></svg>
                        </span>
                        <span id="eye-icon-closed" class="d-none">
                            {{-- Tabler: eye-off --}}
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.585 10.587a2 2 0 0 0 2.829 2.828" /><path d="M16.681 16.673a8.717 8.717 0 0 1 -4.681 1.327c-3.6 0 -6.6 -2 -9 -6c1.272 -2.12 2.712 -3.678 4.32 -4.674m2.86 -1.146a9.055 9.055 0 0 1 1.82 -.18c3.6 0 6.6 2 9 6c-.792 1.32 -1.733 2.43 -2.82 3.329" /><path d="M3 3l18 18" /></svg>
                        </span>
                    </button>
                </div>

                <div class="android-wallet-amount">
                    <span id="wallet-balance-text">Rp {{ number_format($totalInvestment, 0, ',', '.') }}</span>
                    <span id="wallet-balance-masked" class="d-none">Rp ••••••••••</span>
                </div>

                <div class="android-wallet-subchips">
                    <span class="android-subchip">
                        <span class="subchip-dot" style="background-color: #38bdf8;"></span>
                        <span>{{ number_format($totalShares, 0, ',', '.') }} Lembar Saham</span>
                    </span>
                    <span class="android-subchip">
                        <span class="subchip-dot" style="background-color: #10b981;"></span>
                        <span>Porsi: <strong>{{ number_format($totalPercentage, 2) }}%</strong></span>
                    </span>
                </div>

                {{-- 3 Quick Action Buttons with Smooth Scroll --}}
                <div class="android-quick-actions">
                    <button type="button" class="android-action-item border-0 bg-transparent" data-scroll-to="chart" onclick="scrollToQuickSection('chart', event);">
                        <div class="android-action-icon">
                            {{-- Tabler: chart-line --}}
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19l16 0" /><path d="M4 15l4 -6l4 2l4 -5l4 4" /></svg>
                        </div>
                        <span>Grafik</span>
                    </button>

                    <button type="button" class="android-action-item border-0 bg-transparent" data-scroll-to="saham" onclick="scrollToQuickSection('saham', event);">
                        <div class="android-action-icon">
                            {{-- Tabler: file-certificate --}}
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 3v4a1 1 0 0 0 1 1h4" /><path d="M5 8v-3a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2h-5" /><path d="M6 14m-3 0a3 3 0 1 0 6 0a3 3 0 1 0 -6 0" /><path d="M4.5 17l-1.5 5l3 -1.5l3 1.5l-1.5 -5" /></svg>
                        </div>
                        <span>Saham</span>
                    </button>

                    <button type="button" class="android-action-item border-0 bg-transparent" data-scroll-to="profil" onclick="scrollToQuickSection('profil', event);">
                        <div class="android-action-icon">
                            {{-- Tabler: id --}}
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 4m0 3a3 3 0 0 1 3 -3h12a3 3 0 0 1 3 3v10a3 3 0 0 1 -3 3h-12a3 3 0 0 1 -3 -3z" /><path d="M9 10m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0" /><path d="M15 8l2 0" /><path d="M15 12l2 0" /><path d="M7 16l10 0" /></svg>
                        </div>
                        <span>Profil</span>
                    </button>
                </div>
            </div>
        </div>

        {{-- 2. Insight Chips Bar --}}
        <div class="android-insight-chips-bar">
            <div class="android-insight-chip">
                {{-- Tabler: diamond --}}
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 5h12l3 5l-8.5 9.5a.7 .7 0 0 1 -1 0l-8.5 -9.5l3 -5" /><path d="M10 12l-2 -2.2l.6 -1" /></svg>
                <span>Modal: <strong>Rp {{ number_format($totalInvestment, 0, ',', '.') }}</strong></span>
            </div>
            <div class="android-insight-chip">
                {{-- Tabler: chart-pie --}}
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 3.2a9 9 0 1 0 10.8 10.8a1 1 0 0 0 -1 -1h-6.8a2 2 0 0 1 -2 -2v-6.8a1 1 0 0 0 -1 -1" /><path d="M15 3.5a9 9 0 0 1 5.5 5.5h-4.5a1 1 0 0 1 -1 -1v-4.5" /></svg>
                <span>Porsi: <strong>{{ number_format($totalPercentage, 2) }}%</strong></span>
            </div>
            <div class="android-insight-chip">
                {{-- Tabler: coin --}}
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0" /><path d="M14.8 9a2 2 0 0 0 -1.8 -1h-2a2 2 0 1 0 0 4h2a2 2 0 1 1 0 4h-2a2 2 0 0 1 -1.8 -1" /><path d="M12 7v10" /></svg>
                <span>Net Profit: <strong>Rp {{ number_format($chartData['summary']['net_profit'] ?? 0, 0, ',', '.') }}</strong></span>
            </div>
            <div class="android-insight-chip">
                {{-- Tabler: briefcase --}}
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 7m0 2a2 2 0 0 1 2 -2h14a2 2 0 0 1 2 2v9a2 2 0 0 1 -2 2h-14a2 2 0 0 1 -2 -2z" /><path d="M8 7v-2a2 2 0 0 1 2 -2h4a2 2 0 0 1 2 2v2" /><path d="M12 12l0 .01" /><path d="M3 13a20 20 0 0 0 18 0" /></svg>
                <span>Portofolio: <strong>{{ $portfoliosCount }} Saham</strong></span>
            </div>
            <div class="android-insight-chip">
                {{-- Tabler: shield-check --}}
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11.46 20.846a12 12 0 0 1 -7.96 -14.846a12 12 0 0 0 8.5 -3a12 12 0 0 0 8.5 3a12 12 0 0 1 -.09 7.06" /><path d="M15 19l2 2l4 -4" /></svg>
                <span>Status: <strong>Terverifikasi Aktif</strong></span>
            </div>
        </div>

        {{-- 3. 2x2 Metric Cards --}}
        <div class="android-metric-grid">
            {{-- Metric 1: Total Lembar Saham --}}
            <div class="android-metric-card">
                <div class="android-metric-header">
                    <span class="android-metric-title">Lembar Saham</span>
                    <span class="android-metric-mini-icon bg-blue-lt text-primary">
                        {{-- Tabler: file-stack --}}
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 3v4a1 1 0 0 0 1 1h4" /><path d="M5 12v-7a2 2 0 0 1 2 -2h7l5 5v4" /><path d="M5 21h14" /><path d="M5 18h14" /><path d="M5 15h14" /></svg>
                    </span>
                </div>
                <div class="android-metric-val text-primary">
                    {{ number_format($totalShares, 0, ',', '.') }} <span class="fw-normal" style="font-size: 0.8em;">Lbr</span>
                </div>
                <div class="android-metric-foot">
                    <span class="badge bg-blue-lt font-monospace px-1.5 py-0.5">{{ $portfoliosCount }} Entitas Saham</span>
                </div>
            </div>

            {{-- Metric 2: Porsi Kepemilikan --}}
            <div class="android-metric-card">
                <div class="android-metric-header">
                    <span class="android-metric-title">Porsi Kepemilikan</span>
                    <span class="android-metric-mini-icon bg-success-lt text-success">
                        {{-- Tabler: chart-pie --}}
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 3.2a9 9 0 1 0 10.8 10.8a1 1 0 0 0 -1 -1h-6.8a2 2 0 0 1 -2 -2v-6.8a1 1 0 0 0 -1 -1" /><path d="M15 3.5a9 9 0 0 1 5.5 5.5h-4.5a1 1 0 0 1 -1 -1v-4.5" /></svg>
                    </span>
                </div>
                <div class="android-metric-val text-success">
                    {{ number_format($totalPercentage, 2) }}%
                </div>
                <div class="android-metric-foot">
                    <span class="badge bg-success-lt font-monospace px-1.5 py-0.5">Pemegang Terdaftar</span>
                </div>
            </div>

            {{-- Metric 3: Portofolio --}}
            <div class="android-metric-card">
                <div class="android-metric-header">
                    <span class="android-metric-title">Portofolio Saham</span>
                    <span class="android-metric-mini-icon bg-warning-lt text-warning">
                        {{-- Tabler: award --}}
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 9m-6 0a6 6 0 1 0 12 0a6 6 0 1 0 -12 0" /><path d="M12 15l3.4 5.89l1.598 -3.233l3.6 .232l-3.4 -5.889" /><path d="M6.802 12l-3.4 5.89l3.598 .232l1.6 3.233l3.4 -5.889" /></svg>
                    </span>
                </div>
                <div class="android-metric-val text-warning">
                    {{ $portfoliosCount }} Saham
                </div>
                <div class="android-metric-foot">
                    <span class="badge bg-warning-lt font-monospace px-1.5 py-0.5">Semua Aktif</span>
                </div>
            </div>
        </div>
    </div>

    {{-- =====================================================================
         B. DESKTOP ENTERPRISE HERO & KEY METRICS (Desktop Only: >= 768px)
         ===================================================================== --}}
    <div class="d-none d-md-block">
        {{-- 1. Executive Investor Hero Banner --}}
        <div class="investor-hero-card">
            <div class="row align-items-center g-4">
                <div class="col-lg-7">
                    {{-- Identity Section --}}
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="investor-avatar-circle">
                            {{ strtoupper(substr($shareholder->name ?? $user->name, 0, 2)) }}
                        </div>
                        <div>
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <span class="badge bg-white text-primary fw-bold font-monospace px-2 py-0.5" style="font-size: 0.72rem; letter-spacing: 0.04em;">
                                    PORTOFOLIO INVESTOR SAHAM
                                </span>
                                <span class="badge bg-success text-white font-monospace px-2 py-0.5 d-inline-flex align-items-center gap-1" style="font-size: 0.72rem;">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12l5 5l10 -10" /></svg>
                                    Terdaftar & Aktif
                                </span>
                            </div>
                            <h1 class="h1 fw-bold text-white mb-0" style="font-size: 1.75rem; letter-spacing: -0.02em;">
                                {{ $shareholder->name ?? $user->name }}
                            </h1>
                        </div>
                    </div>

                    {{-- Profile Data Grid (2 columns, neat) --}}
                    <div class="investor-profile-grid">
                        <div class="investor-profile-item">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 4m0 3a3 3 0 0 1 3 -3h12a3 3 0 0 1 3 3v10a3 3 0 0 1 -3 3h-12a3 3 0 0 1 -3 -3z" /><path d="M9 10m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0" /><path d="M15 8l2 0" /><path d="M15 12l2 0" /><path d="M7 16l10 0" /></svg>
                            <span class="investor-profile-label">NIK</span>
                            <span class="investor-profile-value font-monospace">{{ $shareholder->id_card_number ?? '-' }}</span>
                        </div>
                        <div class="investor-profile-item">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 4h4l2 5l-2.5 1.5a11 11 0 0 0 5 5l1.5 -2.5l5 2v4a2 2 0 0 1 -2 2a16 16 0 0 1 -15 -15a2 2 0 0 1 2 -2" /></svg>
                            <span class="investor-profile-label">Telepon</span>
                            <span class="investor-profile-value font-monospace">{{ $shareholder->phone ?? $user->phone ?? '-' }}</span>
                        </div>
                        <div class="investor-profile-item">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 7a2 2 0 0 1 2 -2h14a2 2 0 0 1 2 2v10a2 2 0 0 1 -2 2h-14a2 2 0 0 1 -2 -2v-10z" /><path d="M3 7l9 6l9 -6" /></svg>
                            <span class="investor-profile-label">Email</span>
                            <span class="investor-profile-value">{{ $shareholder->email ?? $user->email }}</span>
                        </div>
                        <div class="investor-profile-item">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 7a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v12a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12z" /><path d="M16 3v4" /><path d="M8 3v4" /><path d="M4 11h16" /><path d="M11 15h1" /><path d="M12 15v3" /></svg>
                            <span class="investor-profile-label">Bergabung</span>
                            <span class="investor-profile-value">{{ $shareholder && $shareholder->created_at ? $shareholder->created_at->format('d M Y') : '-' }}</span>
                        </div>
                    </div>
                </div>

                <div class="col-lg-5">
                    <div class="investor-glass-box text-white">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="text-uppercase tracking-wider fw-bold text-white-50" style="font-size: 0.72rem; letter-spacing: 0.05em;">
                                TOTAL MODAL INVESTASI SAYA
                            </span>
                            <button type="button" class="btn btn-sm text-white-50 p-0 border-0 bg-transparent" onclick="toggleWalletBalanceDesk()" title="Sembunyikan/Tampilkan Nilai">
                                <span id="desk-eye-open">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 12a2 2 0 1 0 4 0a2 2 0 0 0 -4 0" /><path d="M21 12c-2.4 4 -5.4 6 -9 6c-3.6 0 -6.6 -2 -9 -6c2.4 -4 5.4 -6 9 -6c3.6 0 6.6 2 9 6" /></svg>
                                </span>
                                <span id="desk-eye-closed" class="d-none">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.585 10.587a2 2 0 0 0 2.829 2.828" /><path d="M16.681 16.673a8.717 8.717 0 0 1 -4.681 1.327c-3.6 0 -6.6 -2 -9 -6c1.272 -2.12 2.712 -3.678 4.32 -4.674m2.86 -1.146a9.055 9.055 0 0 1 1.82 -.18c3.6 0 6.6 2 9 6c-.792 1.32 -1.733 2.43 -2.82 3.329" /><path d="M3 3l18 18" /></svg>
                                </span>
                            </button>
                        </div>
                        <div class="h1 fw-bold text-white font-monospace mb-3" style="font-size: 1.85rem; letter-spacing: -0.02em;">
                            <span id="desk-wallet-balance-text">Rp {{ number_format($totalInvestment, 0, ',', '.') }}</span>
                            <span id="desk-wallet-balance-masked" class="d-none">Rp ••••••••••</span>
                        </div>
                        <div class="d-flex flex-column gap-1.5">
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-white text-primary font-monospace fw-bold px-2 py-1 d-inline-flex align-items-center gap-1" style="font-size: 0.72rem;">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M10 3.2a9 9 0 1 0 10.8 10.8a1 1 0 0 0 -1 -1h-6.8a2 2 0 0 1 -2 -2v-6.8a1 1 0 0 0 -1 -1" /><path d="M15 3.5a9 9 0 0 1 5.5 5.5h-4.5a1 1 0 0 1 -1 -1v-4.5" /></svg>
                                    Porsi Kepemilikan: {{ number_format($totalPercentage, 2) }}%
                                </span>
                            </div>
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <span class="badge bg-white-lt text-white font-monospace px-2 py-1 d-inline-flex align-items-center gap-1" style="font-size: 0.72rem; background: rgba(255,255,255,0.12) !important;">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14 3v4a1 1 0 0 0 1 1h4" /><path d="M5 12v-7a2 2 0 0 1 2 -2h7l5 5v4" /><path d="M5 21h14" /><path d="M5 18h14" /><path d="M5 15h14" /></svg>
                                    {{ number_format($totalShares, 0, ',', '.') }} Lembar Saham
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- 2. 3 Metric Cards --}}
        <div class="row row-deck row-cards mb-4">
            <div class="col-sm-6 col-lg-4">
                <div class="card p-3 border shadow-sm h-100 bg-white" style="border-radius: 14px;">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted small fw-bold text-uppercase">Modal Disetor</span>
                        <span class="avatar avatar-sm bg-primary-lt text-primary rounded-circle">
                            {{-- Tabler: wallet --}}
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 8v-3a1 1 0 0 0 -1 -1h-10a2 2 0 0 0 0 4h12a1 1 0 0 1 1 1v3m0 4v3a1 1 0 0 1 -1 1h-12a2 2 0 0 1 -2 -2v-12" /><path d="M20 12v4h-4a2 2 0 0 1 0 -4h4" /></svg>
                        </span>
                    </div>
                    <div class="h2 fw-bold text-primary font-monospace mb-1">
                        Rp {{ number_format($totalInvestment, 0, ',', '.') }}
                    </div>
                    <div class="text-muted small">Akumulasi seluruh instrumen saham</div>
                </div>
            </div>

            <div class="col-sm-6 col-lg-4">
                <div class="card p-3 border shadow-sm h-100 bg-white" style="border-radius: 14px;">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted small fw-bold text-uppercase">Total Lembar Saham</span>
                        <span class="avatar avatar-sm bg-azure-lt text-azure rounded-circle">
                            {{-- Tabler: file-stack --}}
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 3v4a1 1 0 0 0 1 1h4" /><path d="M5 12v-7a2 2 0 0 1 2 -2h7l5 5v4" /><path d="M5 21h14" /><path d="M5 18h14" /><path d="M5 15h14" /></svg>
                        </span>
                    </div>
                    <div class="h2 fw-bold text-dark font-monospace mb-1">
                        {{ number_format($totalShares, 0, ',', '.') }} Lembar
                    </div>
                    <div class="text-muted small">Terbagi dalam {{ $portfoliosCount }} portofolio aktif</div>
                </div>
            </div>

            <div class="col-sm-6 col-lg-4">
                <div class="card p-3 border shadow-sm h-100 bg-white" style="border-radius: 14px;">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted small fw-bold text-uppercase">Porsi Kepemilikan</span>
                        <span class="avatar avatar-sm bg-success-lt text-success rounded-circle">
                            {{-- Tabler: chart-pie --}}
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 3.2a9 9 0 1 0 10.8 10.8a1 1 0 0 0 -1 -1h-6.8a2 2 0 0 1 -2 -2v-6.8a1 1 0 0 0 -1 -1" /><path d="M15 3.5a9 9 0 0 1 5.5 5.5h-4.5a1 1 0 0 1 -1 -1v-4.5" /></svg>
                        </span>
                    </div>
                    <div class="h2 fw-bold text-success font-monospace mb-1">
                        {{ number_format($totalPercentage, 2) }}%
                    </div>
                    <div class="text-muted small">Dari total modal saham perusahaan</div>
                </div>
            </div>
        </div>
    </div>

    {{-- =====================================================================
         C. SHARED REALTIME FINANCE CHART & TRANSACTION LOGS (Adaptive for Mobile & Desktop)
         ===================================================================== --}}
    <div id="realtime-chart" class="mb-4 scroll-anchor-target">
        @include('components.finance-log-chart', [
            'chartData' => $chartData,
            'timeframe' => $timeframe,
            'webFilter' => $webFilter,
            'title' => 'Grafik Kinerja Finansial (Income, Outcome & Keuntungan)',
            'subtitle' => 'Visualisasi realtime arus kas masuk (Income), pengeluaran (Outcome), dan Keuntungan Bersih (Net Profit) konsolidasi usaha.'
        ])

        {{-- 2-Tab Riwayat Log Transaksi (Web Internal & Gateway Xendit) --}}
        @include('components.finance-log-table', [
            'logs' => $recentLogs,
            'webFilter' => $webFilter,
        ])
    </div>

    {{-- =====================================================================
         D. MOBILE DETAILS & HOLDINGS (Mobile Only: < 768px)
         ===================================================================== --}}
    <div class="android-dashboard-view d-md-none">
        {{-- 5. Rincian Portofolio Saham --}}
        <div id="mobile-my-holdings" class="mt-2 scroll-anchor-target">
            <div class="d-flex justify-content-between align-items-center mb-2 px-1">
                <div class="fw-bold text-dark fs-3 d-flex align-items-center gap-1.5">
                    {{-- Tabler: file-certificate --}}
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-primary"><path d="M14 3v4a1 1 0 0 0 1 1h4" /><path d="M5 8v-3a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2h-5" /><path d="M6 14m-3 0a3 3 0 1 0 6 0a3 3 0 1 0 -6 0" /><path d="M4.5 17l-1.5 5l3 -1.5l3 1.5l-1.5 -5" /></svg>
                    <span>Instrumen Saham Saya</span>
                </div>
                <span class="badge bg-primary-lt font-monospace">{{ $portfoliosCount }} Portofolio</span>
            </div>

            @if($shareholder && $shareholder->holdings->count() > 0)
                <div class="d-flex flex-column gap-2 mb-3">
                    @foreach($shareholder->holdings as $holding)
                        <div class="card border shadow-sm p-3 bg-white" style="border-radius: 16px;">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <span class="badge bg-blue-lt font-monospace fw-bold mb-1">{{ $holding->share_code }}</span>
                                    <div class="fw-bold text-dark fs-3">{{ $holding->entity_name }}</div>
                                </div>
                                <span class="badge bg-success text-white px-2 py-1 font-monospace">{{ number_format($holding->percentage_share, 2) }}%</span>
                            </div>

                            <div class="row g-2 pt-2 border-top">
                                <div class="col-6">
                                    <div class="text-muted small">Total Lembar:</div>
                                    <strong class="text-primary font-monospace fs-4">{{ number_format($holding->total_shares, 0, ',', '.') }} Lbr</strong>
                                </div>
                                <div class="col-6 text-end">
                                    <div class="text-muted small">Modal Disetor:</div>
                                    <strong class="text-dark font-monospace fs-4">Rp {{ number_format($holding->total_investment, 0, ',', '.') }}</strong>
                                </div>
                                <div class="col-6">
                                    <div class="text-muted small">Harga / Lembar:</div>
                                    <span class="font-monospace small">Rp {{ number_format($holding->nominal_value_per_share, 0, ',', '.') }}</span>
                                </div>
                                <div class="col-6 text-end">
                                    <div class="text-muted small">Tanggal Akuisisi:</div>
                                    <span class="small">{{ $holding->acquisition_date ? \Carbon\Carbon::parse($holding->acquisition_date)->format('d M Y') : '-' }}</span>
                                </div>
                            </div>

                            @if($holding->certificate_number)
                                <div class="mt-2.5 pt-2 border-top d-flex justify-content-between align-items-center">
                                    <span class="small text-muted font-monospace d-inline-flex align-items-center gap-1">
                                        {{-- Tabler: certificate --}}
                                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 15m-3 0a3 3 0 1 0 6 0a3 3 0 1 0 -6 0" /><path d="M13 17.5v4.5l2 -1.5l2 1.5v-4.5" /><path d="M10 19h-5a2 2 0 0 1 -2 -2v-10a2 2 0 0 1 2 -2h14a2 2 0 0 1 2 2v10a2 2 0 0 1 -1 1.73" /><path d="M6 9l12 0" /><path d="M6 12l3 0" /><path d="M6 15l2 0" /></svg>
                                        {{ $holding->certificate_number }}
                                    </span>
                                    <span class="badge bg-success-lt font-monospace small">Sertifikat Sah</span>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @else
                <div class="card p-4 text-center text-muted border shadow-sm mb-3" style="border-radius: 16px;">
                    <p class="mb-0">Belum ada instrumen saham tercatat untuk akun Anda.</p>
                </div>
            @endif
        </div>

        {{-- 6. Identitas & Legalitas --}}
        <div id="my-profile" class="card border shadow-sm p-3 bg-white mb-3 scroll-anchor-target" style="border-radius: 16px;">
            <div class="d-flex align-items-center gap-2 mb-3">
                <div class="avatar bg-blue-lt text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                    {{-- Tabler: user-circle --}}
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0" /><path d="M12 10m-3 0a3 3 0 1 0 6 0a3 3 0 1 0 -6 0" /><path d="M6.168 18.849a4 4 0 0 1 3.832 -2.849h4a4 4 0 0 1 3.834 2.855" /></svg>
                </div>
                <div>
                    <div class="fw-bold text-dark fs-3">{{ $shareholder->name ?? $user->name }}</div>
                    <div class="text-muted small">Pemegang Saham Terverifikasi</div>
                </div>
            </div>

            <div class="row g-2 small border-top pt-2">
                <div class="col-6">
                    <span class="text-muted d-block">NIK:</span>
                    <strong class="text-dark font-monospace">{{ $shareholder->id_card_number ?? '-' }}</strong>
                </div>
                <div class="col-6 text-end">
                    <span class="text-muted d-block">Nomor Telepon:</span>
                    <strong class="text-dark font-monospace">{{ $shareholder->phone ?? $user->phone ?? '-' }}</strong>
                </div>
                <div class="col-12 mt-1">
                    <span class="text-muted d-block">Email Terdaftar:</span>
                    <span class="text-dark">{{ $shareholder->email ?? $user->email }}</span>
                </div>
                <div class="col-12 mt-1">
                    <span class="text-muted d-block">Alamat Domisili:</span>
                    <span class="text-dark">{{ $shareholder->address ?? '-' }}</span>
                </div>
                @if($shareholder && $shareholder->notes)
                    <div class="col-12 mt-1 border-top pt-1 text-muted">
                        <em>Catatan: {{ $shareholder->notes }}</em>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- =====================================================================
         E. DESKTOP DETAILS & TABLE (Desktop Only: >= 768px)
         ===================================================================== --}}
    <div class="d-none d-md-block">
        {{-- 4. Tabel Portofolio Saham --}}
        <div id="my-holdings" class="card border shadow-sm mb-4 scroll-anchor-target">
            <div class="card-header d-flex justify-content-between align-items-center py-3 bg-white">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-primary text-white p-1.5 rounded-circle">
                        {{-- Tabler: certificate --}}
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 15m-3 0a3 3 0 1 0 6 0a3 3 0 1 0 -6 0" /><path d="M13 17.5v4.5l2 -1.5l2 1.5v-4.5" /><path d="M10 19h-5a2 2 0 0 1 -2 -2v-10a2 2 0 0 1 2 -2h14a2 2 0 0 1 2 2v10a2 2 0 0 1 -1 1.73" /><path d="M6 9l12 0" /><path d="M6 12l3 0" /><path d="M6 15l2 0" /></svg>
                    </span>
                    <h3 class="card-title fw-bold mb-0 text-dark">Rincian Kepemilikan Instrumen Saham</h3>
                </div>
                <span class="badge bg-blue-lt font-monospace px-2 py-1">{{ $portfoliosCount }} Portofolio Terdaftar</span>
            </div>
            <div class="table-responsive">
                <table class="table table-vcenter card-table table-striped table-hover mb-0">
                    <thead>
                        <tr class="bg-light">
                            <th>Kode Saham</th>
                            <th>Entitas Bisnis</th>
                            <th class="text-end">Jumlah Lembar</th>
                            <th class="text-end">Harga / Lembar</th>
                            <th class="text-end">Total Nilai Investasi</th>
                            <th class="text-center">Porsi (%)</th>
                            <th>Nomor Sertifikat</th>
                            <th>Tanggal Perolehan</th>
                            <th class="text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if($shareholder && $shareholder->holdings->count() > 0)
                            @foreach($shareholder->holdings as $holding)
                                <tr>
                                    <td><span class="badge bg-blue-lt font-monospace fw-bold">{{ $holding->share_code }}</span></td>
                                    <td><strong class="text-dark fs-4">{{ $holding->entity_name }}</strong></td>
                                    <td class="text-end font-monospace fw-bold text-primary" style="white-space: nowrap;">{{ number_format($holding->total_shares, 0, ',', '.') }} Lembar</td>
                                    <td class="text-end font-monospace" style="white-space: nowrap;">Rp {{ number_format($holding->nominal_value_per_share, 0, ',', '.') }}</td>
                                    <td class="text-end font-monospace fw-bold text-dark" style="white-space: nowrap;">Rp {{ number_format($holding->total_investment, 0, ',', '.') }}</td>
                                    <td class="text-center" style="white-space: nowrap;">
                                        <span class="badge bg-success-lt font-monospace fw-bold fs-4">{{ number_format($holding->percentage_share, 2) }}%</span>
                                    </td>
                                    <td>
                                        <span class="font-monospace small text-muted d-inline-flex align-items-center gap-1">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 3v4a1 1 0 0 0 1 1h4" /><path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z" /><path d="M9 15l2 2l4 -4" /></svg>
                                            {{ $holding->certificate_number ?? '-' }}
                                        </span>
                                    </td>
                                    <td style="white-space: nowrap;">{{ $holding->acquisition_date ? \Carbon\Carbon::parse($holding->acquisition_date)->format('d M Y') : '-' }}</td>
                                    <td class="text-center"><span class="badge bg-success text-white">Aktif</span></td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4"><em>Belum ada rincian data saham terdaftar untuk akun ini.</em></td>
                            </tr>
                        @endif
                    </tbody>
                    @if($shareholder && $shareholder->holdings->count() > 0)
                        <tfoot class="bg-light fw-bold">
                            <tr>
                                <td colspan="2" class="text-uppercase text-dark font-monospace">TOTAL AKUMULASI</td>
                                <td class="text-end font-monospace text-primary">{{ number_format($totalShares, 0, ',', '.') }} Lembar</td>
                                <td></td>
                                <td class="text-end font-monospace text-dark">Rp {{ number_format($totalInvestment, 0, ',', '.') }}</td>
                                <td class="text-center font-monospace text-success">{{ number_format($totalPercentage, 2) }}%</td>
                                <td colspan="3"></td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>

        {{-- 5. Identitas & Legalitas --}}
        <div class="row row-cards mb-4">
            <div class="col-12">
                <div id="desktop-my-profile" class="card border shadow-sm scroll-anchor-target">
                    <div class="card-header py-3 bg-white d-flex align-items-center gap-2">
                        <span class="badge bg-azure-lt p-1.5 rounded-circle">
                            {{-- Tabler: id --}}
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 4m0 3a3 3 0 0 1 3 -3h12a3 3 0 0 1 3 3v10a3 3 0 0 1 -3 3h-12a3 3 0 0 1 -3 -3z" /><path d="M9 10m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0" /><path d="M15 8l2 0" /><path d="M15 12l2 0" /><path d="M7 16l10 0" /></svg>
                        </span>
                        <h3 class="card-title fw-bold text-dark mb-0">Identitas & Legalitas Pemegang Saham</h3>
                    </div>
                    <div class="card-body">
                        <dl class="row mb-0 gy-3">
                            <dt class="col-sm-3 text-muted">Nama Lengkap:</dt>
                            <dd class="col-sm-9 fw-bold text-dark fs-4">{{ $shareholder->name ?? $user->name }}</dd>

                            <dt class="col-sm-3 text-muted">NIK KTP:</dt>
                            <dd class="col-sm-9 font-monospace fw-bold text-dark">{{ $shareholder->id_card_number ?? '-' }}</dd>

                            <dt class="col-sm-3 text-muted">Alamat Email:</dt>
                            <dd class="col-sm-9">{{ $shareholder->email ?? $user->email }}</dd>

                            <dt class="col-sm-3 text-muted">Nomor Telepon:</dt>
                            <dd class="col-sm-9 font-monospace">{{ $shareholder->phone ?? $user->phone ?? '-' }}</dd>

                            <dt class="col-sm-3 text-muted">Alamat Domisili:</dt>
                            <dd class="col-sm-9">{{ $shareholder->address ?? '-' }}</dd>

                            <dt class="col-sm-3 text-muted">Status Registrasi:</dt>
                            <dd class="col-sm-9">
                                <span class="badge bg-success-lt fw-bold d-inline-flex align-items-center gap-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12l5 5l10 -10" /></svg>
                                    Terdaftar & Terverifikasi
                                </span>
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
<script>
    function scrollToQuickSection(section, e) {
        if (e && typeof e.preventDefault === 'function') {
            e.preventDefault();
            e.stopPropagation();
        }
        
        var target = null;
        if (section === 'chart' || section === 'grafik') {
            target = document.getElementById('realtime-chart');
        } else if (section === 'holdings' || section === 'saham') {
            var mobH = document.getElementById('mobile-my-holdings');
            var dskH = document.getElementById('my-holdings');
            if (mobH && mobH.offsetParent !== null) {
                target = mobH;
            } else {
                target = dskH || mobH;
            }
        } else if (section === 'profile' || section === 'profil') {
            var mobP = document.getElementById('my-profile');
            var dskP = document.getElementById('desktop-my-profile');
            if (mobP && mobP.offsetParent !== null) {
                target = mobP;
            } else {
                target = dskP || mobP;
            }
        } else if (typeof section === 'string') {
            target = document.getElementById(section) || document.querySelector(section);
        }
        
        if (target) {
            // 1. Native scrollIntoView
            target.scrollIntoView({ behavior: 'smooth', block: 'start', inline: 'nearest' });
            
            // 2. Direct offset window scroll fallback (handling sticky headers)
            var isMobile = window.innerWidth < 768;
            var headerOffset = isMobile ? 65 : 85;
            var scrollY = window.pageYOffset || document.documentElement.scrollTop || document.body.scrollTop || 0;
            var elementTop = target.getBoundingClientRect().top + scrollY - headerOffset;
            
            window.scrollTo({
                top: Math.max(0, elementTop),
                behavior: 'smooth'
            });
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('[data-scroll-to]').forEach(function(btn) {
            btn.addEventListener('click', function(evt) {
                var targetName = this.getAttribute('data-scroll-to');
                if (targetName) {
                    scrollToQuickSection(targetName, evt);
                }
            });
        });
    });

    function toggleWalletBalance() {
        const t = document.getElementById('wallet-balance-text'),
              m = document.getElementById('wallet-balance-masked'),
              o = document.getElementById('eye-icon-open'),
              c = document.getElementById('eye-icon-closed');
        if (!t || !m || !o || !c) return;
        const hidden = t.classList.contains('d-none');
        t.classList.toggle('d-none', !hidden);
        m.classList.toggle('d-none', hidden);
        o.classList.toggle('d-none', !hidden);
        c.classList.toggle('d-none', hidden);
    }

    function toggleWalletBalanceDesk() {
        const t = document.getElementById('desk-wallet-balance-text'),
              m = document.getElementById('desk-wallet-balance-masked'),
              o = document.getElementById('desk-eye-open'),
              c = document.getElementById('desk-eye-closed');
        if (!t || !m || !o || !c) return;
        const hidden = t.classList.contains('d-none');
        t.classList.toggle('d-none', !hidden);
        m.classList.toggle('d-none', hidden);
        o.classList.toggle('d-none', !hidden);
        c.classList.toggle('d-none', hidden);
    }
</script>
@endpush
