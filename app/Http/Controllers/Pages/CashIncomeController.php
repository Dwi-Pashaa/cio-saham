<?php

namespace App\Http\Controllers\Pages;

use App\Exports\CashIncomeExport;
use App\Http\Controllers\Controller;
use App\Models\CashIncome;
use App\Services\CashNotificationService;
use App\Services\XenditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

class CashIncomeController extends Controller
{
    /**
     * Unduh daftar pemasukan kas ke format file Excel (.xlsx).
     */
    public function export(Request $request)
    {
        $fileName = 'pemasukan_kas_pt_cio_' . date('Ymd_His') . '.xlsx';
        return Excel::download(
            new CashIncomeExport(
                $request->start_date,
                $request->end_date
            ),
            $fileName
        );
    }

    public function index(Request $request)
    {
        $query = CashIncome::with('creator')->orderBy('transaction_date', 'desc')->orderBy('id', 'desc');

        if ($request->filled('start_date')) {
            $query->whereDate('transaction_date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('transaction_date', '<=', $request->end_date);
        }

        // Summary metrics
        $totalIncomeSum   = (clone $query)->sum('amount');
        $totalAdminFeeSum = (clone $query)->sum('admin_fee');
        $totalNetSum      = (clone $query)->sum('net_amount');
        $totalCount       = (clone $query)->count();

        if ($request->ajax()) {
            return DataTables::eloquent($query)
                ->filter(function ($q) use ($request) {
                    if ($request->has('search') && !empty($request->input('search.value'))) {
                        $search = trim($request->input('search.value'));
                        $q->where(function ($sub) use ($search) {
                            $sub->where('transaction_number', 'like', "%{$search}%")
                                ->orWhere('sender_name', 'like', "%{$search}%")
                                ->orWhere('bank_name', 'like', "%{$search}%")
                                ->orWhere('account_number', 'like', "%{$search}%")
                                ->orWhere('notes', 'like', "%{$search}%");
                        });
                    }
                })
                ->addColumn('transaction_number_date', function ($item) {
                    $trxNum = e($item->transaction_number);
                    $dateStr = $item->transaction_date ? $item->transaction_date->format('d M Y') : '-';
                    return '<span class="badge bg-green-lt font-monospace fw-bold" style="font-size: 0.72rem;">' . $trxNum . '</span>
                            <div class="text-muted small mt-0.5 font-monospace" style="font-size: 0.74rem;">' . $dateStr . '</div>';
                })
                ->addColumn('sender_info', function ($item) {
                    $name = e($item->sender_name);
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
                    return '<span class="font-monospace fw-bold text-success fs-4" style="white-space: nowrap;">+Rp ' . number_format($item->amount, 0, ',', '.') . '</span>';
                })
                ->addColumn('formatted_admin_fee', function ($item) {
                    if ($item->has_admin_fee && $item->admin_fee > 0) {
                        return '<span class="badge bg-warning-lt font-monospace text-warning fw-bold" style="font-size: 0.72rem;">Rp ' . number_format($item->admin_fee, 0, ',', '.') . '</span>';
                    }
                    return '<span class="badge bg-light text-muted font-monospace" style="font-size: 0.7rem;">Rp 0</span>';
                })
                ->addColumn('formatted_net_amount', function ($item) {
                    return '<span class="font-monospace fw-bold text-primary fs-4" style="white-space: nowrap;">Rp ' . number_format($item->net_amount, 0, ',', '.') . '</span>';
                })
                ->addColumn('proof_badge', function ($item) {
                    if ($item->proof_file) {
                        return '<button type="button" class="btn btn-sm btn-ghost-primary px-2 py-0.5 btn-preview-proof" data-id="' . $item->id . '" title="Lihat Bukti">
                                    <span class="badge bg-blue-lt"><svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="me-0.5"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 12a2 2 0 1 0 4 0a2 2 0 0 0 -4 0" /><path d="M21 12c-2.4 4 -5.4 6 -9 6c-3.6 0 -6.6 -2 -9 -6c2.4 -4 5.4 -6 9 -6c3.6 0 6.6 2 9 6" /></svg>Bukti</span>
                                </button>';
                    }
                    return '<span class="text-muted small">-</span>';
                })
                ->addColumn('action', function ($item) {
                    $user = auth()->user();
                    $btnShow = '<button type="button" class="btn-action btn-action-primary btn-show-income" data-id="' . $item->id . '" title="Detail Slip Transaksi">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M14 3v4a1 1 0 0 0 1 1h4" /><path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z" /><path d="M9 17h6" /><path d="M9 13h6" /></svg>
                                </button>';

                    $btnEdit = '';
                    if ($user && $user->can('ubah pemasukan')) {
                        $btnEdit = '<button type="button" class="btn-action btn-action-warning btn-edit-income" data-id="' . $item->id . '" title="Ubah Data">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M7 7h-1a2 2 0 0 0 -2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2 -2v-1" /><path d="M20.385 6.585a2.1 2.1 0 0 0 -2.97 -2.97l-8.415 8.385v3h3l8.385 -8.415z" /><path d="M16 5l3 3" /></svg>
                                    </button>';
                    }

                    $btnDelete = '';
                    if ($user && $user->can('hapus pemasukan')) {
                        $btnDelete = '<button type="button" class="btn-action btn-action-danger btn-delete-income" data-id="' . $item->id . '" data-name="' . e($item->sender_name) . '" title="Hapus">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 7l16 0" /><path d="M10 11l0 6" /><path d="M14 11l0 6" /><path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" /><path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" /></svg>
                                    </button>';
                    }

                    return '<div class="action-btn-group d-flex justify-content-center align-items-center gap-1">' . $btnShow . $btnEdit . $btnDelete . '</div>';
                })
                ->rawColumns(['transaction_number_date', 'sender_info', 'bank_info', 'formatted_amount', 'formatted_admin_fee', 'formatted_net_amount', 'proof_badge', 'action'])
                ->with([
                    'totalIncomeSum'   => number_format($totalIncomeSum, 0, ',', '.'),
                    'totalAdminFeeSum' => number_format($totalAdminFeeSum, 0, ',', '.'),
                    'totalNetSum'      => number_format($totalNetSum, 0, ',', '.'),
                    'totalCount'       => $totalCount,
                ])
                ->make(true);
        }

        $banksGrouped = XenditService::getSupportedBanks();
        $transactionNumber = CashIncome::generateTransactionNumber();

        return view('pages.cash-incomes.index', compact(
            'totalIncomeSum',
            'totalAdminFeeSum',
            'totalNetSum',
            'totalCount',
            'banksGrouped',
            'transactionNumber'
        ));
    }

    public function create()
    {
        $transactionNumber = CashIncome::generateTransactionNumber();
        $banksGrouped = XenditService::getSupportedBanks();
        return view('pages.cash-incomes.create', compact('transactionNumber', 'banksGrouped'));
    }

    public function store(Request $request)
    {
        if (!$request->has('sender_name') && $request->has('source_account_name')) {
            $request->merge(['sender_name' => $request->source_account_name]);
        }

        $request->validate([
            'transaction_date' => 'required|date',
            'sender_name'      => 'required|string|max:255',
            'bank_name'        => 'required|string|max:100',
            'account_number'   => 'required|string|max:100',
            'amount'           => 'required',
            'has_admin_fee'    => 'required|in:ya,tidak,1,0',
            'admin_fee'        => 'nullable',
            'proof_file'       => 'required|file|mimes:jpg,jpeg,png,webp,pdf|max:5120',
            'notes'            => 'required|string',
        ], [
            'proof_file.required' => 'Bukti transaksi wajib diunggah.',
            'proof_file.uploaded' => 'File bukti transaksi gagal diunggah karena melebihi batas upload server. Silakan kompres foto atau gunakan file di bawah 5MB.',
            'proof_file.mimes'    => 'Format bukti transaksi harus berupa JPG, PNG, WEBP, atau PDF.',
            'proof_file.max'      => 'Ukuran file bukti transaksi maksimal 5MB.',
        ]);

        $rawAmount = (float) str_replace(['.', ','], ['', '.'], str_replace(['Rp', ' ', '.'], '', $request->amount));
        $hasAdmin  = in_array($request->has_admin_fee, ['ya', '1', 1, true], true);
        
        $rawAdminFee = 0;
        if ($hasAdmin && $request->filled('admin_fee')) {
            $rawAdminFee = (float) str_replace(['.', ','], ['', '.'], str_replace(['Rp', ' ', '.'], '', $request->admin_fee));
        }

        $netAmount = max(0, $rawAmount - $rawAdminFee);

        $proofPath = $request->file('proof_file')->store('incomes', 'public');

        $income = CashIncome::create([
            'transaction_number' => CashIncome::generateTransactionNumber(),
            'transaction_date'   => $request->transaction_date,
            'sender_name'        => $request->sender_name,
            'bank_name'          => $request->bank_name,
            'account_number'     => $request->account_number,
            'amount'             => $rawAmount,
            'has_admin_fee'      => $hasAdmin,
            'admin_fee'          => $rawAdminFee,
            'net_amount'         => $netAmount,
            'proof_file'         => $proofPath,
            'notes'              => $request->notes,
            'created_by'         => auth()->id(),
        ]);

        // Kirim Notifikasi WhatsApp Otomatis ke Manajemen
        try {
            app(CashNotificationService::class)->notifyIncomeCreated($income);
        } catch (\Throwable $e) {
            Log::error('[CashIncomeController] Gagal mengirim notifikasi WA kas masuk: ' . $e->getMessage());
        }

        return redirect()->route('cash-incomes.index')->with('success', 'Catatan saldo masuk berhasil ditambahkan.');
    }

    public function show(Request $request, $id)
    {
        $income = CashIncome::with('creator')->findOrFail($id);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'status' => 'success',
                'data'   => [
                    'id'                 => $income->id,
                    'transaction_number' => $income->transaction_number,
                    'transaction_date'   => $income->transaction_date ? $income->transaction_date->format('Y-m-d') : null,
                    'transaction_date_formatted' => $income->transaction_date ? $income->transaction_date->format('d F Y') : '-',
                    'sender_name'        => $income->sender_name,
                    'bank_name'          => $income->bank_name,
                    'account_number'     => $income->account_number,
                    'amount'             => (float) $income->amount,
                    'amount_formatted'   => number_format($income->amount, 0, ',', '.'),
                    'has_admin_fee'      => (bool) $income->has_admin_fee,
                    'admin_fee'          => (float) $income->admin_fee,
                    'admin_fee_formatted' => number_format($income->admin_fee, 0, ',', '.'),
                    'net_amount'         => (float) $income->net_amount,
                    'net_amount_formatted' => number_format($income->net_amount, 0, ',', '.'),
                    'proof_file'         => $income->proof_file,
                    'proof_url'          => $income->proof_url,
                    'proof_is_image'     => $income->proof_file ? in_array(strtolower(pathinfo($income->proof_file, PATHINFO_EXTENSION)), ['jpg','jpeg','png','webp']) : false,
                    'notes'              => $income->notes,
                    'creator_name'       => $income->creator?->name ?? 'Sistem',
                    'created_at_formatted' => $income->created_at ? $income->created_at->format('d M Y, H:i') . ' WIB' : '-',
                ]
            ]);
        }

        return view('pages.cash-incomes.show', compact('income'));
    }

    public function edit(Request $request, $id)
    {
        $income = CashIncome::findOrFail($id);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'status' => 'success',
                'data'   => [
                    'id'                 => $income->id,
                    'transaction_number' => $income->transaction_number,
                    'transaction_date'   => $income->transaction_date ? $income->transaction_date->format('Y-m-d') : null,
                    'sender_name'        => $income->sender_name,
                    'bank_name'          => $income->bank_name,
                    'account_number'     => $income->account_number,
                    'amount'             => number_format($income->amount, 0, ',', '.'),
                    'has_admin_fee'      => $income->has_admin_fee ? 'ya' : 'tidak',
                    'admin_fee'          => number_format($income->admin_fee, 0, ',', '.'),
                    'proof_url'          => $income->proof_url,
                    'notes'              => $income->notes,
                    'update_url'         => route('cash-incomes.update', $income->id),
                ]
            ]);
        }

        $banksGrouped = XenditService::getSupportedBanks();
        return view('pages.cash-incomes.edit', compact('income', 'banksGrouped'));
    }

    public function update(Request $request, $id)
    {
        $income = CashIncome::findOrFail($id);

        if (!$request->has('sender_name') && $request->has('source_account_name')) {
            $request->merge(['sender_name' => $request->source_account_name]);
        }

        $request->validate([
            'transaction_date' => 'required|date',
            'sender_name'      => 'required|string|max:255',
            'bank_name'        => 'required|string|max:100',
            'account_number'   => 'required|string|max:100',
            'amount'           => 'required',
            'has_admin_fee'    => 'required|in:ya,tidak,1,0',
            'admin_fee'        => 'nullable',
            'proof_file'       => 'nullable|file|mimes:jpg,jpeg,png,webp,pdf|max:5120',
            'notes'            => 'required|string',
        ], [
            'proof_file.uploaded' => 'File bukti transaksi gagal diunggah karena melebihi batas upload server. Silakan kompres foto atau gunakan file di bawah 5MB.',
            'proof_file.mimes'    => 'Format bukti transaksi harus berupa JPG, PNG, WEBP, atau PDF.',
            'proof_file.max'      => 'Ukuran file bukti transaksi maksimal 5MB.',
        ]);

        $rawAmount = (float) str_replace(['.', ','], ['', '.'], str_replace(['Rp', ' ', '.'], '', $request->amount));
        $hasAdmin  = in_array($request->has_admin_fee, ['ya', '1', 1, true], true);
        
        $rawAdminFee = 0;
        if ($hasAdmin && $request->filled('admin_fee')) {
            $rawAdminFee = (float) str_replace(['.', ','], ['', '.'], str_replace(['Rp', ' ', '.'], '', $request->admin_fee));
        }

        $netAmount = max(0, $rawAmount - $rawAdminFee);

        $data = [
            'transaction_date' => $request->transaction_date,
            'sender_name'      => $request->sender_name,
            'bank_name'        => $request->bank_name,
            'account_number'   => $request->account_number,
            'amount'           => $rawAmount,
            'has_admin_fee'    => $hasAdmin,
            'admin_fee'        => $rawAdminFee,
            'net_amount'       => $netAmount,
            'notes'            => $request->notes,
        ];

        if ($request->hasFile('proof_file')) {
            if ($income->proof_file && Storage::disk('public')->exists($income->proof_file)) {
                Storage::disk('public')->delete($income->proof_file);
            }
            $data['proof_file'] = $request->file('proof_file')->store('incomes', 'public');
        }

        $income->update($data);

        return redirect()->route('cash-incomes.index')->with('success', 'Catatan saldo masuk berhasil diperbarui.');
    }

    public function destroy(Request $request, $id)
    {
        $income = CashIncome::findOrFail($id);
        if ($income->proof_file && Storage::disk('public')->exists($income->proof_file)) {
            Storage::disk('public')->delete($income->proof_file);
        }
        $income->delete();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'code'    => 200,
                'status'  => 'success',
                'message' => 'Catatan saldo masuk berhasil dihapus.'
            ]);
        }

        return redirect()->route('cash-incomes.index')->with('success', 'Catatan saldo masuk berhasil dihapus.');
    }
}

