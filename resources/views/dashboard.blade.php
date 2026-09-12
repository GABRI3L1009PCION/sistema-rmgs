@extends('layouts.app')

@section('content')
    @php
        $dailyAverage = $stats['actual_kwh'] > 0 ? round($stats['actual_kwh'] / 30) : 0;
        $homesEquivalent = round($stats['actual_kwh'] / 40);
        $treesEquivalent = round($stats['co2_tons'] * 34.53);
        $co2Trend = $trends['co2'];
    @endphp

    <style>
        body:has(.dashboard-screen) { overflow: auto; }
        .dashboard-screen {
            width: min(100%, 1680px);
            margin: 0 auto;
            min-height: calc(100dvh - 86px);
            display: grid;
            grid-template-rows: auto auto auto auto;
            gap: 10px;
            padding: 10px 0 18px;
            overflow: visible;
        }
        .dashboard-hero {
            position: relative;
            min-height: clamp(145px, 17vh, 190px);
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 24px;
            padding: clamp(18px, 1.8vw, 28px);
            border-radius: 8px;
            color: white;
            background:
                linear-gradient(90deg, rgba(4, 25, 55, .9), rgba(4, 25, 55, .48), rgba(4, 25, 55, .08)),
                url('{{ asset('images/dashboard-hero-guatemala.png') }}') center 58% / cover;
            box-shadow: var(--shadow);
        }
        .dashboard-hero .eyebrow { font-size: .86rem; color: rgba(255,255,255,.88); font-weight: 700; }
        .dashboard-hero h1 {
            max-width: none;
            margin-top: 8px;
            font-size: clamp(1.85rem, 2.75vw, 3.05rem);
            line-height: 1.04;
            letter-spacing: 0;
            white-space: nowrap;
        }
        .dashboard-hero .subtitle { max-width: 720px; margin-top: 6px; color: rgba(255,255,255,.9); font-size: clamp(.88rem, 1.05vw, 1.05rem); font-weight: 700; }
        .hero-badge { align-self: flex-start; display: flex; align-items: center; gap: 9px; max-width: 260px; font-size: .82rem; font-weight: 900; color: white; }
        .hero-badge svg { width: 24px; height: 24px; flex: 0 0 auto; }
        .dashboard-kpis { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 10px; }
        .dashboard-kpi {
            display: flex;
            align-items: center;
            justify-content: flex-start;
            gap: 16px;
            min-height: 96px;
            padding: 14px 20px;
        }
        .kpi-icon {
            width: 50px;
            height: 50px;
            flex: 0 0 50px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            background: var(--mint);
            color: var(--green-dark);
            line-height: 0;
        }
        .kpi-icon.blue { background: var(--blue-soft); color: var(--blue); }
        .kpi-icon svg { width: 26px; height: 26px; display: block; flex: 0 0 26px; stroke-width: 2.4; }
        .dashboard-kpi > div { min-width: 0; display: flex; flex-direction: column; justify-content: center; }
        .dashboard-kpi > div > span { display: block; font-weight: 900; color: var(--ink); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .dashboard-kpi strong { display: block; margin-top: 4px; font-size: clamp(1.35rem, 1.8vw, 2.1rem); line-height: 1; color: #061844; }
        html[data-theme="dark"] .dashboard-kpi strong { color: var(--ink); }
        .dashboard-kpi small { display: inline-flex; gap: 8px; margin-top: 5px; color: var(--green); font-size: .68rem; font-weight: 900; }
        .dashboard-kpi small b { color: #637aa8; }
        .dashboard-main {
            display: grid;
            grid-template-columns: minmax(0, 1.18fr) minmax(330px, .52fr);
            gap: 10px;
            align-items: start;
        }
        .chart-card { height: clamp(265px, 30vh, 320px); min-height: 0; display: grid; grid-template-rows: 34px minmax(0, 1fr); padding: 14px 16px 10px; overflow: hidden; }
        .chart-card .card-title { margin: 0; }
        .chart-card h2, .summary-card h2 { font-size: clamp(.98rem, 1.15vw, 1.18rem); }
        .month-chip { min-height: 30px; display: inline-flex; align-items: center; gap: 8px; padding: 0 10px 0 12px; border: 1px solid var(--line); border-radius: 8px; background: var(--surface); color: var(--ink); font-weight: 900; box-shadow: var(--shadow); font-size: .82rem; }
        .month-chip.is-loading { opacity: .62; pointer-events: none; }
        .month-chip select { min-height: 28px; width: auto; min-width: 130px; padding: 2px 24px 2px 0; border: 0; background: transparent; color: var(--ink); font-weight: 900; cursor: pointer; outline: 0; }
        .chart-wrap { min-height: 0; height: 100%; overflow: hidden; }
        .chart-wrap canvas { width: 100% !important; height: 100% !important; }
        .summary-card { height: clamp(265px, 30vh, 320px); min-height: 0; display: grid; grid-template-rows: 34px minmax(0, 1fr) auto; padding: 14px 16px 10px; overflow: hidden; }
        .summary-list { display: grid; align-content: stretch; }
        .summary-row {
            display: grid;
            grid-template-columns: 28px 1fr auto;
            align-items: center;
            gap: 10px;
            min-height: 31px;
            border-bottom: 1px solid var(--line);
            font-size: .82rem;
        }
        .summary-row span { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .summary-row svg { width: 19px; height: 19px; color: var(--blue); }
        .summary-row strong { white-space: nowrap; }
        .sustainability-note { display: flex; align-items: center; gap: 8px; margin-top: 8px; padding: 8px 10px; border-radius: 8px; background: var(--mint); color: var(--green-dark); font-size: .82rem; font-weight: 800; }
        .dashboard-bottom { display: grid; grid-template-columns: minmax(380px, .62fr) minmax(0, 1fr); gap: 10px; }
        .recent-alerts, .farm-table-card { min-height: 205px; overflow: hidden; }
        .dashboard-bottom .card { padding: 16px 18px; }
        .alert-list { display: grid; gap: 8px; }
        .alert-row {
            display: grid;
            grid-template-columns: 38px 1fr auto;
            align-items: center;
            gap: 10px;
            min-height: 52px;
            padding: 8px 12px;
            border: 1px solid var(--line);
            border-radius: 8px;
            background: var(--surface);
        }
        .alert-row .alert-icon { width: 32px; height: 32px; display: grid; place-items: center; border-radius: 8px; background: var(--danger-soft); color: var(--red); }
        .alert-row p { color: #6d82ad; font-size: .82rem; }
        html[data-theme="dark"] .alert-row p { color: var(--muted); }
        .farm-table-card table { table-layout: fixed; }
        .farm-table-card th, .farm-table-card td { padding: 7px 10px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; font-size: .78rem; }
        .farm-name { display: flex; align-items: center; gap: 10px; }
        .farm-thumb { width: 34px; height: 24px; border-radius: 4px; object-fit: cover; }
        .status-text { display: inline-flex; align-items: center; gap: 6px; }
        .status-text::before { content: ''; width: 8px; height: 8px; border-radius: 50%; background: var(--green); }
        .status-text.maintenance::before { background: var(--amber); }
        .status-text.inactive::before { background: var(--red); }
        @media (max-width: 1450px) {
            .dashboard-screen { width: 100%; }
            .dashboard-hero { min-height: 145px; }
            .dashboard-kpi { padding: 14px 16px; }
            .kpi-icon { width: 48px; height: 48px; flex-basis: 48px; }
            .dashboard-main { grid-template-columns: minmax(0, 1.1fr) minmax(330px, .55fr); }
        }
        @media (max-width: 1180px) {
            .dashboard-screen { min-height: 0; grid-template-rows: auto; }
            .dashboard-kpis { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .dashboard-main, .dashboard-bottom { grid-template-columns: 1fr; }
            .dashboard-hero { min-height: 260px; }
            .dashboard-hero h1 { white-space: normal; }
            .chart-card, .summary-card { height: auto; min-height: 300px; }
            .chart-wrap { min-height: 220px; }
        }
        @media (max-width: 720px) {
            .dashboard-hero { display: grid; padding: 24px; }
            .hero-badge { display: none; }
            .dashboard-kpis { grid-template-columns: 1fr; }
            .dashboard-kpi { grid-template-columns: 58px 1fr; }
            .dashboard-kpi strong { font-size: 2rem; }
            .chart-card, .summary-card, .dashboard-bottom .card { padding: 18px; }
            .card-title { align-items: flex-start; flex-direction: column; }
            .summary-row { grid-template-columns: 30px 1fr; }
            .summary-row strong { grid-column: 2; }
        }
    </style>

    <div class="dashboard-screen">
        <section class="dashboard-hero">
            <div>
                <p class="eyebrow">RMGS - Registro y Monitoreo de Generacion Solar Guatemala</p>
                <h1>Energía solar para un mejor mañana</h1>
                <p class="subtitle">Monitoreamos hoy un Guatemala mas limpio y sostenible.</p>
            </div>
            <div class="hero-badge"><i data-lucide="map-pin"></i><span>Guatemala, un pais con mas energia limpia</span></div>
        </section>

        <section class="dashboard-kpis">
            <article class="card dashboard-kpi"><span class="kpi-icon"><i data-lucide="landmark"></i></span><div><span>Granjas solares</span><strong>{{ number_format($stats['farms']) }}</strong><small>Inventario <b>actual</b></small></div></article>
            <article class="card dashboard-kpi"><span class="kpi-icon blue"><i data-lucide="grid-2x2"></i></span><div><span>Paneles instalados</span><strong>{{ number_format($stats['panels']) }}</strong><small>Inventario <b>actual</b></small></div></article>
            <article class="card dashboard-kpi"><span class="kpi-icon blue"><i data-lucide="zap"></i></span><div><span>Capacidad instalada</span><strong>{{ number_format($stats['capacity_kw'], 1) }} kW</strong><small>Capacidad <b>registrada</b></small></div></article>
            <article class="card dashboard-kpi"><span class="kpi-icon"><i data-lucide="leaf"></i></span><div><span>CO2 evitado</span><strong data-dashboard-stat="co2">{{ number_format($stats['co2_tons'], 1) }} t</strong><small data-dashboard-trend="co2">{{ $co2Trend === null ? 'Sin periodo' : (($co2Trend >= 0 ? '+' : '').number_format($co2Trend, 1).'%') }} <b>{{ $co2Trend === null ? 'previo' : 'vs. mes anterior' }}</b></small></div></article>
        </section>

        <section class="dashboard-main">
            <article class="card chart-card">
                <div class="card-title">
                    <h2>Generacion real vs esperada</h2>
                    <form class="month-chip" method="get" action="{{ route('dashboard') }}" data-dashboard-period-form>
                        <select name="period" aria-label="Filtrar dashboard por mes" data-dashboard-period-select>
                            @foreach ($availablePeriods as $period)
                                <option value="{{ $period['value'] }}" @selected($selectedPeriod === $period['value'])>{{ ucfirst($period['label']) }}</option>
                            @endforeach
                        </select>
                    </form>
                </div>
                <div class="chart-wrap"><canvas id="generationChart"></canvas></div>
            </article>

            <aside class="card summary-card">
                <div class="card-title"><h2><i data-lucide="bar-chart-3"></i> Resumen nacional</h2></div>
                <div class="summary-list">
                    <div class="summary-row"><i data-lucide="zap"></i><span>Generacion del mes</span><strong data-dashboard-stat="actual">{{ number_format($stats['actual_kwh']) }} kWh</strong></div>
                    <div class="summary-row"><i data-lucide="bar-chart-3"></i><span>Promedio diario</span><strong data-dashboard-stat="daily">{{ number_format($dailyAverage) }} kWh</strong></div>
                    <div class="summary-row"><i data-lucide="leaf"></i><span>Hogares equivalentes</span><strong data-dashboard-stat="homes">~ {{ number_format($homesEquivalent) }}</strong></div>
                    <div class="summary-row"><i data-lucide="trees"></i><span>Arboles equivalentes</span><strong data-dashboard-stat="trees">~ {{ number_format($treesEquivalent) }}</strong></div>
                </div>
                <div class="sustainability-note"><i data-lucide="leaf"></i> Contribuyendo a un Guatemala mas sostenible.</div>
            </aside>
        </section>

        <section class="dashboard-bottom">
            <article class="card recent-alerts">
                <div class="card-title"><h2><i data-lucide="bell"></i> Alertas recientes</h2><a class="muted" href="{{ route('alerts.index') }}">Ver todas</a></div>
                <div class="alert-list" data-dashboard-alerts>
                    @forelse ($alerts->take(2) as $alert)
                        <div class="alert-row"><span class="alert-icon"><i data-lucide="triangle-alert"></i></span><div><strong>Produccion por debajo de lo esperado</strong><p>{{ $alert->solarFarm->name }}</p></div><span class="muted">{{ $alert->period->translatedFormat('M Y') }}</span></div>
                    @empty
                        <div class="alert-row"><span class="alert-icon"><i data-lucide="circle-check"></i></span><div><strong>Sin alertas activas</strong><p>Las granjas estan dentro del rango esperado.</p></div><span class="muted">Hoy</span></div>
                    @endforelse
                </div>
            </article>

            <article class="card farm-table-card">
                <div class="card-title"><h2>Granjas registradas</h2><a class="muted" href="{{ route('farms.index') }}">Ver todas</a></div>
                <table>
                    <thead><tr><th>Nombre</th><th>Departamento</th><th>Capacidad (kW)</th><th>Generacion (kWh)</th><th>Estado</th></tr></thead>
                    <tbody data-dashboard-farms>
                        @foreach ($featuredFarms as $farm)
                            <tr>
                                <td><span class="farm-name"><img class="farm-thumb" src="{{ asset('images/dashboard-hero-guatemala.png') }}" alt=""><strong>{{ $farm['name'] }}</strong></span></td>
                                <td>{{ $farm['department'] }}</td>
                                <td>{{ number_format($farm['capacity_kw'], 1) }}</td>
                                <td>{{ number_format($farm['generation_kwh']) }}</td>
                                <td><span class="status-text {{ $farm['status'] }}">{{ $farm['status_label'] }}</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </article>
        </section>
    </div>

    <script>
        const generationSeries = @json($generationSeries);
        const labels = Object.keys(generationSeries);
        const dashboardStyle = getComputedStyle(document.documentElement);
        const dashboardGrid = dashboardStyle.getPropertyValue('--line').trim();
        const dashboardText = dashboardStyle.getPropertyValue('--muted').trim();
        const dashboardPeriodUrl = @json(route('dashboard.period'));
        const numberFormatter = new Intl.NumberFormat('en-US');
        const decimalFormatter = new Intl.NumberFormat('en-US', { minimumFractionDigits: 1, maximumFractionDigits: 1 });

        const dashboardChart = new Chart(document.getElementById('generationChart'), {
            type: 'line',
            data: {
                labels,
                datasets: [
                    { label: 'Generacion real', data: labels.map(label => generationSeries[label].actual), borderColor: '#0aa574', backgroundColor: 'rgba(10,165,116,.12)', tension: .35, fill: true, borderWidth: 4, pointRadius: 3 },
                    { label: 'Generacion esperada', data: labels.map(label => generationSeries[label].expected), borderColor: '#1689f4', borderDash: [8,5], tension: .35, borderWidth: 3, pointRadius: 3 },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom', labels: { color: dashboardText, usePointStyle: true, padding: 18, font: { size: 12 } } } },
                scales: {
                    x: { grid: { color: dashboardGrid }, ticks: { color: dashboardText, maxRotation: 0 } },
                    y: { grid: { color: dashboardGrid }, ticks: { color: dashboardText } },
                },
            },
        });

        const periodForm = document.querySelector('[data-dashboard-period-form]');
        const periodSelect = document.querySelector('[data-dashboard-period-select]');
        const statsTargets = {
            actual: document.querySelector('[data-dashboard-stat="actual"]'),
            daily: document.querySelector('[data-dashboard-stat="daily"]'),
            homes: document.querySelector('[data-dashboard-stat="homes"]'),
            trees: document.querySelector('[data-dashboard-stat="trees"]'),
            co2: document.querySelector('[data-dashboard-stat="co2"]'),
        };
        const trendTargets = {
            co2: document.querySelector('[data-dashboard-trend="co2"]'),
        };
        const alertsTarget = document.querySelector('[data-dashboard-alerts]');
        const farmsTarget = document.querySelector('[data-dashboard-farms]');
        const farmThumbUrl = @json(asset('images/dashboard-hero-guatemala.png'));

        function escapeHtml(value) {
            return String(value)
                .replaceAll('&', '&amp;')
                .replaceAll('<', '&lt;')
                .replaceAll('>', '&gt;')
                .replaceAll('"', '&quot;')
                .replaceAll("'", '&#039;');
        }

        function trendLabel(value) {
            if (value === null || value === undefined) {
                return 'Sin periodo <b>previo</b>';
            }

            return `${value >= 0 ? '+' : ''}${decimalFormatter.format(value)}% <b>vs. mes anterior</b>`;
        }

        function updateDashboardStats(payload) {
            const stats = payload.stats;
            const series = payload.generation_series;
            const nextLabels = Object.keys(series);

            dashboardChart.data.labels = nextLabels;
            dashboardChart.data.datasets[0].data = nextLabels.map(label => series[label].actual);
            dashboardChart.data.datasets[1].data = nextLabels.map(label => series[label].expected);
            dashboardChart.update();

            statsTargets.actual.textContent = `${numberFormatter.format(stats.actual_kwh)} kWh`;
            statsTargets.daily.textContent = `${numberFormatter.format(stats.daily_average)} kWh`;
            statsTargets.homes.textContent = `~ ${numberFormatter.format(stats.homes_equivalent)}`;
            statsTargets.trees.textContent = `~ ${numberFormatter.format(stats.trees_equivalent)}`;
            statsTargets.co2.textContent = `${decimalFormatter.format(stats.co2_tons)} t`;
            trendTargets.co2.innerHTML = trendLabel(payload.trends.co2);

            alertsTarget.innerHTML = payload.alerts.length
                ? payload.alerts.map(alert => `
                    <div class="alert-row">
                        <span class="alert-icon"><i data-lucide="triangle-alert"></i></span>
                        <div><strong>Produccion por debajo de lo esperado</strong><p>${escapeHtml(alert.farm)}</p></div>
                        <span class="muted">${escapeHtml(alert.period)}</span>
                    </div>
                `).join('')
                : `
                    <div class="alert-row">
                        <span class="alert-icon"><i data-lucide="circle-check"></i></span>
                        <div><strong>Sin alertas activas</strong><p>Las granjas estan dentro del rango esperado.</p></div>
                        <span class="muted">Periodo</span>
                    </div>
                `;

            farmsTarget.innerHTML = payload.top_farms.map(farm => `
                <tr>
                    <td><span class="farm-name"><img class="farm-thumb" src="${farmThumbUrl}" alt=""><strong>${escapeHtml(farm.name)}</strong></span></td>
                    <td>${escapeHtml(farm.department)}</td>
                    <td>${decimalFormatter.format(farm.capacity_kw)}</td>
                    <td>${numberFormatter.format(farm.generation_kwh)}</td>
                    <td><span class="status-text ${escapeHtml(farm.status)}">${escapeHtml(farm.status_label)}</span></td>
                </tr>
            `).join('');

            if (window.lucide) {
                window.lucide.createIcons();
            }
        }

        periodForm?.addEventListener('submit', (event) => event.preventDefault());
        periodSelect?.addEventListener('change', async (event) => {
            const period = event.target.value;
            const url = new URL(dashboardPeriodUrl, window.location.origin);
            url.searchParams.set('period', period);
            periodForm.classList.add('is-loading');

            try {
                const response = await fetch(url, {
                    headers: { Accept: 'application/json' },
                });

                if (!response.ok) {
                    throw new Error('No se pudo cargar el periodo.');
                }

                updateDashboardStats(await response.json());
                window.history.replaceState({}, '', `${window.location.pathname}?period=${encodeURIComponent(period)}`);
            } catch (error) {
                console.error(error);
                periodForm.submit();
            } finally {
                periodForm.classList.remove('is-loading');
            }
        });
    </script>
@endsection
