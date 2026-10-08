<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\CashIncome;
use App\Models\CashOutcome;
use App\Models\CashSaving;
use App\Models\Setting;
use App\Models\Shareholder;
use Illuminate\Support\Facades\Log;

class CashNotificationService
{
    protected FonnteService $fonnteService;

    public function __construct(FonnteService $fonnteService)
    {
        $this->fonnteService = $fonnteService;
    }

    /**
     * Dapatkan daftar seluruh nomor WhatsApp penerima notifikasi kas:
     * 1. Seluruh nomor telepon Pemegang Saham / Investor Aktif (Shareholders)
     * 2. Nomor target manajemen / admin dari Pengaturan (target_wa_kas / telp)
     *
     * @return string Comma-separated WhatsApp numbers
     */
    public function getRecipientPhoneNumbers(): string
    {
        $numbers = [];

        // 1. Seluruh nomor telepon Pemegang Saham / Investor Aktif
        $shareholderPhones = Shareholder::where('status', 'active')
            ->whereNotNull('phone')
            ->where('phone', '!=', '')
            ->pluck('phone')
            ->toArray();

        foreach ($shareholderPhones as $phone) {
            if (!empty(trim((string)$phone))) {
                $numbers[] = trim((string)$phone);
            }
        }

        // 2. Nomor Target Manajemen / Admin Kas dari Pengaturan
        $setting = Setting::first();
        $managementTarget = $setting?->target_wa_kas ?: $setting?->telp ?: env('TARGET_WA_KAS');
        if (!empty($managementTarget)) {
            $numbers[] = trim((string)$managementTarget);
        }

        return $this->fonnteService->formatPhoneNumber($numbers);
    }

    /**
     * Dapatkan nomor WhatsApp target tunggal/fallback untuk notifikasi.
     */
    public function getTargetPhoneNumber(): ?string
    {
        return $this->getRecipientPhoneNumbers();
    }

    /**
     * Cek apakah notifikasi WhatsApp aktif di Pengaturan.
     */
    public function isNotificationEnabled(): bool
    {
        $setting = Setting::first();
        $channel = strtolower($setting?->notification_channel ?? 'whatsapp');
        return $channel !== 'none';
    }

    /**
     * Hitung total saldo kas PT saat ini.
     * Saldo = Total Pemasukan Bersih - Total Pengeluaran Kas - Total Alokasi Tabungan
     */
    public function getCurrentCashBalance(): float
    {
        $totalIncome = (float) CashIncome::sum('net_amount');
        $totalOutcome = (float) CashOutcome::sum('total_amount');
        $totalSavings = (float) CashSaving::sum('amount');
        return $totalIncome - $totalOutcome - $totalSavings;
    }

    /**
     * Kirim notifikasi WhatsApp otomatis saat mutasi Saldo Masuk (Pemasukan) dicatat.
     *
     * @param CashIncome $income Record pemasukan yang baru disimpan
     * @return array{status: bool, message: string, response?: mixed}
     */
    public function notifyIncomeCreated(CashIncome $income): array
    {
        if (!$this->isNotificationEnabled()) {
            return ['status' => false, 'message' => 'Notifikasi dinonaktifkan di pengaturan.'];
        }

        $target = $this->getRecipientPhoneNumbers();
        if (empty($target)) {
            Log::warning('[CashNotificationService] Nomor WhatsApp target / investor belum tersedia.');
            return ['status' => false, 'message' => 'Nomor WhatsApp target / investor belum tersedia.'];
        }

        // Hitung Saldo Terbaru dan Saldo Sebelumnya
        $currentBalance = $this->getCurrentCashBalance();
        $netIncome = (float) $income->net_amount;
        $previousBalance = $currentBalance - $netIncome;

        // URL File Bukti Transaksi (diarahkan ke domain aplikasi)
        $proofUrl = $income->proof_url ?: asset('storage/' . $income->proof_file);
        $localFilePath = $income->proof_file ? storage_path('app/public/' . $income->proof_file) : null;

        // Format Pesan Sesuai Spesifikasi Template
        $message = "NOTIFIKASI saldo masuk ke rekening PT CIO NETWORK\n"
                 . "Dari rekening           : " . ($income->sender_name ?: '-') . "\n"
                 . "rekening                : " . ($income->bank_name ?: '-') . "\n"
                 . "no Rekening             : " . ($income->account_number ?: '-') . "\n"
                 . "Nominal                 : Rp " . number_format($income->amount, 0, ',', '.') . "\n"
                 . "poto bukti transaksi    : " . $proofUrl . "\n"
                 . "catatan                 : " . ($income->notes ?: '-') . "\n"
                 . "-------------------------------------\n"
                 . "SALDO SEBELUMNYA: Rp " . number_format($previousBalance, 0, ',', '.') . "\n"
                 . "SALDO TERBARU   : Rp " . number_format($currentBalance, 0, ',', '.') . "\n"
                 . "--------------------------------------";

        // Kirim via Fonnte beserta lampiran gambar bukti
        return $this->fonnteService->sendMessage(
            $target,
            $message,
            $proofUrl,
            'bukti_masuk_' . $income->transaction_number . '.jpg',
            $localFilePath
        );
    }

    /**
     * Kirim notifikasi WhatsApp otomatis saat mutasi Saldo Keluar (Pengeluaran) dicatat.
     *
     * @param CashOutcome $outcome Record pengeluaran yang baru disimpan
     * @return array{status: bool, message: string, response?: mixed}
     */
    public function notifyOutcomeCreated(CashOutcome $outcome): array
    {
        if (!$this->isNotificationEnabled()) {
            return ['status' => false, 'message' => 'Notifikasi dinonaktifkan di pengaturan.'];
        }

        $target = $this->getRecipientPhoneNumbers();
        if (empty($target)) {
            Log::warning('[CashNotificationService] Nomor WhatsApp target / investor belum tersedia.');
            return ['status' => false, 'message' => 'Nomor WhatsApp target / investor belum tersedia.'];
        }

        // Hitung Saldo Terbaru dan Saldo Sebelumnya
        $currentBalance = $this->getCurrentCashBalance();
        $totalOutcome = (float) $outcome->total_amount;
        $previousBalance = $currentBalance + $totalOutcome;

        // Format Tanggal Transaksi: Tanggal/Bulan/Tahun (e.g. 05/10/2026)
        $trxDateFormatted = $outcome->transaction_date
            ? $outcome->transaction_date->format('d/m/Y')
            : now()->format('d/m/Y');

        // Status Biaya Admin
        $adminFeeStr = ($outcome->has_admin_fee && $outcome->admin_fee > 0)
            ? 'Ya, Rp ' . number_format($outcome->admin_fee, 0, ',', '.')
            : 'Tidak (Rp 0)';

        // URL File Bukti & Nota (diarahkan ke domain aplikasi)
        $proofUrl = $outcome->proof_url ?: asset('storage/' . $outcome->proof_file);
        $receiptUrl = $outcome->receipt_file ? ($outcome->receipt_url ?: asset('storage/' . $outcome->receipt_file)) : '-';
        $localFilePath = $outcome->proof_file ? storage_path('app/public/' . $outcome->proof_file) : null;

        // Format Pesan Sesuai Spesifikasi Template
        $message = "NOTIFIKASI saldo Keluar " . $trxDateFormatted . "\n"
                 . "rekening tujuan         : " . ($outcome->recipient_name ?: '-') . "\n"
                 . "rekening                : " . ($outcome->bank_name ?: '-') . "\n"
                 . "no Rekening             : " . ($outcome->account_number ?: '-') . "\n"
                 . "Nominal                 : Rp " . number_format($outcome->amount, 0, ',', '.') . "\n"
                 . "pake admin ya/ tidak    : " . $adminFeeStr . "\n"
                 . "poto bukti transaksi    : " . $proofUrl . "\n"
                 . "poto nota pembelian     : " . $receiptUrl . "\n"
                 . "catatan                 : " . ($outcome->notes ?: '-') . "\n"
                 . "-------------------------------------\n"
                 . "SALDO SEBELUMNYA: Rp " . number_format($previousBalance, 0, ',', '.') . "\n"
                 . "SALDO TERBARU   : Rp " . number_format($currentBalance, 0, ',', '.') . "\n"
                 . "--------------------------------------";

        // Kirim via Fonnte beserta lampiran gambar bukti
        return $this->fonnteService->sendMessage(
            $target,
            $message,
            $proofUrl,
            'bukti_keluar_' . $outcome->transaction_number . '.jpg',
            $localFilePath
        );
    }

    /**
     * Kirim notifikasi WhatsApp otomatis saat Saldo dialokasikan/dibagikan ke Tabungan.
     */
    public function notifySavingCreated(CashSaving $saving): array
    {
        if (!$this->isNotificationEnabled()) {
            return ['status' => false, 'message' => 'Notifikasi dinonaktifkan di pengaturan.'];
        }

        $target = $this->getRecipientPhoneNumbers();
        if (empty($target)) {
            Log::warning('[CashNotificationService] Nomor WhatsApp target / investor belum tersedia.');
            return ['status' => false, 'message' => 'Nomor WhatsApp target / investor belum tersedia.'];
        }

        // Saldo Terkini dan Sebelumnya
        $currentBalance = $this->getCurrentCashBalance();
        $savingAmount = (float) $saving->amount;
        $previousBalance = $currentBalance + $savingAmount;

        $trxDateFormatted = $saving->transaction_date
            ? $saving->transaction_date->format('d/m/Y')
            : now()->format('d/m/Y');

        $proofUrl = $saving->proof_url ?: ($saving->proof_file ? asset('storage/' . $saving->proof_file) : '-');
        $localFilePath = $saving->proof_file ? storage_path('app/public/' . $saving->proof_file) : null;

        $message = "NOTIFIKASI ALOKASI KE TABUNGAN " . $trxDateFormatted . "\n"
                 . "No Transaksi            : " . $saving->transaction_number . "\n"
                 . "Penerima / Rekening     : " . ($saving->recipient_name ?: '-') . "\n"
                 . "Bank Tujuan             : " . ($saving->bank_name ?: '-') . "\n"
                 . "No Rekening             : " . ($saving->account_number ?: '-') . "\n"
                 . "Nominal Tabungan        : Rp " . number_format($savingAmount, 0, ',', '.') . "\n"
                 . "Bukti Transfer          : " . $proofUrl . "\n"
                 . "Catatan                 : " . ($saving->notes ?: '-') . "\n"
                 . "-------------------------------------\n"
                 . "SISA KAS OPERASIONAL    : Rp " . number_format($currentBalance, 0, ',', '.') . "\n"
                 . "TOTAL SALDO TABUNGAN    : Rp " . number_format((float) CashSaving::sum('amount'), 0, ',', '.') . "\n"
                 . "--------------------------------------";

        return $this->fonnteService->sendMessage(
            $target,
            $message,
            $proofUrl !== '-' ? $proofUrl : null,
            'bukti_tabungan_' . $saving->transaction_number . '.jpg',
            $localFilePath
        );
    }

    /**
     * Kirim notifikasi WhatsApp otomatis saat Aset Perusahaan / Investor baru ditambahkan.
     */
    public function notifyAssetCreated(Asset $asset): array
    {
        if (!$this->isNotificationEnabled()) {
            return ['status' => false, 'message' => 'Notifikasi dinonaktifkan di pengaturan.'];
        }

        $target = $this->getRecipientPhoneNumbers();
        if (empty($target)) {
            Log::warning('[CashNotificationService] Nomor WhatsApp target / investor belum tersedia.');
            return ['status' => false, 'message' => 'Nomor WhatsApp target / investor belum tersedia.'];
        }

        $trxDateFormatted = $asset->purchase_date
            ? $asset->purchase_date->format('d/m/Y')
            : now()->format('d/m/Y');

        $primaryImage = $asset->images()->where('is_primary', true)->first() ?: $asset->images()->first();
        $imageUrl = $primaryImage ? asset('storage/' . $primaryImage->image_path) : '-';
        $localFilePath = $primaryImage ? storage_path('app/public/' . $primaryImage->image_path) : null;

        $totalAssetValue = (float) Asset::sum('price');
        $totalAssetCount = (int) Asset::count();

        $ownership = $asset->owner_type === 'pt' ? 'PT CIO NETWORK SOLUTION' : 'Pemegang Saham / Investor';
        $ownerDetail = $asset->owner_type === 'shareholder'
            ? ($asset->shareholder?->name ?: ($asset->owner_name ?: 'Investor'))
            : 'PT CIO NETWORK SOLUTION';

        $message = "NOTIFIKASI PENAMBAHAN ASET PERUSAHAAN\n"
                 . "Tanggal Perolehan       : " . $trxDateFormatted . "\n"
                 . "Nama Aset               : " . $asset->name . "\n"
                 . "Kategori                : " . $asset->type . "\n"
                 . "Nilai / Harga Aset      : Rp " . number_format((float) $asset->price, 0, ',', '.') . "\n"
                 . "Kepemilikan             : " . $ownership . " (" . $ownerDetail . ")\n"
                 . "Serial Number (SN)      : " . ($asset->serial_number ?: '-') . "\n"
                 . "MAC Address             : " . ($asset->mac_address ?: '-') . "\n"
                 . "Foto Aset               : " . $imageUrl . "\n"
                 . "Catatan                 : " . ($asset->notes ?: '-') . "\n"
                 . "-------------------------------------\n"
                 . "TOTAL NILAI ASET PT     : Rp " . number_format($totalAssetValue, 0, ',', '.') . "\n"
                 . "TOTAL UNIT ASET         : " . number_format($totalAssetCount, 0, ',', '.') . " Unit\n"
                 . "--------------------------------------";

        return $this->fonnteService->sendMessage(
            $target,
            $message,
            $imageUrl !== '-' ? $imageUrl : null,
            'aset_' . $asset->id . '.jpg',
            $localFilePath
        );
    }
}
