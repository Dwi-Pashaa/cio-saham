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
        Schema::create('cash_incomes', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_number', 50)->unique();
            $table->date('transaction_date');
            $table->string('sender_name', 255);        // Dari Rekening
            $table->string('bank_name', 100);          // Rekening / Nama Bank
            $table->string('account_number', 100);     // No Rekening
            $table->decimal('amount', 15, 2);          // Nominal Pokok
            $table->boolean('has_admin_fee')->default(false); // Ada biaya admin? (ya/tidak)
            $table->decimal('admin_fee', 15, 2)->default(0);  // Biaya admin (manual jika ya, 0 jika tidak)
            $table->decimal('net_amount', 15, 2);      // Total diterima
            $table->string('proof_file', 255);         // Upload bukti transaksi (wajib)
            $table->text('notes');                     // Catatan (wajib)
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('transaction_date');
            $table->index('sender_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cash_incomes');
    }
};
