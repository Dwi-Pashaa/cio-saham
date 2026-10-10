@extends('layouts.app')

@section('pretitle', 'KONFIGURASI SISTEM')
@section('title', 'Pengaturan Sistem')
@section('subtitle', 'Kelola konfigurasi gateway WhatsApp Fonnte, notifikasi kas, dan saluran komunikasi.')

@push('css')
<style>
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
</style>
@endpush

@section('content')
<div class="container-xl py-2">

    @include('components.alert.success')

    <form action="{{ route('setting.store') }}" method="POST">
        @csrf
        <input type="hidden" name="id" value="{{ $settings->id ?? 1 }}">

        <!-- 1. NOTIFIKASI WHATSAPP KAS (FONNTE GATEWAY) -->
        <div class="card shadow-sm border-0 rounded-3 mb-4">
            <div class="card-header py-3.5 px-4 bg-white border-bottom d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div class="d-flex align-items-center gap-2.5">
                    <div class="avatar rounded-3 bg-green-lt text-success" style="width: 38px; height: 38px; display: inline-flex; align-items: center; justify-content: center;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 21l1.65 -3.8a9 9 0 1 1 3.4 2.9l-5.05 .9" /><path d="M9 10a.5 .5 0 0 0 1 0v-1a.5 .5 0 0 0 -1 0v1a5 5 0 0 0 5 5h1a.5 .5 0 0 0 0 -1h-1a.5 .5 0 0 0 0 1" /></svg>
                    </div>
                    <div>
                        <h4 class="card-title fw-bold text-dark mb-0">Gateway WhatsApp Transaksi Kas (Fonnte)</h4>
                        <div class="text-muted small">Notifikasi otomatis pesan WhatsApp beserta foto bukti saat ada mutasi saldo masuk & keluar kas.</div>
                    </div>
                </div>

                <button type="button" id="btnTestFonnte" class="btn btn-sm btn-outline-success px-3 py-1.5 d-inline-flex align-items-center gap-1.5 shadow-xs">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 14l11 -11" /><path d="M21 3l-6.5 18a.55 .55 0 0 1 -1 0l-3.5 -7l-7 -3.5a.55 .55 0 0 1 0 -1l18 -6.5" /></svg>
                    <span>Test Kirim WhatsApp</span>
                </button>
            </div>

            <div class="card-body p-4 bg-white">
                <div class="row g-3">
                    <div class="col-md-6 col-12">
                        <label class="form-label required fw-bold text-dark">Nomor WhatsApp Target Alert Kas</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted font-monospace">+</span>
                            <input type="text" name="target_wa_kas" id="target_wa_kas" class="form-control font-monospace" value="{{ old('target_wa_kas', $settings->target_wa_kas ?? $settings->telp) }}" placeholder="Contoh: 628123456789">
                        </div>
                        <span class="text-muted small mt-1 d-block">Nomor HP yang menerima ringkasan mutasi saldo masuk & keluar kas PT. Gunakan format internasional (contoh: <code>628123456789</code>).</span>
                    </div>

                    <div class="col-md-6 col-12">
                        <label class="form-label required fw-bold text-dark">Token API Fonnte</label>
                        <div class="input-group">
                            <input type="password" name="fonnte_token" id="fonnte_token" class="form-control font-monospace" value="{{ old('fonnte_token', $settings->fonnte_token) }}" placeholder="Masukkan Token API Fonnte">
                            <button class="btn btn-outline-secondary" type="button" id="toggleFonnteToken">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon" id="eyeIcon"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 12a2 2 0 1 0 4 0a2 2 0 0 0 -4 0" /><path d="M21 12c-2.4 4 -5.4 6 -9 6c-3.6 0 -6.6 -2 -9 -6c2.4 -4 5.4 -6 9 -6c3.6 0 6.6 2 9 6" /></svg>
                            </button>
                        </div>
                        <span class="text-muted small mt-1 d-block">Dapatkan token device akun Anda pada dashboard resmi <a href="https://fonnte.com" target="_blank" class="fw-semibold">Fonnte.com</a>.</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. SALURAN NOTIFIKASI DIVIDEN (MEKARI QONTAK) -->
        <div class="card shadow-sm border-0 rounded-3 mb-4">
            <div class="card-header py-3.5 px-4 bg-white border-bottom">
                <div class="d-flex align-items-center gap-2.5">
                    <div class="avatar rounded-3 bg-primary-subtle text-primary" style="width: 38px; height: 38px; display: inline-flex; align-items: center; justify-content: center;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 5a2 2 0 1 1 4 0a7 7 0 0 1 4 6v3a4 4 0 0 0 2 3h-16a4 4 0 0 0 2 -3v-3a7 7 0 0 1 4 -6" /><path d="M9 17v1a3 3 0 0 0 6 0v-1" /></svg>
                    </div>
                    <div>
                        <h4 class="card-title fw-bold text-dark mb-0">Saluran Notifikasi Bukti Transfer Dividen</h4>
                        <div class="text-muted small">Atur kanal pengiriman notifikasi pembagian keuntungan dividen kepada investor via Mekari Qontak.</div>
                    </div>
                </div>
            </div>

            <div class="card-body p-4 bg-white">
                <div class="row g-4">
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

            <!-- 3. REKENING TUJUAN TABUNGAN (ALOKASI KAS KE TABUNGAN) -->
            <div class="card-header py-3.5 px-4 bg-white border-top border-bottom d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div class="d-flex align-items-center gap-2.5">
                    <div class="avatar rounded-3 bg-azure-lt text-azure" style="width: 38px; height: 38px; display: inline-flex; align-items: center; justify-content: center;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M17 8v-3a1 1 0 0 0 -1 -1h-10a2 2 0 0 0 0 4h12a1 1 0 0 1 1 1v3m0 4v3a1 1 0 0 1 -1 1h-12a2 2 0 0 1 -2 -2v-12" /><path d="M20 12v4h-4a2 2 0 0 1 0 -4h4" /></svg>
                    </div>
                    <div>
                        <h4 class="card-title fw-bold text-dark mb-0">Rekening Tujuan Tabungan Default</h4>
                        <div class="text-muted small">Rekening bank tujuan yang otomatis dipakai saat pengguna mengalokasikan kas ke pos tabungan terpisah di Dashboard.</div>
                    </div>
                </div>
                <span class="badge bg-azure-lt text-azure font-monospace">Otomatis Terisi di Dashboard</span>
            </div>

            <div class="card-body p-4 bg-white">
                <div class="row g-3">
                    <div class="col-md-4 col-12">
                        <label class="form-label fw-bold text-dark">Nama Pemilik / Penerima Tabungan</label>
                        <input type="text" name="savings_recipient_name" class="form-control" placeholder="Contoh: Rekening Tabungan PT CIO / Yoga" value="{{ old('savings_recipient_name', $settings->savings_recipient_name ?? '') }}">
                        <div class="form-text text-muted small">Atas nama pemilik rekening tabungan yang menerima alokasi.</div>
                    </div>
                    <div class="col-md-4 col-12">
                        <label class="form-label fw-bold text-dark">Nama Bank Tujuan</label>
                        <input type="text" name="savings_bank_name" class="form-control" placeholder="Contoh: BCA / Mandiri / BRI" value="{{ old('savings_bank_name', $settings->savings_bank_name ?? '') }}">
                        <div class="form-text text-muted small">Nama bank rekening tabungan.</div>
                    </div>
                    <div class="col-md-4 col-12">
                        <label class="form-label fw-bold text-dark">Nomor Rekening</label>
                        <input type="text" name="savings_account_number" class="form-control font-monospace" placeholder="Contoh: 7128912345" value="{{ old('savings_account_number', $settings->savings_account_number ?? '') }}">
                        <div class="form-text text-muted small">Nomor rekening tabungan tujuan.</div>
                    </div>
                </div>
            </div>

            <div class="card-footer bg-white py-3 px-4 d-flex justify-content-end border-top">
                <button type="submit" class="btn btn-primary px-4 py-2 d-inline-flex align-items-center gap-2 rounded-3 fw-bold shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M6 4h10l4 4v10a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2" /><path d="M12 14m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0" /><path d="M14 4l0 4l-6 0l0 -4" /></svg>
                    <span>Simpan Pengaturan</span>
                </button>
            </div>
        </div>
    </form>
</div>
@endsection

@push('js')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Toggle Show/Hide Fonnte Token Password Field
        const toggleBtn = document.getElementById('toggleFonnteToken');
        const tokenInput = document.getElementById('fonnte_token');

        if (toggleBtn && tokenInput) {
            toggleBtn.addEventListener('click', function() {
                if (tokenInput.type === 'password') {
                    tokenInput.type = 'text';
                    this.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10.585 10.587a2 2 0 0 0 2.829 2.83" /><path d="M9.363 5.365a9.4 9.4 0 0 1 2.637 -.365c4 0 7.333 2.333 10 7c-.778 1.361 -1.612 2.524 -2.503 3.488m-2.14 1.861c-1.631 1.1 -3.415 1.651 -5.357 1.651c-4 0 -7.333 -2.333 -10 -7c1.369 -2.395 2.913 -4.175 4.632 -5.341" /><path d="M3 3l18 18" /></svg>';
                } else {
                    tokenInput.type = 'password';
                    this.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 12a2 2 0 1 0 4 0a2 2 0 0 0 -4 0" /><path d="M21 12c-2.4 4 -5.4 6 -9 6c-3.6 0 -6.6 -2 -9 -6c2.4 -4 5.4 -6 9 -6c3.6 0 6.6 2 9 6" /></svg>';
                }
            });
        }

        // Test WhatsApp Send Connection
        const testBtn = document.getElementById('btnTestFonnte');
        if (testBtn) {
            testBtn.addEventListener('click', function() {
                const target = document.getElementById('target_wa_kas').value;
                if (!target) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Nomor Target Kosong',
                        text: 'Silakan isi Nomor WhatsApp Target terlebih dahulu.',
                    });
                    return;
                }

                Swal.fire({
                    title: 'Mengirim Pesan Uji Coba...',
                    text: 'Menghubungi server Fonnte gateway ke nomor: ' + target,
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                $.ajax({
                    url: "{{ route('setting.test-fonnte') }}",
                    type: "POST",
                    data: {
                        target: target
                    },
                    success: function(res) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Terkirim!',
                            text: res.message || 'Pesan uji coba berhasil dikirim via Fonnte.',
                        });
                    },
                    error: function(xhr) {
                        const err = xhr.responseJSON ? xhr.responseJSON.message : 'Terjadi kesalahan sistem.';
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal Mengirim',
                            text: err,
                        });
                    }
                });
            });
        }
    });
</script>
@endpush
