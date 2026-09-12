<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('solar_farms', function (Blueprint $table) {
            $table->unique(['department_id', 'name'], 'solar_farms_department_name_unique');
            $table->index(['department_id', 'status'], 'solar_farms_department_status_index');
            $table->index('municipality', 'solar_farms_municipality_index');
        });
    }

    public function down(): void
    {
        Schema::table('solar_farms', function (Blueprint $table) {
            $table->dropUnique('solar_farms_department_name_unique');
            $table->dropIndex('solar_farms_department_status_index');
            $table->dropIndex('solar_farms_municipality_index');
        });
    }
};
