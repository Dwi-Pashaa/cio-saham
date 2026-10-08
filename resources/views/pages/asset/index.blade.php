@extends('layouts.app')

@section('pretitle', 'INVENTARIS & ASET')
@section('title', 'Manajemen Aset')
@section('subtitle', 'Pencatatan dan monitoring inventaris perangkat serta aset operasional PT dan investor.')

@section('actions')
    <a href="{{ route('assets.export') }}" id="btn-export-asset-excel" class="btn btn-outline-primary btn-sm d-inline-flex align-items-center gap-1.5 shadow-sm px-3 py-2 rounded-2" title="Unduh inventaris aset ke Excel">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M14 3v4a1 1 0 0 0 1 1h4" /><path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z" /><path d="M10 12l4 4m0 -4l-4 4" /></svg>
        <span class="fw-semibold">Download Excel</span>
    </a>
    @can('tambah aset')
        <button type="button" class="btn btn-primary btn-sm d-inline-flex align-items-center gap-1.5 shadow-sm px-3 py-2 rounded-2" data-bs-toggle="modal" data-bs-target="#modalAddAsset">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="icon"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 5l0 14" /><path d="M5 12l14 0" /></svg>
            <span class="fw-semibold">Tambah Aset</span>
        </button>
    @endcan
@endsection

@push('css')
<style>
.btn-outline-purple {
    color: #7e22ce;
    border-color: #d8b4fe;
    background-color: #faf5ff;
}
.btn-outline-purple:hover {
    color: #ffffff;
    background-color: #7e22ce;
    border-color: #7e22ce;
}
.gallery-card {
    transition: transform 0.18s ease, box-shadow 0.18s ease;
    border-radius: 10px;
    overflow: hidden;
}
.gallery-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
}
#modalPhotoPreview {
    z-index: 1070 !important;
}
</style>
@endpush

@section('content')
<div class="container-xl py-2">

    @include('components.alert.success')

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show shadow-sm mb-3" role="alert">
            <div class="d-flex align-items-center">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon me-2 text-danger"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 9v4" /><path d="M12 17h.01" /><path d="M5 19h14a2 2 0 0 0 1.84 -2.75l-7.1 -12.25a2 2 0 0 0 -3.5 0l-7.1 12.25a2 2 0 0 0 1.75 2.75" /></svg>
                <div>
                    <strong>Terdapat kesalahan pengisian data:</strong>
                    <ul class="mb-0 mt-1 ps-3 small">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- 1. Summary Cards Grid (4 Metrik Utama) -->
    <div class="row g-3 mb-3">
        <!-- Total Unit Aset -->
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card h-100 p-3 border-0 shadow-sm bg-white" style="border-radius: 12px; border-left: 4px solid #0ea5e9 !important;">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="text-muted fw-bold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.05em;">Total Unit Aset</span>
                    <span class="avatar bg-azure-lt text-azure rounded-circle" style="width: 36px; height: 36px;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 3l8 4.5l0 9l-8 4.5l-8 -4.5l0 -9l8 -4.5" /><path d="M12 12l8 -4.5" /><path d="M12 12l0 9" /><path d="M12 12l-8 -4.5" /></svg>
                    </span>
                </div>
                <div class="h2 fw-bold text-dark font-monospace mb-1" id="stat-total-count" style="letter-spacing: -0.02em;">
                    {{ $totalCount }} <span class="fs-4 text-muted fw-normal">Unit</span>
                </div>
                <div class="text-muted small" style="font-size: 0.75rem;">Total inventaris terdata</div>
            </div>
        </div>

        <!-- Total Nilai Aset (Valuasi) -->
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card h-100 p-3 border-0 shadow-sm bg-white" style="border-radius: 12px; border-left: 4px solid #16a34a !important;">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="text-muted fw-bold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.05em;">Total Nilai Aset</span>
                    <span class="avatar bg-green-lt text-success rounded-circle" style="width: 36px; height: 36px;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M17 8v-3a1 1 0 0 0 -1 -1h-10a2 2 0 0 0 0 4h12a1 1 0 0 1 1 1v3m0 4v3a1 1 0 0 1 -1 1h-12a2 2 0 0 1 -2 -2v-12" /><path d="M20 12v4h-4a2 2 0 0 1 0 -4h4" /></svg>
                    </span>
                </div>
                <div class="h2 fw-bold text-success font-monospace mb-1" id="stat-total-price" style="letter-spacing: -0.02em;">
                    Rp {{ number_format($totalPriceSum, 0, ',', '.') }}
                </div>
                <div class="text-muted small" style="font-size: 0.75rem;">Total valuasi keseluruhan</div>
            </div>
        </div>

        <!-- Nilai Aset Milik PT -->
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card h-100 p-3 border-0 shadow-sm bg-white" style="border-radius: 12px; border-left: 4px solid #2563eb !important;">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="text-muted fw-bold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.05em;">Aset Milik PT</span>
                    <span class="avatar bg-blue-lt text-primary rounded-circle" style="width: 36px; height: 36px;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 21l18 0" /><path d="M5 21v-14l8 -4v18" /><path d="M19 21v-10l-6 -4" /></svg>
                    </span>
                </div>
                <div class="h2 fw-bold text-primary font-monospace mb-1" id="stat-pt-price" style="letter-spacing: -0.02em;">
                    Rp {{ number_format($ptPriceSum, 0, ',', '.') }}
                </div>
                <div class="text-muted small" style="font-size: 0.75rem;">Milik PT CIO Network Solution</div>
            </div>
        </div>

        <!-- Nilai Aset Milik Investor -->
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card h-100 p-3 border-0 shadow-sm bg-white" style="border-radius: 12px; border-left: 4px solid #9333ea !important;">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="text-muted fw-bold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.05em;">Aset Milik Investor</span>
                    <span class="avatar bg-purple-lt text-purple rounded-circle" style="width: 36px; height: 36px;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9 7m-4 0a4 4 0 1 0 8 0a4 4 0 1 0 -8 0" /><path d="M3 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2" /><path d="M16 3.13a4 4 0 0 1 0 7.75" /><path d="M21 21v-2a4 4 0 0 0 -3 -3.85" /></svg>
                    </span>
                </div>
                <div class="h2 fw-bold text-purple font-monospace mb-1" id="stat-investor-price" style="letter-spacing: -0.02em;">
                    Rp {{ number_format($investorPriceSum, 0, ',', '.') }}
                </div>
                <div class="text-muted small" style="font-size: 0.75rem;">Milik pemegang saham / investor</div>
            </div>
        </div>
    </div>

    <!-- 2. Data Table & Unified Toolbar Card -->
    <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px; overflow: hidden;">
        <!-- Unified Filter & Search Bar Header -->
        <div class="card-header py-3 px-3 bg-white border-bottom">
            <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
                <div>
                    <div class="d-flex align-items-center gap-2">
                        <h3 class="card-title fw-bold text-dark mb-0 fs-3">Daftar Inventaris Aset</h3>
                        <span class="badge bg-secondary-lt font-monospace px-2 py-0.5 rounded-pill" id="badge-total-items">{{ $totalCount }} Aset</span>
                    </div>
                    <p class="text-muted small mb-0 mt-0.5" style="font-size: 0.75rem;">Monitoring spesifikasi perangkat, kepemilikan, foto, dan valuasi aset</p>
                </div>

                <!-- Unified Filter & Search Controls -->
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <!-- Filter Kategori Dropdown -->
                    <div class="input-group input-group-sm" style="width: auto; min-width: 165px;">
                        <span class="input-group-text bg-light border-end-0 text-muted">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="icon"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 4h6v6h-6z" /><path d="M14 4h6v6h-6z" /><path d="M4 14h6v6h-6z" /><path d="M17 17m-3 0a3 3 0 1 0 6 0a3 3 0 1 0 -6 0" /></svg>
                        </span>
                        <select id="filter_type" class="form-select form-select-sm border-start-0 ps-1">
                            <option value="">Semua Kategori</option>
                            @foreach($assetTypes as $type)
                                <option value="{{ $type }}">{{ $type }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Filter Kepemilikan Dropdown -->
                    <div class="input-group input-group-sm" style="width: auto; min-width: 155px;">
                        <span class="input-group-text bg-light border-end-0 text-muted">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="icon"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 3a12 12 0 0 0 8.5 3a12 12 0 0 1 -8.5 15a12 12 0 0 1 -8.5 -15a12 12 0 0 0 8.5 -3" /></svg>
                        </span>
                        <select id="filter_owner_type" class="form-select form-select-sm border-start-0 ps-1">
                            <option value="">Semua Pemilik</option>
                            <option value="pt">Milik PT (CIO)</option>
                            <option value="shareholder">Milik Investor</option>
                        </select>
                    </div>

                    <!-- Filter Investor Spesifik -->
                    <div id="filter_shareholder_wrap" class="d-none">
                        <select id="filter_shareholder_id" class="form-select form-select-sm" style="width: auto; min-width: 175px;">
                            <option value="">Semua Investor</option>
                            @foreach($shareholders as $sh)
                                <option value="{{ $sh->id }}">{{ $sh->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Unified Quick Search Input -->
                    <div class="input-icon" style="min-width: 220px;">
                        <span class="input-icon-addon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="icon text-muted"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 10m-7 0a7 7 0 1 0 14 0a7 7 0 1 0 -14 0" /><path d="M21 21l-6 -6" /></svg>
                        </span>
                        <input type="text" id="custom-search-input" class="form-control form-control-sm" placeholder="Cari nama, SN, MAC...">
                    </div>

                    <!-- Reset Button -->
                    <button type="button" id="btn-reset-filter" class="btn btn-outline-secondary btn-sm px-2.5" title="Reset Semua Filter">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="icon"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M20 11a8.1 8.1 0 0 0 -15.5 -2m-.5 -4v4h4" /><path d="M4 13a8.1 8.1 0 0 0 15.5 2m.5 4v-4h-4" /></svg>
                        <span class="d-none d-sm-inline">Reset</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Table Container -->
        <div class="table-responsive bg-white">
            <table id="assets-table" class="table table-vcenter card-table table-hover align-middle mb-0 w-100">
                <thead>
                    <tr class="bg-light text-muted font-monospace" style="font-size: 0.73rem; text-transform: uppercase; letter-spacing: 0.04em;">
                        <th class="w-1 text-center py-2.5">No</th>
                        <th class="py-2.5" style="min-width: 220px;">Nama & Tipe Barang</th>
                        <th class="py-2.5" style="min-width: 140px;">Harga Perolehan</th>
                        <th class="py-2.5" style="min-width: 170px;">SN & MAC Address</th>
                        <th class="py-2.5" style="min-width: 180px;">Kepemilikan Aset</th>
                        <th class="py-2.5" style="min-width: 130px;">Tgl Perolehan</th>
                        <th class="text-center w-1 py-2.5">Aksi</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<!-- ================= MODAL TAMBAH ASET ================= -->
<div class="modal fade" id="modalAddAsset" tabindex="-1" aria-labelledby="modalAddAssetLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 14px; overflow: hidden;">
            <div class="modal-header bg-white border-bottom py-3 px-4 d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-3">
                    <div class="avatar bg-primary-lt text-primary rounded-circle" style="width: 42px; height: 42px;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 3l8 4.5l0 9l-8 4.5l-8 -4.5l0 -9l8 -4.5" /><path d="M12 12l8 -4.5" /><path d="M12 12l0 9" /><path d="M12 12l-8 -4.5" /></svg>
                    </div>
                    <div>
                        <h4 class="modal-title fw-bold text-dark mb-0 fs-3" id="modalAddAssetLabel">Tambah Data Aset Baru</h4>
                        <p class="text-muted small mb-0 mt-0.5" style="font-size: 0.78rem;">Catat inventaris perangkat jaringan, server, foto barang, dan operasional PT</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form id="formAddAsset" action="{{ route('assets.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body p-4 bg-white">
                    <div class="row g-3">
                        <!-- Nama Barang -->
                        <div class="col-12 col-md-7">
                            <label class="form-label required fw-bold text-dark">Nama Barang / Perangkat</label>
                            <input type="text" name="name" class="form-control" placeholder="Contoh: Router MikroTik CCR2004-16G-2S+" required>
                        </div>

                        <!-- Tipe / Kategori -->
                        <div class="col-12 col-md-5">
                            <label class="form-label required fw-bold text-dark">Kategori / Tipe</label>
                            <input type="text" name="type" class="form-control" list="typeList" placeholder="Pilih atau ketik kategori" required>
                            <datalist id="typeList">
                                @foreach($assetTypes as $t)
                                    <option value="{{ $t }}"></option>
                                @endforeach
                            </datalist>
                        </div>

                        <!-- Harga Barang -->
                        <div class="col-12 col-md-6">
                            <label class="form-label required fw-bold text-dark">Harga Perolehan (Rp)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light font-monospace fw-bold text-success">Rp</span>
                                <input type="text" name="price" id="add_price" class="form-control font-monospace fw-bold rupiah-input" placeholder="0" required>
                            </div>
                        </div>

                        <!-- Tanggal Pembelian / Perolehan -->
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-bold text-dark">Tanggal Perolehan</label>
                            <input type="date" name="purchase_date" class="form-control font-monospace" value="{{ date('Y-m-d') }}">
                        </div>

                        <!-- Serial Number -->
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-bold text-dark">Serial Number (SN) <span class="text-muted fw-normal small">(Opsional)</span></label>
                            <input type="text" name="serial_number" class="form-control font-monospace" placeholder="Contoh: SN-87293104829">
                        </div>

                        <!-- MAC Address -->
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-bold text-dark">MAC Address <span class="text-muted fw-normal small">(Opsional)</span></label>
                            <input type="text" name="mac_address" class="form-control font-monospace" placeholder="Contoh: 00:1B:44:11:3A:B7">
                        </div>

                        <!-- UPLOAD MULTIPLE FOTO ASET -->
                        <div class="col-12">
                            <label class="form-label fw-bold text-dark mb-1 d-flex justify-content-between align-items-center">
                                <span class="d-flex align-items-center gap-1.5">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="icon text-primary"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M15 8h.01" /><path d="M3 6a3 3 0 0 1 3 -3h12a3 3 0 0 1 3 3v12a3 3 0 0 1 -3 3h-12a3 3 0 0 1 -3 -3v-12z" /><path d="M3 16l5 -5c.928 -.893 2.072 -.893 3 0l5 5" /><path d="M14 14l1 -1c.928 -.893 2.072 -.893 3 0l3 3" /></svg>
                                    <span>Foto / Dokumentasi Fisik Aset</span>
                                </span>
                                <span class="badge bg-light text-muted small">Bisa pilih lebih dari 1 foto (Maks. 5MB)</span>
                            </label>
                            <input type="file" name="images[]" id="add_asset_images" class="form-control" accept="image/jpeg,image/png,image/webp,image/jpg" multiple>
                            <div class="form-text text-muted small mt-1">Pilih satu atau beberapa file foto (JPG, PNG, WEBP). Foto pertama akan menjadi foto sampul.</div>
                            <!-- Live Preview Multiple Images -->
                            <div id="add_images_preview" class="d-flex flex-wrap gap-2 mt-2"></div>
                        </div>

                        <!-- PENGATURAN KEPEMILIKAN ASET (Segmented Cards) -->
                        <div class="col-12">
                            <label class="form-label fw-bold text-dark mb-2 d-flex align-items-center gap-1.5">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="icon text-primary"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 3a12 12 0 0 0 8.5 3a12 12 0 0 1 -8.5 15a12 12 0 0 1 -8.5 -15a12 12 0 0 0 8.5 -3" /></svg>
                                <span>Kepemilikan Aset <span class="text-danger">*</span></span>
                            </label>

                            @can('atur pemilik aset')
                                <div class="form-selectgroup form-selectgroup-boxes d-flex flex-column flex-sm-row gap-2 mb-2">
                                    <label class="form-selectgroup-item flex-fill">
                                        <input type="radio" name="owner_type" value="pt" class="form-selectgroup-input owner-type-radio" checked>
                                        <span class="form-selectgroup-label d-flex align-items-center p-3 border rounded-3 h-100">
                                            <span class="avatar bg-blue-lt text-primary rounded-circle me-3 flex-shrink-0" style="width: 36px; height: 36px;">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 21l18 0" /><path d="M5 21v-14l8 -4v18" /><path d="M19 21v-10l-6 -4" /></svg>
                                            </span>
                                            <span class="text-start">
                                                <span class="d-block fw-bold text-dark">Milik PT (PT CIO NETWORK)</span>
                                                <span class="d-block text-muted small" style="font-size: 0.74rem;">Inventaris & operasional resmi perusahaan</span>
                                            </span>
                                        </span>
                                    </label>

                                    <label class="form-selectgroup-item flex-fill">
                                        <input type="radio" name="owner_type" value="shareholder" class="form-selectgroup-input owner-type-radio">
                                        <span class="form-selectgroup-label d-flex align-items-center p-3 border rounded-3 h-100">
                                            <span class="avatar bg-purple-lt text-purple rounded-circle me-3 flex-shrink-0" style="width: 36px; height: 36px;">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9 7m-4 0a4 4 0 1 0 8 0a4 4 0 1 0 -8 0" /><path d="M3 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2" /><path d="M16 3.13a4 4 0 0 1 0 7.75" /><path d="M21 21v-2a4 4 0 0 0 -3 -3.85" /></svg>
                                            </span>
                                            <span class="text-start">
                                                <span class="d-block fw-bold text-dark">Milik Investor / Pemegang Saham</span>
                                                <span class="d-block text-muted small" style="font-size: 0.74rem;">Aset modal atau titipan investor tertentu</span>
                                            </span>
                                        </span>
                                    </label>
                                </div>

                                <div class="mt-2 p-3 bg-light-subtle rounded-3 border shareholder-select-wrap d-none">
                                    <label class="form-label small fw-bold text-purple mb-1">Pilih Pemegang Saham / Investor Pemilik:</label>
                                    <select name="shareholder_id" class="form-select select-shareholder">
                                        <option value="">-- Pilih Investor --</option>
                                        @foreach($shareholders as $sh)
                                            <option value="{{ $sh->id }}">{{ $sh->name }} ({{ $sh->email ?: $sh->phone ?: 'Investor' }})</option>
                                        @endforeach
                                    </select>
                                </div>
                            @else
                                <div class="border rounded-3 p-3 bg-light-subtle d-flex align-items-center gap-3">
                                    <span class="avatar bg-blue-lt text-primary rounded-circle flex-shrink-0" style="width: 38px; height: 38px;">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 13a2 2 0 0 1 2 -2h10a2 2 0 0 1 2 2v6a2 2 0 0 1 -2 2h-10a2 2 0 0 1 -2 -2v-6z" /><path d="M11 16a1 1 0 1 0 2 0a1 1 0 0 0 -2 0" /><path d="M8 11v-4a4 4 0 1 1 8 0v4" /></svg>
                                    </span>
                                    <div>
                                        <div class="fw-bold text-dark">Milik PT CIO NETWORK SOLUTION <span class="badge bg-secondary-lt font-monospace ms-1 small">Default Sistem</span></div>
                                        <div class="text-muted small">Kepemilikan aset otomatis ditetapkan sebagai milik PT untuk tingkat level akun Anda.</div>
                                    </div>
                                </div>
                            @endcan
                        </div>

                        <!-- Catatan / Spesifikasi Lokasi -->
                        <div class="col-12">
                            <label class="form-label fw-bold text-dark">Catatan / Lokasi Penempatan / Spesifikasi Tambahan</label>
                            <textarea name="notes" class="form-control" rows="2" placeholder="Contoh: Terpasang di POP Pusat Server Lt. 2, kondisi prima..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light py-3 px-4 d-flex align-items-center justify-content-between">
                    <button type="button" class="btn btn-ghost-secondary px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary px-4 shadow-sm d-inline-flex align-items-center gap-1.5" id="btnSubmitAddAsset">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="icon"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l5 5l10 -10" /></svg>
                        <span class="fw-semibold">Simpan Aset</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ================= MODAL EDIT ASET ================= -->
<div class="modal fade" id="modalEditAsset" tabindex="-1" aria-labelledby="modalEditAssetLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 14px; overflow: hidden;">
            <div class="modal-header bg-white border-bottom py-3 px-4 d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-3">
                    <div class="avatar bg-warning-lt text-warning rounded-circle" style="width: 42px; height: 42px;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="icon"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M7 7h-1a2 2 0 0 0 -2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2 -2v-1" /><path d="M20.385 6.585a2.1 2.1 0 0 0 -2.97 -2.97l-8.415 8.385v3h3l8.385 -8.415z" /><path d="M16 5l3 3" /></svg>
                    </div>
                    <div>
                        <h4 class="modal-title fw-bold text-dark mb-0 fs-3" id="modalEditAssetLabel">Ubah Data Aset</h4>
                        <p class="text-muted small mb-0 mt-0.5" style="font-size: 0.78rem;">Perbarui rincian spesifikasi, foto, atau status inventaris</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form id="formEditAsset" action="" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="modal-body p-4 bg-white">
                    <div class="row g-3">
                        <!-- Nama Barang -->
                        <div class="col-12 col-md-7">
                            <label class="form-label required fw-bold text-dark">Nama Barang / Perangkat</label>
                            <input type="text" name="name" id="edit_name" class="form-control" required>
                        </div>

                        <!-- Tipe / Kategori -->
                        <div class="col-12 col-md-5">
                            <label class="form-label required fw-bold text-dark">Kategori / Tipe</label>
                            <input type="text" name="type" id="edit_type" class="form-control" list="typeList" required>
                        </div>

                        <!-- Harga Barang -->
                        <div class="col-12 col-md-6">
                            <label class="form-label required fw-bold text-dark">Harga Perolehan (Rp)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light font-monospace fw-bold text-success">Rp</span>
                                <input type="text" name="price" id="edit_price" class="form-control font-monospace fw-bold rupiah-input" required>
                            </div>
                        </div>

                        <!-- Tanggal Pembelian / Perolehan -->
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-bold text-dark">Tanggal Perolehan</label>
                            <input type="date" name="purchase_date" id="edit_purchase_date" class="form-control font-monospace">
                        </div>

                        <!-- Serial Number -->
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-bold text-dark">Serial Number (SN) <span class="text-muted fw-normal small">(Opsional)</span></label>
                            <input type="text" name="serial_number" id="edit_serial_number" class="form-control font-monospace">
                        </div>

                        <!-- MAC Address -->
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-bold text-dark">MAC Address <span class="text-muted fw-normal small">(Opsional)</span></label>
                            <input type="text" name="mac_address" id="edit_mac_address" class="form-control font-monospace">
                        </div>

                        <!-- KELOLA FOTO ASET DI EDIT MODAL -->
                        <div class="col-12">
                            <label class="form-label fw-bold text-dark mb-1 d-flex justify-content-between align-items-center">
                                <span class="d-flex align-items-center gap-1.5">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="icon text-primary"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M15 8h.01" /><path d="M3 6a3 3 0 0 1 3 -3h12a3 3 0 0 1 3 3v12a3 3 0 0 1 -3 3h-12a3 3 0 0 1 -3 -3v-12z" /><path d="M3 16l5 -5c.928 -.893 2.072 -.893 3 0l5 5" /><path d="M14 14l1 -1c.928 -.893 2.072 -.893 3 0l3 3" /></svg>
                                    <span>Kelola Foto / Gambar Aset</span>
                                </span>
                                <span class="badge bg-light text-muted small">Maks. 5MB / foto</span>
                            </label>

                            <!-- Foto Saat Ini -->
                            <div id="edit_existing_images_wrap" class="p-3 bg-light-subtle rounded-3 border mb-2 d-none">
                                <div class="small fw-bold text-muted text-uppercase mb-2" style="font-size: 0.7rem;">Foto Tersimpan (Klik ikon silang untuk menghapus):</div>
                                <div id="edit_existing_images" class="d-flex flex-wrap gap-2.5"></div>
                            </div>
                            <input type="hidden" name="deleted_image_ids" id="edit_deleted_image_ids" value="">

                            <!-- Upload Tambahan Foto Baru -->
                            <input type="file" name="images[]" id="edit_asset_images" class="form-control" accept="image/jpeg,image/png,image/webp,image/jpg" multiple>
                            <div class="form-text text-muted small mt-1">Pilih foto tambahan untuk diunggah ke aset ini.</div>
                            <div id="edit_images_preview" class="d-flex flex-wrap gap-2 mt-2"></div>
                        </div>

                        <!-- PENGATURAN KEPEMILIKAN ASET -->
                        <div class="col-12">
                            <label class="form-label fw-bold text-dark mb-2 d-flex align-items-center gap-1.5">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="icon text-primary"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 3a12 12 0 0 0 8.5 3a12 12 0 0 1 -8.5 15a12 12 0 0 1 -8.5 -15a12 12 0 0 0 8.5 -3" /></svg>
                                <span>Kepemilikan Aset <span class="text-danger">*</span></span>
                            </label>

                            @can('atur pemilik aset')
                                <div class="form-selectgroup form-selectgroup-boxes d-flex flex-column flex-sm-row gap-2 mb-2">
                                    <label class="form-selectgroup-item flex-fill">
                                        <input type="radio" name="owner_type" id="edit_owner_pt" value="pt" class="form-selectgroup-input edit-owner-type-radio" checked>
                                        <span class="form-selectgroup-label d-flex align-items-center p-3 border rounded-3 h-100">
                                            <span class="avatar bg-blue-lt text-primary rounded-circle me-3 flex-shrink-0" style="width: 36px; height: 36px;">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 21l18 0" /><path d="M5 21v-14l8 -4v18" /><path d="M19 21v-10l-6 -4" /></svg>
                                            </span>
                                            <span class="text-start">
                                                <span class="d-block fw-bold text-dark">Milik PT (PT CIO NETWORK)</span>
                                                <span class="d-block text-muted small" style="font-size: 0.74rem;">Inventaris & operasional resmi perusahaan</span>
                                            </span>
                                        </span>
                                    </label>
                                    <label class="form-selectgroup-item flex-fill">
                                        <input type="radio" name="owner_type" id="edit_owner_shareholder" value="shareholder" class="form-selectgroup-input edit-owner-type-radio">
                                        <span class="form-selectgroup-label d-flex align-items-center p-3 border rounded-3 h-100">
                                            <span class="avatar bg-purple-lt text-purple rounded-circle me-3 flex-shrink-0" style="width: 36px; height: 36px;">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9 7m-4 0a4 4 0 1 0 8 0a4 4 0 1 0 -8 0" /><path d="M3 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2" /><path d="M16 3.13a4 4 0 0 1 0 7.75" /><path d="M21 21v-2a4 4 0 0 0 -3 -3.85" /></svg>
                                            </span>
                                            <span class="text-start">
                                                <span class="d-block fw-bold text-dark">Milik Investor / Pemegang Saham</span>
                                                <span class="d-block text-muted small" style="font-size: 0.74rem;">Aset modal atau titipan investor tertentu</span>
                                            </span>
                                        </span>
                                    </label>
                                </div>

                                <div class="mt-2 p-3 bg-light-subtle rounded-3 border edit-shareholder-select-wrap d-none">
                                    <label class="form-label small fw-bold text-purple mb-1">Pilih Pemegang Saham / Investor Pemilik:</label>
                                    <select name="shareholder_id" id="edit_shareholder_id" class="form-select select-shareholder">
                                        <option value="">-- Pilih Investor --</option>
                                        @foreach($shareholders as $sh)
                                            <option value="{{ $sh->id }}">{{ $sh->name }} ({{ $sh->email ?: $sh->phone ?: 'Investor' }})</option>
                                        @endforeach
                                    </select>
                                </div>
                            @else
                                <div class="border rounded-3 p-3 bg-light-subtle d-flex align-items-center gap-3">
                                    <span class="avatar bg-blue-lt text-primary rounded-circle flex-shrink-0" style="width: 38px; height: 38px;">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 13a2 2 0 0 1 2 -2h10a2 2 0 0 1 2 2v6a2 2 0 0 1 -2 2h-10a2 2 0 0 1 -2 -2v-6z" /><path d="M11 16a1 1 0 1 0 2 0a1 1 0 0 0 -2 0" /><path d="M8 11v-4a4 4 0 1 1 8 0v4" /></svg>
                                    </span>
                                    <div>
                                        <div class="fw-bold text-dark">Status Kepemilikan Terkunci</div>
                                        <div class="text-muted small">Kepemilikan aset tidak dapat diubah oleh tingkat level akun Anda.</div>
                                    </div>
                                </div>
                            @endcan
                        </div>

                        <!-- Catatan / Lokasi Penempatan -->
                        <div class="col-12">
                            <label class="form-label fw-bold text-dark">Catatan / Lokasi Penempatan / Spesifikasi Tambahan</label>
                            <textarea name="notes" id="edit_notes" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light py-3 px-4 d-flex align-items-center justify-content-between">
                    <button type="button" class="btn btn-ghost-secondary px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning px-4 shadow-sm d-inline-flex align-items-center gap-1.5" id="btnSubmitEditAsset">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="icon"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l5 5l10 -10" /></svg>
                        <span class="fw-semibold text-dark">Perbarui Aset</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ================= MODAL DETAIL ASET ================= -->
<div class="modal fade" id="modalShowAsset" tabindex="-1" aria-labelledby="modalShowAssetLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 14px; overflow: hidden;">
            <div class="modal-header bg-white border-bottom py-3 px-4 d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-3">
                    <div class="avatar bg-blue-lt text-primary rounded-circle" style="width: 42px; height: 42px;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="icon"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 3l8 4.5l0 9l-8 4.5l-8 -4.5l0 -9l8 -4.5" /><path d="M12 12l8 -4.5" /><path d="M12 12l0 9" /><path d="M12 12l-8 -4.5" /></svg>
                    </div>
                    <div>
                        <h4 class="modal-title fw-bold text-dark mb-0 fs-3" id="modalShowAssetLabel">Rincian Spesifikasi Aset</h4>
                        <p class="text-muted small mb-0 mt-0.5" style="font-size: 0.78rem;">Informasi lengkap identitas, kepemilikan, galeri foto, dan riwayat perolehan</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 bg-white" id="showAssetContent">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status"></div>
                    <div class="text-muted small mt-2">Memuat rincian aset...</div>
                </div>
            </div>
            <div class="modal-footer bg-light py-2.5 px-4 d-flex justify-content-end">
                <button type="button" class="btn btn-secondary px-3" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- ================= MODAL GALERI SEMUA FOTO ASET ================= -->
<div class="modal fade" id="modalAssetGallery" tabindex="-1" aria-labelledby="modalAssetGalleryLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 14px; overflow: hidden;">
            <div class="modal-header bg-white border-bottom py-3 px-4 d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-3">
                    <div class="avatar bg-purple-lt text-purple rounded-circle" style="width: 42px; height: 42px;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="icon"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M15 8h.01" /><path d="M3 6a3 3 0 0 1 3 -3h12a3 3 0 0 1 3 3v12a3 3 0 0 1 -3 3h-12a3 3 0 0 1 -3 -3v-12z" /><path d="M3 16l5 -5c.928 -.893 2.072 -.893 3 0l5 5" /><path d="M14 14l1 -1c.928 -.893 2.072 -.893 3 0l3 3" /></svg>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2">
                            <h4 class="modal-title fw-bold text-dark mb-0 fs-3" id="galleryModalTitle">Galeri Foto Aset</h4>
                            <span class="badge bg-purple text-white font-monospace" id="galleryModalBadge">0 Foto</span>
                        </div>
                        <p class="text-muted small mb-0 mt-0.5" style="font-size: 0.78rem;">Dokumentasi foto fisik inventaris aset. Klik foto untuk memperbesar tampilan.</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 bg-light" id="galleryModalContent" style="min-height: 250px;">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status"></div>
                    <div class="text-muted small mt-2">Memuat galeri foto...</div>
                </div>
            </div>
            <div class="modal-footer bg-white py-2.5 px-4 d-flex justify-content-between align-items-center">
                <div class="text-muted small" style="font-size: 0.75rem;">
                    💡 Tips: Klik kartu foto untuk melihat ukuran penuh di lightbox.
                </div>
                <button type="button" class="btn btn-secondary px-3" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- ================= MODAL LIGHTBOX PREVIEW FOTO ================= -->
<div class="modal fade" id="modalPhotoPreview" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg bg-dark text-white rounded-3 overflow-hidden">
            <div class="modal-header border-0 py-2.5 px-3 bg-dark d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="icon text-primary"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M15 8h.01" /><path d="M3 6a3 3 0 0 1 3 -3h12a3 3 0 0 1 3 3v12a3 3 0 0 1 -3 3h-12a3 3 0 0 1 -3 -3v-12z" /><path d="M3 16l5 -5c.928 -.893 2.072 -.893 3 0l5 5" /><path d="M14 14l1 -1c.928 -.893 2.072 -.893 3 0l3 3" /></svg>
                    <h5 class="modal-title mb-0 fs-4 fw-bold text-white text-truncate" id="photoPreviewTitle">Foto Aset</h5>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0 text-center bg-black d-flex align-items-center justify-content-center" style="min-height: 380px; max-height: 80vh;">
                <img id="photoPreviewImg" src="" class="img-fluid" style="max-height: 75vh; object-fit: contain;">
            </div>
        </div>
    </div>
</div>

@endsection

@push('js')
<script>
$(document).ready(function () {
    // 1. Format Rupiah Input Mask
    $(document).on('keyup', '.rupiah-input', function () {
        var value = $(this).val().replace(/[^0-9]/g, '');
        if (value) {
            $(this).val(new Intl.NumberFormat('id-ID').format(value));
        } else {
            $(this).val('');
        }
    });

    // 2. Client-side Multiple Image Preview (Add Modal)
    $('#add_asset_images').on('change', function () {
        var previewWrap = $('#add_images_preview');
        previewWrap.empty();
        if (this.files && this.files.length > 0) {
            Array.from(this.files).forEach(function (file, index) {
                var reader = new FileReader();
                reader.onload = function (e) {
                    var primaryBadge = index === 0 ? '<span class="badge bg-primary position-absolute bottom-0 start-0 m-1 font-monospace" style="font-size: 0.6rem;">Utama</span>' : '';
                    var card = $(`
                        <div class="position-relative border rounded-2 p-1 bg-white shadow-2xs" style="width: 78px; height: 78px;">
                            <img src="${e.target.result}" class="rounded-1 w-100 h-100" style="object-fit: cover;">
                            ${primaryBadge}
                        </div>
                    `);
                    previewWrap.append(card);
                };
                reader.readAsDataURL(file);
            });
        }
    });

    // Client-side Multiple Image Preview (Edit Modal)
    $('#edit_asset_images').on('change', function () {
        var previewWrap = $('#edit_images_preview');
        previewWrap.empty();
        if (this.files && this.files.length > 0) {
            Array.from(this.files).forEach(function (file) {
                var reader = new FileReader();
                reader.onload = function (e) {
                    var card = $(`
                        <div class="position-relative border rounded-2 p-1 bg-white shadow-2xs" style="width: 78px; height: 78px;">
                            <img src="${e.target.result}" class="rounded-1 w-100 h-100" style="object-fit: cover;">
                            <span class="badge bg-success position-absolute bottom-0 start-0 m-1 font-monospace" style="font-size: 0.6rem;">Baru</span>
                        </div>
                    `);
                    previewWrap.append(card);
                };
                reader.readAsDataURL(file);
            });
        }
    });

    // 3. Toggle Shareholder selection in Add Modal
    $('.owner-type-radio').on('change', function () {
        if ($(this).val() === 'shareholder') {
            $('.shareholder-select-wrap').removeClass('d-none');
            $('.select-shareholder').prop('required', true);
        } else {
            $('.shareholder-select-wrap').addClass('d-none');
            $('.select-shareholder').prop('required', false);
        }
    });

    // Toggle Shareholder selection in Edit Modal
    $('.edit-owner-type-radio').on('change', function () {
        if ($(this).val() === 'shareholder') {
            $('.edit-shareholder-select-wrap').removeClass('d-none');
            $('#edit_shareholder_id').prop('required', true);
        } else {
            $('.edit-shareholder-select-wrap').addClass('d-none');
            $('#edit_shareholder_id').prop('required', false);
        }
    });

    // 4. Filter Toolbar Events
    $('#filter_owner_type').on('change', function() {
        if ($(this).val() === 'shareholder') {
            $('#filter_shareholder_wrap').removeClass('d-none');
        } else {
            $('#filter_shareholder_wrap').addClass('d-none');
            $('#filter_shareholder_id').val('');
        }
        table.ajax.reload();
    });

    $('#filter_type').on('change', function() {
        table.ajax.reload();
    });

    $('#filter_shareholder_id').on('change', function() {
        table.ajax.reload();
    });

    $('#btn-reset-filter').on('click', function () {
        $('#filter_type').val('');
        $('#filter_owner_type').val('');
        $('#filter_shareholder_wrap').addClass('d-none');
        $('#filter_shareholder_id').val('');
        $('#custom-search-input').val('');
        table.search('').ajax.reload();
    });

    // 5. Initialize DataTables Server-Side
    var table = $('#assets-table').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        dom: "<'row g-0'<'col-12'tr>><'d-flex flex-column flex-sm-row align-items-center justify-content-between p-3 border-top gap-2 bg-white'<'text-muted small'i><'d-flex align-items-center gap-2'lp>>",
        ajax: {
            url: "{{ route('assets.index') }}",
            data: function (d) {
                d.type = $('#filter_type').val();
                d.owner_type = $('#filter_owner_type').val();
                d.shareholder_id = $('#filter_shareholder_id').val();
            }
        },
        columns: [
            { data: 'DT_RowIndex', name: 'id', orderable: false, searchable: false, className: 'text-center font-monospace small py-2.5', defaultContent: '' },
            { data: 'name_and_type', name: 'name', className: 'py-2.5' },
            { data: 'price_formatted', name: 'price', className: 'py-2.5' },
            { data: 'serial_and_mac', name: 'serial_number', className: 'py-2.5' },
            { data: 'owner_info', name: 'owner_name', className: 'py-2.5' },
            { data: 'purchase_date_creator', name: 'purchase_date', className: 'py-2.5' },
            { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center py-2.5' }
        ],
        order: [[5, 'desc']],
        pageLength: 15,
        lengthMenu: [[10, 15, 25, 50, 100], [10, 15, 25, 50, 100]],
        language: {
            processing: '<div class="spinner-border spinner-border-sm text-primary me-2"></div>Memuat data aset...',
            lengthMenu: "Tampilkan _MENU_ baris",
            info: "Menampilkan _START_ s/d _END_ dari _TOTAL_ aset",
            infoEmpty: "Menampilkan 0 data",
            zeroRecords: `<div class="text-center py-5">
                            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="1.5" class="mb-2"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 10m-7 0a7 7 0 1 0 14 0a7 7 0 1 0 -14 0" /><path d="M21 21l-6 -6" /></svg>
                            <h4 class="text-dark fw-bold mb-1">Tidak Ada Data yang Cocok</h4>
                            <p class="text-muted small mb-0">Coba ubah kata kunci pencarian atau sesuaikan filter Anda.</p>
                          </div>`,
            emptyTable: `<div class="text-center py-5">
                            <svg xmlns="http://www.w3.org/2000/svg" width="56" height="56" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round" class="mb-3 text-muted"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 3l8 4.5l0 9l-8 4.5l-8 -4.5l0 -9l8 -4.5" /><path d="M12 12l8 -4.5" /><path d="M12 12l0 9" /><path d="M12 12l-8 -4.5" /></svg>
                            <h3 class="text-dark fw-bold mb-1">Belum Ada Data Aset</h3>
                            <p class="text-muted small mb-3">Inventaris perangkat atau aset operasional belum terdata di sistem.</p>
                            @can('tambah aset')
                                <button type="button" class="btn btn-primary btn-sm px-3 py-1.5 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalAddAsset">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="icon me-1"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 5l0 14" /><path d="M5 12l14 0" /></svg>
                                    Tambah Aset Pertama
                                </button>
                            @endcan
                         </div>`,
            paginate: {
                first: "&laquo;",
                last: "&raquo;",
                next: "&rsaquo;",
                previous: "&lsaquo;"
            }
        },
        drawCallback: function (settings) {
            var json = settings.json;
            if (json) {
                if (json.totalCount !== undefined) {
                    $('#stat-total-count').html(json.totalCount + ' <span class="fs-4 text-muted fw-normal">Unit</span>');
                    $('#badge-total-items').text(json.totalCount + ' Aset');
                }
                if (json.totalPriceSum !== undefined) $('#stat-total-price').text('Rp ' + json.totalPriceSum);
                if (json.ptPriceSum !== undefined) $('#stat-pt-price').text('Rp ' + json.ptPriceSum);
                if (json.investorPriceSum !== undefined) $('#stat-investor-price').text('Rp ' + json.investorPriceSum);
            }
        }
    });

    // Custom Search Integration with Debounce
    var searchTimer;
    $('#custom-search-input').on('keyup', function () {
        clearTimeout(searchTimer);
        var val = this.value;
        searchTimer = setTimeout(function () {
            table.search(val).draw();
        }, 300);
    });

    // 6. View All Asset Photos in Gallery Modal
    $(document).on('click', '.btn-view-gallery', function () {
        var id = $(this).data('id');
        $('#modalAssetGallery').modal('show');
        $('#galleryModalContent').html('<div class="text-center py-5"><div class="spinner-border text-primary" role="status"></div><div class="text-muted small mt-2">Memuat galeri foto...</div></div>');

        $.ajax({
            url: "{{ url('assets') }}/" + id,
            type: "GET",
            dataType: "json",
            success: function (res) {
                if (res.status === 'success') {
                    var d = res.data;
                    $('#galleryModalTitle').text('Galeri Foto: ' + d.name);
                    var count = d.images ? d.images.length : 0;
                    $('#galleryModalBadge').text(count + ' Foto');

                    if (d.images && d.images.length > 0) {
                        var gridHtml = '<div class="row g-3">';
                        d.images.forEach(function (img, idx) {
                            var isPrimBadge = img.is_primary ? '<span class="badge bg-primary position-absolute top-0 start-0 m-2 shadow-sm font-monospace" style="font-size: 0.65rem;">Sampul Utama</span>' : '';
                            gridHtml += `
                                <div class="col-6 col-md-4 col-lg-3">
                                    <div class="gallery-card border bg-white position-relative shadow-2xs h-100 d-flex flex-column">
                                        <div class="position-relative overflow-hidden cursor-pointer btn-preview-photo" data-img="${img.url}" data-name="${d.name} (Foto ${idx + 1})" style="height: 155px; background-color: #0f172a;" title="Klik untuk memperbesar">
                                            <img src="${img.url}" class="w-100 h-100" style="object-fit: cover; transition: transform 0.25s ease;" onmouseover="this.style.transform='scale(1.06)'" onmouseout="this.style.transform='scale(1)'">
                                            ${isPrimBadge}
                                            <div class="position-absolute bottom-0 end-0 m-1 bg-dark bg-opacity-75 text-white rounded px-1.5 py-0.5 font-monospace" style="font-size: 0.65rem;">
                                                #${idx + 1}
                                            </div>
                                        </div>
                                        <div class="p-2 border-top bg-light d-flex justify-content-between align-items-center">
                                            <button type="button" class="btn btn-ghost-primary btn-xs btn-preview-photo" data-img="${img.url}" data-name="${d.name} (Foto ${idx + 1})">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 10m-7 0a7 7 0 1 0 14 0a7 7 0 1 0 -14 0" /><path d="M21 21l-6 -6" /><path d="M10 7v6" /><path d="M7 10h6" /></svg>
                                                Perbesar
                                            </button>
                                            <a href="${img.url}" target="_blank" class="btn btn-ghost-secondary btn-xs" title="Buka foto di tab baru">
                                                ↗
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            `;
                        });
                        gridHtml += '</div>';
                        $('#galleryModalContent').html(gridHtml);
                    } else {
                        $('#galleryModalContent').html(`
                            <div class="text-center py-5">
                                <div class="avatar bg-light text-muted rounded-circle mb-2" style="width: 50px; height: 50px;">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M15 8h.01" /><path d="M3 6a3 3 0 0 1 3 -3h12a3 3 0 0 1 3 3v12a3 3 0 0 1 -3 3h-12a3 3 0 0 1 -3 -3v-12z" /><path d="M3 16l5 -5c.928 -.893 2.072 -.893 3 0l5 5" /><path d="M14 14l1 -1c.928 -.893 2.072 -.893 3 0l3 3" /></svg>
                                </div>
                                <h4 class="text-dark fw-bold mb-1">Belum Ada Foto</h4>
                                <p class="text-muted small mb-0">Aset ini belum memiliki foto yang diunggah.</p>
                            </div>
                        `);
                    }
                }
            },
            error: function () {
                $('#galleryModalContent').html('<div class="alert alert-danger mb-0">Gagal mengambil galeri foto aset.</div>');
            }
        });
    });

    // 7. Click Photo Thumbnail for Full Preview Lightbox
    $(document).on('click', '.btn-preview-photo', function () {
        var url = $(this).data('img');
        var name = $(this).data('name') || 'Foto Aset';
        $('#photoPreviewImg').attr('src', url);
        $('#photoPreviewTitle').text(name);
        $('#modalPhotoPreview').modal('show');
    });

    // Restore modal-open body class for stacked modals
    $('#modalPhotoPreview').on('hidden.bs.modal', function () {
        if ($('#modalAssetGallery').hasClass('show') || $('#modalShowAsset').hasClass('show')) {
            $('body').addClass('modal-open');
        }
    });

    // 7. Clipboard Copy Micro-Interaction
    $(document).on('click', '.btn-copy-sn, .btn-copy-mac', function (e) {
        e.preventDefault();
        var text = $(this).data('clipboard-text');
        if (navigator.clipboard) {
            navigator.clipboard.writeText(text).then(function() {
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: 'Disalin: ' + text,
                    showConfirmButton: false,
                    timer: 1500
                });
            });
        }
    });

    // 8. Detail Modal (Show)
    $(document).on('click', '.btn-show-asset', function () {
        var id = $(this).data('id');
        $('#modalShowAsset').modal('show');
        $('#showAssetContent').html('<div class="text-center py-5"><div class="spinner-border text-primary" role="status"></div><div class="text-muted small mt-2">Memuat rincian aset...</div></div>');

        $.ajax({
            url: "{{ url('assets') }}/" + id,
            type: "GET",
            dataType: "json",
            success: function (res) {
                if (res.status === 'success') {
                    var d = res.data;
                    var ownerBadge = d.owner_type === 'shareholder'
                        ? '<span class="badge bg-purple-lt fw-bold font-monospace fs-4 px-2.5 py-1">Investor: ' + (d.shareholder_name || d.owner_label) + '</span>'
                        : '<span class="badge bg-blue-lt fw-bold font-monospace fs-4 px-2.5 py-1">PT CIO NETWORK SOLUTION</span>';

                    // Gallery HTML
                    var galleryHtml = '';
                    if (d.images && d.images.length > 0) {
                        galleryHtml = '<div class="col-12"><div class="border p-3 rounded-2 bg-white"><div class="text-muted small fw-bold text-uppercase mb-2" style="font-size: 0.7rem;">Galeri Foto Aset (' + d.images.length + ' Foto)</div><div class="d-flex flex-wrap gap-2.5">';
                        d.images.forEach(function (img, idx) {
                            var isPrim = img.is_primary ? '<span class="badge bg-primary position-absolute bottom-0 start-0 m-1 font-monospace" style="font-size: 0.55rem;">Sampul</span>' : '';
                            galleryHtml += `
                                <div class="position-relative border rounded-2 p-1 bg-white cursor-pointer btn-preview-photo shadow-2xs" data-img="${img.url}" data-name="${d.name} (${idx+1})" style="width: 86px; height: 86px;" title="Klik untuk memperbesar">
                                    <img src="${img.url}" class="rounded-1 w-100 h-100" style="object-fit: cover;">
                                    ${isPrim}
                                </div>
                            `;
                        });
                        galleryHtml += '</div></div></div>';
                    }

                    var html = `
                        <div class="card p-3 border-0 bg-light-subtle rounded-3 mb-3">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <span class="badge bg-secondary-lt fw-bold mb-1">${d.type}</span>
                                    <h3 class="fw-bold text-dark mb-0">${d.name}</h3>
                                </div>
                                <div class="text-end">
                                    <div class="text-muted small">Harga Perolehan</div>
                                    <div class="fs-2 fw-bold text-success font-monospace">Rp ${d.price_formatted}</div>
                                </div>
                            </div>
                        </div>

                        <div class="row g-3">
                            ${galleryHtml}

                            <div class="col-12 col-md-6">
                                <div class="border p-3 rounded-2 h-100 bg-white">
                                    <div class="text-muted small fw-bold text-uppercase mb-1" style="font-size: 0.7rem;">Status Kepemilikan</div>
                                    <div>${ownerBadge}</div>
                                </div>
                            </div>

                            <div class="col-12 col-md-6">
                                <div class="border p-3 rounded-2 h-100 bg-white">
                                    <div class="text-muted small fw-bold text-uppercase mb-1" style="font-size: 0.7rem;">Tanggal Pembelian / Perolehan</div>
                                    <div class="fs-4 fw-semibold text-dark font-monospace">${d.purchase_date_formatted}</div>
                                </div>
                            </div>

                            <div class="col-12 col-md-6">
                                <div class="border p-3 rounded-2 h-100 bg-white">
                                    <div class="text-muted small fw-bold text-uppercase mb-1" style="font-size: 0.7rem;">Serial Number (SN)</div>
                                    <div class="fs-4 fw-bold font-monospace text-azure">${d.serial_number}</div>
                                </div>
                            </div>

                            <div class="col-12 col-md-6">
                                <div class="border p-3 rounded-2 h-100 bg-white">
                                    <div class="text-muted small fw-bold text-uppercase mb-1" style="font-size: 0.7rem;">MAC Address</div>
                                    <div class="fs-4 fw-bold font-monospace text-teal">${d.mac_address}</div>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="border p-3 rounded-2 bg-white">
                                    <div class="text-muted small fw-bold text-uppercase mb-1" style="font-size: 0.7rem;">Catatan / Penempatan / Spesifikasi</div>
                                    <div class="text-dark" style="white-space: pre-line;">${d.notes}</div>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="d-flex justify-content-between align-items-center text-muted small pt-2 border-top" style="font-size: 0.75rem;">
                                    <span>Dicatat oleh: <strong>${d.creator_name}</strong></span>
                                    <span>Waktu Input: <strong>${d.created_at_formatted}</strong></span>
                                </div>
                            </div>
                        </div>
                    `;
                    $('#showAssetContent').html(html);
                }
            },
            error: function () {
                $('#showAssetContent').html('<div class="alert alert-danger mb-0">Gagal mengambil data aset.</div>');
            }
        });
    });

    // 9. Edit Modal (Fetch Data & Photos)
    var deletedImageIds = [];
    $(document).on('click', '.btn-edit-asset', function () {
        var id = $(this).data('id');
        deletedImageIds = [];
        $('#edit_deleted_image_ids').val('');
        $('#edit_images_preview').empty();
        $('#edit_asset_images').val('');

        $.ajax({
            url: "{{ url('assets') }}/" + id + "/edit",
            type: "GET",
            dataType: "json",
            success: function (res) {
                if (res.status === 'success') {
                    var d = res.data;
                    $('#formEditAsset').attr('action', d.update_url);
                    $('#edit_name').val(d.name);
                    $('#edit_type').val(d.type);
                    $('#edit_price').val(new Intl.NumberFormat('id-ID').format(d.price));
                    $('#edit_purchase_date').val(d.purchase_date);
                    $('#edit_serial_number').val(d.serial_number);
                    $('#edit_mac_address').val(d.mac_address);
                    $('#edit_notes').val(d.notes);

                    // Existing Images in Edit Modal
                    var existingWrap = $('#edit_existing_images');
                    existingWrap.empty();
                    if (d.images && d.images.length > 0) {
                        $('#edit_existing_images_wrap').removeClass('d-none');
                        d.images.forEach(function (img) {
                            var item = $(`
                                <div class="position-relative border rounded-2 p-1 bg-white shadow-2xs existing-img-item" id="existing_img_${img.id}" style="width: 78px; height: 78px;">
                                    <img src="${img.url}" class="rounded-1 w-100 h-100" style="object-fit: cover;">
                                    <button type="button" class="btn btn-danger btn-sm p-0 position-absolute top-0 end-0 rounded-circle btn-remove-existing-img" data-id="${img.id}" title="Hapus foto ini" style="width: 20px; height: 20px; transform: translate(30%, -30%); line-height: 1;">
                                        &times;
                                    </button>
                                </div>
                            `);
                            existingWrap.append(item);
                        });
                    } else {
                        $('#edit_existing_images_wrap').addClass('d-none');
                    }

                    if (d.owner_type === 'shareholder') {
                        $('#edit_owner_shareholder').prop('checked', true).trigger('change');
                        $('#edit_shareholder_id').val(d.shareholder_id);
                        $('.edit-shareholder-select-wrap').removeClass('d-none');
                    } else {
                        $('#edit_owner_pt').prop('checked', true).trigger('change');
                        $('#edit_shareholder_id').val('');
                        $('.edit-shareholder-select-wrap').addClass('d-none');
                    }

                    $('#modalEditAsset').modal('show');
                }
            },
            error: function () {
                Swal.fire('Error', 'Gagal memuat data aset untuk diedit.', 'error');
            }
        });
    });

    // Remove existing image in Edit Modal
    $(document).on('click', '.btn-remove-existing-img', function (e) {
        e.preventDefault();
        var imgId = $(this).data('id');
        deletedImageIds.push(imgId);
        $('#edit_deleted_image_ids').val(deletedImageIds.join(','));
        $('#existing_img_' + imgId).fadeOut(250, function () {
            $(this).remove();
            if ($('#edit_existing_images .existing-img-item').length === 0) {
                $('#edit_existing_images_wrap').addClass('d-none');
            }
        });
    });

    // 10. Delete Asset Confirmation
    $(document).on('click', '.btn-delete-asset', function () {
        var id = $(this).data('id');
        var name = $(this).data('name');

        Swal.fire({
            title: 'Hapus Aset?',
            text: "Apakah Anda yakin ingin menghapus data aset \"" + name + "\"? Seluruh data dan foto inventaris ini akan dihapus.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "{{ url('assets') }}/" + id,
                    type: "DELETE",
                    data: {
                        _token: "{{ csrf_token() }}"
                    },
                    success: function (res) {
                        Swal.fire('Terhapus!', res.message || 'Data aset berhasil dihapus.', 'success');
                        table.ajax.reload();
                    },
                    error: function (xhr) {
                        Swal.fire('Gagal!', xhr.responseJSON?.message || 'Terjadi kesalahan saat menghapus data.', 'error');
                    }
                });
            }
        });
        // Download Excel with active filters
        $('#btn-export-asset-excel').on('click', function () {
            const type = $('#filter_type').val();
            const ownerType = $('#filter_owner_type').val();
            const baseUrl = "{{ route('assets.export') }}";
            const params = new URLSearchParams();
            if (type) params.set('type', type);
            if (ownerType) params.set('owner_type', ownerType);
            const query = params.toString();
            $(this).attr('href', query ? `${baseUrl}?${query}` : baseUrl);
        });
    });
});
</script>
@endpush
