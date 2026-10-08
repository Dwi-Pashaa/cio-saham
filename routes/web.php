<?php

use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Pages\AssetController;
use App\Http\Controllers\Pages\CashIncomeController;
use App\Http\Controllers\Pages\CashOutcomeController;
use App\Http\Controllers\Pages\CashSavingController;
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
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard')->can('lihat dashboard');
    Route::get('/dashboard/chart-data', [DashboardController::class, 'chartData'])->name('dashboard.chart-data')->can('lihat dashboard');

    // Profil Akun (Menu Default untuk SEMUA level akses tanpa batas permission)
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

    // Manajemen Saldo Masuk (Pemasukan)
    Route::prefix('cash-incomes')->name('cash-incomes.')->group(function () {
        Route::get('/', [CashIncomeController::class, 'index'])->name('index')->can('lihat pemasukan');
        Route::get('/export', [CashIncomeController::class, 'export'])->name('export')->can('lihat pemasukan');
        Route::get('/create', [CashIncomeController::class, 'create'])->name('create')->can('tambah pemasukan');
        Route::post('/store', [CashIncomeController::class, 'store'])->name('store')->can('tambah pemasukan');
        Route::get('/{id}', [CashIncomeController::class, 'show'])->name('show')->can('lihat pemasukan');
        Route::get('/{id}/edit', [CashIncomeController::class, 'edit'])->name('edit')->can('ubah pemasukan');
        Route::put('/{id}', [CashIncomeController::class, 'update'])->name('update')->can('ubah pemasukan');
        Route::delete('/{id}', [CashIncomeController::class, 'destroy'])->name('destroy')->can('hapus pemasukan');
    });

    // Manajemen Saldo Keluar (Pengeluaran)
    Route::prefix('cash-outcomes')->name('cash-outcomes.')->group(function () {
        Route::get('/', [CashOutcomeController::class, 'index'])->name('index')->can('lihat pengeluaran');
        Route::get('/export', [CashOutcomeController::class, 'export'])->name('export')->can('lihat pengeluaran');
        Route::get('/create', [CashOutcomeController::class, 'create'])->name('create')->can('tambah pengeluaran');
        Route::post('/store', [CashOutcomeController::class, 'store'])->name('store')->can('tambah pengeluaran');
        Route::get('/{id}', [CashOutcomeController::class, 'show'])->name('show')->can('lihat pengeluaran');
        Route::get('/{id}/edit', [CashOutcomeController::class, 'edit'])->name('edit')->can('ubah pengeluaran');
        Route::put('/{id}', [CashOutcomeController::class, 'update'])->name('update')->can('ubah pengeluaran');
        Route::delete('/{id}', [CashOutcomeController::class, 'destroy'])->name('destroy')->can('hapus pengeluaran');
    });

    // Manajemen Saldo Tabungan (Pembagian ke Tabungan)
    Route::prefix('cash-savings')->name('cash-savings.')->group(function () {
        Route::post('/store', [CashSavingController::class, 'store'])->name('store')->can('bagikan ke tabungan');
        Route::get('/{id}', [CashSavingController::class, 'show'])->name('show')->can('lihat tabungan');
        Route::delete('/{id}', [CashSavingController::class, 'destroy'])->name('destroy')->can('hapus tabungan');
    });

    // Manajemen Aset Perusahaan & Investor
    Route::prefix('assets')->name('assets.')->group(function () {
        Route::get('/', [AssetController::class, 'index'])->name('index')->can('lihat aset');
        Route::get('/export', [AssetController::class, 'export'])->name('export')->can('lihat aset');
        Route::get('/create', [AssetController::class, 'create'])->name('create')->can('tambah aset');
        Route::post('/store', [AssetController::class, 'store'])->name('store')->can('tambah aset');
        Route::get('/{id}', [AssetController::class, 'show'])->name('show')->can('lihat aset');
        Route::get('/{id}/edit', [AssetController::class, 'edit'])->name('edit')->can('ubah aset');
        Route::put('/{id}', [AssetController::class, 'update'])->name('update')->can('ubah aset');
        Route::delete('/{id}', [AssetController::class, 'destroy'])->name('destroy')->can('hapus aset');
        Route::delete('/images/{imageId}', [AssetController::class, 'destroyImage'])->name('images.destroy')->can('ubah aset');
    });

    // Manajemen Pemilik Saham (Shareholders)
    Route::prefix('shareholders')->name('shareholders.')->group(function () {
        Route::get('/', [ShareholderController::class, 'index'])->name('index')->can('lihat investor');
        Route::get('/create', [ShareholderController::class, 'create'])->name('create')->can('tambah investor');
        Route::post('/store', [ShareholderController::class, 'store'])->name('store')->can('tambah investor');
        Route::get('/{id}', [ShareholderController::class, 'show'])->name('show')->can('lihat investor');
        Route::get('/{id}/edit', [ShareholderController::class, 'edit'])->name('edit')->can('ubah investor');
        Route::put('/{id}', [ShareholderController::class, 'update'])->name('update')->can('ubah investor');
        Route::delete('/{id}', [ShareholderController::class, 'destroy'])->name('destroy')->can('hapus investor');
    });

    // Manajemen Alokasi Saham Per Pemilik (1 to Many)
    Route::prefix('share-holdings')->name('share-holdings.')->group(function () {
        Route::post('/store', [ShareHoldingController::class, 'store'])->name('store')->can('tambah kepemilikan saham');
        Route::put('/{id}', [ShareHoldingController::class, 'update'])->name('update')->can('ubah kepemilikan saham');
        Route::delete('/{id}', [ShareHoldingController::class, 'destroy'])->name('destroy')->can('hapus kepemilikan saham');
    });

    // Direktori Portofolio Pemegang Saham (Read-Only & Search untuk Level Pemegang Saham & Admin)
    Route::prefix('investor-directory')->name('investor-directory.')->group(function () {
        Route::get('/', [InvestorDirectoryController::class, 'index'])->name('index')->can('lihat portofolio investor');
        Route::get('/{id}', [InvestorDirectoryController::class, 'show'])->name('show')->can('lihat portofolio investor');
    });

    // Laporan Keuntungan Saham / Imbal Hasil Tahunan (CRUD Admin, Read-Only Pemegang Saham)
    Route::prefix('investment-reports')->name('investment-reports.')->group(function () {
        Route::get('/', [InvestmentReportController::class, 'index'])->name('index')->can('lihat laporan imbal hasil');
        Route::post('/store', [InvestmentReportController::class, 'store'])->name('store')->can('tambah laporan imbal hasil');
        Route::put('/{id}', [InvestmentReportController::class, 'update'])->name('update')->can('ubah laporan imbal hasil');
        Route::delete('/{id}', [InvestmentReportController::class, 'destroy'])->name('destroy')->can('hapus laporan imbal hasil');
    });

    // data role (Level Akses)
    Route::prefix('master-data/level-akses')->name('role.')->group(function () {
        Route::get('/', [RoleController::class, 'index'])->name('index')->can('lihat level akses');
        Route::post('/store', [RoleController::class, 'store'])->name('store')->can('tambah level akses');
        Route::get('/{id}/permission', [RoleController::class, 'permission'])->name('permission')->can('atur hak akses');
        Route::put('/{id}/savePermission', [RoleController::class, 'savePermission'])->name('savePermission')->can('atur hak akses');
        Route::get('/{id}/show', [RoleController::class, 'show'])->name('show')->can('lihat level akses');
        Route::put('/{id}/update', [RoleController::class, 'update'])->name('update')->can('ubah level akses');
        Route::delete('/{id}/destroy', [RoleController::class, 'destroy'])->name('destroy')->can('hapus level akses');
    });

    // data users (Pengguna)
    Route::prefix('master-data/pengguna')->name('user.')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('index')->can('lihat pengguna');
        Route::get('/create', [UserController::class, 'create'])->name('create')->can('tambah pengguna');
        Route::post('/store', [UserController::class, 'store'])->name('store')->can('tambah pengguna');
        Route::get('/{id}/edit', [UserController::class, 'edit'])->name('edit')->can('ubah pengguna');
        Route::put('/{id}/update', [UserController::class, 'update'])->name('update')->can('ubah pengguna');
        Route::delete('/{id}/destroy', [UserController::class, 'destroy'])->name('destroy')->can('hapus pengguna');
    });

    // setting
    Route::prefix('setting')->group(function () {
        Route::get('/', [SettingController::class, 'index'])->name('setting')->can('lihat pengaturan');
        Route::post('/store', [SettingController::class, 'store'])->name('setting.store')->can('ubah pengaturan');
        Route::post('/dashboard-columns', [SettingController::class, 'saveDashboardColumns'])->name('setting.dashboard.columns')->can('ubah pengaturan');
        Route::post('/test-fonnte', [SettingController::class, 'testFonnte'])->name('setting.test-fonnte')->can('ubah pengaturan');
    });
});
