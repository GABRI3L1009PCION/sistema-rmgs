@extends('layouts.app')

@section('content')
    <section class="grid stats">
        <article class="card stat"><p class="label">Granjas solares</p><p class="value">{{ $stats['farms'] }}</p></article>
        <article class="card stat"><p class="label">Paneles instalados</p><p class="value">{{ number_format($stats['panels']) }}</p></article>
        <article class="card stat"><p class="label">Capacidad instalada</p><p class="value">{{ number_format($stats['capacity_kw'], 1) }} <span class="unit">kW</span></p></article>
        <article class="card stat"><p class="label">CO2 evitado</p><p class="value">{{ number_format($stats['co2_tons'], 1) }} <span class="unit">t</span></p></article>
    </section>

    <section class="grid two section">
        <article class="card">
            <h2>Generacion real vs esperada</h2>
            <canvas id="generationChart" height="130"></canvas>
        </article>
        <article class="card">
            <h2>Resumen nacional</h2>
            <table>
                <tr><th>Indicador</th><th>Valor</th></tr>
                <tr><td>Generacion acumulada</td><td>{{ number_format($stats['actual_kwh'], 2) }} kWh</td></tr>
                <tr><td>Generacion esperada</td><td>{{ number_format($stats['expected_kwh'], 2) }} kWh</td></tr>
                <tr><td>Familias beneficiadas</td><td>{{ number_format($stats['families']) }}</td></tr>
                <tr><td>Alertas activas</td><td><span class="pill {{ $alerts->count() ? 'alert' : 'ok' }}">{{ $alerts->count() }}</span></td></tr>
                <tr><td>Proyeccion</td><td>Promedio movil simple de ultimos 3 periodos</td></tr>
            </table>
        </article>
    </section>

    <section class="card section">
        <h2>Mapa interactivo de granjas solares</h2>
        <div id="map"></div>
    </section>

    <section class="grid two section">
        <article class="card">
            <h2>Ranking por departamento</h2>
            <table>
                <thead>
                    <tr>
                        <th>Departamento</th><th>Granjas</th><th>Paneles</th><th>kWh</th><th>CO2 t</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($departmentReports->take(8) as $report)
                        <tr>
                            <td>{{ $report['department'] }}</td>
                            <td>{{ $report['farms'] }}</td>
                            <td>{{ number_format($report['panels']) }}</td>
                            <td>{{ number_format($report['actual_kwh']) }}</td>
                            <td>{{ number_format($report['co2_tons'], 1) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </article>
        <article class="card">
            <h2>Alertas de desempeno</h2>
            <table>
                <thead>
                    <tr><th>Granja</th><th>Periodo</th><th>Desviacion</th></tr>
                </thead>
                <tbody>
                    @forelse ($alerts as $alert)
                        <tr>
                            <td>{{ $alert->solarFarm->name }}</td>
                            <td>{{ $alert->period->format('Y-m') }}</td>
                            <td><span class="pill alert">{{ number_format($alert->deviation_percent, 1) }}%</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="3">Sin alertas activas.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </article>
    </section>

    <section class="card section">
        <h2>Granjas registradas y proyeccion</h2>
        <table>
            <thead>
                <tr>
                    <th>Granja</th><th>Departamento</th><th>Capacidad</th><th>Familias</th><th>Proyeccion kWh</th><th>Estado</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($farms as $farm)
                    <tr>
                        <td>{{ $farm->name }}</td>
                        <td>{{ $farm->department->name }}</td>
                        <td>{{ number_format($farm->installedCapacityKw(), 1) }} kW</td>
                        <td>{{ number_format($farm->families_benefited) }}</td>
                        <td>{{ number_format($farm->projectedGenerationKwh(), 1) }}</td>
                        <td><span class="pill ok">{{ $farm->status }}</span></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </section>

    <script>
        const generationSeries = @json($generationSeries);
        const labels = Object.keys(generationSeries);
        new Chart(document.getElementById('generationChart'), {
            type: 'line',
            data: {
                labels,
                datasets: [
                    { label: 'Real kWh', data: labels.map(label => generationSeries[label].actual), borderColor: '#1f7a4d', backgroundColor: '#1f7a4d22', tension: .25, fill: true },
                    { label: 'Esperada kWh', data: labels.map(label => generationSeries[label].expected), borderColor: '#c77700', backgroundColor: '#c7770022', tension: .25, fill: true }
                ]
            },
            options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
        });

        const farms = @json($mapFarms);
        const map = L.map('map').setView([15.2, -90.4], 7);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 18,
            attribution: '&copy; OpenStreetMap'
        }).addTo(map);
        farms.forEach(farm => {
            L.marker([farm.lat, farm.lng]).addTo(map).bindPopup(`
                <strong>${farm.name}</strong><br>
                ${farm.municipality}, ${farm.department}<br>
                Capacidad: ${Number(farm.capacity_kw).toFixed(1)} kW<br>
                Familias: ${farm.families}<br>
                Proyeccion: ${Number(farm.projection).toFixed(1)} kWh
            `);
        });
    </script>
@endsection
