<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            if (!Schema::hasColumn('settings', 'fonnte_token')) {
                $table->string('fonnte_token')->nullable()->after('notification_channel');
            }
            if (!Schema::hasColumn('settings', 'target_wa_kas')) {
                $table->string('target_wa_kas')->nullable()->after('fonnte_token');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn(['fonnte_token', 'target_wa_kas']);
        });
    }
};
