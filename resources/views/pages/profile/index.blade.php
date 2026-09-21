@extends('layouts.app')

@section('pretitle', 'AKUN PENGGUNA')
@section('title', 'Profil Saya')
@section('subtitle', 'Kelola informasi identitas, legalitas pemegang saham, dan sesi akun Anda.')

@push('css')
<style>
    /* ==========================================================================
       ENTERPRISE EXECUTIVE PROFILE - REFINED UI SYSTEM
       ========================================================================== */
    .profile-container {
        max-width: 1140px;
        margin: 0 auto;
        padding-top: 0.5rem;
        padding-bottom: 2rem;
    }

    /* 1. Main Header Profile Card */
    .profile-header-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        padding: 1.5rem 1.75rem;
        box-shadow: 0 2px 10px rgba(15, 23, 42, 0.04);
        margin-bottom: 1.5rem;
        position: relative;
    }

    .profile-avatar-circle {
        width: 68px;
        height: 68px;
        border-radius: 50%;
        background: linear-gradient(135deg, #1d4ed8 0%, #3b82f6 100%);
        color: #ffffff;
        font-size: 1.6rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 4px 14px rgba(37, 99, 235, 0.25);
        border: 3px solid #ffffff;
        flex-shrink: 0;
    }

    .profile-name {
        font-size: 1.35rem;
        font-weight: 700;
        color: #0f172a;
        margin: 0;
        line-height: 1.25;
    }

    .profile-email-meta {
        font-size: 0.875rem;
        color: #64748b;
        margin-top: 2px;
    }

    .profile-tag-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 3px 10px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
    }

    /* 2. Equity / Portfolio Stats Grid */
    .equity-stats-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 1.15rem 1.25rem;
        box-shadow: 0 2px 8px rgba(15, 23, 42, 0.03);
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        transition: all 0.2s ease;
    }

    .equity-stats-card:hover {
        border-color: #cbd5e1;
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(15, 23, 42, 0.06);
    }

    .equity-stats-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 0.65rem;
    }

    .equity-stats-icon {
        width: 34px;
        height: 34px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .equity-stats-val {
        font-size: 1.3rem;
        font-weight: 700;
        color: #0f172a;
        line-height: 1.2;
        margin-bottom: 2px;
    }

    .equity-stats-desc {
        font-size: 0.75rem;
        color: #64748b;
        font-weight: 500;
    }

    /* 3. Section Cards */
    .profile-content-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        box-shadow: 0 2px 10px rgba(15, 23, 42, 0.04);
        margin-bottom: 1.5rem;
        overflow: hidden;
    }

    .profile-card-top {
        padding: 1.1rem 1.4rem;
        border-bottom: 1px solid #f1f5f9;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .profile-card-content {
        padding: 1.4rem;
    }

    .profile-info-block {
        background: #f8fafc;
        border: 1px solid #edf2f7;
        border-radius: 12px;
        padding: 12px 14px;
        height: 100%;
    }

    .profile-info-label {
        font-size: 0.72rem;
        font-weight: 600;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.4px;
        margin-bottom: 4px;
    }

    .profile-info-val {
        font-size: 0.92rem;
        font-weight: 600;
        color: #0f172a;
    }

    /* 4. Form Inputs */
    .profile-input-group label {
        font-size: 0.825rem;
        font-weight: 600;
        color: #334155;
        margin-bottom: 6px;
    }

    .profile-input-group .form-control {
        border-radius: 10px;
        border: 1px solid #cbd5e1;
        padding: 9px 13px;
        font-size: 0.9rem;
        color: #0f172a;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }

    .profile-input-group .form-control:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
    }

    /* 5. Logout Card */
    .profile-logout-box {
        background: #fff5f5;
        border: 1px solid #fee2e2;
        border-radius: 16px;
        padding: 1.35rem;
    }

    /* Mobile Refinements (< 768px) */
    @media (max-width: 767.98px) {
        .profile-container {
            padding-left: 8px;
            padding-right: 8px;
            padding-top: 0.25rem;
        }

        .profile-header-card {
            padding: 1.15rem 1rem;
            border-radius: 14px;
            margin-bottom: 1rem;
        }

        .profile-avatar-circle {
            width: 58px;
            height: 58px;
            font-size: 1.35rem;
        }

        .profile-name {
            font-size: 1.15rem;
        }

        .profile-card-top {
            padding: 0.9rem 1rem;
        }

        .profile-card-content {
            padding: 1rem;
        }

        .equity-stats-card {
            padding: 0.9rem 1rem;
        }

        .equity-stats-val {
            font-size: 1.15rem;
        }
    }
</style>
@endpush

@section('content')
<div class="profile-container">

    {{-- Notification Alert --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible shadow-sm fade show mb-3" role="alert" style="border-radius: 12px;">
            <div class="d-flex align-items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-success"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l5 5l10 -10" /></svg>
                <span class="fw-semibold">{{ session('success') }}</span>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- =================================================================
         1. KARTU PROFIL UTAMA (Header Profile)
         ================================================================= --}}
    <div class="profile-header-card">
        <div class="d-flex align-items-center gap-3.5 flex-wrap flex-sm-nowrap">
            <div class="profile-avatar-circle">
                {{ strtoupper(substr($user->name, 0, 2)) }}
            </div>
            <div class="flex-grow-1 min-w-0">
                <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                    <h1 class="profile-name text-truncate">{{ $user->name }}</h1>
                    <span class="profile-tag-pill bg-primary-subtle text-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 3a12 12 0 0 0 8.5 3a12 12 0 0 1 -8.5 15a12 12 0 0 1 -8.5 -15a12 12 0 0 0 8.5 -3" /></svg>
                        {{ $user->roles->first()->name ?? 'Pengguna Sistem' }}
                    </span>
                    <span class="profile-tag-pill bg-success-subtle text-success">
                        <span class="pulse-live-dot" style="width: 5px; height: 5px;"></span>
                        Akun Aktif
                    </span>
                </div>
                <div class="profile-email-meta text-truncate">{{ $user->email }}</div>
                <div class="d-flex align-items-center gap-3 mt-2 small text-muted flex-wrap">
                    <span>Username: <strong class="text-dark font-monospace">{{ $user->username ?? '-' }}</strong></span>
                    <span>•</span>
                    <span>Terdaftar: <strong class="text-dark">{{ $user->created_at ? $user->created_at->format('d M Y') : '-' }}</strong></span>
                </div>
            </div>
        </div>
    </div>

    {{-- =================================================================
         2. RINGKASAN PORTOFOLIO SAHAM (Jika Investor / Shareholder)
         ================================================================= --}}
    @if($shareholder)
        <div class="row g-3 mb-4">
            <div class="col-md-6 col-12">
                <div class="equity-stats-card">
                    <div class="equity-stats-header">
                        <span class="equity-stats-desc text-uppercase fw-bold">Porsi Kepemilikan</span>
                        <div class="equity-stats-icon bg-blue-subtle text-primary">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 12a9 9 0 1 0 18 0a9 9 0 0 0 -18 0" /><path d="M12 3a9 9 0 0 1 9 9h-9z" /></svg>
                        </div>
                    </div>
                    <div>
                        <div class="equity-stats-val font-monospace text-primary">{{ number_format($totalPercentage, 2) }}%</div>
                        <div class="equity-stats-desc">Porsi total ekuitas perusahaan</div>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-12">
                <div class="equity-stats-card">
                    <div class="equity-stats-header">
                        <span class="equity-stats-desc text-uppercase fw-bold">Total Lembar Saham</span>
                        <div class="equity-stats-icon bg-azure-subtle text-azure">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M15 15m-3 0a3 3 0 1 0 6 0a3 3 0 1 0 -6 0" /><path d="M13 17.5v4.5l2 -1.5l2 1.5v-4.5" /><path d="M10 19h-5a2 2 0 0 1 -2 -2v-10a2 2 0 0 1 2 -2h14a2 2 0 0 1 2 2v10a2 2 0 0 1 -1 1.73" /></svg>
                        </div>
                    </div>
                    <div>
                        <div class="equity-stats-val font-monospace text-dark">{{ number_format($totalShares, 0, ',', '.') }} <span class="fs-6 fw-normal text-muted">Lembar</span></div>
                        <div class="equity-stats-desc">{{ $shareholder->holdings->count() }} instrumen aktif tercatat</div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- =================================================================
         3. KONTEN DETAIL: Informasi Akun, Legalitas & Sesi / Logout
         ================================================================= --}}
    <div class="row g-4">
        {{-- Kolom Kiri: Form Detail Profil & Data Legalitas --}}
        <div class="col-lg-8">
            <form action="{{ route('profile.update') }}" method="POST">
                @csrf
                @method('PUT')

                {{-- Form Kontak & Data Akun --}}
                <div class="profile-content-card">
                    <div class="profile-card-top">
                        <div class="d-flex align-items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-primary"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M8 7a4 4 0 1 0 8 0a4 4 0 0 0 -8 0" /><path d="M6 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2" /></svg>
                            <h4 class="fw-bold mb-0 text-dark" style="font-size: 0.95rem;">Informasi Akun & Kontak</h4>
                        </div>
                        <span class="badge bg-blue-subtle text-primary fw-semibold px-2 py-1" style="font-size: 0.72rem;">Data Utama</span>
                    </div>
                    <div class="profile-card-content">
                        <div class="row g-3">
                            <div class="col-md-6 profile-input-group">
                                <label class="form-label required">Nama Lengkap</label>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required placeholder="Nama lengkap Anda">
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 profile-input-group">
                                <label class="form-label required">Alamat Email</label>
                                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required placeholder="email@domain.com">
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 profile-input-group">
                                <label class="form-label">Nomor WhatsApp / HP</label>
                                <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $user->phone ?? ($shareholder->phone ?? '')) }}" placeholder="0812xxxxxxxx">
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 profile-input-group">
                                <label class="form-label">Username Login</label>
                                <input type="text" class="form-control bg-light text-muted" value="{{ $user->username }}" readonly disabled>
                            </div>

                            @if($shareholder)
                                <div class="col-12 profile-input-group">
                                    <label class="form-label">Alamat Domisili KTP</label>
                                    <textarea name="address" class="form-control" rows="2" placeholder="Alamat lengkap sesuai KTP">{{ old('address', $shareholder->address ?? '') }}</textarea>
                                </div>
                            @endif
                        </div>

                        <div class="d-flex justify-content-end mt-3 pt-2">
                            <button type="submit" class="btn btn-primary px-3.5 py-2 d-inline-flex align-items-center gap-2 fw-semibold" style="border-radius: 10px;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l5 5l10 -10" /></svg>
                                Simpan Perubahan
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Detail Legalitas Kependudukan (Jika Investor) --}}
                @if($shareholder)
                    <div class="profile-content-card">
                        <div class="profile-card-top">
                            <div class="d-flex align-items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-success"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 4m0 3a3 3 0 0 1 3 -3h12a3 3 0 0 1 3 3v10a3 3 0 0 1 -3 3h-12a3 3 0 0 1 -3 -3z" /><path d="M9 10m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0" /><path d="M15 8l2 0" /><path d="M15 12l2 0" /><path d="M7 16l10 0" /></svg>
                                <h4 class="fw-bold mb-0 text-dark" style="font-size: 0.95rem;">Data Legalitas & KTP Pemegang Saham</h4>
                            </div>
                            <span class="badge bg-success-subtle text-success fw-semibold px-2 py-1" style="font-size: 0.72rem;">Terverifikasi</span>
                        </div>
                        <div class="profile-card-content">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="profile-info-block">
                                        <div class="profile-info-label">Nomor Induk Kependudukan (NIK)</div>
                                        <div class="profile-info-val font-monospace">{{ $shareholder->id_card_number ?? '-' }}</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="profile-info-block">
                                        <div class="profile-info-label">Status Verifikasi</div>
                                        <div class="profile-info-val text-success d-flex align-items-center gap-1.5">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l5 5l10 -10" /></svg>
                                            {{ ucfirst($shareholder->status ?? 'Aktif') }}
                                        </div>
                                    </div>
                                </div>
                                @if($shareholder->notes)
                                    <div class="col-12">
                                        <div class="small text-muted p-2.5 bg-light rounded-3 border">
                                            <strong>Catatan Pengelola:</strong> {{ $shareholder->notes }}
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif
            </form>
        </div>

        {{-- Kolom Kanan: Sesi & Tombol Logout --}}
        <div class="col-lg-4">
            {{-- Info Sesi --}}
            <div class="profile-content-card">
                <div class="profile-card-top">
                    <div class="d-flex align-items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-primary"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 3a12 12 0 0 0 8.5 3a12 12 0 0 1 -8.5 15a12 12 0 0 1 -8.5 -15a12 12 0 0 0 8.5 -3" /></svg>
                        <h4 class="fw-bold mb-0 text-dark" style="font-size: 0.95rem;">Informasi Sesi</h4>
                    </div>
                </div>
                <div class="profile-card-content">
                    <div class="d-flex flex-column gap-2 small">
                        <div class="d-flex justify-content-between py-1.5 border-bottom">
                            <span class="text-muted">Status Login:</span>
                            <span class="text-success fw-semibold d-flex align-items-center gap-1">
                                <span class="pulse-live-dot" style="width: 5px; height: 5px;"></span>
                                Sesi Aktif
                            </span>
                        </div>
                        <div class="d-flex justify-content-between py-1.5 border-bottom">
                            <span class="text-muted">Akses Level:</span>
                            <strong class="text-dark">{{ $user->roles->first()->name ?? 'Administrator' }}</strong>
                        </div>
                        <div class="d-flex justify-content-between py-1.5 border-bottom">
                            <span class="text-muted">Waktu Server:</span>
                            <span class="text-dark font-monospace">{{ now()->translatedFormat('d M Y, H:i') }}</span>
                        </div>
                        <div class="d-flex justify-content-between py-1.5">
                            <span class="text-muted">Keamanan:</span>
                            <span class="badge bg-success-subtle text-success fw-semibold px-2 py-0.5">Terlindungi</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Kartu Logout --}}
            <div class="profile-logout-box shadow-sm">
                <div class="d-flex align-items-center gap-2 mb-1.5 text-danger">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M14 8v-2a2 2 0 0 0 -2 -2h-7a2 2 0 0 0 -2 2v12a2 2 0 0 0 2 2h7a2 2 0 0 0 2 -2v-2" /><path d="M9 12h12l-3 -3" /><path d="M18 15l3 -3" /></svg>
                    <h4 class="fw-bold mb-0 text-danger" style="font-size: 0.95rem;">Keluar dari Akun</h4>
                </div>
                <p class="text-muted small mb-3" style="font-size: 0.8rem; line-height: 1.4;">
                    Akhiri sesi aktif Anda pada perangkat ini dengan aman.
                </p>
                <a href="{{ route('logout') }}" class="btn btn-danger w-100 py-2 d-flex align-items-center justify-content-center gap-2 fw-semibold" style="border-radius: 10px;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M14 8v-2a2 2 0 0 0 -2 -2h-7a2 2 0 0 0 -2 2v12a2 2 0 0 0 2 2h7a2 2 0 0 0 2 -2v-2" /><path d="M9 12h12l-3 -3" /><path d="M18 15l3 -3" /></svg>
                    <span>Keluar (Logout)</span>
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
