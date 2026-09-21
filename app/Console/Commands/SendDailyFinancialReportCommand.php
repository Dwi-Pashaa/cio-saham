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

        // 2. Ambil daftar investor penerima
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
                ->whereNotNull('email')
                ->where('email', '!=', '')
                ->get();
        }

        if ($shareholders->isEmpty()) {
            $this->warn("Tidak ada pemegang saham aktif yang memiliki alamat email.");
            return Command::SUCCESS;
        }

        $this->info("Ditemukan {$shareholders->count()} pemegang saham aktif penerima.");

        $sentCount = 0;
        $failedCount = 0;

        foreach ($shareholders as $sh) {
            $percentage = (float) ($sh->total_percentage ?? 0);
            $personalProfit = $netProfit * ($percentage / 100);

            $this->line("-> Investor: {$sh->name} ({$sh->email}) | Porsi: {$percentage}% | Estimasi Laba: Rp " . number_format($personalProfit, 0, ',', '.'));

            if ($isDryRun) {
                $this->comment("   [DRY-RUN] Email dilewati.");
                continue;
            }

            try {
                Mail::to($sh->email)->send(new DailyFinancialReportMail($sh, $dailyData, $personalProfit));
                $sentCount++;
                $this->info("   [OK] Email berhasil dikirim ke {$sh->email}");
            } catch (\Throwable $e) {
                $failedCount++;
                $this->error("   [GAGAL] Gagal mengirim ke {$sh->email}: " . $e->getMessage());
                Log::error("Cron Daily Report: Gagal kirim email", [
                    'shareholder_id' => $sh->id ?? null,
                    'email'          => $sh->email,
                    'error'          => $e->getMessage(),
                ]);
            }
        }

        $this->info("==========================================================");
        if ($isDryRun) {
            $this->info(" SIMULASI SELESAI: Laporan berhasil disimulasikan (--dry-run).");
            $this->comment(" (Catatan: Tidak ada email fisik yang dikirim keluar saat mode simulasi aktif)");
        } else {
            $this->info(" PENGIRIMAN SELESAI: {$sentCount} Berhasil | {$failedCount} Gagal");
            if ($failedCount > 0) {
                $this->warn(" Catatan: Email yang gagal disebabkan oleh alamat email tujuan tidak terdaftar/aktif di server mail.");
            }
        }
        $this->info("==========================================================");

        Log::info("Cron Daily Report Selesai", [
            'date'   => $targetDate,
            'sent'   => $sentCount,
            'failed' => $failedCount,
        ]);

        return ($sentCount > 0 || $isDryRun) ? Command::SUCCESS : Command::FAILURE;
    }
}
