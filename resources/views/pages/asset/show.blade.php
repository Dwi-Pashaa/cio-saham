@extends('layouts.app')

@section('pretitle', 'RINCIAN INVENTARIS')
@section('title', $asset->name)
@section('subtitle', 'Detail spesifikasi, foto dokumentasi, informasi nomor seri, dan status kepemilikan aset.')

@section('actions')
    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('assets.index') }}" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-1 shadow-none">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l14 0" /><path d="M5 12l6 6" /><path d="M5 12l6 -6" /></svg>
            <span>Kembali</span>
        </a>
        @can('ubah aset')
            <a href="{{ route('assets.edit', $asset->id) }}" class="btn btn-warning btn-sm d-inline-flex align-items-center gap-1 shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="icon"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M7 7h-1a2 2 0 0 0 -2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2 -2v-1" /><path d="M20.385 6.585a2.1 2.1 0 0 0 -2.97 -2.97l-8.415 8.385v3h3l8.385 -8.415z" /><path d="M16 5l3 3" /></svg>
                <span>Edit Aset</span>
            </a>
        @endcan
    </div>
@endsection

@section('content')
<div class="container-xl py-2">
    <!-- Hero Card Header -->
    <div class="card p-4 border-0 shadow-sm bg-white mb-4 rounded-3">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            <div>
                <span class="badge bg-secondary-lt fw-bold px-2.5 py-1 mb-2" style="font-size: 0.8rem;">
                    {{ $asset->type }}
                </span>
                <h1 class="fw-bold text-dark mb-1 fs-1">{{ $asset->name }}</h1>
                <div class="text-muted small">ID Inventaris: <span class="font-monospace fw-semibold">#AST-{{ str_pad($asset->id, 5, '0', STR_PAD_LEFT) }}</span></div>
            </div>
            <div class="text-md-end p-3 bg-light rounded-3">
                <div class="text-muted small fw-bold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.04em;">Harga / Nilai Perolehan</div>
                <div class="h1 fw-bold text-success font-monospace mb-0" style="letter-spacing: -0.02em;">
                    {{ $asset->formatted_price }}
                </div>
            </div>
        </div>
    </div>

    <!-- Galeri Foto Aset (Jika ada) -->
    @if($asset->images->count() > 0)
        <div class="card p-4 border-0 shadow-sm bg-white mb-4 rounded-3">
            <div class="d-flex align-items-center gap-2 mb-3">
                <span class="badge bg-purple-lt p-2 rounded-circle">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="icon"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M15 8h.01" /><path d="M3 6a3 3 0 0 1 3 -3h12a3 3 0 0 1 3 3v12a3 3 0 0 1 -3 3h-12a3 3 0 0 1 -3 -3v-12z" /><path d="M3 16l5 -5c.928 -.893 2.072 -.893 3 0l5 5" /><path d="M14 14l1 -1c.928 -.893 2.072 -.893 3 0l3 3" /></svg>
                </span>
                <h4 class="fw-bold text-dark mb-0 fs-3">Galeri Foto Aset ({{ $asset->images->count() }} Foto)</h4>
            </div>
            <div class="d-flex flex-wrap gap-3">
                @foreach($asset->images as $idx => $img)
                    <div class="position-relative border rounded-2 p-1.5 bg-white cursor-pointer shadow-2xs btn-zoom-photo" data-img="{{ $img->url }}" data-name="{{ $asset->name }} (Foto {{ $idx + 1 }})" style="width: 120px; height: 120px;" title="Klik untuk memperbesar">
                        <img src="{{ $img->url }}" class="rounded-1 w-100 h-100" style="object-fit: cover;">
                        @if($img->is_primary)
                            <span class="badge bg-primary position-absolute bottom-0 start-0 m-2 font-monospace" style="font-size: 0.62rem;">Sampul</span>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Details Grid -->
    <div class="row g-3 mb-4">
        <!-- 1. Kepemilikan -->
        <div class="col-12 col-md-6">
            <div class="card p-4 border-0 shadow-sm bg-white h-100 rounded-3">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <span class="badge bg-blue-lt p-2 rounded-circle">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="icon"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 3a12 12 0 0 0 8.5 3a12 12 0 0 1 -8.5 15a12 12 0 0 1 -8.5 -15a12 12 0 0 0 8.5 -3" /></svg>
                    </span>
                    <h4 class="fw-bold text-dark mb-0 fs-3">Status Kepemilikan</h4>
                </div>

                <div class="mb-3">
                    <div class="text-muted small fw-bold text-uppercase mb-1" style="font-size: 0.7rem;">Pemilik Aset:</div>
                    @if($asset->owner_type === 'shareholder')
                        <span class="badge bg-purple-lt fw-bold font-monospace fs-3 px-3 py-1.5 d-inline-flex align-items-center gap-1.5">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9 7m-4 0a4 4 0 1 0 8 0a4 4 0 1 0 -8 0" /><path d="M3 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2" /></svg>
                            Investor: {{ $asset->shareholder?->name ?? $asset->owner_name }}
                        </span>
                        @if($asset->shareholder)
                            <div class="mt-2 small text-muted">
                                Email: {{ $asset->shareholder->email ?: '-' }} | No HP: {{ $asset->shareholder->phone ?: '-' }}
                            </div>
                        @endif
                    @else
                        <span class="badge bg-blue-lt fw-bold font-monospace fs-3 px-3 py-1.5 d-inline-flex align-items-center gap-1.5">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 21l18 0" /><path d="M5 21v-14l8 -4v18" /><path d="M19 21v-10l-6 -4" /></svg>
                            PT CIO NETWORK SOLUTION
                        </span>
                    @endif
                </div>

                <div class="mt-auto pt-3 border-top d-flex justify-content-between align-items-center text-muted small">
                    <span>Tipe Entitas: <strong>{{ strtoupper($asset->owner_type) }}</strong></span>
                </div>
            </div>
        </div>

        <!-- 2. Waktu & Perolehan -->
        <div class="col-12 col-md-6">
            <div class="card p-4 border-0 shadow-sm bg-white h-100 rounded-3">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <span class="badge bg-green-lt p-2 rounded-circle">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="icon"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 7a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v12a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12z" /><path d="M16 3v4" /><path d="M8 3v4" /><path d="M4 11h16" /></svg>
                    </span>
                    <h4 class="fw-bold text-dark mb-0 fs-3">Tanggal & Registrasi</h4>
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <div class="text-muted small fw-bold text-uppercase" style="font-size: 0.7rem;">Tanggal Perolehan:</div>
                        <div class="fs-3 fw-bold text-dark font-monospace">{{ $asset->formatted_purchase_date }}</div>
                    </div>
                    <div class="col-6">
                        <div class="text-muted small fw-bold text-uppercase" style="font-size: 0.7rem;">Dicatat Pada:</div>
                        <div class="fs-4 text-dark font-monospace">{{ $asset->created_at ? $asset->created_at->format('d M Y, H:i') : '-' }}</div>
                    </div>
                </div>

                <div class="mt-auto pt-3 border-top d-flex justify-content-between align-items-center text-muted small">
                    <span>Dicatat oleh: <strong>{{ $asset->creator?->name ?? 'Sistem' }}</strong></span>
                </div>
            </div>
        </div>

        <!-- 3. Nomor Seri (SN) -->
        <div class="col-12 col-md-6">
            <div class="card p-4 border-0 shadow-sm bg-white h-100 rounded-3">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <span class="badge bg-azure-lt p-2 rounded-circle">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="icon"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M8 8m0 2a2 2 0 0 1 2 -2h8a2 2 0 0 1 2 2v8a2 2 0 0 1 -2 2h-8a2 2 0 0 1 -2 -2z" /><path d="M16 8v-2a2 2 0 0 0 -2 -2h-8a2 2 0 0 0 -2 2v8a2 2 0 0 0 2 2h2" /></svg>
                    </span>
                    <h4 class="fw-bold text-dark mb-0 fs-3">Serial Number (SN)</h4>
                </div>
                <div class="fs-2 fw-bold font-monospace text-azure mb-1">
                    {{ $asset->serial_number ?: '-' }}
                </div>
                <div class="text-muted small">Nomor seri unik perangkat pabrikan</div>
            </div>
        </div>

        <!-- 4. MAC Address -->
        <div class="col-12 col-md-6">
            <div class="card p-4 border-0 shadow-sm bg-white h-100 rounded-3">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <span class="badge bg-teal-lt p-2 rounded-circle">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="icon"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 12h1m8 -9v1m8 8h1m-9 8v1m-6.4 -15.4l.7 .7m12.1 -.7l-.7 .7m0 11.4l.7 .7m-12.1 -.7l-.7 .7" /></svg>
                    </span>
                    <h4 class="fw-bold text-dark mb-0 fs-3">MAC Address</h4>
                </div>
                <div class="fs-2 fw-bold font-monospace text-teal mb-1">
                    {{ $asset->mac_address ?: '-' }}
                </div>
                <div class="text-muted small">Alamat fisik jaringan (Network Interface)</div>
            </div>
        </div>

        <!-- 5. Catatan / Lokasi Penempatan -->
        <div class="col-12">
            <div class="card p-4 border-0 shadow-sm bg-white rounded-3">
                <h4 class="fw-bold text-dark mb-2 fs-3">Catatan / Lokasi Penempatan / Spesifikasi Tambahan</h4>
                <div class="text-muted" style="white-space: pre-line; line-height: 1.6;">
                    {{ $asset->notes ?: 'Tidak ada catatan atau spesifikasi tambahan untuk aset ini.' }}
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Zoom Photo -->
<div class="modal fade" id="modalZoomPhoto" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg bg-dark text-white rounded-3 overflow-hidden">
            <div class="modal-header border-0 py-2.5 px-3 bg-dark d-flex justify-content-between align-items-center">
                <h5 class="modal-title mb-0 fs-4 fw-bold text-white text-truncate" id="zoomPhotoTitle">Foto Aset</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0 text-center bg-black d-flex align-items-center justify-content-center" style="min-height: 380px; max-height: 80vh;">
                <img id="zoomPhotoImg" src="" class="img-fluid" style="max-height: 75vh; object-fit: contain;">
            </div>
        </div>
    </div>
</div>

@endsection

@push('js')
<script>
$(document).ready(function () {
    $('.btn-zoom-photo').on('click', function () {
        var url = $(this).data('img');
        var name = $(this).data('name') || 'Foto Aset';
        $('#zoomPhotoImg').attr('src', url);
        $('#zoomPhotoTitle').text(name);
        $('#modalZoomPhoto').modal('show');
    });
});
</script>
@endpush
