@extends('layouts.app')

@section('pretitle', 'LAPORAN KINERJA INVESTASI')
@section('title', 'Laporan Keuntungan Saham')
@section('subtitle', 'Rekapitulasi nominal saham awal dan perolehan keuntungan setelah satu tahun investasi.')

@push('css')
<style>
    /* TomSelect Tabler Integration & Elevation */
    .ts-wrapper.form-select,
    .ts-wrapper.form-control,
    .ts-wrapper {
        padding: 0 !important;
        border: none !important;
        box-shadow: none !important;
        background: transparent !important;
    }
    .ts-wrapper.form-select .ts-control,
    .ts-wrapper.form-control .ts-control,
    .ts-wrapper.single .ts-control,
    .ts-wrapper .ts-control,
    .ts-control {
        border: 1.5px solid #94a3b8 !important;
        border-radius: 8px !important;
        min-height: 44px !important;
        padding: 0.55rem 2.4rem 0.55rem 0.85rem !important;
        display: flex !important;
        align-items: center !important;
        background-color: #ffffff !important;
        font-size: 0.875rem !important;
        color: #1e293b !important;
        box-shadow: 0 1px 2px rgba(10, 37, 64, 0.05) !important;
        transition: all 0.15s ease !important;
        position: relative !important;
    }
    .ts-wrapper:hover .ts-control,
    .ts-wrapper.form-select:hover .ts-control {
        border-color: #475569 !important;
    }
    .ts-wrapper.focus .ts-control,
    .ts-wrapper.input-active .ts-control,
    .ts-wrapper.form-select.focus .ts-control,
    .ts-wrapper.form-select.input-active .ts-control,
    .ts-wrapper.dropdown-active .ts-control {
        border-color: #2563eb !important;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.2) !important;
        background-color: #ffffff !important;
    }
    .ts-wrapper.single .ts-control::after,
    .ts-wrapper.form-select.single .ts-control::after {
        content: "" !important;
        position: absolute !important;
        right: 14px !important;
        top: 50% !important;
        margin-top: -3px !important;
        width: 12px !important;
        height: 7px !important;
        border: none !important;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23475569' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m2 5 6 6 6-6'/%3e%3c/svg%3e") !important;
        background-repeat: no-repeat !important;
        background-size: 12px 7px !important;
        pointer-events: none !important;
        transition: transform 0.2s ease !important;
    }
    .ts-wrapper.single.dropdown-active .ts-control::after,
    .ts-wrapper.form-select.single.dropdown-active .ts-control::after {
        transform: rotate(180deg) !important;
    }
    .ts-dropdown {
        z-index: 1065 !important;
        border-radius: 10px !important;
        border: 1.5px solid #94a3b8 !important;
        box-shadow: 0 10px 25px -3px rgba(10, 37, 64, 0.18), 0 4px 6px -2px rgba(10, 37, 64, 0.08) !important;
        overflow: hidden !important;
        background-color: #ffffff !important;
    }
    .ts-dropdown .option {
        padding: 0.65rem 0.95rem !important;
        font-size: 0.875rem !important;
        border-bottom: 1px solid #f1f5f9;
        transition: background 0.15s ease;
    }
    .ts-dropdown .option:last-child {
        border-bottom: none;
    }
    .ts-dropdown .option.active,
    .ts-dropdown .option:hover {
        background-color: #eff6ff !important;
        color: #1e40af !important;
    }
    .ts-dropdown .option.selected {
        background-color: #dbeafe !important;
        color: #1e40af !important;
        font-weight: 600;
    }
    .ts-wrapper.form-select-sm .ts-control,
    .ts-wrapper.form-select-sm.single .ts-control {
        min-height: 36px !important;
        padding: 0.25rem 2rem 0.25rem 0.65rem !important;
        font-size: 0.8rem !important;
        border: 1.5px solid #94a3b8 !important;
    }

    /* Modal Dialog Width & Sizing */
    @media (min-width: 768px) {
        .modal-dialog.modal-report-lg,
        .modal-dialog.modal-lg {
            max-width: 860px !important;
            margin-right: auto;
            margin-left: auto;
        }
    }

    /* Modal Styling */
    .modal-content.report-modal-content {
        border-radius: 16px !important;
        border: 1px solid #e2e8f0 !important;
        overflow: hidden !important;
        box-shadow: 0 20px 35px -5px rgba(10, 37, 64, 0.18), 0 10px 15px -3px rgba(10, 37, 64, 0.08) !important;
    }
    .report-modal-header {
        background: linear-gradient(135deg, #0a2540 0%, #1e40af 100%) !important;
        color: #ffffff !important;
        padding: 1.25rem 1.75rem !important;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1) !important;
    }
    .report-modal-icon {
        width: 44px;
        height: 44px;
        background: rgba(255, 255, 255, 0.12);
        border: 1px solid rgba(255, 255, 255, 0.25);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #ffffff;
        flex-shrink: 0;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.12);
    }
    .financial-card-input {
        border-radius: 12px;
        padding: 1.15rem;
        transition: all 0.2s ease;
    }
    .financial-card-capital {
        background: #f8faff;
        border: 1.5px solid #93c5fd;
    }
    .financial-card-capital:focus-within {
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
    }
    .financial-card-profit {
        background: #f0fdf4;
        border: 1.5px solid #86efac;
    }
    .financial-card-profit:focus-within {
        border-color: #10b981;
        box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.15);
    }
</style>
@endpush

@section('actions')
    @can('tambah laporan imbal hasil')
        <button type="button" class="btn btn-primary d-inline-flex align-items-center gap-1.5 shadow-sm px-3 py-2" data-bs-toggle="modal" data-bs-target="#modal-add-report">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 5l0 14" /><path d="M5 12l14 0" /></svg>
            <span class="fw-semibold">Tambah Laporan Tahunan</span>
        </button>
    @endcan
@endsection

@section('content')
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm d-flex align-items-center gap-2 mb-4" role="alert">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="icon text-success flex-shrink-0"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l5 5l10 -10" /></svg>
        <div>{{ session('success') }}</div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm d-flex align-items-center gap-2 mb-4" role="alert">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="icon text-danger flex-shrink-0"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 9v4" /><path d="M12 17h.01" /><path d="M12 3c7.2 0 9 1.8 9 9s-1.8 9 -9 9s-9 -1.8 -9 -9s1.8 -9 9 -9z" /></svg>
        <div>
            <ul class="mb-0 ps-3">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<!-- Table Card -->
<div class="card border shadow-sm bg-white">
    <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-3 bg-white py-3">
        <div class="d-flex align-items-center gap-2">
            <span class="pulse-live-dot"></span>
            <h3 class="card-title fw-bold mb-0 text-dark">Daftar Laporan Keuntungan Saham</h3>
            <span class="badge bg-blue-lt fw-bold">Server-Side DataTables</span>
        </div>

        <!-- Search & Filter Controls -->
        <div class="d-flex flex-wrap align-items-center gap-2 m-0">
            @if($isAdmin)
                <div class="input-icon">
                    <span class="input-icon-addon text-muted">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 10m-7 0a7 7 0 1 0 14 0a7 7 0 1 0 -14 0" /><path d="M21 21l-6 -6" /></svg>
                    </span>
                    <input type="text" id="filter_search" class="form-control form-control-sm" placeholder="Cari nama pemegang saham..." style="min-width: 200px;">
                </div>
            @endif

            <div style="min-width: 140px;">
                <select id="filter_year" class="form-select form-select-sm">
                    <option value="">Semua Tahun</option>
                    @foreach($availableYears as $yr)
                        <option value="{{ $yr }}">Tahun {{ $yr }}</option>
                    @endforeach
                </select>
            </div>

            <div style="min-width: 150px;">
                <select id="filter_status" class="form-select form-select-sm">
                    <option value="">Semua Status</option>
                    <option value="distributed">Dibagikan</option>
                    <option value="pending">Tertunda</option>
                    <option value="reinvested">Direinvestasi</option>
                </select>
            </div>

            <button type="button" id="btn-reset-filter" class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-1" title="Reset filter">
                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M20 11a8.1 8.1 0 0 0 -15.5 -2m-.5 -4v4h4" /><path d="M4 13a8.1 8.1 0 0 0 15.5 2m.5 4v-4h-4" /></svg>
                <span>Reset</span>
            </button>
        </div>
    </div>

    <div class="table-responsive p-3">
        <table id="table-investment-reports" class="table table-vcenter card-table table-hover w-100">
            <thead>
                <tr>
                    <th style="min-width: 90px;">Tahun</th>
                    <th style="min-width: 200px;">Pemegang Saham</th>
                    <th class="text-end" style="min-width: 170px;">Nominal Saham Awal</th>
                    <th class="text-end" style="min-width: 170px;">Keuntungan 1 Tahun</th>
                    <th class="text-center" style="min-width: 120px;">Status</th>
                    <th style="min-width: 180px;">Keterangan</th>
                    @if($isAdmin)
                        <th class="text-end" style="min-width: 140px;">Aksi</th>
                    @endif
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>
</div>

@can('tambah laporan imbal hasil')
    <!-- Modal Tambah Laporan Tahunan (Executive Design) -->
    <div class="modal fade" id="modal-add-report" tabindex="-1" aria-labelledby="modalAddReportLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content report-modal-content">
                <form action="{{ route('investment-reports.store') }}" method="POST">
                    @csrf
                    <!-- Premium Header -->
                    <div class="report-modal-header d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-3">
                            <div class="report-modal-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 5l0 14" /><path d="M5 12l14 0" /></svg>
                            </div>
                            <div>
                                <h4 class="modal-title fw-bold text-white mb-0" id="modalAddReportLabel">Tambah Laporan Keuntungan Saham</h4>
                                <p class="text-white-50 small mb-0 mt-0.5">Input manual nominal awal modal saham dan perolehan laba tahunan</p>
                            </div>
                        </div>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body p-4 bg-white">
                        <!-- Section 1: Profil Pemegang Saham & Periode -->
                        <div class="row g-3 mb-4">
                            <div class="col-md-7">
                                <label class="form-label required fw-bold text-dark d-flex align-items-center gap-1.5 mb-1.5">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="#2563eb" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M8 7a4 4 0 1 0 8 0a4 4 0 0 0 -8 0" /><path d="M6 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2" /></svg>
                                    <span>Pilih Pemegang Saham</span>
                                </label>
                                <select name="shareholder_id" class="form-select tom-select" required>
                                    <option value="">-- Cari Nama / Email Pemegang Saham --</option>
                                    @foreach($allShareholders as $sh)
                                        <option value="{{ $sh->id }}" {{ old('shareholder_id') == $sh->id ? 'selected' : '' }}>
                                            {{ $sh->name }} ({{ $sh->email ?: 'ID: #' . $sh->id }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-5">
                                <label class="form-label required fw-bold text-dark d-flex align-items-center gap-1.5 mb-1.5">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="#2563eb" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 7a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v12a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12z" /><path d="M16 3v4" /><path d="M8 3v4" /><path d="M4 11h16" /><path d="M11 15h1" /><path d="M12 15v3" /></svg>
                                    <span>Tahun Investasi</span>
                                </label>
                                <select name="year" class="form-select tom-select font-monospace" required>
                                    <option value="">-- Pilih Periode Tahun --</option>
                                    @php
                                        $currYear = (int) date('Y');
                                    @endphp
                                    @for($y = $currYear; $y >= $currYear - 5; $y--)
                                        <option value="{{ $y }}" {{ old('year', $currYear) == $y ? 'selected' : '' }}>
                                            Tahun {{ $y }}
                                        </option>
                                    @endfor
                                </select>
                            </div>
                        </div>

                        <!-- Section 2: Financial Input Cards (Highlight Blue & Mint) -->
                        <div class="row g-3 mb-4">
                            <!-- Card 1: Nominal Saham Awal -->
                            <div class="col-md-6">
                                <div class="financial-card-input financial-card-capital">
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <label class="form-label required fw-bold text-primary mb-0 d-flex align-items-center gap-1.5">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M17 8v-3a1 1 0 0 0 -1 -1h-10a2 2 0 0 0 0 4h12a1 1 0 0 1 1 1v3m0 4v3a1 1 0 0 1 -1 1h-12a2 2 0 0 1 -2 -2v-12" /><path d="M20 12v4h-4a2 2 0 0 1 0 -4h4" /></svg>
                                            <span>Nominal Saham Awal</span>
                                        </label>
                                        <span class="badge bg-blue text-white px-2 py-0.5 font-monospace" style="font-size: 0.68rem;">Modal Disetor</span>
                                    </div>
                                    <div class="input-group input-group-flat shadow-sm">
                                        <span class="input-group-text bg-white fw-bold font-monospace text-primary border-end-0 px-3">Rp</span>
                                        <input type="text" name="initial_capital" class="form-control form-control-lg font-monospace fw-bold text-dark rupiah-format-input border-start-0 ps-1" placeholder="Misal: 200.000.000" value="{{ old('initial_capital') }}" oninput="formatRupiahLive(this)" required>
                                    </div>
                                    <small class="text-muted d-block mt-2" style="font-size: 0.775rem;">Nominal awal yang disetor di tahun tersebut.</small>
                                </div>
                            </div>

                            <!-- Card 2: Keuntungan 1 Tahun -->
                            <div class="col-md-6">
                                <div class="financial-card-input financial-card-profit">
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <label class="form-label required fw-bold text-success mb-0 d-flex align-items-center gap-1.5">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 17l6 -6l4 4l8 -8" /><path d="M14 7l7 0l0 7" /></svg>
                                            <span>Keuntungan 1 Tahun</span>
                                        </label>
                                        <span class="badge bg-success text-white px-2 py-0.5 font-monospace" style="font-size: 0.68rem;">Imbal Hasil</span>
                                    </div>
                                    <div class="input-group input-group-flat shadow-sm">
                                        <span class="input-group-text bg-white fw-bold font-monospace text-success border-end-0 px-3">+ Rp</span>
                                        <input type="text" name="profit_amount" class="form-control form-control-lg font-monospace fw-bold text-success rupiah-format-input border-start-0 ps-1" placeholder="Misal: 50.000.000" value="{{ old('profit_amount') }}" oninput="formatRupiahLive(this)" required>
                                    </div>
                                    <small class="text-muted d-block mt-2" style="font-size: 0.775rem;">Nilai profit/hasil dividen yang diperoleh.</small>
                                </div>
                            </div>
                        </div>

                        <!-- Section 3: Status & Catatan -->
                        <div class="row g-3">
                            <div class="col-md-5">
                                <label class="form-label fw-bold text-dark d-flex align-items-center gap-1.5 mb-1.5">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="#64748b" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0" /><path d="M9 12l2 2l4 -4" /></svg>
                                    <span>Status Keuntungan</span>
                                </label>
                                <select name="status" class="form-select tom-select">
                                    <option value="distributed" {{ old('status') === 'distributed' ? 'selected' : '' }}>Dibagikan (Distributed)</option>
                                    <option value="pending" {{ old('status') === 'pending' ? 'selected' : '' }}>Tertunda (Pending)</option>
                                    <option value="reinvested" {{ old('status') === 'reinvested' ? 'selected' : '' }}>Direinvestasi (Reinvested)</option>
                                </select>
                            </div>

                            <div class="col-md-7">
                                <label class="form-label fw-bold text-dark d-flex align-items-center gap-1.5 mb-1.5">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="#64748b" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 20h4l10.5 -10.5a2.828 2.828 0 1 0 -4 -4l-10.5 10.5v4" /><path d="M13.5 6.5l4 4" /></svg>
                                    <span>Keterangan / Catatan Tambahan</span>
                                </label>
                                <input type="text" name="notes" class="form-control" placeholder="Contoh: Dividen RUPS Q4, kinerja ekspansi server..." value="{{ old('notes') }}">
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer bg-light-subtle px-4 py-3 border-top d-flex justify-content-between align-items-center">
                        <button type="button" class="btn btn-outline-secondary px-3" data-bs-dismiss="modal">
                            Batal
                        </button>
                        <button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-2 px-4 shadow-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l5 5l10 -10" /></svg>
                            <span class="fw-semibold">Simpan Laporan Tahunan</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Reusable Dynamic Modal Edit Laporan (Executive Design) -->
    <div class="modal fade" id="modal-edit-report" tabindex="-1" aria-labelledby="modalEditReportLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content report-modal-content">
                <form id="form-edit-report" method="POST">
                    @csrf
                    @method('PUT')
                    <!-- Premium Header -->
                    <div class="report-modal-header d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-3">
                            <div class="report-modal-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 20h4l10.5 -10.5a2.828 2.828 0 1 0 -4 -4l-10.5 10.5v4" /><path d="M13.5 6.5l4 4" /></svg>
                            </div>
                            <div>
                                <h4 class="modal-title fw-bold text-white mb-0" id="modalEditReportLabel">
                                    Edit Laporan Keuntungan Saham
                                </h4>
                                <p class="text-white-50 small mb-0 mt-0.5">Perbarui nominal modal disetor atau hasil keuntungan tahunan</p>
                            </div>
                        </div>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body p-4 bg-white">
                        <!-- Section 1: Profil Pemegang Saham & Periode -->
                        <div class="row g-3 mb-4">
                            <div class="col-md-7">
                                <label class="form-label required fw-bold text-dark d-flex align-items-center gap-1.5 mb-1.5">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="#2563eb" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M8 7a4 4 0 1 0 8 0a4 4 0 0 0 -8 0" /><path d="M6 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2" /></svg>
                                    <span>Pemegang Saham</span>
                                </label>
                                <select name="shareholder_id" id="edit_report_shareholder_id" class="form-select" required>
                                    @foreach($allShareholders as $sh)
                                        <option value="{{ $sh->id }}">
                                            {{ $sh->name }} ({{ $sh->email ?: 'ID: #' . $sh->id }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-5">
                                <label class="form-label required fw-bold text-dark d-flex align-items-center gap-1.5 mb-1.5">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="#2563eb" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 7a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v12a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12z" /><path d="M16 3v4" /><path d="M8 3v4" /><path d="M4 11h16" /><path d="M11 15h1" /><path d="M12 15v3" /></svg>
                                    <span>Tahun Investasi</span>
                                </label>
                                <input type="number" name="year" id="edit_report_year" class="form-control font-monospace" min="2000" max="2099" required>
                            </div>
                        </div>

                        <!-- Section 2: Financial Input Cards -->
                        <div class="row g-3 mb-4">
                            <!-- Card 1: Nominal Saham Awal -->
                            <div class="col-md-6">
                                <div class="financial-card-input financial-card-capital">
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <label class="form-label required fw-bold text-primary mb-0 d-flex align-items-center gap-1.5">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M17 8v-3a1 1 0 0 0 -1 -1h-10a2 2 0 0 0 0 4h12a1 1 0 0 1 1 1v3m0 4v3a1 1 0 0 1 -1 1h-12a2 2 0 0 1 -2 -2v-12" /><path d="M20 12v4h-4a2 2 0 0 1 0 -4h4" /></svg>
                                            <span>Nominal Saham Awal</span>
                                        </label>
                                        <span class="badge bg-blue text-white px-2 py-0.5 font-monospace" style="font-size: 0.68rem;">Modal Disetor</span>
                                    </div>
                                    <div class="input-group input-group-flat shadow-sm">
                                        <span class="input-group-text bg-white fw-bold font-monospace text-primary border-end-0 px-3">Rp</span>
                                        <input type="text" name="initial_capital" id="edit_report_capital" class="form-control form-control-lg font-monospace fw-bold text-dark rupiah-format-input border-start-0 ps-1" oninput="formatRupiahLive(this)" required>
                                    </div>
                                    <small class="text-muted d-block mt-2" style="font-size: 0.775rem;">Nominal awal yang disetor di tahun tersebut.</small>
                                </div>
                            </div>

                            <!-- Card 2: Keuntungan 1 Tahun -->
                            <div class="col-md-6">
                                <div class="financial-card-input financial-card-profit">
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <label class="form-label required fw-bold text-success mb-0 d-flex align-items-center gap-1.5">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 17l6 -6l4 4l8 -8" /><path d="M14 7l7 0l0 7" /></svg>
                                            <span>Keuntungan 1 Tahun</span>
                                        </label>
                                        <span class="badge bg-success text-white px-2 py-0.5 font-monospace" style="font-size: 0.68rem;">Imbal Hasil</span>
                                    </div>
                                    <div class="input-group input-group-flat shadow-sm">
                                        <span class="input-group-text bg-white fw-bold font-monospace text-success border-end-0 px-3">+ Rp</span>
                                        <input type="text" name="profit_amount" id="edit_report_profit" class="form-control form-control-lg font-monospace fw-bold text-success rupiah-format-input border-start-0 ps-1" oninput="formatRupiahLive(this)" required>
                                    </div>
                                    <small class="text-muted d-block mt-2" style="font-size: 0.775rem;">Nilai profit/hasil dividen yang diperoleh.</small>
                                </div>
                            </div>
                        </div>

                        <!-- Section 3: Status & Catatan -->
                        <div class="row g-3">
                            <div class="col-md-5">
                                <label class="form-label fw-bold text-dark d-flex align-items-center gap-1.5 mb-1.5">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="#64748b" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0" /><path d="M9 12l2 2l4 -4" /></svg>
                                    <span>Status Keuntungan</span>
                                </label>
                                <select name="status" id="edit_report_status" class="form-select">
                                    <option value="distributed">Dibagikan (Distributed)</option>
                                    <option value="pending">Tertunda (Pending)</option>
                                    <option value="reinvested">Direinvestasi (Reinvested)</option>
                                </select>
                            </div>

                            <div class="col-md-7">
                                <label class="form-label fw-bold text-dark d-flex align-items-center gap-1.5 mb-1.5">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="#64748b" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 20h4l10.5 -10.5a2.828 2.828 0 1 0 -4 -4l-10.5 10.5v4" /><path d="M13.5 6.5l4 4" /></svg>
                                    <span>Keterangan / Catatan Tambahan</span>
                                </label>
                                <input type="text" name="notes" id="edit_report_notes" class="form-control" placeholder="Catatan dividen atau performa bisnis...">
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer bg-light-subtle px-4 py-3 border-top d-flex justify-content-between align-items-center">
                        <button type="button" class="btn btn-outline-secondary px-3" data-bs-dismiss="modal">
                            Batal
                        </button>
                        <button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-2 px-4 shadow-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l5 5l10 -10" /></svg>
                            <span class="fw-semibold">Simpan Perubahan</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endcan

@push('js')
<script src="{{ asset('libs/tom-select/dist/js/tom-select.complete.min.js') }}"></script>
<script>
    function formatRupiahLive(input) {
        if (!input) return;
        let val = input.value;
        let isNegative = val.startsWith('-');
        let cleaned = val.replace(/[^0-9]/g, '');
        if (!cleaned) {
            input.value = '';
            return;
        }
        let formatted = cleaned.replace(/\B(?=(\d{3})+(?!\d))/g, ".");
        input.value = (isNegative ? '-' : '') + formatted;
    }

    document.addEventListener("DOMContentLoaded", function () {
        // Initialize DataTable
        const table = $('#table-investment-reports').DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            ajax: {
                url: "{{ route('investment-reports.index') }}",
                data: function (d) {
                    d.year = $('#filter_year').val();
                    d.status = $('#filter_status').val();
                }
            },
            columns: [
                { data: 'year_badge', name: 'year', className: 'py-2.5' },
                { data: 'shareholder_info', name: 'shareholder.name', className: 'py-2.5' },
                { data: 'initial_capital_formatted', name: 'initial_capital', className: 'text-end py-2.5 font-monospace' },
                { data: 'profit_amount_formatted', name: 'profit_amount', className: 'text-end py-2.5 font-monospace' },
                { data: 'status_badge', name: 'status', className: 'text-center py-2.5' },
                { data: 'notes_formatted', name: 'notes', className: 'py-2.5' },
                @if($isAdmin)
                    { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-end pe-3 py-2.5' },
                @endif
            ],
            order: [[0, 'desc']],
            pageLength: 15,
            lengthMenu: [[10, 15, 25, 50, 100], [10, 15, 25, 50, 100]]
        });

        $('#filter_year, #filter_status').on('change', function () {
            table.draw();
        });

        $('#filter_search').on('keyup', function () {
            table.search(this.value).draw();
        });

        $('#btn-reset-filter').on('click', function () {
            $('#filter_search').val('');
            $('#filter_year').val('');
            $('#filter_status').val('');
            table.search('').draw();
        });

        // Open Dynamic Edit Report Modal
        $(document).on('click', '.btn-edit-report', function () {
            const id = $(this).data('id');
            $.get("{{ url('investment-reports') }}/" + id, function (res) {
                if (res.status === 'success') {
                    const d = res.data;
                    $('#form-edit-report').attr('action', d.update_url);
                    $('#edit_report_shareholder_id').val(d.shareholder_id);
                    $('#edit_report_year').val(d.year);
                    $('#edit_report_capital').val(d.initial_capital);
                    $('#edit_report_profit').val(d.profit_amount);
                    $('#edit_report_status').val(d.status);
                    $('#edit_report_notes').val(d.notes);
                    $('#modal-edit-report').modal('show');
                }
            });
        });

        // Delete Report with SweetAlert2
        $(document).on('click', '.btn-delete-report', function () {
            const id = $(this).data('id');
            const year = $(this).data('year');
            const name = $(this).data('name');

            Swal.fire({
                title: "Konfirmasi Hapus",
                text: "Yakin ingin menghapus data laporan keuntungan tahun " + year + " untuk " + name + "?",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#ef4444",
                cancelButtonColor: "#64748b",
                confirmButtonText: "Ya, Hapus",
                cancelButtonText: "Batal"
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ url('investment-reports') }}/" + id,
                        type: "DELETE",
                        success: function (response) {
                            Swal.fire({
                                icon: "success",
                                title: "Berhasil",
                                text: response.message || "Laporan keuntungan saham berhasil dihapus.",
                                timer: 2000,
                                showConfirmButton: false
                            });
                            table.ajax.reload(null, false);
                        },
                        error: function (xhr) {
                            Swal.fire({
                                icon: "error",
                                title: "Gagal",
                                text: "Terjadi kesalahan saat menghapus data."
                            });
                        }
                    });
                }
            });
        });

        // Initialize visible / modal tom-selects for Add Modal
        document.querySelectorAll('#modal-add-report .tom-select').forEach(function (el) {
            if (!el.tomselect) {
                new TomSelect(el, {
                    create: false,
                    copyClassesToDropdown: false,
                    dropdownParent: 'body'
                });
            }
        });
    });
</script>
@endpush

@endsection
