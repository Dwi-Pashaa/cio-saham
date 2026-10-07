@extends('layouts.app')

@section('pretitle', 'KAS & ARUS DANA')
@section('title', 'Detail Saldo Masuk')
@section('subtitle', 'Rincian transaksi pemasukan ' . $income->transaction_number)

@section('actions')
    <a href="{{ route('cash-incomes.index') }}" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-1">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l14 0" /><path d="M5 12l6 6" /><path d="M5 12l6 -6" /></svg>
        <span>Kembali</span>
    </a>
    <a href="{{ route('cash-incomes.edit', $income->id) }}" class="btn btn-primary btn-sm d-inline-flex align-items-center gap-1">
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
                        <h4 class="card-title fw-bold text-dark mb-0">Informasi Mutasi Masuk</h4>
                        <span class="badge bg-blue-lt font-monospace px-2.5 py-1 fs-5 fw-bold">{{ $income->transaction_number }}</span>
                    </div>
                </div>
                <div class="card-body p-4">
                    <dl class="row mb-0 gy-3">
                        <dt class="col-sm-4 text-muted">Tanggal Transaksi:</dt>
                        <dd class="col-sm-8 fw-bold font-monospace text-dark fs-4">
                            {{ $income->transaction_date ? $income->transaction_date->translatedFormat('d F Y') : '-' }}
                        </dd>

                        <dt class="col-sm-4 text-muted">Dari Rekening:</dt>
                        <dd class="col-sm-8 fw-bold text-dark fs-4">{{ $income->sender_name }}</dd>

                        <dt class="col-sm-4 text-muted">Rekening / Bank Tujuan:</dt>
                        <dd class="col-sm-8">
                            <span class="badge bg-secondary-lt font-monospace fw-bold">{{ $income->bank_name }}</span>
                            <span class="font-monospace text-dark ms-1.5 fw-bold">{{ $income->account_number }}</span>
                        </dd>

                        <dt class="col-sm-4 text-muted">Nominal Pokok:</dt>
                        <dd class="col-sm-8 font-monospace fw-bold text-success fs-3">
                            +Rp {{ number_format($income->amount, 0, ',', '.') }}
                        </dd>

                        <dt class="col-sm-4 text-muted">Biaya Admin:</dt>
                        <dd class="col-sm-8 font-monospace fw-bold {{ $income->admin_fee > 0 ? 'text-warning' : 'text-muted' }}">
                            Rp {{ number_format($income->admin_fee, 0, ',', '.') }}
                            @if($income->has_admin_fee && $income->admin_fee > 0)
                                <span class="badge bg-warning-lt ms-1">Ada Admin</span>
                            @else
                                <span class="badge bg-light text-muted ms-1">Tanpa Admin</span>
                            @endif
                        </dd>

                        <dt class="col-sm-4 text-muted">Bersih Diterima:</dt>
                        <dd class="col-sm-8 font-monospace fw-bold text-primary fs-3">
                            Rp {{ number_format($income->net_amount, 0, ',', '.') }}
                        </dd>

                        <dt class="col-sm-4 text-muted">Catatan Transaksi:</dt>
                        <dd class="col-sm-8 text-dark bg-light p-2.5 rounded-3">
                            {{ $income->notes }}
                        </dd>

                        <dt class="col-sm-4 text-muted">Dicatat Oleh:</dt>
                        <dd class="col-sm-8 text-muted small">
                            {{ $income->creator->name ?? 'Sistem' }} ({{ $income->created_at ? $income->created_at->format('d M Y H:i') : '-' }})
                        </dd>
                    </dl>
                </div>
            </div>
        </div>

        <!-- Bukti Transaksi -->
        <div class="col-lg-5">
            <div class="card shadow-sm border-0 rounded-3 h-100">
                <div class="card-header py-3.5 px-4 bg-white border-bottom">
                    <h4 class="card-title fw-bold text-dark mb-0">Bukti Transaksi</h4>
                </div>
                <div class="card-body p-4 text-center">
                    @if($income->proof_file)
                        @php
                            $isPdf = str_ends_with(strtolower($income->proof_file), '.pdf');
                        @endphp
                        @if($isPdf)
                            <div class="p-4 bg-light rounded-3 text-center mb-3">
                                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-danger mb-2"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M14 3v4a1 1 0 0 0 1 1h4" /><path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z" /><path d="M9 17h6" /><path d="M9 13h6" /></svg>
                                <div class="fw-bold text-dark">Dokumen Bukti PDF</div>
                                <div class="text-muted small">Klik tombol di bawah untuk membuka dokumen</div>
                            </div>
                        @else
                            <a href="{{ $income->proof_url }}" target="_blank">
                                <img src="{{ $income->proof_url }}" alt="Bukti Transaksi" class="img-fluid rounded border shadow-sm mb-3" style="max-height: 380px; object-fit: contain;">
                            </a>
                        @endif

                        <a href="{{ $income->proof_url }}" target="_blank" class="btn btn-outline-primary d-inline-flex align-items-center gap-1.5 w-100 justify-content-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 12a2 2 0 1 0 4 0a2 2 0 0 0 -4 0" /><path d="M21 12c-2.4 4 -5.4 6 -9 6c-3.6 0 -6.6 -2 -9 -6c2.4 -4 5.4 -6 9 -6c3.6 0 6.6 2 9 6" /></svg>
                            <span>Buka Bukti Full Size</span>
                        </a>
                    @else
                        <div class="py-5 text-muted">
                            <em>Tidak ada berkas bukti transaksi terlampir.</em>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
