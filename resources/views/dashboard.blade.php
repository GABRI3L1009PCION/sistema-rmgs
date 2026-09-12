@extends('layouts.app')

@section('content')
    @php
        $featuredFarms = $farms->take(3);
        $monthlyGeneration = $stats['actual_kwh'];
        $dailyAverage = $records->count() ? $stats['actual_kwh'] / max($records->count() * 30, 1) : 0;
        $equivalentHomes = $stats['actual_kwh'] ? $stats['actual_kwh'] / 40 : 0;
        $equivalentTrees = $stats['co2_tons'] ? $stats['co2_tons'] * 34.5 : 0;
    @endphp

    <style>
        body:has(.dashboard-screen) { overflow: hidden; }
        .dashboard-screen { height: calc(100dvh - 86px); min-height: 630px; display: grid; grid-template-rows: clamp(132px, 15vh, 146px) 62px 112px minmax(160px, 1fr) 150px; gap: 8px; }
        .hero { border-radius: 8px; position: relative; overflow: hidden; display: grid; align-items: center; padding: 22px 28px; color: white; background: linear-gradient(90deg, rgba(4,25,55,.82), rgba(4,25,55,.30), rgba(4,25,55,.05)), url('{{ asset('images/dashboard-hero-guatemala.png') }}') center 58% / cover; box-shadow: var(--shadow); }
        .hero-content { position: relative; z-index: 1; max-width: 680px; transform: translateY(-1px); }
        .hero-eyebrow { font-size: .72rem; margin-bottom: 4px; opacity: .94; }
        .hero h1 { font-size: clamp(1.9rem, 2.2vw, 2.35rem); line-height: .96; letter-spacing: 0; max-width: 590px; }
        .hero-content > p:last-child { margin-top: 4px; font-size: .88rem; line-height: 1.2; opacity: .96; }
        .hero-location { position: absolute; z-index: 2; top: 17px; right: 22px; display: grid; grid-template-columns: 22px 1fr; gap: 7px; max-width: 205px; font-size: .7rem; font-weight: 800; text-shadow: 0 1px 8px rgba(0,0,0,.35); }
        .filter-row { display: grid; grid-template-columns: minmax(430px,.95fr) minmax(360px,1.05fr); align-items: end; gap: 28px; }
        .filter-control { display: block; align-self: stretch; }
        .filter-control > span:first-child { display: block; margin-bottom: 3px; color: var(--muted); font-size: .72rem; font-weight: 800; }
        .select-shell { height: 42px; display: flex; align-items: center; gap: 9px; padding: 0 13px; overflow: hidden; background: white; border: 1px solid var(--line); border-radius: 8px; }
        .select-shell select { border: 0; outline: 0; padding: 0; min-height: 38px; box-shadow: none; font-size: .82rem; font-weight: 800; background: transparent; }
        .map-note { height: 46px; display: grid; grid-template-columns: 28px 1fr; gap: 10px; align-items: center; color: var(--muted); padding-left: 22px; border-left: 1px solid var(--line); font-size: .76rem; line-height: 1.25; }
        .map-note-icon { width: 28px; height: 28px; border-radius: 50%; background: #dff1ff; color: var(--blue); display: grid; place-items: center; }
        .map-note-icon svg { width: 17px; }
        .map-note strong { color: var(--ink); }
        .map-note button { border: 0; padding: 0; background: transparent; color: var(--blue); font-weight: 900; cursor: pointer; }
        .kpis { grid-template-columns: repeat(4, minmax(0,1fr)); }
        .kpi-card { min-width: 0; display: grid; grid-template-columns: 58px minmax(0,1fr); gap: 13px; align-items: center; padding: 13px 16px; }
        .kpi-icon { width: 56px; height: 56px; border-radius: 8px; display: grid; place-items: center; }
        .kpi-icon svg { width: 29px; height: 29px; stroke-width: 2.5; }
        .kpi-icon.green { background: var(--mint); color: var(--green-dark); }
        .kpi-icon.blue { background: var(--blue-soft); color: var(--blue); }
        .kpi-copy { min-width: 0; }
        .kpi-label { font-weight: 800; margin-bottom: 5px; font-size: .78rem; line-height: 1.15; white-space: nowrap; }
        .kpi-value { font-size: clamp(1.38rem,1.7vw,1.9rem); font-weight: 900; line-height: 1; white-space: nowrap; }
        .kpi-trend { margin-top: 6px; color: var(--green); font-size: .78rem; font-weight: 900; }
        .kpi-trend span { margin-left: 6px; color: var(--muted); font-size: .67rem; font-weight: 700; white-space: nowrap; }
        .primary-grid { grid-template-columns: minmax(0,1.48fr) minmax(340px,.82fr); min-height: 0; }
        .chart-card, .summary-card { min-height: 0; overflow: hidden; }
        .chart-card { display: grid; grid-template-rows: 36px minmax(0,1fr); }
        .chart-card .card-title, .summary-card .card-title { margin: 0; }
        .chart-wrap { min-height: 0; }
        .chart-wrap canvas { width: 100% !important; height: 100% !important; }
        .month-picker.compact { min-height: 34px; padding: 0 12px; font-size: .75rem; }
        .summary-card { display: grid; grid-template-rows: 32px minmax(0,1fr) 38px; }
        .summary-list { min-height: 0; display: grid; grid-template-rows: repeat(4,1fr); }
        .summary-item { display: grid; grid-template-columns: 25px 1fr auto; align-items: center; gap: 8px; min-height: 0; border-bottom: 1px solid #e8eff7; font-size: .74rem; }
        .summary-item svg { width: 18px; color: var(--blue); }
        .summary-item strong { font-size: .78rem; white-space: nowrap; }
        .summary-callout { margin-top: 6px; padding: 0 11px; border-radius: 8px; background: #e7f7ee; color: #2c7255; display: flex; gap: 8px; align-items: center; font-size: .72rem; }
        .summary-callout svg { width: 18px; }
        .bottom-grid { grid-template-columns: minmax(360px,.82fr) minmax(0,1.18fr); min-height: 0; }
        .bottom-grid .card { min-height: 0; overflow: visible; padding: 8px 14px; }
        .bottom-grid .card-title { margin-bottom: 4px; }
        .bottom-grid .card-title h2 { font-size: .9rem; }
        .alert-list { display: grid; gap: 5px; }
        .alert-row { display: grid; grid-template-columns: 28px 1fr auto; gap: 8px; align-items: center; min-height: 42px; padding: 4px 8px; border: 1px solid #e8eff7; border-radius: 7px; font-size: .68rem; }
        .alert-row p { margin-top: 2px; }
        .alert-icon { width: 25px; height: 25px; border-radius: 6px; display: grid; place-items: center; color: white; }
        .alert-icon svg { width: 15px; }
        .alert-icon.red { background: var(--red); }
        .alert-icon.amber { background: var(--amber); }
        .table-card { overflow: hidden; }
        .table-card table { table-layout: fixed; font-size: .66rem; }
        .table-card th, .table-card td { padding: 4px 7px; line-height: 1.15; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .farm-thumb { width: 30px; height: 22px; flex: 0 0 auto; border-radius: 4px; background: url('https://images.unsplash.com/photo-1508514177221-188b1cf16e9d?auto=format&fit=crop&w=120&q=70') center / cover; }
        .status-dot { width: 8px; height: 8px; border-radius: 50%; background: var(--green); display: inline-block; margin-right: 5px; }
        .map-dialog { width: min(1000px,90vw); border: 0; border-radius: 8px; padding: 0; box-shadow: 0 28px 80px rgba(7,22,74,.28); }
        .map-dialog::backdrop { background: rgba(7,22,50,.48); }
        .map-dialog-header { display: flex; align-items: center; justify-content: space-between; padding: 14px 18px; border-bottom: 1px solid var(--line); }
        .map-dialog #map { height: min(620px,72vh); border: 0; border-radius: 0; }
        @media (max-width: 1180px) {
            body:has(.dashboard-screen) { overflow: auto; }
            .dashboard-screen { height: auto; min-height: 0; grid-template-rows: auto; }
            .hero { min-height: 175px; }
            .filter-row { grid-template-columns: 1fr; gap: 8px; }
            .map-note { border-left: 0; padding-left: 0; }
            .kpis { grid-template-columns: repeat(2,minmax(0,1fr)); }
            .primary-grid, .bottom-grid { grid-template-columns: 1fr; }
            .chart-card { height: 330px; }
            .summary-card { min-height: 260px; }
        }
        @media (max-width: 700px) { .kpis { grid-template-columns: 1fr; } .hero-location { display: none; } .hero h1 { font-size: 2rem; } }
    </style>

    <div class="dashboard-screen">
        <section class="hero">
            <div class="hero-content"><p class="hero-eyebrow">RMGS - Registro y Monitoreo de Generacion Solar Guatemala</p><h1>Energia solar<br>para un mejor manana</h1><p>Monitoreamos hoy un Guatemala mas limpio y sostenible.</p></div>
            <div class="hero-location"><i data-lucide="map-pin"></i><span>Guatemala, un pais con mas energia limpia</span></div>
        </section>

        <section class="filter-row">
            <label class="filter-control"><span>Departamento</span><span class="select-shell"><i data-lucide="map-pin"></i><select aria-label="Departamento"><option>Todos los departamentos</option>@foreach ($departmentReports as $report)<option>{{ $report['department'] }}</option>@endforeach</select><i data-lucide="chevron-down"></i></span></label>
            <div class="map-note"><span class="map-note-icon"><i data-lucide="info"></i></span><p><strong>Mapa disponible en <button type="button" data-open-map>Ver mapa</button>.</strong><br>Selecciona un departamento para visualizar las granjas.</p></div>
        </section>

        <section class="grid kpis">
            <article class="card kpi-card"><span class="kpi-icon green"><i data-lucide="landmark"></i></span><div class="kpi-copy"><p class="kpi-label">Granjas solares</p><p class="kpi-value">{{ number_format($stats['farms']) }}</p><p class="kpi-trend">+20% <span>vs. mes anterior</span></p></div></article>
            <article class="card kpi-card" id="paneles"><span class="kpi-icon blue"><i data-lucide="grid-2x2"></i></span><div class="kpi-copy"><p class="kpi-label">Paneles instalados</p><p class="kpi-value">{{ number_format($stats['panels']) }}</p><p class="kpi-trend">+12% <span>vs. mes anterior</span></p></div></article>
            <article class="card kpi-card"><span class="kpi-icon blue"><i data-lucide="zap"></i></span><div class="kpi-copy"><p class="kpi-label">Capacidad instalada</p><p class="kpi-value">{{ number_format($stats['capacity_kw'], 1) }} kW</p><p class="kpi-trend">+8% <span>vs. mes anterior</span></p></div></article>
            <article class="card kpi-card"><span class="kpi-icon green"><i data-lucide="leaf"></i></span><div class="kpi-copy"><p class="kpi-label">CO2 evitado</p><p class="kpi-value">{{ number_format($stats['co2_tons'], 1) }} t</p><p class="kpi-trend">+14% <span>vs. mes anterior</span></p></div></article>
        </section>

        <section class="grid primary-grid">
            <article class="card chart-card" id="proyecciones"><div class="card-title"><h2>Generacion real vs esperada</h2><span class="month-picker compact">Septiembre 2026 <i data-lucide="chevron-down"></i></span></div><div class="chart-wrap"><canvas id="generationChart"></canvas></div></article>
            <article class="card summary-card"><div class="card-title"><h2><i data-lucide="bar-chart-3"></i> Resumen nacional</h2></div><div class="summary-list"><div class="summary-item"><i data-lucide="zap"></i><span>Generacion del mes</span><strong>{{ number_format($monthlyGeneration) }} kWh</strong></div><div class="summary-item"><i data-lucide="bar-chart-3"></i><span>Promedio diario</span><strong>{{ number_format($dailyAverage) }} kWh</strong></div><div class="summary-item"><i data-lucide="leaf"></i><span>Hogares equivalentes</span><strong>~ {{ number_format($equivalentHomes) }}</strong></div><div class="summary-item"><i data-lucide="trees"></i><span>Arboles equivalentes</span><strong>~ {{ number_format($equivalentTrees) }}</strong></div></div><div class="summary-callout"><i data-lucide="leaf"></i><span>Contribuyendo a un Guatemala mas sostenible.</span></div></article>
        </section>

        <section class="grid bottom-grid">
            <article class="card" id="alertas"><div class="card-title"><h2><i data-lucide="bell"></i> Alertas recientes</h2><a class="muted" href="{{ route('alerts.index') }}">Ver todas</a></div><div class="alert-list">@forelse ($alerts->take(2) as $alert)<div class="alert-row"><span class="alert-icon red"><i data-lucide="triangle-alert"></i></span><div><strong>Produccion por debajo de lo esperado</strong><p class="muted">{{ $alert->solarFarm->name }}</p></div><span class="muted">{{ $alert->period->format('M Y') }}</span></div>@empty<div class="alert-row"><span class="alert-icon amber"><i data-lucide="circle-check"></i></span><div><strong>Sin alertas activas</strong><p class="muted">Todas las granjas se mantienen dentro del rango esperado.</p></div><span class="muted">Hoy</span></div>@endforelse</div></article>
            <article class="card table-card"><div class="card-title"><h2>Granjas registradas</h2><a class="muted" href="{{ route('farms.create') }}">Ver todas</a></div><table><thead><tr><th style="width:29%">Nombre</th><th>Departamento</th><th>Capacidad (kW)</th><th>Generacion (kWh)</th><th>Estado</th></tr></thead><tbody>@foreach ($featuredFarms as $farm)<tr><td><span class="nav"><span class="farm-thumb"></span><strong>{{ $farm->name }}</strong></span></td><td>{{ $farm->department->name }}</td><td>{{ number_format($farm->installedCapacityKw(), 1) }}</td><td>{{ number_format($farm->energyRecords->sum('actual_kwh')) }}</td><td><span class="status-dot"></span>{{ ucfirst($farm->status) }}</td></tr>@endforeach</tbody></table></article>
        </section>
    </div>

    <dialog class="map-dialog" id="mapa"><div class="map-dialog-header"><h2>Mapa de granjas solares</h2><form method="dialog"><button class="btn" aria-label="Cerrar mapa"><i data-lucide="x"></i></button></form></div><div id="map"></div></dialog>

    <script>
        const generationSeries = @json($generationSeries);
        const labels = Object.keys(generationSeries);
        new Chart(document.getElementById('generationChart'), { type: 'line', data: { labels, datasets: [{ label: 'Generacion real', data: labels.map(label => generationSeries[label].actual), borderColor: '#0aa574', backgroundColor: 'rgba(10,165,116,.10)', tension: .35, fill: true, pointRadius: 0, borderWidth: 3 }, { label: 'Generacion esperada', data: labels.map(label => generationSeries[label].expected), borderColor: '#1689f4', borderDash: [7,5], tension: .35, pointRadius: 0, borderWidth: 2 }] }, options: { responsive: true, maintainAspectRatio: false, layout: { padding: { top: 3 } }, plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, boxWidth: 7, padding: 12, font: { size: 10 } } } }, scales: { x: { grid: { color: '#e8eff7' }, ticks: { color: '#58709b', font: { size: 9 } } }, y: { grid: { color: '#e8eff7' }, ticks: { color: '#58709b', font: { size: 9 } } } } } });
        const farms = @json($mapFarms);
        const mapDialog = document.getElementById('mapa');
        const map = L.map('map').setView([15.2,-90.4], 7);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 18, attribution: '&copy; OpenStreetMap' }).addTo(map);
        farms.forEach(farm => L.marker([farm.lat,farm.lng]).addTo(map).bindPopup(`<strong>${farm.name}</strong><br>${farm.municipality}, ${farm.department}<br>Capacidad: ${Number(farm.capacity_kw).toFixed(1)} kW`));
        document.querySelectorAll('[data-open-map], a[href="/#mapa"]').forEach(trigger => trigger.addEventListener('click', event => { event.preventDefault(); mapDialog.showModal(); window.setTimeout(() => map.invalidateSize(), 80); }));
        if (window.location.hash === '#mapa') { mapDialog.showModal(); window.setTimeout(() => map.invalidateSize(), 80); }
    </script>
@endsection
