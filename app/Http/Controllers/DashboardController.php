<?php

namespace App\Http\Controllers;

use App\Models\Alert;
use App\Models\Department;
use App\Models\EnergyRecord;
use App\Models\SolarFarm;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class DashboardController extends Controller
{
    public function index()
    {
        $farms = SolarFarm::with(['department', 'farmPanels.panelModel', 'energyRecords'])->get();
        $records = EnergyRecord::with('solarFarm.department')->get();
        $alerts = Alert::with('solarFarm.department')->where('status', 'active')->latest()->get();
        $departments = Department::with('solarFarms.energyRecords', 'solarFarms.farmPanels.panelModel')->get();

        return view('dashboard', [
            'stats' => $this->nationalStats($farms, $records),
            'farms' => $farms,
            'records' => $records->sortByDesc('period'),
            'alerts' => $alerts,
            'departmentReports' => $this->departmentReports($departments),
            'generationSeries' => $this->generationSeries($records),
            'mapFarms' => $this->mapFarms($farms),
        ]);
    }

    public function apiStats()
    {
        $farms = SolarFarm::with(['department', 'farmPanels.panelModel', 'energyRecords'])->get();
        $records = EnergyRecord::with('solarFarm.department')->get();

        return response()->json([
            'national' => $this->nationalStats($farms, $records),
            'departments' => $this->departmentReports(
                Department::with('solarFarms.energyRecords', 'solarFarms.farmPanels.panelModel')->get()
            )->values(),
            'alerts' => Alert::with('solarFarm.department')->where('status', 'active')->get(),
        ]);
    }

    public function reports()
    {
        $farms = SolarFarm::with(['department', 'farmPanels.panelModel', 'energyRecords'])->get();
        $records = EnergyRecord::with('solarFarm.department')->orderByDesc('period')->get();
        $latestPeriod = $records->max('period');
        $currentRecords = $latestPeriod
            ? $records->filter(fn (EnergyRecord $record) => $record->period->format('Y-m') === $latestPeriod->format('Y-m'))
            : collect();

        return view('reports.index', [
            'farms' => $farms->sortBy('name'),
            'departments' => Department::orderBy('name')->get(),
            'latestPeriod' => $latestPeriod,
            'stats' => [
                'actual_kwh' => round($currentRecords->sum('actual_kwh'), 2),
                'daily_average' => round($currentRecords->sum('actual_kwh') / 30, 2),
                'co2_tons' => round($currentRecords->sum('co2_avoided_kg') / 1000, 2),
                'active_farms' => $farms->where('status', 'active')->count(),
                'panels' => $farms->sum(fn (SolarFarm $farm) => $farm->farmPanels->sum('quantity')),
            ],
            'generationSeries' => $this->generationSeries($records),
            'departmentSeries' => $currentRecords->groupBy(fn (EnergyRecord $record) => $record->solarFarm->department->name)
                ->map(fn ($items) => round($items->sum('actual_kwh'), 2))->sortDesc()->toArray(),
        ]);
    }

    public function reportCsv()
    {
        $records = EnergyRecord::with('solarFarm.department')->orderByDesc('period')->get();

        return response()->streamDownload(function () use ($records) {
            $output = fopen('php://output', 'w');
            fputcsv($output, ['Periodo', 'Granja', 'Departamento', 'Generacion real kWh', 'Generacion esperada kWh', 'CO2 evitado kg']);
            foreach ($records as $record) {
                fputcsv($output, [$record->period->format('Y-m'), $record->solarFarm->name, $record->solarFarm->department->name, $record->actual_kwh, $record->expected_kwh, $record->co2_avoided_kg]);
            }
            fclose($output);
        }, 'reporte-generacion-rmgs.csv', ['Content-Type' => 'text/csv']);
    }

    public function reportPrint()
    {
        return view('reports.print', [
            'records' => EnergyRecord::with('solarFarm.department')->orderByDesc('period')->get(),
        ]);
    }

    public function alerts()
    {
        $alerts = Alert::with('solarFarm.department')->orderByDesc('period')->get();
        $activeAlerts = $alerts->where('status', 'active');

        return view('alerts.index', [
            'alerts' => $alerts,
            'departments' => Department::orderBy('name')->get(),
            'stats' => [
                'high' => $activeAlerts->where('deviation_percent', '>=', 25)->count(),
                'medium' => $activeAlerts->where('deviation_percent', '>=', 20)->where('deviation_percent', '<', 25)->count(),
                'low' => $activeAlerts->where('deviation_percent', '<', 20)->count(),
                'resolved' => $alerts->where('status', 'resolved')->count(),
            ],
        ]);
    }

    public function updateAlertStatus(Request $request, Alert $alert)
    {
        $validated = $request->validate(['status' => ['required', 'in:active,resolved']]);
        $alert->update($validated);

        return redirect()->route('alerts.index')->with('status', 'Estado de la alerta actualizado.');
    }

    public function projections()
    {
        $farms = SolarFarm::with(['farmPanels.panelModel', 'energyRecords'])->get();
        $records = EnergyRecord::all();
        $startYear = (int) ($records->max('period')?->format('Y') ?? now()->year);
        $baseGeneration = max((float) $records->sum('actual_kwh'), 1);
        $currentCapacity = $farms->sum(fn (SolarFarm $farm) => $farm->installedCapacityKw());
        $projectionRows = collect(range($startYear, $startYear + 4))->map(function (int $year, int $index) use ($baseGeneration) {
            return [
                'year' => $year,
                'conservative' => round($baseGeneration * (1.03 ** $index), 2),
                'base' => round($baseGeneration * (1.08 ** $index), 2),
                'optimistic' => round($baseGeneration * (1.12 ** $index), 2),
            ];
        });

        return view('projections.index', [
            'startYear' => $startYear,
            'endYear' => $startYear + 4,
            'historicalGeneration' => $baseGeneration,
            'projectionRows' => $projectionRows,
            'stats' => [
                'projected_generation' => $projectionRows->last()['base'],
                'projected_capacity' => round($currentCapacity * (1.08 ** 4), 1),
                'projected_farms' => $farms->count() + 6,
                'accumulated_co2' => round($projectionRows->sum('base') * 0.40 / 1000, 1),
            ],
        ]);
    }

    public function projectionCsv()
    {
        $records = EnergyRecord::all();
        $startYear = (int) ($records->max('period')?->format('Y') ?? now()->year);
        $baseGeneration = max((float) $records->sum('actual_kwh'), 1);

        return response()->streamDownload(function () use ($startYear, $baseGeneration) {
            $output = fopen('php://output', 'w');
            fputcsv($output, ['Ano', 'Escenario conservador kWh', 'Escenario base kWh', 'Escenario optimista kWh']);
            foreach (range(0, 4) as $index) {
                fputcsv($output, [$startYear + $index, round($baseGeneration * (1.03 ** $index), 2), round($baseGeneration * (1.08 ** $index), 2), round($baseGeneration * (1.12 ** $index), 2)]);
            }
            fclose($output);
        }, 'proyecciones-rmgs.csv', ['Content-Type' => 'text/csv']);
    }

    public function map()
    {
        $farms = SolarFarm::with(['department', 'farmPanels.panelModel', 'energyRecords'])->orderBy('name')->get();
        $records = $farms->flatMap->energyRecords;

        return view('map.index', [
            'farms' => $farms,
            'departments' => Department::orderBy('name')->get(),
            'stats' => $this->nationalStats($farms, $records),
            'mapFarms' => $farms->map(fn (SolarFarm $farm) => [
                'id' => $farm->id,
                'name' => $farm->name,
                'department' => $farm->department->name,
                'municipality' => $farm->municipality,
                'lat' => (float) $farm->latitude,
                'lng' => (float) $farm->longitude,
                'status' => $farm->status,
                'panels' => $farm->farmPanels->sum('quantity'),
                'capacity_kw' => $farm->installedCapacityKw(),
                'co2_tons' => round($farm->energyRecords->sum('co2_avoided_kg') / 1000, 2),
            ])->values(),
        ]);
    }

    public function settings(Request $request)
    {
        return view('settings.index', ['settings' => array_merge([
            'organization' => 'RMGS - Registro y Monitoreo de Generacion Solar Guatemala',
            'email' => 'info@rmguatemala.gob.gt', 'phone' => '+502 2310-0000',
            'address' => 'Ciudad de Guatemala, Guatemala', 'low_generation' => 80,
            'offline_minutes' => 30, 'panel_temperature' => 75, 'session_minutes' => 60,
            'email_notifications' => true, 'push_notifications' => true,
            'weekly_reports' => true, 'critical_alerts' => true, 'activity_log' => true,
            'theme' => 'light', 'color' => 'green',
        ], $request->session()->get('settings', []))]);
    }

    public function updateSettings(Request $request)
    {
        $settings = $request->validate([
            'organization' => ['required', 'string', 'max:255'], 'email' => ['required', 'email'],
            'phone' => ['nullable', 'string', 'max:50'], 'address' => ['nullable', 'string', 'max:255'],
            'low_generation' => ['required', 'integer', 'between:1,100'],
            'offline_minutes' => ['required', 'integer', 'min:1'], 'panel_temperature' => ['required', 'integer', 'between:1,150'],
            'session_minutes' => ['required', 'integer', 'min:5'], 'theme' => ['required', 'in:light,dark,auto'],
            'color' => ['required', 'in:green,blue,cyan,purple,orange,red'],
        ]);
        foreach (['email_notifications', 'push_notifications', 'weekly_reports', 'critical_alerts', 'activity_log'] as $key) {
            $settings[$key] = $request->boolean($key);
        }
        $request->session()->put('settings', $settings);

        return redirect()->route('settings.index')->with('status', 'Configuracion guardada correctamente.');
    }

    private function nationalStats(Collection $farms, Collection $records): array
    {
        return [
            'farms' => $farms->count(),
            'panels' => $farms->sum(fn (SolarFarm $farm) => $farm->farmPanels->sum('quantity')),
            'capacity_kw' => round($farms->sum(fn (SolarFarm $farm) => $farm->installedCapacityKw()), 2),
            'actual_kwh' => round($records->sum('actual_kwh'), 2),
            'expected_kwh' => round($records->sum('expected_kwh'), 2),
            'co2_tons' => round($records->sum('co2_avoided_kg') / 1000, 2),
            'families' => $farms->sum('families_benefited'),
        ];
    }

    private function departmentReports(Collection $departments): Collection
    {
        return $departments->map(function (Department $department) {
            $farms = $department->solarFarms;
            $records = $farms->flatMap->energyRecords;

            return [
                'department' => $department->name,
                'farms' => $farms->count(),
                'panels' => $farms->sum(fn (SolarFarm $farm) => $farm->farmPanels->sum('quantity')),
                'capacity_kw' => round($farms->sum(fn (SolarFarm $farm) => $farm->installedCapacityKw()), 2),
                'actual_kwh' => round($records->sum('actual_kwh'), 2),
                'expected_kwh' => round($records->sum('expected_kwh'), 2),
                'co2_tons' => round($records->sum('co2_avoided_kg') / 1000, 2),
                'families' => $farms->sum('families_benefited'),
            ];
        })->sortByDesc('actual_kwh');
    }

    private function generationSeries(Collection $records): array
    {
        return $records
            ->groupBy(fn (EnergyRecord $record) => $record->period->format('Y-m'))
            ->sortKeys()
            ->map(fn (Collection $items) => [
                'actual' => round($items->sum('actual_kwh'), 2),
                'expected' => round($items->sum('expected_kwh'), 2),
            ])
            ->toArray();
    }

    private function mapFarms(Collection $farms): array
    {
        return $farms->map(fn (SolarFarm $farm) => [
            'name' => $farm->name,
            'department' => $farm->department->name,
            'municipality' => $farm->municipality,
            'lat' => (float) $farm->latitude,
            'lng' => (float) $farm->longitude,
            'capacity_kw' => $farm->installedCapacityKw(),
            'families' => $farm->families_benefited,
            'projection' => $farm->projectedGenerationKwh(),
        ])->values()->toArray();
    }
}
