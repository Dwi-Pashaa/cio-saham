@extends('layouts.app')

@section('pretitle', 'DETAIL PORTOFOLIO')
@section('title', 'Detail Pemilik Saham: ' . $shareholder->name)
@section('subtitle', 'ID Pemilik: #' . str_pad($shareholder->id, 4, '0', STR_PAD_LEFT) . ' • Didaftarkan: ' . $shareholder->created_at->format('d M Y'))

@section('actions')
    <a href="{{ route('shareholders.index') }}" class="btn btn-outline-secondary btn-sm">
        &larr; Kembali ke Daftar
    </a>
    @can('edit-shareholder')
        <a href="{{ route('shareholders.edit', $shareholder->id) }}" class="btn btn-outline-primary btn-sm">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M7 7h-1a2 2 0 0 0 -2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2 -2v-1" /><path d="M20.385 6.585a2.1 2.1 0 0 0 -2.97 -2.97l-8.415 8.415v3h3l8.415 -8.415z" /><path d="M16 5l3 3" /></svg>
            Edit Profil
        </a>
    @endcan
@endsection

@section('content')
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<!-- 1. Profil Pemilik Saham (1 User Identity) -->
@include('components.shareholder-card', ['shareholder' => $shareholder])

<!-- 2. Ringkasan Ekuitas Pemilik Ini -->
<div class="row row-cards mb-4">
    <div class="col-sm-6 col-lg-3">
        <div class="card p-3 border shadow-sm h-100 bg-white">
            <span class="text-muted small fw-bold text-uppercase tracking-wider">Instrumen Saham</span>
            <strong class="h2 mb-0 text-dark mt-1 font-monospace">{{ $shareholder->holdings->count() }} Data Saham</strong>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card p-3 border shadow-sm h-100 bg-white">
            <span class="text-muted small fw-bold text-uppercase tracking-wider">Total Lembar Dimiliki</span>
            <strong class="h2 mb-0 text-primary mt-1 font-monospace">{{ number_format($shareholder->total_shares, 0, ',', '.') }} Lembar</strong>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card p-3 border shadow-sm h-100 bg-white">
            <span class="text-muted small fw-bold text-uppercase tracking-wider">Total Nilai Investasi</span>
            <strong class="h2 mb-0 text-success mt-1 font-monospace">Rp {{ number_format($shareholder->total_investment, 0, ',', '.') }}</strong>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card p-3 border shadow-sm h-100 bg-white">
            <span class="text-muted small fw-bold text-uppercase tracking-wider">Porsi Kepemilikan</span>
            <strong class="h2 mb-0 text-info mt-1 font-monospace">{{ number_format($shareholder->total_percentage, 2) }}%</strong>
        </div>
    </div>
</div>

<!-- 3. Daftar Kepemilikan Saham (Banyak Saham / 1-to-Many) -->
@include('components.share-holding-list', ['shareholder' => $shareholder])

<!-- 4. Modals (Tambah & Edit Saham) -->
@include('components.share-holding-modal', ['shareholder' => $shareholder])

@endsection
