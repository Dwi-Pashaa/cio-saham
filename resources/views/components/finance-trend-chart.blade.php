@props([
    'chartData' => [
        'range' => '30d',
        'categories' => [],
        'series' => [],
        'summary' => [
            'total_income' => 0,
            'total_outcome' => 0,
            'total_asset' => 0,
        ],
    ],
    'title' => 'Grafik Tren Keuangan & Aset Perusahaan',
    'subtitle' => 'Perbandingan pemasukan, pengeluaran, dan perolehan aset berdasarkan periode.'
])

@php
    $activeRange = $chartData['range'] ?? '30d';
    $ranges = [
        '7d'         => '7 Hari',
        '30d'        => '30 Hari',
        'this_month' => 'Bulan Ini',
        'year'       => 'Tahun Ini',
    ];
    $kpis = [
        ['key' => 'income',  'idx' => 0, 'label' => 'Pemasukan',   'color' => '#10b981', 'soft' => 'rgba(16,185,129,.08)', 'value' => $chartData['summary']['total_income'] ?? 0],
        ['key' => 'outcome', 'idx' => 1, 'label' => 'Pengeluaran', 'color' => '#ef4444', 'soft' => 'rgba(239,68,68,.08)',  'value' => $chartData['summary']['total_outcome'] ?? 0],
        ['key' => 'asset',   'idx' => 2, 'label' => 'Asset',       'color' => '#206bc4', 'soft' => 'rgba(32,107,196,.08)', 'value' => $chartData['summary']['total_asset'] ?? 0],
    ];
@endphp

@push('css')
<style>
    .ftc-card { border: 1px solid #e6e9ef; border-radius: 14px; background: #fff; overflow: hidden; }
    .ftc-header { padding: 18px 22px 14px; display: flex; flex-wrap: wrap; align-items: flex-start; justify-content: space-between; gap: 14px; }
    .ftc-title { font-size: 1rem; font-weight: 700; color: #0f172a; margin: 0; letter-spacing: -.01em; }
    .ftc-subtitle { font-size: .78rem; color: #64748b; margin: 4px 0 0; }
    .ftc-segment { display: inline-flex; background: #f1f5f9; border-radius: 10px; padding: 3px; gap: 2px; }
    .ftc-segment button { border: 0; background: transparent; color: #475569; font-size: .76rem; font-weight: 600; padding: 6px 12px; border-radius: 8px; line-height: 1.2; transition: all .18s ease; white-space: nowrap; }
    .ftc-segment button:hover { color: #0f172a; }
    .ftc-segment button.active { background: #fff; color: #0f172a; box-shadow: 0 1px 2px rgba(15,23,42,.08), 0 1px 1px rgba(15,23,42,.04); }

    .ftc-kpis { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 12px; padding: 0 22px 6px; }
    .ftc-kpi { position: relative; display: flex; flex-direction: column; gap: 4px; text-align: left; border: 1px solid #eef1f5; border-radius: 12px; padding: 12px 14px 12px 16px; background: #fff; cursor: pointer; transition: all .18s ease; overflow: hidden; }
    .ftc-kpi::before { content: ''; position: absolute; left: 0; top: 10px; bottom: 10px; width: 3px; border-radius: 0 3px 3px 0; background: var(--kpi-color); }
    .ftc-kpi:hover { background: var(--kpi-soft); border-color: transparent; }
    .ftc-kpi.is-off { opacity: .45; }
    .ftc-kpi.is-off .ftc-kpi-value { text-decoration: line-through; }
    .ftc-kpi-label { display: flex; align-items: center; gap: 8px; font-size: .72rem; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: .05em; }
    .ftc-kpi-dot { width: 8px; height: 8px; border-radius: 50%; background: var(--kpi-color); box-shadow: 0 0 0 3px var(--kpi-soft); flex-shrink: 0; }
    .ftc-kpi-value { font-size: 1.15rem; font-weight: 700; color: #0f172a; font-variant-numeric: tabular-nums; letter-spacing: -.01em; }
    .ftc-kpi-hint { font-size: .68rem; color: #94a3b8; }

    .ftc-body { position: relative; padding: 6px 12px 8px 6px; }
    .ftc-loading { position: absolute; inset: 0; background: rgba(255,255,255,.7); display: none; align-items: center; justify-content: center; gap: 8px; z-index: 5; font-size: .78rem; font-weight: 600; color: #475569; }
    .ftc-loading.show { display: flex; }
    .ftc-empty { position: absolute; inset: 0; display: none; align-items: center; justify-content: center; flex-direction: column; color: #94a3b8; font-size: .8rem; pointer-events: none; }
    .ftc-empty.show { display: flex; }

    .ftc-footer { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 8px; padding: 10px 22px; border-top: 1px solid #f1f5f9; background: #fafbfc; font-size: .72rem; color: #94a3b8; }

    #finance-trend-apexchart .apexcharts-tooltip { border: 1px solid #e2e8f0 !important; box-shadow: 0 8px 24px rgba(15,23,42,.08) !important; border-radius: 10px !important; }
    #finance-trend-apexchart .apexcharts-tooltip-title { background: #f8fafc !important; border-bottom: 1px solid #eef1f5 !important; font-weight: 600 !important; font-size: 12px !important; }

    @media (max-width: 767.98px) {
        .ftc-kpis { grid-template-columns: 1fr; }
        .ftc-header { padding: 16px; }
        .ftc-kpis { padding: 0 16px 6px; }
        .ftc-footer { padding: 10px 16px; }
    }
</style>
@endpush

<div class="ftc-card shadow-sm mt-3">
    {{-- Header --}}
    <div class="ftc-header">
        <div>
            <h3 class="ftc-title">{{ $title }}</h3>
            <p class="ftc-subtitle">{{ $subtitle }}</p>
        </div>
        <div class="ftc-segment" role="group" aria-label="Rentang waktu" id="finance-chart-range-group">
            @foreach($ranges as $rKey => $rLabel)
                <button type="button" class="btn-range-filter {{ $activeRange === $rKey ? 'active' : '' }}" data-range="{{ $rKey }}" id="ftc-range-{{ $rKey }}">
                    {{ $rLabel }}
                </button>
            @endforeach
        </div>
    </div>

    {{-- KPI tiles (juga berfungsi sebagai legend interaktif) --}}
    <div class="ftc-kpis">
        @foreach($kpis as $kpi)
            <button type="button" class="ftc-kpi" data-series-idx="{{ $kpi['idx'] }}" data-series-name="{{ $kpi['label'] }}"
                    id="ftc-kpi-{{ $kpi['key'] }}" style="--kpi-color: {{ $kpi['color'] }}; --kpi-soft: {{ $kpi['soft'] }};">
                <span class="ftc-kpi-label"><span class="ftc-kpi-dot"></span>{{ $kpi['label'] }}</span>
                <span class="ftc-kpi-value" id="chart-sum-{{ $kpi['key'] }}">Rp {{ number_format((float) $kpi['value'], 0, ',', '.') }}</span>
            </button>
        @endforeach
    </div>

    {{-- Chart --}}
    <div class="ftc-body">
        <div class="ftc-loading" id="chart-loading-overlay">
            <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
            Memperbarui grafik...
        </div>
        <div class="ftc-empty" id="chart-empty-state">
            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M4 19l16 0" /><path d="M4 15l4 -6l4 2l4 -5l4 4" /></svg>
            <span class="mt-1">Belum ada transaksi pada periode ini</span>
        </div>
        <div id="finance-trend-apexchart" style="min-height: 320px;"></div>
    </div>

    <div class="ftc-footer">
        <span>Klik kartu metrik untuk menampilkan / menyembunyikan garis.</span>
        <span id="ftc-period-label">Periode: {{ $ranges[$activeRange] ?? '30 Hari' }}</span>
    </div>
</div>

@push('js')
<script>
(function () {
    let chart = null;
    const rangeLabels = @json($ranges);
    const initialCategories = @json($chartData['categories'] ?? []);
    const initialSeries = @json($chartData['series'] ?? []);

    const idr = new Intl.NumberFormat('id-ID');
    const formatRupiah = (n) => 'Rp ' + idr.format(Math.round(n || 0));
    const formatCompact = (v) => {
        const a = Math.abs(v);
        if (a >= 1e9) return 'Rp ' + (v / 1e9).toLocaleString('id-ID', { maximumFractionDigits: 1 }) + ' M';
        if (a >= 1e6) return 'Rp ' + (v / 1e6).toLocaleString('id-ID', { maximumFractionDigits: 1 }) + ' jt';
        if (a >= 1e3) return 'Rp ' + (v / 1e3).toLocaleString('id-ID', { maximumFractionDigits: 0 }) + ' rb';
        return 'Rp ' + idr.format(v);
    };

    const isEmpty = (series) => !series.some(s => (s.data || []).some(v => Number(v) > 0));
    const toggleEmpty = (series) => {
        const el = document.getElementById('chart-empty-state');
        if (el) el.classList.toggle('show', isEmpty(series));
    };

    const baseSeries = initialSeries.length ? initialSeries : [
        { name: 'Pemasukan', data: [] }, { name: 'Pengeluaran', data: [] }, { name: 'Asset', data: [] }
    ];

    const options = {
        chart: {
            type: 'area',
            height: 320,
            fontFamily: 'inherit',
            toolbar: { show: false },
            zoom: { enabled: false },
            animations: { enabled: true, easing: 'easeinout', speed: 400 },
            parentHeightOffset: 0,
        },
        series: baseSeries.map(s => ({ name: s.name, data: s.data })),
        colors: ['#10b981', '#ef4444', '#206bc4'],
        stroke: { curve: 'monotoneCubic', width: 2.25, lineCap: 'round' },
        fill: {
            type: 'gradient',
            gradient: { shadeIntensity: 1, opacityFrom: 0.18, opacityTo: 0, stops: [0, 95, 100] }
        },
        dataLabels: { enabled: false },
        markers: { size: 0, strokeWidth: 2, strokeColors: '#fff', hover: { size: 5 } },
        legend: { show: false },
        grid: {
            borderColor: '#eef1f5',
            strokeDashArray: 3,
            xaxis: { lines: { show: false } },
            yaxis: { lines: { show: true } },
            padding: { left: 8, right: 12, top: 0, bottom: 0 }
        },
        xaxis: {
            categories: initialCategories,
            tickPlacement: 'on',
            tickAmount: 8,
            labels: {
                rotate: 0,
                hideOverlappingLabels: true,
                trim: false,
                style: { colors: '#94a3b8', fontSize: '11px', fontWeight: 500 }
            },
            axisBorder: { show: false },
            axisTicks: { show: false },
            crosshairs: { stroke: { color: '#cbd5e1', width: 1, dashArray: 4 } },
            tooltip: { enabled: false }
        },
        yaxis: {
            min: 0,
            forceNiceScale: true,
            tickAmount: 4,
            labels: {
                formatter: formatCompact,
                style: { colors: '#94a3b8', fontSize: '11px', fontWeight: 500 },
                offsetX: -4
            }
        },
        tooltip: {
            theme: 'light',
            shared: true,
            intersect: false,
            marker: { show: true },
            y: { formatter: (v) => formatRupiah(v) }
        }
    };

    function render() {
        const el = document.querySelector('#finance-trend-apexchart');
        if (!el) return;
        if (typeof window.ApexCharts === 'undefined') { setTimeout(render, 50); return; }
        chart = new ApexCharts(el, options);
        chart.render();
        toggleEmpty(baseSeries);
    }

    document.addEventListener('DOMContentLoaded', function () {
        render();

        // KPI tiles sebagai legend interaktif
        document.querySelectorAll('.ftc-kpi').forEach(tile => {
            tile.addEventListener('click', function () {
                if (!chart) return;
                chart.toggleSeries(this.dataset.seriesName);
                this.classList.toggle('is-off');
            });
        });

        // Filter rentang waktu
        const buttons = document.querySelectorAll('.btn-range-filter');
        const loading = document.getElementById('chart-loading-overlay');
        const periodLabel = document.getElementById('ftc-period-label');

        buttons.forEach(btn => {
            btn.addEventListener('click', function () {
                if (this.classList.contains('active')) return;
                const range = this.dataset.range;
                buttons.forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                loading && loading.classList.add('show');

                fetch(`{{ route('dashboard.chart-data') }}?range=${encodeURIComponent(range)}`, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(r => r.json())
                .then(data => {
                    if (chart && data.series) {
                        chart.updateOptions({
                            xaxis: { categories: data.categories, tickAmount: range === 'year' ? 12 : 8 },
                            series: data.series.map(s => ({ name: s.name, data: s.data }))
                        }, true, true);
                        // pertahankan status garis yang disembunyikan
                        document.querySelectorAll('.ftc-kpi.is-off').forEach(t => chart.hideSeries(t.dataset.seriesName));
                        toggleEmpty(data.series);
                    }
                    if (data.summary) {
                        document.getElementById('chart-sum-income').textContent  = formatRupiah(data.summary.total_income);
                        document.getElementById('chart-sum-outcome').textContent = formatRupiah(data.summary.total_outcome);
                        document.getElementById('chart-sum-asset').textContent   = formatRupiah(data.summary.total_asset);
                    }
                    if (periodLabel) periodLabel.textContent = 'Periode: ' + (rangeLabels[range] || range);
                })
                .catch(err => console.error('Gagal memuat data grafik:', err))
                .finally(() => loading && loading.classList.remove('show'));
            });
        });
    });
})();
</script>
@endpush
