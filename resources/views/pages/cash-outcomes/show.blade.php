@extends('layouts.app')

@section('pretitle', 'KAS & ARUS DANA')
@section('title', 'Detail Saldo Keluar')
@section('subtitle', 'Rincian transaksi pengeluaran ' . $outcome->transaction_number)

@section('actions')
    <a href="{{ route('cash-outcomes.index') }}" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-1">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l14 0" /><path d="M5 12l6 6" /><path d="M5 12l6 -6" /></svg>
        <span>Kembali</span>
    </a>
    <a href="{{ route('cash-outcomes.edit', $outcome->id) }}" class="btn btn-primary btn-sm d-inline-flex align-items-center gap-1">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 20h4l10.5 -10.5a2.828 2.828 0 1 0 -4 -4l-10.5 10.5v4" /><path d="M13.5 6.5l4 4" /></svg>
        <span>Ubah Data</span>
    </a>
@endsection

@section('content')
<div class="container-xl py-2">
    <div class="row g-3">
        <!-- Main Details -->
        <div class="col-lg-7">
            <div class="card shadow-sm border-0 rounded-3 h-100">
                <div class="card-header py-3.5 px-4 bg-white border-bottom">
                    <div class="d-flex justify-content-between align-items-center w-100">
                        <h4 class="card-title fw-bold text-dark mb-0">Informasi Mutasi Keluar</h4>
                        <span class="badge bg-red-lt font-monospace px-2.5 py-1 fs-5 fw-bold">{{ $outcome->transaction_number }}</span>
                    </div>
                </div>
                <div class="card-body p-4">
                    <dl class="row mb-0 gy-3">
                        <dt class="col-sm-4 text-muted">Tanggal Transaksi:</dt>
                        <dd class="col-sm-8 fw-bold font-monospace text-dark fs-4">
                            {{ $outcome->transaction_date ? $outcome->transaction_date->translatedFormat('d F Y') : '-' }}
                        </dd>

                        <dt class="col-sm-4 text-muted">Rekening Tujuan:</dt>
                        <dd class="col-sm-8 fw-bold text-dark fs-4">{{ $outcome->recipient_name }}</dd>

                        <dt class="col-sm-4 text-muted">Rekening / Bank:</dt>
                        <dd class="col-sm-8">
                            <span class="badge bg-secondary-lt font-monospace fw-bold">{{ $outcome->bank_name }}</span>
                            <span class="font-monospace text-dark ms-1.5 fw-bold">{{ $outcome->account_number }}</span>
                        </dd>

                        <dt class="col-sm-4 text-muted">Nominal Pokok:</dt>
                        <dd class="col-sm-8 font-monospace fw-bold text-dark fs-3">
                            Rp {{ number_format($outcome->amount, 0, ',', '.') }}
                        </dd>

                        <dt class="col-sm-4 text-muted">Biaya Admin:</dt>
                        <dd class="col-sm-8 font-monospace fw-bold {{ $outcome->admin_fee > 0 ? 'text-warning' : 'text-muted' }}">
                            Rp {{ number_format($outcome->admin_fee, 0, ',', '.') }}
                            @if($outcome->has_admin_fee && $outcome->admin_fee > 0)
                                <span class="badge bg-warning-lt ms-1">Ada Admin</span>
                            @else
                                <span class="badge bg-light text-muted ms-1">Tanpa Admin</span>
                            @endif
                        </dd>

                        <dt class="col-sm-4 text-muted">Total Pengeluaran:</dt>
                        <dd class="col-sm-8 font-monospace fw-bold text-danger fs-3">
                            -Rp {{ number_format($outcome->total_amount, 0, ',', '.') }}
                        </dd>

                        <dt class="col-sm-4 text-muted">Tipe Pengeluaran:</dt>
                        <dd class="col-sm-8">
                            @if($outcome->is_asset)
                                <span class="badge bg-purple text-white fw-bold font-monospace px-2.5 py-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="me-1"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l5 5l10 -10" /></svg>
                                    Aset Perusahaan (Capex)
                                </span>
                            @else
                                <span class="badge bg-light text-muted font-monospace px-2.5 py-1">
                                    Beban Operasional / Rutin (Opex)
                                </span>
                            @endif
                        </dd>

                        <dt class="col-sm-4 text-muted">Catatan Transaksi:</dt>
                        <dd class="col-sm-8 text-dark bg-light p-2.5 rounded-3">
                            {{ $outcome->notes }}
                        </dd>

                        <dt class="col-sm-4 text-muted">Dicatat Oleh:</dt>
                        <dd class="col-sm-8 text-muted small">
                            {{ $outcome->creator->name ?? 'Sistem' }} ({{ $outcome->created_at ? $outcome->created_at->format('d M Y H:i') : '-' }})
                        </dd>
                    </dl>
                </div>
            </div>
        </div>

        <!-- Bukti & Nota -->
        <div class="col-lg-5">
            <div class="card shadow-sm border-0 rounded-3 mb-3">
                <div class="card-header py-3 px-4 bg-white border-bottom">
                    <h4 class="card-title fw-bold text-dark mb-0">Bukti Transfer (Wajib)</h4>
                </div>
                <div class="card-body p-3 text-center">
                    @if($outcome->proof_file)
                        @php
                            $isPdfProof = str_ends_with(strtolower($outcome->proof_file), '.pdf');
                        @endphp
                        @if($isPdfProof)
                            <div class="p-3 bg-light rounded-3 text-center mb-2">
                                <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-danger mb-1"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M14 3v4a1 1 0 0 0 1 1h4" /><path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z" /><path d="M9 17h6" /><path d="M9 13h6" /></svg>
                                <div class="fw-bold text-dark small">Dokumen Bukti Transfer PDF</div>
                            </div>
                        @else
                            <a href="{{ $outcome->proof_url }}" target="_blank">
                                <img src="{{ $outcome->proof_url }}" alt="Bukti Transfer" class="img-fluid rounded border shadow-sm mb-2" style="max-height: 200px; object-fit: contain;">
                            </a>
                        @endif

                        <a href="{{ $outcome->proof_url }}" target="_blank" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1 w-100 justify-content-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 12a2 2 0 1 0 4 0a2 2 0 0 0 -4 0" /><path d="M21 12c-2.4 4 -5.4 6 -9 6c-3.6 0 -6.6 -2 -9 -6c2.4 -4 5.4 -6 9 -6c3.6 0 6.6 2 9 6" /></svg>
                            <span>Buka Bukti Transfer</span>
                        </a>
                    @else
                        <div class="text-muted small py-3">Tidak ada berkas bukti transfer.</div>
                    @endif
                </div>
            </div>

            <!-- Nota Pembelian -->
            <div class="card shadow-sm border-0 rounded-3">
                <div class="card-header py-3 px-4 bg-white border-bottom">
                    <h4 class="card-title fw-bold text-dark mb-0">Nota / Struk Pembelian (Opsional)</h4>
                </div>
                <div class="card-body p-3 text-center">
                    @if($outcome->receipt_file)
                        @php
                            $isPdfReceipt = str_ends_with(strtolower($outcome->receipt_file), '.pdf');
                        @endphp
                        @if($isPdfReceipt)
                            <div class="p-3 bg-light rounded-3 text-center mb-2">
                                <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-success mb-1"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M14 3v4a1 1 0 0 0 1 1h4" /><path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z" /><path d="M9 17h6" /><path d="M9 13h6" /></svg>
                                <div class="fw-bold text-dark small">Dokumen Nota PDF</div>
                            </div>
                        @else
                            <a href="{{ $outcome->receipt_url }}" target="_blank">
                                <img src="{{ $outcome->receipt_url }}" alt="Nota Pembelian" class="img-fluid rounded border shadow-sm mb-2" style="max-height: 200px; object-fit: contain;">
                            </a>
                        @endif

                        <a href="{{ $outcome->receipt_url }}" target="_blank" class="btn btn-sm btn-outline-success d-inline-flex align-items-center gap-1 w-100 justify-content-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 12a2 2 0 1 0 4 0a2 2 0 0 0 -4 0" /><path d="M21 12c-2.4 4 -5.4 6 -9 6c-3.6 0 -6.6 -2 -9 -6c2.4 -4 5.4 -6 9 -6c3.6 0 6.6 2 9 6" /></svg>
                            <span>Buka Nota Pembelian</span>
                        </a>
                    @else
                        <div class="text-muted small py-3">Tidak ada nota pembelian yang dilampirkan.</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
