<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SolarDataSeeder extends Seeder
{
    public function run(): void
    {
        $farms = [
            ['Alta Verapaz', 'Central Solar Verapaz', 'Coban', 15.4697, -90.3723],
            ['Baja Verapaz', 'Valle Solar Salama', 'Salama', 15.1028, -90.3181],
            ['Chimaltenango', 'Parque Solar Chimaltenango', 'Chimaltenango', 14.6611, -90.8194],
            ['Chiquimula', 'Oriente Solar Chiquimula', 'Chiquimula', 14.7972, -89.5448],
            ['El Progreso', 'Corredor Solar Guastatoya', 'Guastatoya', 14.8538, -90.0649],
            ['Escuintla', 'Costa Sur Energia Limpia', 'Santa Lucia Cotzumalguapa', 14.3350, -91.0232],
            ['Guatemala', 'Nodo Solar Central', 'Villa Nueva', 14.5269, -90.5875],
            ['Huehuetenango', 'Cuchumatanes Solar', 'Huehuetenango', 15.3192, -91.4706],
            ['Izabal', 'Parque Solar Puerto Barrios', 'Puerto Barrios', 15.7322, -88.5945],
            ['Jalapa', 'Jalapa Renovable', 'Jalapa', 14.6347, -89.9886],
            ['Jutiapa', 'Jutiapa Solar', 'Jutiapa', 14.2919, -89.8958],
            ['Peten', 'Mundo Maya Solar', 'Flores', 16.9297, -89.8917],
            ['Quetzaltenango', 'Altiplano Fotovoltaico', 'Olintepeque', 14.8870, -91.5132],
            ['Quiche', 'Energia Solar Quiche', 'Santa Cruz del Quiche', 15.0306, -91.1481],
            ['Retalhuleu', 'Retalhuleu Fotovoltaico', 'Retalhuleu', 14.5349, -91.6770],
            ['Sacatepequez', 'Antigua Solar', 'Antigua Guatemala', 14.5586, -90.7339],
            ['San Marcos', 'Marquense Solar', 'San Marcos', 14.9639, -91.7944],
            ['Santa Rosa', 'Santa Rosa Energia', 'Cuilapa', 14.2769, -90.2993],
            ['Solola', 'Lago Solar Atitlan', 'Panajachel', 14.7400, -91.1590],
            ['Suchitepequez', 'Mazatenango Solar', 'Mazatenango', 14.5347, -91.5038],
            ['Totonicapan', 'Totonicapan Solar', 'Totonicapan', 14.9117, -91.3611],
            ['Zacapa', 'Granja Solar Motagua', 'Rio Hondo', 15.0410, -89.5852],
        ];
        $modelIds = DB::table('panel_models')->orderBy('id')->pluck('id')->values();

        foreach ($farms as $index => [$department, $name, $municipality, $latitude, $longitude]) {
            $departmentId = DB::table('departments')->where('name', $department)->value('id');
            DB::table('solar_farms')->updateOrInsert(['department_id' => $departmentId, 'name' => $name], [
                'owner' => 'Programa Solar Guatemala', 'municipality' => $municipality,
                'latitude' => $latitude, 'longitude' => $longitude,
                'families_benefited' => 350 + ($index * 45),
                'status' => $index % 9 === 0 ? 'maintenance' : 'active',
                'notes' => 'Datos demostrativos para monitoreo nacional.',
                'updated_at' => now(), 'created_at' => now(),
            ]);
            $farmId = DB::table('solar_farms')->where('department_id', $departmentId)->where('name', $name)->value('id');
            $modelId = $modelIds[$index % $modelIds->count()];
            $quantity = 240 + (($index % 8) * 90);
            DB::table('farm_panels')->updateOrInsert(
                ['solar_farm_id' => $farmId, 'panel_model_id' => $modelId],
                ['quantity' => $quantity, 'updated_at' => now(), 'created_at' => now()]
            );
            $power = (float) DB::table('panel_models')->where('id', $modelId)->value('nominal_power_kw');
            for ($month = 0; $month < 12; $month++) {
                $period = now()->startOfMonth()->subMonths(11 - $month);
                $expected = round($quantity * $power * (118 + (($month % 6) * 7)), 2);
                $ratio = (($index + $month) % 11 === 0) ? .74 : (.91 + ((($index * 3 + $month) % 16) / 100));
                $actual = round($expected * $ratio, 2);
                DB::table('energy_records')->updateOrInsert(
                    ['solar_farm_id' => $farmId, 'period' => $period->toDateString()],
                    ['actual_kwh' => $actual, 'expected_kwh' => $expected,
                        'co2_avoided_kg' => round($actual * .40, 2), 'notes' => 'Medicion mensual de demostracion.',
                        'updated_at' => now(), 'created_at' => now()]
                );
            }
        }

        foreach (DB::table('energy_records')->whereRaw('actual_kwh < expected_kwh * 0.8')->get() as $record) {
            $deviation = round((($record->actual_kwh - $record->expected_kwh) / $record->expected_kwh) * 100, 2);
            DB::table('alerts')->updateOrInsert(['energy_record_id' => $record->id], [
                'solar_farm_id' => $record->solar_farm_id, 'type' => 'low_generation',
                'priority' => $deviation <= -25 ? 'critical' : 'high',
                'title' => 'Produccion por debajo de lo esperado',
                'description' => 'La generacion mensual presenta una desviacion importante.',
                'period' => $record->period, 'actual_kwh' => $record->actual_kwh,
                'expected_kwh' => $record->expected_kwh, 'deviation_percent' => $deviation,
                'detected_at' => $record->period, 'status' => 'active',
                'metadata' => json_encode(['source' => 'automatic_seeder']),
                'updated_at' => now(), 'created_at' => now(),
            ]);
        }
    }
}
