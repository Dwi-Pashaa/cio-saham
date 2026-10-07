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
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->string('name');                                     // Nama barang (WAJIB)
            $table->string('type');                                     // Tipe/kategori barang (WAJIB)
            $table->decimal('price', 15, 2);                            // Harga perolehan (WAJIB)
            $table->string('serial_number')->nullable();                // Serial Number (OPSIONAL)
            $table->string('mac_address')->nullable();                  // MAC Address (OPSIONAL)
            $table->string('owner_type')->default('pt');                // 'pt' atau 'shareholder'
            $table->foreignId('shareholder_id')->nullable()
                  ->constrained('shareholders')->nullOnDelete();       // FK ke investor (opsional)
            $table->string('owner_name')->default('PT CIO NETWORK SOLUTION');
            $table->date('purchase_date')->nullable();                  // Tanggal perolehan
            $table->text('notes')->nullable();                          // Catatan spesifikasi/lokasi
            $table->foreignId('created_by')->nullable()
                  ->constrained('users')->nullOnDelete();               // Pencatat aset
            $table->timestamps();

            // Indexes untuk kecepatan query & filter DataTables
            $table->index('name');
            $table->index('type');
            $table->index('serial_number');
            $table->index('mac_address');
            $table->index('owner_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};
