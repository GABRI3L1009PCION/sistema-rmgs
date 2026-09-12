<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\EnergyRecord;
use App\Models\SolarFarm;

class ApiController extends Controller
{
    public function departments()
    {
        return Department::orderBy('name')->get();
    }

    public function farms()
    {
        return SolarFarm::with('department', 'farmPanels.panelModel')->get();
    }

    public function generation()
    {
        return EnergyRecord::with('solarFarm.department')->orderByDesc('period')->get();
    }

    public function docs()
    {
        return response()->json([
            'name' => 'API REST Sistema RMGS',
            'endpoints' => [
                'GET /api/departments' => 'Lista los 22 departamentos de Guatemala.',
                'GET /api/farms' => 'Lista granjas solares con departamento y paneles.',
                'GET /api/generation' => 'Lista registros de generacion real y esperada.',
                'GET /api/stats' => 'Devuelve indicadores nacionales, reportes por departamento y alertas.',
            ],
            'calculation_rules' => [
                'installed_capacity_kw' => 'cantidad de paneles * potencia nominal del modelo',
                'co2_avoided_kg' => 'kWh reales * 0.40 kg CO2/kWh',
                'alert' => 'generacion real al menos 20% debajo de la esperada',
                'projection' => 'promedio movil simple de los ultimos 3 periodos reales',
            ],
        ]);
    }
}
