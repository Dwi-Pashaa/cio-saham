@props(['shareholder'])

<div class="card border shadow-sm mb-4 bg-white">
    <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-3 bg-white">
        <div>
            <h3 class="card-title fw-bold mb-0 text-dark">Daftar Kepemilikan Saham (Portofolio)</h3>
            <span class="text-muted small">
                Pemilik ini memiliki <strong>{{ $shareholder->holdings->count() }}</strong> instrumen saham terdaftar.
            </span>
        </div>
        @can('create-share-holding')
            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modal-add-holding">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 5l0 14" /><path d="M5 12l14 0" /></svg>
                + Tambah Saham untuk Pemilik Ini
            </button>
        @endcan
    </div>

    @if($shareholder->holdings->isEmpty())
        <div class="card-body text-center py-5 bg-white">
            <div class="text-muted mb-3">
                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-coin" width="48" height="48" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0" /><path d="M14.8 9a2 2 0 0 0 -1.8 -1h-2a2 2 0 1 0 0 4h2a2 2 0 1 1 0 4h-2a2 2 0 0 1 -1.8 -1" /><path d="M12 7v10" /></svg>
            </div>
            <h4 class="text-dark">Belum Ada Data Saham</h4>
            <p class="text-muted small">Pemilik saham ini belum memiliki instrumen alokasi saham terdaftar.</p>
            @can('create-share-holding')
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modal-add-holding">
                    + Tambahkan Saham Sekarang
                </button>
            @endcan
        </div>
    @else
        <div class="table-responsive">
            <table class="table table-vcenter card-table table-hover">
                <thead>
                    <tr>
                        <th>Kode Saham</th>
                        <th>Entitas / Unit Usaha</th>
                        <th class="text-end">Jumlah Lembar</th>
                        <th class="text-end">Nilai Nominal / Par</th>
                        <th class="text-end">Total Nilai Investasi</th>
                        <th class="text-center">Porsi (%)</th>
                        <th>Tgl Akuisisi</th>
                        <th>Status</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($shareholder->holdings as $holding)
                        <tr>
                            <td>
                                <span class="badge bg-blue-lt fw-bold font-monospace">{{ $holding->share_code }}</span>
                                @if($holding->certificate_number)
                                    <div class="text-muted small font-monospace mt-0.5">Sertifikat: {{ $holding->certificate_number }}</div>
                                @endif
                            </td>
                            <td>
                                <strong class="text-dark">{{ $holding->entity_name }}</strong>
                            </td>
                            <td class="text-end fw-bold font-monospace">
                                {{ number_format($holding->total_shares, 0, ',', '.') }} Lembar
                            </td>
                            <td class="text-end text-muted font-monospace">
                                Rp {{ number_format($holding->nominal_value_per_share, 0, ',', '.') }}
                            </td>
                            <td class="text-end fw-bold text-primary font-monospace">
                                Rp {{ number_format($holding->total_investment, 0, ',', '.') }}
                            </td>
                            <td class="text-center">
                                <span class="badge bg-success-lt fw-bold font-monospace">
                                    {{ number_format($holding->percentage_share, 2) }}%
                                </span>
                            </td>
                            <td class="text-muted small">
                                {{ $holding->acquisition_date ? $holding->acquisition_date->format('d M Y') : '-' }}
                            </td>
                            <td>
                                @if($holding->status === 'active')
                                    <span class="badge bg-success-lt text-success fw-bold">
                                        <span class="pulse-live-dot me-1" style="width: 6px; height: 6px;"></span> Aktif
                                    </span>
                                @elseif($holding->status === 'transferred')
                                    <span class="badge bg-warning-lt text-warning fw-bold">Dialihkan</span>
                                @else
                                    <span class="badge bg-danger-lt text-danger fw-bold">Terjual</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="btn-group">
                                    @can('edit-share-holding')
                                        <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#modal-edit-holding-{{ $holding->id }}">
                                            Edit
                                        </button>
                                    @endcan
                                    @can('delete-share-holding')
                                        <form action="{{ route('share-holdings.destroy', $holding->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus data saham {{ $holding->share_code }} ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                Hapus
                                            </button>
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="table-light fw-bold">
                        <td colspan="2" class="text-dark">TOTAL PORTOFOLIO:</td>
                        <td class="text-end font-monospace text-dark">{{ number_format($shareholder->total_shares, 0, ',', '.') }} Lembar</td>
                        <td class="text-end">-</td>
                        <td class="text-end text-primary font-monospace">Rp {{ number_format($shareholder->total_investment, 0, ',', '.') }}</td>
                        <td class="text-center text-success font-monospace">{{ number_format($shareholder->total_percentage, 2) }}%</td>
                        <td colspan="3"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    @endif
</div>
