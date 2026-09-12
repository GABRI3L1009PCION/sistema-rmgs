<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('generation_projections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('solar_farm_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('target_period');
            $table->string('method', 80)->default('moving_average');
            $table->unsignedTinyInteger('historical_window')->default(3);
            $table->decimal('projected_kwh', 14, 2);
            $table->decimal('lower_bound_kwh', 14, 2)->nullable();
            $table->decimal('upper_bound_kwh', 14, 2)->nullable();
            $table->decimal('actual_kwh', 14, 2)->nullable();
            $table->json('parameters')->nullable();
            $table->timestamps();
            $table->unique(
                ['solar_farm_id', 'target_period', 'method'],
                'generation_projections_farm_period_method_unique'
            );
            $table->index('target_period', 'generation_projections_period_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('generation_projections');
    }
};
