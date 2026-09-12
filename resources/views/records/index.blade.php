@extends('layouts.app')

@section('content')
    <style>
        body:has(.generation-screen) { overflow: hidden; }
        .generation-screen { height: calc(100dvh - 86px); min-height: 650px; display: grid; grid-template-rows: clamp(132px,15vh,146px) 58px 110px minmax(220px,1fr) 170px; gap: 9px; }
        .generation-hero { position: relative; overflow: hidden; display: flex; align-items: center; padding: 22px 28px; border-radius: 8px; color: white; background: linear-gradient(90deg,rgba(4,25,55,.82),rgba(4,25,55,.28),rgba(4,25,55,.05)),url('{{ asset('images/dashboard-hero-guatemala.png') }}') center 58%/cover; box-shadow: var(--shadow); }
        .generation-hero .eyebrow { margin-bottom: 7px; font-size: .72rem; opacity: .95; }
        .generation-hero h1 { font-size: clamp(2rem,2.4vw,2.5rem); line-height: 1; }
        .generation-hero .subtitle { max-width: 390px; margin-top: 7px; font-size: .9rem; line-height: 1.25; }
        .generation-hero .hero-note { position: absolute; top: 17px; right: 22px; display: flex; gap: 7px; max-width: 190px; font-size: .68rem; font-weight: 800; }
        .generation-hero .hero-note svg { width: 18px; }
        .generation-filters { display: grid; grid-template-columns: repeat(3,minmax(0,1fr)); gap: 18px; align-items: end; }
        .generation-filters label { display: block; font-size: .66rem; }
        .generation-filter-box { position: relative; display: block; margin-top: 3px; }
        .generation-filter-box svg { position: absolute; z-index: 1; left: 12px; top: 50%; width: 17px; transform: translateY(-50%); color: var(--muted); pointer-events: none; }
        .generation-filters select { height: 38px; min-height: 38px; padding: 4px 10px 4px 38px; font-size: .72rem; }
        .generation-kpis { grid-template-columns: repeat(4,minmax(0,1fr)); }
        .generation-kpi { min-width: 0; display: grid; grid-template-columns: 56px minmax(0,1fr); gap: 13px; align-items: center; padding: 12px 16px; }
        .generation-kpi-icon { width: 54px; height: 54px; display: grid; place-items: center; border-radius: 8px; color: var(--blue); background: var(--blue-soft); }
        .generation-kpi-icon.green { color: var(--green-dark); background: var(--mint); }
        .generation-kpi-icon svg { width: 28px; height: 28px; }
        .generation-kpi-label { margin-bottom: 5px; font-size: .72rem; white-space: nowrap; }
        .generation-kpi-value { font-size: clamp(1.25rem,1.55vw,1.7rem); line-height: 1; font-weight: 900; white-space: nowrap; }
        .generation-kpi-trend { margin-top: 6px; color: var(--green); font-size: .73rem; font-weight: 900; }
        .generation-kpi-trend span { margin-left: 6px; color: var(--muted); font-size: .62rem; font-weight: 700; }
        .generation-charts { min-height: 0; display: grid; grid-template-columns: minmax(0,1.45fr) minmax(360px,.85fr); gap: 10px; }
        .generation-chart-card { min-height: 0; display: grid; grid-template-rows: 34px minmax(0,1fr); padding: 12px 16px; overflow: hidden; }
        .generation-chart-card .card-title { margin: 0; }.generation-chart-card h2 { font-size: .95rem; }
        .generation-chart-wrap { min-height: 0; }.generation-chart-wrap canvas { width: 100%!important; height: 100%!important; }
        .chart-period { min-height: 32px; padding: 0 10px; font-size: .68rem; }
        .records-card { min-height: 0; overflow: hidden; padding: 9px 14px; }
        .records-card .card-title { margin-bottom: 5px; }.records-card .card-title h2 { font-size: .95rem; }
        .records-table { table-layout: fixed; font-size: .66rem; }
        .records-table th,.records-table td { height: 23px; padding: 3px 8px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .records-table th { background: #f5f9fd; }
        .difference-positive { color: var(--green); font-weight: 800; }.difference-negative { color: var(--red); font-weight: 800; }
        .record-status { display: inline-flex; align-items: center; gap: 6px; color: var(--muted); }.record-status::before { content: ''; width: 8px; height: 8px; border-radius: 50%; background: var(--green); }
        @media(max-width:1180px){body:has(.generation-screen){overflow:auto}.generation-screen{height:auto;grid-template-rows:auto}.generation-filters,.generation-kpis{grid-template-columns:repeat(2,minmax(0,1fr))}.generation-charts{grid-template-columns:1fr}.generation-chart-card{height:340px}.records-card{min-height:300px}}
        @media(max-width:700px){.generation-filters,.generation-kpis{grid-template-columns:1fr}.generation-hero .hero-note{display:none}}
    </style>

    <div class="generation-screen">
        <section class="generation-hero"><div><p class="eyebrow">RMGS - Registro y Monitoreo de Generacion Solar Guatemala</p><h1>Generacion</h1><p class="subtitle">Cada kilovatio cuenta para un Guatemala mas limpio y sostenible.</p></div><div class="hero-note"><i data-lucide="map-pin"></i><span>Guatemala, un pais con mas energia limpia</span></div></section>

        <section class="generation-filters">
            <label>Periodo<span class="generation-filter-box"><i data-lucide="calendar-days"></i><select id="period-filter"><option value="">Todos los periodos</option>@foreach($records->groupBy(fn($record)=>$record->period->format('Y-m')) as $period=>$items)<option value="{{ $period }}" @selected($latestPeriod && $period===$latestPeriod->format('Y-m'))>{{ $items->first()->period->translatedFormat('F Y') }}</option>@endforeach</select></span></label>
            <label>Departamento<span class="generation-filter-box"><i data-lucide="map-pin"></i><select id="generation-department-filter"><option value="">Todos los departamentos</option>@foreach($departments as $department)<option value="{{ $department->name }}">{{ $department->name }}</option>@endforeach</select></span></label>
            <label>Granja<span class="generation-filter-box"><i data-lucide="landmark"></i><select id="generation-farm-filter"><option value="">Todas las granjas</option>@foreach($farms as $farm)<option value="{{ $farm->name }}">{{ $farm->name }}</option>@endforeach</select></span></label>
        </section>

        <section class="grid generation-kpis">
            <article class="card generation-kpi"><span class="generation-kpi-icon green"><i data-lucide="zap"></i></span><div><p class="generation-kpi-label">Generacion total</p><p class="generation-kpi-value">{{ number_format($stats['actual_kwh']) }} kWh</p><p class="generation-kpi-trend">+12% <span>vs. mes anterior</span></p></div></article>
            <article class="card generation-kpi"><span class="generation-kpi-icon"><i data-lucide="bar-chart-3"></i></span><div><p class="generation-kpi-label">Generacion promedio diaria</p><p class="generation-kpi-value">{{ number_format($stats['daily_average']) }} kWh</p><p class="generation-kpi-trend">+8% <span>vs. mes anterior</span></p></div></article>
            <article class="card generation-kpi"><span class="generation-kpi-icon"><i data-lucide="target"></i></span><div><p class="generation-kpi-label">Cumplimiento de la meta</p><p class="generation-kpi-value">{{ number_format($stats['compliance'],1) }}%</p><p class="generation-kpi-trend">Meta mensual</p></div></article>
            <article class="card generation-kpi"><span class="generation-kpi-icon green"><i data-lucide="leaf"></i></span><div><p class="generation-kpi-label">CO2 evitado</p><p class="generation-kpi-value">{{ number_format($stats['co2_tons'],1) }} t</p><p class="generation-kpi-trend">+14% <span>vs. mes anterior</span></p></div></article>
        </section>

        <section class="generation-charts">
            <article class="card generation-chart-card"><div class="card-title"><h2>Generacion real vs esperada</h2><span class="month-picker chart-period">{{ $latestPeriod?->translatedFormat('F Y') ?? 'Sin datos' }}<i data-lucide="chevron-down"></i></span></div><div class="generation-chart-wrap"><canvas id="generation-history-chart"></canvas></div></article>
            <article class="card generation-chart-card"><div class="card-title"><h2>Generacion por granja</h2></div><div class="generation-chart-wrap"><canvas id="generation-farm-chart"></canvas></div></article>
        </section>

        <section class="card records-card"><div class="card-title"><h2>Lecturas por periodo</h2><a class="muted" href="{{ route('records.create') }}">Registrar nueva</a></div><table class="records-table"><thead><tr><th>Periodo</th><th>Granja</th><th>Generacion real (kWh)</th><th>Generacion esperada (kWh)</th><th>Diferencia</th><th>Cumplimiento</th><th>Estado</th></tr></thead><tbody>@foreach($records->take(5) as $record)@php $difference=$record->actual_kwh-$record->expected_kwh; $compliance=$record->expected_kwh>0?($record->actual_kwh/$record->expected_kwh)*100:0; @endphp<tr class="generation-record" data-period="{{ $record->period->format('Y-m') }}" data-department="{{ $record->solarFarm->department->name }}" data-farm="{{ $record->solarFarm->name }}"><td>{{ $record->period->format('m/Y') }}</td><td>{{ $record->solarFarm->name }}</td><td>{{ number_format($record->actual_kwh) }}</td><td>{{ number_format($record->expected_kwh) }}</td><td class="{{ $difference>=0?'difference-positive':'difference-negative' }}">{{ $difference>=0?'+':'' }}{{ number_format($difference) }}</td><td>{{ number_format($compliance,1) }}%</td><td><span class="record-status">Completa</span></td></tr>@endforeach</tbody></table></section>
    </div>

    <script>
        const generationSeries=@json($generationSeries),farmSeries=@json($farmSeries),historyLabels=Object.keys(generationSeries);
        new Chart(document.getElementById('generation-history-chart'),{type:'line',data:{labels:historyLabels,datasets:[{label:'Generacion real',data:historyLabels.map(key=>generationSeries[key].actual),borderColor:'#0aa574',backgroundColor:'rgba(10,165,116,.1)',fill:true,tension:.35,pointRadius:0,borderWidth:3},{label:'Generacion esperada',data:historyLabels.map(key=>generationSeries[key].expected),borderColor:'#1689f4',borderDash:[7,5],tension:.35,pointRadius:0,borderWidth:2}]},options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{position:'bottom',labels:{usePointStyle:true,font:{size:9}}}},scales:{x:{grid:{color:'#e8eff7'},ticks:{font:{size:9}}},y:{grid:{color:'#e8eff7'},ticks:{font:{size:9}}}}}});
        new Chart(document.getElementById('generation-farm-chart'),{type:'bar',data:{labels:farmSeries.map(item=>item.name),datasets:[{data:farmSeries.map(item=>item.actual),backgroundColor:farmSeries.map((_,index)=>index===0?'#20b486':'#2c97ed'),borderRadius:4}]},options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false}},scales:{x:{grid:{color:'#e8eff7'},ticks:{font:{size:8},maxRotation:0}},y:{grid:{color:'#e8eff7'},ticks:{font:{size:9}}}}}});
        const generationRows=[...document.querySelectorAll('.generation-record')],periodFilter=document.getElementById('period-filter'),departmentFilter=document.getElementById('generation-department-filter'),farmFilter=document.getElementById('generation-farm-filter');
        function filterRecords(){generationRows.forEach(row=>row.hidden=!!((periodFilter.value&&row.dataset.period!==periodFilter.value)||(departmentFilter.value&&row.dataset.department!==departmentFilter.value)||(farmFilter.value&&row.dataset.farm!==farmFilter.value)))}
        [periodFilter,departmentFilter,farmFilter].forEach(control=>control.addEventListener('input',filterRecords));
    </script>
@endsection
