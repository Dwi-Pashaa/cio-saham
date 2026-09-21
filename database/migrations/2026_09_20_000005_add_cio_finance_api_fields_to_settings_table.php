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
            if (!Schema::hasColumn('settings', 'notification_channel')) {
                $table->string('notification_channel')->default('whatsapp')->after('telp');
            }
            if (!Schema::hasColumn('settings', 'admin_fee')) {
                $table->decimal('admin_fee', 15, 2)->default(0)->after('notification_channel');
            }
            if (!Schema::hasColumn('settings', 'dashboard_columns')) {
                $table->json('dashboard_columns')->nullable()->after('admin_fee');
            }
            if (!Schema::hasColumn('settings', 'cio_finance_base_url')) {
                $table->string('cio_finance_base_url')->nullable()->default('https://finance.cionetwork.id')->after('dashboard_columns');
            }
            if (!Schema::hasColumn('settings', 'cio_finance_client_id')) {
                $table->string('cio_finance_client_id')->nullable()->default('test_web_saham_18dcf3aab5a0d552f5670a3978c7cd22')->after('cio_finance_base_url');
            }
            if (!Schema::hasColumn('settings', 'cio_finance_key_id')) {
                $table->string('cio_finance_key_id')->nullable()->default('kid_4e0479ba4b715ac5')->after('cio_finance_client_id');
            }
            if (!Schema::hasColumn('settings', 'cio_finance_secret_key')) {
                $table->text('cio_finance_secret_key')->nullable()->after('cio_finance_key_id');
            }
            if (!Schema::hasColumn('settings', 'cio_finance_timeout')) {
                $table->integer('cio_finance_timeout')->default(30)->after('cio_finance_secret_key');
            }
        });

        // Seed / Update first record with defaults if empty
        $setting = DB::table('settings')->first();
        if ($setting) {
            DB::table('settings')->where('id', $setting->id)->update([
                'cio_finance_base_url'   => $setting->cio_finance_base_url ?? 'https://finance.cionetwork.id',
                'cio_finance_client_id'  => $setting->cio_finance_client_id ?? 'test_web_saham_18dcf3aab5a0d552f5670a3978c7cd22',
                'cio_finance_key_id'     => $setting->cio_finance_key_id ?? 'kid_4e0479ba4b715ac5',
                'cio_finance_secret_key' => $setting->cio_finance_secret_key ?? 'b60777bc6d6569ad65f875e81f824cda74b3c1cb05a188081ef974ee6c943ed7',
                'cio_finance_timeout'    => $setting->cio_finance_timeout ?? 30,
            ]);
        } else {
            DB::table('settings')->insert([
                'telp'                   => '628123456789',
                'notification_channel'   => 'whatsapp',
                'admin_fee'              => 0,
                'cio_finance_base_url'   => 'https://finance.cionetwork.id',
                'cio_finance_client_id'  => 'test_web_saham_18dcf3aab5a0d552f5670a3978c7cd22',
                'cio_finance_key_id'     => 'kid_4e0479ba4b715ac5',
                'cio_finance_secret_key' => 'b60777bc6d6569ad65f875e81f824cda74b3c1cb05a188081ef974ee6c943ed7',
                'cio_finance_timeout'    => 30,
                'created_at'             => now(),
                'updated_at'             => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn([
                'notification_channel',
                'admin_fee',
                'dashboard_columns',
                'cio_finance_base_url',
                'cio_finance_client_id',
                'cio_finance_key_id',
                'cio_finance_secret_key',
                'cio_finance_timeout',
            ]);
        });
    }
};
