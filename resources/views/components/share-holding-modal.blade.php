@props(['shareholder'])

<!-- Modal Tambah Saham Baru untuk Pemilik Terpilih -->
<div class="modal fade" id="modal-add-holding" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg border">
            <form action="{{ route('share-holdings.store') }}" method="POST">
                @csrf
                <input type="hidden" name="shareholder_id" value="{{ $shareholder->id }}">

                <div class="modal-header bg-white border-bottom py-3 px-4">
                    <h5 class="modal-title fw-bold text-dark">Tambah Instrumen Saham untuk {{ $shareholder->name }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4 bg-white">
                    <div class="mb-3">
                        <label class="form-label required">Kode Saham</label>
                        <input type="text" name="share_code" class="form-control" placeholder="Misal: CIO-CORE-01, CIO-SAAS-02" required value="{{ old('share_code') }}">
                        <small class="form-hint">Kode unik saham atau seri lembar saham.</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label required">Entitas / Unit Usaha</label>
                        <input type="text" name="entity_name" class="form-control" placeholder="Misal: CIO Network Core / Unit SaaS" required value="{{ old('entity_name', 'CIO Network Core') }}">
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label required">Jumlah Lembar Saham</label>
                            <input type="number" name="total_shares" id="modal_total_shares" class="form-control" placeholder="Contoh: 1000" min="1" required value="{{ old('total_shares', 1000) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label required">Nilai Nominal / Par (Rp)</label>
                            <input type="number" name="nominal_value_per_share" id="modal_nominal_value" class="form-control" placeholder="10000" min="100" required value="{{ old('nominal_value_per_share', 10000) }}">
                        </div>
                    </div>

                    <div class="mb-3 p-3 bg-light-subtle border rounded-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted small">Estimasi Total Investasi:</span>
                            <strong class="text-primary h4 mb-0 font-monospace" id="modal_calc_total">Rp 10.000.000</strong>
                        </div>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label required">Tanggal Akuisisi</label>
                            <input type="date" name="acquisition_date" class="form-control" required value="{{ old('acquisition_date', date('Y-m-d')) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">No. Sertifikat Saham</label>
                            <input type="text" name="certificate_number" class="form-control font-monospace" placeholder="CERT/CIO/2026/..." value="{{ old('certificate_number') }}">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label required">Status Saham</label>
                        <select name="status" class="form-select">
                            <option value="active" selected>Aktif</option>
                            <option value="transferred">Dialihkan</option>
                            <option value="sold">Terjual</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Catatan Mutasi / Transaksi</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="Catatan opsional..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light-subtle py-3 px-4 border-top">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary px-4">Simpan Saham</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit untuk Masing-masing Saham -->
@foreach($shareholder->holdings as $holding)
<div class="modal fade" id="modal-edit-holding-{{ $holding->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg border">
            <form action="{{ route('share-holdings.update', $holding->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="modal-header bg-white border-bottom py-3 px-4">
                    <h5 class="modal-title fw-bold text-dark">Edit Saham: {{ $holding->share_code }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4 bg-white">
                    <div class="mb-3">
                        <label class="form-label required">Kode Saham</label>
                        <input type="text" name="share_code" class="form-control font-monospace" value="{{ old('share_code', $holding->share_code) }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label required">Entitas / Unit Usaha</label>
                        <input type="text" name="entity_name" class="form-control" value="{{ old('entity_name', $holding->entity_name) }}" required>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label required">Jumlah Lembar Saham</label>
                            <input type="number" name="total_shares" id="edit_total_shares_{{ $holding->id }}" class="form-control" min="1" value="{{ old('total_shares', $holding->total_shares) }}" required oninput="calcEditTotal({{ $holding->id }})">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label required">Nilai Nominal / Par (Rp)</label>
                            <input type="number" name="nominal_value_per_share" id="edit_nominal_value_{{ $holding->id }}" class="form-control" min="100" value="{{ old('nominal_value_per_share', (int)$holding->nominal_value_per_share) }}" required oninput="calcEditTotal({{ $holding->id }})">
                        </div>
                    </div>

                    <div class="mb-3 p-3 bg-light-subtle border rounded-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted small">Total Nilai Investasi:</span>
                            <strong class="text-primary h4 mb-0 font-monospace" id="edit_calc_total_{{ $holding->id }}">Rp {{ number_format($holding->total_investment, 0, ',', '.') }}</strong>
                        </div>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label required">Tanggal Akuisisi</label>
                            <input type="date" name="acquisition_date" class="form-control" value="{{ old('acquisition_date', $holding->acquisition_date ? $holding->acquisition_date->format('Y-m-d') : '') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">No. Sertifikat</label>
                            <input type="text" name="certificate_number" class="form-control font-monospace" value="{{ old('certificate_number', $holding->certificate_number) }}">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label required">Status Saham</label>
                        <select name="status" class="form-select">
                            <option value="active" {{ $holding->status === 'active' ? 'selected' : '' }}>Aktif</option>
                            <option value="transferred" {{ $holding->status === 'transferred' ? 'selected' : '' }}>Dialihkan</option>
                            <option value="sold" {{ $holding->status === 'sold' ? 'selected' : '' }}>Terjual</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Catatan Mutasi / Transaksi</label>
                        <textarea name="notes" class="form-control" rows="2">{{ old('notes', $holding->notes) }}</textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light-subtle py-3 px-4 border-top">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary px-4">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach

@push('js')
<script>
    // Realtime calculation for modal add
    const addShares = document.getElementById('modal_total_shares');
    const addNominal = document.getElementById('modal_nominal_value');
    const addTotal = document.getElementById('modal_calc_total');

    function calcAdd() {
        if (addShares && addNominal && addTotal) {
            const shares = parseFloat(addShares.value) || 0;
            const nominal = parseFloat(addNominal.value) || 0;
            const total = shares * nominal;
            addTotal.textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(total);
        }
    }

    if (addShares && addNominal) {
        addShares.addEventListener('input', calcAdd);
        addNominal.addEventListener('input', calcAdd);
        calcAdd();
    }

    function calcEditTotal(id) {
        const s = document.getElementById('edit_total_shares_' + id);
        const n = document.getElementById('edit_nominal_value_' + id);
        const t = document.getElementById('edit_calc_total_' + id);
        if (s && n && t) {
            const total = (parseFloat(s.value) || 0) * (parseFloat(n.value) || 0);
            t.textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(total);
        }
    }
</script>
@endpush
