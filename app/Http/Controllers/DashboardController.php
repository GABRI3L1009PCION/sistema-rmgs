<?php

namespace App\Http\Controllers;

use App\Models\Alert;
use App\Models\Department;
use App\Models\EnergyRecord;
use App\Models\SolarFarm;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $farms = SolarFarm::with(['department', 'farmPanels.panelModel', 'energyRecords'])->get();
        $records = EnergyRecord::with('solarFarm.department')->get();
        $alerts = Alert::with('solarFarm.department')->where('status', 'active')->latest()->get();
        $departments = Department::with('solarFarms.energyRecords', 'solarFarms.farmPanels.panelModel')->get();
        $availablePeriods = $this->availablePeriods($records);
        $requestedPeriod = $request->query('period');
        $selectedPeriod = $availablePeriods->contains($requestedPeriod)
            ? $requestedPeriod
            : $availablePeriods->first();
        $periodRecords = $this->periodRecords($records, $selectedPeriod);
        $previousPeriod = $this->previousPeriod($availablePeriods, $selectedPeriod);
        $previousRecords = $this->periodRecords($records, $previousPeriod);
        $periodStats = $this->nationalStats($farms, $periodRecords);

        return view('dashboard', [
            'stats' => $periodStats,
            'farms' => $farms,
            'featuredFarms' => $this->topFarmsForPeriod($farms, $periodRecords),
            'records' => $records->sortByDesc('period'),
            'alerts' => $this->periodAlerts($alerts, $selectedPeriod),
            'departmentReports' => $this->departmentReports($departments),
            'generationSeries' => $this->generationSeries($records, $selectedPeriod),
            'mapFarms' => $this->mapFarms($farms),
            'availablePeriods' => $availablePeriods->map(fn (string $period) => [
                'value' => $period,
                'label' => Carbon::createFromFormat('Y-m', $period)->translatedFormat('F Y'),
            ]),
            'selectedPeriod' => $selectedPeriod,
            'trends' => [
                'co2' => $this->percentageChange(
                    $periodStats['co2_tons'],
                    $this->nationalStats($farms, $previousRecords)['co2_tons']
                ),
            ],
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

    public function periodStats(Request $request)
    {
        $farms = SolarFarm::with(['department', 'farmPanels.panelModel'])->get();
        $records = EnergyRecord::with('solarFarm.department')->get();
        $availablePeriods = $this->availablePeriods($records);
        $requestedPeriod = $request->query('period');
        $selectedPeriod = $availablePeriods->contains($requestedPeriod)
            ? $requestedPeriod
            : $availablePeriods->first();
        $periodRecords = $this->periodRecords($records, $selectedPeriod);
        $stats = $this->nationalStats($farms, $periodRecords);
        $previousPeriod = $this->previousPeriod($availablePeriods, $selectedPeriod);
        $previousStats = $this->nationalStats($farms, $this->periodRecords($records, $previousPeriod));
        $dailyAverage = $stats['actual_kwh'] > 0 ? round($stats['actual_kwh'] / 30) : 0;
        $alerts = Alert::with('solarFarm.department')->where('status', 'active')->latest()->get();

        return response()->json([
            'selected_period' => $selectedPeriod,
            'stats' => [
                'actual_kwh' => $stats['actual_kwh'],
                'co2_tons' => $stats['co2_tons'],
                'daily_average' => $dailyAverage,
                'homes_equivalent' => round($stats['actual_kwh'] / 40),
                'trees_equivalent' => round($stats['co2_tons'] * 34.53),
            ],
            'trends' => [
                'co2' => $this->percentageChange($stats['co2_tons'], $previousStats['co2_tons']),
            ],
            'generation_series' => $this->generationSeries($records, $selectedPeriod),
            'alerts' => $this->periodAlerts($alerts, $selectedPeriod)->map(fn (Alert $alert) => [
                'farm' => $alert->solarFarm->name,
                'period' => $alert->period->translatedFormat('M Y'),
            ])->values(),
            'top_farms' => $this->topFarmsForPeriod($farms, $periodRecords)->map(fn (array $farm) => [
                'name' => $farm['name'],
                'department' => $farm['department'],
                'capacity_kw' => $farm['capacity_kw'],
                'generation_kwh' => $farm['generation_kwh'],
                'status' => $farm['status'],
                'status_label' => $farm['status_label'],
            ])->values(),
        ]);
    }

    public function reports(Request $request)
    {
        return view('reports.index', $this->reportData($request));
    }

    public function reportCsv(Request $request)
    {
        $data = $this->reportData($request);
        $records = $data['records'];

        return response()->streamDownload(function () use ($records, $data) {
            $output = fopen('php://output', 'w');
            fputcsv($output, ['Reporte RMGS', $data['reportTypeName']]);
            fputcsv($output, ['Periodo', $data['selectedPeriodLabel'], 'Departamento', $data['selectedDepartmentName'], 'Granja', $data['selectedFarmName']]);
            fputcsv($output, []);
            fputcsv($output, ['Periodo', 'Granja', 'Departamento', 'Generacion real kWh', 'Generacion esperada kWh', 'Diferencia kWh', 'Cumplimiento %', 'CO2 evitado kg']);
            foreach ($records as $record) {
                $difference = $record->actual_kwh - $record->expected_kwh;
                $compliance = $record->expected_kwh > 0 ? $record->actual_kwh / $record->expected_kwh * 100 : 0;
                fputcsv($output, [$record->period->format('Y-m'), $record->solarFarm->name, $record->solarFarm->department->name, $record->actual_kwh, $record->expected_kwh, round($difference, 2), round($compliance, 2), $record->co2_avoided_kg]);
            }
            fclose($output);
        }, 'reporte-'.$data['selectedType'].'-'.$data['selectedPeriod'].'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function reportPrint(Request $request)
    {
        return view('reports.print', $this->reportData($request));
    }

    private function reportData(Request $request): array
    {
        $farms = SolarFarm::with(['department', 'farmPanels.panelModel'])->orderBy('name')->get();
        $allRecords = EnergyRecord::with('solarFarm.department')->orderBy('period')->get();
        $periods = $allRecords->map(fn (EnergyRecord $record) => $record->period->format('Y-m'))->unique()->sortDesc()->values();
        $requestedPeriod = $request->string('period')->toString();
        $selectedPeriod = $periods->contains($requestedPeriod) ? $requestedPeriod : ($periods->first() ?? now()->format('Y-m'));
        $selectedDepartmentId = $request->integer('department_id') ?: null;
        $selectedFarmId = $request->integer('farm_id') ?: null;
        $selectedType = in_array($request->input('type'), ['generation', 'environment', 'executive'], true)
            ? $request->input('type')
            : 'generation';

        $scopeFarms = $farms
            ->when($selectedDepartmentId, fn (Collection $items) => $items->where('department_id', $selectedDepartmentId))
            ->when($selectedFarmId, fn (Collection $items) => $items->where('id', $selectedFarmId));
        $scopeFarmIds = $scopeFarms->pluck('id');
        $scopeRecords = $allRecords->whereIn('solar_farm_id', $scopeFarmIds);
        $records = $scopeRecords->filter(fn (EnergyRecord $record) => $record->period->format('Y-m') === $selectedPeriod)->values();
        $trendRecords = $scopeRecords->filter(fn (EnergyRecord $record) => $record->period->format('Y-m') <= $selectedPeriod);
        $generationSeries = collect($this->generationSeries($trendRecords))->take(-6)->toArray();

        $actual = (float) $records->sum('actual_kwh');
        $expected = (float) $records->sum('expected_kwh');
        $co2Tons = (float) $records->sum('co2_avoided_kg') / 1000;
        $days = Carbon::createFromFormat('Y-m-d', $selectedPeriod.'-01')->daysInMonth;
        $compliance = $expected > 0 ? $actual / $expected * 100 : 0;
        $reportTypeNames = [
            'generation' => 'Generacion de energia',
            'environment' => 'Impacto ambiental',
            'executive' => 'Resumen ejecutivo',
        ];
        $summary = match ($selectedType) {
            'environment' => [
                ['leaf', 'CO2 evitado', number_format($co2Tons, 2).' t'],
                ['scale', 'Factor aplicado', '0.40 kg/kWh'],
                ['landmark', 'Granjas con datos', number_format($records->pluck('solar_farm_id')->unique()->count())],
                ['bar-chart-3', 'Promedio por granja', number_format($records->count() ? $co2Tons / $records->count() : 0, 2).' t'],
            ],
            'executive' => [
                ['zap', 'Generacion real', number_format($actual).' kWh'],
                ['target', 'Generacion esperada', number_format($expected).' kWh'],
                ['gauge', 'Cumplimiento', number_format($compliance, 1).'%'],
                ['landmark', 'Granjas activas', number_format($scopeFarms->where('status', 'active')->count())],
            ],
            default => [
                ['zap', 'Generacion total', number_format($actual).' kWh'],
                ['bar-chart-3', 'Promedio diario', number_format($actual / max($days, 1)).' kWh'],
                ['trending-up', 'Maxima registrada', number_format((float) $records->max('actual_kwh')).' kWh'],
                ['trending-down', 'Minima registrada', number_format((float) $records->min('actual_kwh')).' kWh'],
            ],
        };
        $departmentSeries = $records->groupBy(fn (EnergyRecord $record) => $record->solarFarm->department->name)
            ->map(fn (Collection $items) => round($selectedType === 'environment' ? $items->sum('co2_avoided_kg') / 1000 : $items->sum('actual_kwh'), 2))
            ->sortDesc()
            ->toArray();

        return [
            'farms' => $farms,
            'departments' => Department::orderBy('name')->get(),
            'periods' => $periods,
            'records' => $records,
            'selectedPeriod' => $selectedPeriod,
            'selectedPeriodLabel' => Carbon::createFromFormat('Y-m-d', $selectedPeriod.'-01')->translatedFormat('F Y'),
            'selectedDepartmentId' => $selectedDepartmentId,
            'selectedDepartmentName' => $selectedDepartmentId ? (Department::find($selectedDepartmentId)?->name ?? 'Sin resultados') : 'Todos los departamentos',
            'selectedFarmId' => $selectedFarmId,
            'selectedFarmName' => $selectedFarmId ? ($farms->firstWhere('id', $selectedFarmId)?->name ?? 'Sin resultados') : 'Todas las granjas',
            'selectedType' => $selectedType,
            'reportTypeName' => $reportTypeNames[$selectedType],
            'summary' => $summary,
            'stats' => [
                'actual_kwh' => round($actual, 2),
                'expected_kwh' => round($expected, 2),
                'daily_average' => round($actual / max($days, 1), 2),
                'co2_tons' => round($co2Tons, 2),
                'active_farms' => $scopeFarms->where('status', 'active')->count(),
                'panels' => $scopeFarms->sum(fn (SolarFarm $farm) => $farm->installedPanelsCount()),
                'compliance' => round($compliance, 2),
            ],
            'generationSeries' => $generationSeries,
            'departmentSeries' => $departmentSeries,
        ];
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
            fputcsv($output, ['Año', 'Escenario conservador kWh', 'Escenario base kWh', 'Escenario optimista kWh']);
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
                'panels' => $farm->installedPanelsCount(),
                'capacity_kw' => $farm->installedCapacityKw(),
                'co2_tons' => round($farm->energyRecords->sum('co2_avoided_kg') / 1000, 2),
            ])->values(),
        ]);
    }

    private function nationalStats(Collection $farms, Collection $records): array
    {
        return [
            'farms' => $farms->count(),
            'panels' => $farms->sum(fn (SolarFarm $farm) => $farm->installedPanelsCount()),
            'capacity_kw' => round($farms->sum(fn (SolarFarm $farm) => $farm->installedCapacityKw()), 2),
            'actual_kwh' => round($records->sum('actual_kwh'), 2),
            'expected_kwh' => round($records->sum('expected_kwh'), 2),
            'co2_tons' => round($records->sum('co2_avoided_kg') / 1000, 2),
            'families' => $farms->sum('families_benefited'),
        ];
    }

    private function availablePeriods(Collection $records): Collection
    {
        return $records
            ->pluck('period')
            ->map(fn (Carbon $period) => $period->format('Y-m'))
            ->unique()
            ->sortDesc()
            ->values();
    }

    private function periodRecords(Collection $records, ?string $selectedPeriod): Collection
    {
        if (! $selectedPeriod) {
            return collect();
        }

        return $records->filter(fn (EnergyRecord $record) => $record->period->format('Y-m') === $selectedPeriod);
    }

    private function previousPeriod(Collection $availablePeriods, ?string $selectedPeriod): ?string
    {
        if (! $selectedPeriod) {
            return null;
        }

        return $availablePeriods
            ->filter(fn (string $period) => $period < $selectedPeriod)
            ->sortDesc()
            ->first();
    }

    private function percentageChange(float|int $current, float|int $previous): ?float
    {
        if ((float) $previous === 0.0) {
            return null;
        }

        return round((($current - $previous) / $previous) * 100, 1);
    }

    private function periodAlerts(Collection $alerts, ?string $selectedPeriod): Collection
    {
        if (! $selectedPeriod) {
            return collect();
        }

        return $alerts
            ->filter(fn (Alert $alert) => $alert->period->format('Y-m') === $selectedPeriod)
            ->take(2)
            ->values();
    }

    private function topFarmsForPeriod(Collection $farms, Collection $periodRecords): Collection
    {
        $generationByFarm = $periodRecords
            ->groupBy('solar_farm_id')
            ->map(fn (Collection $records) => round($records->sum('actual_kwh'), 2))
            ->sortDesc();
        $farmsById = $farms->keyBy('id');

        return $generationByFarm
            ->take(3)
            ->map(function (float $generation, int $farmId) use ($farmsById) {
                $farm = $farmsById->get($farmId);

                return [
                    'name' => $farm->name,
                    'department' => $farm->department->name,
                    'capacity_kw' => round($farm->installedCapacityKw(), 1),
                    'generation_kwh' => $generation,
                    'status' => $farm->status,
                    'status_label' => $farm->status === 'maintenance' ? 'Mantenimiento' : ($farm->status === 'active' ? 'Activa' : 'Inactiva'),
                ];
            })
            ->values();
    }

    private function departmentReports(Collection $departments): Collection
    {
        return $departments->map(function (Department $department) {
            $farms = $department->solarFarms;
            $records = $farms->flatMap->energyRecords;

            return [
                'department' => $department->name,
                'farms' => $farms->count(),
                'panels' => $farms->sum(fn (SolarFarm $farm) => $farm->installedPanelsCount()),
                'capacity_kw' => round($farms->sum(fn (SolarFarm $farm) => $farm->installedCapacityKw()), 2),
                'actual_kwh' => round($records->sum('actual_kwh'), 2),
                'expected_kwh' => round($records->sum('expected_kwh'), 2),
                'co2_tons' => round($records->sum('co2_avoided_kg') / 1000, 2),
                'families' => $farms->sum('families_benefited'),
            ];
        })->sortByDesc('actual_kwh');
    }

    private function generationSeries(Collection $records, ?string $selectedPeriod = null): array
    {
        $series = $records
            ->groupBy(fn (EnergyRecord $record) => $record->period->format('Y-m'))
            ->sortKeys()
            ->map(fn (Collection $items) => [
                'actual' => round($items->sum('actual_kwh'), 2),
                'expected' => round($items->sum('expected_kwh'), 2),
            ]);

        if ($selectedPeriod) {
            $series = $series
                ->filter(fn (array $item, string $period) => $period <= $selectedPeriod)
                ->take(-3);
        }

        return $series->toArray();
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
