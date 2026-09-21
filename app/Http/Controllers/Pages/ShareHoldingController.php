<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;
use App\Http\Requests\ShareHolding\StoreShareHoldingRequest;
use App\Http\Requests\ShareHolding\UpdateShareHoldingRequest;
use App\Models\Shareholder;
use App\Models\ShareHolding;
use App\Models\ShareTransaction;
use App\Services\ShareholderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ShareHoldingController extends Controller
{
    protected ShareholderService $shareholderService;

    public function __construct(ShareholderService $shareholderService)
    {
        $this->shareholderService = $shareholderService;
    }

    /**
     * Tambahkan alokasi saham baru ke pemilik saham tertentu.
     */
    public function store(StoreShareHoldingRequest $request)
    {
        DB::beginTransaction();
        try {
            $shareholder = Shareholder::findOrFail($request->shareholder_id);

            $nominal = (float) $request->nominal_value_per_share;
            $shares  = (int) $request->total_shares;
            $totalInvestment = $nominal * $shares;

            $holding = ShareHolding::create([
                'shareholder_id'          => $shareholder->id,
                'share_code'              => $request->share_code,
                'entity_name'             => $request->entity_name,
                'total_shares'            => $shares,
                'nominal_value_per_share' => $nominal,
                'total_investment'        => $totalInvestment,
                'certificate_number'      => $request->certificate_number ?? ('CERT/CIO/' . date('Y') . '/' . str_pad($shareholder->id, 3, '0', STR_PAD_LEFT) . '-' . uniqid()),
                'acquisition_date'        => $request->acquisition_date,
                'status'                  => $request->status ?? 'active',
            ]);

            ShareTransaction::create([
                'share_holding_id' => $holding->id,
                'transaction_type' => 'buy',
                'shares_amount'    => $shares,
                'price_per_share'  => $nominal,
                'total_amount'     => $totalInvestment,
                'transaction_date' => $request->acquisition_date,
                'reference_no'     => 'TRX-BUY-' . uniqid(),
                'notes'            => $request->notes ?? 'Penambahan kepemilikan saham baru',
            ]);

            DB::commit();

            $this->shareholderService->recalculatePercentages();

            return redirect()->route('shareholders.show', $shareholder->id)
                ->with('success', "Alokasi saham {$holding->share_code} ({$holding->entity_name}) berhasil ditambahkan ke {$shareholder->name}.");
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal menambahkan alokasi saham: ' . $e->getMessage());
        }
    }

    /**
     * Update data alokasi saham.
     */
    public function update(UpdateShareHoldingRequest $request, $id)
    {
        DB::beginTransaction();
        try {
            $holding = ShareHolding::findOrFail($id);

            $nominal = (float) $request->nominal_value_per_share;
            $shares  = (int) $request->total_shares;
            $totalInvestment = $nominal * $shares;

            $holding->update([
                'share_code'              => $request->share_code,
                'entity_name'             => $request->entity_name,
                'total_shares'            => $shares,
                'nominal_value_per_share' => $nominal,
                'total_investment'        => $totalInvestment,
                'certificate_number'      => $request->certificate_number,
                'acquisition_date'        => $request->acquisition_date,
                'status'                  => $request->status,
            ]);

            DB::commit();

            $this->shareholderService->recalculatePercentages();

            return redirect()->route('shareholders.show', $holding->shareholder_id)
                ->with('success', "Data kepemilikan saham {$holding->share_code} berhasil diperbarui.");
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal memperbarui data saham: ' . $e->getMessage());
        }
    }

    /**
     * Hapus data alokasi saham.
     */
    public function destroy($id)
    {
        $holding = ShareHolding::findOrFail($id);
        $shareholderId = $holding->shareholder_id;
        $holding->delete();

        $this->shareholderService->recalculatePercentages();

        return redirect()->route('shareholders.show', $shareholderId)
            ->with('success', 'Data kepemilikan saham berhasil dihapus.');
    }
}
