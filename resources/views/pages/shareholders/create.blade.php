@extends('layouts.app')

@section('pretitle', 'MANAJEMEN EKUITAS')
@section('title', 'Tambah Pemilik Saham Baru')
@section('subtitle', 'Registrasi data identitas pemilik saham dan opsi alokasi instrumen lembar saham perdana.')

@section('actions')
    <a href="{{ route('shareholders.index') }}" class="btn btn-outline-secondary btn-sm">
        &larr; Kembali ke Daftar
    </a>
@endsection

@section('content')
@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
        <h4 class="alert-title fw-bold">Terdapat kesalahan input:</h4>
        <ul class="mb-0 small">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<form action="{{ route('shareholders.store') }}" method="POST">
    @csrf

    <div class="row row-cards mb-4">
        <!-- Data Profil Pemilik (1 User Data) -->
        <div class="col-lg-6">
            <div class="card border shadow-sm h-100 bg-white">
                <div class="card-header border-bottom py-3 px-4 bg-white">
                    <h3 class="card-title fw-bold mb-0 text-primary">1. Data Profil Pemilik Saham</h3>
                </div>
                <div class="card-body p-4">
                    <div class="mb-3">
                        <label class="form-label required">Nama Lengkap Pemilik Saham</label>
                        <input type="text" name="name" class="form-control" placeholder="Contoh: Yoga Pratama" value="{{ old('name') }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label required">Nomor Identitas (NIK / KTP)</label>
                        <input type="text" name="id_card_number" class="form-control font-monospace" placeholder="16 digit NIK..." value="{{ old('id_card_number') }}" required>
                        <small class="form-hint text-muted">Nomor identitas harus unik dan belum pernah didaftarkan sebelumnya.</small>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" placeholder="nama@domain.com" value="{{ old('email') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Nomor Telepon / WhatsApp</label>
                            <input type="text" name="phone" class="form-control" placeholder="08xxxxxxxxxx" value="{{ old('phone') }}">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Alamat Lengkap</label>
                        <textarea name="address" class="form-control" rows="3" placeholder="Alamat domisili / kantor...">{{ old('address') }}</textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Catatan / Keterangan</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="Catatan opsional...">{{ old('notes') }}</textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label required">Status Pemilik</label>
                        <select name="status" class="form-select">
                            <option value="active" {{ old('status') === 'active' ? 'selected' : '' }}>Aktif</option>
                            <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Non-Aktif</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Alokasi Saham Perdana (Optional initial share) -->
        <div class="col-lg-6">
            <div class="card border shadow-sm h-100 bg-white">
                <div class="card-header border-bottom py-3 px-4 bg-white">
                    <h3 class="card-title fw-bold mb-0 text-dark">2. Alokasi Saham Perdana (Opsional)</h3>
                </div>
                <div class="card-body p-4">
                    <p class="text-muted small mb-3">
                        Anda dapat langsung mendaftarkan instrumen saham perdana di sini. Saham tambahan lainnya dapat ditambahkan sewaktu-waktu di halaman detail pemilik saham.
                    </p>

                    <div class="mb-3">
                        <label class="form-label">Kode Saham</label>
                        <input type="text" name="share_code" class="form-control font-monospace" placeholder="Misal: CIO-CORE-01" value="{{ old('share_code', 'CIO-CORE-01') }}">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Entitas / Unit Usaha Saham</label>
                        <input type="text" name="entity_name" class="form-control" placeholder="Misal: CIO Network Core" value="{{ old('entity_name', 'CIO Network Core') }}">
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Jumlah Lembar Saham</label>
                            <input type="number" name="total_shares" id="create_total_shares" class="form-control" placeholder="Contoh: 1000" min="1" value="{{ old('total_shares', 1000) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Nilai Nominal per Lembar (Rp)</label>
                            <input type="number" name="nominal_value_per_share" id="create_nominal_value" class="form-control" placeholder="10000" min="100" value="{{ old('nominal_value_per_share', 10000) }}">
                        </div>
                    </div>

                    <div class="mb-3 p-3 bg-light-subtle border rounded-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted small">Estimasi Nilai Investasi Awal:</span>
                            <strong class="text-primary h4 mb-0 font-monospace" id="create_calc_total">Rp 10.000.000</strong>
                        </div>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Tanggal Akuisisi</label>
                            <input type="date" name="acquisition_date" class="form-control" value="{{ old('acquisition_date', date('Y-m-d')) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">No. Sertifikat Saham</label>
                            <input type="text" name="certificate_number" class="form-control font-monospace" placeholder="CERT/CIO/2026/01" value="{{ old('certificate_number') }}">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Status Saham Perdana</label>
                        <select name="holding_status" class="form-select">
                            <option value="active" {{ old('holding_status') === 'active' ? 'selected' : '' }}>Aktif</option>
                            <option value="transferred" {{ old('holding_status') === 'transferred' ? 'selected' : '' }}>Dialihkan</option>
                            <option value="sold" {{ old('holding_status') === 'sold' ? 'selected' : '' }}>Terjual</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sticky / Bottom Actions -->
    <div class="card border shadow-sm bg-white">
        <div class="card-body p-3 d-flex justify-content-between align-items-center">
            <a href="{{ route('shareholders.index') }}" class="btn btn-outline-secondary px-3">
                Batal
            </a>
            <button type="submit" class="btn btn-primary px-4">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon me-1"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 5l0 14" /><path d="M5 12l14 0" /></svg>
                Simpan Data Pemilik Saham
            </button>
        </div>
    </div>
</form>

@push('js')
<script>
    const sharesInput = document.getElementById('create_total_shares');
    const nominalInput = document.getElementById('create_nominal_value');
    const totalDisplay = document.getElementById('create_calc_total');

    function calcCreate() {
        if (sharesInput && nominalInput && totalDisplay) {
            const s = parseFloat(sharesInput.value) || 0;
            const n = parseFloat(nominalInput.value) || 0;
            const total = s * n;
            totalDisplay.textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(total);
        }
    }

    if (sharesInput && nominalInput) {
        sharesInput.addEventListener('input', calcCreate);
        nominalInput.addEventListener('input', calcCreate);
        calcCreate();
    }
</script>
@endpush
@endsection
