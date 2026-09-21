@extends('layouts.app')

@section('pretitle', 'PORTAL EKUITAS & PERTUMBUHAN USAHA')
@section('title', 'Dashboard Finansial & Portofolio Saham')
@section('subtitle', 'Monitoring konsolidasi arus kas mutasi multi-unit web, gateway Xendit, dan kepemilikan saham.')

@section('actions')
    <button type="button" class="btn btn-outline-primary btn-sm d-inline-flex align-items-center gap-1.5" onclick="if(window.refreshFinanceChart) { window.refreshFinanceChart(); } else { window.location.reload(); }">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M20 11a8.1 8.1 0 0 0 -15.5 -2m-.5 -4v4h4" /><path d="M4 13a8.1 8.1 0 0 0 15.5 2m.5 4v-4h-4" /></svg>
        <span>Segarkan Data</span>
    </button>
@endsection

@section('content')
    @php
        // Financial overview data
        $totalBalance = (float) ($overviewData['balance']['total'] ?? 0);
        $manualBalance = (float) ($overviewData['balance']['manual'] ?? 0);
        $xenditBalance = (float) ($overviewData['balance']['xendit'] ?? 0);
        $currentRevenue = (float) ($overviewData['current_month']['revenue'] ?? 0);
        $currentNetProfit = (float) ($overviewData['current_month']['net_profit'] ?? 0);
        $profitMargin = (float) ($overviewData['current_month']['profit_margin'] ?? 0);
        $xenditStatus = $overviewData['xendit_status'] ?? 'connected';

        // Growth analytics data
        $healthScore = (int) ($growthAnalytics['financial_health_score'] ?? ($growthAnalytics['health_score'] ?? 85));
        $growthMoM = (float) ($growthAnalytics['growth_rate_mom'] ?? ($growthAnalytics['revenue_growth_rate'] ?? 0));
        $growthYoY = (float) ($growthAnalytics['growth_rate_yoy'] ?? 0);
        $statusText = $growthAnalytics['status'] ?? ($growthAnalytics['status_label'] ?? 'Healthy & Expanding');
        $netMargin = (float) ($growthAnalytics['profitability']['net_margin'] ?? ($growthAnalytics['profit_margin'] ?? 0));
        $burnRate = (float) ($growthAnalytics['profitability']['monthly_burn_rate'] ?? 0);
        $runway = (float) ($growthAnalytics['profitability']['runway_months'] ?? 12.0);
        $valuation = (float) ($growthAnalytics['estimated_company_valuation'] ?? ($growthAnalytics['current_valuation'] ?? 180000000));
    @endphp

    <!-- =========================================================================
         A. NATIVE ANDROID FINTECH APP DASHBOARD (Visible on Mobile Only: < 768px)
         ========================================================================= -->
    <div class="d-md-none android-dashboard-layout">
        <!-- 1. Native Hero Wallet / Balance Card -->
        <div class="android-hero-wallet">
            <div class="android-wallet-card">
                <div class="android-wallet-bg-pattern"></div>
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="android-wallet-label">
                        <span class="pulse-live-dot me-1.5" style="width: 6px; height: 6px;"></span>
                        Total Saldo Konsolidasi
                    </span>
                    <button type="button" class="android-wallet-eye-btn" id="toggle-balance-btn" onclick="toggleWalletBalance()" title="Sembunyikan/Tampilkan Saldo">
                        <svg id="eye-icon-open" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="icon"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 12a2 2 0 1 0 4 0a2 2 0 0 0 -4 0" /><path d="M21 12c-2.4 4 -5.4 6 -9 6c-3.6 0 -6.6 -2 -9 -6c2.4 -4 5.4 -6 9 -6c3.6 0 6.6 2 9 6" /></svg>
                        <svg id="eye-icon-closed" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="icon d-none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10.585 10.587a2 2 0 0 0 2.829 2.828" /><path d="M16.681 16.673a8.717 8.717 0 0 1 -4.681 1.327c-3.6 0 -6.6 -2 -9 -6c1.272 -2.12 2.712 -3.678 4.32 -4.674m2.86 -1.146a9.055 9.055 0 0 1 1.82 -.18c3.6 0 6.6 2 9 6c-.666 1.11 -1.379 2.067 -2.138 2.87" /><path d="M3 3l18 18" /></svg>
                    </button>
                </div>

                <!-- Big Nominal -->
                <div class="android-wallet-amount" id="wallet-balance-text">
                    Rp {{ number_format($totalBalance, 0, ',', '.') }}
                </div>
                <div class="android-wallet-amount d-none" id="wallet-balance-masked">
                    Rp ••••••••••
                </div>

                <!-- Sub-chips (Manual vs Xendit) -->
                <div class="android-wallet-subchips">
                    <span class="android-subchip">
                        <span class="subchip-dot bg-primary"></span>
                        <span>Manual: <strong>Rp {{ number_format($manualBalance, 0, ',', '.') }}</strong></span>
                    </span>
                    <span class="android-subchip">
                        <span class="subchip-dot bg-success"></span>
                        <span>Xendit: <strong>Rp {{ number_format($xenditBalance, 0, ',', '.') }}</strong></span>
                    </span>
                </div>

                <!-- 4 Native App Quick Actions -->
                <div class="android-quick-actions">
                    <a href="{{ route('shareholders.create') }}" class="android-action-item">
                        <div class="android-action-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M8 7a4 4 0 1 0 8 0a4 4 0 0 0 -8 0" /><path d="M6 21v-2a4 4 0 0 1 4 -4h4c.342 0 .674 .043 .99 .124" /><path d="M16 19h6" /><path d="M19 16v6" /></svg>
                        </div>
                        <span>+ Saham</span>
                    </a>

                    <button type="button" class="android-action-item border-0 bg-transparent" onclick="if(window.refreshFinanceChart) { window.refreshFinanceChart(); } else { window.location.reload(); }">
                        <div class="android-action-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M20 11a8.1 8.1 0 0 0 -15.5 -2m-.5 -4v4h4" /><path d="M4 13a8.1 8.1 0 0 0 15.5 2m.5 4v-4h-4" /></svg>
                        </div>
                        <span>Segarkan</span>
                    </button>

                    <a href="{{ route('shareholders.index') }}" class="android-action-item">
                        <div class="android-action-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9 7m-4 0a4 4 0 1 0 8 0a4 4 0 1 0 -8 0" /><path d="M3 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2" /><path d="M16 3.13a4 4 0 0 1 0 7.75" /><path d="M21 21v-2a4 4 0 0 0 -3 -3.85" /></svg>
                        </div>
                        <span>Investor</span>
                    </a>

                    <a href="{{ route('setting') }}" class="android-action-item">
                        <div class="android-action-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10.325 4.317c.426 -1.756 2.924 -1.756 3.35 0a1.724 1.724 0 0 0 2.573 1.066c1.543 -.94 3.31 .826 2.37 2.37a1.724 1.724 0 0 0 1.065 2.572c1.756 .426 1.756 2.924 0 3.35a1.724 1.724 0 0 0 -1.066 2.573c.94 1.543 -.826 3.31 -2.37 2.37a1.724 1.724 0 0 0 -2.572 1.065c-.426 1.756 -2.924 1.756 -3.35 0a1.724 1.724 0 0 0 -2.573 -1.066c-1.543 .94 -3.31 -.826 -2.37 -2.37a1.724 1.724 0 0 0 -1.065 -2.572c-1.756 -.426 -1.756 -2.924 0 -3.35a1.724 1.724 0 0 0 1.066 -2.573c-.94 -1.543 .826 -3.31 2.37 -2.37c1 .608 2.296 .07 2.572 -1.065z" /><path d="M9 12a3 3 0 1 0 6 0a3 3 0 0 0 -6 0" /></svg>
                        </div>
                        <span>Setelan</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- 2. Horizontal Swipeable Insight Chips Bar -->
        <div class="android-insight-chips-bar">
            <div class="android-insight-chip">
                <span>🚀</span>
                <span>Performa: <strong>{{ $statusText }}</strong></span>
            </div>
            <div class="android-insight-chip">
                <span class="{{ $growthMoM >= 0 ? 'text-success' : 'text-danger' }}">{{ $growthMoM >= 0 ? '▲' : '▼' }}</span>
                <span>MoM: <strong class="{{ $growthMoM >= 0 ? 'text-success' : 'text-danger' }}">{{ $growthMoM >= 0 ? '+' : '' }}{{ number_format($growthMoM, 1) }}%</strong></span>
            </div>
            <div class="android-insight-chip">
                <span>💎</span>
                <span>Margin: <strong>{{ number_format($profitMargin, 1) }}%</strong></span>
            </div>
            <div class="android-insight-chip">
                <span>⏱️</span>
                <span>Runway: <strong>{{ number_format($runway, 1) }} Bln</strong></span>
            </div>
            <div class="android-insight-chip">
                <span>🏥</span>
                <span>Skor Sehat: <strong>{{ $healthScore }}/100</strong></span>
            </div>
        </div>

        <!-- 3. Android 2x2 Metric Cards Grid -->
        <div class="android-metric-grid">
            <!-- Metric 1: Revenue -->
            <div class="android-metric-card">
                <div class="android-metric-header">
                    <span class="android-metric-title">Revenue Bulan Ini</span>
                    <span class="android-metric-mini-icon bg-success-lt text-success">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 19l16 0" /><path d="M4 15l4 -6l4 2l4 -5l4 4" /></svg>
                    </span>
                </div>
                <div class="android-metric-val text-dark">
                    Rp {{ number_format($currentRevenue, 0, ',', '.') }}
                </div>
                <div class="android-metric-foot">
                    <span class="badge bg-success-lt font-monospace px-1.5 py-0.5">Surplus {{ number_format($profitMargin, 1) }}%</span>
                </div>
            </div>

            <!-- Metric 2: Modal Saham -->
            <div class="android-metric-card">
                <div class="android-metric-header">
                    <span class="android-metric-title">Modal Saham</span>
                    <span class="android-metric-mini-icon bg-warning-lt text-warning">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M15 15m-3 0a3 3 0 1 0 6 0a3 3 0 1 0 -6 0" /><path d="M13 17.5v4.5l2 -1.5l2 1.5v-4.5" /></svg>
                    </span>
                </div>
                <div class="android-metric-val text-primary">
                    Rp {{ number_format((float) ($equitySummary['total_investment'] ?? 180000000), 0, ',', '.') }}
                </div>
                <div class="android-metric-foot text-muted small font-monospace">
                    {{ number_format((int) ($equitySummary['total_shares'] ?? 18000), 0, ',', '.') }} Lembar
                </div>
            </div>

            <!-- Metric 3: Pemegang Saham -->
            <div class="android-metric-card">
                <div class="android-metric-header">
                    <span class="android-metric-title">Pemegang Saham</span>
                    <span class="android-metric-mini-icon bg-info-lt text-info">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9 7m-4 0a4 4 0 1 0 8 0a4 4 0 1 0 -8 0" /><path d="M3 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2" /></svg>
                    </span>
                </div>
                <div class="android-metric-val text-dark">
                    {{ $equitySummary['total_shareholders'] ?? 3 }} <span class="fs-5 fw-normal text-muted">Orang</span>
                </div>
                <div class="android-metric-foot text-muted small">
                    {{ $equitySummary['total_holdings'] ?? 4 }} Alokasi Portofolio
                </div>
            </div>

            <!-- Metric 4: Laba Bersih -->
            <div class="android-metric-card">
                <div class="android-metric-header">
                    <span class="android-metric-title">Laba Bersih</span>
                    <span class="android-metric-mini-icon bg-blue-lt text-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M17 8v-3a1 1 0 0 0 -1 -1h-10a2 2 0 0 0 0 4h12a1 1 0 0 1 1 1v3m0 4v3a1 1 0 0 1 -1 1h-12a2 2 0 0 1 -2 -2v-12" /><path d="M20 12v4h-4a2 2 0 0 1 0 -4h4" /></svg>
                    </span>
                </div>
                <div class="android-metric-val {{ $currentNetProfit >= 0 ? 'text-success' : 'text-danger' }}">
                    Rp {{ number_format($currentNetProfit, 0, ',', '.') }}
                </div>
                <div class="android-metric-foot text-muted small">
                    Margin: {{ number_format($profitMargin, 1) }}%
                </div>
            </div>
        </div>

        <!-- 4. Android Health Score & Valuation Sheet Card -->
        <div class="android-health-sheet">
            <div class="android-health-header">
                <div class="android-health-title">
                    <span>🏥</span>
                    <span>Financial Health & Valuation</span>
                </div>
                <span class="badge {{ $healthScore >= 80 ? 'bg-success' : ($healthScore >= 60 ? 'bg-warning' : 'bg-danger') }} text-white fw-bold px-2 py-1">
                    {{ $healthScore }}/100
                </span>
            </div>
            <div class="progress progress-sm mb-3" style="background-color: #e2e8f0; height: 6px; border-radius: 9999px;">
                <div class="progress-bar {{ $healthScore >= 80 ? 'bg-success' : ($healthScore >= 60 ? 'bg-warning' : 'bg-danger') }}" style="width: {{ $healthScore }}%" role="progressbar"></div>
            </div>
            <div class="android-health-row">
                <span>Estimasi Valuasi Usaha:</span>
                <strong class="text-primary fs-4">Rp {{ number_format($valuation, 0, ',', '.') }}</strong>
            </div>
            @if($burnRate > 0)
            <div class="android-health-row">
                <span>Monthly Burn Rate:</span>
                <strong class="text-warning">Rp {{ number_format($burnRate, 0, ',', '.') }}/bln</strong>
            </div>
            @endif
            <div class="android-health-row border-top pt-2 mt-1">
                <span>Status Koneksi Gateway:</span>
                <span class="badge bg-success-lt fw-bold">
                    <span class="pulse-live-dot me-1" style="width: 5px; height: 5px;"></span> Terhubung Realtime
                </span>
            </div>
        </div>
    </div>

    <!-- =========================================================================
         B. DESKTOP ENTERPRISE VIEW (Visible on >= 768px Only)
         ========================================================================= -->
    <div class="d-none d-md-block">
        <!-- 1. Widget Status Pertumbuhan Perusahaan (Bullish/Growing Indicator) -->
        @include('components.company-growth-widget', ['growth' => $growthAnalytics])

        <!-- 2. Ringkasan Metrik Saldo Finansial & Portofolio Saham -->
        <div class="row row-deck row-cards mb-4">
            <!-- Card 1: Total Saldo Konsolidasi -->
            <div class="col-sm-6 col-lg-3">
                @include('components.finance-metric-card', [
                    'title' => 'Total Saldo Konsolidasi',
                    'value' => 'Rp ' . number_format($totalBalance, 0, ',', '.'),
                    'subtitle' => 'Manual: Rp ' . number_format($manualBalance, 0, ',', '.') . ' | Xendit: Rp ' . number_format($xenditBalance, 0, ',', '.'),
                    'badgeText' => 'Live CIO Finance',
                    'badgeType' => 'success',
                    'icon' => '<svg xmlns="http://www.w3.org/2000/svg" class="icon" width="22" height="22" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M17 8v-3a1 1 0 0 0 -1 -1h-10a2 2 0 0 0 0 4h12a1 1 0 0 1 1 1v3m0 4v3a1 1 0 0 1 -1 1h-12a2 2 0 0 1 -2 -2v-12" /><path d="M20 12v4h-4a2 2 0 0 1 0 -4h4" /></svg>',
                    'iconBg' => 'primary',
                ])
            </div>

            <!-- Card 2: Revenue & Laba Bulan Berjalan -->
            <div class="col-sm-6 col-lg-3">
                @include('components.finance-metric-card', [
                    'title' => 'Revenue Bulan Berjalan',
                    'value' => 'Rp ' . number_format($currentRevenue, 0, ',', '.'),
                    'subtitle' => 'Laba Bersih: Rp ' . number_format($currentNetProfit, 0, ',', '.') . ' (' . number_format($profitMargin, 1) . '%)',
                    'badgeText' => $currentNetProfit >= 0 ? 'Surplus' : 'Defisit',
                    'badgeType' => $currentNetProfit >= 0 ? 'success' : 'danger',
                    'icon' => '<svg xmlns="http://www.w3.org/2000/svg" class="icon" width="22" height="22" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 19l16 0" /><path d="M4 15l4 -6l4 2l4 -5l4 4" /></svg>',
                    'iconBg' => 'success',
                ])
            </div>

            <!-- Card 3: Total Modal Saham Disetor -->
            <div class="col-sm-6 col-lg-3">
                @include('components.finance-metric-card', [
                    'title' => 'Total Modal Saham',
                    'value' => 'Rp ' . number_format((float) ($equitySummary['total_investment'] ?? 180000000), 0, ',', '.'),
                    'subtitle' => number_format((int) ($equitySummary['total_shares'] ?? 18000), 0, ',', '.') . ' Lembar Beredar',
                    'badgeText' => 'Ekuitas Terdaftar',
                    'badgeType' => 'warning',
                    'icon' => '<svg xmlns="http://www.w3.org/2000/svg" class="icon" width="22" height="22" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M15 15m-3 0a3 3 0 1 0 6 0a3 3 0 1 0 -6 0" /><path d="M13 17.5v4.5l2 -1.5l2 1.5v-4.5" /><path d="M10 19h-5a2 2 0 0 1 -2 -2v-10a2 2 0 0 1 2 -2h14a2 2 0 0 1 2 2v10a2 2 0 0 1 -1 1.73" /><path d="M6 9l12 0" /><path d="M6 12l3 0" /><path d="M6 15l2 0" /></svg>',
                    'iconBg' => 'warning',
                ])
            </div>

            <!-- Card 4: Pemegang Saham & Alokasi Portofolio -->
            <div class="col-sm-6 col-lg-3">
                @include('components.finance-metric-card', [
                    'title' => 'Pemegang Saham Aktif',
                    'value' => ($equitySummary['total_shareholders'] ?? 3) . ' Orang',
                    'subtitle' => ($equitySummary['total_holdings'] ?? 4) . ' Alokasi Saham Portofolio',
                    'badgeText' => '1 Pemilik > 1 Saham',
                    'badgeType' => 'info',
                    'icon' => '<svg xmlns="http://www.w3.org/2000/svg" class="icon" width="22" height="22" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9 7m-4 0a4 4 0 1 0 8 0a4 4 0 1 0 -8 0" /><path d="M3 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2" /><path d="M16 3.13a4 4 0 0 1 0 7.75" /><path d="M21 21v-2a4 4 0 0 0 -3 -3.85" /></svg>',
                    'iconBg' => 'info',
                ])
            </div>
        </div>
    </div>

    <!-- =========================================================================
         C. SHARED COMPONENTS (Adaptive for both Mobile & Desktop)
         ========================================================================= -->

    <!-- 3. Grafik Interaktif Log Web Finance Multi-Web (ApexCharts) -->
    @include('components.finance-log-chart', [
        'chartData' => $chartData,
        'timeframe' => $timeframe,
        'webFilter' => $webFilter,
    ])

    <!-- 4. Ringkasan Sumber Web & Pemegang Saham Terbesar -->
    <div class="row row-cards mb-4">
        <!-- Rincian Aktivitas per Web Client -->
        <div class="col-xl-6 col-lg-6">
            <div class="card border shadow-sm h-100 d-flex flex-column">
                <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2 py-2 px-3 bg-white border-bottom">
                    <div>
                        <h3 class="card-title fw-bold mb-0 text-dark fs-4">Rincian Arus Log per Unit Web</h3>
                        <p class="text-muted small mb-0" style="font-size: 0.76rem;">
                            Periode: <span class="fw-semibold text-primary font-monospace">{{ \Carbon\Carbon::parse($startDate ?? date('Y-m-d'))->translatedFormat('d M Y') }}</span>
                            @if(($startDate ?? date('Y-m-d')) !== ($endDate ?? date('Y-m-d')))
                                s/d <span class="fw-semibold text-primary font-monospace">{{ \Carbon\Carbon::parse($endDate ?? date('Y-m-d'))->translatedFormat('d M Y') }}</span>
                            @else
                                <span class="badge bg-blue-lt ms-1 font-monospace" style="font-size: 0.65rem; padding: 2px 5px;">Harian</span>
                            @endif
                        </p>
                    </div>

                    <!-- Date Range Filter Form -->
                    <form action="{{ route('dashboard') }}" method="GET" class="d-flex align-items-center gap-1 m-0">
                        @if(request('timeframe'))
                            <input type="hidden" name="timeframe" value="{{ request('timeframe') }}">
                        @endif
                        @if(request('web_filter'))
                            <input type="hidden" name="web_filter" value="{{ request('web_filter') }}">
                        @endif
                        <div class="input-group input-group-sm">
                            <input type="date" name="start_date" class="form-control form-control-sm font-monospace px-1.5 text-center" style="font-size: 0.74rem; max-width: 110px;" value="{{ $startDate ?? date('Y-m-d') }}" title="Tanggal Mulai" required>
                            <span class="input-group-text px-1 text-muted" style="font-size: 0.75rem;">-</span>
                            <input type="date" name="end_date" class="form-control form-control-sm font-monospace px-1.5 text-center" style="font-size: 0.74rem; max-width: 110px;" value="{{ $endDate ?? date('Y-m-d') }}" title="Tanggal Selesai" required>
                            <button type="submit" class="btn btn-sm btn-primary px-2" title="Filter Rentang Tanggal">
                                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 10m-7 0a7 7 0 1 0 14 0a7 7 0 1 0 -14 0" /><path d="M21 21l-6 -6" /></svg>
                            </button>
                        </div>
                        @if(($startDate ?? date('Y-m-d')) !== date('Y-m-d') || ($endDate ?? date('Y-m-d')) !== date('Y-m-d'))
                            <a href="{{ route('dashboard') }}" class="btn btn-sm btn-outline-secondary px-2" style="font-size: 0.74rem;" title="Reset ke Hari Ini">
                                Reset
                            </a>
                        @endif
                    </form>
                </div>
                <div class="table-responsive flex-grow-1">
                    <table class="table table-vcenter card-table table-hover mb-0">
                        <thead>
                            <tr class="bg-light-subtle text-muted text-uppercase" style="font-size: 0.68rem; letter-spacing: 0.04em;">
                                <th class="ps-3 py-2">Unit Web</th>
                                <th class="text-end py-2">Total Inflow</th>
                                <th class="text-end py-2">Total Outflow</th>
                                <th class="text-end py-2">Keuntungan</th>
                                <th class="text-center pe-3 py-2">Aktivitas</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $breakdownItems = $webBreakdown ?? $chartData['web_breakdown'] ?? [];
                                $totalInflowAll = 0;
                                $totalOutflowAll = 0;
                                $totalProfitAll = 0;
                                $totalEventsAll = 0;
                            @endphp
                            @if(!empty($breakdownItems))
                                @foreach($breakdownItems as $web)
                                    @php
                                        $inflow = (float) ($web['inflow'] ?? $web['total_inflow'] ?? 0);
                                        $outflow = (float) ($web['outflow'] ?? $web['total_outflow'] ?? 0);
                                        $profit = (float) ($web['net_profit'] ?? ($inflow - $outflow));
                                        $events = (int) ($web['log_count'] ?? 0);

                                        $totalInflowAll += $inflow;
                                        $totalOutflowAll += $outflow;
                                        $totalProfitAll += $profit;
                                        $totalEventsAll += $events;
                                    @endphp
                                    <tr>
                                        <td class="ps-3 py-2">
                                            <div class="fw-bold text-dark" style="font-size: 0.84rem;">{{ $web['name'] }}</div>
                                            <span class="badge bg-secondary-lt font-monospace" style="font-size: 0.64rem; padding: 1px 4px;">{{ $web['code'] }}</span>
                                        </td>
                                        <td class="text-end text-primary fw-semibold font-monospace py-2" style="white-space: nowrap; font-size: 0.8rem;">
                                            +Rp {{ number_format($inflow, 0, ',', '.') }}
                                        </td>
                                        <td class="text-end text-danger fw-semibold font-monospace py-2" style="white-space: nowrap; font-size: 0.8rem;">
                                            -Rp {{ number_format($outflow, 0, ',', '.') }}
                                        </td>
                                        <td class="text-end fw-bold font-monospace py-2" style="white-space: nowrap; font-size: 0.8rem;">
                                            @if($profit > 0)
                                                <span class="text-success">+Rp {{ number_format($profit, 0, ',', '.') }}</span>
                                            @elseif($profit < 0)
                                                <span class="text-danger">-Rp {{ number_format(abs($profit), 0, ',', '.') }}</span>
                                            @else
                                                <span class="text-muted">Rp 0</span>
                                            @endif
                                        </td>
                                        <td class="text-center pe-3 py-2">
                                            <span class="badge {{ $events > 0 ? 'bg-blue-lt text-primary' : 'bg-secondary-lt text-muted' }} fw-bold font-monospace" style="font-size: 0.72rem; padding: 3px 6px;">{{ $events }} Event</span>
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">
                                        <em>Belum ada rincian transaksi per unit web tercatat pada rentang tanggal ini.</em>
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                        @if(!empty($breakdownItems) && ($totalInflowAll > 0 || $totalOutflowAll > 0 || $totalEventsAll > 0))
                            <tfoot class="bg-light fw-bold border-top">
                                <tr>
                                    <td class="ps-3 py-2"><span class="text-uppercase small tracking-wider text-dark" style="font-size: 0.74rem;">Total</span></td>
                                    <td class="text-end text-primary font-monospace py-2" style="white-space: nowrap; font-size: 0.82rem;">
                                        +Rp {{ number_format($totalInflowAll, 0, ',', '.') }}
                                    </td>
                                    <td class="text-end text-danger font-monospace py-2" style="white-space: nowrap; font-size: 0.82rem;">
                                        -Rp {{ number_format($totalOutflowAll, 0, ',', '.') }}
                                    </td>
                                    <td class="text-end font-monospace py-2" style="white-space: nowrap; font-size: 0.82rem;">
                                        @if($totalProfitAll > 0)
                                            <span class="text-success">+Rp {{ number_format($totalProfitAll, 0, ',', '.') }}</span>
                                        @elseif($totalProfitAll < 0)
                                            <span class="text-danger">-Rp {{ number_format(abs($totalProfitAll), 0, ',', '.') }}</span>
                                        @else
                                            <span class="text-muted">Rp 0</span>
                                        @endif
                                    </td>
                                    <td class="text-center pe-3 py-2">
                                        <span class="badge bg-primary text-white font-monospace" style="font-size: 0.72rem; padding: 3px 6px;">{{ $totalEventsAll }} Event</span>
                                    </td>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>

        <!-- Daftar Pemilik Saham Terbesar (1 Pemilik -> Banyak Saham) -->
        <div class="col-xl-6 col-lg-6">
            <div class="card border shadow-sm h-100 d-flex flex-column">
                <div class="card-header d-flex justify-content-between align-items-center py-2 px-3 bg-white border-bottom" style="min-height: 57px;">
                    <div>
                        <h3 class="card-title fw-bold mb-0 text-dark fs-4">Struktur Kepemilikan Saham</h3>
                        <p class="text-muted small mb-0" style="font-size: 0.76rem;">Distribusi portofolio investor utama</p>
                    </div>
                    @can('view-shareholders')
                        <a href="{{ route('shareholders.index') }}" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1 px-2.5 py-1" style="font-size: 0.78rem;">
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
                                        <a href="{{ route('shareholders.show', $sh->id) }}" class="text-reset fw-bold d-block text-decoration-none hover-primary" style="font-size: 0.84rem;">
                                            {{ $sh->name }}
                                        </a>
                                        <div class="text-muted small" style="font-size: 0.72rem;">NIK: <span class="font-monospace">{{ $sh->id_card_number }}</span></div>
                                    </td>
                                    <td class="text-center py-2">
                                        <span class="badge bg-purple-lt fw-bold font-monospace" style="font-size: 0.72rem; padding: 3px 6px;">
                                            {{ $sh->active_holdings_count ?? $sh->activeHoldings->count() }} Saham
                                        </span>
                                    </td>
                                    <td class="text-end fw-bold font-monospace text-dark py-2" style="white-space: nowrap; font-size: 0.82rem;">
                                        {{ number_format($sh->total_shares, 0, ',', '.') }} <span class="text-muted fw-normal" style="font-size: 0.75rem;">Lembar</span>
                                    </td>
                                    <td class="text-end pe-3 py-2" style="white-space: nowrap;">
                                        <span class="badge bg-success-lt text-success fw-bold font-monospace" style="font-size: 0.74rem; padding: 3px 6px;">
                                            {{ number_format($sh->total_percentage, 2) }}%
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

    <!-- 5. TABEL DAFTAR LOG TRANSAKSI & AKTIVITAS MULTI-WEB (LIVE FEED) -->
    @include('components.finance-log-table', [
        'logs' => $recentLogs,
        'webFilter' => $webFilter,
    ])
@endsection

@push('js')
<script>
    // Eye Toggle functionality for Native Android Wallet Card
    function toggleWalletBalance() {
        const textEl = document.getElementById('wallet-balance-text');
        const maskEl = document.getElementById('wallet-balance-masked');
        const eyeOpen = document.getElementById('eye-icon-open');
        const eyeClosed = document.getElementById('eye-icon-closed');

        if (!textEl || !maskEl || !eyeOpen || !eyeClosed) return;

        if (textEl.classList.contains('d-none')) {
            textEl.classList.remove('d-none');
            maskEl.classList.add('d-none');
            eyeOpen.classList.remove('d-none');
            eyeClosed.classList.add('d-none');
        } else {
            textEl.classList.add('d-none');
            maskEl.classList.remove('d-none');
            eyeOpen.classList.add('d-none');
            eyeClosed.classList.remove('d-none');
        }
    }
</script>
@endpush