<?php

namespace App\Console\Commands;

use App\Mail\DailyFinancialReportMail;
use App\Models\Shareholder;
use App\Services\FinanceAnalyticsService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendDailyFinancialReportCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'finance:send-daily-report 
                            {--date= : Tanggal laporan finansial dalam format YYYY-MM-DD (Default: Hari ini)}
                            {--test-email= : Kirim uji coba hanya ke alamat email ini}
                            {--dry-run : Tampilkan ringkasan di konsol tanpa mengirim email}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Kirim email laporan finansial harian (Uang Masuk, Uang Keluar, dan Keuntungan) ke seluruh pemegang saham aktif';

    protected FinanceAnalyticsService $analyticsService;

    public function __construct(FinanceAnalyticsService $analyticsService)
    {
        parent::__construct();
        $this->analyticsService = $analyticsService;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $targetDate = $this->option('date') ?: Carbon::now()->format('Y-m-d');
        $testEmail  = $this->option('test-email');
        $isDryRun   = $this->option('dry-run');

        $this->info("==========================================================");
        $this->info(" MEMULAI PENGIRIMAN LAPORAN FINANSIAL HARIAN ({$targetDate})");
        $this->info("==========================================================");

        // 1. Ambil data analitik finansial harian konsolidasi
        $this->line("Mengambil data analitik keuangan tanggal {$targetDate}...");
        try {
            $dailyData = $this->analyticsService->getDailyDetailedReportData($targetDate);
            $totalInflow   = (float) ($dailyData['total_inflow'] ?? 0);
            $totalOutflow  = (float) ($dailyData['total_outflow'] ?? 0);
            $netProfit     = (float) ($dailyData['net_profit'] ?? ($totalInflow - $totalOutflow));
            $marginPct     = (float) ($dailyData['profit_margin_pct'] ?? ($totalInflow > 0 ? ($netProfit / $totalInflow) * 100 : 0));
        } catch (\Throwable $e) {
            $this->error("Gagal mengambil data analitik keuangan: " . $e->getMessage());
            Log::error("Cron Daily Report: Gagal mengambil data analitik", ['error' => $e->getMessage()]);
            return Command::FAILURE;
        }

        $this->table(
            ['Metrik', 'Nominal / Nilai'],
            [
                ['Tanggal Rekapan', Carbon::parse($targetDate)->translatedFormat('d F Y')],
                ['Uang Masuk (Income)', '+Rp ' . number_format($totalInflow, 0, ',', '.')],
                ['Uang Keluar (Outcome)', '-Rp ' . number_format($totalOutflow, 0, ',', '.')],
                ['Keuntungan Bersih (Net Profit)', 'Rp ' . number_format($netProfit, 0, ',', '.')],
                ['Profit Margin', number_format($marginPct, 1) . '%'],
            ]
        );

        // 2. Cek Pengaturan Saluran Notifikasi Sistem
        $setting = \App\Models\Setting::first();
        $channel = strtolower($setting?->notification_channel ?? 'whatsapp');

        $this->info("Saluran Notifikasi Terkonfigurasi: " . strtoupper($channel));

        if ($channel === 'none') {
            $this->warn("Pengiriman dibatalkan: Pengaturan saluran notifikasi disetel ke 'none' (Nonaktif).");
            return Command::SUCCESS;
        }

        $shouldSendEmail = in_array($channel, ['email', 'both']);
        $shouldSendWa    = in_array($channel, ['whatsapp', 'both']);

        // 3. Ambil daftar investor penerima
        if ($testEmail) {
            $this->warn("Mode Uji Coba: Hanya mengirim ke {$testEmail}");
            $sampleShareholder = Shareholder::where('email', $testEmail)->first();
            if (!$sampleShareholder) {
                $sampleShareholder = new Shareholder([
                    'name'             => 'Test Investor',
                    'email'            => $testEmail,
                    'total_shares'     => 1000,
                    'total_percentage' => 10.0,
                    'status'           => 'active',
                ]);
            }
            $shareholders = collect([$sampleShareholder]);
        } else {
            $shareholders = Shareholder::with('holdings')
                ->where('status', 'active')
                ->where(function ($query) use ($shouldSendEmail, $shouldSendWa) {
                    if ($shouldSendEmail && !$shouldSendWa) {
                        $query->whereNotNull('email')->where('email', '!=', '');
                    } elseif ($shouldSendWa && !$shouldSendEmail) {
                        $query->whereNotNull('phone')->where('phone', '!=', '');
                    } else {
                        $query->where(function ($q) {
                            $q->whereNotNull('email')->where('email', '!=', '');
                        })->orWhere(function ($q) {
                            $q->whereNotNull('phone')->where('phone', '!=', '');
                        });
                    }
                })
                ->get();
        }

        if ($shareholders->isEmpty()) {
            $this->warn("Tidak ada pemegang saham aktif yang memenuhi kriteria penerima notifikasi.");
            return Command::SUCCESS;
        }

        $this->info("Ditemukan {$shareholders->count()} pemegang saham aktif penerima.");

        $sentEmailCount = 0;
        $failedEmailCount = 0;
        $sentWaCount = 0;
        $failedWaCount = 0;

        $fonnteService = $shouldSendWa ? app(\App\Services\FonnteService::class) : null;
        $appName = config('app.name', 'CIO Investor');
        $formattedDate = Carbon::parse($targetDate)->translatedFormat('d F Y');

        foreach ($shareholders as $sh) {
            $percentage = (float) ($sh->total_percentage ?? 0);
            $personalProfit = $netProfit * ($percentage / 100);

            $this->line("-> Investor: {$sh->name} | Email: " . ($sh->email ?: '-') . " | WA: " . ($sh->phone ?: '-') . " | Porsi: {$percentage}% | Estimasi Laba: Rp " . number_format($personalProfit, 0, ',', '.'));

            if ($isDryRun) {
                $this->comment("   [DRY-RUN] Notifikasi dilewati.");
                continue;
            }

            // A. Kirim Email jika saluran mengizinkan
            if ($shouldSendEmail && !empty($sh->email)) {
                try {
                    Mail::to($sh->email)->send(new DailyFinancialReportMail($sh, $dailyData, $personalProfit));
                    $sentEmailCount++;
                    $this->info("   [OK-EMAIL] Email berhasil dikirim ke {$sh->email}");
                } catch (\Throwable $e) {
                    $failedEmailCount++;
                    $this->error("   [GAGAL-EMAIL] Gagal mengirim ke {$sh->email}: " . $e->getMessage());
                    Log::error("Cron Daily Report: Gagal kirim email", [
                        'shareholder_id' => $sh->id ?? null,
                        'email'          => $sh->email,
                        'error'          => $e->getMessage(),
                    ]);
                }
            }

            // B. Kirim WhatsApp jika saluran mengizinkan
            if ($shouldSendWa && !empty($sh->phone) && $fonnteService) {
                try {
                    $waMessage = "*📊 LAPORAN KEUANGAN HARIAN - {$appName}*\n"
                        . "📅 *Tanggal:* {$formattedDate}\n\n"
                        . "Halo *{$sh->name}*,\n"
                        . "Berikut ringkasan kinerja keuangan hari ini:\n\n"
                        . "💰 *Uang Masuk:* Rp " . number_format($totalInflow, 0, ',', '.') . "\n"
                        . "💸 *Uang Keluar:* Rp " . number_format($totalOutflow, 0, ',', '.') . "\n"
                        . "📈 *Laba Bersih:* Rp " . number_format($netProfit, 0, ',', '.') . "\n"
                        . "📊 *Margin:* " . number_format($marginPct, 1) . "%\n"
                        . "------------------------------------\n"
                        . "🏷️ *Porsi Saham Anda:* {$percentage}%\n"
                        . "💵 *Estimasi Porsi Laba:* Rp " . number_format($personalProfit, 0, ',', '.') . "\n\n"
                        . "Detail lengkap dapat diakses melalui portal investor.\nTerima kasih!";

                    $res = $fonnteService->sendMessage($sh->phone, $waMessage);
                    if ($res['success'] ?? false) {
                        $sentWaCount++;
                        $this->info("   [OK-WA] WhatsApp berhasil dikirim ke {$sh->phone}");
                    } else {
                        $failedWaCount++;
                        $this->warn("   [GAGAL-WA] Gagal kirim WA ke {$sh->phone}: " . ($res['message'] ?? 'Unknown error'));
                    }
                } catch (\Throwable $e) {
                    $failedWaCount++;
                    $this->error("   [GAGAL-WA] Gagal kirim WA ke {$sh->phone}: " . $e->getMessage());
                    Log::error("Cron Daily Report: Gagal kirim WA", [
                        'shareholder_id' => $sh->id ?? null,
                        'phone'          => $sh->phone,
                        'error'          => $e->getMessage(),
                    ]);
                }
            }
        }

        $this->info("==========================================================");
        if ($isDryRun) {
            $this->info(" SIMULASI SELESAI: Laporan berhasil disimulasikan (--dry-run).");
            $this->comment(" (Catatan: Tidak ada notifikasi fisik yang dikirim keluar saat mode simulasi aktif)");
        } else {
            $this->info(" PENGIRIMAN SELESAI:");
            if ($shouldSendEmail) {
                $this->info(" -> Email:    {$sentEmailCount} Berhasil | {$failedEmailCount} Gagal");
            }
            if ($shouldSendWa) {
                $this->info(" -> WhatsApp: {$sentWaCount} Berhasil | {$failedWaCount} Gagal");
            }
        }
        $this->info("==========================================================");

        Log::info("Cron Daily Report Selesai", [
            'date'       => $targetDate,
            'channel'    => $channel,
            'sent_email' => $sentEmailCount,
            'sent_wa'    => $sentWaCount,
        ]);

        return Command::SUCCESS;
    }
}
