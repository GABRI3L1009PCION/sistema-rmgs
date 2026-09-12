<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('farm_panels', function (Blueprint $table) {
            $table->unique(['solar_farm_id', 'panel_model_id'], 'farm_panels_farm_model_unique');
            $table->index('quantity', 'farm_panels_quantity_index');
        });
    }

    public function down(): void
    {
        Schema::table('farm_panels', function (Blueprint $table) {
            $table->dropUnique('farm_panels_farm_model_unique');
            $table->dropIndex('farm_panels_quantity_index');
        });
    }
};
