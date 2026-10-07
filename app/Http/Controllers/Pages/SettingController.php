<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\FonnteService;
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
            ]);
        }
        $dashboardColumns = Setting::dashboardColumns();
        return view("pages.setting.index", compact("settings", "dashboardColumns"));    
    }

    public function store(Request $request) 
    {
        $request->validate([
            "telp"                 => "nullable|string",
            "notification_channel" => "nullable|in:whatsapp,email,both,none",
            "fonnte_token"         => "nullable|string",
            "target_wa_kas"        => "nullable|string",
            "admin_fee"            => "nullable",
        ]);

        $setting = Setting::find($request->id ?? 1);
        $telp = $request->has('telp') ? $request->telp : ($setting->telp ?? '628123456789');
        $notificationChannel = $request->notification_channel ?? ($setting->notification_channel ?? 'whatsapp');
        $fonnteToken = $request->has('fonnte_token') ? $request->fonnte_token : ($setting->fonnte_token ?? null);
        $targetWaKas = $request->has('target_wa_kas') ? $request->target_wa_kas : ($setting->target_wa_kas ?? null);
        $adminFee = $request->has('admin_fee') ? (float) str_replace('.', '', $request->admin_fee ?? 0) : ($setting->admin_fee ?? 0);

        Setting::updateOrCreate(
            ['id' => $request->id ?? 1],
            [
                "telp"                 => $telp,
                "notification_channel" => $notificationChannel,
                "fonnte_token"         => $fonnteToken,
                "target_wa_kas"        => $targetWaKas,
                "admin_fee"            => $adminFee,
            ]
        );

        return back()->with('success', 'Berhasil memperbarui pengaturan sistem.');
    }

    public function testFonnte(Request $request, FonnteService $fonnteService)
    {
        $request->validate([
            'target' => 'required|string',
        ]);

        $result = $fonnteService->testConnection($request->target);

        if ($result['status']) {
            return response()->json([
                'status'  => 'success',
                'message' => 'Pesan uji coba WhatsApp berhasil dikirim ke ' . $request->target . ' via Fonnte!',
            ]);
        }

        return response()->json([
            'status'  => 'error',
            'message' => $result['message'],
        ], 400);
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
