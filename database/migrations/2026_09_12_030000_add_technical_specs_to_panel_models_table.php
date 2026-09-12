<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('panel_models', function (Blueprint $table) {
            $table->string('technology', 100)->default('Monocristalino')->after('nominal_power_kw');
            $table->string('panel_type', 100)->default('Modulo fotovoltaico')->after('technology');
            $table->decimal('efficiency_percent', 5, 2)->nullable()->after('panel_type');
            $table->string('dimensions', 100)->nullable()->after('efficiency_percent');
            $table->decimal('weight_kg', 7, 2)->nullable()->after('dimensions');
            $table->unsignedSmallInteger('warranty_years')->nullable()->after('weight_kg');
        });
    }

    public function down(): void
    {
        Schema::table('panel_models', function (Blueprint $table) {
            $table->dropColumn([
                'technology',
                'panel_type',
                'efficiency_percent',
                'dimensions',
                'weight_kg',
                'warranty_years',
            ]);
        });
    }
};
