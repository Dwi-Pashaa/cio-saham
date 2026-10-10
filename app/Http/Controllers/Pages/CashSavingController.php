<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;
use App\Models\CashIncome;
use App\Models\CashOutcome;
use App\Models\CashSaving;
use App\Models\Setting;
use App\Services\CashNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class CashSavingController extends Controller
{
    /**
     * Simpan alokasi/pembagian saldo kas ke tabungan.
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        $isAdmin = $user ? $user->hasRole('Admin') : false;
        $defaultAccount = Setting::getDefaultSavingsAccount();

        // Jika bukan admin dan rekening default sudah tersimpan, pakai data tersimpan
        if (!$isAdmin && !empty($defaultAccount['is_configured'])) {
            $recipientName = $defaultAccount['recipient_name'];
            $bankName      = $defaultAccount['bank_name'];
            $accountNumber = $defaultAccount['account_number'];
        } else {
            $recipientName = $request->input('recipient_name') ?: ($defaultAccount['recipient_name'] ?? null);
            $bankName      = $request->input('bank_name') ?: ($defaultAccount['bank_name'] ?? null);
            $accountNumber = $request->input('account_number') ?: ($defaultAccount['account_number'] ?? null);
        }

        $request->merge([
            'recipient_name' => $recipientName,
            'bank_name'      => $bankName,
            'account_number' => $accountNumber,
        ]);

        $request->validate([
            'transaction_date' => 'required|date',
            'amount'           => 'required',
            'recipient_name'   => 'required|string|max:255',
            'bank_name'        => 'required|string|max:100',
            'account_number'   => 'nullable|string|max:50',
            'notes'            => 'nullable|string',
            'proof_file'       => 'nullable|file|mimes:jpeg,png,jpg,webp,pdf|max:5120',
        ], [
            'transaction_date.required' => 'Tanggal alokasi tabungan wajib diisi.',
            'amount.required'           => 'Nominal alokasi tabungan wajib diisi.',
            'recipient_name.required'   => 'Nama penerima / pemilik rekening wajib diisi.',
            'bank_name.required'        => 'Nama bank tujuan wajib dipilih / diisi.',
            'proof_file.mimes'          => 'Format bukti transfer harus berupa JPG, PNG, WEBP, atau PDF.',
            'proof_file.max'            => 'Ukuran file bukti transfer maksimal 5MB.',
        ]);

        // Parsing nominal Rupiah
        $rawAmount = (float) str_replace(['.', ','], ['', '.'], str_replace(['Rp', ' ', '.'], '', $request->amount));

        if ($rawAmount <= 0) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Nominal alokasi tabungan harus lebih besar dari Rp 0.',
                ], 422);
            }
            return back()->with('error', 'Nominal alokasi tabungan harus lebih besar dari Rp 0.')->withInput();
        }

        // Cek saldo kas saat ini yang tersedia
        $totalIncome = (float) CashIncome::sum('net_amount');
        $totalOutcome = (float) CashOutcome::sum('total_amount');
        $totalSavings = (float) CashSaving::sum('amount');
        $currentAvailableBalance = $totalIncome - $totalOutcome - $totalSavings;

        if ($rawAmount > $currentAvailableBalance) {
            $formattedLimit = 'Rp ' . number_format($currentAvailableBalance, 0, ',', '.');
            $errMsg = "Nominal alokasi (Rp " . number_format($rawAmount, 0, ',', '.') . ") melebihi sisa Saldo Kas saat ini ({$formattedLimit}).";
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'status'  => 'error',
                    'message' => $errMsg,
                ], 422);
            }
            return back()->with('error', $errMsg)->withInput();
        }

        $proofPath = null;
        if ($request->hasFile('proof_file')) {
            $proofPath = $request->file('proof_file')->store('savings/proofs', 'public');
        }

        $saving = CashSaving::create([
            'transaction_number' => CashSaving::generateTransactionNumber(),
            'transaction_date'   => $request->transaction_date,
            'amount'             => $rawAmount,
            'recipient_name'     => $request->recipient_name,
            'bank_name'          => $request->bank_name,
            'account_number'     => $request->account_number,
            'notes'              => $request->notes,
            'proof_file'         => $proofPath,
            'created_by'         => auth()->id(),
        ]);

        // Jika Admin, perbarui data rekening tabungan default di pengaturan sistem
        if ($isAdmin) {
            $setting = Setting::first();
            if ($setting) {
                $setting->update([
                    'savings_recipient_name' => $saving->recipient_name,
                    'savings_bank_name'      => $saving->bank_name,
                    'savings_account_number' => $saving->account_number,
                ]);
            } else {
                Setting::create([
                    'telp'                   => '628123456789',
                    'savings_recipient_name' => $saving->recipient_name,
                    'savings_bank_name'      => $saving->bank_name,
                    'savings_account_number' => $saving->account_number,
                ]);
            }
        }

        // Notifikasi WA bila aktif
        try {
            app(CashNotificationService::class)->notifySavingCreated($saving);
        } catch (\Throwable $e) {
            Log::error('[CashSavingController] Gagal notifikasi WA alokasi tabungan: ' . $e->getMessage());
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'status'  => 'success',
                'message' => 'Alokasi saldo ke tabungan berhasil disimpan!',
                'data'    => $saving,
            ]);
        }

        return redirect()->route('dashboard')->with('success', 'Berhasil membagikan saldo ke tabungan.');
    }

    /**
     * Ambil data detail slip transaksi alokasi tabungan.
     */
    public function show(Request $request, $id)
    {
        $saving = CashSaving::with('creator')->findOrFail($id);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'status' => 'success',
                'data'   => [
                    'id'                 => $saving->id,
                    'transaction_number' => $saving->transaction_number,
                    'transaction_date'   => $saving->transaction_date ? $saving->transaction_date->format('d M Y') : '-',
                    'raw_date'           => $saving->transaction_date ? $saving->transaction_date->format('Y-m-d') : '',
                    'recipient_name'     => $saving->recipient_name,
                    'bank_name'          => $saving->bank_name,
                    'account_number'     => $saving->account_number ?: '-',
                    'amount'             => (float) $saving->amount,
                    'formatted_amount'   => 'Rp ' . number_format($saving->amount, 0, ',', '.'),
                    'notes'              => $saving->notes ?: '-',
                    'proof_file'         => $saving->proof_file,
                    'proof_url'          => $saving->proof_url,
                    'creator_name'       => $saving->creator ? $saving->creator->name : 'Sistem',
                    'created_at'         => $saving->created_at ? $saving->created_at->format('d M Y H:i') : '-',
                ],
            ]);
        }

        return response()->json(['status' => 'success', 'data' => $saving]);
    }

    /**
     * Hapus data alokasi tabungan.
     */
    public function destroy(Request $request, $id)
    {
        $saving = CashSaving::findOrFail($id);

        if ($saving->proof_file && Storage::disk('public')->exists($saving->proof_file)) {
            Storage::disk('public')->delete($saving->proof_file);
        }

        $saving->delete();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'status'  => 'success',
                'message' => 'Catatan alokasi tabungan berhasil dihapus dan saldo kas telah dikembalikan.',
            ]);
        }

        return redirect()->route('dashboard')->with('success', 'Catatan alokasi tabungan berhasil dihapus.');
    }
}
