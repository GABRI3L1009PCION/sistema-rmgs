<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\EnergyRecord;
use App\Models\PanelModel;
use App\Models\SolarFarm;
use App\Services\SolarMetricsService;
use Illuminate\Http\Request;

class SolarFarmController extends Controller
{
    public function index()
    {
        $farms = SolarFarm::with(['department', 'farmPanels.panelModel', 'energyRecords'])
            ->orderBy('name')
            ->get();

        return view('farms.index', [
            'farms' => $farms,
            'departments' => Department::orderBy('name')->get(),
            'stats' => [
                'farms' => $farms->count(),
                'panels' => $farms->sum(fn (SolarFarm $farm) => $farm->farmPanels->sum('quantity')),
                'capacity_kw' => round($farms->sum(fn (SolarFarm $farm) => $farm->installedCapacityKw()), 2),
                'co2_tons' => round($farms->flatMap->energyRecords->sum('co2_avoided_kg') / 1000, 2),
            ],
            'mapFarms' => $farms->map(fn (SolarFarm $farm) => [
                'id' => $farm->id,
                'name' => $farm->name,
                'department' => $farm->department->name,
                'lat' => (float) $farm->latitude,
                'lng' => (float) $farm->longitude,
                'status' => $farm->status,
                'capacity_kw' => $farm->installedCapacityKw(),
            ])->values(),
        ]);
    }

    public function create()
    {
        return view('farms.create', [
            'departments' => Department::orderBy('name')->get(),
            'panelModels' => PanelModel::where('status', 'active')->orderBy('brand')->get(),
        ]);
    }

    public function panels()
    {
        $panelModels = PanelModel::with('farmPanels.solarFarm.department')
            ->orderByDesc('status')
            ->orderBy('brand')
            ->get();
        $installations = $panelModels->flatMap(fn (PanelModel $panel) => $panel->farmPanels);

        return view('panels.index', [
            'panelModels' => $panelModels,
            'farms' => SolarFarm::orderBy('name')->get(),
            'stats' => [
                'models' => $panelModels->count(),
                'panels' => $installations->sum('quantity'),
                'capacity_kw' => round($installations->sum(
                    fn ($installation) => $installation->quantity * $installation->panelModel->nominal_power_kw
                ), 2),
                'farms' => $installations->pluck('solar_farm_id')->unique()->count(),
            ],
        ]);
    }

    public function createPanel()
    {
        return view('panels.create');
    }

    public function storePanel(Request $request)
    {
        $validated = $request->validate([
            'brand' => ['required', 'string', 'max:100'],
            'model' => ['required', 'string', 'max:150'],
            'nominal_power_kw' => ['required', 'numeric', 'min:0.001'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        PanelModel::create($validated);

        return redirect()->route('panels.index')->with('status', 'Modelo de panel registrado correctamente.');
    }

    public function generation()
    {
        $records = EnergyRecord::with('solarFarm.department')->orderByDesc('period')->get();
        $farms = SolarFarm::with('department')->orderBy('name')->get();
        $latestPeriod = $records->max('period');
        $currentRecords = $latestPeriod
            ? $records->filter(fn (EnergyRecord $record) => $record->period->format('Y-m') === $latestPeriod->format('Y-m'))
            : collect();
        $actual = (float) $currentRecords->sum('actual_kwh');
        $expected = (float) $currentRecords->sum('expected_kwh');

        return view('records.index', [
            'records' => $records,
            'farms' => $farms,
            'departments' => Department::orderBy('name')->get(),
            'latestPeriod' => $latestPeriod,
            'stats' => [
                'actual_kwh' => round($actual, 2),
                'daily_average' => round($actual / 30, 2),
                'compliance' => $expected > 0 ? round(($actual / $expected) * 100, 1) : 0,
                'co2_tons' => round($currentRecords->sum('co2_avoided_kg') / 1000, 2),
            ],
            'generationSeries' => $records->groupBy(fn (EnergyRecord $record) => $record->period->format('Y-m'))
                ->sortKeys()->map(fn ($items) => [
                    'actual' => round($items->sum('actual_kwh'), 2),
                    'expected' => round($items->sum('expected_kwh'), 2),
                ])->toArray(),
            'farmSeries' => $currentRecords->groupBy('solar_farm_id')->map(fn ($items) => [
                'name' => $items->first()->solarFarm->name,
                'actual' => round($items->sum('actual_kwh'), 2),
            ])->values(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'department_id' => ['required', 'exists:departments,id'],
            'name' => ['required', 'string', 'max:255'],
            'owner' => ['nullable', 'string', 'max:255'],
            'municipality' => ['required', 'string', 'max:255'],
            'latitude' => ['required', 'numeric', 'between:13,18'],
            'longitude' => ['required', 'numeric', 'between:-93,-88'],
            'families_benefited' => ['required', 'integer', 'min:0'],
            'panel_model_id' => ['required', 'exists:panel_models,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'expected_kwh' => ['required', 'numeric', 'min:1'],
            'actual_kwh' => ['required', 'numeric', 'min:0'],
        ]);

        $farm = SolarFarm::create([
            'department_id' => $validated['department_id'],
            'name' => $validated['name'],
            'owner' => $validated['owner'] ?? null,
            'municipality' => $validated['municipality'],
            'latitude' => $validated['latitude'],
            'longitude' => $validated['longitude'],
            'families_benefited' => $validated['families_benefited'],
            'status' => 'active',
        ]);

        $farm->farmPanels()->create([
            'panel_model_id' => $validated['panel_model_id'],
            'quantity' => $validated['quantity'],
        ]);

        $record = $farm->energyRecords()->create([
            'period' => now()->startOfMonth(),
            'actual_kwh' => $validated['actual_kwh'],
            'expected_kwh' => $validated['expected_kwh'],
            'co2_avoided_kg' => $this->calculateCo2Avoided($validated['actual_kwh']),
            'notes' => 'Registro inicial desde formulario rapido.',
        ]);

        $this->createAlertWhenNeeded($farm, $record);

        return redirect()->route('farms.index')->with('status', 'Granja solar registrada correctamente.');
    }

    public function edit(SolarFarm $farm)
    {
        return view('farms.edit', [
            'farm' => $farm,
            'departments' => Department::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, SolarFarm $farm)
    {
        $validated = $request->validate([
            'department_id' => ['required', 'exists:departments,id'],
            'name' => ['required', 'string', 'max:255'],
            'owner' => ['nullable', 'string', 'max:255'],
            'municipality' => ['required', 'string', 'max:255'],
            'latitude' => ['required', 'numeric', 'between:13,18'],
            'longitude' => ['required', 'numeric', 'between:-93,-88'],
            'families_benefited' => ['required', 'integer', 'min:0'],
            'status' => ['required', 'in:active,inactive,maintenance'],
            'notes' => ['nullable', 'string'],
        ]);

        $farm->update($validated);

        return redirect()->route('farms.index')->with('status', 'Granja solar actualizada correctamente.');
    }

    public function deactivate(SolarFarm $farm)
    {
        $farm->update(['status' => 'inactive']);

        return redirect()->route('farms.index')->with('status', 'Granja solar desactivada correctamente.');
    }

    public function createRecord()
    {
        return view('records.create', [
            'farms' => SolarFarm::with('department')->orderBy('name')->get(),
        ]);
    }

    public function storeRecord(Request $request)
    {
        $validated = $request->validate([
            'solar_farm_id' => ['required', 'exists:solar_farms,id'],
            'period' => ['required', 'date'],
            'expected_kwh' => ['required', 'numeric', 'min:1'],
            'actual_kwh' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
        ]);

        $farm = SolarFarm::findOrFail($validated['solar_farm_id']);
        $record = $farm->energyRecords()->create([
            'period' => $validated['period'],
            'actual_kwh' => $validated['actual_kwh'],
            'expected_kwh' => $validated['expected_kwh'],
            'co2_avoided_kg' => $this->calculateCo2Avoided($validated['actual_kwh']),
            'notes' => $validated['notes'] ?? null,
        ]);

        $this->createAlertWhenNeeded($farm, $record);

        return redirect()->route('records.index')->with('status', 'Registro historico de generacion guardado correctamente.');
    }

    public function editRecord(EnergyRecord $record)
    {
        return view('records.edit', [
            'record' => $record->load('solarFarm.department'),
            'farms' => SolarFarm::with('department')->orderBy('name')->get(),
        ]);
    }

    public function updateRecord(Request $request, EnergyRecord $record)
    {
        $validated = $request->validate([
            'solar_farm_id' => ['required', 'exists:solar_farms,id'],
            'period' => ['required', 'date'],
            'expected_kwh' => ['required', 'numeric', 'min:1'],
            'actual_kwh' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
        ]);

        $record->alerts()->delete();
        $record->update([
            'solar_farm_id' => $validated['solar_farm_id'],
            'period' => $validated['period'],
            'actual_kwh' => $validated['actual_kwh'],
            'expected_kwh' => $validated['expected_kwh'],
            'co2_avoided_kg' => $this->calculateCo2Avoided($validated['actual_kwh']),
            'notes' => $validated['notes'] ?? null,
        ]);

        $record->refresh();
        $this->createAlertWhenNeeded($record->solarFarm, $record);

        return redirect()->route('records.index')->with('status', 'Registro historico actualizado correctamente.');
    }

    public function destroyRecord(EnergyRecord $record)
    {
        $record->alerts()->delete();
        $record->delete();

        return redirect()->route('records.index')->with('status', 'Registro historico eliminado correctamente.');
    }

    private function calculateCo2Avoided(float|int|string $actualKwh): float
    {
        return app(SolarMetricsService::class)->co2AvoidedKg((float) $actualKwh);
    }

    private function createAlertWhenNeeded(SolarFarm $farm, EnergyRecord $record): void
    {
        $deviation = $record->deviationPercent();

        if (! app(SolarMetricsService::class)->shouldTriggerAlert($record->actual_kwh, $record->expected_kwh)) {
            return;
        }

        $farm->alerts()->create([
            'energy_record_id' => $record->id,
            'period' => $record->period,
            'actual_kwh' => $record->actual_kwh,
            'expected_kwh' => $record->expected_kwh,
            'deviation_percent' => $deviation,
            'status' => 'active',
        ]);
    }
}
