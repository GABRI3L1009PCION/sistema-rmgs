<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('maintenance_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('solar_farm_id')->constrained()->cascadeOnDelete();
            $table->foreignId('panel_model_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->string('type', 80);
            $table->enum('priority', ['low', 'medium', 'high', 'critical'])->default('medium');
            $table->enum('status', ['scheduled', 'in_progress', 'completed', 'cancelled'])->default('scheduled');
            $table->dateTime('scheduled_at')->index();
            $table->dateTime('started_at')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->decimal('cost', 12, 2)->nullable();
            $table->text('description')->nullable();
            $table->text('resolution_notes')->nullable();
            $table->timestamps();
            $table->index(['solar_farm_id', 'status'], 'maintenance_farm_status_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenance_records');
    }
};
