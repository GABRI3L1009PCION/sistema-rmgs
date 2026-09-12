<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>{{ $reportTypeName }} - RMGS</title>
    <style>
        body{font-family:Arial,sans-serif;color:#07164a;margin:28px}header{display:flex;align-items:center;gap:24px;border-bottom:2px solid #0aa574;padding-bottom:14px;margin-bottom:14px}header img{width:210px}h1{margin:0}.meta{display:grid;grid-template-columns:repeat(3,1fr);gap:10px;margin:14px 0}.meta div{padding:9px;border:1px solid #dce8f6}.meta span{display:block;color:#58709b;font-size:11px}.empty{padding:24px;text-align:center;color:#58709b}table{width:100%;border-collapse:collapse;font-size:11px}th,td{padding:7px;border-bottom:1px solid #dce8f6;text-align:left}th{background:#eff7fc}@media print{button{display:none}}
    </style>
</head>
<body>
    <header><img src="{{ asset('images/rmgs-logo.png') }}" alt="RMGS"><div><h1>{{ $reportTypeName }}</h1><p>Registro y Monitoreo de Generacion Solar Guatemala</p></div></header>
    <button onclick="window.print()">Guardar como PDF</button>
    <section class="meta"><div><span>Periodo</span><strong>{{ $selectedPeriodLabel }}</strong></div><div><span>Departamento</span><strong>{{ $selectedDepartmentName }}</strong></div><div><span>Granja</span><strong>{{ $selectedFarmName }}</strong></div></section>
    @if($records->isEmpty())
        <div class="empty">No existen registros para los filtros seleccionados.</div>
    @else
        <table><thead><tr><th>Periodo</th><th>Granja</th><th>Departamento</th><th>Real kWh</th><th>Esperada kWh</th><th>Diferencia</th><th>Cumplimiento</th><th>CO2 kg</th></tr></thead><tbody>
        @foreach($records as $record)
            @php $difference=$record->actual_kwh-$record->expected_kwh; $compliance=$record->expected_kwh>0?$record->actual_kwh/$record->expected_kwh*100:0; @endphp
            <tr><td>{{ $record->period->format('Y-m') }}</td><td>{{ $record->solarFarm->name }}</td><td>{{ $record->solarFarm->department->name }}</td><td>{{ number_format($record->actual_kwh,2) }}</td><td>{{ number_format($record->expected_kwh,2) }}</td><td>{{ number_format($difference,2) }}</td><td>{{ number_format($compliance,1) }}%</td><td>{{ number_format($record->co2_avoided_kg,2) }}</td></tr>
        @endforeach
        </tbody></table>
    @endif
    <script>window.addEventListener('load',()=>window.print());</script>
</body>
</html>
