@extends('layouts.app')

@section('content')
    @php
        $featuredFarms = $farms->sortByDesc(fn ($farm) => $farm->energyRecords->sum('actual_kwh'))->take(5);
        $activeFarms = $farms->where('status', 'active')->count();
        $compliance = $stats['expected_kwh'] > 0 ? ($stats['actual_kwh'] / $stats['expected_kwh']) * 100 : 0;
        $topDepartment = $departmentReports->first();
    @endphp

    <style>
        body:has(.dashboard-screen) { overflow: auto; }
        .dashboard-screen { display: grid; gap: 12px; }
        .overview-strip { display: grid; grid-template-columns: minmax(0,1.2fr) repeat(3,minmax(170px,.55fr)); gap: 12px; }
        .mission-panel { min-height: 190px; display: grid; align-content: end; gap: 10px; color: white; background: linear-gradient(90deg, rgba(5,28,46,.9), rgba(5,28,46,.44)), url('{{ asset('images/dashboard-hero-guatemala.png') }}') center 58% / cover; }
        .mission-panel h1 { max-width: 620px; font-size: clamp(2rem,3vw,3.1rem); line-height: 1; }
        .mission-panel p { max-width: 620px; color: rgba(255,255,255,.9); }
        .mini-kpi { display: grid; align-content: space-between; min-height: 190px; }
        .mini-kpi-icon { width: 42px; height: 42px; display: grid; place-items: center; border-radius: 8px; background: var(--blue-soft); color: var(--blue); }
        .mini-kpi-icon.green { background: var(--mint); color: var(--green-dark); }
        .mini-kpi strong { display: block; font-size: 1.7rem; line-height: 1; margin-top: 18px; }
        .mini-kpi span { color: var(--muted); font-size: .78rem; font-weight: 800; }
        .workbench { display: grid; grid-template-columns: minmax(0,1.35fr) minmax(360px,.75fr); gap: 12px; }
        .chart-card { min-height: 360px; display: grid; grid-template-rows: auto minmax(0,1fr); }
        .chart-wrap { min-height: 0; }
        .chart-wrap canvas { width: 100% !important; height: 100% !important; }
        .health-grid { display: grid; gap: 10px; }
        .health-item { display: grid; grid-template-columns: 36px 1fr auto; align-items: center; gap: 10px; padding: 10px; border: 1px solid var(--line); border-radius: 8px; background: #f8fbff; }
        .health-item svg { width: 20px; color: var(--blue); }
        .health-item strong { white-space: nowrap; }
        .bottom-layout { display: grid; grid-template-columns: minmax(0,1fr) minmax(360px,.72fr); gap: 12px; }
        .farm-ranking table { table-layout: fixed; }
        .farm-ranking td, .farm-ranking th { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .status-text { display: inline-flex; align-items: center; gap: 6px; }
        .status-text::before { content: ''; width: 8px; height: 8px; border-radius: 50%; background: var(--green); }
        .status-text.maintenance::before { background: var(--amber); }
        .status-text.inactive::before { background: var(--red); }
        .alert-stack { display: grid; gap: 8px; }
        .alert-tile { display: grid; grid-template-columns: 34px 1fr auto; align-items: center; gap: 9px; padding: 10px; border-radius: 8px; background: #fff7ed; color: #8a4b08; }
        .alert-tile svg { width: 18px; color: var(--amber); }
        .alert-tile.danger { background: #fff1f0; color: #a11d1d; }
        .alert-tile.danger svg { color: var(--red); }
        @media (max-width: 1180px) {
            .overview-strip, .workbench, .bottom-layout { grid-template-columns: 1fr; }
            .mini-kpi, .mission-panel { min-height: 150px; }
        }
    </style>

    <div class="dashboard-screen">
        <section class="overview-strip">
            <article class="card mission-panel">
                <div>
                    <p class="muted" style="color:rgba(255,255,255,.78)">RMGS Guatemala</p>
                    <h1>Monitoreo nacional de generacion solar</h1>
                    <p>{{ number_format($stats['families']) }} familias beneficiadas y {{ number_format($stats['co2_tons'], 1) }} toneladas de CO2 evitadas con datos registrados.</p>
                </div>
            </article>
            <article class="card mini-kpi"><span class="mini-kpi-icon green"><i data-lucide="landmark"></i></span><div><strong>{{ number_format($activeFarms) }}/{{ number_format($stats['farms']) }}</strong><span>Granjas activas</span></div></article>
            <article class="card mini-kpi"><span class="mini-kpi-icon"><i data-lucide="zap"></i></span><div><strong>{{ number_format($stats['capacity_kw'], 1) }} kW</strong><span>Capacidad instalada</span></div></article>
            <article class="card mini-kpi"><span class="mini-kpi-icon green"><i data-lucide="target"></i></span><div><strong>{{ number_format($compliance, 1) }}%</strong><span>Cumplimiento acumulado</span></div></article>
        </section>

        <section class="workbench">
            <article class="card chart-card">
                <div class="card-title">
                    <h2>Generacion real contra esperada</h2>
                    <a class="btn" href="{{ route('records.create') }}"><i data-lucide="plus"></i>Registrar lectura</a>
                </div>
                <div class="chart-wrap"><canvas id="generationChart"></canvas></div>
            </article>

            <aside class="card">
                <div class="card-title"><h2>Lectura rapida</h2><a class="muted" href="{{ route('reports.index') }}">Reportes</a></div>
                <div class="health-grid">
                    <div class="health-item"><i data-lucide="bar-chart-3"></i><span>Generacion acumulada</span><strong>{{ number_format($stats['actual_kwh']) }} kWh</strong></div>
                    <div class="health-item"><i data-lucide="grid-2x2"></i><span>Paneles instalados</span><strong>{{ number_format($stats['panels']) }}</strong></div>
                    <div class="health-item"><i data-lucide="map-pin"></i><span>Departamento lider</span><strong>{{ $topDepartment['department'] ?? 'Sin datos' }}</strong></div>
                    <div class="health-item"><i data-lucide="bell"></i><span>Alertas activas</span><strong>{{ number_format($alerts->count()) }}</strong></div>
                </div>
            </aside>
        </section>

        <section class="bottom-layout">
            <article class="card farm-ranking">
                <div class="card-title"><h2>Granjas con mayor generacion</h2><a class="muted" href="{{ route('farms.index') }}">Gestionar granjas</a></div>
                <table>
                    <thead><tr><th>Granja</th><th>Departamento</th><th>Capacidad</th><th>Generacion</th><th>Estado</th></tr></thead>
                    <tbody>
                        @foreach ($featuredFarms as $farm)
                            <tr>
                                <td><strong>{{ $farm->name }}</strong></td>
                                <td>{{ $farm->department->name }}</td>
                                <td>{{ number_format($farm->installedCapacityKw(), 1) }} kW</td>
                                <td>{{ number_format($farm->energyRecords->sum('actual_kwh')) }} kWh</td>
                                <td><span class="status-text {{ $farm->status }}">{{ $farm->status === 'maintenance' ? 'Mantenimiento' : ($farm->status === 'active' ? 'Activa' : 'Inactiva') }}</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </article>

            <article class="card">
                <div class="card-title"><h2>Atencion requerida</h2><a class="muted" href="{{ route('alerts.index') }}">Ver alertas</a></div>
                <div class="alert-stack">
                    @forelse ($alerts->take(4) as $alert)
                        <div class="alert-tile danger"><i data-lucide="triangle-alert"></i><div><strong>{{ $alert->solarFarm->name }}</strong><p class="muted">{{ number_format($alert->deviation_percent, 1) }}% debajo de lo esperado</p></div><span>{{ $alert->period->format('m/Y') }}</span></div>
                    @empty
                        <div class="alert-tile"><i data-lucide="circle-check"></i><div><strong>Sin alertas activas</strong><p class="muted">Las granjas estan dentro del rango esperado.</p></div><span>Hoy</span></div>
                    @endforelse
                </div>
            </article>
        </section>
    </div>

    <script>
        const generationSeries = @json($generationSeries);
        const labels = Object.keys(generationSeries);

        new Chart(document.getElementById('generationChart'), {
            type: 'line',
            data: {
                labels,
                datasets: [
                    { label: 'Real', data: labels.map(label => generationSeries[label].actual), borderColor: '#0aa574', backgroundColor: 'rgba(10,165,116,.10)', tension: .35, fill: true, borderWidth: 3 },
                    { label: 'Esperada', data: labels.map(label => generationSeries[label].expected), borderColor: '#1689f4', borderDash: [7,5], tension: .35, borderWidth: 2 },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom' } },
                scales: { x: { grid: { color: '#e8eff7' } }, y: { grid: { color: '#e8eff7' } } },
            },
        });
    </script>
@endsection
