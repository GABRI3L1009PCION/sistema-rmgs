@extends('layouts.app')

@section('content')
    <style>
        body:has(.farms-screen) { overflow: hidden; }
        .farms-screen { height: calc(100dvh - 86px); min-height: 650px; display: grid; grid-template-rows: clamp(132px, 16vh, 152px) 112px minmax(360px, 1fr); gap: 10px; }
        .farms-hero { position: relative; overflow: hidden; display: flex; align-items: center; padding: 24px 28px; border-radius: 8px; color: white; background: linear-gradient(90deg, rgba(4,25,55,.82), rgba(4,25,55,.28), rgba(4,25,55,.05)), url('{{ asset('images/dashboard-hero-guatemala.png') }}') center 58% / cover; box-shadow: var(--shadow); }
        .farms-hero-copy { position: relative; z-index: 1; }
        .farms-hero .eyebrow { margin-bottom: 8px; font-size: .72rem; opacity: .95; }
        .farms-hero h1 { font-size: clamp(2rem, 2.4vw, 2.55rem); line-height: 1; }
        .farms-hero .subtitle { margin-top: 8px; font-size: .92rem; }
        .farms-hero .hero-note { position: absolute; top: 18px; right: 22px; display: flex; gap: 8px; align-items: flex-start; max-width: 190px; font-size: .7rem; font-weight: 800; }
        .farms-hero .hero-note svg { width: 19px; flex: 0 0 auto; }
        .farm-kpis { grid-template-columns: repeat(4, minmax(0, 1fr)); }
        .farm-kpi { min-width: 0; display: grid; grid-template-columns: 58px minmax(0, 1fr); align-items: center; gap: 13px; padding: 13px 16px; }
        .farm-kpi-icon { width: 56px; height: 56px; display: grid; place-items: center; border-radius: 8px; color: var(--blue); background: var(--blue-soft); }
        .farm-kpi-icon.green { color: var(--green-dark); background: var(--mint); }
        .farm-kpi-icon svg { width: 29px; height: 29px; }
        .farm-kpi-label { margin-bottom: 5px; font-size: .75rem; color: var(--ink); white-space: nowrap; }
        .farm-kpi-value { font-size: clamp(1.35rem, 1.7vw, 1.85rem); line-height: 1; font-weight: 900; white-space: nowrap; }
        .farm-kpi-trend { margin-top: 6px; color: var(--green); font-size: .76rem; font-weight: 900; }
        .farm-kpi-trend span { margin-left: 6px; color: var(--muted); font-size: .64rem; font-weight: 700; }
        .farms-content { min-height: 0; display: grid; grid-template-columns: minmax(0, 1.65fr) minmax(330px, .75fr); gap: 10px; }
        .farms-list-card, .farm-map-card { min-height: 0; overflow: hidden; }
        .farms-list-card { display: grid; grid-template-rows: auto auto minmax(0, 1fr) auto; padding: 14px 16px 10px; }
        .list-heading { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 9px; }
        .list-heading-actions { display: flex; align-items: center; gap: 8px; flex: 0 0 auto; }
        .list-heading h2 { font-size: 1.12rem; }
        .list-heading p { margin-top: 3px; }
        .farm-filters { display: grid; grid-template-columns: .95fr .95fr .85fr 1.05fr; gap: 10px; margin-bottom: 10px; }
        .farm-filters label { gap: 3px; font-size: .68rem; }
        .filter-box { position: relative; }
        .filter-box > svg { position: absolute; z-index: 1; left: 10px; top: 50%; width: 17px; transform: translateY(-50%); color: var(--muted); pointer-events: none; }
        .farm-filters select, .farm-filters input { min-height: 36px; height: 36px; padding: 5px 10px 5px 34px; font-size: .72rem; }
        .farms-table-wrap { min-height: 0; overflow: hidden; border: 1px solid var(--line); border-radius: 7px; }
        .farms-table { table-layout: fixed; font-size: .68rem; }
        .farms-table th, .farms-table td { height: 34px; padding: 4px 8px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .farms-table th { background: #f5f9fd; }
        .farm-name { display: flex; align-items: center; gap: 8px; min-width: 0; }
        .farm-thumb { width: 34px; height: 23px; flex: 0 0 auto; border-radius: 4px; object-fit: cover; }
        .farm-status { display: inline-flex; align-items: center; gap: 6px; font-weight: 800; }
        .farm-status::before { content: ''; width: 9px; height: 9px; border-radius: 50%; background: var(--green); }
        .farm-status.maintenance { color: #c88400; }
        .farm-status.maintenance::before { background: var(--amber); }
        .farm-status.inactive { color: var(--red); }
        .farm-status.inactive::before { background: var(--red); }
        .farm-actions { display: flex; align-items: center; justify-content: flex-end; gap: 5px; }
        .icon-action { width: 27px; height: 27px; display: inline-grid; place-items: center; padding: 0; border: 0; border-radius: 6px; color: #4d6591; background: transparent; cursor: pointer; }
        .icon-action:hover { color: var(--blue); background: var(--blue-soft); }
        .icon-action.danger:hover { color: var(--red); background: #fff1f0; }
        .icon-action svg { width: 16px; }
        .table-footer { display: flex; align-items: center; justify-content: space-between; padding-top: 8px; font-size: .68rem; color: var(--muted); }
        .page-pill { min-width: 34px; height: 30px; display: grid; place-items: center; border-radius: 7px; background: var(--mint); color: var(--green-dark); font-weight: 900; }
        .farm-map-card { display: grid; grid-template-rows: 34px minmax(0, 1fr) 32px; padding: 12px; }
        .map-card-title { display: flex; align-items: center; justify-content: space-between; gap: 8px; font-size: .72rem; }
        .map-card-title h2 { display: flex; align-items: center; gap: 7px; font-size: .92rem; }
        .map-card-title svg { width: 18px; color: var(--blue); }
        .map-card-title a { color: var(--blue); font-weight: 800; }
        #farms-map { min-height: 0; height: 100%; border: 0; border-radius: 7px; }
        .map-legend { display: flex; align-items: end; justify-content: center; gap: 24px; color: var(--muted); font-size: .66rem; }
        .legend-dot { display: inline-block; width: 9px; height: 9px; margin-right: 5px; border-radius: 50%; background: var(--green); }
        .legend-dot.maintenance { background: var(--amber); }
        @media (max-width: 1180px) {
            body:has(.farms-screen) { overflow: auto; }
            .farms-screen { height: auto; min-height: 0; grid-template-rows: auto; }
            .farm-kpis { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .farms-content { grid-template-columns: 1fr; }
            .farms-list-card { min-height: 480px; }
            .farm-map-card { min-height: 480px; }
        }
        @media (max-width: 700px) { .farm-kpis, .farm-filters { grid-template-columns: 1fr; } .farms-hero .hero-note { display: none; } }
    </style>

    <div class="farms-screen">
        <section class="farms-hero">
            <div class="farms-hero-copy">
                <p class="eyebrow">RMGS - Registro y Monitoreo de Generacion Solar Guatemala</p>
                <h1>Granjas solares</h1>
                <p class="subtitle">Gestiona y consulta todas las granjas solares registradas en Guatemala.</p>
            </div>
            <div class="hero-note"><i data-lucide="map-pin"></i><span>Ubicaciones y operacion por departamento</span></div>
        </section>

        <section class="grid farm-kpis">
            <article class="card farm-kpi"><span class="farm-kpi-icon green"><i data-lucide="landmark"></i></span><div><p class="farm-kpi-label">Total de granjas</p><p class="farm-kpi-value">{{ number_format($stats['farms']) }}</p><p class="farm-kpi-trend">Registro nacional</p></div></article>
            <article class="card farm-kpi"><span class="farm-kpi-icon"><i data-lucide="zap"></i></span><div><p class="farm-kpi-label">Capacidad total instalada</p><p class="farm-kpi-value">{{ number_format($stats['capacity_kw'], 1) }} kW</p><p class="farm-kpi-trend">Suma de paneles</p></div></article>
            <article class="card farm-kpi"><span class="farm-kpi-icon"><i data-lucide="grid-2x2"></i></span><div><p class="farm-kpi-label">Total de paneles</p><p class="farm-kpi-value">{{ number_format($stats['panels']) }}</p><p class="farm-kpi-trend">Instalados</p></div></article>
            <article class="card farm-kpi"><span class="farm-kpi-icon green"><i data-lucide="leaf"></i></span><div><p class="farm-kpi-label">CO2 evitado (estimado)</p><p class="farm-kpi-value">{{ number_format($stats['co2_tons'], 1) }} t</p><p class="farm-kpi-trend">Factor 0.40 kg/kWh</p></div></article>
        </section>

        <section class="farms-content">
            <article class="card farms-list-card">
                <header class="list-heading">
                    <div>
                        <h2>Listado de granjas solares</h2>
                        <p class="muted"><span id="visible-farm-count">{{ $farms->count() }}</span> granjas registradas en el sistema</p>
                    </div>
                    <div class="list-heading-actions">
                        <a class="btn primary" href="{{ route('farms.create') }}"><i data-lucide="plus"></i>Nueva granja</a>
                    </div>
                </header>
                <div class="farm-filters">
                    <label>Departamento<span class="filter-box"><i data-lucide="map-pin"></i><select id="department-filter"><option value="">Todos los departamentos</option>@foreach ($departments as $department)<option value="{{ $department->name }}" data-department-id="{{ $department->id }}">{{ $department->name }}</option>@endforeach</select></span></label>
                    <label>Municipio<span class="filter-box"><i data-lucide="map"></i><select id="municipality-filter"><option value="">Todos los municipios</option></select></span></label>
                    <label>Estado<span class="filter-box"><i data-lucide="circle"></i><select id="status-filter"><option value="">Todos los estados</option><option value="active">Activas</option><option value="maintenance">En mantenimiento</option><option value="inactive">Inactivas</option></select></span></label>
                    <label>Busqueda<span class="filter-box"><i data-lucide="search"></i><input id="farm-search" type="search" placeholder="Buscar por nombre..."></span></label>
                </div>
                <div class="farms-table-wrap">
                    <table class="farms-table">
                        <thead><tr><th style="width:25%">Nombre</th><th style="width:17%">Departamento</th><th style="width:16%">Capacidad (kW)</th><th style="width:12%">Paneles</th><th style="width:17%">Estado</th><th style="width:13%; text-align:right">Acciones</th></tr></thead>
                        <tbody id="farms-table-body">
                            @foreach ($farms as $farm)
                                @php $panelCount = $farm->installedPanelsCount(); @endphp
                                <tr data-farm-id="{{ $farm->id }}" data-name="{{ strtolower($farm->name) }}" data-department="{{ $farm->department->name }}" data-municipality="{{ $farm->municipality }}" data-status="{{ $farm->status }}">
                                    <td><span class="farm-name"><img class="farm-thumb" src="{{ asset('images/dashboard-hero-guatemala.png') }}" alt=""><strong>{{ $farm->name }}</strong></span></td>
                                    <td>{{ $farm->department->name }}</td><td>{{ number_format($farm->installedCapacityKw(), 1) }}</td><td>{{ number_format($panelCount) }}</td>
                                    <td><span class="farm-status {{ $farm->status }}">{{ $farm->status === 'maintenance' ? 'En mantenimiento' : ($farm->status === 'active' ? 'Activa' : 'Inactiva') }}</span></td>
                                    <td><span class="farm-actions"><button class="icon-action" type="button" data-focus-farm="{{ $farm->id }}" title="Ver ubicacion"><i data-lucide="map-pin"></i></button><a class="icon-action" href="{{ route('farms.show', $farm) }}" title="Ver detalle"><i data-lucide="eye"></i></a><a class="icon-action" href="{{ route('farms.edit', $farm) }}" title="Editar granja"><i data-lucide="pencil"></i></a>@if ($farm->status !== 'inactive')<form method="post" action="{{ route('farms.deactivate', $farm) }}">@csrf @method('PATCH')<button class="icon-action danger" type="submit" title="Desactivar granja"><i data-lucide="power"></i></button></form>@endif</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <footer class="table-footer"><span>Mostrando <strong id="footer-farm-count">{{ $farms->count() }}</strong> de {{ $farms->count() }} granjas</span><span class="page-pill">1</span></footer>
            </article>

            <article class="card farm-map-card">
                <header class="map-card-title"><h2><i data-lucide="map"></i>Ubicacion de granjas</h2><a href="#farms-map">Ver mapa completo</a></header>
                <div id="farms-map"></div>
                <footer class="map-legend"><span><i class="legend-dot"></i>Granja activa</span><span><i class="legend-dot maintenance"></i>En mantenimiento</span></footer>
            </article>
        </section>
    </div>

    <script>
        const farmData = @json($mapFarms);
        const municipalitiesByDepartment = @json($municipalitiesByDepartment);
        const farmsMap = L.map('farms-map', { zoomControl: true }).setView([15.4, -90.3], 7);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 18, attribution: '&copy; OpenStreetMap' }).addTo(farmsMap);
        const farmMarkers = new Map();
        farmData.forEach(farm => {
            const color = farm.status === 'maintenance' ? '#f2a900' : (farm.status === 'inactive' ? '#ef4444' : '#0aa574');
            const marker = L.circleMarker([farm.lat, farm.lng], { radius: 8, color: '#fff', weight: 3, fillColor: color, fillOpacity: 1 }).addTo(farmsMap).bindPopup(`<strong>${farm.name}</strong><br>${farm.department} / ${farm.municipality}<br>${Number(farm.capacity_kw).toFixed(1)} kW`);
            farmMarkers.set(Number(farm.id), marker);
        });
        const rows = [...document.querySelectorAll('#farms-table-body tr')];
        const departmentFilter = document.getElementById('department-filter');
        const municipalityFilter = document.getElementById('municipality-filter');
        const statusFilter = document.getElementById('status-filter');
        const farmSearch = document.getElementById('farm-search');

        function refreshMunicipalityFilter() {
            const selectedOption = departmentFilter.selectedOptions[0];
            const departmentId = selectedOption?.dataset.departmentId;
            const municipalities = departmentId ? (municipalitiesByDepartment[departmentId] || []) : [];
            municipalityFilter.innerHTML = '<option value="">Todos los municipios</option>';
            municipalities.forEach(municipality => {
                const option = document.createElement('option');
                option.value = municipality;
                option.textContent = municipality;
                municipalityFilter.appendChild(option);
            });
            municipalityFilter.disabled = municipalities.length === 0;
        }

        function syncMapMarkers(visibleIds) {
            const visibleMarkers = [];
            farmMarkers.forEach((marker, farmId) => {
                if (visibleIds.has(farmId)) {
                    if (!farmsMap.hasLayer(marker)) marker.addTo(farmsMap);
                    visibleMarkers.push(marker);
                    return;
                }

                if (farmsMap.hasLayer(marker)) farmsMap.removeLayer(marker);
            });

            if (visibleMarkers.length > 0) {
                const bounds = L.featureGroup(visibleMarkers).getBounds().pad(0.2);
                farmsMap.fitBounds(bounds, { maxZoom: 11, animate: true });
                return;
            }

            farmsMap.setView([15.4, -90.3], 7);
        }

        function filterFarms() {
            const query = farmSearch.value.trim().toLowerCase();
            let visible = 0;
            const visibleIds = new Set();
            rows.forEach(row => {
                const show = (!departmentFilter.value || row.dataset.department === departmentFilter.value)
                    && (!municipalityFilter.value || row.dataset.municipality === municipalityFilter.value)
                    && (!statusFilter.value || row.dataset.status === statusFilter.value)
                    && (!query || row.dataset.name.includes(query));
                row.hidden = !show;
                if (show) {
                    visible++;
                    visibleIds.add(Number(row.dataset.farmId));
                }
            });
            document.getElementById('visible-farm-count').textContent = visible;
            document.getElementById('footer-farm-count').textContent = visible;
            syncMapMarkers(visibleIds);
        }
        departmentFilter.addEventListener('input', () => {
            refreshMunicipalityFilter();
            filterFarms();
        });
        [municipalityFilter, statusFilter, farmSearch].forEach(control => control.addEventListener('input', filterFarms));
        document.querySelectorAll('[data-focus-farm]').forEach(button => button.addEventListener('click', () => {
            const marker = farmMarkers.get(Number(button.dataset.focusFarm));
            if (marker) {
                if (!farmsMap.hasLayer(marker)) marker.addTo(farmsMap);
                farmsMap.setView(marker.getLatLng(), 11);
                marker.openPopup();
            }
        }));
        refreshMunicipalityFilter();
        filterFarms();
        window.setTimeout(() => {
            farmsMap.invalidateSize();
            filterFarms();
        }, 100);
    </script>
@endsection
