@extends('layouts.app')

@section('pretitle', 'DIREKTORI PORTOFOLIO')
@section('title', 'Portofolio Saham: ' . $shareholder->name)
@section('subtitle', 'ID Investor: #' . str_pad($shareholder->id, 4, '0', STR_PAD_LEFT) . ' • Bergabung: ' . $shareholder->created_at->format('d M Y'))

@section('actions')
    <a href="{{ route('investor-directory.index') }}" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-1">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l14 0" /><path d="M5 12l6 6" /><path d="M5 12l6 -6" /></svg>
        <span>Kembali ke Direktori</span>
    </a>
@endsection

@section('content')
<!-- 1. Profil Ringkas Pemegang Saham -->
<div class="card border shadow-sm mb-4 bg-white">
    <div class="card-body p-4">
        <div class="row align-items-center g-3">
            <div class="col-auto">
                <div class="shareholder-avatar-circle" style="width: 56px; height: 56px; font-size: 1.35rem;">
                    {{ strtoupper(substr($shareholder->name, 0, 2)) }}
                </div>
            </div>
            <div class="col">
                <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                    <h2 class="card-title h2 mb-0 fw-bold text-dark">{{ $shareholder->name }}</h2>
                    @if($shareholder->status === 'active')
                        <span class="badge bg-success-lt text-success fw-bold d-inline-flex align-items-center gap-1">
                            <span class="pulse-live-dot" style="width: 6px; height: 6px;"></span>
                            Investor Aktif
                        </span>
                    @else
                        <span class="badge bg-danger-lt text-danger fw-bold">Non-Aktif</span>
                    @endif
                    <span class="badge bg-blue-lt font-monospace fw-bold">ID: #{{ str_pad($shareholder->id, 4, '0', STR_PAD_LEFT) }}</span>
                </div>
                <div class="d-flex flex-wrap align-items-center gap-3 text-muted small mt-1">
                    @if($shareholder->email)
                        <span class="d-inline-flex align-items-center gap-1">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 7a2 2 0 0 1 2 -2h14a2 2 0 0 1 2 2v10a2 2 0 0 1 -2 2h-14a2 2 0 0 1 -2 -2v-10z" /><path d="M3 7l9 6l9 -6" /></svg>
                            {{ $shareholder->email }}
                        </span>
                    @endif
                    <span class="d-inline-flex align-items-center gap-1">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 5m0 2a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v12a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2z" /><path d="M16 3l0 4" /><path d="M8 3l0 4" /><path d="M4 11l16 0" /></svg>
                        Terdaftar Sejak: {{ $shareholder->created_at->format('d M Y') }}
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 2. Ringkasan Ekuitas Pemilik Ini -->
<div class="row row-cards mb-4">
    <div class="col-sm-6 col-lg-3">
        <div class="card p-3 border shadow-sm h-100 bg-white">
            <span class="text-muted small fw-bold text-uppercase tracking-wider">Instrumen Saham</span>
            <strong class="h2 mb-0 text-dark mt-1 font-monospace">{{ $shareholder->holdings->count() }} Data Saham</strong>
            <div class="text-muted small mt-1">Alokasi portofolio</div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card p-3 border shadow-sm h-100 bg-white">
            <span class="text-muted small fw-bold text-uppercase tracking-wider">Total Lembar Dimiliki</span>
            <strong class="h2 mb-0 text-primary mt-1 font-monospace">{{ number_format($shareholder->total_shares, 0, ',', '.') }} Lembar</strong>
            <div class="text-muted small mt-1">Sirkulasi unit saham</div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card p-3 border shadow-sm h-100 bg-white">
            <span class="text-muted small fw-bold text-uppercase tracking-wider">Total Nilai Investasi</span>
            <strong class="h2 mb-0 text-success mt-1 font-monospace">Rp {{ number_format($shareholder->total_investment, 0, ',', '.') }}</strong>
            <div class="text-muted small mt-1">Akumulasi modal disetor</div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card p-3 border shadow-sm h-100 bg-white">
            <span class="text-muted small fw-bold text-uppercase tracking-wider">Porsi Kepemilikan</span>
            <strong class="h2 mb-0 text-info mt-1 font-monospace">{{ number_format($shareholder->total_percentage, 2) }}%</strong>
            <div class="text-muted small mt-1">Dari total ekuitas perusahaan</div>
        </div>
    </div>
</div>

<!-- 3. Daftar Kepemilikan Saham (Portofolio Read-Only) -->
<div class="card border shadow-sm mb-4 bg-white">
    <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-3 bg-white py-3">
        <div>
            <h3 class="card-title fw-bold mb-0 text-dark">Rincian Alokasi Instrumen Saham</h3>
            <span class="text-muted small">
                Pemegang saham ini memiliki <strong>{{ $shareholder->holdings->count() }}</strong> instrumen saham terdaftar.
            </span>
        </div>
        <span class="badge bg-azure-lt fw-bold font-monospace">Mode Pratinjau (Read-Only)</span>
    </div>

    @if($shareholder->holdings->isEmpty())
        <div class="card-body text-center py-5 bg-white">
            <div class="text-muted mb-3">
                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-coin" width="48" height="48" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0" /><path d="M14.8 9a2 2 0 0 0 -1.8 -1h-2a2 2 0 1 0 0 4h2a2 2 0 1 1 0 4h-2a2 2 0 0 1 -1.8 -1" /><path d="M12 7v10" /></svg>
            </div>
            <h4 class="text-dark">Belum Ada Data Instrumen Saham</h4>
            <p class="text-muted small">Pemegang saham ini belum memiliki instrumen alokasi saham aktif.</p>
        </div>
    @else
        <div class="table-responsive">
            <table class="table table-vcenter card-table table-hover">
                <thead>
                    <tr>
                        <th style="min-width: 140px;">Kode Saham</th>
                        <th style="min-width: 200px;">Entitas / Unit Usaha</th>
                        <th class="text-end" style="min-width: 150px;">Jumlah Lembar</th>
                        <th class="text-end" style="min-width: 150px;">Nilai Nominal / Par</th>
                        <th class="text-end" style="min-width: 170px;">Total Nilai Investasi</th>
                        <th class="text-center" style="min-width: 120px;">Porsi (%)</th>
                        <th style="min-width: 130px;">Tgl Akuisisi</th>
                        <th class="text-center" style="min-width: 110px;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($shareholder->holdings as $holding)
                        <tr>
                            <td>
                                <span class="badge bg-blue-lt fw-bold font-monospace">{{ $holding->share_code }}</span>
                                @if($holding->certificate_number)
                                    <div class="text-muted small font-monospace mt-0.5">Sertifikat: {{ $holding->certificate_number }}</div>
                                @endif
                            </td>
                            <td>
                                <strong class="text-dark">{{ $holding->entity_name }}</strong>
                            </td>
                            <td class="text-end fw-bold font-monospace">
                                {{ number_format($holding->total_shares, 0, ',', '.') }} Lembar
                            </td>
                            <td class="text-end text-muted font-monospace">
                                Rp {{ number_format($holding->nominal_value_per_share, 0, ',', '.') }}
                            </td>
                            <td class="text-end fw-bold text-primary font-monospace">
                                Rp {{ number_format($holding->total_investment, 0, ',', '.') }}
                            </td>
                            <td class="text-center">
                                <span class="badge bg-success-lt fw-bold font-monospace">
                                    {{ number_format($holding->percentage_share, 2) }}%
                                </span>
                            </td>
                            <td class="text-muted small">
                                {{ $holding->acquisition_date ? $holding->acquisition_date->format('d M Y') : '-' }}
                            </td>
                            <td class="text-center">
                                @if($holding->status === 'active')
                                    <span class="badge bg-success-lt text-success fw-bold">
                                        <span class="pulse-live-dot me-1" style="width: 6px; height: 6px;"></span> Aktif
                                    </span>
                                @elseif($holding->status === 'transferred')
                                    <span class="badge bg-warning-lt text-warning fw-bold">Dialihkan</span>
                                @else
                                    <span class="badge bg-danger-lt text-danger fw-bold">Terjual</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="table-light fw-bold">
                        <td colspan="2" class="text-dark">TOTAL PORTOFOLIO:</td>
                        <td class="text-end font-monospace text-dark">{{ number_format($shareholder->total_shares, 0, ',', '.') }} Lembar</td>
                        <td class="text-end">-</td>
                        <td class="text-end text-primary font-monospace">Rp {{ number_format($shareholder->total_investment, 0, ',', '.') }}</td>
                        <td class="text-center text-success font-monospace">{{ number_format($shareholder->total_percentage, 2) }}%</td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    @endif
</div>
@endsection
