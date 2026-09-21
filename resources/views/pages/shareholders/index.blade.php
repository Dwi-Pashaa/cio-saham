@extends('layouts.app')

@section('pretitle', 'MANAJEMEN EKUITAS')
@section('title', 'Manajemen Pemilik Saham')
@section('subtitle', 'Pengelolaan profil pemilik saham dengan relasi 1 Pemilik dapat memiliki banyak instrumen saham (One-to-Many).')

@section('actions')
    @can('create-shareholder')
        <a href="{{ route('shareholders.create') }}" class="btn btn-primary d-inline-flex align-items-center gap-1.5 shadow-sm">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M8 7a4 4 0 1 0 8 0a4 4 0 0 0 -8 0" /><path d="M6 21v-2a4 4 0 0 1 4 -4h4c.342 0 .674 .043 .99 .124" /><path d="M16 19h6" /><path d="M19 16v6" /></svg>
            <span>Tambah Pemilik Saham</span>
        </a>
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

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm d-flex align-items-center gap-2 mb-4" role="alert">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="icon text-danger flex-shrink-0"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 9v4" /><path d="M12 17h.01" /><path d="M12 3c7.2 0 9 1.8 9 9s-1.8 9 -9 9s-9 -1.8 -9 -9s1.8 -9 9 -9z" /></svg>
        <div>{{ session('error') }}</div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

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
                <div class="finance-stat-icon" style="background-color: var(--saham-blue-ice); color: var(--saham-blue-royal);">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9 7m-4 0a4 4 0 1 0 8 0a4 4 0 1 0 -8 0" /><path d="M3 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2" /><path d="M16 3.13a4 4 0 0 1 0 7.75" /><path d="M21 21v-2a4 4 0 0 0 -3 -3.85" /></svg>
                </div>
            </div>
            <div class="d-flex align-items-center gap-1.5 mt-2 pt-2 border-top border-light-subtle small">
                <span class="badge bg-blue-lt fw-bold font-monospace">100% Terverifikasi</span>
                <span class="text-muted text-truncate" style="font-size: 0.775rem;">Entitas terdaftar</span>
            </div>
        </div>
    </div>

    <!-- Card 2: Total Portofolio Saham -->
    <div class="col-sm-6 col-lg-3">
        <div class="finance-stat-card">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <div class="overflow-hidden pe-2">
                    <span class="text-muted small fw-bold text-uppercase tracking-wider" style="font-size: 0.72rem;">Total Portofolio Saham</span>
                    <h3 class="h2 mb-0 fw-bold mt-1 text-dark font-monospace">{{ $equitySummary['total_holdings'] ?? 0 }} <span class="fs-4 fw-medium text-muted">Instrumen</span></h3>
                </div>
                <div class="finance-stat-icon" style="background-color: #f0f9ff; color: #0284c7;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 7m0 2a2 2 0 0 1 2 -2h14a2 2 0 0 1 2 2v9a2 2 0 0 1 -2 2h-14a2 2 0 0 1 -2 -2z" /><path d="M8 7v-2a2 2 0 0 1 2 -2h4a2 2 0 0 1 2 2v2" /><path d="M12 12l0 .01" /><path d="M3 13a20 20 0 0 0 18 0" /></svg>
                </div>
            </div>
            <div class="d-flex align-items-center gap-1.5 mt-2 pt-2 border-top border-light-subtle small">
                <span class="badge bg-azure-lt fw-bold font-monospace">Multi-Instrumen</span>
                <span class="text-muted text-truncate" style="font-size: 0.775rem;">1 Pemilik > 1 Saham</span>
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
                <span class="badge bg-purple-lt fw-bold font-monospace">Beredar</span>
                <span class="text-muted text-truncate" style="font-size: 0.775rem;">Sirkulasi lembar aktif</span>
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
                <div class="finance-stat-icon" style="background-color: var(--saham-bullish-bg); color: var(--saham-bullish);">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M17 8v-3a1 1 0 0 0 -1 -1h-10a2 2 0 0 0 0 4h12a1 1 0 0 1 1 1v3m0 4v3a1 1 0 0 1 -1 1h-12a2 2 0 0 1 -2 -2v-12" /><path d="M20 12v4h-4a2 2 0 0 1 0 -4h4" /></svg>
                </div>
            </div>
            <div class="d-flex align-items-center gap-1.5 mt-2 pt-2 border-top border-light-subtle small">
                <span class="badge bg-success-lt fw-bold font-monospace">Equity Inflow</span>
                <span class="text-muted text-truncate" style="font-size: 0.775rem;">Nilai modal investasi</span>
            </div>
        </div>
    </div>
</div>

<!-- Table Card -->
<div class="card border shadow-sm bg-white">
    <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-3 bg-white py-3">
        <div class="d-flex align-items-center gap-2">
            <span class="pulse-live-dot"></span>
            <h3 class="card-title fw-bold mb-0 text-dark">Daftar Pemegang Saham</h3>
            <span class="badge bg-blue-lt fw-bold">{{ $shareholders->total() }} Data Terdaftar</span>
        </div>

        <!-- Search & Filter Form (Responsive & Neat) -->
        <form action="{{ route('shareholders.index') }}" method="GET" class="d-flex flex-wrap align-items-center gap-2 m-0">
            <div class="input-icon">
                <span class="input-icon-addon text-muted">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 10m-7 0a7 7 0 1 0 14 0a7 7 0 1 0 -14 0" /><path d="M21 21l-6 -6" /></svg>
                </span>
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Cari nama, NIK, telp..." value="{{ request('search') }}" style="min-width: 210px;">
            </div>
            <select name="status" class="form-select form-select-sm" style="width: auto; min-width: 130px;" onchange="this.form.submit()">
                <option value="">Semua Status</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Non-Aktif</option>
            </select>
            <button type="submit" class="btn btn-sm btn-primary d-inline-flex align-items-center gap-1">
                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 10m-7 0a7 7 0 1 0 14 0a7 7 0 1 0 -14 0" /><path d="M21 21l-6 -6" /></svg>
                <span>Cari</span>
            </button>
            @if(request('search') || request('status'))
                <a href="{{ route('shareholders.index') }}" class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-1" title="Reset filter">
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M20 11a8.1 8.1 0 0 0 -15.5 -2m-.5 -4v4h4" /><path d="M4 13a8.1 8.1 0 0 0 15.5 2m.5 4v-4h-4" /></svg>
                    <span>Reset</span>
                </a>
            @endif
        </form>
    </div>

    @if($shareholders->isEmpty())
        <div class="card-body text-center py-5 bg-white">
            <div class="text-muted mb-3">
                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-user-x text-muted opacity-50" width="56" height="56" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M8 7a4 4 0 1 0 8 0a4 4 0 0 0 -8 0" /><path d="M6 21v-2a4 4 0 0 1 4 -4h3.5" /><path d="M22 22l-5 -5" /><path d="M17 22l5 -5" /></svg>
            </div>
            <h4 class="text-dark fw-bold">Data Pemilik Saham Tidak Ditemukan</h4>
            <p class="text-muted small" style="max-width: 420px; margin: 0 auto 1.25rem;">
                Tidak ada data pemegang saham yang sesuai dengan filter pencarian Anda atau belum ada data yang dimasukkan.
            </p>
            @can('create-shareholder')
                <a href="{{ route('shareholders.create') }}" class="btn btn-primary d-inline-flex align-items-center gap-1.5">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M8 7a4 4 0 1 0 8 0a4 4 0 0 0 -8 0" /><path d="M6 21v-2a4 4 0 0 1 4 -4h4c.342 0 .674 .043 .99 .124" /><path d="M16 19h6" /><path d="M19 16v6" /></svg>
                    <span>Tambah Pemilik Saham Baru</span>
                </a>
            @endcan
        </div>
    @else
        <div class="table-responsive">
            <table class="table table-vcenter card-table table-hover shareholder-table">
                <thead>
                    <tr>
                        <th style="min-width: 220px;">Pemegang Saham</th>
                        <th style="min-width: 160px;">NIK / KTP</th>
                        <th class="text-center" style="min-width: 130px;">Jumlah Saham</th>
                        <th class="text-end" style="min-width: 140px;">Total Lembar</th>
                        <th class="text-end" style="min-width: 160px;">Total Investasi</th>
                        <th class="text-center" style="min-width: 120px;">Total Porsi (%)</th>
                        <th class="text-center" style="min-width: 110px;">Status</th>
                        <th class="text-end" style="min-width: 230px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($shareholders as $sh)
                        <tr>
                            <!-- Pemegang Saham: Avatar + Name + Email -->
                            <td>
                                <div class="d-flex align-items-center gap-2.5">
                                    <div class="shareholder-avatar-circle" style="width: 36px; height: 36px; font-size: 0.85rem;">
                                        {{ strtoupper(substr($sh->name, 0, 2)) }}
                                    </div>
                                    <div class="overflow-hidden">
                                        <a href="{{ route('shareholders.show', $sh->id) }}" class="text-dark fw-bold d-block text-decoration-none hover-primary text-truncate" title="{{ $sh->name }}">
                                            {{ $sh->name }}
                                        </a>
                                        @if($sh->email)
                                            <span class="text-muted small d-block text-truncate" style="font-size: 0.775rem;">{{ $sh->email }}</span>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            <!-- NIK / KTP -->
                            <td>
                                <span class="badge bg-secondary-lt font-monospace px-2 py-1" style="font-size: 0.8rem; letter-spacing: 0.02em;">
                                    {{ $sh->id_card_number }}
                                </span>
                            </td>

                            <!-- Jumlah Saham (Badge with SVG Icon) -->
                            <td class="text-center">
                                <span class="badge bg-purple-lt fw-bold d-inline-flex align-items-center gap-1 font-monospace px-2 py-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 7m0 2a2 2 0 0 1 2 -2h14a2 2 0 0 1 2 2v9a2 2 0 0 1 -2 2h-14a2 2 0 0 1 -2 -2z" /><path d="M8 7v-2a2 2 0 0 1 2 -2h4a2 2 0 0 1 2 2v2" /></svg>
                                    <span>{{ $sh->activeHoldings->count() }} Data Saham</span>
                                </span>
                            </td>

                            <!-- Total Lembar (Strictly One Line Tabular) -->
                            <td class="text-end fw-bold font-monospace" style="white-space: nowrap !important;">
                                <span class="text-dark fs-4">{{ number_format($sh->total_shares, 0, ',', '.') }}</span>
                                <span class="small text-muted fw-normal">Lembar</span>
                            </td>

                            <!-- Total Investasi (Strictly One Line Tabular) -->
                            <td class="text-end fw-bold font-monospace" style="white-space: nowrap !important;">
                                <span class="text-primary fs-4">Rp {{ number_format($sh->total_investment, 0, ',', '.') }}</span>
                            </td>

                            <!-- Total Porsi (%) -->
                            <td class="text-center">
                                <span class="badge bg-success-lt fw-bold font-monospace px-2 py-1" style="font-size: 0.825rem;">
                                    {{ number_format($sh->total_percentage, 2) }}%
                                </span>
                            </td>

                            <!-- Status -->
                            <td class="text-center">
                                @if($sh->status === 'active')
                                    <span class="badge bg-success-lt text-success fw-bold d-inline-flex align-items-center gap-1 px-2 py-1">
                                        <span class="pulse-live-dot" style="width: 6px; height: 6px;"></span>
                                        <span>Aktif</span>
                                    </span>
                                @else
                                    <span class="badge bg-danger-lt text-danger fw-bold px-2 py-1">
                                        Non-Aktif
                                    </span>
                                @endif
                            </td>

                            <!-- Aksi (With Clear SVG Icons & Touch-Friendly Sizing) -->
                            <td class="text-end">
                                <div class="shareholder-action-group justify-content-end">
                                    <!-- Detail & Portofolio Saham -->
                                    <a href="{{ route('shareholders.show', $sh->id) }}" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1" title="Lihat Portofolio Saham Lengkap">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 12a2 2 0 1 0 4 0a2 2 0 0 0 -4 0" /><path d="M21 12c-2.4 4 -5.4 6 -9 6c-3.6 0 -6.6 -2 -9 -6c2.4 -4 5.4 -6 9 -6c3.6 0 6.6 2 9 6" /></svg>
                                        <span>Detail & Saham</span>
                                    </a>

                                    <!-- Edit Profil -->
                                    @can('edit-shareholder')
                                        <a href="{{ route('shareholders.edit', $sh->id) }}" class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-1" title="Edit Profil Pemilik">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 20h4l10.5 -10.5a2.828 2.828 0 1 0 -4 -4l-10.5 10.5v4" /><path d="M13.5 6.5l4 4" /></svg>
                                            <span>Edit</span>
                                        </a>
                                    @endcan

                                    <!-- Hapus -->
                                    @can('delete-shareholder')
                                        <form action="{{ route('shareholders.destroy', $sh->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus data pemilik saham {{ $sh->name }} beserta seluruh data sahamnya?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger d-inline-flex align-items-center gap-1" title="Hapus Pemilik Saham">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 7l16 0" /><path d="M10 11l0 6" /><path d="M14 11l0 6" /><path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" /><path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" /></svg>
                                                <span>Hapus</span>
                                            </button>
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white d-flex flex-wrap align-items-center justify-content-between gap-2 py-3 border-top">
            <span class="text-muted small">
                Menampilkan <strong>{{ $shareholders->count() }}</strong> dari <strong>{{ $shareholders->total() }}</strong> pemilik saham
            </span>
            <div class="m-0">
                {{ $shareholders->links() }}
            </div>
        </div>
    @endif
</div>
@endsection
