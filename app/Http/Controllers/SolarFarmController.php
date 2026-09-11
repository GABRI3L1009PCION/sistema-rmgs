<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\PanelModel;
use App\Models\SolarFarm;
use Illuminate\Http\Request;

class SolarFarmController extends Controller
{
    public function create()
    {
        return view('farms.create', [
            'departments' => Department::orderBy('name')->get(),
            'panelModels' => PanelModel::where('status', 'active')->orderBy('brand')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'department_id' => ['required', 'exists:departments,id'],
            'name' => ['required', 'string', 'max:255'],
            'owner' => ['nullable', 'string', 'max:255'],
            'municipality' => ['required', 'string', 'max:255'],
            'latitude' => ['required', 'numeric', 'between:13,18'],
            'longitude' => ['required', 'numeric', 'between:-93,-88'],
            'families_benefited' => ['required', 'integer', 'min:0'],
            'panel_model_id' => ['required', 'exists:panel_models,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'expected_kwh' => ['required', 'numeric', 'min:1'],
            'actual_kwh' => ['required', 'numeric', 'min:0'],
        ]);

        $farm = SolarFarm::create([
            'department_id' => $validated['department_id'],
            'name' => $validated['name'],
            'owner' => $validated['owner'] ?? null,
            'municipality' => $validated['municipality'],
            'latitude' => $validated['latitude'],
            'longitude' => $validated['longitude'],
            'families_benefited' => $validated['families_benefited'],
            'status' => 'active',
        ]);

        $farm->farmPanels()->create([
            'panel_model_id' => $validated['panel_model_id'],
            'quantity' => $validated['quantity'],
        ]);

        $record = $farm->energyRecords()->create([
            'period' => now()->startOfMonth(),
            'actual_kwh' => $validated['actual_kwh'],
            'expected_kwh' => $validated['expected_kwh'],
            'co2_avoided_kg' => round($validated['actual_kwh'] * 0.70, 2),
            'notes' => 'Registro inicial desde formulario rapido.',
        ]);

        $deviation = $record->deviationPercent();
        if ($deviation >= 20) {
            $farm->alerts()->create([
                'energy_record_id' => $record->id,
                'period' => $record->period,
                'actual_kwh' => $record->actual_kwh,
                'expected_kwh' => $record->expected_kwh,
                'deviation_percent' => $deviation,
                'status' => 'active',
            ]);
        }

        return redirect('/')->with('status', 'Granja solar registrada correctamente.');
    }
}
