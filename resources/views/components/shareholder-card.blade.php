@props(['shareholder'])

<div class="card mb-4 border shadow-sm bg-white">
    <div class="card-body p-4">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
            <div class="d-flex align-items-center gap-3">
                <div class="shareholder-avatar-circle shadow-xs">
                    {{ strtoupper(substr($shareholder->name, 0, 2)) }}
                </div>
                <div>
                    <div class="d-flex align-items-center gap-2">
                        <h3 class="card-title fw-bold mb-0 text-dark fs-2">{{ $shareholder->name }}</h3>
                        @if($shareholder->status === 'active')
                            <span class="badge bg-success-lt fw-bold">
                                <span class="pulse-live-dot me-1" style="width: 6px; height: 6px;"></span> Aktif
                            </span>
                        @else
                            <span class="badge bg-danger-lt fw-bold">Non-Aktif</span>
                        @endif
                    </div>
                    <div class="text-muted small mt-1">
                        <span class="me-2">NIK: <strong class="text-dark font-monospace">{{ $shareholder->id_card_number }}</strong></span>
                        @if($shareholder->phone)
                            <span class="me-2">&bull; Telp: <strong class="text-dark">{{ $shareholder->phone }}</strong></span>
                        @endif
                        @if($shareholder->email)
                            <span>&bull; Email: <strong class="text-dark">{{ $shareholder->email }}</strong></span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="d-flex align-items-center gap-2">
                @can('edit-shareholder')
                    <a href="{{ route('shareholders.edit', $shareholder->id) }}" class="btn btn-outline-primary btn-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M7 7h-1a2 2 0 0 0 -2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2 -2v-1" /><path d="M20.385 6.585a2.1 2.1 0 0 0 -2.97 -2.97l-8.415 8.415v3h3l8.415 -8.415z" /><path d="M16 5l3 3" /></svg>
                        Edit Profil Pemilik
                    </a>
                @endcan
            </div>
        </div>

        @if($shareholder->address || $shareholder->notes)
            <div class="row g-3 border-top pt-3 mt-2 text-muted small">
                @if($shareholder->address)
                    <div class="col-md-6">
                        <strong class="text-dark d-block mb-0.5">Alamat Domisili:</strong>
                        <span>{{ $shareholder->address }}</span>
                    </div>
                @endif
                @if($shareholder->notes)
                    <div class="col-md-6">
                        <strong class="text-dark d-block mb-0.5">Catatan Khusus:</strong>
                        <span>{{ $shareholder->notes }}</span>
                    </div>
                @endif
            </div>
        @endif
    </div>
</div>
