<?php

namespace App\Http\Controllers;

use App\Models\Alert;
use App\Models\Department;
use App\Models\EnergyRecord;
use App\Models\SolarFarm;
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
