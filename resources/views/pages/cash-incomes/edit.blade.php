@extends('layouts.app')

@section('pretitle', 'KAS & ARUS DANA')
@section('title', 'Ubah Saldo Masuk (Pemasukan)')
@section('subtitle', 'Perbarui data catatan pemasukan ' . $income->transaction_number)

@section('content')
<div class="container-xl py-2">
    <div class="row justify-content-center">
        <div class="col-lg-9 col-md-11">

            @include('components.alert.error')

            <div class="card shadow-sm border-0 rounded-3">
                <div class="card-header py-3.5 px-4 bg-white border-bottom">
                    <div class="d-flex align-items-center justify-content-between w-100">
                        <div class="d-flex align-items-center gap-2.5">
                            <div class="avatar rounded-3 bg-primary-subtle text-primary" style="width: 38px; height: 38px; display: inline-flex; align-items: center; justify-content: center;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 20h4l10.5 -10.5a2.828 2.828 0 1 0 -4 -4l-10.5 10.5v4" /><path d="M13.5 6.5l4 4" /></svg>
                            </div>
                            <div>
                                <h4 class="card-title fw-bold text-dark mb-0">Ubah Catatan Saldo Masuk</h4>
                                <div class="text-muted small">ID Transaksi: <span class="font-monospace fw-bold text-primary">{{ $income->transaction_number }}</span></div>
                            </div>
                        </div>
                    </div>
                </div>

                <form action="{{ route('cash-incomes.update', $income->id) }}" method="POST" enctype="multipart/form-data" id="form-income">
                    @csrf
                    @method('PUT')
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <!-- 1. Tanggal Transaksi -->
                            <div class="col-md-6">
                                <label for="transaction_date" class="form-label fw-bold text-dark">
                                    Tanggal Transaksi <span class="text-danger">*</span>
                                </label>
                                <input type="date" name="transaction_date" id="transaction_date" class="form-control font-monospace @error('transaction_date') is-invalid @enderror" value="{{ old('transaction_date', $income->transaction_date ? $income->transaction_date->format('Y-m-d') : date('Y-m-d')) }}" required>
                                @error('transaction_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- 2. Dari Rekening (Nama Pengirim) -->
                            <div class="col-md-6">
                                <label for="sender_name" class="form-label fw-bold text-dark">
                                    Dari Rekening (Nama Pengirim) <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="sender_name" id="sender_name" class="form-control @error('sender_name') is-invalid @enderror" value="{{ old('sender_name', $income->sender_name) }}" required>
                                @error('sender_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- 3. Rekening / Nama Bank -->
                            <div class="col-md-6">
                                <label for="bank_name" class="form-label fw-bold text-dark">
                                    Rekening / Bank Tujuan <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="bank_name" id="bank_name" class="form-control @error('bank_name') is-invalid @enderror" value="{{ old('bank_name', $income->bank_name) }}" required>
                                @error('bank_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- 4. No Rekening -->
                            <div class="col-md-6">
                                <label for="account_number" class="form-label fw-bold text-dark">
                                    No Rekening <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="account_number" id="account_number" class="form-control font-monospace @error('account_number') is-invalid @enderror" value="{{ old('account_number', $income->account_number) }}" required>
                                @error('account_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- 5. Nominal Pokok -->
                            <div class="col-12">
                                <label for="amount_display" class="form-label fw-bold text-dark">
                                    Nominal Pemasukan (Rp) <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light fw-bold">Rp</span>
                                    <input type="text" id="amount_display" class="form-control font-monospace fs-2 fw-bold text-success @error('amount') is-invalid @enderror" value="{{ old('amount', number_format($income->amount, 0, ',', '.')) }}" required>
                                    <input type="hidden" name="amount" id="amount" value="{{ old('amount', (int) $income->amount) }}">
                                </div>
                                @error('amount')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- 6. Pilihan Biaya Admin (Ya / Tidak) -->
                            <div class="col-12 pt-2 border-top">
                                <label class="form-label fw-bold text-dark mb-2">
                                    Ada Biaya Admin? <span class="text-danger">*</span>
                                </label>
                                @php
                                    $hasAdminOld = old('has_admin_fee', $income->has_admin_fee ? 'ya' : 'tidak');
                                @endphp
                                <div class="d-flex gap-3">
                                    <label class="form-selectgroup-item flex-fill">
                                        <input type="radio" name="has_admin_fee" value="tidak" class="form-selectgroup-input" id="radio-admin-no" {{ $hasAdminOld === 'tidak' ? 'checked' : '' }} onchange="toggleAdminFee(false)">
                                        <div class="p-2.5 text-center border rounded-3 cursor-pointer bg-white">
                                            <strong class="text-dark d-block">Tidak Ada Admin</strong>
                                            <span class="text-muted small">Biaya admin Rp 0</span>
                                        </div>
                                    </label>
                                    <label class="form-selectgroup-item flex-fill">
                                        <input type="radio" name="has_admin_fee" value="ya" class="form-selectgroup-input" id="radio-admin-yes" {{ $hasAdminOld === 'ya' ? 'checked' : '' }} onchange="toggleAdminFee(true)">
                                        <div class="p-2.5 text-center border rounded-3 cursor-pointer bg-white">
                                            <strong class="text-warning d-block">Ada Biaya Admin</strong>
                                            <span class="text-muted small">Input nominal manual</span>
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <!-- 7. Nominal Biaya Admin (Kondisional) -->
                            <div class="col-12 {{ $hasAdminOld === 'ya' ? '' : 'd-none' }}" id="wrap-admin-fee">
                                <label for="admin_fee_display" class="form-label fw-bold text-dark">
                                    Nominal Biaya Admin (Rp) <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-warning fw-bold">Rp</span>
                                    <input type="text" id="admin_fee_display" class="form-control font-monospace fw-bold text-warning" value="{{ old('admin_fee', number_format($income->admin_fee, 0, ',', '.')) }}">
                                    <input type="hidden" name="admin_fee" id="admin_fee" value="{{ old('admin_fee', (int) $income->admin_fee) }}">
                                </div>
                            </div>

                            <!-- 8. Upload Bukti Transaksi -->
                            <div class="col-12 pt-2 border-top">
                                <label for="proof_file" class="form-label fw-bold text-dark">
                                    Upload Bukti Transaksi Baru (Opsional - Kosongkan jika tidak ingin ganti)
                                </label>
                                <input type="file" name="proof_file" id="proof_file" class="form-control @error('proof_file') is-invalid @enderror" accept="image/*,application/pdf" onchange="previewImage(this, 'preview-proof')">
                                <div class="form-text text-muted small">Format didukung: JPG, PNG, WEBP, PDF (Maksimal 5MB).</div>
                                @if($income->proof_file)
                                    <div class="mt-2">
                                        <span class="text-muted small d-block mb-1">Bukti saat ini:</span>
                                        <a href="{{ $income->proof_url }}" target="_blank" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M15 8h.01" /><path d="M3 6a3 3 0 0 1 3 -3h12a3 3 0 0 1 3 3v12a3 3 0 0 1 -3 3h-12a3 3 0 0 1 -3 -3v-12z" /><path d="M3 16l5 -5c.928 -.893 2.072 -.893 3 0l5 5" /><path d="M14 14l1 -1c.928 -.893 2.072 -.893 3 0l3 3" /></svg>
                                            <span>Lihat Berkas Terpasang</span>
                                        </a>
                                    </div>
                                @endif
                                <div class="mt-2 d-none" id="wrap-preview-proof">
                                    <span class="text-muted small d-block mb-1">Pratinjau berkas baru:</span>
                                    <img id="preview-proof" src="#" alt="Pratinjau Bukti" class="rounded border p-1" style="max-height: 160px; max-width: 100%;">
                                </div>
                            </div>

                            <!-- 9. Catatan / Keterangan -->
                            <div class="col-12">
                                <label for="notes" class="form-label fw-bold text-dark">
                                    Catatan / Keterangan Transaksi <span class="text-danger">*</span>
                                </label>
                                <textarea name="notes" id="notes" rows="3" class="form-control @error('notes') is-invalid @enderror" required>{{ old('notes', $income->notes) }}</textarea>
                                @error('notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="card-footer bg-white py-3 px-4 d-flex justify-content-between align-items-center border-top">
                        <a href="{{ route('cash-incomes.index') }}" class="btn btn-outline-secondary px-3">
                            Batal
                        </a>
                        <button type="submit" class="btn btn-primary px-4 d-inline-flex align-items-center gap-2 fw-bold shadow-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M6 4h10l4 4v10a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2" /><path d="M12 14m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0" /><path d="M14 4l0 4l-6 0l0 -4" /></svg>
                            <span>Simpan Perubahan</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
    function formatRupiah(angka) {
        var number_string = angka.replace(/[^,\d]/g, '').toString(),
            split   = number_string.split(','),
            sisa     = split[0].length % 3,
            rupiah     = split[0].substr(0, sisa),
            ribuan     = split[0].substr(sisa).match(/\d{3}/gi);

        if (ribuan) {
            var separator = sisa ? '.' : '';
            rupiah += separator + ribuan.join('.');
        }

        return split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
    }

    const amountDisplay = document.getElementById('amount_display');
    const amountHidden = document.getElementById('amount');
    if (amountDisplay && amountHidden) {
        amountDisplay.addEventListener('input', function(e) {
            this.value = formatRupiah(this.value);
            amountHidden.value = this.value.replace(/\./g, '');
        });
    }

    const adminDisplay = document.getElementById('admin_fee_display');
    const adminHidden = document.getElementById('admin_fee');
    if (adminDisplay && adminHidden) {
        adminDisplay.addEventListener('input', function(e) {
            this.value = formatRupiah(this.value);
            adminHidden.value = this.value.replace(/\./g, '');
        });
    }

    function toggleAdminFee(show) {
        const wrap = document.getElementById('wrap-admin-fee');
        if (!wrap) return;
        if (show) {
            wrap.classList.remove('d-none');
        } else {
            wrap.classList.add('d-none');
            if (adminHidden && adminDisplay) {
                adminHidden.value = '0';
                adminDisplay.value = '0';
            }
        }
    }

    function previewImage(input, targetId) {
        const wrap = document.getElementById('wrap-' + targetId);
        const img = document.getElementById(targetId);
        if (input.files && input.files[0]) {
            if (input.files[0].type.includes('image')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    img.src = e.target.result;
                    if (wrap) wrap.classList.remove('d-none');
                }
                reader.readAsDataURL(input.files[0]);
            } else {
                if (wrap) wrap.classList.add('d-none');
            }
        }
    }
</script>
@endpush
