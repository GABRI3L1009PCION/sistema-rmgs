<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OperationsSeeder extends Seeder
{
    public function run(): void
    {
        $adminId = DB::table('users')->where('email', 'admin@rmgs.test')->value('id');
        $technicianId = DB::table('users')->where('email', 'tecnico@rmgs.test')->value('id');
        $settings = [
            ['general', 'organization_name', 'RMGS - Registro y Monitoreo de Generacion Solar', 'string', true],
            ['general', 'contact_email', 'admin@rmgs.test', 'string', true],
            ['general', 'co2_factor', '0.40', 'decimal', true],
            ['alerts', 'low_generation_threshold', '80', 'integer', false],
            ['alerts', 'offline_minutes', '30', 'integer', false],
            ['alerts', 'panel_temperature_limit', '75', 'integer', false],
            ['security', 'session_minutes', '60', 'integer', false],
        ];
        foreach ($settings as [$group, $key, $value, $type, $public]) {
            DB::table('system_settings')->updateOrInsert(['key' => $key], [
                'group' => $group, 'value' => $value, 'type' => $type,
                'is_public' => $public, 'updated_by' => $adminId,
                'updated_at' => now(), 'created_at' => now(),
            ]);
        }

        $farms = DB::table('solar_farms')->orderBy('id')->get();
        foreach ($farms as $index => $farm) {
            $panelId = DB::table('farm_panels')->where('solar_farm_id', $farm->id)->value('panel_model_id');
            DB::table('maintenance_records')->updateOrInsert(
                ['solar_farm_id' => $farm->id, 'type' => 'Inspeccion preventiva'],
                ['panel_model_id' => $panelId, 'assigned_to' => $technicianId,
                    'priority' => $index % 5 === 0 ? 'high' : 'medium',
                    'status' => $index < 5 ? 'completed' : 'scheduled',
                    'scheduled_at' => now()->addDays(($index - 5) * 4),
                    'completed_at' => $index < 5 ? now()->subDays(5 - $index) : null,
                    'cost' => 900 + ($index * 75),
                    'description' => 'Revision electrica, limpieza y comprobacion de rendimiento.',
                    'resolution_notes' => $index < 5 ? 'Inspeccion completada sin hallazgos criticos.' : null,
                    'updated_at' => now(), 'created_at' => now()]
            );

            $average = (float) DB::table('energy_records')->where('solar_farm_id', $farm->id)->avg('actual_kwh');
            foreach (range(1, 4) as $year) {
                $target = now()->addYears($year)->startOfYear();
                $projected = round($average * 12 * (1 + (.08 * $year)), 2);
                DB::table('generation_projections')->updateOrInsert(
                    ['solar_farm_id' => $farm->id, 'target_period' => $target->toDateString(), 'method' => 'moving_average'],
                    ['created_by' => $adminId, 'historical_window' => 12,
                        'projected_kwh' => $projected, 'lower_bound_kwh' => round($projected * .90, 2),
                        'upper_bound_kwh' => round($projected * 1.10, 2),
                        'parameters' => json_encode(['annual_growth' => .08]),
                        'updated_at' => now(), 'created_at' => now()]
                );
            }
        }
    }
}
