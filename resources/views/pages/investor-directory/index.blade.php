@extends('layouts.app')

@section('pretitle', 'DIREKTORI PORTOFOLIO')
@section('title', 'Portofolio Pemegang Saham')
@section('subtitle', 'Daftar dan penelusuran portofolio kepemilikan saham seluruh investor terdaftar di CIO Network.')

@section('content')
<!-- Summary Cards (Corporate Blue & White with SVG Icons) -->
<div class="row row-cards mb-4">
    <!-- Card 1: Total Pemegang Saham -->
    <div class="col-sm-6 col-lg-3">
        <div class="finance-stat-card">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <div class="overflow-hidden pe-2">
                    <span class="text-muted small fw-bold text-uppercase tracking-wider" style="font-size: 0.72rem;">Total Pemegang Saham</span>
                    <h3 class="h2 mb-0 fw-bold mt-1 text-primary font-monospace">{{ $equitySummary['total_shareholders'] ?? 0 }} <span class="fs-4 fw-medium text-muted">Orang</span></h3>
                </div>
                <div class="finance-stat-icon" style="background-color: var(--saham-blue-ice, #e0f2fe); color: var(--saham-blue-royal, #0284c7);">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9 7m-4 0a4 4 0 1 0 8 0a4 4 0 1 0 -8 0" /><path d="M3 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2" /><path d="M16 3.13a4 4 0 0 1 0 7.75" /><path d="M21 21v-2a4 4 0 0 0 -3 -3.85" /></svg>
                </div>
            </div>
            <div class="d-flex align-items-center gap-1.5 mt-2 pt-2 border-top border-light-subtle small">
                <span class="badge bg-blue-lt fw-bold font-monospace">Terdaftar</span>
                <span class="text-muted text-truncate" style="font-size: 0.775rem;">Investor Terverifikasi</span>
            </div>
        </div>
    </div>

    <!-- Card 2: Total Portofolio Saham -->
    <div class="col-sm-6 col-lg-3">
        <div class="finance-stat-card">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <div class="overflow-hidden pe-2">
                    <span class="text-muted small fw-bold text-uppercase tracking-wider" style="font-size: 0.72rem;">Total Portofolio Instrumen</span>
                    <h3 class="h2 mb-0 fw-bold mt-1 text-dark font-monospace">{{ $equitySummary['total_holdings'] ?? 0 }} <span class="fs-4 fw-medium text-muted">Instrumen</span></h3>
                </div>
                <div class="finance-stat-icon" style="background-color: #f0f9ff; color: #0284c7;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 7m0 2a2 2 0 0 1 2 -2h14a2 2 0 0 1 2 2v9a2 2 0 0 1 -2 2h-14a2 2 0 0 1 -2 -2z" /><path d="M8 7v-2a2 2 0 0 1 2 -2h4a2 2 0 0 1 2 2v2" /><path d="M12 12l0 .01" /><path d="M3 13a20 20 0 0 0 18 0" /></svg>
                </div>
            </div>
            <div class="d-flex align-items-center gap-1.5 mt-2 pt-2 border-top border-light-subtle small">
                <span class="badge bg-azure-lt fw-bold font-monospace">Multi-Saham</span>
                <span class="text-muted text-truncate" style="font-size: 0.775rem;">Alokasi instrumen aktif</span>
            </div>
        </div>
    </div>

    <!-- Card 3: Total Lembar Saham -->
    <div class="col-sm-6 col-lg-3">
        <div class="finance-stat-card">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <div class="overflow-hidden pe-2">
                    <span class="text-muted small fw-bold text-uppercase tracking-wider" style="font-size: 0.72rem;">Total Lembar Saham</span>
                    <h3 class="h2 mb-0 fw-bold mt-1 text-info font-monospace">{{ number_format($equitySummary['total_shares'] ?? 0, 0, ',', '.') }} <span class="fs-4 fw-medium text-muted">Lembar</span></h3>
                </div>
                <div class="finance-stat-icon" style="background-color: #ede9fe; color: #7c3aed;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M15 15m-3 0a3 3 0 1 0 6 0a3 3 0 1 0 -6 0" /><path d="M13 17.5v4.5l2 -1.5l2 1.5v-4.5" /><path d="M10 19h-5a2 2 0 0 1 -2 -2v-10a2 2 0 0 1 2 -2h14a2 2 0 0 1 2 2v10a2 2 0 0 1 -1 1.73" /><path d="M6 9l12 0" /><path d="M6 12l3 0" /><path d="M6 15l2 0" /></svg>
                </div>
            </div>
            <div class="d-flex align-items-center gap-1.5 mt-2 pt-2 border-top border-light-subtle small">
                <span class="badge bg-purple-lt fw-bold font-monospace">Sirkulasi</span>
                <span class="text-muted text-truncate" style="font-size: 0.775rem;">Seluruh kepemilikan saham</span>
            </div>
        </div>
    </div>

    <!-- Card 4: Total Modal Disetor -->
    <div class="col-sm-6 col-lg-3">
        <div class="finance-stat-card">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <div class="overflow-hidden pe-2">
                    <span class="text-muted small fw-bold text-uppercase tracking-wider" style="font-size: 0.72rem;">Total Modal Disetor</span>
                    <h3 class="h2 mb-0 fw-bold mt-1 text-success font-monospace" style="white-space: nowrap;">Rp {{ number_format($equitySummary['total_investment'] ?? 0, 0, ',', '.') }}</h3>
                </div>
                <div class="finance-stat-icon" style="background-color: var(--saham-bullish-bg, #ecfdf5); color: var(--saham-bullish, #059669);">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M17 8v-3a1 1 0 0 0 -1 -1h-10a2 2 0 0 0 0 4h12a1 1 0 0 1 1 1v3m0 4v3a1 1 0 0 1 -1 1h-12a2 2 0 0 1 -2 -2v-12" /><path d="M20 12v4h-4a2 2 0 0 1 0 -4h4" /></svg>
                </div>
            </div>
            <div class="d-flex align-items-center gap-1.5 mt-2 pt-2 border-top border-light-subtle small">
                <span class="badge bg-success-lt fw-bold font-monospace">Ekuitas Aktif</span>
                <span class="text-muted text-truncate" style="font-size: 0.775rem;">Total modal terhimpun</span>
            </div>
        </div>
    </div>
</div>

<!-- Table Card -->
<div class="card border shadow-sm bg-white">
    <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-3 bg-white py-3">
        <div class="d-flex align-items-center gap-2">
            <span class="pulse-live-dot"></span>
            <h3 class="card-title fw-bold mb-0 text-dark">Direktori Pemegang Saham</h3>
            <span class="badge bg-blue-lt fw-bold">Server-Side DataTables</span>
        </div>

        <!-- Search & Filter Controls -->
        <div class="d-flex flex-wrap align-items-center gap-2 m-0">
            <div class="input-icon">
                <span class="input-icon-addon text-muted">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 10m-7 0a7 7 0 1 0 14 0a7 7 0 1 0 -14 0" /><path d="M21 21l-6 -6" /></svg>
                </span>
                <input type="text" id="filter_search" class="form-control form-control-sm" placeholder="Cari nama, email, kode saham..." style="min-width: 220px;">
            </div>
            <select id="filter_status" class="form-select form-select-sm" style="width: auto; min-width: 140px;">
                <option value="">Semua Status</option>
                <option value="active">Aktif</option>
                <option value="inactive">Non-Aktif</option>
            </select>
            <button type="button" id="btn-reset-filter" class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-1" title="Reset filter">
                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M20 11a8.1 8.1 0 0 0 -15.5 -2m-.5 -4v4h4" /><path d="M4 13a8.1 8.1 0 0 0 15.5 2m.5 4v-4h-4" /></svg>
                <span>Reset</span>
            </button>
        </div>
    </div>

    <div class="table-responsive p-3">
        <table id="table-investor-directory" class="table table-vcenter card-table table-hover shareholder-table w-100">
            <thead>
                <tr>
                    <th style="min-width: 240px;">Pemegang Saham</th>
                    <th class="text-center" style="min-width: 140px;">Instrumen Saham</th>
                    <th class="text-end" style="min-width: 150px;">Total Lembar</th>
                    <th class="text-end" style="min-width: 170px;">Total Investasi</th>
                    <th class="text-center" style="min-width: 130px;">Porsi Kepemilikan (%)</th>
                    <th class="text-center" style="min-width: 110px;">Status</th>
                    <th class="text-end" style="min-width: 160px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('js')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const table = $('#table-investor-directory').DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            ajax: {
                url: "{{ route('investor-directory.index') }}",
                data: function (d) {
                    d.status = $('#filter_status').val();
                }
            },
            columns: [
                { data: 'shareholder_info', name: 'name' },
                { data: 'holdings_badge', name: 'holdings_count', orderable: false, searchable: false, className: 'text-center' },
                { data: 'total_shares_formatted', name: 'total_shares', searchable: false, className: 'text-end' },
                { data: 'total_investment_formatted', name: 'total_investment', searchable: false, className: 'text-end' },
                { data: 'total_percentage_formatted', name: 'total_percentage', searchable: false, className: 'text-center' },
                { data: 'status_badge', name: 'status', className: 'text-center' },
                { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-end' },
            ],
            order: [[0, 'asc']],
            pageLength: 10,
            lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]]
        });

        $('#filter_status').on('change', function () {
            table.draw();
        });

        $('#filter_search').on('keyup', function () {
            table.search(this.value).draw();
        });

        $('#btn-reset-filter').on('click', function () {
            $('#filter_search').val('');
            $('#filter_status').val('');
            table.search('').draw();
        });
    });
</script>
@endpush
