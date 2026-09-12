<?php

namespace Tests\Unit;

use App\Models\EnergyRecord;
use App\Models\FarmPanel;
use App\Models\PanelModel;
use App\Services\SolarMetricsService;
use Carbon\Carbon;
use Tests\TestCase;

class SolarMetricsServiceTest extends TestCase
{
    private SolarMetricsService $metrics;

    protected function setUp(): void
    {
        parent::setUp();

        $this->metrics = new SolarMetricsService();
    }

    public function test_it_calculates_co2_avoided_from_actual_generation(): void
    {
        $this->assertSame(400.0, $this->metrics->co2AvoidedKg(1000));
        $this->assertSame(49.38, $this->metrics->co2AvoidedKg(123.456));
    }

    public function test_it_calculates_generation_deviation_percent(): void
    {
        $this->assertSame(25.0, $this->metrics->deviationPercent(750, 1000));
        $this->assertSame(0.0, $this->metrics->deviationPercent(1000, 0));
    }

    public function test_it_triggers_alert_when_generation_is_twenty_percent_below_expected(): void
    {
        $this->assertTrue($this->metrics->shouldTriggerAlert(800, 1000));
        $this->assertFalse($this->metrics->shouldTriggerAlert(810, 1000));
    }

    public function test_it_projects_generation_using_last_three_periods(): void
    {
        $records = collect([
            $this->record('2026-06-01', 90),
            $this->record('2026-07-01', 120),
            $this->record('2026-08-01', 150),
            $this->record('2026-09-01', 180),
        ]);

        $this->assertSame(150.0, $this->metrics->projectedGenerationKwh($records));
    }

    public function test_it_calculates_installed_capacity_from_panel_quantities(): void
    {
        $panels = collect([
            $this->farmPanel(quantity: 10, nominalPowerKw: 0.450),
            $this->farmPanel(quantity: 5, nominalPowerKw: 0.550),
        ]);

        $this->assertSame(7.25, $this->metrics->installedCapacityKw($panels));
    }

    private function record(string $period, float $actualKwh): EnergyRecord
    {
        $record = new EnergyRecord([
            'actual_kwh' => $actualKwh,
            'expected_kwh' => $actualKwh,
        ]);
        $record->period = Carbon::parse($period);

        return $record;
    }

    private function farmPanel(int $quantity, float $nominalPowerKw): FarmPanel
    {
        $farmPanel = new FarmPanel(['quantity' => $quantity]);
        $farmPanel->setRelation('panelModel', new PanelModel([
            'nominal_power_kw' => $nominalPowerKw,
        ]));

        return $farmPanel;
    }
}
