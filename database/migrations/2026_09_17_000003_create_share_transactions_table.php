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
        Schema::create('share_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('share_holding_id')->constrained('share_holdings')->cascadeOnDelete();
            $table->enum('transaction_type', ['initial', 'buy', 'sell', 'split'])->default('initial');
            $table->unsignedBigInteger('shares_amount');
            $table->decimal('price_per_share', 15, 2);
            $table->decimal('total_amount', 18, 2);
            $table->date('transaction_date');
            $table->string('reference_no', 100)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('share_transactions');
    }
};
