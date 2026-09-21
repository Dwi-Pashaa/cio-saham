@extends('layouts.app')

@section('pretitle', 'KONFIGURASI SISTEM')
@section('title', 'Pengaturan Sistem')
@section('subtitle', 'Kelola konfigurasi umum, webhook, notifikasi, dan integrasi API CIO Finance.')

@push('css')
<style>
    /* ==========================================================================
       MODERN SAAS SETTINGS SYSTEM
       ========================================================================== */
    .settings-nav-pills {
        display: flex;
        gap: 8px;
        background: #f1f5f9;
        padding: 6px;
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        margin-bottom: 1.5rem;
    }

    .settings-nav-link {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 10px 16px;
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.875rem;
        color: #64748b;
        background: transparent;
        border: none;
        transition: all 0.2s ease;
        text-decoration: none;
        cursor: pointer;
    }

    .settings-nav-link:hover {
        color: #1e293b;
        background: rgba(255, 255, 255, 0.6);
    }

    .settings-nav-link.active {
        color: #2563eb !important;
        background: #ffffff !important;
        box-shadow: 0 2px 8px rgba(15, 23, 42, 0.08);
    }

    /* Radio Channel Option Cards */
    .channel-select-card {
        border: 2px solid #e2e8f0;
        border-radius: 12px;
        padding: 14px 16px;
        background: #ffffff;
        cursor: pointer;
        transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        display: flex;
        align-items: center;
        gap: 12px;
        height: 100%;
        position: relative;
    }

    .channel-select-card:hover {
        border-color: #93c5fd;
        background: #f8fafc;
        transform: translateY(-1px);
    }

    .form-selectgroup-input:checked + .channel-select-card {
        border-color: #2563eb !important;
        background: linear-gradient(180deg, #ffffff 0%, #eff6ff 100%) !important;
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.12);
    }

    .channel-icon-wrap {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    /* Developer Integration Box */
    .dev-integration-box {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 1.25rem;
    }

    .code-copy-box {
        background: #ffffff;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        padding: 8px 12px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        font-family: monospace;
        font-size: 0.85rem;
    }

    /* Dashboard Column Toggle Card */
    .col-toggle-card {
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 14px 18px;
        background: #ffffff;
        transition: all 0.2s ease;
    }

    .col-toggle-card:hover {
        background: #f8fafc;
        border-color: #cbd5e1;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
    }

    .form-check-input:checked {
        background-color: #2563eb !important;
        border-color: #2563eb !important;
    }

    .num-font {
        font-variant-numeric: tabular-nums;
    }

    @media (max-width: 768px) {
        .settings-nav-pills { flex-direction: column; }
        .settings-nav-link { justify-content: flex-start; }
    }
</style>
@endpush

@section('content')
<div class="container-xl py-2">

    @include('components.alert.success')

    <!-- Modern Tab Navigation Bar -->
    <ul class="nav settings-nav-pills" id="settingsTab" role="tablist">
        <li class="nav-item flex-fill" role="presentation">
            <button class="settings-nav-link active w-100" id="tab-notif-btn" data-bs-toggle="tab" data-bs-target="#tab-notif" type="button" role="tab" aria-controls="tab-notif" aria-selected="true">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 5a2 2 0 1 1 4 0a7 7 0 0 1 4 6v3a4 4 0 0 0 2 3h-16a4 4 0 0 0 2 -3v-3a7 7 0 0 1 4 -6" /><path d="M9 17v1a3 3 0 0 0 6 0v-1" /></svg>
                <span>Saluran Notifikasi</span>
            </button>
        </li>
        <li class="nav-item flex-fill" role="presentation">
            <button class="settings-nav-link w-100" id="tab-xendit-btn" data-bs-toggle="tab" data-bs-target="#tab-xendit" type="button" role="tab" aria-controls="tab-xendit" aria-selected="false">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 5m0 3a3 3 0 0 1 3 -3h12a3 3 0 0 1 3 3v8a3 3 0 0 1 -3 3h-12a3 3 0 0 1 -3 -3z" /><path d="M3 10l18 0" /><path d="M7 15l.01 0" /><path d="M11 15l2 0" /></svg>
                <span>Integrasi API Web Finance</span>
            </button>
        </li>
    </ul>

    <!-- Tab Content Panels -->
    <div class="tab-content" id="settingsTabContent">
        {{-- =========================================================
             TAB 1: SALURAN NOTIFIKASI
        ========================================================== --}}
        <div class="tab-pane fade show active" id="tab-notif" role="tabpanel" aria-labelledby="tab-notif-btn">
            <form action="{{ route('setting.store') }}" method="POST">
                @csrf
                <input type="hidden" name="id" value="{{ $settings->id ?? 1 }}">
                <!-- Preserve Web Finance fields when saving Tab 1 -->
                <input type="hidden" name="cio_finance_base_url" value="{{ $settings->cio_finance_base_url ?? '' }}">
                <input type="hidden" name="cio_finance_client_id" value="{{ $settings->cio_finance_client_id ?? '' }}">
                <input type="hidden" name="cio_finance_key_id" value="{{ $settings->cio_finance_key_id ?? '' }}">
                <input type="hidden" name="cio_finance_secret_key" value="{{ $settings->cio_finance_secret_key ?? '' }}">
                <input type="hidden" name="cio_finance_timeout" value="{{ $settings->cio_finance_timeout ?? 30 }}">

                <div class="card shadow-sm border-0 rounded-3">
                    <div class="card-header py-3.5 px-4 bg-white border-bottom">
                        <div class="d-flex align-items-center gap-2.5">
                            <div class="avatar rounded-3 bg-primary-subtle text-primary" style="width: 38px; height: 38px; display: inline-flex; align-items: center; justify-content: center;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 5a2 2 0 1 1 4 0a7 7 0 0 1 4 6v3a4 4 0 0 0 2 3h-16a4 4 0 0 0 2 -3v-3a7 7 0 0 1 4 -6" /><path d="M9 17v1a3 3 0 0 0 6 0v-1" /></svg>
                            </div>
                            <div>
                                <h4 class="card-title fw-bold text-dark mb-0">Saluran Notifikasi Bukti Transfer</h4>
                                <div class="text-muted small">Atur kanal pengiriman notifikasi bukti transfer dividen kepada investor via Mekari Qontak.</div>
                            </div>
                        </div>
                    </div>

                    <div class="card-body p-4">
                        <div class="row g-4">
                            <!-- Saluran Notifikasi Dividen (Mekari Qontak) -->
                            <div class="col-12">
                                <label class="form-label fw-bold text-dark mb-2">
                                    Pilih Saluran Notifikasi Bukti Transfer (Mekari Qontak) <span class="text-danger">*</span>
                                </label>
                                @php
                                    $currentChannel = old('notification_channel', $settings->notification_channel ?? 'whatsapp');
                                @endphp
                                <div class="row g-3">
                                    <!-- WhatsApp -->
                                    <div class="col-md-3 col-sm-6">
                                        <label class="form-selectgroup-item w-100">
                                            <input type="radio" name="notification_channel" value="whatsapp" class="form-selectgroup-input" {{ $currentChannel === 'whatsapp' ? 'checked' : '' }}>
                                            <div class="channel-select-card">
                                                <div class="channel-icon-wrap" style="background: #ecfdf5; color: #059669;">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 21l1.65 -3.8a9 9 0 1 1 3.4 2.9l-5.05 .9" /><path d="M9 10a.5 .5 0 0 0 1 0v-1a.5 .5 0 0 0 -1 0v1a5 5 0 0 0 5 5h1a.5 .5 0 0 0 0 -1h-1a.5 .5 0 0 0 0 1" /></svg>
                                                </div>
                                                <div>
                                                    <strong class="d-block text-dark">WhatsApp</strong>
                                                    <span class="text-muted" style="font-size: 0.75rem;">Kirim template WA</span>
                                                </div>
                                            </div>
                                        </label>
                                    </div>

                                    <!-- Email -->
                                    <div class="col-md-3 col-sm-6">
                                        <label class="form-selectgroup-item w-100">
                                            <input type="radio" name="notification_channel" value="email" class="form-selectgroup-input" {{ $currentChannel === 'email' ? 'checked' : '' }}>
                                            <div class="channel-select-card">
                                                <div class="channel-icon-wrap" style="background: #eff6ff; color: #2563eb;">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 7a2 2 0 0 1 2 -2h14a2 2 0 0 1 2 2v10a2 2 0 0 1 -2 2h-14a2 2 0 0 1 -2 -2v-10z" /><path d="M3 7l9 6l9 -6" /></svg>
                                                </div>
                                                <div>
                                                    <strong class="d-block text-dark">Email</strong>
                                                    <span class="text-muted" style="font-size: 0.75rem;">Kirim bukti PDF</span>
                                                </div>
                                            </div>
                                        </label>
                                    </div>

                                    <!-- Both -->
                                    <div class="col-md-3 col-sm-6">
                                        <label class="form-selectgroup-item w-100">
                                            <input type="radio" name="notification_channel" value="both" class="form-selectgroup-input" {{ $currentChannel === 'both' ? 'checked' : '' }}>
                                            <div class="channel-select-card">
                                                <div class="channel-icon-wrap" style="background: #f5f3ff; color: #7c3aed;">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M8 9h8" /><path d="M8 13h6" /><path d="M18 4a3 3 0 0 1 3 3v8a3 3 0 0 1 -3 3h-5l-5 3v-3h-2a3 3 0 0 1 -3 -3v-8a3 3 0 0 1 3 -3h12z" /></svg>
                                                </div>
                                                <div>
                                                    <strong class="d-block text-dark">Keduanya</strong>
                                                    <span class="text-muted" style="font-size: 0.75rem;">WA & Email</span>
                                                </div>
                                            </div>
                                        </label>
                                    </div>

                                    <!-- None -->
                                    <div class="col-md-3 col-sm-6">
                                        <label class="form-selectgroup-item w-100">
                                            <input type="radio" name="notification_channel" value="none" class="form-selectgroup-input" {{ $currentChannel === 'none' ? 'checked' : '' }}>
                                            <div class="channel-select-card">
                                                <div class="channel-icon-wrap" style="background: #f1f5f9; color: #64748b;">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M18 6l-12 12" /><path d="M6 6l12 12" /></svg>
                                                </div>
                                                <div>
                                                    <strong class="d-block text-dark">Nonaktif</strong>
                                                    <span class="text-muted" style="font-size: 0.75rem;">Tanpa notifikasi</span>
                                                </div>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer bg-white py-3 px-4 d-flex justify-content-end border-top">
                        <button type="submit" class="btn btn-primary px-4 py-2 d-inline-flex align-items-center gap-2 rounded-3 fw-bold">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M6 4h10l4 4v10a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2" /><path d="M12 14m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0" /><path d="M14 4l0 4l-6 0l0 -4" /></svg>
                            <span>Simpan Saluran Notifikasi</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>

        {{-- =========================================================
             TAB 2: INTEGRASI API WEB FINANCE (CIO FINANCE)
        ========================================================== --}}
        <div class="tab-pane fade" id="tab-xendit" role="tabpanel" aria-labelledby="tab-xendit-btn">
            <form action="{{ route('setting.store') }}" method="POST">
                @csrf
                <input type="hidden" name="id" value="{{ $settings->id ?? 1 }}">
                <!-- Preserve general fields when saving Tab 2 -->
                <input type="hidden" name="telp" value="{{ $settings->telp ?? '628123456789' }}">
                <input type="hidden" name="notification_channel" value="{{ $settings->notification_channel ?? 'whatsapp' }}">
                <input type="hidden" name="admin_fee" value="{{ $settings->admin_fee ?? 0 }}">

                <div class="card shadow-sm border-0 rounded-3">
                    <div class="card-header py-3.5 px-4 bg-white border-bottom">
                        <div class="d-flex align-items-center gap-2.5">
                            <div class="avatar rounded-3 bg-azure-subtle text-azure" style="width: 38px; height: 38px; display: inline-flex; align-items: center; justify-content: center;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 5m0 3a3 3 0 0 1 3 -3h12a3 3 0 0 1 3 3v8a3 3 0 0 1 -3 3h-12a3 3 0 0 1 -3 -3z" /><path d="M3 10l18 0" /><path d="M7 15l.01 0" /><path d="M11 15l2 0" /></svg>
                            </div>
                            <div>
                                <h4 class="card-title fw-bold text-dark mb-0">Integrasi API Web Finance (CIO Finance)</h4>
                                <div class="text-muted small">Konfigurasi kunci HMAC-SHA256, Client ID, Key ID, dan endpoint server Web Finance pusat.</div>
                            </div>
                        </div>
                    </div>

                    <div class="card-body p-4">
                        <div class="row g-4">
                            <!-- Base URL -->
                            <div class="col-md-6">
                                <label for="cio_finance_base_url" class="form-label fw-bold text-dark">
                                    Base URL Web Finance <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="cio_finance_base_url" id="cio_finance_base_url" value="{{ old('cio_finance_base_url', $settings->cio_finance_base_url ?? 'https://finance.cionetwork.id') }}" class="form-control font-monospace" placeholder="https://finance.cionetwork.id" required>
                                <div class="form-text text-muted small mt-1">
                                    URL server sentral CIO Finance.
                                </div>
                            </div>

                            <!-- Timeout -->
                            <div class="col-md-6">
                                <label for="cio_finance_timeout" class="form-label fw-bold text-dark">
                                    API Request Timeout (Detik)
                                </label>
                                <div class="input-group">
                                    <input type="number" name="cio_finance_timeout" id="cio_finance_timeout" value="{{ old('cio_finance_timeout', $settings->cio_finance_timeout ?? 30) }}" class="form-control font-monospace" min="5" max="120" placeholder="30">
                                    <span class="input-group-text bg-light text-muted">detik</span>
                                </div>
                                <div class="form-text text-muted small mt-1">
                                    Batas waktu tunggu koneksi HTTP ke server Web Finance (default: 30 detik).
                                </div>
                            </div>

                            <!-- Client ID -->
                            <div class="col-md-6">
                                <label for="cio_finance_client_id" class="form-label fw-bold text-dark">
                                    Client ID (X-Client-ID) <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="cio_finance_client_id" id="cio_finance_client_id" value="{{ old('cio_finance_client_id', $settings->cio_finance_client_id ?? 'test_web_saham_18dcf3aab5a0d552f5670a3978c7cd22') }}" class="form-control font-monospace" placeholder="test_web_saham_..." required>
                                <div class="form-text text-muted small mt-1">
                                    Identitas unik aplikasi portal saham ini di sistem CIO Finance.
                                </div>
                            </div>

                            <!-- Key ID -->
                            <div class="col-md-6">
                                <label for="cio_finance_key_id" class="form-label fw-bold text-dark">
                                    Key ID (X-Key-ID) <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="cio_finance_key_id" id="cio_finance_key_id" value="{{ old('cio_finance_key_id', $settings->cio_finance_key_id ?? 'kid_4e0479ba4b715ac5') }}" class="form-control font-monospace" placeholder="kid_..." required>
                                <div class="form-text text-muted small mt-1">
                                    Kunci publik identifier untuk pasangan secret key HMAC.
                                </div>
                            </div>

                            <!-- Secret Key -->
                            <div class="col-12">
                                <label for="cio_finance_secret_key" class="form-label fw-bold text-dark">
                                    HMAC Secret Key (X-Signature Generator) <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <input type="password" name="cio_finance_secret_key" id="cio_finance_secret_key" value="{{ old('cio_finance_secret_key', $settings->cio_finance_secret_key ?? 'b60777bc6d6569ad65f875e81f824cda74b3c1cb05a188081ef974ee6c943ed7') }}" class="form-control font-monospace" placeholder="Secret Key HMAC" required>
                                    <button class="btn btn-outline-secondary" type="button" id="btn-toggle-secret" title="Tampilkan/Sembunyikan Kunci">
                                        <svg id="eye-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 12a2 2 0 1 0 4 0a2 2 0 0 0 -4 0" /><path d="M21 12c-2.4 4 -5.4 6 -9 6c-3.6 0 -6.6 -2 -9 -6c2.4 -4 5.4 -6 9 -6c3.6 0 6.6 2 9 6" /></svg>
                                    </button>
                                </div>
                                <div class="form-text text-muted small mt-1">
                                    Kunci rahasia HMAC-SHA256 untuk otentikasi data transaksi mutasi dan sinkronisasi saldo. Disimpan aman di database lokal.
                                </div>
                            </div>

                            <!-- Standarisasi Router Multi-Website Box -->
                            <div class="col-12">
                                <div class="dev-integration-box">
                                    <div class="d-flex align-items-center justify-content-between mb-3">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="badge bg-primary text-white px-2.5 py-1 rounded-pill fw-bold">STANDAR PREFIX</span>
                                            <strong class="text-dark">Router Forwarder Multi-Website</strong>
                                        </div>
                                        <span class="badge bg-blue-lt fw-bold font-monospace fs-5">INV-</span>
                                    </div>
                                    <p class="text-muted small mb-3">
                                        Seluruh ID transaksi, referensi mutasi finance, dan disbursement Xendit dari sistem Investor menggunakan prefix <code>INV-</code>. Router sentral Web Finance akan secara otomatis mem-forward webhook ke endpoint receiver di bawah ini:
                                    </p>
                                    <div class="code-copy-box">
                                        <span class="text-primary font-monospace" id="webhook-url">{{ url('/api/xendit/callback') }}</span>
                                        <button type="button" class="btn btn-sm btn-primary px-3 py-1 d-inline-flex align-items-center gap-1 rounded-2" id="btn-copy-webhook">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M8 8m0 2a2 2 0 0 1 2 -2h8a2 2 0 0 1 2 2v8a2 2 0 0 1 -2 2h-8a2 2 0 0 1 -2 -2z" /><path d="M16 8v-2a2 2 0 0 0 -2 -2h-8a2 2 0 0 0 -2 2v8a2 2 0 0 0 2 2h2" /></svg>
                                            <span>Salin URL</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer bg-white py-3 px-4 d-flex justify-content-end border-top">
                        <button type="submit" class="btn btn-primary px-4 py-2 d-inline-flex align-items-center gap-2 rounded-3 fw-bold">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M6 4h10l4 4v10a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2" /><path d="M12 14m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0" /><path d="M14 4l0 4l-6 0l0 -4" /></svg>
                            <span>Simpan Kredensial Web Finance</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Toggle show/hide password for CIO Finance secret key
        const btnToggleSecret = document.getElementById('btn-toggle-secret');
        const secretInput = document.getElementById('cio_finance_secret_key');
        if (btnToggleSecret && secretInput) {
            btnToggleSecret.addEventListener('click', function() {
                const isPassword = secretInput.type === 'password';
                secretInput.type = isPassword ? 'text' : 'password';
            });
        }

        // One-click copy webhook URL
        const btnCopyWebhook = document.getElementById('btn-copy-webhook');
        if (btnCopyWebhook) {
            btnCopyWebhook.addEventListener('click', function() {
                const url = document.getElementById('webhook-url').innerText;
                navigator.clipboard.writeText(url).then(() => {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: 'URL Webhook berhasil disalin!',
                        showConfirmButton: false,
                        timer: 2000
                    });
                });
            });
        }

        // Remember active tab after refresh
        const activeTabKey = 'cio_active_settings_tab';
        const savedTab = localStorage.getItem(activeTabKey);
        if (savedTab) {
            const triggerEl = document.querySelector(`[data-bs-target="${savedTab}"]`);
            if (triggerEl) {
                const tabInstance = new bootstrap.Tab(triggerEl);
                tabInstance.show();
            }
        }

        document.querySelectorAll('.settings-nav-link').forEach(function(tabBtn) {
            tabBtn.addEventListener('shown.bs.tab', function(e) {
                localStorage.setItem(activeTabKey, e.target.getAttribute('data-bs-target'));
            });
        });
    });
</script>
@endpush