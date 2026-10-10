<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            if (!Schema::hasColumn('settings', 'savings_recipient_name')) {
                $table->string('savings_recipient_name')->nullable()->after('target_wa_kas');
            }
            if (!Schema::hasColumn('settings', 'savings_bank_name')) {
                $table->string('savings_bank_name', 100)->nullable()->after('savings_recipient_name');
            }
            if (!Schema::hasColumn('settings', 'savings_account_number')) {
                $table->string('savings_account_number', 50)->nullable()->after('savings_bank_name');
            }
        });

        // Jika terdapat catatan cash_savings sebelumnya, isi default setting dari transaksi tabungan terakhir
        $latestSaving = DB::table('cash_savings')->orderByDesc('id')->first();
        if ($latestSaving) {
            $setting = DB::table('settings')->first();
            if ($setting) {
                DB::table('settings')->where('id', $setting->id)->update([
                    'savings_recipient_name' => $setting->savings_recipient_name ?? $latestSaving->recipient_name,
                    'savings_bank_name'      => $setting->savings_bank_name ?? $latestSaving->bank_name,
                    'savings_account_number' => $setting->savings_account_number ?? $latestSaving->account_number,
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn([
                'savings_recipient_name',
                'savings_bank_name',
                'savings_account_number',
            ]);
        });
    }
};
