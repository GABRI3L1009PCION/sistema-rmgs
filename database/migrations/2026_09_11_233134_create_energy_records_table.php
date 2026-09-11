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
        Schema::create('energy_records', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('solar_farm_id');
            $table->date('period');
            $table->decimal('actual_kwh', 12, 2);
            $table->decimal('expected_kwh', 12, 2);
            $table->decimal('co2_avoided_kg', 12, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('energy_records');
    }
};
