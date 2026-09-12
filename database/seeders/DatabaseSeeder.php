<?php

namespace Database\Seeders;

use App\Models\Alert;
use App\Models\Department;
use App\Models\EnergyRecord;
use App\Models\PanelModel;
use App\Models\SolarFarm;
use App\Models\User;
use App\Services\SolarMetricsService;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@rmgs.test'],
            [
                'name' => 'Gabriel Admin',
                'password' => Hash::make('password'),
            ]
        );

        $departments = [
            ['Alta Verapaz', 15.5943, -90.1495], ['Baja Verapaz', 15.1020, -90.3147],
            ['Chimaltenango', 14.6611, -90.8194], ['Chiquimula', 14.7972, -89.5448],
            ['El Progreso', 14.8497, -90.0640], ['Escuintla', 14.3009, -90.7850],
            ['Guatemala', 14.6349, -90.5069], ['Huehuetenango', 15.3192, -91.4706],
            ['Izabal', 15.7370, -88.6050], ['Jalapa', 14.6347, -89.9886],
            ['Jutiapa', 14.2919, -89.8958], ['Peten', 16.9120, -89.8927],
            ['Quetzaltenango', 14.8347, -91.5181], ['Quiche', 15.0306, -91.1483],
            ['Retalhuleu', 14.5349, -91.6770], ['Sacatepequez', 14.5586, -90.7339],
            ['San Marcos', 14.9639, -91.7944], ['Santa Rosa', 14.2760, -90.2985],
            ['Solola', 14.7730, -91.1836], ['Suchitepequez', 14.5350, -91.5030],
            ['Totonicapan', 14.9117, -91.3611], ['Zacapa', 14.9722, -89.5306],
        ];

        foreach ($departments as [$name, $lat, $lng]) {
            Department::create([
                'name' => $name,
                'slug' => Str::slug($name),
                'latitude' => $lat,
                'longitude' => $lng,
            ]);
        }

        $panels = collect([
            ['SolarMax', 'SM-450 Mono', 0.450],
            ['HelioTech', 'HT-550 Bifacial', 0.550],
            ['Quetzal Solar', 'QS-410 Poly', 0.410],
        ])->map(fn ($panel) => PanelModel::create([
            'brand' => $panel[0],
            'model' => $panel[1],
            'nominal_power_kw' => $panel[2],
            'status' => 'active',
        ]));

        $farms = [
            ['Parque Solar Puerto Barrios', 'Izabal', 'Puerto Barrios', 15.7322, -88.5945, 820, 620, 0, [118000, 123500, 121200]],
            ['Granja Solar Motagua', 'Zacapa', 'Rio Hondo', 15.0410, -89.5852, 540, 430, 1, [84200, 86100, 69000]],
            ['Nodo Solar Central', 'Guatemala', 'Villa Nueva', 14.5269, -90.5875, 1200, 980, 1, [176500, 181200, 185900]],
            ['Altiplano Fotovoltaico', 'Quetzaltenango', 'Olintepeque', 14.8870, -91.5132, 460, 360, 2, [66500, 68800, 70400]],
            ['Costa Sur Energia Limpia', 'Escuintla', 'Santa Lucia Cotzumalguapa', 14.3350, -91.0232, 980, 790, 0, [142200, 148000, 114500]],
            ['Lago Solar Atitlan', 'Solola', 'Panajachel', 14.7400, -91.1590, 310, 240, 2, [45100, 47200, 48600]],
        ];

        $metrics = app(SolarMetricsService::class);

        foreach ($farms as [$name, $departmentName, $municipality, $lat, $lng, $families, $quantity, $panelIndex, $actuals]) {
            $farm = SolarFarm::create([
                'department_id' => Department::where('name', $departmentName)->value('id'),
                'name' => $name,
                'owner' => 'Programa Solar Guatemala',
                'municipality' => $municipality,
                'latitude' => $lat,
                'longitude' => $lng,
                'families_benefited' => $families,
                'status' => 'active',
                'notes' => 'Datos de demostracion para evaluar dashboard, mapa, alertas y proyecciones.',
            ]);

            $farm->farmPanels()->create([
                'panel_model_id' => $panels[$panelIndex]->id,
                'quantity' => $quantity,
            ]);

            foreach ($actuals as $month => $actual) {
                $expected = round($actual * ($month === 2 && in_array($departmentName, ['Zacapa', 'Escuintla']) ? 1.28 : 1.08), 2);
                $record = EnergyRecord::create([
                    'solar_farm_id' => $farm->id,
                    'period' => now()->subMonths(2 - $month)->startOfMonth(),
                    'actual_kwh' => $actual,
                    'expected_kwh' => $expected,
                    'co2_avoided_kg' => $metrics->co2AvoidedKg($actual),
                    'notes' => 'CO2 calculado con factor 0.40 kg CO2/kWh.',
                ]);

                $deviation = $record->deviationPercent();
                if ($metrics->shouldTriggerAlert($record->actual_kwh, $record->expected_kwh)) {
                    Alert::create([
                        'solar_farm_id' => $farm->id,
                        'energy_record_id' => $record->id,
                        'period' => $record->period,
                        'actual_kwh' => $record->actual_kwh,
                        'expected_kwh' => $record->expected_kwh,
                        'deviation_percent' => $deviation,
                        'status' => 'active',
                    ]);
                }
            }
        }
    }
}
