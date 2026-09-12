<?php

namespace App\Models;

use App\Services\SolarMetricsService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EnergyRecord extends Model
{
    protected $fillable = [
        'solar_farm_id',
        'period',
        'actual_kwh',
        'expected_kwh',
        'co2_avoided_kg',
        'notes',
    ];

    protected $casts = [
        'period' => 'date',
    ];

    public function solarFarm(): BelongsTo
    {
        return $this->belongsTo(SolarFarm::class);
    }

    public function alerts(): HasMany
    {
        return $this->hasMany(Alert::class);
    }

    public function deviationPercent(): float
    {
        return app(SolarMetricsService::class)->deviationPercent($this->actual_kwh, $this->expected_kwh);
    }
}
