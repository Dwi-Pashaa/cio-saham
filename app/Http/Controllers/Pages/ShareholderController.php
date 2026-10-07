<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;
use App\Http\Requests\Shareholder\StoreShareholderRequest;
use App\Http\Requests\Shareholder\UpdateShareholderRequest;
use App\Models\Shareholder;
use App\Models\ShareHolding;
use App\Models\ShareTransaction;
use App\Services\ShareholderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class ShareholderController extends Controller
{
    protected ShareholderService $shareholderService;

    public function __construct(ShareholderService $shareholderService)
    {
        $this->shareholderService = $shareholderService;
    }

    /**
     * Tampilkan daftar seluruh pemegang saham.
     */
    public function index(Request $request)
    {
        $query = Shareholder::with(['activeHoldings'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $equitySummary = $this->shareholderService->getEquitySummary();

        if ($request->ajax()) {
            return DataTables::eloquent($query)
                ->filter(function ($q) use ($request) {
                    if ($request->has('search') && !empty($request->input('search.value'))) {
                        $search = trim($request->input('search.value'));
                        $q->where(function ($sub) use ($search) {
                            $sub->where('name', 'like', "%{$search}%")
                                ->orWhere('id_card_number', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%")
                                ->orWhere('phone', 'like', "%{$search}%");
                        });
                    }
                    if ($request->filled('status')) {
                        $q->where('status', $request->status);
                    }
                })
                ->addColumn('shareholder_info', function ($sh) {
                    $initials = strtoupper(substr($sh->name, 0, 2));
                    $name = e($sh->name);
                    $email = $sh->email ? '<span class="text-muted small d-block text-truncate" style="font-size: 0.775rem;">' . e($sh->email) . '</span>' : '';
                    $showUrl = route('shareholders.show', $sh->id);

                    return '<div class="d-flex align-items-center gap-2.5">
                                <div class="shareholder-avatar-circle" style="width: 36px; height: 36px; font-size: 0.85rem;">
                                    ' . $initials . '
                                </div>
                                <div class="overflow-hidden">
                                    <a href="' . $showUrl . '" class="text-dark fw-bold d-block text-decoration-none hover-primary text-truncate" title="' . $name . '">
                                        ' . $name . '
                                    </a>
                                    ' . $email . '
                                </div>
                            </div>';
                })
                ->addColumn('id_card_badge', function ($sh) {
                    return '<span class="badge bg-secondary-lt font-monospace px-2 py-1" style="font-size: 0.8rem; letter-spacing: 0.02em;">' . e($sh->id_card_number ?: '-') . '</span>';
                })
                ->addColumn('holdings_badge', function ($sh) {
                    $count = $sh->activeHoldings->count();
                    return '<span class="badge bg-purple-lt fw-bold d-inline-flex align-items-center gap-1 font-monospace px-2 py-1">
                                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 7m0 2a2 2 0 0 1 2 -2h14a2 2 0 0 1 2 2v9a2 2 0 0 1 -2 2h-14a2 2 0 0 1 -2 -2z" /><path d="M8 7v-2a2 2 0 0 1 2 -2h4a2 2 0 0 1 2 2v2" /></svg>
                                <span>' . $count . ' Data Saham</span>
                            </span>';
                })
                ->addColumn('total_shares_formatted', function ($sh) {
                    return '<div class="text-end fw-bold font-monospace" style="white-space: nowrap !important;">
                                <span class="text-dark fs-4">' . number_format($sh->total_shares, 0, ',', '.') . '</span>
                                <span class="small text-muted fw-normal">Lembar</span>
                            </div>';
                })
                ->addColumn('total_investment_formatted', function ($sh) {
                    return '<div class="text-end fw-bold font-monospace" style="white-space: nowrap !important;">
                                <span class="text-primary fs-4">Rp ' . number_format($sh->total_investment, 0, ',', '.') . '</span>
                            </div>';
                })
                ->addColumn('total_percentage_formatted', function ($sh) {
                    return '<div class="text-center">
                                <span class="badge bg-success-lt fw-bold font-monospace px-2 py-1" style="font-size: 0.825rem;">
                                    ' . number_format($sh->total_percentage, 2) . '%
                                </span>
                            </div>';
                })
                ->addColumn('status_badge', function ($sh) {
                    if ($sh->status === 'active') {
                        return '<div class="text-center">
                                    <span class="badge bg-success-lt text-success fw-bold d-inline-flex align-items-center gap-1 px-2 py-1">
                                        <span class="pulse-live-dot" style="width: 6px; height: 6px;"></span>
                                        <span>Aktif</span>
                                    </span>
                                </div>';
                    }
                    return '<div class="text-center">
                                <span class="badge bg-danger-lt text-danger fw-bold px-2 py-1">
                                    Non-Aktif
                                </span>
                            </div>';
                })
                ->addColumn('action', function ($sh) {
                    $user = auth()->user();
                    $btnShow = '<a href="' . route('shareholders.show', $sh->id) . '" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1" title="Lihat Portofolio Saham Lengkap">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 12a2 2 0 1 0 4 0a2 2 0 0 0 -4 0" /><path d="M21 12c-2.4 4 -5.4 6 -9 6c-3.6 0 -6.6 -2 -9 -6c2.4 -4 5.4 -6 9 -6c3.6 0 6.6 2 9 6" /></svg>
                                    <span>Detail & Saham</span>
                                </a>';

                    $btnEdit = '';
                    if ($user && $user->can('ubah investor')) {
                        $btnEdit = '<a href="' . route('shareholders.edit', $sh->id) . '" class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-1" title="Edit Profil Pemilik">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 20h4l10.5 -10.5a2.828 2.828 0 1 0 -4 -4l-10.5 10.5v4" /><path d="M13.5 6.5l4 4" /></svg>
                                        <span>Edit</span>
                                    </a>';
                    }

                    $btnDelete = '';
                    if ($user && $user->can('hapus investor')) {
                        $btnDelete = '<button type="button" class="btn btn-sm btn-outline-danger d-inline-flex align-items-center gap-1 btn-delete-shareholder" data-id="' . $sh->id . '" data-name="' . e($sh->name) . '" title="Hapus Pemilik Saham">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 7l16 0" /><path d="M10 11l0 6" /><path d="M14 11l0 6" /><path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" /><path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" /></svg>
                                        <span>Hapus</span>
                                    </button>';
                    }

                    return '<div class="shareholder-action-group justify-content-end d-flex align-items-center gap-1">' . $btnShow . $btnEdit . $btnDelete . '</div>';
                })
                ->rawColumns(['shareholder_info', 'id_card_badge', 'holdings_badge', 'total_shares_formatted', 'total_investment_formatted', 'total_percentage_formatted', 'status_badge', 'action'])
                ->make(true);
        }

        return view('pages.shareholders.index', compact('equitySummary'));
    }

    /**
     * Form tambah pemegang saham baru.
     */
    public function create()
    {
        return view('pages.shareholders.create');
    }

    /**
     * Simpan pemegang saham baru & saham perdana (jika ada).
     */
    public function store(StoreShareholderRequest $request)
    {
        DB::beginTransaction();
        try {
            $shareholder = Shareholder::create([
                'name'           => $request->name,
                'email'          => $request->email,
                'phone'          => $request->phone,
                'id_card_number' => $request->id_card_number,
                'address'        => $request->address,
                'notes'          => $request->notes,
                'status'         => $request->status ?? 'active',
            ]);

            // Jika ada input data saham awal
            if ($request->filled('share_code') && $request->filled('total_shares') && (int) $request->total_shares > 0) {
                $nominal = (float) ($request->nominal_value_per_share ?? 10000);
                $shares  = (int) $request->total_shares;
                $totalInvestment = $shares * $nominal;

                $holding = ShareHolding::create([
                    'shareholder_id'          => $shareholder->id,
                    'share_code'              => $request->share_code,
                    'entity_name'             => $request->entity_name ?? 'CIO Network Core',
                    'total_shares'            => $shares,
                    'nominal_value_per_share' => $nominal,
                    'total_investment'        => $totalInvestment,
                    'certificate_number'      => $request->certificate_number ?? ('CERT/CIO/' . date('Y') . '/' . str_pad($shareholder->id, 3, '0', STR_PAD_LEFT)),
                    'acquisition_date'        => $request->acquisition_date ?? now()->toDateString(),
                    'status'                  => 'active',
                ]);

                ShareTransaction::create([
                    'share_holding_id' => $holding->id,
                    'transaction_type' => 'initial',
                    'shares_amount'    => $shares,
                    'price_per_share'  => $nominal,
                    'total_amount'     => $totalInvestment,
                    'transaction_date' => $request->acquisition_date ?? now()->toDateString(),
                    'reference_no'     => 'TRX-INIT-' . uniqid(),
                    'notes'            => 'Penyetoran modal saham perdana',
                ]);
            }

            DB::commit();

            // Hitung ulang persentase saham
            $this->shareholderService->recalculatePercentages();

            return redirect()->route('shareholders.show', $shareholder->id)
                ->with('success', 'Data Pemilik Saham berhasil ditambahkan.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal menambahkan data: ' . $e->getMessage());
        }
    }

    /**
     * Tampilkan detail 1 profil pemilik beserta seluruh kepemilikan saham miliknya (1 to Many).
     */
    public function show($id)
    {
        $shareholder = Shareholder::with(['holdings.transactions', 'user'])->findOrFail($id);
        $equitySummary = $this->shareholderService->getEquitySummary();

        return view('pages.shareholders.show', compact('shareholder', 'equitySummary'));
    }

    /**
     * Form edit profil pemilik saham.
     */
    public function edit($id)
    {
        $shareholder = Shareholder::findOrFail($id);
        return view('pages.shareholders.edit', compact('shareholder'));
    }

    /**
     * Simpan perubahan data profil pemilik saham.
     */
    public function update(UpdateShareholderRequest $request, $id)
    {
        $shareholder = Shareholder::findOrFail($id);
        $shareholder->update([
            'name'           => $request->name,
            'email'          => $request->email,
            'phone'          => $request->phone,
            'id_card_number' => $request->id_card_number,
            'address'        => $request->address,
            'notes'          => $request->notes,
            'status'         => $request->status,
        ]);

        return redirect()->route('shareholders.show', $shareholder->id)
            ->with('success', 'Profil Pemilik Saham berhasil diperbarui.');
    }

    /**
     * Hapus pemegang saham beserta seluruh sahamnya.
     */
    public function destroy(Request $request, $id)
    {
        $shareholder = Shareholder::findOrFail($id);
        $shareholder->delete();

        $this->shareholderService->recalculatePercentages();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'code'    => 200,
                'status'  => 'success',
                'message' => 'Data Pemilik Saham berhasil dihapus.'
            ]);
        }

        return redirect()->route('shareholders.index')
            ->with('success', 'Data Pemilik Saham berhasil dihapus.');
    }
}

