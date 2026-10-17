<?php

namespace App\Services;

use App\Mail\CashNotificationMail;
use App\Models\Asset;
use App\Models\CashIncome;
use App\Models\CashOutcome;
use App\Models\CashSaving;
use App\Models\Setting;
use App\Models\Shareholder;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class CashNotificationService
{
    protected FonnteService $fonnteService;

    public function __construct(FonnteService $fonnteService)
    {
        $this->fonnteService = $fonnteService;
    }

    /**
     * Dapatkan saluran notifikasi aktif dari Pengaturan ('whatsapp', 'email', 'both', 'none').
     */
    public function getNotificationChannel(): string
    {
        $setting = Setting::first();
        return strtolower($setting?->notification_channel ?? 'whatsapp');
    }

    /**
     * Cek apakah pengiriman via WhatsApp diaktifkan ('whatsapp' atau 'both').
     */
    public function shouldSendWhatsApp(): bool
    {
        return in_array($this->getNotificationChannel(), ['whatsapp', 'both']);
    }

    /**
     * Cek apakah pengiriman via Email diaktifkan ('email' atau 'both').
     */
    public function shouldSendEmail(): bool
    {
        return in_array($this->getNotificationChannel(), ['email', 'both']);
    }

    /**
     * Cek apakah notifikasi aktif di Pengaturan (bukan 'none').
     */
    public function isNotificationEnabled(): bool
    {
        return $this->getNotificationChannel() !== 'none';
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
     * Dapatkan daftar alamat email penerima notifikasi kas & aset:
     * 1. Seluruh email Pemegang Saham / Investor Aktif (Shareholders)
     * 2. Email Admin / Manajemen
     *
     * @return array<string>
     */
    public function getRecipientEmails(): array
    {
        $emails = [];

        // 1. Email Seluruh Pemegang Saham Aktif
        $shareholderEmails = Shareholder::where('status', 'active')
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->pluck('email')
            ->toArray();

        foreach ($shareholderEmails as $email) {
            $cleaned = trim((string)$email);
            if (filter_var($cleaned, FILTER_VALIDATE_EMAIL) && !in_array($cleaned, $emails)) {
                $emails[] = $cleaned;
            }
        }

        // 2. Email Administrator & Manajemen Sistem
        $adminEmails = User::whereNotNull('email')
            ->where('email', '!=', '')
            ->where(function ($q) {
                $q->whereHas('roles', function ($rq) {
                    $rq->whereIn('name', ['admin', 'Administrator']);
                })->orWhere('id', 1);
            })
            ->pluck('email')
            ->toArray();

        foreach ($adminEmails as $email) {
            $cleaned = trim((string)$email);
            if (filter_var($cleaned, FILTER_VALIDATE_EMAIL) && !in_array($cleaned, $emails)) {
                $emails[] = $cleaned;
            }
        }

        return $emails;
    }

    /**
     * Dapatkan nomor WhatsApp target tunggal/fallback untuk notifikasi.
     */
    public function getTargetPhoneNumber(): ?string
    {
        return $this->getRecipientPhoneNumbers();
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
     * Kirim notifikasi otomatis (WhatsApp, Email, atau keduanya) saat mutasi Saldo Masuk (Pemasukan) dicatat.
     *
     * @param CashIncome $income Record pemasukan yang baru disimpan
     * @return array{status: bool, message: string, results?: array}
     */
    public function notifyIncomeCreated(CashIncome $income): array
    {
        if (!$this->isNotificationEnabled()) {
            return [
                'status'   => false,
                'success'  => false,
                'message'  => 'Notifikasi dinonaktifkan di pengaturan.',
                'results'  => [],
                'whatsapp' => null,
                'email'    => null,
            ];
        }

        // Hitung Saldo Terbaru dan Saldo Sebelumnya
        $currentBalance  = $this->getCurrentCashBalance();
        $netIncome       = (float) $income->net_amount;
        $previousBalance = $currentBalance - $netIncome;

        // URL File Bukti Transaksi
        $proofUrl      = $income->proof_url ?: ($income->proof_file ? asset('storage/' . $income->proof_file) : null);
        $localFilePath = $income->proof_file ? storage_path('app/public/' . $income->proof_file) : null;

        $results = [];

        // 1. Kirim via WhatsApp jika diaktifkan (whatsapp / both)
        if ($this->shouldSendWhatsApp()) {
            $target = $this->getRecipientPhoneNumbers();
            if (!empty($target)) {
                $message = "NOTIFIKASI saldo masuk ke rekening PT CIO NETWORK\n"
                         . "Dari rekening           : " . ($income->sender_name ?: '-') . "\n"
                         . "rekening                : " . ($income->bank_name ?: '-') . "\n"
                         . "no Rekening             : " . ($income->account_number ?: '-') . "\n"
                         . "Nominal                 : Rp " . number_format($income->amount, 0, ',', '.') . "\n"
                         . "poto bukti transaksi    : " . ($proofUrl ?: '-') . "\n"
                         . "catatan                 : " . ($income->notes ?: '-') . "\n"
                         . "-------------------------------------\n"
                         . "SALDO SEBELUMNYA: Rp " . number_format($previousBalance, 0, ',', '.') . "\n"
                         . "SALDO TERBARU   : Rp " . number_format($currentBalance, 0, ',', '.') . "\n"
                         . "--------------------------------------";

                $results['wa'] = $this->fonnteService->sendMessage(
                    $target,
                    $message,
                    $proofUrl,
                    'bukti_masuk_' . $income->transaction_number . '.jpg',
                    $localFilePath
                );
            } else {
                Log::warning('[CashNotificationService] Nomor WhatsApp target / investor belum tersedia.');
                $results['wa'] = ['status' => false, 'message' => 'Nomor WhatsApp target / investor belum tersedia.'];
            }
        }

        // 2. Kirim via Email jika diaktifkan (email / both)
        if ($this->shouldSendEmail()) {
            $emails = $this->getRecipientEmails();
            if (!empty($emails)) {
                $details = [
                    'No. Transaksi'    => $income->transaction_number,
                    'Tanggal Transaksi'=> $income->transaction_date ? $income->transaction_date->format('d/m/Y') : now()->format('d/m/Y'),
                    'Dari Rekening'    => $income->sender_name ?: '-',
                    'Bank Rekening'    => $income->bank_name ?: '-',
                    'Nomor Rekening'   => $income->account_number ?: '-',
                    'Nominal Masuk'    => 'Rp ' . number_format($income->amount, 0, ',', '.'),
                    'Biaya Admin'      => ($income->has_admin_fee && $income->admin_fee > 0) ? 'Rp ' . number_format($income->admin_fee, 0, ',', '.') : 'Tidak ada (Rp 0)',
                    'Pemasukan Bersih' => 'Rp ' . number_format($income->net_amount, 0, ',', '.'),
                    'Catatan'          => $income->notes ?: '-',
                ];

                try {
                    Mail::to($emails)->send(new CashNotificationMail(
                        type: 'income',
                        mailSubject: "💰 Notifikasi Saldo Masuk Kas PT CIO [Rp " . number_format($income->net_amount, 0, ',', '.') . "]",
                        title: "Pemasukan Kas PT CIO Network Solution",
                        details: $details,
                        previousBalance: $previousBalance,
                        currentBalance: $currentBalance,
                        proofUrl: $proofUrl,
                        notes: $income->notes,
                        localFilePath: $localFilePath
                    ));
                    $results['email'] = ['status' => true, 'message' => 'Email notifikasi berhasil dikirim ke ' . count($emails) . ' penerima.'];
                } catch (\Throwable $e) {
                    Log::error('[CashNotificationService] Gagal mengirim email pemasukan: ' . $e->getMessage());
                    $results['email'] = ['status' => false, 'message' => 'Gagal mengirim email: ' . $e->getMessage()];
                }
            } else {
                Log::warning('[CashNotificationService] Alamat email target / investor belum tersedia.');
                $results['email'] = ['status' => false, 'message' => 'Alamat email target belum tersedia.'];
            }
        }

        return [
            'status'   => true,
            'success'  => true,
            'message'  => 'Notifikasi saldo masuk berhasil diproses.',
            'results'  => $results,
            'whatsapp' => $results['wa'] ?? null,
            'email'    => $results['email'] ?? null,
        ];
    }

    /**
     * Kirim notifikasi otomatis (WhatsApp, Email, atau keduanya) saat mutasi Saldo Keluar (Pengeluaran) dicatat.
     *
     * @param CashOutcome $outcome Record pengeluaran yang baru disimpan
     * @return array{status: bool, message: string, results?: array}
     */
    public function notifyOutcomeCreated(CashOutcome $outcome): array
    {
        if (!$this->isNotificationEnabled()) {
            return [
                'status'   => false,
                'success'  => false,
                'message'  => 'Notifikasi dinonaktifkan di pengaturan.',
                'results'  => [],
                'whatsapp' => null,
                'email'    => null,
            ];
        }

        // Hitung Saldo Terbaru dan Saldo Sebelumnya
        $currentBalance  = $this->getCurrentCashBalance();
        $totalOutcome    = (float) $outcome->total_amount;
        $previousBalance = $currentBalance + $totalOutcome;

        // Format Tanggal Transaksi: Tanggal/Bulan/Tahun (e.g. 05/10/2026)
        $trxDateFormatted = $outcome->transaction_date
            ? $outcome->transaction_date->format('d/m/Y')
            : now()->format('d/m/Y');

        // Status Biaya Admin
        $adminFeeStr = ($outcome->has_admin_fee && $outcome->admin_fee > 0)
            ? 'Ya, Rp ' . number_format($outcome->admin_fee, 0, ',', '.')
            : 'Tidak (Rp 0)';

        // URL File Bukti & Nota
        $proofUrl      = $outcome->proof_url ?: ($outcome->proof_file ? asset('storage/' . $outcome->proof_file) : null);
        $receiptUrl    = $outcome->receipt_file ? ($outcome->receipt_url ?: asset('storage/' . $outcome->receipt_file)) : '-';
        $localFilePath = $outcome->proof_file ? storage_path('app/public/' . $outcome->proof_file) : null;

        $results = [];

        // 1. Kirim via WhatsApp jika diaktifkan (whatsapp / both)
        if ($this->shouldSendWhatsApp()) {
            $target = $this->getRecipientPhoneNumbers();
            if (!empty($target)) {
                $message = "NOTIFIKASI saldo Keluar " . $trxDateFormatted . "\n"
                         . "rekening tujuan         : " . ($outcome->recipient_name ?: '-') . "\n"
                         . "rekening                : " . ($outcome->bank_name ?: '-') . "\n"
                         . "no Rekening             : " . ($outcome->account_number ?: '-') . "\n"
                         . "Nominal                 : Rp " . number_format($outcome->amount, 0, ',', '.') . "\n"
                         . "pake admin ya/ tidak    : " . $adminFeeStr . "\n"
                         . "poto bukti transaksi    : " . ($proofUrl ?: '-') . "\n"
                         . "poto nota pembelian     : " . $receiptUrl . "\n"
                         . "catatan                 : " . ($outcome->notes ?: '-') . "\n"
                         . "-------------------------------------\n"
                         . "SALDO SEBELUMNYA: Rp " . number_format($previousBalance, 0, ',', '.') . "\n"
                         . "SALDO TERBARU   : Rp " . number_format($currentBalance, 0, ',', '.') . "\n"
                         . "--------------------------------------";

                $results['wa'] = $this->fonnteService->sendMessage(
                    $target,
                    $message,
                    $proofUrl,
                    'bukti_keluar_' . $outcome->transaction_number . '.jpg',
                    $localFilePath
                );
            } else {
                Log::warning('[CashNotificationService] Nomor WhatsApp target / investor belum tersedia.');
                $results['wa'] = ['status' => false, 'message' => 'Nomor WhatsApp target / investor belum tersedia.'];
            }
        }

        // 2. Kirim via Email jika diaktifkan (email / both)
        if ($this->shouldSendEmail()) {
            $emails = $this->getRecipientEmails();
            if (!empty($emails)) {
                $details = [
                    'No. Transaksi'    => $outcome->transaction_number,
                    'Tanggal Transaksi'=> $trxDateFormatted,
                    'Rekening Tujuan'  => $outcome->recipient_name ?: '-',
                    'Bank Tujuan'      => $outcome->bank_name ?: '-',
                    'Nomor Rekening'   => $outcome->account_number ?: '-',
                    'Nominal Keluar'   => 'Rp ' . number_format($outcome->amount, 0, ',', '.'),
                    'Biaya Admin'      => $adminFeeStr,
                    'Total Pengeluaran'=> 'Rp ' . number_format($outcome->total_amount, 0, ',', '.'),
                    'Catatan'          => $outcome->notes ?: '-',
                ];

                try {
                    Mail::to($emails)->send(new CashNotificationMail(
                        type: 'outcome',
                        mailSubject: "💸 Notifikasi Saldo Keluar Kas PT CIO [Rp " . number_format($outcome->total_amount, 0, ',', '.') . "]",
                        title: "Pengeluaran Kas PT CIO Network Solution",
                        details: $details,
                        previousBalance: $previousBalance,
                        currentBalance: $currentBalance,
                        proofUrl: $proofUrl,
                        notes: $outcome->notes,
                        localFilePath: $localFilePath
                    ));
                    $results['email'] = ['status' => true, 'message' => 'Email notifikasi pengeluaran berhasil dikirim.'];
                } catch (\Throwable $e) {
                    Log::error('[CashNotificationService] Gagal mengirim email pengeluaran: ' . $e->getMessage());
                    $results['email'] = ['status' => false, 'message' => 'Gagal mengirim email: ' . $e->getMessage()];
                }
            } else {
                Log::warning('[CashNotificationService] Alamat email target / investor belum tersedia.');
                $results['email'] = ['status' => false, 'message' => 'Alamat email target belum tersedia.'];
            }
        }

        return [
            'status'   => true,
            'success'  => true,
            'message'  => 'Notifikasi saldo keluar berhasil diproses.',
            'results'  => $results,
            'whatsapp' => $results['wa'] ?? null,
            'email'    => $results['email'] ?? null,
        ];
    }

    /**
     * Kirim notifikasi otomatis saat Saldo dialokasikan/dibagikan ke Tabungan.
     */
    public function notifySavingCreated(CashSaving $saving): array
    {
        if (!$this->isNotificationEnabled()) {
            return [
                'status'   => false,
                'success'  => false,
                'message'  => 'Notifikasi dinonaktifkan di pengaturan.',
                'results'  => [],
                'whatsapp' => null,
                'email'    => null,
            ];
        }

        // Saldo Terkini dan Sebelumnya
        $currentBalance  = $this->getCurrentCashBalance();
        $savingAmount    = (float) $saving->amount;
        $previousBalance = $currentBalance + $savingAmount;
        $totalSavings    = (float) CashSaving::sum('amount');

        $trxDateFormatted = $saving->transaction_date
            ? $saving->transaction_date->format('d/m/Y')
            : now()->format('d/m/Y');

        $proofUrl      = $saving->proof_url ?: ($saving->proof_file ? asset('storage/' . $saving->proof_file) : null);
        $localFilePath = $saving->proof_file ? storage_path('app/public/' . $saving->proof_file) : null;

        $results = [];

        // 1. Kirim via WhatsApp jika diaktifkan (whatsapp / both)
        if ($this->shouldSendWhatsApp()) {
            $target = $this->getRecipientPhoneNumbers();
            if (!empty($target)) {
                $message = "NOTIFIKASI ALOKASI KE TABUNGAN " . $trxDateFormatted . "\n"
                         . "No Transaksi            : " . $saving->transaction_number . "\n"
                         . "Penerima / Rekening     : " . ($saving->recipient_name ?: '-') . "\n"
                         . "Bank Tujuan             : " . ($saving->bank_name ?: '-') . "\n"
                         . "No Rekening             : " . ($saving->account_number ?: '-') . "\n"
                         . "Nominal Tabungan        : Rp " . number_format($savingAmount, 0, ',', '.') . "\n"
                         . "Bukti Transfer          : " . ($proofUrl ?: '-') . "\n"
                         . "Catatan                 : " . ($saving->notes ?: '-') . "\n"
                         . "-------------------------------------\n"
                         . "SISA KAS OPERASIONAL    : Rp " . number_format($currentBalance, 0, ',', '.') . "\n"
                         . "TOTAL SALDO TABUNGAN    : Rp " . number_format($totalSavings, 0, ',', '.') . "\n"
                         . "--------------------------------------";

                $results['wa'] = $this->fonnteService->sendMessage(
                    $target,
                    $message,
                    $proofUrl,
                    'bukti_tabungan_' . $saving->transaction_number . '.jpg',
                    $localFilePath
                );
            } else {
                Log::warning('[CashNotificationService] Nomor WhatsApp target / investor belum tersedia.');
                $results['wa'] = ['status' => false, 'message' => 'Nomor WhatsApp target belum tersedia.'];
            }
        }

        // 2. Kirim via Email jika diaktifkan (email / both)
        if ($this->shouldSendEmail()) {
            $emails = $this->getRecipientEmails();
            if (!empty($emails)) {
                $details = [
                    'No. Transaksi'        => $saving->transaction_number,
                    'Tanggal Alokasi'      => $trxDateFormatted,
                    'Penerima / Rekening'  => $saving->recipient_name ?: '-',
                    'Bank Tujuan'          => $saving->bank_name ?: '-',
                    'Nomor Rekening'       => $saving->account_number ?: '-',
                    'Nominal Tabungan'     => 'Rp ' . number_format($savingAmount, 0, ',', '.'),
                    'Total Saldo Tabungan' => 'Rp ' . number_format($totalSavings, 0, ',', '.'),
                    'Catatan'              => $saving->notes ?: '-',
                ];

                try {
                    Mail::to($emails)->send(new CashNotificationMail(
                        type: 'saving',
                        mailSubject: "🏦 Notifikasi Alokasi Tabungan Kas PT CIO [Rp " . number_format($savingAmount, 0, ',', '.') . "]",
                        title: "Alokasi Kas ke Rekening Tabungan",
                        details: $details,
                        previousBalance: $previousBalance,
                        currentBalance: $currentBalance,
                        proofUrl: $proofUrl,
                        notes: $saving->notes,
                        localFilePath: $localFilePath
                    ));
                    $results['email'] = ['status' => true, 'message' => 'Email notifikasi tabungan berhasil dikirim.'];
                } catch (\Throwable $e) {
                    Log::error('[CashNotificationService] Gagal mengirim email alokasi tabungan: ' . $e->getMessage());
                    $results['email'] = ['status' => false, 'message' => 'Gagal mengirim email: ' . $e->getMessage()];
                }
            }
        }

        return [
            'status'   => true,
            'success'  => true,
            'message'  => 'Notifikasi tabungan berhasil diproses.',
            'results'  => $results,
            'whatsapp' => $results['wa'] ?? null,
            'email'    => $results['email'] ?? null,
        ];
    }

    /**
     * Kirim notifikasi otomatis saat Aset Perusahaan / Investor baru ditambahkan.
     */
    public function notifyAssetCreated(Asset $asset): array
    {
        if (!$this->isNotificationEnabled()) {
            return [
                'status'   => false,
                'success'  => false,
                'message'  => 'Notifikasi dinonaktifkan di pengaturan.',
                'results'  => [],
                'whatsapp' => null,
                'email'    => null,
            ];
        }

        $trxDateFormatted = $asset->purchase_date
            ? $asset->purchase_date->format('d/m/Y')
            : now()->format('d/m/Y');

        $primaryImage = $asset->images()->where('is_primary', true)->first() ?: $asset->images()->first();
        $imageUrl = $primaryImage ? asset('storage/' . $primaryImage->image_path) : null;
        $localFilePath = $primaryImage ? storage_path('app/public/' . $primaryImage->image_path) : null;

        $totalAssetValue = (float) Asset::sum('price');
        $totalAssetCount = (int) Asset::count();

        $ownership = $asset->owner_type === 'pt' ? 'PT CIO NETWORK SOLUTION' : 'Pemegang Saham / Investor';
        $ownerDetail = $asset->owner_type === 'shareholder'
            ? ($asset->shareholder?->name ?: ($asset->owner_name ?: 'Investor'))
            : 'PT CIO NETWORK SOLUTION';

        $results = [];

        // 1. Kirim via WhatsApp jika diaktifkan (whatsapp / both)
        if ($this->shouldSendWhatsApp()) {
            $target = $this->getRecipientPhoneNumbers();
            if (!empty($target)) {
                $message = "NOTIFIKASI PENAMBAHAN ASET PERUSAHAAN\n"
                         . "Tanggal Perolehan       : " . $trxDateFormatted . "\n"
                         . "Nama Aset               : " . $asset->name . "\n"
                         . "Kategori                : " . $asset->type . "\n"
                         . "Nilai / Harga Aset      : Rp " . number_format((float) $asset->price, 0, ',', '.') . "\n"
                         . "Kepemilikan             : " . $ownership . " (" . $ownerDetail . ")\n"
                         . "Serial Number (SN)      : " . ($asset->serial_number ?: '-') . "\n"
                         . "MAC Address             : " . ($asset->mac_address ?: '-') . "\n"
                         . "Foto Aset               : " . ($imageUrl ?: '-') . "\n"
                         . "Catatan                 : " . ($asset->notes ?: '-') . "\n"
                         . "-------------------------------------\n"
                         . "TOTAL NILAI ASET PT     : Rp " . number_format($totalAssetValue, 0, ',', '.') . "\n"
                         . "TOTAL UNIT ASET         : " . number_format($totalAssetCount, 0, ',', '.') . " Unit\n"
                         . "--------------------------------------";

                $results['wa'] = $this->fonnteService->sendMessage(
                    $target,
                    $message,
                    $imageUrl,
                    'aset_' . $asset->id . '.jpg',
                    $localFilePath
                );
            } else {
                Log::warning('[CashNotificationService] Nomor WhatsApp target / investor belum tersedia.');
                $results['wa'] = ['status' => false, 'message' => 'Nomor WhatsApp belum tersedia.'];
            }
        }

        // 2. Kirim via Email jika diaktifkan (email / both)
        if ($this->shouldSendEmail()) {
            $emails = $this->getRecipientEmails();
            if (!empty($emails)) {
                $details = [
                    'Tanggal Perolehan'     => $trxDateFormatted,
                    'Nama Perangkat / Aset' => $asset->name,
                    'Kategori / Tipe'       => $asset->type,
                    'Nilai / Harga Aset'    => 'Rp ' . number_format((float) $asset->price, 0, ',', '.'),
                    'Status Kepemilikan'    => "{$ownership} ({$ownerDetail})",
                    'Serial Number (SN)'    => $asset->serial_number ?: '-',
                    'MAC Address'           => $asset->mac_address ?: '-',
                    'Total Nilai Aset PT'   => 'Rp ' . number_format($totalAssetValue, 0, ',', '.'),
                    'Total Unit Aset'       => number_format($totalAssetCount, 0, ',', '.') . ' Unit',
                    'Catatan / Lokasi'      => $asset->notes ?: '-',
                ];

                try {
                    Mail::to($emails)->send(new CashNotificationMail(
                        type: 'asset',
                        mailSubject: "📦 Notifikasi Penambahan Inventaris Aset PT CIO [{$asset->name}]",
                        title: "Inventaris / Aset Baru Tercatat",
                        details: $details,
                        previousBalance: null,
                        currentBalance: null,
                        proofUrl: $imageUrl,
                        notes: $asset->notes,
                        localFilePath: $localFilePath
                    ));
                    $results['email'] = ['status' => true, 'message' => 'Email notifikasi penambahan aset berhasil dikirim.'];
                } catch (\Throwable $e) {
                    Log::error('[CashNotificationService] Gagal mengirim email penambahan aset: ' . $e->getMessage());
                    $results['email'] = ['status' => false, 'message' => 'Gagal mengirim email: ' . $e->getMessage()];
                }
            }
        }

        return [
            'status'   => true,
            'success'  => true,
            'message'  => 'Notifikasi penambahan aset berhasil diproses.',
            'results'  => $results,
            'whatsapp' => $results['wa'] ?? null,
            'email'    => $results['email'] ?? null,
        ];
    }
}
