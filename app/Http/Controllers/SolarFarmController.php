<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\EnergyRecord;
use App\Models\FarmPanel;
use App\Models\PanelModel;
use App\Models\SolarFarm;
use App\Services\SolarMetricsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

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
            'municipalitiesByDepartment' => $this->municipalitiesByDepartment(),
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
                'department_id' => $farm->department_id,
                'municipality' => $farm->municipality,
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
            'municipalitiesByDepartment' => $this->municipalitiesByDepartment(),
            'municipalityCoordinates' => $this->locationDefaultsByDepartment(),
            'panelModels' => PanelModel::where('status', 'active')->orderBy('brand')->get(),
        ]);
    }

    public function show(SolarFarm $farm)
    {
        $farm->load([
            'department',
            'farmPanels.panelModel',
            'energyRecords' => fn ($query) => $query->orderByDesc('period'),
            'alerts' => fn ($query) => $query->orderByDesc('period'),
        ]);

        return view('farms.show', [
            'farm' => $farm,
            'panelModels' => PanelModel::where('status', 'active')->orderBy('brand')->get(),
            'stats' => [
                'panels' => $farm->farmPanels->sum('quantity'),
                'capacity_kw' => $farm->installedCapacityKw(),
                'actual_kwh' => $farm->energyRecords->sum('actual_kwh'),
                'expected_kwh' => $farm->energyRecords->sum('expected_kwh'),
                'co2_tons' => round($farm->energyRecords->sum('co2_avoided_kg') / 1000, 2),
                'projection' => $farm->projectedGenerationKwh(),
            ],
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
            'model' => ['required', 'string', 'max:150', Rule::unique('panel_models')->where(fn ($query) => $query->where('brand', $request->brand))],
            'nominal_power_kw' => ['required', 'numeric', 'min:0.001'],
            'technology' => ['required', 'string', 'max:100'],
            'panel_type' => ['required', 'string', 'max:100'],
            'efficiency_percent' => ['nullable', 'numeric', 'between:0.01,100'],
            'dimensions' => ['nullable', 'string', 'max:100'],
            'weight_kg' => ['nullable', 'numeric', 'min:0'],
            'warranty_years' => ['nullable', 'integer', 'between:1,100'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        PanelModel::create($validated);

        return redirect()->route('panels.index')->with('status', 'Modelo de panel registrado correctamente.');
    }

    public function editPanel(PanelModel $panel)
    {
        $panel->load('farmPanels.solarFarm');

        return view('panels.edit', [
            'panel' => $panel,
        ]);
    }

    public function updatePanel(Request $request, PanelModel $panel)
    {
        $validated = $request->validate([
            'brand' => ['required', 'string', 'max:100'],
            'model' => ['required', 'string', 'max:150', Rule::unique('panel_models')->where(fn ($query) => $query->where('brand', $request->brand))->ignore($panel->id)],
            'nominal_power_kw' => ['required', 'numeric', 'min:0.001'],
            'technology' => ['required', 'string', 'max:100'],
            'panel_type' => ['required', 'string', 'max:100'],
            'efficiency_percent' => ['nullable', 'numeric', 'between:0.01,100'],
            'dimensions' => ['nullable', 'string', 'max:100'],
            'weight_kg' => ['nullable', 'numeric', 'min:0'],
            'warranty_years' => ['nullable', 'integer', 'between:1,100'],
            'status' => ['required', 'in:active,inactive'],
            'installations' => ['sometimes', 'array'],
            'installations.*.quantity' => ['required', 'integer', 'min:1'],
        ]);

        $installations = $validated['installations'] ?? [];
        unset($validated['installations']);

        DB::transaction(function () use ($panel, $validated, $installations) {
            $panel->update($validated);

            foreach ($installations as $installationId => $data) {
                $panel->farmPanels()->whereKey($installationId)->update([
                    'quantity' => $data['quantity'],
                ]);
            }
        });

        return redirect()->route('panels.index')->with('status', 'Modelo de panel actualizado correctamente.');
    }

    public function deactivatePanel(PanelModel $panel)
    {
        $panel->update(['status' => 'inactive']);

        return redirect()->route('panels.index')->with('status', 'Modelo de panel desactivado correctamente.');
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

        $this->validateMunicipalityForDepartment((int) $validated['department_id'], $validated['municipality']);
        $validated = $this->applyDefaultCoordinates($validated);

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
            'municipalitiesByDepartment' => $this->municipalitiesByDepartment(),
            'municipalityCoordinates' => $this->locationDefaultsByDepartment(),
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

        $this->validateMunicipalityForDepartment((int) $validated['department_id'], $validated['municipality']);
        $validated = $this->applyDefaultCoordinates($validated);

        $farm->update($validated);

        return redirect()->route('farms.index')->with('status', 'Granja solar actualizada correctamente.');
    }

    public function deactivate(SolarFarm $farm)
    {
        $farm->update(['status' => 'inactive']);

        return redirect()->route('farms.index')->with('status', 'Granja solar desactivada correctamente.');
    }

    public function storeFarmPanel(Request $request, SolarFarm $farm)
    {
        $validated = $request->validate([
            'panel_model_id' => ['required', 'exists:panel_models,id'],
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $installation = FarmPanel::firstOrNew([
            'solar_farm_id' => $farm->id,
            'panel_model_id' => $validated['panel_model_id'],
        ]);
        $installation->quantity = (int) $installation->quantity + (int) $validated['quantity'];
        $installation->save();

        return redirect()->route('farms.show', $farm)->with('status', 'Paneles agregados a la granja correctamente.');
    }

    public function updateFarmPanel(Request $request, SolarFarm $farm, FarmPanel $farmPanel)
    {
        abort_unless($farmPanel->solar_farm_id === $farm->id, 404);

        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $farmPanel->update(['quantity' => $validated['quantity']]);

        return redirect()->route('farms.show', $farm)->with('status', 'Cantidad de paneles actualizada correctamente.');
    }

    public function destroyFarmPanel(SolarFarm $farm, FarmPanel $farmPanel)
    {
        abort_unless($farmPanel->solar_farm_id === $farm->id, 404);

        $farmPanel->delete();

        return redirect()->route('farms.show', $farm)->with('status', 'Paneles retirados de la granja correctamente.');
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

    private function municipalitiesByDepartment(): array
    {
        $catalog = $this->municipalityCatalog();

        return Department::with('solarFarms:id,department_id,municipality')
            ->orderBy('name')
            ->get()
            ->mapWithKeys(function (Department $department) use ($catalog) {
                $municipalities = collect($catalog[$department->name] ?? [])
                    ->merge($department->solarFarms->pluck('municipality'))
                    ->filter()
                    ->unique()
                    ->sort()
                    ->values()
                    ->all();

                return [$department->id => $municipalities];
            })
            ->all();
    }

    private function validateMunicipalityForDepartment(int $departmentId, string $municipality): void
    {
        $municipalities = collect($this->municipalitiesByDepartment()[$departmentId] ?? []);

        if ($municipalities->isNotEmpty() && ! $municipalities->contains($municipality)) {
            throw ValidationException::withMessages([
                'municipality' => 'Selecciona un municipio valido para el departamento elegido.',
            ]);
        }
    }

    private function applyDefaultCoordinates(array $validated): array
    {
        $coordinates = $this->defaultCoordinatesForMunicipality(
            (int) $validated['department_id'],
            $validated['municipality']
        );

        if ($coordinates !== null) {
            $validated['latitude'] = $coordinates['lat'];
            $validated['longitude'] = $coordinates['lng'];
        }

        return $validated;
    }

    private function defaultCoordinatesForMunicipality(int $departmentId, string $municipality): ?array
    {
        return $this->locationDefaultsByDepartment()[$departmentId][$municipality] ?? null;
    }

    private function locationDefaultsByDepartment(): array
    {
        $catalog = $this->municipalityCatalog();
        $specific = $this->specificMunicipalityCoordinates();

        return Department::orderBy('name')->get()
            ->mapWithKeys(function (Department $department) use ($catalog, $specific) {
                $municipalities = collect($catalog[$department->name] ?? []);

                $coordinates = $municipalities->mapWithKeys(function (string $municipality) use ($department, $specific) {
                    $default = $specific[$department->name][$municipality] ?? [
                        'lat' => (float) $department->latitude,
                        'lng' => (float) $department->longitude,
                    ];

                    return [$municipality => $default];
                })->all();

                return [$department->id => $coordinates];
            })
            ->all();
    }

    private function specificMunicipalityCoordinates(): array
    {
        return [
            'Escuintla' => [
                'Santa Lucia Cotzumalguapa' => ['lat' => 14.3350, 'lng' => -91.0232],
            ],
            'Guatemala' => [
                'Guatemala' => ['lat' => 14.6349, 'lng' => -90.5069],
                'Mixco' => ['lat' => 14.6333, 'lng' => -90.6064],
                'Villa Nueva' => ['lat' => 14.5269, 'lng' => -90.5875],
            ],
            'Izabal' => [
                'Puerto Barrios' => ['lat' => 15.7322, 'lng' => -88.5945],
            ],
            'Quetzaltenango' => [
                'Olintepeque' => ['lat' => 14.8870, 'lng' => -91.5132],
            ],
            'Solola' => [
                'Panajachel' => ['lat' => 14.7400, 'lng' => -91.1590],
            ],
            'Zacapa' => [
                'Rio Hondo' => ['lat' => 15.0410, 'lng' => -89.5852],
            ],
        ];
    }

    private function municipalityCatalog(): array
    {
        return [
            'Alta Verapaz' => ['Cahabon', 'Chahal', 'Chisec', 'Coban', 'Fray Bartolome de las Casas', 'Lanquin', 'Panzos', 'Raxruha', 'San Cristobal Verapaz', 'San Juan Chamelco', 'San Pedro Carcha', 'Santa Catalina La Tinta', 'Santa Cruz Verapaz', 'Senahu', 'Tactic', 'Tamahu', 'Tucuru'],
            'Baja Verapaz' => ['Cubulco', 'El Chol', 'Granados', 'Purulha', 'Rabinal', 'Salama', 'San Jeronimo', 'San Miguel Chicaj'],
            'Chimaltenango' => ['Acatenango', 'Chimaltenango', 'El Tejar', 'Parramos', 'Patzicia', 'Patzun', 'Pochuta', 'San Andres Itzapa', 'San Jose Poaquil', 'San Juan Comalapa', 'San Martin Jilotepeque', 'Santa Apolonia', 'Santa Cruz Balanya', 'Tecpan Guatemala', 'Yepocapa', 'Zaragoza'],
            'Chiquimula' => ['Camotan', 'Chiquimula', 'Concepcion Las Minas', 'Esquipulas', 'Ipala', 'Jocotan', 'Olopa', 'Quezaltepeque', 'San Jacinto', 'San Jose La Arada', 'San Juan Ermita'],
            'El Progreso' => ['El Jicaro', 'Guastatoya', 'Morazan', 'San Agustin Acasaguastlan', 'San Antonio La Paz', 'San Cristobal Acasaguastlan', 'Sanarate', 'Sansare'],
            'Escuintla' => ['Escuintla', 'Guanagazapa', 'Iztapa', 'La Democracia', 'La Gomera', 'Masagua', 'Nueva Concepcion', 'Palin', 'San Jose', 'San Vicente Pacaya', 'Santa Lucia Cotzumalguapa', 'Siquinala', 'Tiquisate'],
            'Guatemala' => ['Amatitlan', 'Chinautla', 'Chuarrancho', 'Fraijanes', 'Guatemala', 'Mixco', 'Palencia', 'San Jose del Golfo', 'San Jose Pinula', 'San Juan Sacatepequez', 'San Miguel Petapa', 'San Pedro Ayampuc', 'San Pedro Sacatepequez', 'San Raymundo', 'Santa Catarina Pinula', 'Villa Canales', 'Villa Nueva'],
            'Huehuetenango' => ['Aguacatan', 'Barillas', 'Chiantla', 'Colotenango', 'Concepcion Huista', 'Cuilco', 'Huehuetenango', 'Ixtahuacan', 'Jacaltenango', 'La Democracia', 'La Libertad', 'Malacatancito', 'Nenton', 'San Antonio Huista', 'San Gaspar Ixchil', 'San Juan Atitan', 'San Juan Ixcoy', 'San Mateo Ixtatan', 'San Miguel Acatan', 'San Pedro Necta', 'San Rafael La Independencia', 'San Rafael Petzal', 'San Sebastian Coatan', 'San Sebastian Huehuetenango', 'Santa Ana Huista', 'Santa Barbara', 'Santa Eulalia', 'Santiago Chimaltenango', 'Soloma', 'Tectitan', 'Todos Santos Cuchumatan', 'Union Cantinil'],
            'Izabal' => ['El Estor', 'Livingston', 'Los Amates', 'Morales', 'Puerto Barrios'],
            'Jalapa' => ['Jalapa', 'Mataquescuintla', 'Monjas', 'San Carlos Alzatate', 'San Luis Jilotepeque', 'San Manuel Chaparron', 'San Pedro Pinula'],
            'Jutiapa' => ['Agua Blanca', 'Asuncion Mita', 'Atescatempa', 'Comapa', 'Conguaco', 'El Adelanto', 'El Progreso', 'Jalpatagua', 'Jerez', 'Jutiapa', 'Moyuta', 'Pasaco', 'Quesada', 'San Jose Acatempa', 'Santa Catarina Mita', 'Yupiltepeque', 'Zapotitlan'],
            'Peten' => ['Dolores', 'El Chal', 'Flores', 'La Libertad', 'Las Cruces', 'Melchor de Mencos', 'Poptun', 'San Andres', 'San Benito', 'San Francisco', 'San Jose', 'San Luis', 'Santa Ana', 'Sayaxche'],
            'Quetzaltenango' => ['Almolonga', 'Cabrican', 'Cajola', 'Cantel', 'Coatepeque', 'Colomba', 'Concepcion Chiquirichapa', 'El Palmar', 'Flores Costa Cuca', 'Genova', 'Huitan', 'La Esperanza', 'Olintepeque', 'Palestina de Los Altos', 'Quetzaltenango', 'Salcaja', 'San Carlos Sija', 'San Francisco La Union', 'San Juan Ostuncalco', 'San Martin Sacatepequez', 'San Mateo', 'San Miguel Siguila', 'Sibilia', 'Zunil'],
            'Quiche' => ['Canilla', 'Chajul', 'Chicaman', 'Chiche', 'Chichicastenango', 'Chinique', 'Cunen', 'Ixcan', 'Joyabaj', 'Nebaj', 'Pachalum', 'Patzite', 'Sacapulas', 'San Andres Sajcabaja', 'San Antonio Ilotenango', 'San Bartolome Jocotenango', 'San Juan Cotzal', 'San Pedro Jocopilas', 'Santa Cruz del Quiche', 'Uspantan', 'Zacualpa'],
            'Retalhuleu' => ['Champerico', 'El Asintal', 'Nuevo San Carlos', 'Retalhuleu', 'San Andres Villa Seca', 'San Felipe', 'San Martin Zapotitlan', 'San Sebastian', 'Santa Cruz Mulua'],
            'Sacatepequez' => ['Alotenango', 'Antigua Guatemala', 'Ciudad Vieja', 'Jocotenango', 'Magdalena Milpas Altas', 'Pastores', 'San Antonio Aguas Calientes', 'San Bartolome Milpas Altas', 'San Lucas Sacatepequez', 'San Miguel Duenas', 'Santa Catarina Barahona', 'Santa Lucia Milpas Altas', 'Santa Maria de Jesus', 'Santiago Sacatepequez', 'Santo Domingo Xenacoj', 'Sumpango'],
            'San Marcos' => ['Ayutla', 'Catarina', 'Comitancillo', 'Concepcion Tutuapa', 'El Quetzal', 'El Rodeo', 'El Tumbador', 'Esquipulas Palo Gordo', 'Ixchiguan', 'La Blanca', 'La Reforma', 'Malacatan', 'Nuevo Progreso', 'Ocos', 'Pajapita', 'Rio Blanco', 'San Antonio Sacatepequez', 'San Cristobal Cucho', 'San Jose Ojetenam', 'San Lorenzo', 'San Marcos', 'San Miguel Ixtahuacan', 'San Pablo', 'San Pedro Sacatepequez', 'San Rafael Pie de la Cuesta', 'Sibinal', 'Sipacapa', 'Tacana', 'Tajumulco', 'Tejutla'],
            'Santa Rosa' => ['Barberena', 'Casillas', 'Chiquimulilla', 'Cuilapa', 'Guazacapan', 'Nueva Santa Rosa', 'Oratorio', 'Pueblo Nuevo Vinas', 'San Juan Tecuaco', 'San Rafael Las Flores', 'Santa Cruz Naranjo', 'Santa Maria Ixhuatan', 'Santa Rosa de Lima', 'Taxisco'],
            'Solola' => ['Concepcion', 'Nahuala', 'Panajachel', 'San Andres Semetabaj', 'San Antonio Palopo', 'San Jose Chacaya', 'San Juan La Laguna', 'San Lucas Toliman', 'San Marcos La Laguna', 'San Pablo La Laguna', 'San Pedro La Laguna', 'Santa Catarina Ixtahuacan', 'Santa Catarina Palopo', 'Santa Clara La Laguna', 'Santa Cruz La Laguna', 'Santa Lucia Utatlan', 'Santa Maria Visitacion', 'Santiago Atitlan', 'Solola'],
            'Suchitepequez' => ['Chicacao', 'Cuyotenango', 'Mazatenango', 'Patulul', 'Pueblo Nuevo', 'Rio Bravo', 'Samayac', 'San Antonio Suchitepequez', 'San Bernardino', 'San Francisco Zapotitlan', 'San Gabriel', 'San Jose El Idolo', 'San Juan Bautista', 'San Lorenzo', 'San Miguel Panan', 'San Pablo Jocopilas', 'Santa Barbara', 'Santo Domingo Suchitepequez', 'Santo Tomas La Union', 'Zunilito'],
            'Totonicapan' => ['Momostenango', 'San Andres Xecul', 'San Bartolo', 'San Cristobal Totonicapan', 'San Francisco El Alto', 'Santa Lucia La Reforma', 'Santa Maria Chiquimula', 'Totonicapan'],
            'Zacapa' => ['Cabanas', 'Estanzuela', 'Gualan', 'Huite', 'La Union', 'Rio Hondo', 'San Diego', 'Teculutan', 'Usumatlan', 'Zacapa'],
        ];
    }
}
