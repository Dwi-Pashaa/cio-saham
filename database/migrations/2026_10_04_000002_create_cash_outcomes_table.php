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
        Schema::create('cash_outcomes', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_number', 50)->unique();
            $table->date('transaction_date');
            $table->string('recipient_name', 255);     // Rekening Tujuan
            $table->string('bank_name', 100);          // Rekening / Nama Bank
            $table->string('account_number', 100);     // No Rekening
            $table->decimal('amount', 15, 2);          // Nominal Pokok
            $table->boolean('has_admin_fee')->default(false); // Ada biaya admin? (ya/tidak)
            $table->decimal('admin_fee', 15, 2)->default(0);  // Biaya admin (manual jika ya, 0 jika tidak)
            $table->decimal('total_amount', 15, 2);    // Total pengeluaran (amount + admin_fee)
            $table->string('proof_file', 255);         // Upload bukti transaksi (wajib)
            $table->string('receipt_file', 255)->nullable(); // Upload nota pembelian (opsional)
            $table->text('notes');                     // Catatan (wajib)
            $table->boolean('is_asset')->default(false); // Masukkan ke aset ya/tidak
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('transaction_date');
            $table->index('recipient_name');
            $table->index('is_asset');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cash_outcomes');
    }
};
