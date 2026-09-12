@extends('layouts.app')

@section('content')
    @php
        $featuredFarms = $farms->sortByDesc(fn ($farm) => $farm->energyRecords->sum('actual_kwh'))->take(3);
        $activeFarms = $farms->where('status', 'active')->count();
        $compliance = $stats['expected_kwh'] > 0 ? ($stats['actual_kwh'] / $stats['expected_kwh']) * 100 : 0;
        $topDepartment = $departmentReports->first();
    @endphp

    <style>
        body:has(.dashboard-screen) { overflow: auto; }
        .dashboard-screen {
            min-height: calc(100dvh - 82px);
            display: grid;
            grid-template-rows: 136px 82px 300px 158px;
            gap: 8px;
            overflow: visible;
            padding-bottom: 10px;
        }
        .overview-strip { display: grid; grid-template-columns: 1fr; gap: 8px; }
        .dashboard-kpis { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 8px; }
        .dashboard-screen .card { padding: 10px 12px; }
        .mission-panel { min-height: 0; display: grid; align-content: center; gap: 8px; color: white; background: linear-gradient(90deg, rgba(5,28,46,.9), rgba(5,28,46,.44)), url('{{ asset('images/dashboard-hero-guatemala.png') }}') center 58% / cover; }
        .mission-panel h1 { max-width: 620px; font-size: clamp(1.42rem,1.85vw,1.95rem); line-height: 1.12; }
        .mission-panel p { max-width: 620px; color: rgba(255,255,255,.9); font-size: .8rem; line-height: 1.45; }
        .mini-kpi { min-height: 0; display: grid; grid-template-columns: 42px minmax(0,1fr); align-items: center; gap: 12px; }
        .mini-kpi-icon { width: 34px; height: 34px; display: grid; place-items: center; border-radius: 8px; background: var(--blue-soft); color: var(--blue); }
        .mini-kpi-icon.green { background: var(--mint); color: var(--green-dark); }
        .mini-kpi strong { display: block; font-size: 1.28rem; line-height: 1; margin-top: 0; }
        .mini-kpi span { color: var(--muted); font-size: .7rem; font-weight: 800; }
        .workbench { min-height: 0; display: grid; grid-template-columns: minmax(0,1.35fr) minmax(360px,.75fr); gap: 8px; align-items: stretch; }
        .chart-card { min-height: 0; display: grid; grid-template-rows: 34px minmax(0,1fr); overflow: hidden; }
        .chart-wrap { min-height: 0; }
        .chart-wrap canvas { max-height: 100%; }
        .chart-wrap canvas { width: 100% !important; height: 100% !important; }
        .workbench > aside { min-height: 0; overflow: hidden; }
        .health-grid { display: grid; gap: 6px; }
        .health-item { display: grid; grid-template-columns: 28px 1fr auto; align-items: center; gap: 8px; padding: 6px 8px; border: 1px solid var(--line); border-radius: 8px; background: #f8fbff; font-size: .78rem; }
        .health-item svg { width: 20px; color: var(--blue); }
        .health-item strong { white-space: nowrap; }
        .bottom-layout { min-height: 0; display: grid; grid-template-columns: minmax(0,1fr) minmax(360px,.72fr); gap: 8px; }
        .bottom-layout > .card { min-height: 0; overflow: hidden; }
        .farm-ranking table { table-layout: fixed; }
        .farm-ranking table { font-size: .66rem; }
        .farm-ranking td, .farm-ranking th { padding: 3px 8px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .status-text { display: inline-flex; align-items: center; gap: 6px; }
        .status-text::before { content: ''; width: 8px; height: 8px; border-radius: 50%; background: var(--green); }
        .status-text.maintenance::before { background: var(--amber); }
        .status-text.inactive::before { background: var(--red); }
        .alert-stack { display: grid; gap: 6px; }
        .alert-tile { display: grid; grid-template-columns: 28px 1fr auto; align-items: center; gap: 8px; padding: 6px 8px; border-radius: 8px; background: #fff7ed; color: #8a4b08; font-size: .7rem; }
        .alert-tile p { font-size: .72rem; line-height: 1.1; }
        .alert-tile svg { width: 18px; color: var(--amber); }
        .alert-tile.danger { background: #fff1f0; color: #a11d1d; }
        .alert-tile.danger svg { color: var(--red); }
        @media (max-width: 1180px) {
            body:has(.dashboard-screen) { overflow: auto; }
            .dashboard-screen { height: auto; min-height: 0; overflow: visible; }
            .overview-strip, .dashboard-kpis, .workbench, .bottom-layout { grid-template-columns: 1fr; }
            .mini-kpi, .mission-panel { min-height: 150px; }
            .chart-card { min-height: 340px; }
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
        </section>

        <section class="dashboard-kpis">
            <article class="card mini-kpi"><span class="mini-kpi-icon green"><i data-lucide="landmark"></i></span><div><strong>{{ number_format($activeFarms) }}/{{ number_format($stats['farms']) }}</strong><span>Granjas activas</span></div></article>
            <article class="card mini-kpi"><span class="mini-kpi-icon"><i data-lucide="zap"></i></span><div><strong>{{ number_format($stats['capacity_kw'], 1) }} kW</strong><span>Capacidad instalada</span></div></article>
            <article class="card mini-kpi"><span class="mini-kpi-icon green"><i data-lucide="target"></i></span><div><strong>{{ number_format($compliance, 1) }}%</strong><span>Cumplimiento acumulado</span></div></article>
        </section>

        <section class="workbench">
            <article class="card chart-card">
                <div class="card-title"><h2>Generacion real contra esperada</h2><span class="muted">Ultimos periodos</span></div>
                <div class="chart-wrap"><canvas id="generationChart"></canvas></div>
            </article>

            <aside class="card">
                <div class="card-title"><h2>Lectura rapida</h2><span class="muted">Acumulado</span></div>
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
                <div class="card-title"><h2>Granjas con mayor generacion</h2><span class="muted">Top 3</span></div>
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
                <div class="card-title"><h2>Atencion requerida</h2><span class="muted">Activas</span></div>
                <div class="alert-stack">
                    @forelse ($alerts->take(2) as $alert)
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
                layout: { padding: { top: 2, right: 8, bottom: 0, left: 0 } },
                plugins: { legend: { position: 'bottom', labels: { boxWidth: 34, padding: 8 } } },
                scales: {
                    x: { grid: { color: '#e8eff7' }, ticks: { maxRotation: 0 } },
                    y: { grid: { color: '#e8eff7' } },
                },
            },
        });
    </script>
@endsection
