@extends('layouts.app')

@section('pretitle', 'INVENTARIS & ASET')
@section('title', 'Tambah Aset Baru')
@section('subtitle', 'Mencatat inventaris barang, perangkat jaringan, foto aset, atau barang operasional baru.')

@section('actions')
    <a href="{{ route('assets.index') }}" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-1 shadow-none">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l14 0" /><path d="M5 12l6 6" /><path d="M5 12l6 -6" /></svg>
        <span>Kembali ke Inventaris</span>
    </a>
@endsection

@section('content')
<div class="container-xl py-2">

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

    <div class="card shadow-sm border-0 bg-white" style="border-radius: 12px; overflow: hidden;">
        <div class="card-header py-3 px-4 bg-white border-bottom d-flex align-items-center gap-3">
            <div class="avatar bg-primary-lt text-primary rounded-circle" style="width: 40px; height: 40px;">
                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 3l8 4.5l0 9l-8 4.5l-8 -4.5l0 -9l8 -4.5" /><path d="M12 12l8 -4.5" /><path d="M12 12l0 9" /><path d="M12 12l-8 -4.5" /></svg>
            </div>
            <div>
                <h3 class="card-title fw-bold text-dark mb-0 fs-3">Formulir Pendaftaran Aset</h3>
                <p class="text-muted small mb-0 mt-0.5">Catat informasi identitas perangkat, harga perolehan, foto, dan kepemilikan</p>
            </div>
        </div>
        <form action="{{ route('assets.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="card-body p-4">
                <div class="row g-3">
                    <!-- Nama Barang -->
                    <div class="col-12 col-md-7">
                        <label class="form-label required fw-bold text-dark">Nama Barang / Perangkat</label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" placeholder="Contoh: Router MikroTik CCR2004-16G-2S+" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Tipe / Kategori -->
                    <div class="col-12 col-md-5">
                        <label class="form-label required fw-bold text-dark">Kategori / Tipe</label>
                        <input type="text" name="type" class="form-control @error('type') is-invalid @enderror" list="typeList" value="{{ old('type') }}" placeholder="Pilih atau ketik kategori" required>
                        <datalist id="typeList">
                            @foreach($assetTypes as $t)
                                <option value="{{ $t }}"></option>
                            @endforeach
                        </datalist>
                        @error('type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Harga Barang -->
                    <div class="col-12 col-md-6">
                        <label class="form-label required fw-bold text-dark">Harga Perolehan (Rp)</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light font-monospace fw-bold text-success">Rp</span>
                            <input type="text" name="price" id="price" class="form-control font-monospace fw-bold rupiah-input @error('price') is-invalid @enderror" value="{{ old('price') ? number_format((float)old('price'), 0, ',', '.') : '' }}" placeholder="0" required>
                        </div>
                        @error('price')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Tanggal Pembelian / Perolehan -->
                    <div class="col-12 col-md-6">
                        <label class="form-label fw-bold text-dark">Tanggal Perolehan</label>
                        <input type="date" name="purchase_date" class="form-control font-monospace @error('purchase_date') is-invalid @enderror" value="{{ old('purchase_date', date('Y-m-d')) }}">
                        @error('purchase_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Serial Number -->
                    <div class="col-12 col-md-6">
                        <label class="form-label fw-bold text-dark">Serial Number (SN) <span class="text-muted fw-normal small">(Opsional)</span></label>
                        <input type="text" name="serial_number" class="form-control font-monospace @error('serial_number') is-invalid @enderror" value="{{ old('serial_number') }}" placeholder="Contoh: SN-87293104829">
                        @error('serial_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- MAC Address -->
                    <div class="col-12 col-md-6">
                        <label class="form-label fw-bold text-dark">MAC Address <span class="text-muted fw-normal small">(Opsional)</span></label>
                        <input type="text" name="mac_address" class="form-control font-monospace @error('mac_address') is-invalid @enderror" value="{{ old('mac_address') }}" placeholder="Contoh: 00:1B:44:11:3A:B7">
                        @error('mac_address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- UPLOAD MULTIPLE FOTO ASET -->
                    <div class="col-12">
                        <label class="form-label fw-bold text-dark mb-1 d-flex justify-content-between align-items-center">
                            <span class="d-flex align-items-center gap-1.5">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="icon text-primary"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M15 8h.01" /><path d="M3 6a3 3 0 0 1 3 -3h12a3 3 0 0 1 3 3v12a3 3 0 0 1 -3 3h-12a3 3 0 0 1 -3 -3v-12z" /><path d="M3 16l5 -5c.928 -.893 2.072 -.893 3 0l5 5" /><path d="M14 14l1 -1c.928 -.893 2.072 -.893 3 0l3 3" /></svg>
                                <span>Foto / Dokumentasi Fisik Aset</span>
                            </span>
                            <span class="badge bg-light text-muted small">Bisa pilih lebih dari 1 foto (Maks. 5MB)</span>
                        </label>
                        <input type="file" name="images[]" id="standalone_asset_images" class="form-control" accept="image/jpeg,image/png,image/webp,image/jpg" multiple>
                        <div class="form-text text-muted small mt-1">Pilih satu atau beberapa file foto (JPG, PNG, WEBP). Foto pertama akan menjadi foto sampul.</div>
                        <div id="standalone_images_preview" class="d-flex flex-wrap gap-2 mt-2"></div>
                    </div>

                    <!-- PENGATURAN KEPEMILIKAN ASET -->
                    <div class="col-12">
                        <label class="form-label fw-bold text-dark mb-2 d-flex align-items-center gap-1.5">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="icon text-primary"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 3a12 12 0 0 0 8.5 3a12 12 0 0 1 -8.5 15a12 12 0 0 1 -8.5 -15a12 12 0 0 0 8.5 -3" /></svg>
                            <span>Kepemilikan Aset <span class="text-danger">*</span></span>
                        </label>

                        @can('atur pemilik aset')
                            <div class="form-selectgroup form-selectgroup-boxes d-flex flex-column flex-sm-row gap-2 mb-2">
                                <label class="form-selectgroup-item flex-fill">
                                    <input type="radio" name="owner_type" value="pt" class="form-selectgroup-input owner-type-radio" {{ old('owner_type', 'pt') === 'pt' ? 'checked' : '' }}>
                                    <span class="form-selectgroup-label d-flex align-items-center p-3 border rounded-3 h-100">
                                        <span class="avatar bg-blue-lt text-primary rounded-circle me-3 flex-shrink-0" style="width: 36px; height: 36px;">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 21l18 0" /><path d="M5 21v-14l8 -4v18" /><path d="M19 21v-10l-6 -4" /></svg>
                                        </span>
                                        <span class="text-start">
                                            <span class="d-block fw-bold text-dark">Milik PT (PT CIO NETWORK)</span>
                                            <span class="d-block text-muted small" style="font-size: 0.74rem;">Inventaris & operasional resmi perusahaan</span>
                                        </span>
                                    </span>
                                </label>
                                <label class="form-selectgroup-item flex-fill">
                                    <input type="radio" name="owner_type" value="shareholder" class="form-selectgroup-input owner-type-radio" {{ old('owner_type') === 'shareholder' ? 'checked' : '' }}>
                                    <span class="form-selectgroup-label d-flex align-items-center p-3 border rounded-3 h-100">
                                        <span class="avatar bg-purple-lt text-purple rounded-circle me-3 flex-shrink-0" style="width: 36px; height: 36px;">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9 7m-4 0a4 4 0 1 0 8 0a4 4 0 1 0 -8 0" /><path d="M3 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2" /><path d="M16 3.13a4 4 0 0 1 0 7.75" /><path d="M21 21v-2a4 4 0 0 0 -3 -3.85" /></svg>
                                        </span>
                                        <span class="text-start">
                                            <span class="d-block fw-bold text-dark">Milik Investor / Pemegang Saham</span>
                                            <span class="d-block text-muted small" style="font-size: 0.74rem;">Aset modal atau titipan investor tertentu</span>
                                        </span>
                                    </span>
                                </label>
                            </div>

                            <div class="mt-2 p-3 bg-light-subtle rounded-3 border shareholder-select-wrap {{ old('owner_type') === 'shareholder' ? '' : 'd-none' }}">
                                <label class="form-label small fw-bold text-purple mb-1">Pilih Pemegang Saham / Investor Pemilik:</label>
                                <select name="shareholder_id" class="form-select select-shareholder @error('shareholder_id') is-invalid @enderror">
                                    <option value="">-- Pilih Investor --</option>
                                    @foreach($shareholders as $sh)
                                        <option value="{{ $sh->id }}" {{ old('shareholder_id') == $sh->id ? 'selected' : '' }}>
                                            {{ $sh->name }} ({{ $sh->email ?: $sh->phone ?: 'Investor' }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('shareholder_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        @else
                            <div class="border rounded-3 p-3 bg-light-subtle d-flex align-items-center gap-3">
                                <span class="avatar bg-blue-lt text-primary rounded-circle flex-shrink-0" style="width: 38px; height: 38px;">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 13a2 2 0 0 1 2 -2h10a2 2 0 0 1 2 2v6a2 2 0 0 1 -2 2h-10a2 2 0 0 1 -2 -2v-6z" /><path d="M11 16a1 1 0 1 0 2 0a1 1 0 0 0 -2 0" /><path d="M8 11v-4a4 4 0 1 1 8 0v4" /></svg>
                                </span>
                                <div>
                                    <div class="fw-bold text-dark">Milik PT CIO NETWORK SOLUTION <span class="badge bg-secondary-lt font-monospace ms-1 small">Default Sistem</span></div>
                                    <div class="text-muted small">Kepemilikan aset otomatis ditetapkan sebagai milik PT untuk tingkat level akun Anda.</div>
                                </div>
                            </div>
                        @endcan
                    </div>

                    <!-- Catatan / Spesifikasi Lokasi -->
                    <div class="col-12">
                        <label class="form-label fw-bold text-dark">Catatan / Lokasi Penempatan / Spesifikasi Tambahan</label>
                        <textarea name="notes" class="form-control @error('notes') is-invalid @enderror" rows="3" placeholder="Contoh: Terpasang di POP Pusat Server Lt. 2, kondisi prima...">{{ old('notes') }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
            <div class="card-footer bg-light py-3 px-4 d-flex align-items-center justify-content-between">
                <a href="{{ route('assets.index') }}" class="btn btn-ghost-secondary px-3">Batal</a>
                <button type="submit" class="btn btn-primary px-4 shadow-sm d-inline-flex align-items-center gap-1.5">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="icon"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l5 5l10 -10" /></svg>
                    <span class="fw-semibold">Simpan Data Aset</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('js')
<script>
$(document).ready(function () {
    $(document).on('keyup', '.rupiah-input', function () {
        var value = $(this).val().replace(/[^0-9]/g, '');
        if (value) {
            $(this).val(new Intl.NumberFormat('id-ID').format(value));
        } else {
            $(this).val('');
        }
    });

    $('#standalone_asset_images').on('change', function () {
        var previewWrap = $('#standalone_images_preview');
        previewWrap.empty();
        if (this.files && this.files.length > 0) {
            Array.from(this.files).forEach(function (file, index) {
                var reader = new FileReader();
                reader.onload = function (e) {
                    var primaryBadge = index === 0 ? '<span class="badge bg-primary position-absolute bottom-0 start-0 m-1 font-monospace" style="font-size: 0.6rem;">Utama</span>' : '';
                    var card = $(`
                        <div class="position-relative border rounded-2 p-1 bg-white shadow-2xs" style="width: 82px; height: 82px;">
                            <img src="${e.target.result}" class="rounded-1 w-100 h-100" style="object-fit: cover;">
                            ${primaryBadge}
                        </div>
                    `);
                    previewWrap.append(card);
                };
                reader.readAsDataURL(file);
            });
        }
    });

    $('.owner-type-radio').on('change', function () {
        if ($(this).val() === 'shareholder') {
            $('.shareholder-select-wrap').removeClass('d-none');
            $('.select-shareholder').prop('required', true);
        } else {
            $('.shareholder-select-wrap').addClass('d-none');
            $('.select-shareholder').prop('required', false);
        }
    });
});
</script>
@endpush
