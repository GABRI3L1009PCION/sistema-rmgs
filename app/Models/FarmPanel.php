<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FarmPanel extends Model
{
    protected $fillable = ['solar_farm_id', 'panel_model_id', 'quantity'];

    public function solarFarm(): BelongsTo
    {
        return $this->belongsTo(SolarFarm::class);
    }

    public function panelModel(): BelongsTo
    {
        return $this->belongsTo(PanelModel::class);
    }
}
