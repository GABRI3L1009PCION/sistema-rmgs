<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('energy_records', function (Blueprint $table) {
            $table->foreign('solar_farm_id')
                ->references('id')
                ->on('solar_farms')
                ->cascadeOnDelete();
            $table->unique(['solar_farm_id', 'period'], 'energy_records_farm_period_unique');
            $table->index('period', 'energy_records_period_index');
        });
    }

    public function down(): void
    {
        Schema::table('energy_records', function (Blueprint $table) {
            $table->dropForeign(['solar_farm_id']);
            $table->dropUnique('energy_records_farm_period_unique');
            $table->dropIndex('energy_records_period_index');
        });
    }
};
