<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('farm_panels', function (Blueprint $table) {
            $table->enum('status', ['active', 'inactive'])->default('active')->after('quantity');
            $table->index('status', 'farm_panels_status_index');
        });
    }

    public function down(): void
    {
        Schema::table('farm_panels', function (Blueprint $table) {
            $table->dropIndex('farm_panels_status_index');
            $table->dropColumn('status');
        });
    }
};
