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
        Schema::create('investment_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shareholder_id')->constrained('shareholders')->onDelete('cascade');
            $table->integer('year')->comment('Tahun Mulai Investasi / Periode Laporan');
            $table->decimal('initial_capital', 15, 2)->comment('Nominal Saham / Modal Awal');
            $table->decimal('profit_amount', 15, 2)->comment('Keuntungan Setelah 1 Tahun');
            $table->string('status')->default('distributed')->comment('Status Pembagian: distributed, pending, reinvested');
            $table->text('notes')->nullable()->comment('Keterangan / Catatan Tambahan');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('investment_reports');
    }
};
