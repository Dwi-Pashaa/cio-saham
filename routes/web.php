<?php

use App\Http\Controllers\Api\FinanceLogApiController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Pages\DashboardController;
use App\Http\Controllers\Pages\InvestmentReportController;
use App\Http\Controllers\Pages\InvestorDirectoryController;
use App\Http\Controllers\Pages\ProfileController;
use App\Http\Controllers\Pages\RoleController;
use App\Http\Controllers\Pages\SettingController;
use App\Http\Controllers\Pages\ShareholderController;
use App\Http\Controllers\Pages\ShareHoldingController;
use App\Http\Controllers\Pages\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', [LoginController::class, 'index'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('post.login');

// Reset Password Multi-Channel (Email & WhatsApp OTP)
Route::get('/forgot-password', [ForgotPasswordController::class, 'showForgotForm'])->name('password.request');
Route::post('/forgot-password', [ForgotPasswordController::class, 'sendOtp'])->name('password.email');
Route::get('/verify-otp', [ForgotPasswordController::class, 'showVerifyOtpForm'])->name('password.verify.form');
Route::post('/verify-otp', [ForgotPasswordController::class, 'verifyOtp'])->name('password.verify');
Route::post('/verify-otp/channel', [ForgotPasswordController::class, 'sendChannel'])->name('password.send.channel');
Route::post('/resend-otp', [ForgotPasswordController::class, 'resendOtp'])->name('password.resend');
Route::get('/reset-password', [ForgotPasswordController::class, 'showResetPasswordForm'])->name('password.reset');
Route::post('/reset-password', [ForgotPasswordController::class, 'resetPassword'])->name('password.update');

Route::middleware(['auth'])->group(function () {
    // logout
    Route::get('/logout', [LoginController::class, 'logout'])->name('logout');

    // dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profil Akun (Menu Default untuk SEMUA level akses tanpa batas permission)
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

    // Manajemen Pemilik Saham (Shareholders)
    Route::prefix('shareholders')->name('shareholders.')->group(function () {
        Route::get('/', [ShareholderController::class, 'index'])->name('index')->can('view-shareholders');
        Route::get('/create', [ShareholderController::class, 'create'])->name('create')->can('create-shareholder');
        Route::post('/store', [ShareholderController::class, 'store'])->name('store')->can('create-shareholder');
        Route::get('/{id}', [ShareholderController::class, 'show'])->name('show')->can('view-shareholders');
        Route::get('/{id}/edit', [ShareholderController::class, 'edit'])->name('edit')->can('edit-shareholder');
        Route::put('/{id}', [ShareholderController::class, 'update'])->name('update')->can('edit-shareholder');
        Route::delete('/{id}', [ShareholderController::class, 'destroy'])->name('destroy')->can('delete-shareholder');
    });

    // Manajemen Alokasi Saham Per Pemilik (1 to Many)
    Route::prefix('share-holdings')->name('share-holdings.')->group(function () {
        Route::post('/store', [ShareHoldingController::class, 'store'])->name('store')->can('create-share-holding');
        Route::put('/{id}', [ShareHoldingController::class, 'update'])->name('update')->can('edit-share-holding');
        Route::delete('/{id}', [ShareHoldingController::class, 'destroy'])->name('destroy')->can('delete-share-holding');
    });

    // Direktori Portofolio Pemegang Saham (Read-Only & Search untuk Level Pemegang Saham & Admin)
    Route::prefix('investor-directory')->name('investor-directory.')->group(function () {
        Route::get('/', [InvestorDirectoryController::class, 'index'])->name('index')->can('view-shareholder-directory');
        Route::get('/{id}', [InvestorDirectoryController::class, 'show'])->name('show')->can('view-shareholder-directory');
    });

    // Laporan Keuntungan Saham / Imbal Hasil Tahunan (CRUD Admin, Read-Only Pemegang Saham)
    Route::prefix('investment-reports')->name('investment-reports.')->group(function () {
        Route::get('/', [InvestmentReportController::class, 'index'])->name('index')->can('view-investment-reports');
        Route::post('/store', [InvestmentReportController::class, 'store'])->name('store')->can('create-investment-report');
        Route::put('/{id}', [InvestmentReportController::class, 'update'])->name('update')->can('edit-investment-report');
        Route::delete('/{id}', [InvestmentReportController::class, 'destroy'])->name('destroy')->can('delete-investment-report');
    });

    // Internal API Routes untuk Finance Dashboard AJAX
    Route::prefix('api/finance')->name('web.api.finance.')->group(function () {
        Route::get('/overview', [FinanceLogApiController::class, 'getOverview'])->name('overview');
        Route::get('/chart', [FinanceLogApiController::class, 'getChartData'])->name('chart');
        Route::get('/growth', [FinanceLogApiController::class, 'getGrowthData'])->name('growth');
        Route::get('/history', [FinanceLogApiController::class, 'getHistoryData'])->name('history');
        Route::post('/sync', [FinanceLogApiController::class, 'syncFromFinance'])->name('sync');
    });

    // data role
    Route::prefix('role')->group(function () {
        Route::get('/', [RoleController::class, 'index'])->name('role.index')->can('lihat role');
        Route::post('/store', [RoleController::class, 'store'])->name('role.store')->can('buat role');
        Route::get('/{id}/permission', [RoleController::class, 'permission'])->name('role.permission')->can('ubah role');
        Route::put('/{id}/savePermission', [RoleController::class, 'savePermission'])->name('role.savePermission')->can('ubah role');
        Route::get('/{id}/show', [RoleController::class, 'show'])->name('role.show')->can('lihat role');
        Route::put('/{id}/update', [RoleController::class, 'update'])->name('role.update')->can('ubah role');
        Route::delete('/{id}/destroy', [RoleController::class, 'destroy'])->name('role.destroy')->can('hapus role');
    });

    // data users
    Route::prefix('users')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('user.index')->can('lihat pengguna');
        Route::get('/create', [UserController::class, 'create'])->name('user.create')->can('buat pengguna');
        Route::post('/store', [UserController::class, 'store'])->name('user.store')->can('buat pengguna');
        Route::get('/{id}/edit', [UserController::class, 'edit'])->name('user.edit')->can('ubah pengguna');
        Route::put('/{id}/update', [UserController::class, 'update'])->name('user.update')->can('ubah pengguna');
        Route::delete('/{id}/destroy', [UserController::class, 'destroy'])->name('user.destroy')->can('hapus pengguna');
    });

    // setting
    Route::prefix('setting')->group(function () {
        Route::get('/', [SettingController::class, 'index'])->name('setting')->can('view-settings');
        Route::post('/store', [SettingController::class, 'store'])->name('setting.store')->can('manage-settings');
        Route::post('/dashboard-columns', [SettingController::class, 'saveDashboardColumns'])->name('setting.dashboard.columns')->can('manage-settings');
    });

    // Preview Tampilan Email Rekapan Harian di Browser
    Route::get('/preview-daily-report', function () {
        $shareholder = \App\Models\Shareholder::first();
        $analytics = app(\App\Services\FinanceAnalyticsService::class);
        $targetDate = request('date', date('Y-m-d'));
        $dailyData = $analytics->getDailyDetailedReportData($targetDate, true);
        $personalProfit = $dailyData['net_profit'] * (($shareholder->total_percentage ?? 0) / 100);

        return new \App\Mail\DailyFinancialReportMail($shareholder, $dailyData, $personalProfit);
    })->name('email.preview.daily');

    // Unduh Dokumen PDF Laporan Finansial Harian Langsung
    Route::get('/download-daily-report-pdf', function () {
        $shareholder = \App\Models\Shareholder::first();
        $setting = \App\Models\Setting::first();
        $analytics = app(\App\Services\FinanceAnalyticsService::class);
        $targetDate = request('date', date('Y-m-d'));
        $dailyData = $analytics->getDailyDetailedReportData($targetDate, true);
        $personalProfit = $dailyData['net_profit'] * (($shareholder->total_percentage ?? 0) / 100);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.daily_financial_report', [
            'shareholder'    => $shareholder,
            'dailyData'      => $dailyData,
            'personalProfit' => $personalProfit,
            'setting'        => $setting,
        ])->setPaper('a4', 'portrait');

        $safeName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $shareholder->name ?? 'Investor');
        return $pdf->download("Laporan_Finansial_Harian_{$targetDate}_{$safeName}.pdf");
    })->name('daily.report.pdf.download');
});
