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

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('id_card_number', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $shareholders = $query->paginate(10)->withQueryString();
        $equitySummary = $this->shareholderService->getEquitySummary();

        return view('pages.shareholders.index', compact('shareholders', 'equitySummary'));
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
    public function destroy($id)
    {
        $shareholder = Shareholder::findOrFail($id);
        $shareholder->delete();

        $this->shareholderService->recalculatePercentages();

        return redirect()->route('shareholders.index')
            ->with('success', 'Data Pemilik Saham berhasil dihapus.');
    }
}
