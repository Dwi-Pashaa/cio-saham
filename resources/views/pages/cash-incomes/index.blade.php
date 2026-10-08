@extends('layouts.app')

@section('pretitle', 'KAS & ARUS DANA')
@section('title', 'Catatan Saldo Masuk (Pemasukan)')
@section('subtitle', 'Pencatatan mutasi dana masuk ke rekening PT CIO NETWORK.')

@section('actions')
    <a href="{{ route('cash-incomes.export') }}" id="btn-export-income-excel" class="btn btn-outline-success btn-sm d-inline-flex align-items-center gap-1.5 shadow-sm px-3 py-2 rounded-2" title="Unduh data pemasukan kas ke Excel">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M14 3v4a1 1 0 0 0 1 1h4" /><path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z" /><path d="M10 12l4 4m0 -4l-4 4" /></svg>
        <span class="fw-semibold">Download Excel</span>
    </a>
    @can('tambah pemasukan')
        <button type="button" class="btn btn-success btn-sm d-inline-flex align-items-center gap-1.5 shadow-sm px-3 py-2 rounded-2" data-bs-toggle="modal" data-bs-target="#modalAddIncome">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="icon"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 5l0 14" /><path d="M5 12l14 0" /></svg>
            <span class="fw-semibold">Tambah Pemasukan</span>
        </button>
    @endcan
@endsection

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

    <!-- 1. Summary Cards Grid -->
    <div class="row g-3 mb-3">
        <div class="col-12 col-sm-6 col-lg-4">
            <div class="card h-100 p-3 border-0 shadow-sm bg-white" style="border-radius: 12px; border-left: 4px solid #16a34a !important;">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="text-muted fw-bold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.05em;">Total Dana Masuk</span>
                    <span class="badge bg-green-lt text-success p-2 rounded-circle">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M17 8v-3a1 1 0 0 0 -1 -1h-10a2 2 0 0 0 0 4h12a1 1 0 0 1 1 1v3m0 4v3a1 1 0 0 1 -1 1h-12a2 2 0 0 1 -2 -2v-12" /><path d="M20 12v4h-4a2 2 0 0 1 0 -4h4" /></svg>
                    </span>
                </div>
                <div class="h2 fw-bold text-success font-monospace mb-1" id="stat-total-income" style="letter-spacing: -0.02em;">
                    Rp {{ number_format($totalIncomeSum, 0, ',', '.') }}
                </div>
                <div class="text-muted small" style="font-size: 0.75rem;">Akumulasi total dari <span id="stat-total-count">{{ $totalCount }}</span> transaksi</div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-lg-4">
            <div class="card h-100 p-3 border-0 shadow-sm bg-white" style="border-radius: 12px; border-left: 4px solid #f59e0b !important;">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="text-muted fw-bold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.05em;">Total Biaya Admin</span>
                    <span class="badge bg-warning-lt text-warning p-2 rounded-circle">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9 14c0 1.657 2.686 3 6 3s6 -1.343 6 -3s-2.686 -3 -6 -3s-6 1.343 -6 3z" /><path d="M9 14v4c0 1.656 2.686 3 6 3s6 -1.344 6 -3v-4" /><path d="M3 6c0 1.072 1.144 2.062 3 2.598s4.144 .536 6 0c1.856 -.536 3 -1.526 3 -2.598c0 -1.072 -1.144 -2.063 -3 -2.598c-1.856 -.536 -4.144 -.536 -6 0c-1.856 .535 -3 1.526 -3 2.598z" /><path d="M3 6v10c0 .888 .772 1.45 2 2" /><path d="M3 11c0 .888 .772 1.45 2 2" /></svg>
                    </span>
                </div>
                <div class="h2 fw-bold text-warning font-monospace mb-1" id="stat-total-admin" style="letter-spacing: -0.02em;">
                    Rp {{ number_format($totalAdminFeeSum, 0, ',', '.') }}
                </div>
                <div class="text-muted small" style="font-size: 0.75rem;">Potongan admin fee transfer</div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-lg-4">
            <div class="card h-100 p-3 border-0 shadow-sm bg-white" style="border-radius: 12px; border-left: 4px solid #2563eb !important;">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="text-muted fw-bold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.05em;">Total Bersih Diterima</span>
                    <span class="badge bg-primary-lt text-primary p-2 rounded-circle">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 7a2 2 0 0 1 2 -2h14a2 2 0 0 1 2 2v10a2 2 0 0 1 -2 2h-14a2 2 0 0 1 -2 -2v-10z" /><path d="M3 7l9 6l9 -6" /></svg>
                    </span>
                </div>
                <div class="h2 fw-bold text-primary font-monospace mb-1" id="stat-total-net" style="letter-spacing: -0.02em;">
                    Rp {{ number_format($totalNetSum, 0, ',', '.') }}
                </div>
                <div class="text-muted small" style="font-size: 0.75rem;">Dana bersih masuk ke kas PT</div>
            </div>
        </div>
    </div>

    <!-- 2. Data Table & Filter Card -->
    <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px; overflow: hidden;">
        <!-- Filter Bar Header -->
        <div class="card-header py-3 px-3 bg-white border-bottom">
            <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
                <div>
                    <h3 class="card-title fw-bold text-dark mb-0 fs-3">Riwayat Saldo Masuk</h3>
                    <p class="text-muted small mb-0 mt-0.5" style="font-size: 0.75rem;">Semua pencatatan transaksi uang masuk (Server-Side)</p>
                </div>

                <!-- Unified Date Filter & Quick Chips -->
                <div class="d-flex flex-wrap align-items-center justify-content-lg-end gap-2">
                    <!-- Quick Preset Chips -->
                    <div class="d-flex align-items-center gap-1">
                        <button type="button" class="quick-filter-chip active" data-range="all">Semua</button>
                        <button type="button" class="quick-filter-chip" data-range="today">Hari Ini</button>
                        <button type="button" class="quick-filter-chip" data-range="month">Bulan Ini</button>
                    </div>

                    <!-- Single Clean Date Range Container -->
                    <div class="filter-date-container">
                        <span class="text-muted pe-1 d-flex align-items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-secondary"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 7a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v12a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12z" /><path d="M16 3v4" /><path d="M8 3v4" /><path d="M4 11h16" /></svg>
                        </span>
                        <input type="date" id="filter_start_date" class="filter-date-input" title="Dari Tanggal">
                        <span class="filter-date-separator">&rarr;</span>
                        <input type="date" id="filter_end_date" class="filter-date-input" title="Sampai Tanggal">
                    </div>

                    <!-- Action Buttons -->
                    <div class="d-flex align-items-center gap-1.5">
                        <button type="button" id="btn-apply-filter" class="btn btn-primary btn-filter-action shadow-xs">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 4h16v2.172a2 2 0 0 1 -.586 1.414l-4.828 4.828a2 2 0 0 0 -.586 1.414v3.172l-4 2v-5.172a2 2 0 0 0 -.586 -1.414l-4.828 -4.828a2 2 0 0 1 -.586 -1.414v-2.172z" /></svg>
                            <span>Filter</span>
                        </button>
                        <button type="button" id="btn-reset-filter" class="btn btn-outline-secondary btn-filter-action" title="Reset Filter">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M20 11a8.1 8.1 0 0 0 -15.5 -2m-.5 -4v4h4" /><path d="M4 13a8.1 8.1 0 0 0 15.5 2m.5 4v-4h-4" /></svg>
                            <span>Reset</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="table-responsive p-3">
            <table id="table-cash-incomes" class="table table-vcenter card-table table-hover mb-0 w-100 cash-table">
                <thead>
                    <tr>
                        <th class="ps-3 py-2.5">No. Transaksi / Tanggal</th>
                        <th class="py-2.5">Dari Rekening (Pengirim)</th>
                        <th class="py-2.5">Rekening / Bank</th>
                        <th class="text-end py-2.5">Nominal Pokok</th>
                        <th class="text-center py-2.5">Admin Fee</th>
                        <th class="text-end py-2.5">Bersih Diterima</th>
                        <th class="text-center py-2.5">Bukti</th>
                        <th class="text-center pe-3 py-2.5">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('modal')
<!-- MODAL ADD INCOME (TAMBAH DATA) -->
<div class="modal fade" id="modalAddIncome" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 14px; overflow: hidden;">
            <form action="{{ route('cash-incomes.store') }}" method="POST" enctype="multipart/form-data" id="formAddIncome">
                @csrf
                <div class="modal-header bg-white border-bottom py-3 px-4 d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-3">
                        <div class="avatar bg-green-lt text-success rounded-circle" style="width: 40px; height: 40px;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M17 8v-3a1 1 0 0 0 -1 -1h-10a2 2 0 0 0 0 4h12a1 1 0 0 1 1 1v3m0 4v3a1 1 0 0 1 -1 1h-12a2 2 0 0 1 -2 -2v-12" /><path d="M20 12v4h-4a2 2 0 0 1 0 -4h4" /></svg>
                        </div>
                        <div>
                            <h4 class="modal-title fw-bold text-dark mb-0">Catat Saldo Masuk (Pemasukan)</h4>
                            <p class="text-muted small mb-0 mt-0.5">Pencatatan dana masuk ke rekening PT CIO NETWORK</p>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4 bg-white">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label required fw-semibold text-dark">Dari Rekening (Atas Nama Pengirim)</label>
                            <input type="text" name="source_account_name" class="form-control" value="{{ old('source_account_name') }}" required placeholder="Contoh: PT Mitra Solusi / Budi Santoso">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label required fw-semibold text-dark">Pilihan Bank / Saluran</label>
                            <select name="bank_name" id="select_bank_add" class="form-select select2-bank" required style="width: 100%;">
                                <option value="">-- Pilih Bank / E-Wallet --</option>
                                @if(isset($banksGrouped))
                                    @foreach($banksGrouped as $category => $banks)
                                        <optgroup label="{{ $category }}">
                                            @foreach($banks as $b)
                                                <option value="{{ $b['name'] }}" {{ old('bank_name') == $b['name'] ? 'selected' : '' }}>
                                                    {{ $b['name'] }}
                                                </option>
                                            @endforeach
                                        </optgroup>
                                    @endforeach
                                @endif
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label required fw-semibold text-dark">Nomor Rekening</label>
                            <input type="text" name="account_number" class="form-control font-monospace" value="{{ old('account_number') }}" required placeholder="Contoh: 8830123456">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label required fw-semibold text-dark">Tanggal Transaksi</label>
                            <input type="date" name="transaction_date" class="form-control font-monospace" value="{{ old('transaction_date', date('Y-m-d')) }}" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label required fw-semibold text-dark">Nominal Masuk (Rp)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light fw-bold text-success">Rp</span>
                                <input type="text" name="amount" id="add_amount" class="form-control font-monospace fw-bold fs-4 input-rupiah" value="{{ old('amount') }}" required placeholder="0">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-dark d-flex justify-content-between align-items-center">
                                <span>Potongan Biaya Admin</span>
                                <span class="badge bg-light text-muted font-monospace" style="font-size: 0.7rem;">Opsional</span>
                            </label>
                            <div class="segmented-fee-group mb-2">
                                <button type="button" class="segmented-fee-btn active" id="btn_fee_no_add" onclick="setFeeMode('add', false)">Tidak Ada (Rp 0)</button>
                                <button type="button" class="segmented-fee-btn" id="btn_fee_yes_add" onclick="setFeeMode('add', true)">Ada Potongan</button>
                            </div>
                            <input type="hidden" name="has_admin_fee" id="has_admin_fee_add" value="tidak">
                            <div id="wrap_admin_fee_add" class="d-none">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-light text-warning fw-bold">Rp</span>
                                    <input type="text" name="admin_fee" id="add_admin_fee" class="form-control font-monospace input-rupiah" value="{{ old('admin_fee', '0') }}" placeholder="0">
                                </div>
                            </div>
                        </div>

                        <!-- Live Calculation Summary Box -->
                        <div class="col-12">
                            <div class="live-calc-box live-calc-income">
                                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                                    <div>
                                        <div class="text-success fw-bold small text-uppercase" style="letter-spacing: 0.04em;">Estimasi Bersih Masuk ke Kas:</div>
                                        <div class="h2 fw-bold font-monospace text-primary mb-0" id="calc_net_add">Rp 0</div>
                                    </div>
                                    <div class="text-end text-muted small font-monospace">
                                        <span id="calc_gross_add">Rp 0</span> (Pokok) &minus; <span id="calc_fee_add" class="text-warning">Rp 0</span> (Admin)
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <label class="form-label required fw-semibold text-dark">Upload Bukti Transaksi</label>
                            <input type="file" name="proof_file" id="add_proof_file" class="form-control" required accept="image/*,application/pdf" onchange="previewUpload(this, 'add_proof_preview_wrap', 'add_proof_preview_img')">
                            <div class="small text-muted mt-1">Format didukung: JPG, PNG, WEBP, atau PDF (Maks. 5MB)</div>
                            <div id="add_proof_preview_wrap" class="mt-2 d-none">
                                <img id="add_proof_preview_img" src="#" alt="Preview Bukti" class="rounded border p-1" style="max-height: 120px;">
                            </div>
                        </div>

                        <div class="col-12">
                            <label class="form-label required fw-semibold text-dark">Catatan / Keterangan</label>
                            <textarea name="notes" rows="2" class="form-control" required placeholder="Tuliskan keterangan tujuan atau detail transaksi pemasukan...">{{ old('notes') }}</textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2.5 px-4 d-flex justify-content-between">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success btn-sm px-4 fw-semibold shadow-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="me-1"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l5 5l10 -10" /></svg>
                        Simpan Pemasukan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- DYNAMIC MODAL SHOW INCOME (DIGITAL SLIP) -->
<div class="modal fade" id="modalShowIncome" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 14px; overflow: hidden;">
            <div class="modal-header bg-white border-bottom py-3 px-4 d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-3">
                    <div class="avatar bg-green-lt text-success rounded-circle" style="width: 40px; height: 40px;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M14 3v4a1 1 0 0 0 1 1h4" /><path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z" /><path d="M9 17h6" /><path d="M9 13h6" /></svg>
                    </div>
                    <div>
                        <h4 class="modal-title fw-bold text-dark mb-0">Slip Bukti Saldo Masuk</h4>
                        <span class="small font-monospace text-muted" id="show_transaction_number">-</span>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 bg-light-subtle" id="show_modal_body">
                <div class="text-center py-5 text-muted">
                    <div class="spinner-border spinner-border-sm text-success me-2" role="status"></div> Memuat data slip transaksi...
                </div>
            </div>
            <div class="modal-footer bg-white py-2.5 px-4 d-flex justify-content-between border-top">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="window.print()">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="me-1"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M17 17h2a2 2 0 0 0 2 -2v-4a2 2 0 0 0 -2 -2h-14a2 2 0 0 0 -2 2v4a2 2 0 0 0 2 2h2" /><path d="M17 9v-4a2 2 0 0 0 -2 -2h-6a2 2 0 0 0 -2 2v4" /><path d="M7 13m0 2a2 2 0 0 1 2 -2h6a2 2 0 0 1 2 2v4a2 2 0 0 1 -2 2h-6a2 2 0 0 1 -2 -2z" /></svg>
                        Cetak Slip
                    </button>
                    @can('ubah pemasukan')
                        <button type="button" class="btn btn-primary btn-sm px-3" id="btn-show-to-edit">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="me-1"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M7 7h-1a2 2 0 0 0 -2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2 -2v-1" /><path d="M20.385 6.585a2.1 2.1 0 0 0 -2.97 -2.97l-8.415 8.385v3h3l8.385 -8.415z" /><path d="M16 5l3 3" /></svg>
                            Ubah Data Ini
                        </button>
                    @endcan
                </div>
            </div>
        </div>
    </div>
</div>

<!-- DYNAMIC MODAL EDIT INCOME -->
<div class="modal fade" id="modalEditIncome" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 14px; overflow: hidden;">
            <form id="formEditIncome" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="modal-header bg-white border-bottom py-3 px-4 d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-3">
                        <div class="avatar bg-blue-lt text-primary rounded-circle" style="width: 40px; height: 40px;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M7 7h-1a2 2 0 0 0 -2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2 -2v-1" /><path d="M20.385 6.585a2.1 2.1 0 0 0 -2.97 -2.97l-8.415 8.385v3h3l8.385 -8.415z" /><path d="M16 5l3 3" /></svg>
                        </div>
                        <div>
                            <h4 class="modal-title fw-bold text-dark mb-0">Ubah Catatan Pemasukan</h4>
                            <span class="small font-monospace text-muted" id="edit_transaction_number">-</span>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4 bg-white">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label required fw-semibold text-dark">Dari Rekening (Atas Nama Pengirim)</label>
                            <input type="text" name="source_account_name" id="edit_sender_name" class="form-control" required placeholder="Contoh: PT Mitra Solusi / Budi Santoso">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label required fw-semibold text-dark">Pilihan Bank / Saluran</label>
                            <select name="bank_name" id="select_bank_edit" class="form-select select2-bank" required style="width: 100%;">
                                <option value="">-- Pilih Bank / E-Wallet --</option>
                                @if(isset($banksGrouped))
                                    @foreach($banksGrouped as $category => $banks)
                                        <optgroup label="{{ $category }}">
                                            @foreach($banks as $b)
                                                <option value="{{ $b['name'] }}">{{ $b['name'] }}</option>
                                            @endforeach
                                        </optgroup>
                                    @endforeach
                                @endif
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label required fw-semibold text-dark">Nomor Rekening</label>
                            <input type="text" name="account_number" id="edit_account_number" class="form-control font-monospace" required placeholder="8830123xxx">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label required fw-semibold text-dark">Tanggal Transaksi</label>
                            <input type="date" name="transaction_date" id="edit_transaction_date" class="form-control font-monospace" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label required fw-semibold text-dark">Nominal Masuk (Rp)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light fw-bold text-success">Rp</span>
                                <input type="text" name="amount" id="edit_amount" class="form-control font-monospace fw-bold fs-4 input-rupiah" required placeholder="0">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-dark d-flex justify-content-between align-items-center">
                                <span>Potongan Biaya Admin</span>
                                <span class="badge bg-light text-muted font-monospace" style="font-size: 0.7rem;">Opsional</span>
                            </label>
                            <div class="segmented-fee-group mb-2">
                                <button type="button" class="segmented-fee-btn" id="btn_fee_no_edit" onclick="setFeeMode('edit', false)">Tidak Ada (Rp 0)</button>
                                <button type="button" class="segmented-fee-btn" id="btn_fee_yes_edit" onclick="setFeeMode('edit', true)">Ada Potongan</button>
                            </div>
                            <input type="hidden" name="has_admin_fee" id="has_admin_fee_edit" value="tidak">
                            <div id="wrap_admin_fee_edit" class="d-none">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-light text-warning fw-bold">Rp</span>
                                    <input type="text" name="admin_fee" id="edit_admin_fee" class="form-control font-monospace input-rupiah" placeholder="0">
                                </div>
                            </div>
                        </div>

                        <!-- Live Calculation Summary Box -->
                        <div class="col-12">
                            <div class="live-calc-box live-calc-income">
                                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                                    <div>
                                        <div class="text-success fw-bold small text-uppercase" style="letter-spacing: 0.04em;">Estimasi Bersih Masuk ke Kas:</div>
                                        <div class="h2 fw-bold font-monospace text-primary mb-0" id="calc_net_edit">Rp 0</div>
                                    </div>
                                    <div class="text-end text-muted small font-monospace">
                                        <span id="calc_gross_edit">Rp 0</span> (Pokok) &minus; <span id="calc_fee_edit" class="text-warning">Rp 0</span> (Admin)
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold text-dark">Upload Bukti Transaksi Baru (Opsional jika tidak diganti)</label>
                            <input type="file" name="proof_file" class="form-control" accept="image/*,application/pdf" onchange="previewUpload(this, 'edit_proof_preview_wrap', 'edit_proof_preview_img')">
                            <div id="edit_proof_preview" class="small text-muted mt-1 d-none"></div>
                            <div id="edit_proof_preview_wrap" class="mt-2 d-none">
                                <img id="edit_proof_preview_img" src="#" alt="Preview Bukti Baru" class="rounded border p-1" style="max-height: 120px;">
                            </div>
                        </div>

                        <div class="col-12">
                            <label class="form-label required fw-semibold text-dark">Catatan / Keterangan</label>
                            <textarea name="notes" id="edit_notes" rows="2" class="form-control" required placeholder="Tuliskan keterangan detail transaksi..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2.5 px-4 d-flex justify-content-between">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm px-4 fw-semibold shadow-sm">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endpush

@push('js')
<script>
    // Global Helper functions
    function parseRupiah(str) {
        if (!str) return 0;
        let cleaned = str.toString().replace(/[^0-9]/g, '');
        return parseFloat(cleaned) || 0;
    }

    function formatRupiahDisplay(num) {
        return 'Rp ' + Math.round(num).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }

    function updateLiveCalculation(prefix) {
        let gross = parseRupiah($('#' + prefix + '_amount').val());
        let hasFee = $('#has_admin_fee_' + prefix).val() === 'ya';
        let fee = hasFee ? parseRupiah($('#' + prefix + '_admin_fee').val()) : 0;
        let net = Math.max(0, gross - fee);

        $('#calc_gross_' + prefix).text(formatRupiahDisplay(gross));
        $('#calc_fee_' + prefix).text(formatRupiahDisplay(fee));
        $('#calc_net_' + prefix).text(formatRupiahDisplay(net));
    }

    function setFeeMode(prefix, hasFee) {
        $('#has_admin_fee_' + prefix).val(hasFee ? 'ya' : 'tidak');
        if (hasFee) {
            $('#btn_fee_no_' + prefix).removeClass('active');
            $('#btn_fee_yes_' + prefix).addClass('active');
            $('#wrap_admin_fee_' + prefix).removeClass('d-none');
            setTimeout(function() {
                $('#' + prefix + '_admin_fee').focus();
            }, 50);
        } else {
            $('#btn_fee_yes_' + prefix).removeClass('active');
            $('#btn_fee_no_' + prefix).addClass('active');
            $('#wrap_admin_fee_' + prefix).addClass('d-none');
            $('#' + prefix + '_admin_fee').val('0');
        }
        updateLiveCalculation(prefix);
    }

    function previewUpload(input, wrapId, imgId) {
        if (input.files && input.files[0]) {
            let file = input.files[0];
            if (file.type.match('image.*')) {
                let reader = new FileReader();
                reader.onload = function (e) {
                    $('#' + imgId).attr('src', e.target.result);
                    $('#' + wrapId).removeClass('d-none');
                };
                reader.readAsDataURL(file);
            } else {
                $('#' + wrapId).addClass('d-none');
            }
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        // Initialize Select2 on Modals
        $('#select_bank_add').select2({
            theme: 'bootstrap-5',
            dropdownParent: $('#modalAddIncome'),
            tags: true,
            placeholder: '-- Pilih atau Ketik Nama Bank --',
            allowClear: true
        });

        $('#select_bank_edit').select2({
            theme: 'bootstrap-5',
            dropdownParent: $('#modalEditIncome'),
            tags: true,
            placeholder: '-- Pilih atau Ketik Nama Bank --',
            allowClear: true
        });

        // Rupiah Formatter on Inputs
        function bindRupiahInputs() {
            document.querySelectorAll('.input-rupiah').forEach(function (input) {
                input.removeEventListener('input', handleRupiahInput);
                input.addEventListener('input', handleRupiahInput);
            });
        }

        function handleRupiahInput() {
            let raw = this.value.replace(/[^0-9]/g, '');
            if (raw === '') {
                this.value = '';
            } else {
                this.value = raw.replace(/\B(?=(\d{3})+(?!\d))/g, ".");
            }

            if (this.id.includes('add')) {
                updateLiveCalculation('add');
            } else if (this.id.includes('edit')) {
                updateLiveCalculation('edit');
            }
        }

        bindRupiahInputs();

        // Copy account number micro interaction
        $(document).on('click', '.btn-copy-account', function (e) {
            e.preventDefault();
            let text = $(this).data('clipboard-text');
            if (navigator.clipboard) {
                navigator.clipboard.writeText(text).then(function () {
                    const Toast = Swal.mixin({
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 1500
                    });
                    Toast.fire({ icon: 'success', title: 'No. Rekening disalin: ' + text });
                });
            }
        });

        // Initialize DataTable
        const table = $('#table-cash-incomes').DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            ajax: {
                url: "{{ route('cash-incomes.index') }}",
                data: function (d) {
                    d.start_date = $('#filter_start_date').val();
                    d.end_date = $('#filter_end_date').val();
                }
            },
            columns: [
                { data: 'transaction_number_date', name: 'transaction_number', className: 'ps-3 py-2.5' },
                { data: 'sender_info', name: 'sender_name', className: 'py-2.5' },
                { data: 'bank_info', name: 'bank_name', className: 'py-2.5' },
                { data: 'formatted_amount', name: 'amount', className: 'text-end py-2.5 font-monospace' },
                { data: 'formatted_admin_fee', name: 'admin_fee', className: 'text-center py-2.5' },
                { data: 'formatted_net_amount', name: 'net_amount', className: 'text-end py-2.5 font-monospace' },
                { data: 'proof_badge', name: 'proof_file', orderable: false, searchable: false, className: 'text-center py-2.5' },
                { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center pe-3 py-2.5' },
            ],
            order: [[0, 'desc']],
            pageLength: 15,
            lengthMenu: [[10, 15, 25, 50, 100], [10, 15, 25, 50, 100]],
            language: {
                emptyTable: `<div class="text-center py-5">
                    <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="mb-2"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M17 8v-3a1 1 0 0 0 -1 -1h-10a2 2 0 0 0 0 4h12a1 1 0 0 1 1 1v3m0 4v3a1 1 0 0 1 -1 1h-12a2 2 0 0 1 -2 -2v-12" /><path d="M20 12v4h-4a2 2 0 0 1 0 -4h4" /></svg>
                    <h4 class="text-dark fw-bold mb-1">Belum Ada Catatan Pemasukan</h4>
                    <p class="text-muted small mb-0">Klik tombol Tambah Pemasukan untuk mencatat mutasi uang masuk pertama.</p>
                </div>`
            },
            drawCallback: function (settings) {
                const response = settings.json;
                if (response) {
                    if (response.totalIncomeSum !== undefined) {
                        $('#stat-total-income').text('Rp ' + response.totalIncomeSum);
                    }
                    if (response.totalAdminFeeSum !== undefined) {
                        $('#stat-total-admin').text('Rp ' + response.totalAdminFeeSum);
                    }
                    if (response.totalNetSum !== undefined) {
                        $('#stat-total-net').text('Rp ' + response.totalNetSum);
                    }
                    if (response.totalCount !== undefined) {
                        $('#stat-total-count').text(response.totalCount);
                    }
                }
            }
        });

        // Filter events
        $('#btn-apply-filter').on('click', function () {
            $('.quick-filter-chip').removeClass('active');
            table.draw();
        });

        // Quick filter chips event
        $('.quick-filter-chip').on('click', function () {
            $('.quick-filter-chip').removeClass('active');
            $(this).addClass('active');

            let range = $(this).data('range');
            let today = new Date();
            let yyyy = today.getFullYear();
            let mm = String(today.getMonth() + 1).padStart(2, '0');
            let dd = String(today.getDate()).padStart(2, '0');

            if (range === 'today') {
                let todayStr = `${yyyy}-${mm}-${dd}`;
                $('#filter_start_date').val(todayStr);
                $('#filter_end_date').val(todayStr);
            } else if (range === 'month') {
                let firstDay = `${yyyy}-${mm}-01`;
                let lastDay = new Date(yyyy, today.getMonth() + 1, 0).getDate();
                let lastDayStr = `${yyyy}-${mm}-${String(lastDay).padStart(2, '0')}`;
                $('#filter_start_date').val(firstDay);
                $('#filter_end_date').val(lastDayStr);
            } else {
                $('#filter_start_date').val('');
                $('#filter_end_date').val('');
            }

            table.draw();
        });

        $('#filter_start_date, #filter_end_date').on('change', function () {
            $('.quick-filter-chip').removeClass('active');
        });

        $('#btn-reset-filter').on('click', function () {
            $('.quick-filter-chip').removeClass('active');
            $('.quick-filter-chip[data-range="all"]').addClass('active');
            $('#filter_start_date').val('');
            $('#filter_end_date').val('');
            table.search('').draw();
        });

        let currentActiveId = null;

        // Open Show Modal (Digital Slip)
        $(document).on('click', '.btn-show-income, .btn-preview-proof', function () {
            const id = $(this).data('id');
            currentActiveId = id;
            $('#modalShowIncome').modal('show');
            $('#show_modal_body').html('<div class="text-center py-5 text-muted"><div class="spinner-border spinner-border-sm text-success me-2" role="status"></div> Memuat slip transaksi digital...</div>');

            $.get("{{ url('cash-incomes') }}/" + id, function (res) {
                if (res.status === 'success') {
                    const data = res.data;
                    $('#show_transaction_number').text(data.transaction_number);
                    
                    let proofHtml = '<span class="text-muted small fst-italic">Tidak ada lampiran berkas</span>';
                    if (data.proof_url) {
                        if (data.proof_is_image) {
                            proofHtml = `
                                <div class="text-center">
                                    <img src="${data.proof_url}" alt="Bukti Transfer" class="img-fluid rounded border shadow-sm mb-2" style="max-height: 220px;">
                                    <div>
                                        <a href="${data.proof_url}" target="_blank" class="btn btn-sm btn-outline-primary px-3 py-1">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="me-1"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 6h-6a2 2 0 0 0 -2 2v10a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-6" /><path d="M11 13l9 -9" /><path d="M15 4h5v5" /></svg>
                                            Buka Berkas Asli (HD)
                                        </a>
                                    </div>
                                </div>
                            `;
                        } else {
                            proofHtml = `
                                <div class="text-center py-3">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="mb-2"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M14 3v4a1 1 0 0 0 1 1h4" /><path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z" /></svg>
                                    <div class="fw-semibold text-dark mb-2">Dokumen Faktur / Bukti PDF Tersedia</div>
                                    <a href="${data.proof_url}" target="_blank" class="btn btn-sm btn-primary px-3 py-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="me-1"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2 -2v-2" /><path d="M7 11l5 5l5 -5" /><path d="M12 4l0 12" /></svg>
                                        Unduh / Buka Dokumen PDF
                                    </a>
                                </div>
                            `;
                        }
                    }

                    const html = `
                        <div class="digital-slip">
                            <div class="digital-slip-header-income text-center">
                                <span class="badge bg-white text-success fw-bold px-2.5 py-1 mb-2">BUKTI MUTASI KAS MASUK</span>
                                <div class="text-white-50 small font-monospace">TOTAL BERSIH DITERIMA</div>
                                <div class="h1 fw-bold font-monospace text-white my-1" style="letter-spacing: -0.02em;">Rp ${data.net_amount_formatted}</div>
                                <div class="small text-white-50 font-monospace">${data.transaction_number} &bull; ${data.transaction_date_formatted}</div>
                            </div>

                            <div class="p-4 bg-white">
                                <div class="digital-slip-row">
                                    <span class="digital-slip-label">Dari Rekening (Pengirim)</span>
                                    <span class="digital-slip-value text-dark fs-4">${data.sender_name}</span>
                                </div>
                                <div class="digital-slip-row">
                                    <span class="digital-slip-label">Bank / Saluran</span>
                                    <span class="digital-slip-value"><span class="badge bg-secondary-lt font-monospace fw-bold">${data.bank_name}</span></span>
                                </div>
                                <div class="digital-slip-row">
                                    <span class="digital-slip-label">Nomor Rekening</span>
                                    <span class="digital-slip-value font-monospace">${data.account_number}</span>
                                </div>
                                <div class="digital-slip-row">
                                    <span class="digital-slip-label">Dicatat Oleh</span>
                                    <span class="digital-slip-value">${data.creator_name} <span class="text-muted small">(${data.created_at_formatted})</span></span>
                                </div>

                                <div class="p-3 my-3 rounded-3" style="background-color: #f8fafc; border: 1px solid #e2e8f0;">
                                    <div class="d-flex justify-content-between mb-1.5 small">
                                        <span class="text-muted">Nominal Pokok Masuk:</span>
                                        <span class="font-monospace fw-bold text-success">+ Rp ${data.amount_formatted}</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-1.5 small">
                                        <span class="text-muted">Potongan Biaya Admin:</span>
                                        <span class="font-monospace fw-bold ${data.has_admin_fee ? 'text-warning' : 'text-muted'}">- Rp ${data.admin_fee_formatted}</span>
                                    </div>
                                    <hr class="my-2">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="fw-bold text-dark">Total Bersih Kas:</span>
                                        <span class="h3 fw-bold font-monospace text-primary mb-0">Rp ${data.net_amount_formatted}</span>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <div class="text-muted small fw-bold text-uppercase mb-1" style="font-size: 0.72rem;">Catatan Transaksi:</div>
                                    <div class="p-2.5 rounded-2 bg-light text-dark small" style="white-space: pre-line;">${data.notes || '-'}</div>
                                </div>

                                <div>
                                    <div class="text-muted small fw-bold text-uppercase mb-2" style="font-size: 0.72rem;">Lampiran Bukti Transaksi:</div>
                                    <div class="p-3 border rounded-3 bg-white">
                                        ${proofHtml}
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                    $('#show_modal_body').html(html);
                }
            });
        });

        // Show modal to Edit modal transition
        $('#btn-show-to-edit').on('click', function () {
            $('#modalShowIncome').modal('hide');
            if (currentActiveId) {
                openEditModal(currentActiveId);
            }
        });

        // Open Edit Modal function
        function openEditModal(id) {
            $.get("{{ url('cash-incomes') }}/" + id + "/edit", function (res) {
                if (res.status === 'success') {
                    const data = res.data;
                    $('#edit_transaction_number').text(data.transaction_number);
                    $('#formEditIncome').attr('action', data.update_url);
                    $('#edit_sender_name').val(data.sender_name);
                    
                    // Set Bank in Select2
                    if ($('#select_bank_edit').find("option[value='" + data.bank_name + "']").length) {
                        $('#select_bank_edit').val(data.bank_name).trigger('change');
                    } else {
                        // Custom bank tag
                        var newOption = new Option(data.bank_name, data.bank_name, true, true);
                        $('#select_bank_edit').append(newOption).trigger('change');
                    }

                    $('#edit_account_number').val(data.account_number);
                    $('#edit_amount').val(data.amount);
                    $('#edit_transaction_date').val(data.transaction_date);
                    $('#edit_notes').val(data.notes);

                    if (data.has_admin_fee === 'ya') {
                        setFeeMode('edit', true);
                        $('#edit_admin_fee').val(data.admin_fee);
                    } else {
                        setFeeMode('edit', false);
                        $('#edit_admin_fee').val('0');
                    }

                    updateLiveCalculation('edit');

                    if (data.proof_url) {
                        $('#edit_proof_preview').removeClass('d-none').html(`File saat ini: <a href="${data.proof_url}" target="_blank" class="fw-semibold text-primary">Lihat Lampiran Tersimpan</a>`);
                    } else {
                        $('#edit_proof_preview').addClass('d-none').html('');
                    }

                    $('#modalEditIncome').modal('show');
                }
            });
        }

        $(document).on('click', '.btn-edit-income', function () {
            const id = $(this).data('id');
            openEditModal(id);
        });

        // Delete Income
        $(document).on('click', '.btn-delete-income', function () {
            const id = $(this).data('id');
            const name = $(this).data('name');

            Swal.fire({
                title: "Konfirmasi Hapus",
                text: "Apakah Anda yakin ingin menghapus catatan pemasukan dari " + name + "?",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#16a34a",
                cancelButtonColor: "#ef4444",
                confirmButtonText: "Ya, Hapus",
                cancelButtonText: "Batal"
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ url('cash-incomes') }}/" + id,
                        type: "DELETE",
                        success: function (response) {
                            Swal.fire({
                                icon: "success",
                                title: "Berhasil",
                                text: response.message || "Data pemasukan berhasil dihapus.",
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
        // Download Excel with active date filters
        $('#btn-export-income-excel').on('click', function () {
            const startDate = $('#filter_start_date').val();
            const endDate = $('#filter_end_date').val();
            const baseUrl = "{{ route('cash-incomes.export') }}";
            const params = new URLSearchParams();
            if (startDate) params.set('start_date', startDate);
            if (endDate) params.set('end_date', endDate);
            const query = params.toString();
            $(this).attr('href', query ? `${baseUrl}?${query}` : baseUrl);
        });
    });
</script>
@endpush
@endsection
