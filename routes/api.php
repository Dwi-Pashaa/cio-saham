<?php

use App\Http\Controllers\Api\FinanceLogApiController;
use App\Http\Controllers\Api\XenditWebhookController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Finance Analytics & Live Feed AJAX API
Route::prefix('finance')->name('api.finance.')->group(function () {
    Route::get('/overview', [FinanceLogApiController::class, 'getOverview'])->name('overview');
    Route::get('/chart', [FinanceLogApiController::class, 'getChartData'])->name('chart');
    Route::get('/growth', [FinanceLogApiController::class, 'getGrowthData'])->name('growth');
    Route::get('/history', [FinanceLogApiController::class, 'getHistoryData'])->name('history');
    Route::post('/sync', [FinanceLogApiController::class, 'syncFromFinance'])->name('sync');
});

// Xendit Webhook Receiver (Central Router Multi-Website)
Route::post('/xendit/callback', [XenditWebhookController::class, 'handleCallback'])->name('api.xendit.callback');
