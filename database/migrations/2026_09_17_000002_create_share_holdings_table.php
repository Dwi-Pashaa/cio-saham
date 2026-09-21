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
        Schema::create('share_holdings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shareholder_id')->constrained('shareholders')->cascadeOnDelete();
            $table->string('share_code', 50); // e.g. CIO-CORE-01, CIO-SAAS-02
            $table->string('entity_name', 255); // e.g. CIO Network Core, CIO SaaS Solution
            $table->unsignedBigInteger('total_shares'); // Lembar saham
            $table->decimal('nominal_value_per_share', 15, 2)->default(10000); // Nilai par dasar per lembar
            $table->decimal('total_investment', 18, 2); // Total nominal investasi (lembar * par)
            $table->decimal('percentage_share', 5, 2)->default(0); // Persentase kepemilikan (%)
            $table->string('certificate_number', 100)->nullable();
            $table->date('acquisition_date');
            $table->enum('status', ['active', 'transferred', 'sold'])->default('active');
            $table->timestamps();

            $table->index(['shareholder_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('share_holdings');
    }
};
