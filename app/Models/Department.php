<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends Model
{
    protected $fillable = ['name', 'slug', 'latitude', 'longitude'];

    public function solarFarms(): HasMany
    {
        return $this->hasMany(SolarFarm::class);
    }
}
