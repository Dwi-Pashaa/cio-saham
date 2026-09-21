<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index() 
    {
        $settings = Setting::first();
        if (!$settings) {
            $settings = Setting::create([
                'telp'                   => '628123456789',
                'notification_channel'   => 'whatsapp',
                'admin_fee'              => 0,
                'cio_finance_base_url'   => 'https://finance.cionetwork.id',
                'cio_finance_client_id'  => 'test_web_saham_18dcf3aab5a0d552f5670a3978c7cd22',
                'cio_finance_key_id'     => 'kid_4e0479ba4b715ac5',
                'cio_finance_secret_key' => 'b60777bc6d6569ad65f875e81f824cda74b3c1cb05a188081ef974ee6c943ed7',
                'cio_finance_timeout'    => 30,
            ]);
        }
        $dashboardColumns = Setting::dashboardColumns();
        return view("pages.setting.index", compact("settings", "dashboardColumns"));    
    }

    public function store(Request $request) 
    {
        $request->validate([
            "telp"                   => "nullable|string",
            "notification_channel"   => "nullable|in:whatsapp,email,both,none",
            "admin_fee"              => "nullable",
            "cio_finance_base_url"   => "nullable|string",
            "cio_finance_client_id"  => "nullable|string",
            "cio_finance_key_id"     => "nullable|string",
            "cio_finance_secret_key" => "nullable|string",
            "cio_finance_timeout"    => "nullable|numeric",
            "xendit_secret_key"      => "nullable|string",
            "xendit_webhook_token"   => "nullable|string",
        ]);

        $setting = Setting::find($request->id ?? 1);
        $telp = $request->has('telp') ? $request->telp : ($setting->telp ?? '628123456789');
        $notificationChannel = $request->notification_channel ?? ($setting->notification_channel ?? 'whatsapp');
        $adminFee = $request->has('admin_fee') ? (float) str_replace('.', '', $request->admin_fee ?? 0) : ($setting->admin_fee ?? 0);

        $baseUrl   = $request->filled('cio_finance_base_url') ? rtrim($request->cio_finance_base_url, '/') : ($setting->cio_finance_base_url ?? 'https://finance.cionetwork.id');
        $clientId  = $request->filled('cio_finance_client_id') ? $request->cio_finance_client_id : ($setting->cio_finance_client_id ?? 'test_web_saham_18dcf3aab5a0d552f5670a3978c7cd22');
        $keyId     = $request->filled('cio_finance_key_id') ? $request->cio_finance_key_id : ($setting->cio_finance_key_id ?? 'kid_4e0479ba4b715ac5');
        $secretKey = $request->filled('cio_finance_secret_key') ? $request->cio_finance_secret_key : ($setting->cio_finance_secret_key ?? 'b60777bc6d6569ad65f875e81f824cda74b3c1cb05a188081ef974ee6c943ed7');
        $timeout   = $request->filled('cio_finance_timeout') ? (int) $request->cio_finance_timeout : ($setting->cio_finance_timeout ?? 30);

        Setting::updateOrCreate(
            ['id' => $request->id ?? 1],
            [
                "telp"                   => $telp,
                "notification_channel"   => $notificationChannel,
                "admin_fee"              => $adminFee,
                "cio_finance_base_url"   => $baseUrl,
                "cio_finance_client_id"  => $clientId,
                "cio_finance_key_id"     => $keyId,
                "cio_finance_secret_key" => $secretKey,
                "cio_finance_timeout"    => $timeout,
                "xendit_secret_key"      => $request->xendit_secret_key ?? ($setting->xendit_secret_key ?? null),
                "xendit_webhook_token"   => $request->xendit_webhook_token ?? ($setting->xendit_webhook_token ?? null),
            ]
        );

        return back()->with('success', 'Berhasil memperbarui pengaturan sistem.');
    }

    public function saveDashboardColumns(Request $request)
    {
        $columns = [];
        $allowedKeys = ['dana_investasi', 'persentase', 'nominal_pendapatan', 'status_pembayaran'];

        foreach ($allowedKeys as $key) {
            $columns[$key] = [
                'visible' => $request->has("columns.$key") ? true : false
            ];
        }

        Setting::updateOrCreate([], ['dashboard_columns' => $columns]);

        return back()->with('success', 'Pengaturan kolom dashboard berhasil disimpan.');
    }
}
