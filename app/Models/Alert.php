<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Alert extends Model
{
    protected $fillable = [
        'solar_farm_id',
        'energy_record_id',
        'period',
        'actual_kwh',
        'expected_kwh',
        'deviation_percent',
        'status',
    ];

    protected $casts = [
        'period' => 'date',
    ];

    public function solarFarm(): BelongsTo
    {
        return $this->belongsTo(SolarFarm::class);
    }

    public function energyRecord(): BelongsTo
    {
        return $this->belongsTo(EnergyRecord::class);
    }
}
