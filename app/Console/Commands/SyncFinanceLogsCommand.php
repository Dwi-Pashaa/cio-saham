<?php

namespace App\Console\Commands;

use App\Services\FinanceAnalyticsService;
use Illuminate\Console\Command;

class SyncFinanceLogsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'finance:sync-logs {--fresh : Bersihkan cache dan tarik ulang seluruh log}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Tarik dan sinkronisasikan mutasi log terbaru dari CIO Finance API ke database lokal';

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
        $this->info("==========================================================");
        $this->info(" MEMULAI PENARIKAN LOG MUTASI DARI WEB CIO FINANCE API");
        $this->info("==========================================================");

        try {
            $this->line("Menghubungi endpoint CIO Finance API...");
            $result = $this->analyticsService->syncFinanceLogs();

            if (!empty($result['success'])) {
                $this->info("Sinkronisasi Selesai!");
                $this->line(" - Total Log Diproses : " . ($result['synced_count'] ?? 0));
                $this->line(" - Log Baru Ditambahkan: " . ($result['inserted_count'] ?? 0));
                $this->line(" - Log Diperbarui     : " . ($result['updated_count'] ?? 0));
                $this->line(" - Log Tanpa Perubahan: " . ($result['unchanged_count'] ?? 0) . " (Dilewati agar tidak double)");
                return Command::SUCCESS;
            } else {
                $this->error("Gagal melakukan sinkronisasi log.");
                return Command::FAILURE;
            }
        } catch (\Throwable $e) {
            $this->error("Terjadi kesalahan saat penarikan log: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
