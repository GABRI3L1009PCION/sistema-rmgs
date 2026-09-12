@extends('layouts.app')

@section('content')
    <style>
        body:has(.generation-screen) { overflow: auto; }
        .generation-screen { display: grid; gap: 12px; }
        .generation-hero { min-height: 190px; position: relative; overflow: hidden; display: flex; align-items: center; justify-content: space-between; gap: 20px; padding: 26px 32px; border-radius: 8px; color: white; background: linear-gradient(90deg, rgba(4,25,55,.86), rgba(4,25,55,.34), rgba(4,25,55,.08)), url('{{ asset('images/dashboard-hero-guatemala.png') }}') center 58% / cover; box-shadow: var(--shadow); }
        .generation-hero h1 { font-size: clamp(2.2rem, 3vw, 3.4rem); line-height: 1; }
        .generation-hero p { margin-top: 10px; max-width: 560px; font-size: 1rem; color: rgba(255,255,255,.92); }
        .generation-hero-note { display: grid; grid-template-columns: 28px 1fr; gap: 10px; max-width: 260px; font-weight: 900; text-shadow: 0 1px 12px rgba(0,0,0,.35); }
        .generation-hero-note svg { width: 26px; }
        .generation-toolbar { display: grid; grid-template-columns: repeat(3, minmax(0,1fr)); gap: 12px; }
        .filter-card label { font-size: .72rem; }
        .metric-row { grid-template-columns: repeat(4, minmax(0, 1fr)); }
        .metric-card { display: grid; grid-template-columns: 44px 1fr; gap: 12px; align-items: center; }
        .metric-card i { width: 44px; height: 44px; display: grid; place-items: center; border-radius: 8px; background: var(--blue-soft); color: var(--blue); }
        .metric-card i.green { background: var(--mint); color: var(--green-dark); }
        .metric-card strong { display: block; font-size: 1.45rem; line-height: 1; }
        .generation-layout { display: grid; grid-template-columns: minmax(0, 1.25fr) minmax(360px, .75fr); gap: 12px; }
        .chart-panel { min-height: 360px; display: grid; grid-template-rows: auto minmax(0,1fr); }
        .chart-wrap { min-height: 0; }
        .chart-wrap canvas { width: 100% !important; height: 100% !important; }
        .records-table td, .records-table th { white-space: nowrap; }
        .record-actions { display: flex; gap: 6px; justify-content: flex-end; }
        .record-actions form { margin: 0; }
        .difference-positive { color: var(--green); font-weight: 900; }
        .difference-negative { color: var(--red); font-weight: 900; }
        @media (max-width: 1100px) {
            .generation-toolbar, .metric-row, .generation-layout { grid-template-columns: 1fr; }
            .generation-hero { display: grid; }
        }
    </style>

    <div class="generation-screen">
        <section class="generation-hero">
            <div>
                <span>RMGS - Bitácora energética</span>
                <h1>Generación mensual</h1>
                <p>Compara lecturas reales contra metas esperadas y corrige históricos desde una sola vista operativa.</p>
            </div>
            <div class="generation-hero-note"><i data-lucide="activity"></i><span>Las alertas se recalculan al guardar cada lectura.</span></div>
        </section>

        <section class="card filter-card generation-toolbar">
            <label>Periodo
                <select id="period-filter">
                    <option value="">Todos los periodos</option>
                    @foreach($records->groupBy(fn($record) => $record->period->format('Y-m')) as $period => $items)
                        <option value="{{ $period }}">{{ $items->first()->period->format('m/Y') }}</option>
                    @endforeach
                </select>
            </label>
            <label>Departamento
                <select id="generation-department-filter">
                    <option value="">Todos los departamentos</option>
                    @foreach($departments as $department)<option value="{{ $department->name }}">{{ $department->name }}</option>@endforeach
                </select>
            </label>
            <label>Granja
                <select id="generation-farm-filter">
                    <option value="">Todas las granjas</option>
                    @foreach($farms as $farm)<option value="{{ $farm->name }}">{{ $farm->name }}</option>@endforeach
                </select>
            </label>
        </section>

        <section class="grid metric-row">
            <article class="card metric-card"><i class="green" data-lucide="zap"></i><div><span class="muted">Generación total</span><strong id="generation-total">{{ number_format($stats['actual_kwh']) }} kWh</strong></div></article>
            <article class="card metric-card"><i data-lucide="bar-chart-3"></i><div><span class="muted">Promedio diario</span><strong id="generation-daily-average">{{ number_format($stats['daily_average']) }} kWh</strong></div></article>
            <article class="card metric-card"><i data-lucide="target"></i><div><span class="muted">Cumplimiento</span><strong id="generation-compliance">{{ number_format($stats['compliance'], 1) }}%</strong></div></article>
            <article class="card metric-card"><i class="green" data-lucide="leaf"></i><div><span class="muted">CO2 evitado</span><strong id="generation-co2">{{ number_format($stats['co2_tons'], 1) }} t</strong></div></article>
        </section>

        <section class="generation-layout">
            <article class="card chart-panel">
                <div class="card-title"><h2>Tendencia mensual</h2><span class="muted">Real vs esperada</span></div>
                <div class="chart-wrap"><canvas id="generation-history-chart"></canvas></div>
            </article>
            <article class="card chart-panel">
                <div class="card-title"><h2>Producción por granja</h2><span class="muted" id="farm-chart-period">{{ $latestPeriod?->format('m/Y') ?? 'Sin periodo' }}</span></div>
                <div class="chart-wrap"><canvas id="generation-farm-chart"></canvas></div>
            </article>
        </section>

        <section class="card">
            <div class="card-title"><h2>Registros históricos</h2><span class="muted">Lecturas cargadas desde la base de datos</span></div>
            <table class="records-table">
                <thead><tr><th>Periodo</th><th>Granja</th><th>Departamento</th><th>Real</th><th>Esperada</th><th>Diferencia</th><th>Cumplimiento</th></tr></thead>
                <tbody>
                    @foreach($records as $record)
                        @php
                            $difference = $record->actual_kwh - $record->expected_kwh;
                            $compliance = $record->expected_kwh > 0 ? ($record->actual_kwh / $record->expected_kwh) * 100 : 0;
                        @endphp
                        <tr class="generation-record" data-record-id="{{ $record->id }}" data-period="{{ $record->period->format('Y-m') }}" data-department="{{ $record->solarFarm->department->name }}" data-farm="{{ $record->solarFarm->name }}" data-actual="{{ $record->actual_kwh }}" data-expected="{{ $record->expected_kwh }}" data-co2="{{ $record->co2_avoided_kg }}">
                            <td>{{ $record->period->format('m/Y') }}</td>
                            <td><strong>{{ $record->solarFarm->name }}</strong></td>
                            <td>{{ $record->solarFarm->department->name }}</td>
                            <td>{{ number_format($record->actual_kwh) }} kWh</td>
                            <td>{{ number_format($record->expected_kwh) }} kWh</td>
                            <td class="{{ $difference >= 0 ? 'difference-positive' : 'difference-negative' }}">{{ $difference >= 0 ? '+' : '' }}{{ number_format($difference) }} kWh</td>
                            <td>{{ number_format($compliance, 1) }}%</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </section>
    </div>

    <script>
        const generationSeries = @json($generationSeries);
        const farmSeries = @json($farmSeries);
        const generationRecords = @json($recordData);
        const historyLabels = Object.keys(generationSeries);

        const numberFormatter = new Intl.NumberFormat('en-US', { maximumFractionDigits: 0 });
        const decimalFormatter = new Intl.NumberFormat('en-US', { minimumFractionDigits: 1, maximumFractionDigits: 1 });
        const totalOutput = document.getElementById('generation-total');
        const dailyOutput = document.getElementById('generation-daily-average');
        const complianceOutput = document.getElementById('generation-compliance');
        const co2Output = document.getElementById('generation-co2');
        const farmChartPeriod = document.getElementById('farm-chart-period');

        const historyChart = new Chart(document.getElementById('generation-history-chart'), {
            type: 'line',
            data: {
                labels: historyLabels,
                datasets: [
                    { label: 'Real', data: historyLabels.map(key => generationSeries[key].actual), borderColor: '#0aa574', backgroundColor: 'rgba(10,165,116,.1)', fill: true, tension: .35, borderWidth: 3 },
                    { label: 'Esperada', data: historyLabels.map(key => generationSeries[key].expected), borderColor: '#1689f4', borderDash: [7,5], tension: .35, borderWidth: 2 },
                ],
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } },
        });

        const farmChart = new Chart(document.getElementById('generation-farm-chart'), {
            type: 'bar',
            data: { labels: farmSeries.map(item => item.name), datasets: [{ data: farmSeries.map(item => item.actual), backgroundColor: '#1689f4', borderRadius: 4 }] },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } },
        });

        const generationRows = [...document.querySelectorAll('.generation-record')];
        const periodFilter = document.getElementById('period-filter');
        const departmentFilter = document.getElementById('generation-department-filter');
        const farmFilter = document.getElementById('generation-farm-filter');

        function selectedRecords() {
            return generationRecords.filter(record => (!periodFilter.value || record.period === periodFilter.value)
                && (!departmentFilter.value || record.department === departmentFilter.value)
                && (!farmFilter.value || record.farm === farmFilter.value));
        }

        function refreshFarmOptions() {
            const currentFarm = farmFilter.value;
            const farmNames = [...new Set(generationRecords
                .filter(record => !departmentFilter.value || record.department === departmentFilter.value)
                .map(record => record.farm))]
                .sort();

            farmFilter.innerHTML = '<option value="">Todas las granjas</option>';
            farmNames.forEach(farmName => {
                const option = document.createElement('option');
                option.value = farmName;
                option.textContent = farmName;
                option.selected = farmName === currentFarm;
                farmFilter.appendChild(option);
            });

            if (currentFarm && !farmNames.includes(currentFarm)) {
                farmFilter.value = '';
            }
        }

        function groupRecords(records, key, mapper) {
            return records.reduce((groups, record) => {
                const groupKey = mapper ? mapper(record) : record[key];
                groups[groupKey] ??= [];
                groups[groupKey].push(record);
                return groups;
            }, {});
        }

        function sum(records, field) {
            return records.reduce((total, record) => total + Number(record[field] || 0), 0);
        }

        function updateMetrics(records) {
            const actual = sum(records, 'actual');
            const expected = sum(records, 'expected');
            const co2Tons = sum(records, 'co2_kg') / 1000;
            const periodCount = Math.max(new Set(records.map(record => record.period)).size, 1);
            const dailyAverage = actual / (periodCount * 30);
            const compliance = expected > 0 ? (actual / expected) * 100 : 0;

            totalOutput.textContent = `${numberFormatter.format(actual)} kWh`;
            dailyOutput.textContent = `${numberFormatter.format(dailyAverage)} kWh`;
            complianceOutput.textContent = `${decimalFormatter.format(compliance)}%`;
            co2Output.textContent = `${decimalFormatter.format(co2Tons)} t`;
        }

        function updateHistoryChart(records) {
            const periodGroups = groupRecords(records, 'period');
            const labels = Object.keys(periodGroups).sort();

            historyChart.data.labels = labels;
            historyChart.data.datasets[0].data = labels.map(period => sum(periodGroups[period], 'actual'));
            historyChart.data.datasets[1].data = labels.map(period => sum(periodGroups[period], 'expected'));
            historyChart.update();
        }

        function updateFarmChart(records) {
            const farmGroups = groupRecords(records, 'farm');
            const labels = Object.keys(farmGroups).sort();

            farmChart.data.labels = labels;
            farmChart.data.datasets[0].data = labels.map(farm => sum(farmGroups[farm], 'actual'));
            farmChart.update();

            if (periodFilter.value) {
                const selected = generationRecords.find(record => record.period === periodFilter.value);
                farmChartPeriod.textContent = selected?.period_label ?? periodFilter.value;
                return;
            }

            farmChartPeriod.textContent = 'Periodos filtrados';
        }

        function filterRecords() {
            const records = selectedRecords();
            const visibleIds = new Set(records.map(record => String(record.id)));

            generationRows.forEach(row => {
                row.hidden = !visibleIds.has(row.dataset.recordId);
            });

            updateMetrics(records);
            updateHistoryChart(records);
            updateFarmChart(records);
        }

        periodFilter.addEventListener('input', filterRecords);
        departmentFilter.addEventListener('input', () => {
            refreshFarmOptions();
            filterRecords();
        });
        farmFilter.addEventListener('input', filterRecords);
        refreshFarmOptions();
        filterRecords();
    </script>
@endsection
