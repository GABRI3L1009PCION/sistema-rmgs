<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('alerts', function (Blueprint $table) {
            $table->string('type', 80)->default('low_generation')->after('energy_record_id');
            $table->enum('priority', ['low', 'medium', 'high', 'critical'])->default('high')->after('type');
            $table->string('title')->nullable()->after('priority');
            $table->text('description')->nullable()->after('title');
            $table->timestamp('detected_at')->nullable()->after('deviation_percent');
            $table->timestamp('resolved_at')->nullable()->after('status');
            $table->foreignId('resolved_by')->nullable()->after('resolved_at')->constrained('users')->nullOnDelete();
            $table->json('metadata')->nullable()->after('resolved_by');
            $table->index(['solar_farm_id', 'status', 'period'], 'alerts_farm_status_period_index');
            $table->index(['priority', 'status'], 'alerts_priority_status_index');
            $table->unique('energy_record_id', 'alerts_energy_record_unique');
        });
    }

    public function down(): void
    {
        Schema::table('alerts', function (Blueprint $table) {
            $table->dropUnique('alerts_energy_record_unique');
            $table->dropIndex('alerts_priority_status_index');
            $table->dropIndex('alerts_farm_status_period_index');
            $table->dropForeign(['resolved_by']);
            $table->dropColumn([
                'type', 'priority', 'title', 'description', 'detected_at', 'resolved_at', 'resolved_by', 'metadata',
            ]);
        });
    }
};
