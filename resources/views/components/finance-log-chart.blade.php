@props([
    'chartData',
    'timeframe' => '7d',
    'webFilter' => 'ALL',
    'title' => 'Grafik Realtime Finansial (Income, Outcome & Keuntungan)',
    'subtitle' => 'Visualisasi terintegrasi mutasi arus kas masuk (Income), arus keluar (Outcome), dan Keuntungan Bersih (Net Profit) konsolidasi usaha.'
])

<div class="card terminal-chart-card mb-4 border shadow-sm">
    <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-3 bg-white">
        <div>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <span class="pulse-live-dot flex-shrink-0"></span>
                <h3 class="card-title fw-bold mb-0 text-dark">{{ $title }}</h3>
                <span class="badge bg-green-lt text-green small font-monospace text-nowrap flex-shrink-0">Live Realtime</span>
            </div>
            <p class="text-muted small mb-0 mt-1">
                {{ $subtitle }}
            </p>
        </div>

        <div class="d-flex flex-wrap align-items-center gap-2">
            <!-- Filter Web Selector -->
            @php
                $clientNames = [
                    'ALL'             => 'Semua Unit Web',
                    'WEB_SLIP_GAJI'   => 'Web Slip Gaji',
                    'CIO_FINANCE'     => 'CIO Finance',
                    'CIO_OPERASIONAL' => 'CIO Operasional',
                    'CIO_INVESTOR'    => 'CIO Investor Portal',
                ];
                $activeLabel = $clientNames[$webFilter] ?? ($webFilter && $webFilter !== 'ALL' ? ucwords(str_replace('_', ' ', $webFilter)) : 'Semua Unit Web');
            @endphp
            <div class="dropdown">
                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="webFilterDropdownBtn" data-bs-toggle="dropdown">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-filter me-1" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 4h16v2.172a2 2 0 0 1 -.586 1.414l-4.414 4.414v7l-6 2v-8.5l-4.48 -4.928a2 2 0 0 1 -.52 -1.345v-2.227z" /></svg>
                    <span id="selected-web-label">{{ $activeLabel }}</span>
                </button>
                <div class="dropdown-menu dropdown-menu-end shadow-md border" id="web-filter-menu">
                    <button class="dropdown-item filter-web-btn {{ empty($webFilter) || $webFilter === 'ALL' ? 'active' : '' }}" data-client="ALL">
                        Semua Unit Web
                    </button>
                    <button class="dropdown-item filter-web-btn {{ $webFilter === 'WEB_SLIP_GAJI' ? 'active' : '' }}" data-client="WEB_SLIP_GAJI">
                        Web Slip Gaji
                    </button>
                    <button class="dropdown-item filter-web-btn {{ $webFilter === 'CIO_FINANCE' ? 'active' : '' }}" data-client="CIO_FINANCE">
                        CIO Finance
                    </button>
                    <button class="dropdown-item filter-web-btn {{ $webFilter === 'CIO_OPERASIONAL' ? 'active' : '' }}" data-client="CIO_OPERASIONAL">
                        CIO Operasional
                    </button>
                    <button class="dropdown-item filter-web-btn {{ $webFilter === 'CIO_INVESTOR' ? 'active' : '' }}" data-client="CIO_INVESTOR">
                        CIO Investor Portal
                    </button>
                </div>
            </div>

            <!-- Timeframe Filter Buttons (1H, 7H, 30H, 90H, YTD, 1TH) -->
            <div class="btn-group" role="group" id="timeframe-btn-group">
                @php
                    $timeframeMap = [
                        '1d'  => '1H',
                        '7d'  => '7H',
                        '30d' => '30H',
                        '90d' => '90H',
                        'ytd' => 'YTD',
                        '1y'  => '1TH'
                    ];
                @endphp
                @foreach($timeframeMap as $tfKey => $tfLabel)
                    <button type="button"
                            class="terminal-timeframe-btn timeframe-select-btn {{ $timeframe === $tfKey ? 'active' : '' }}"
                            data-range="{{ $tfKey }}">
                        {{ $tfLabel }}
                    </button>
                @endforeach
            </div>
        </div>
    </div>

    <div class="card-body position-relative bg-white py-3" style="min-height: 360px;">
        <!-- Shimmering Chart Skeleton Loading Overlay (Fades out when chart is ready) -->
        <div id="chart-skeleton-placeholder" class="p-3" style="position: absolute; inset: 0; background: #ffffff; z-index: 8; transition: opacity 0.3s ease; pointer-events: none;">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="skeleton-shimmer skeleton-pill" style="width: 150px; height: 22px;"></div>
                <div class="d-flex gap-1.5">
                    <div class="skeleton-shimmer skeleton-pill" style="width: 42px; height: 22px;"></div>
                    <div class="skeleton-shimmer skeleton-pill" style="width: 42px; height: 22px;"></div>
                    <div class="skeleton-shimmer skeleton-pill" style="width: 42px; height: 22px;"></div>
                </div>
            </div>
            <div class="skeleton-shimmer skeleton-shimmer-blue" style="width: 100%; height: 270px; border-radius: 12px;"></div>
        </div>

        <div id="finance-multiweb-chart" style="min-height: 340px;"></div>
    </div>

    <!-- Web Breakdown Summary Footnote -->
    <div class="card-footer bg-light-subtle py-2.5 border-top">
        <div class="row text-center align-items-center g-2">
            <div class="col-md-3">
                <span class="text-muted small">Total Income (Pemasukan):</span>
                <strong class="text-success d-block font-monospace fs-5" id="footnote-inflow">
                    +Rp {{ number_format((float) ($chartData['summary']['total_inflow'] ?? 0), 0, ',', '.') }}
                </strong>
            </div>
            <div class="col-md-3">
                <span class="text-muted small">Total Outcome (Pengeluaran):</span>
                <strong class="text-danger d-block font-monospace fs-5" id="footnote-outflow">
                    -Rp {{ number_format((float) ($chartData['summary']['total_outflow'] ?? 0), 0, ',', '.') }}
                </strong>
            </div>
            <div class="col-md-3">
                <span class="text-muted small">Keuntungan Bersih (Net Profit):</span>
                <strong class="text-primary d-block font-monospace fs-5" id="footnote-netprofit">
                    Rp {{ number_format((float) ($chartData['summary']['net_profit'] ?? 0), 0, ',', '.') }}
                </strong>
            </div>
            <div class="col-md-3">
                <span class="text-muted small">Net Profit Margin:</span>
                <strong class="text-dark d-block font-monospace fs-5" id="footnote-margin">
                    {{ number_format((float) ($chartData['summary']['profit_margin_pct'] ?? 0), 1) }}%
                </strong>
            </div>
        </div>
    </div>
</div>

@push('js')
<script>
document.addEventListener("DOMContentLoaded", function() {
    let currentRange = @json($timeframe ?? '7d');
    let currentClient = @json($webFilter ?? 'ALL');

    const initialCategories = @json($chartData['categories'] ?? []);
    const initialSeries = @json($chartData['series'] ?? []);

    const options = {
        chart: {
            type: 'area',
            height: 340,
            fontFamily: 'inherit',
            toolbar: { show: false },
            animations: {
                enabled: true,
                easing: 'easeinout',
                speed: 500,
            }
        },
        dataLabels: { enabled: false },
        stroke: {
            curve: 'smooth',
            width: [2.5, 2.5, 3]
        },
        fill: {
            type: 'gradient',
            gradient: {
                shadeIntensity: 1,
                opacityFrom: 0.35,
                opacityTo: 0.04,
                stops: [0, 90, 100]
            }
        },
        xaxis: {
            categories: initialCategories,
            labels: {
                style: { colors: '#64748b', fontSize: '12px', fontWeight: 500 }
            },
            axisBorder: { show: true, color: '#e2e8f0' },
            axisTicks: { show: false }
        },
        yaxis: {
            labels: {
                formatter: function (value) {
                    return 'Rp ' + new Intl.NumberFormat('id-ID', { maximumFractionDigits: 0 }).format(value);
                },
                style: { colors: '#64748b', fontSize: '12px', fontWeight: 500 }
            }
        },
        colors: ['#10b981', '#ef4444', '#2563eb'],
        series: (initialSeries.length > 0 ? initialSeries : [
            { name: 'Income (Pemasukan)', data: [] },
            { name: 'Outcome (Pengeluaran)', data: [] },
            { name: 'Keuntungan (Net Profit)', data: [] }
        ]).map(function (s) {
            if (s.name.indexOf('Inflow') !== -1 || s.name.indexOf('Pemasukan') !== -1) return Object.assign({}, s, { name: 'Income (Pemasukan)' });
            if (s.name.indexOf('Outflow') !== -1 || s.name.indexOf('Pengeluaran') !== -1) return Object.assign({}, s, { name: 'Outcome (Pengeluaran)' });
            if (s.name.indexOf('Profit') !== -1 || s.name.indexOf('Laba') !== -1 || s.name.indexOf('Keuntungan') !== -1) return Object.assign({}, s, { name: 'Keuntungan (Net Profit)' });
            return s;
        }),
        tooltip: {
            theme: 'light',
            y: {
                formatter: function (value) {
                    return 'Rp ' + new Intl.NumberFormat('id-ID').format(value);
                }
            }
        },
        grid: {
            borderColor: '#f1f5f9',
            strokeDashArray: 4,
        },
        legend: {
            position: 'top',
            horizontalAlign: 'right',
            labels: { colors: '#334155' },
            fontWeight: 600
        }
    };

    const chartEl = document.querySelector("#finance-multiweb-chart");
    if (!chartEl) return;

    const skeletonOverlay = document.getElementById('chart-skeleton-placeholder');

    function hideSkeleton() {
        if (skeletonOverlay) {
            skeletonOverlay.style.opacity = '0';
            setTimeout(() => {
                skeletonOverlay.classList.add('d-none');
            }, 300);
        }
    }

    function showSkeleton() {
        if (skeletonOverlay) {
            skeletonOverlay.classList.remove('d-none');
            skeletonOverlay.style.opacity = '1';
        }
    }

    function initChart() {
        if (typeof ApexCharts === 'undefined') {
            setTimeout(initChart, 50);
            return;
        }
        chartInstance = new ApexCharts(chartEl, options);
        chartInstance.render().then(() => {
            hideSkeleton();
        }).catch(() => {
            hideSkeleton();
        });
        window._activeFinanceChart = chartInstance;
        // Safety fallback to ensure chart is never blocked
        setTimeout(hideSkeleton, 600);
    }

    initChart();

    // Fungsi fetch data grafik secara AJAX dinamis
    function fetchChartData(range, clientCode) {
        showSkeleton();

        const interval = (range === '1d' || range === '24h' || range === 'today' || range === '1h') ? 'hourly' : 'daily';

        const params = new URLSearchParams({
            range: range,
            interval: interval,
            client_code: clientCode
        });

        fetch('{{ route("web.api.finance.chart") }}?' + params.toString(), {
            headers: {
                'Accept': 'application/json'
            }
        })
            .then(res => {
                if (!res.ok) {
                    throw new Error('HTTP error status ' + res.status);
                }
                return res.json();
            })
            .then(res => {
                if (res && res.success && res.data) {
                    const d = res.data;
                    const mappedSeries = (d.series || []).map(function (s) {
                        if (s.name.indexOf('Inflow') !== -1 || s.name.indexOf('Pemasukan') !== -1) return Object.assign({}, s, { name: 'Income (Pemasukan)' });
                        if (s.name.indexOf('Outflow') !== -1 || s.name.indexOf('Pengeluaran') !== -1) return Object.assign({}, s, { name: 'Outcome (Pengeluaran)' });
                        if (s.name.indexOf('Profit') !== -1 || s.name.indexOf('Laba') !== -1 || s.name.indexOf('Keuntungan') !== -1) return Object.assign({}, s, { name: 'Keuntungan (Net Profit)' });
                        return s;
                    });
                    if (chartInstance) {
                        chartInstance.updateOptions({
                            xaxis: { categories: d.categories || [] },
                            series: mappedSeries
                        });
                    }

                    if (d.summary) {
                        const inEl = document.getElementById('footnote-inflow');
                        const outEl = document.getElementById('footnote-outflow');
                        const netEl = document.getElementById('footnote-netprofit');
                        const marginEl = document.getElementById('footnote-margin');

                        if (inEl) inEl.textContent = '+Rp ' + new Intl.NumberFormat('id-ID').format(d.summary.total_inflow || 0);
                        if (outEl) outEl.textContent = '-Rp ' + new Intl.NumberFormat('id-ID').format(d.summary.total_outflow || 0);
                        if (netEl) netEl.textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(d.summary.net_profit || 0);
                        if (marginEl) marginEl.textContent = Number(d.summary.profit_margin_pct || 0).toFixed(1) + '%';
                    }
                }
            })
            .catch(err => console.error('Chart fetch error:', err))
            .finally(() => {
                hideSkeleton();
            });
    }

    // Expose refresh function globally for the header action button
    window.refreshFinanceChart = function() {
        fetchChartData(currentRange, currentClient);
    };

    // Event listener: Selector Periode (1H, 7H, 30H, 90H, YTD, 1TH)
    document.querySelectorAll('.timeframe-select-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            document.querySelectorAll('.timeframe-select-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');

            currentRange = this.getAttribute('data-range');
            fetchChartData(currentRange, currentClient);
        });
    });

    // Event listener: Selector Unit Web
    document.querySelectorAll('.filter-web-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            document.querySelectorAll('.filter-web-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');

            currentClient = this.getAttribute('data-client');
            const labelEl = document.getElementById('selected-web-label');
            if (labelEl) {
                labelEl.textContent = this.textContent.trim();
            }

            fetchChartData(currentRange, currentClient);
        });
    });
});
</script>
@endpush
