<?php

namespace App\Models;

use App\Services\SolarMetricsService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SolarFarm extends Model
{
    protected $fillable = [
        'department_id',
        'name',
        'owner',
        'municipality',
        'latitude',
        'longitude',
        'families_benefited',
        'status',
        'notes',
    ];

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function farmPanels(): HasMany
    {
        return $this->hasMany(FarmPanel::class);
    }

    public function energyRecords(): HasMany
    {
        return $this->hasMany(EnergyRecord::class);
    }

    public function alerts(): HasMany
    {
        return $this->hasMany(Alert::class);
    }

    public function installedCapacityKw(): float
    {
        return app(SolarMetricsService::class)->installedCapacityKw($this->farmPanels);
    }

    public function projectedGenerationKwh(): float
    {
        return app(SolarMetricsService::class)->projectedGenerationKwh($this->energyRecords);
    }
}
