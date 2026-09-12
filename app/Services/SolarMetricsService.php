<?php

namespace App\Services;

use App\Models\FarmPanel;
use App\Models\EnergyRecord;
use Illuminate\Support\Collection;

class SolarMetricsService
{
    public const CO2_KG_PER_KWH = 0.40;
    public const ALERT_THRESHOLD_PERCENT = 20.0;
    public const DEFAULT_PROJECTION_PERIODS = 3;

    public function installedCapacityKw(Collection $farmPanels): float
    {
        return round((float) $farmPanels->sum(
            fn (FarmPanel $farmPanel) => $farmPanel->quantity * $farmPanel->panelModel->nominal_power_kw
        ), 2);
    }

    public function co2AvoidedKg(float $actualKwh): float
    {
        return round($actualKwh * self::CO2_KG_PER_KWH, 2);
    }

    public function deviationPercent(float $actualKwh, float $expectedKwh): float
    {
        if ($expectedKwh <= 0) {
            return 0;
        }

        return round((1 - ($actualKwh / $expectedKwh)) * 100, 2);
    }

    public function shouldTriggerAlert(float $actualKwh, float $expectedKwh): bool
    {
        return $this->deviationPercent($actualKwh, $expectedKwh) >= self::ALERT_THRESHOLD_PERCENT;
    }

    public function projectedGenerationKwh(Collection $energyRecords, int $periods = self::DEFAULT_PROJECTION_PERIODS): float
    {
        $records = $energyRecords->sortByDesc('period')->take($periods);

        if ($records->isEmpty()) {
            return 0;
        }

        return round((float) $records->avg(fn (EnergyRecord $record) => $record->actual_kwh), 2);
    }
}
