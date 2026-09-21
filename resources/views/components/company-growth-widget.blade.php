@props(['growth'])

@php
    $healthScore = (int) ($growth['financial_health_score'] ?? ($growth['health_score'] ?? 85));
    $growthMoM = (float) ($growth['growth_rate_mom'] ?? ($growth['revenue_growth_rate'] ?? 0));
    $growthYoY = (float) ($growth['growth_rate_yoy'] ?? 0);
    $statusText = $growth['status'] ?? ($growth['status_label'] ?? 'Healthy & Expanding');
    $netMargin = (float) ($growth['profitability']['net_margin'] ?? ($growth['profit_margin'] ?? 0));
    $burnRate = (float) ($growth['profitability']['monthly_burn_rate'] ?? 0);
    $runway = (float) ($growth['profitability']['runway_months'] ?? 12.0);
    $valuation = (float) ($growth['estimated_company_valuation'] ?? ($growth['current_valuation'] ?? 180000000));

    $healthBadgeClass = match(true) {
        $healthScore >= 80 => 'bg-success',
        $healthScore >= 60 => 'bg-warning',
        default            => 'bg-danger',
    };
@endphp

<div class="growth-status-banner mb-4">
    <div class="row align-items-center">
        <div class="col-lg-8">
            <div class="d-flex align-items-center gap-2 mb-2">
                <span class="pulse-live-dot"></span>
                <span class="text-uppercase tracking-wider fw-bold small text-primary">
                    Indikator Pertumbuhan Usaha & Kesehatan Finansial (Live CIO Finance)
                </span>
            </div>
            <h2 class="h1 mb-2 fw-bolder text-dark d-flex align-items-center gap-2">
                <span>🚀</span>
                <span>{{ $statusText }}</span>
            </h2>
            <p class="mb-3 text-secondary" style="max-width: 680px; font-size: 0.925rem;">
                Performa finansial dihitung secara otomatis dari mutasi seluruh unit web client dan saluran pembayaran gateway Xendit yang terhubung.
            </p>

            <div class="d-flex flex-wrap gap-2 align-items-center">
                <!-- Box 1: Growth MoM -->
                <div class="growth-metric-box">
                    <span class="metric-label">Growth MoM:</span>
                    <strong class="metric-value {{ $growthMoM >= 0 ? 'text-success' : 'text-danger' }}">
                        {{ $growthMoM >= 0 ? '+' : '' }}{{ number_format($growthMoM, 1) }}%
                    </strong>
                    @if($growthMoM >= 0)
                        <span class="badge bg-success-lt fw-bold">▲ Naik</span>
                    @else
                        <span class="badge bg-danger-lt fw-bold">▼ Turun</span>
                    @endif
                </div>

                <!-- Box 2: Growth YoY -->
                @if($growthYoY != 0)
                <div class="growth-metric-box">
                    <span class="metric-label">Growth YoY:</span>
                    <strong class="metric-value text-primary">
                        {{ $growthYoY >= 0 ? '+' : '' }}{{ number_format($growthYoY, 1) }}%
                    </strong>
                </div>
                @endif

                <!-- Box 3: Margin Laba Bersih -->
                <div class="growth-metric-box">
                    <span class="metric-label">Net Profit Margin:</span>
                    <strong class="metric-value text-dark">{{ number_format($netMargin, 1) }}%</strong>
                </div>

                <!-- Box 4: Runway Kas -->
                <div class="growth-metric-box">
                    <span class="metric-label">Kas Runway:</span>
                    <strong class="metric-value text-primary">{{ number_format($runway, 1) }} Bulan</strong>
                </div>
            </div>
        </div>

        <div class="col-lg-4 text-lg-end mt-4 mt-lg-0">
            <div class="growth-side-card text-start d-inline-block" style="min-width: 290px;">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="label-muted">Financial Health Score</span>
                    <span class="badge {{ $healthBadgeClass }} text-white fw-bold px-2 py-1">{{ $healthScore }}/100</span>
                </div>
                <div class="progress progress-sm mb-3" style="background-color: #e2e8f0; height: 6px;">
                    <div class="progress-bar {{ $healthBadgeClass }}" style="width: {{ $healthScore }}%" role="progressbar"></div>
                </div>
                <div class="d-flex justify-content-between label-muted mb-2">
                    <span>Estimasi Valuasi Usaha:</span>
                    <strong class="value-highlight text-primary">Rp {{ number_format($valuation, 0, ',', '.') }}</strong>
                </div>
                @if($burnRate > 0)
                <div class="d-flex justify-content-between label-muted mb-2">
                    <span>Monthly Burn Rate:</span>
                    <span class="text-warning fw-bold font-monospace">Rp {{ number_format($burnRate, 0, ',', '.') }}/bln</span>
                </div>
                @endif
                <div class="d-flex justify-content-between label-muted">
                    <span>Status Jaringan:</span>
                    <span class="badge bg-success-lt fw-bold">
                        <span class="pulse-live-dot me-1" style="width: 7px; height: 7px;"></span> Terhubung Realtime
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>
