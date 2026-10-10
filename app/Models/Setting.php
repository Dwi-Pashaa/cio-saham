<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;
    protected $table = 'settings';
    protected $fillable = [
        'telp', 
        'notification_channel', 
        'fonnte_token',
        'target_wa_kas',
        'admin_fee', 
        'cio_finance_base_url',
        'cio_finance_client_id',
        'cio_finance_key_id',
        'cio_finance_secret_key',
        'cio_finance_timeout',
        'xendit_secret_key', 
        'xendit_webhook_token', 
        'dashboard_columns',
        'savings_recipient_name',
        'savings_bank_name',
        'savings_account_number',
    ];
    protected $casts = [
        'dashboard_columns'   => 'array',
        'admin_fee'           => 'double',
        'cio_finance_timeout' => 'integer',
    ];

    /**
     * Return the list of dashboard columns with their visibility.
     * Falls back to all columns visible if not configured.
     */
    public static function dashboardColumns(): array
    {
        $setting = self::first();
        $saved = $setting?->dashboard_columns ?? [];

        $defaults = [
            'dana_investasi'     => ['label' => 'Dana Investasi',     'visible' => true],
            'persentase'         => ['label' => 'Persentase',         'visible' => true],
            'nominal_pendapatan' => ['label' => 'Nominal Pendapatan', 'visible' => true],
            'status_pembayaran'  => ['label' => 'Status Pembayaran',  'visible' => true],
        ];

        foreach ($defaults as $key => &$col) {
            if (isset($saved[$key])) {
                $col['visible'] = (bool) $saved[$key]['visible'];
            }
        }

        return $defaults;
    }

    /**
     * Ambil data rekening tabungan default yang tersimpan.
     * Fallback ke transaksi alokasi tabungan terakhir jika belum disimpan di settings.
     */
    public static function getDefaultSavingsAccount(): array
    {
        $setting = self::first();
        if ($setting && !empty($setting->savings_recipient_name)) {
            return [
                'recipient_name' => $setting->savings_recipient_name,
                'bank_name'      => $setting->savings_bank_name ?? '',
                'account_number' => $setting->savings_account_number ?? '',
                'is_configured'  => true,
            ];
        }

        $latestSaving = CashSaving::latest('id')->first();
        if ($latestSaving) {
            return [
                'recipient_name' => $latestSaving->recipient_name,
                'bank_name'      => $latestSaving->bank_name,
                'account_number' => $latestSaving->account_number ?? '',
                'is_configured'  => true,
            ];
        }

        return [
            'recipient_name' => '',
            'bank_name'      => '',
            'account_number' => '',
            'is_configured'  => false,
        ];
    }
}

