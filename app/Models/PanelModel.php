<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PanelModel extends Model
{
    protected $fillable = ['brand', 'model', 'nominal_power_kw', 'status'];

    public function farmPanels(): HasMany
    {
        return $this->hasMany(FarmPanel::class);
    }
}
