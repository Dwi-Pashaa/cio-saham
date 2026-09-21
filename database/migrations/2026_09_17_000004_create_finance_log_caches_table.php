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
        Schema::create('finance_log_caches', function (Blueprint $table) {
            $table->id();
            $table->string('source_client_code', 100);
            $table->string('source_client_name', 255);
            $table->string('event', 50)->default('created');
            $table->string('subject_type', 100); // Income, Expense, BalanceDeduct, etc.
            $table->decimal('amount', 18, 2)->default(0);
            $table->string('balance_type', 50)->nullable(); // manual, xendit, auto
            $table->text('description')->nullable();
            $table->timestamp('log_created_at')->nullable();
            $table->json('raw_payload')->nullable();
            $table->timestamps();

            $table->index(['source_client_code', 'log_created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('finance_log_caches');
    }
};
