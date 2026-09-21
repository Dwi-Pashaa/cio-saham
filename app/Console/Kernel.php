<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // 1. Sinkronisasi data log mutasi finansial dari CIO Finance API setiap 10 menit
        $schedule->command('finance:sync-logs')
            ->everyTenMinutes()
            ->timezone('Asia/Jakarta')
            ->appendOutputTo(storage_path('logs/cron_finance_sync.log'))
            ->runInBackground();

        // 2. Pengiriman Rekapan Email Finansial Harian (Uang Masuk, Keluar, Laba) ke Pemegang Saham setiap jam 23:00 WIB
        $schedule->command('finance:send-daily-report')
            ->dailyAt('23:00')
            ->timezone('Asia/Jakarta')
            ->appendOutputTo(storage_path('logs/cron_daily_report.log'))
            ->runInBackground();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
