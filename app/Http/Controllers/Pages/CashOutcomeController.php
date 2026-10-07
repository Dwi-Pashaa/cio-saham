<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;
use App\Models\CashOutcome;
use App\Services\CashNotificationService;
use App\Services\XenditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;

class CashOutcomeController extends Controller
{
    public function index(Request $request)
    {
        $query = CashOutcome::with('creator')->orderBy('transaction_date', 'desc')->orderBy('id', 'desc');

        if ($request->filled('start_date')) {
            $query->whereDate('transaction_date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('transaction_date', '<=', $request->end_date);
        }

        // Summary metrics
        $totalOutcomeSum  = (clone $query)->sum('amount');
        $totalAdminFeeSum = (clone $query)->sum('admin_fee');
        $totalOverallSum  = (clone $query)->sum('total_amount');
        $totalCount       = (clone $query)->count();

        if ($request->ajax()) {
            return DataTables::eloquent($query)
                ->filter(function ($q) use ($request) {
                    if ($request->has('search') && !empty($request->input('search.value'))) {
                        $search = trim($request->input('search.value'));
                        $q->where(function ($sub) use ($search) {
                            $sub->where('transaction_number', 'like', "%{$search}%")
                                ->orWhere('recipient_name', 'like', "%{$search}%")
                                ->orWhere('bank_name', 'like', "%{$search}%")
                                ->orWhere('account_number', 'like', "%{$search}%")
                                ->orWhere('notes', 'like', "%{$search}%");
                        });
                    }
                })
                ->addColumn('transaction_number_date', function ($item) {
                    $trxNum = e($item->transaction_number);
                    $dateStr = $item->transaction_date ? $item->transaction_date->format('d M Y') : '-';
                    return '<span class="badge bg-red-lt font-monospace fw-bold" style="font-size: 0.72rem;">' . $trxNum . '</span>
                            <div class="text-muted small mt-0.5 font-monospace" style="font-size: 0.74rem;">' . $dateStr . '</div>';
                })
                ->addColumn('recipient_info', function ($item) {
                    $name = e($item->recipient_name);
                    $notes = e($item->notes);
                    return '<strong class="text-dark d-block fs-4">' . $name . '</strong>
                            <div class="text-muted small text-truncate" style="max-width: 220px; font-size: 0.73rem;" title="' . $notes . '">' . $notes . '</div>';
                })
                ->addColumn('bank_info', function ($item) {
                    $bank = e($item->bank_name);
                    $acc = e($item->account_number);
                    return '<span class="badge bg-secondary-lt fw-bold font-monospace" style="font-size: 0.7rem;">' . $bank . '</span>
                            <div class="font-monospace text-dark small mt-0.5 d-flex align-items-center gap-1" style="font-size: 0.76rem;">
                                <span>' . $acc . '</span>
                                <button type="button" class="btn btn-ghost-secondary p-0 btn-copy-account" data-clipboard-text="' . $acc . '" title="Salin nomor rekening" style="width: 16px; height: 16px; line-height: 1;">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M8 8m0 2a2 2 0 0 1 2 -2h8a2 2 0 0 1 2 2v8a2 2 0 0 1 -2 2h-8a2 2 0 0 1 -2 -2z" /><path d="M16 8v-2a2 2 0 0 0 -2 -2h-8a2 2 0 0 0 -2 2v8a2 2 0 0 0 2 2h2" /></svg>
                                </button>
                            </div>';
                })
                ->addColumn('formatted_amount', function ($item) {
                    return '<span class="font-monospace fw-semibold text-dark fs-4" style="white-space: nowrap;">Rp ' . number_format($item->amount, 0, ',', '.') . '</span>';
                })
                ->addColumn('formatted_admin_fee', function ($item) {
                    if ($item->has_admin_fee && $item->admin_fee > 0) {
                        return '<span class="badge bg-warning-lt font-monospace text-warning fw-bold" style="font-size: 0.72rem;">+Rp ' . number_format($item->admin_fee, 0, ',', '.') . '</span>';
                    }
                    return '<span class="badge bg-light text-muted font-monospace" style="font-size: 0.7rem;">Rp 0</span>';
                })
                ->addColumn('formatted_total_amount', function ($item) {
                    return '<span class="font-monospace fw-bold text-danger fs-4" style="white-space: nowrap;">-Rp ' . number_format($item->total_amount, 0, ',', '.') . '</span>';
                })
                ->addColumn('files_badge', function ($item) {
                    $html = '<div class="d-inline-flex align-items-center gap-1">';
                    if ($item->proof_file) {
                        $html .= '<button type="button" class="btn btn-sm btn-ghost-primary px-1.5 py-0.5 btn-preview-proof" data-id="' . $item->id . '" title="Lihat Bukti">
                                    <span class="badge bg-blue-lt"><svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="me-0.5"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 12a2 2 0 1 0 4 0a2 2 0 0 0 -4 0" /><path d="M21 12c-2.4 4 -5.4 6 -9 6c-3.6 0 -6.6 -2 -9 -6c2.4 -4 5.4 -6 9 -6c3.6 0 6.6 2 9 6" /></svg>Bukti</span>
                                  </button>';
                    }
                    if ($item->receipt_file) {
                        $html .= '<button type="button" class="btn btn-sm btn-ghost-success px-1.5 py-0.5 btn-preview-receipt" data-id="' . $item->id . '" title="Lihat Nota">
                                    <span class="badge bg-green-lt"><svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="me-0.5"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M14 3v4a1 1 0 0 0 1 1h4" /><path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z" /><path d="M9 17h6" /><path d="M9 13h6" /></svg>Nota</span>
                                  </button>';
                    }
                    if (!$item->proof_file && !$item->receipt_file) {
                        $html .= '<span class="text-muted small">-</span>';
                    }
                    $html .= '</div>';
                    return $html;
                })
                ->addColumn('action', function ($item) {
                    $user = auth()->user();
                    $btnShow = '<button type="button" class="btn-action btn-action-primary btn-show-outcome" data-id="' . $item->id . '" title="Detail Slip Transaksi">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M14 3v4a1 1 0 0 0 1 1h4" /><path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z" /><path d="M9 17h6" /><path d="M9 13h6" /></svg>
                                </button>';

                    $btnEdit = '';
                    if ($user && $user->can('ubah pengeluaran')) {
                        $btnEdit = '<button type="button" class="btn-action btn-action-warning btn-edit-outcome" data-id="' . $item->id . '" title="Ubah Data">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M7 7h-1a2 2 0 0 0 -2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2 -2v-1" /><path d="M20.385 6.585a2.1 2.1 0 0 0 -2.97 -2.97l-8.415 8.385v3h3l8.385 -8.415z" /><path d="M16 5l3 3" /></svg>
                                    </button>';
                    }

                    $btnDelete = '';
                    if ($user && $user->can('hapus pengeluaran')) {
                        $btnDelete = '<button type="button" class="btn-action btn-action-danger btn-delete-outcome" data-id="' . $item->id . '" data-name="' . e($item->recipient_name) . '" title="Hapus">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 7l16 0" /><path d="M10 11l0 6" /><path d="M14 11l0 6" /><path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" /><path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" /></svg>
                                    </button>';
                    }

                    return '<div class="action-btn-group d-flex justify-content-center align-items-center gap-1">' . $btnShow . $btnEdit . $btnDelete . '</div>';
                })
                ->rawColumns(['transaction_number_date', 'recipient_info', 'bank_info', 'formatted_amount', 'formatted_admin_fee', 'formatted_total_amount', 'files_badge', 'action'])
                ->with([
                    'totalOutcomeSum'  => number_format($totalOutcomeSum, 0, ',', '.'),
                    'totalAdminFeeSum' => number_format($totalAdminFeeSum, 0, ',', '.'),
                    'totalOverallSum'  => number_format($totalOverallSum, 0, ',', '.'),
                    'totalCount'       => $totalCount,
                ])
                ->make(true);
        }

        $banksGrouped = XenditService::getSupportedBanks();
        $transactionNumber = CashOutcome::generateTransactionNumber();

        return view('pages.cash-outcomes.index', compact(
            'totalOutcomeSum',
            'totalAdminFeeSum',
            'totalOverallSum',
            'totalCount',
            'banksGrouped',
            'transactionNumber'
        ));
    }

    public function create()
    {
        $transactionNumber = CashOutcome::generateTransactionNumber();
        $banksGrouped = XenditService::getSupportedBanks();
        return view('pages.cash-outcomes.create', compact('transactionNumber', 'banksGrouped'));
    }

    public function store(Request $request)
    {
        if (!$request->has('recipient_name') && $request->has('recipient_account_name')) {
            $request->merge(['recipient_name' => $request->recipient_account_name]);
        }

        $request->validate([
            'transaction_date' => 'required|date',
            'recipient_name'   => 'required|string|max:255',
            'bank_name'        => 'required|string|max:100',
            'account_number'   => 'required|string|max:100',
            'amount'           => 'required',
            'has_admin_fee'    => 'required|in:ya,tidak,1,0',
            'admin_fee'        => 'nullable',
            'proof_file'       => 'required|file|mimes:jpg,jpeg,png,webp,pdf|max:5120',
            'receipt_file'     => 'nullable|file|mimes:jpg,jpeg,png,webp,pdf|max:5120',
            'notes'            => 'required|string',
        ], [
            'proof_file.required'   => 'Bukti transfer / pembayaran wajib diunggah.',
            'proof_file.uploaded'   => 'File bukti transfer gagal diunggah karena melebihi batas upload server. Silakan kompres foto atau gunakan file di bawah 5MB.',
            'proof_file.mimes'      => 'Format bukti transfer harus berupa JPG, PNG, WEBP, atau PDF.',
            'proof_file.max'        => 'Ukuran file bukti transfer maksimal 5MB.',
            'receipt_file.uploaded' => 'File nota/kuitansi gagal diunggah karena melebihi batas upload server. Silakan kompres foto atau gunakan file di bawah 5MB.',
            'receipt_file.mimes'    => 'Format nota/kuitansi harus berupa JPG, PNG, WEBP, atau PDF.',
            'receipt_file.max'      => 'Ukuran file nota/kuitansi maksimal 5MB.',
        ]);

        $rawAmount = (float) str_replace(['.', ','], ['', '.'], str_replace(['Rp', ' ', '.'], '', $request->amount));
        $hasAdmin  = in_array($request->has_admin_fee, ['ya', '1', 1, true], true);
        
        $rawAdminFee = 0;
        if ($hasAdmin && $request->filled('admin_fee')) {
            $rawAdminFee = (float) str_replace(['.', ','], ['', '.'], str_replace(['Rp', ' ', '.'], '', $request->admin_fee));
        }

        $totalAmount = $rawAmount + $rawAdminFee;

        $proofPath = $request->file('proof_file')->store('outcomes/proofs', 'public');
        
        $receiptPath = null;
        if ($request->hasFile('receipt_file')) {
            $receiptPath = $request->file('receipt_file')->store('outcomes/receipts', 'public');
        }

        $outcome = CashOutcome::create([
            'transaction_number' => CashOutcome::generateTransactionNumber(),
            'transaction_date'   => $request->transaction_date,
            'recipient_name'     => $request->recipient_name,
            'bank_name'          => $request->bank_name,
            'account_number'     => $request->account_number,
            'amount'             => $rawAmount,
            'has_admin_fee'      => $hasAdmin,
            'admin_fee'          => $rawAdminFee,
            'total_amount'       => $totalAmount,
            'proof_file'         => $proofPath,
            'receipt_file'       => $receiptPath,
            'notes'              => $request->notes,
            'is_asset'           => false,
            'created_by'         => auth()->id(),
        ]);

        // Kirim Notifikasi WhatsApp Otomatis ke Manajemen
        try {
            app(CashNotificationService::class)->notifyOutcomeCreated($outcome);
        } catch (\Throwable $e) {
            Log::error('[CashOutcomeController] Gagal mengirim notifikasi WA kas keluar: ' . $e->getMessage());
        }

        return redirect()->route('cash-outcomes.index')->with('success', 'Catatan saldo keluar berhasil ditambahkan.');
    }

    public function show(Request $request, $id)
    {
        $outcome = CashOutcome::with('creator')->findOrFail($id);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'status' => 'success',
                'data'   => [
                    'id'                 => $outcome->id,
                    'transaction_number' => $outcome->transaction_number,
                    'transaction_date'   => $outcome->transaction_date ? $outcome->transaction_date->format('Y-m-d') : null,
                    'transaction_date_formatted' => $outcome->transaction_date ? $outcome->transaction_date->format('d F Y') : '-',
                    'recipient_name'     => $outcome->recipient_name,
                    'bank_name'          => $outcome->bank_name,
                    'account_number'     => $outcome->account_number,
                    'amount'             => (float) $outcome->amount,
                    'amount_formatted'   => number_format($outcome->amount, 0, ',', '.'),
                    'has_admin_fee'      => (bool) $outcome->has_admin_fee,
                    'admin_fee'          => (float) $outcome->admin_fee,
                    'admin_fee_formatted' => number_format($outcome->admin_fee, 0, ',', '.'),
                    'total_amount'       => (float) $outcome->total_amount,
                    'total_amount_formatted' => number_format($outcome->total_amount, 0, ',', '.'),
                    'proof_file'         => $outcome->proof_file,
                    'proof_url'          => $outcome->proof_url,
                    'proof_is_image'     => $outcome->proof_file ? in_array(strtolower(pathinfo($outcome->proof_file, PATHINFO_EXTENSION)), ['jpg','jpeg','png','webp']) : false,
                    'receipt_file'       => $outcome->receipt_file,
                    'receipt_url'        => $outcome->receipt_url,
                    'receipt_is_image'   => $outcome->receipt_file ? in_array(strtolower(pathinfo($outcome->receipt_file, PATHINFO_EXTENSION)), ['jpg','jpeg','png','webp']) : false,
                    'notes'              => $outcome->notes,
                    'creator_name'       => $outcome->creator?->name ?? 'Sistem',
                    'created_at_formatted' => $outcome->created_at ? $outcome->created_at->format('d M Y, H:i') . ' WIB' : '-',
                ]
            ]);
        }

        return view('pages.cash-outcomes.show', compact('outcome'));
    }

    public function edit(Request $request, $id)
    {
        $outcome = CashOutcome::findOrFail($id);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'status' => 'success',
                'data'   => [
                    'id'                 => $outcome->id,
                    'transaction_number' => $outcome->transaction_number,
                    'transaction_date'   => $outcome->transaction_date ? $outcome->transaction_date->format('Y-m-d') : null,
                    'recipient_name'     => $outcome->recipient_name,
                    'bank_name'          => $outcome->bank_name,
                    'account_number'     => $outcome->account_number,
                    'amount'             => number_format($outcome->amount, 0, ',', '.'),
                    'has_admin_fee'      => $outcome->has_admin_fee ? 'ya' : 'tidak',
                    'admin_fee'          => number_format($outcome->admin_fee, 0, ',', '.'),
                    'proof_url'          => $outcome->proof_url,
                    'receipt_url'        => $outcome->receipt_url,
                    'notes'              => $outcome->notes,
                    'update_url'         => route('cash-outcomes.update', $outcome->id),
                ]
            ]);
        }

        $banksGrouped = XenditService::getSupportedBanks();
        return view('pages.cash-outcomes.edit', compact('outcome', 'banksGrouped'));
    }

    public function update(Request $request, $id)
    {
        $outcome = CashOutcome::findOrFail($id);

        if (!$request->has('recipient_name') && $request->has('recipient_account_name')) {
            $request->merge(['recipient_name' => $request->recipient_account_name]);
        }

        $request->validate([
            'transaction_date' => 'required|date',
            'recipient_name'   => 'required|string|max:255',
            'bank_name'        => 'required|string|max:100',
            'account_number'   => 'required|string|max:100',
            'amount'           => 'required',
            'has_admin_fee'    => 'required|in:ya,tidak,1,0',
            'admin_fee'        => 'nullable',
            'proof_file'       => 'nullable|file|mimes:jpg,jpeg,png,webp,pdf|max:5120',
            'receipt_file'     => 'nullable|file|mimes:jpg,jpeg,png,webp,pdf|max:5120',
            'notes'            => 'required|string',
        ], [
            'proof_file.uploaded'   => 'File bukti transfer gagal diunggah karena melebihi batas upload server. Silakan kompres foto atau gunakan file di bawah 5MB.',
            'proof_file.mimes'      => 'Format bukti transfer harus berupa JPG, PNG, WEBP, atau PDF.',
            'proof_file.max'        => 'Ukuran file bukti transfer maksimal 5MB.',
            'receipt_file.uploaded' => 'File nota/kuitansi gagal diunggah karena melebihi batas upload server. Silakan kompres foto atau gunakan file di bawah 5MB.',
            'receipt_file.mimes'    => 'Format nota/kuitansi harus berupa JPG, PNG, WEBP, atau PDF.',
            'receipt_file.max'      => 'Ukuran file nota/kuitansi maksimal 5MB.',
        ]);

        $rawAmount = (float) str_replace(['.', ','], ['', '.'], str_replace(['Rp', ' ', '.'], '', $request->amount));
        $hasAdmin  = in_array($request->has_admin_fee, ['ya', '1', 1, true], true);
        $isAsset   = in_array($request->is_asset, ['ya', '1', 1, true], true);
        
        $rawAdminFee = 0;
        if ($hasAdmin && $request->filled('admin_fee')) {
            $rawAdminFee = (float) str_replace(['.', ','], ['', '.'], str_replace(['Rp', ' ', '.'], '', $request->admin_fee));
        }

        $totalAmount = $rawAmount + $rawAdminFee;

        $data = [
            'transaction_date' => $request->transaction_date,
            'recipient_name'   => $request->recipient_name,
            'bank_name'        => $request->bank_name,
            'account_number'   => $request->account_number,
            'amount'           => $rawAmount,
            'has_admin_fee'    => $hasAdmin,
            'admin_fee'        => $rawAdminFee,
            'total_amount'     => $totalAmount,
            'notes'            => $request->notes,
            'is_asset'         => $isAsset,
        ];

        if ($request->hasFile('proof_file')) {
            if ($outcome->proof_file && Storage::disk('public')->exists($outcome->proof_file)) {
                Storage::disk('public')->delete($outcome->proof_file);
            }
            $data['proof_file'] = $request->file('proof_file')->store('outcomes/proofs', 'public');
        }

        if ($request->hasFile('receipt_file')) {
            if ($outcome->receipt_file && Storage::disk('public')->exists($outcome->receipt_file)) {
                Storage::disk('public')->delete($outcome->receipt_file);
            }
            $data['receipt_file'] = $request->file('receipt_file')->store('outcomes/receipts', 'public');
        }

        $outcome->update($data);

        return redirect()->route('cash-outcomes.index')->with('success', 'Catatan saldo keluar berhasil diperbarui.');
    }

    public function destroy(Request $request, $id)
    {
        $outcome = CashOutcome::findOrFail($id);
        if ($outcome->proof_file && Storage::disk('public')->exists($outcome->proof_file)) {
            Storage::disk('public')->delete($outcome->proof_file);
        }
        if ($outcome->receipt_file && Storage::disk('public')->exists($outcome->receipt_file)) {
            Storage::disk('public')->delete($outcome->receipt_file);
        }
        $outcome->delete();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'code'    => 200,
                'status'  => 'success',
                'message' => 'Catatan saldo keluar berhasil dihapus.'
            ]);
        }

        return redirect()->route('cash-outcomes.index')->with('success', 'Catatan saldo keluar berhasil dihapus.');
    }
}

