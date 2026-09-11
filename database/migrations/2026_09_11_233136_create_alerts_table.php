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
        Schema::create('alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('solar_farm_id')->constrained()->cascadeOnDelete();
            $table->foreignId('energy_record_id')->nullable()->constrained()->nullOnDelete();
            $table->date('period');
            $table->decimal('actual_kwh', 12, 2);
            $table->decimal('expected_kwh', 12, 2);
            $table->decimal('deviation_percent', 6, 2);
            $table->enum('status', ['active', 'resolved'])->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alerts');
    }
};
