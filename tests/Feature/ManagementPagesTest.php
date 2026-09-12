<?php

namespace Tests\Feature;

use App\Models\PanelModel;
use App\Models\Department;
use App\Models\EnergyRecord;
use App\Models\SolarFarm;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ManagementPagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
        $this->actingAs(User::firstOrFail());
    }

    public function test_farm_detail_page_loads(): void
    {
        $farm = SolarFarm::firstOrFail();

        $this->get(route('farms.show', $farm))
            ->assertOk()
            ->assertSee($farm->name)
            ->assertSee('Paneles instalados');
    }

    public function test_farms_index_shows_create_access(): void
    {
        $this->get(route('farms.index'))
            ->assertOk()
            ->assertSee('Nueva granja')
            ->assertSee(route('farms.create'), false);
    }

    public function test_farm_create_page_shows_expected_generation_fields(): void
    {
        $this->get(route('farms.create'))
            ->assertOk()
            ->assertSee('Capacidad estimada')
            ->assertSee('Generacion esperada kWh')
            ->assertSee('data-expected-kwh', false);
    }

    public function test_panel_edit_page_loads(): void
    {
        $panel = PanelModel::firstOrFail();

        $this->get(route('panels.edit', $panel))
            ->assertOk()
            ->assertSee('Editar modelo de panel')
            ->assertSee($panel->model);
    }

    public function test_dashboard_period_filter_updates_month_summary(): void
    {
        $month = Carbon::parse(EnergyRecord::query()->min('period'))->startOfMonth();
        $period = $month->format('Y-m');
        $expectedKwh = EnergyRecord::whereBetween('period', [$month, $month->copy()->endOfMonth()])->sum('actual_kwh');

        $this->get(route('dashboard', ['period' => $period]))
            ->assertOk()
            ->assertSee(number_format($expectedKwh).' kWh')
            ->assertSee('selected', false);
    }

    public function test_dashboard_period_endpoint_returns_month_data(): void
    {
        $month = Carbon::parse(EnergyRecord::query()->min('period'))->startOfMonth();
        $period = $month->format('Y-m');
        $expectedKwh = EnergyRecord::whereBetween('period', [$month, $month->copy()->endOfMonth()])->sum('actual_kwh');

        $response = $this->getJson(route('dashboard.period', ['period' => $period]))
            ->assertOk()
            ->assertJsonPath('selected_period', $period)
            ->assertJsonStructure([
                'stats' => ['actual_kwh', 'co2_tons', 'daily_average', 'homes_equivalent', 'trees_equivalent'],
                'trends' => ['co2'],
                'generation_series',
                'alerts',
                'top_farms',
            ]);

        $this->assertEquals((float) $expectedKwh, (float) $response->json('stats.actual_kwh'));
        $this->assertNotEmpty($response->json('top_farms'));
    }

    public function test_generation_page_exposes_records_for_live_filters(): void
    {
        $record = EnergyRecord::with('solarFarm.department')->firstOrFail();

        $this->get(route('records.index'))
            ->assertOk()
            ->assertDontSee('Nueva lectura')
            ->assertDontSee('records.create', false)
            ->assertSee('generationRecords', false)
            ->assertSee('"id":'.$record->id, false)
            ->assertSee('data-record-id="'.$record->id.'"', false)
            ->assertSee('generation-total', false)
            ->assertSee('generation-farm-filter', false);
    }

    public function test_generation_manual_record_routes_are_not_available(): void
    {
        $this->get('/generacion/nueva')->assertNotFound();
    }

    public function test_farm_update_rejects_municipality_from_other_department(): void
    {
        $farm = SolarFarm::firstOrFail();
        $department = Department::where('name', 'Guatemala')->firstOrFail();

        $this->put(route('farms.update', $farm), [
            'department_id' => $department->id,
            'name' => $farm->name,
            'owner' => $farm->owner,
            'municipality' => 'Puerto Barrios',
            'latitude' => $farm->latitude,
            'longitude' => $farm->longitude,
            'families_benefited' => $farm->families_benefited,
            'status' => $farm->status,
            'notes' => $farm->notes,
        ])->assertSessionHasErrors('municipality');
    }

    public function test_farm_update_snaps_coordinates_to_selected_municipality(): void
    {
        $farm = SolarFarm::where('name', 'Parque Solar Puerto Barrios')->firstOrFail();
        $department = Department::where('name', 'Guatemala')->firstOrFail();

        $this->put(route('farms.update', $farm), [
            'department_id' => $department->id,
            'name' => $farm->name,
            'owner' => $farm->owner,
            'municipality' => 'Mixco',
            'latitude' => $farm->latitude,
            'longitude' => $farm->longitude,
            'families_benefited' => $farm->families_benefited,
            'status' => $farm->status,
            'notes' => $farm->notes,
        ])->assertRedirect(route('farms.index'));

        $farm->refresh();

        $this->assertSame($department->id, $farm->department_id);
        $this->assertSame('Mixco', $farm->municipality);
        $this->assertEqualsWithDelta(14.6333, (float) $farm->latitude, 0.0001);
        $this->assertEqualsWithDelta(-90.6064, (float) $farm->longitude, 0.0001);
    }

    public function test_panel_rows_have_unique_installation_selection_keys(): void
    {
        $panel = PanelModel::where('brand', 'HelioTech')->with('farmPanels')->firstOrFail();
        $response = $this->get(route('panels.index'));

        $response->assertOk();
        foreach ($panel->farmPanels as $installation) {
            $response->assertSee('data-row-key="installation-'.$installation->id.'"', false);
        }
    }

    public function test_panel_technical_data_and_installation_quantities_can_be_updated(): void
    {
        $panel = PanelModel::where('brand', 'HelioTech')->with('farmPanels')->firstOrFail();
        $installation = $panel->farmPanels->firstOrFail();

        $this->put(route('panels.update', $panel), [
            'brand' => $panel->brand,
            'model' => $panel->model,
            'nominal_power_kw' => 0.555,
            'technology' => 'Monocristalino bifacial N-Type',
            'panel_type' => 'Modulo fotovoltaico bifacial',
            'efficiency_percent' => 22.15,
            'dimensions' => '2278 x 1134 x 35 mm',
            'weight_kg' => 32.10,
            'warranty_years' => 30,
            'status' => 'active',
            'installations' => [
                $installation->id => ['quantity' => 475],
            ],
        ])->assertRedirect(route('panels.index'));

        $this->assertDatabaseHas('panel_models', [
            'id' => $panel->id,
            'technology' => 'Monocristalino bifacial N-Type',
            'efficiency_percent' => 22.15,
            'warranty_years' => 30,
        ]);
        $this->assertDatabaseHas('farm_panels', [
            'id' => $installation->id,
            'quantity' => 475,
        ]);
    }

    public function test_reports_apply_filters_and_report_type(): void
    {
        $farm = SolarFarm::has('energyRecords')->with(['department', 'energyRecords'])->firstOrFail();
        $period = $farm->energyRecords->first()->period->format('Y-m');

        $this->get(route('reports.index', [
            'period' => $period,
            'department_id' => $farm->department_id,
            'farm_id' => $farm->id,
            'type' => 'environment',
        ]))
            ->assertOk()
            ->assertViewHas('selectedType', 'environment')
            ->assertViewHas('records', fn ($records) => $records->isNotEmpty() && $records->every(fn ($record) => $record->solar_farm_id === $farm->id))
            ->assertSee('Impacto ambiental')
            ->assertSee($farm->name);
    }

    public function test_filtered_report_can_be_exported_to_csv(): void
    {
        $farm = SolarFarm::has('energyRecords')->with('energyRecords')->firstOrFail();
        $period = $farm->energyRecords->first()->period->format('Y-m');

        $this->get(route('reports.csv', [
            'period' => $period,
            'farm_id' => $farm->id,
            'type' => 'executive',
        ]))
            ->assertOk()
            ->assertDownload('reporte-executive-'.$period.'.csv');
    }
}
