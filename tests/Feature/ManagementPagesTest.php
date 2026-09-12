<?php

namespace Tests\Feature;

use App\Models\PanelModel;
use App\Models\SolarFarm;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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

    public function test_panel_edit_page_loads(): void
    {
        $panel = PanelModel::firstOrFail();

        $this->get(route('panels.edit', $panel))
            ->assertOk()
            ->assertSee('Editar modelo de panel')
            ->assertSee($panel->model);
    }
}
