@extends('layouts.app')

@section('content')
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.Default.css">
    <style>
        body:has(.map-screen) { overflow: hidden; }
        .map-screen { height: calc(100dvh - 86px); min-height: 650px; display: grid; grid-template-rows: 118px 50px minmax(0,1fr); gap: 10px; }
        .map-hero { position: relative; overflow: hidden; display: flex; align-items: center; justify-content: space-between; gap: 20px; padding: 20px 28px; border-radius: 8px; color: white; background: linear-gradient(90deg,rgba(4,25,55,.86),rgba(4,25,55,.35),rgba(4,25,55,.06)),url('{{ asset('images/dashboard-hero-guatemala.png') }}') center 58%/cover; box-shadow: var(--shadow); }
        .map-hero::after { content: ''; position: absolute; inset: 0; background: linear-gradient(110deg, transparent 0%, rgba(255,255,255,.16) 46%, transparent 58%); transform: translateX(-120%); animation: map-hero-sheen 5.8s ease-in-out infinite; pointer-events: none; }
        .map-hero > * { position: relative; z-index: 1; }
        .map-hero .eyebrow { margin-bottom: 5px; font-size: .72rem; opacity: .95; }
        .map-hero h1 { font-size: clamp(2rem,2.4vw,2.7rem); line-height: 1; }
        .map-hero .subtitle { margin-top: 7px; font-size: .9rem; color: rgba(255,255,255,.92); }
        .map-hero .hero-note { display: flex; align-items: center; gap: 8px; max-width: 240px; font-size: .74rem; font-weight: 900; text-shadow: 0 1px 12px rgba(0,0,0,.35); }
        .map-hero .hero-note svg { width: 21px; }
        .map-toolbar { display: grid; grid-template-columns: minmax(290px,.42fr) minmax(280px,.35fr) minmax(0,1fr); gap: 10px; align-items: stretch; }
        .map-control { position: relative; display: flex; align-items: center; gap: 10px; min-height: 50px; padding: 0 13px; border: 1px solid var(--line); border-radius: 8px; background: var(--surface); box-shadow: var(--shadow); }
        .map-control svg { width: 19px; color: var(--blue); flex: 0 0 auto; }
        .map-control input, .map-control select { min-height: 36px; height: 36px; padding: 0; border: 0; background: transparent; font-size: .78rem; outline: 0; }
        .map-help { display: flex; align-items: center; gap: 10px; min-height: 50px; padding: 0 14px; border-radius: 8px; color: var(--muted); background: color-mix(in srgb, var(--blue-soft) 80%, var(--surface)); font-size: .72rem; }
        .map-help svg { width: 20px; color: var(--blue); flex: 0 0 auto; }
        .map-help strong { color: var(--ink); }
        .map-content { min-height: 0; display: grid; grid-template-columns: minmax(0,1fr) 390px; gap: 10px; }
        .map-card { min-height: 0; position: relative; overflow: hidden; padding: 0; }
        .map-card #national-map { width: 100%; height: 100%; min-height: 0; border: 0; border-radius: 8px; }
        .map-legend { position: absolute; z-index: 500; top: 12px; right: 12px; display: grid; gap: 7px; padding: 10px 12px; border-radius: 8px; background: color-mix(in srgb, var(--surface) 94%, transparent); box-shadow: 0 12px 24px color-mix(in srgb, var(--ink) 12%, transparent); font-size: .68rem; color: var(--muted); }
        .legend-dot { display: inline-block; width: 9px; height: 9px; margin-right: 6px; border-radius: 50%; background: var(--green); }
        .legend-dot.maintenance { background: var(--amber); }
        .legend-dot.inactive { background: var(--red); }
        .map-side { min-height: 0; display: grid; grid-template-rows: auto minmax(0,1fr); gap: 10px; }
        .department-summary, .visible-farms { min-height: 0; overflow: hidden; padding: 14px; }
        .map-card-title { display: flex; align-items: center; justify-content: space-between; gap: 8px; margin-bottom: 12px; }
        .map-card-title h2 { display: flex; align-items: center; gap: 8px; font-size: 1rem; line-height: 1.1; }
        .map-card-title h2 svg { width: 19px; color: var(--green); }
        .map-card-title span { color: var(--blue); font-size: .68rem; }
        .department-head { display: flex; gap: 10px; align-items: center; }
        .department-icon { width: 42px; height: 42px; display: grid; place-items: center; border-radius: 8px; color: var(--green-dark); background: var(--mint); }
        .department-icon svg { width: 23px; }
        .department-head h3 { font-size: .92rem; }
        .department-head p { margin-top: 3px; color: var(--muted); font-size: .68rem; }
        .department-kpis { display: grid; grid-template-columns: repeat(2,minmax(0,1fr)); gap: 8px; margin-top: 12px; }
        .department-kpi { min-width: 0; padding: 9px; border: 1px solid var(--line); border-radius: 8px; background: var(--surface-2); }
        .department-kpi span { display: block; color: var(--muted); font-size: .62rem; }
        .department-kpi strong { display: block; margin-top: 4px; font-size: 1rem; white-space: nowrap; }
        .visible-farms { display: grid; grid-template-rows: auto minmax(0,1fr) auto; }
        .farm-list { min-height: 0; overflow: auto; display: grid; align-content: start; gap: 7px; padding-right: 3px; }
        .farm-card { width: 100%; display: grid; grid-template-columns: 46px 1fr auto; gap: 10px; align-items: center; padding: 8px; border: 1px solid var(--line); border-radius: 8px; background: var(--surface-2); color: var(--ink); cursor: pointer; text-align: left; transition: transform .18s ease, background-color .22s ease, border-color .22s ease, box-shadow .22s ease; }
        .farm-card:hover, .farm-card.active { border-color: color-mix(in srgb, var(--green) 55%, var(--line)); background: color-mix(in srgb, var(--mint) 50%, var(--surface)); }
        .farm-card:hover { transform: translateX(2px); }
        .farm-card.active { box-shadow: 0 14px 28px color-mix(in srgb, var(--green) 18%, transparent); }
        .farm-card img { width: 46px; height: 34px; border-radius: 6px; object-fit: cover; }
        .farm-card strong { display: block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; font-size: .78rem; }
        .farm-card span { display: block; margin-top: 2px; color: var(--muted); font-size: .66rem; }
        .farm-card em { font-style: normal; font-size: .7rem; font-weight: 900; color: var(--ink); }
        .farm-status { display: inline-flex; align-items: center; gap: 5px; margin-top: 4px; }
        .farm-status::before { content: ''; width: 8px; height: 8px; border-radius: 50%; background: var(--green); }
        .farm-status.maintenance::before { background: var(--amber); }
        .farm-status.inactive::before { background: var(--red); }
        .map-tip { display: flex; align-items: center; gap: 7px; margin-top: 9px; padding: 7px 9px; border-radius: 8px; background: var(--mint); color: var(--green-dark); font-size: .66rem; }
        .map-tip svg { width: 16px; }
        .empty-map-list { display: none; padding: 20px; border: 1px dashed var(--line); border-radius: 8px; color: var(--muted); text-align: center; font-size: .78rem; }
        .list-limit-note { margin-top: 7px; color: var(--muted); font-size: .64rem; }
        .rmgs-marker { background: transparent; border: 0; }
        .map-marker-dot { width: 20px; height: 20px; display: block; border: 3px solid #fff; border-radius: 999px; background: var(--green); box-shadow: 0 8px 18px rgba(7,22,74,.25); }
        .map-marker-dot.maintenance { background: var(--amber); }
        .map-marker-dot.inactive { background: var(--red); }
        .rmgs-marker.marker-active .map-marker-dot { animation: marker-pulse 1.3s ease-in-out infinite; box-shadow: 0 0 0 8px color-mix(in srgb, var(--green) 22%, transparent), 0 10px 24px rgba(7,22,74,.28); }
        .rmgs-cluster { width: 42px; height: 42px; display: grid; place-items: center; border: 3px solid #fff; border-radius: 999px; background: linear-gradient(135deg, var(--green), var(--blue)); color: white; font-weight: 900; box-shadow: 0 12px 28px rgba(7,22,74,.26); }
        @keyframes marker-pulse { 0%, 100% { transform: scale(1); } 50% { transform: scale(1.18); } }
        @keyframes map-hero-sheen { 0%, 62% { transform: translateX(-120%); } 82%, 100% { transform: translateX(120%); } }
        @media (prefers-reduced-motion: reduce) { .map-hero::after, .rmgs-marker.marker-active .map-marker-dot { animation: none; } .farm-card { transition: none; } }
        @media(max-width:1180px){body:has(.map-screen){overflow:auto}.map-screen{height:auto;grid-template-rows:auto}.map-toolbar,.map-content{grid-template-columns:1fr}.map-card{height:560px}.map-side{grid-template-rows:auto}.visible-farms{min-height:420px}}
        @media(max-width:700px){.map-hero .hero-note{display:none}.department-kpis{grid-template-columns:1fr}.map-screen{min-height:0}}
    </style>

    <div class="map-screen">
        <section class="map-hero">
            <div>
                <p class="eyebrow">Cobertura geográfica</p>
                <h1>Mapa nacional</h1>
                <p class="subtitle">Explora las granjas solares por ubicación, capacidad instalada y estado operativo.</p>
            </div>
            <div class="hero-note"><i data-lucide="map-pin"></i><span>Marcadores por estado operativo</span></div>
        </section>

        <section class="map-toolbar">
            <label class="map-control"><i data-lucide="map-pin"></i><select id="map-department-filter"><option value="">Todos los departamentos</option>@foreach($departments as $department)<option value="{{ $department->name }}">{{ $department->name }}</option>@endforeach</select></label>
            <label class="map-control"><i data-lucide="search"></i><input id="map-search" type="search" placeholder="Buscar granja..."></label>
            <div class="map-help"><i data-lucide="info"></i><p><strong>Haz clic en un marcador</strong> para centrarlo y revisar su información.</p></div>
        </section>

        <section class="map-content">
            <article class="card map-card">
                <div id="national-map"></div>
                <div class="map-legend"><span><i class="legend-dot"></i>Operativa</span><span><i class="legend-dot maintenance"></i>Mantenimiento</span><span><i class="legend-dot inactive"></i>Inactiva</span></div>
            </article>

            <aside class="map-side">
                <section class="card department-summary">
                    <div class="map-card-title"><h2><i data-lucide="map"></i>Resumen</h2><span id="visible-map-count">{{ $farms->count() }} visibles</span></div>
                    <div class="department-head"><span class="department-icon"><i data-lucide="landmark"></i></span><div><h3 id="department-name">Todos los departamentos</h3><p id="department-caption">Vista general de Guatemala</p></div></div>
                    <div class="department-kpis">
                        <div class="department-kpi"><span>Granjas solares</span><strong id="map-stat-farms">{{ $stats['farms'] }}</strong></div>
                        <div class="department-kpi"><span>Paneles</span><strong id="map-stat-panels">{{ number_format($stats['panels']) }}</strong></div>
                        <div class="department-kpi"><span>Capacidad</span><strong id="map-stat-capacity">{{ number_format($stats['capacity_kw'],1) }} kW</strong></div>
                        <div class="department-kpi"><span>CO2 evitado</span><strong id="map-stat-co2">{{ number_format($stats['co2_tons'],1) }} t</strong></div>
                    </div>
                </section>

                <section class="card visible-farms">
                    <div class="map-card-title"><h2><i data-lucide="list"></i>Granjas visibles</h2><span>Selecciona una</span></div>
                    <div class="farm-list" id="map-farms-list">
                        <div class="empty-map-list" id="empty-map-list">No hay granjas para los filtros seleccionados.</div>
                    </div>
                    <div class="list-limit-note" id="map-list-note"></div>
                    <div class="map-tip"><i data-lucide="leaf"></i>El mapa se ajusta automáticamente según los filtros.</div>
                </section>
            </aside>
        </section>
    </div>

    <script src="https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js"></script>
    <script>
        const mapFarms = @json($mapFarms);
        const farmThumb = '{{ asset('images/dashboard-hero-guatemala.png') }}';
        const listLimit = 50;
        const departmentFilter = document.getElementById('map-department-filter');
        const searchInput = document.getElementById('map-search');
        const farmList = document.getElementById('map-farms-list');
        const emptyState = document.getElementById('empty-map-list');
        const listNote = document.getElementById('map-list-note');
        const nationalMap = L.map('national-map', { zoomControl: true }).setView([15.2, -90.35], 7);
        const markers = new Map();
        let activeFarmId = null;

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 18, attribution: '&copy; OpenStreetMap' }).addTo(nationalMap);
        const markerLayer = window.L.markerClusterGroup
            ? L.markerClusterGroup({
                chunkedLoading: true,
                showCoverageOnHover: false,
                maxClusterRadius: 48,
                iconCreateFunction: cluster => L.divIcon({
                    html: `<span class="rmgs-cluster">${cluster.getChildCount()}</span>`,
                    className: '',
                    iconSize: [42, 42],
                }),
            })
            : L.layerGroup();
        markerLayer.addTo(nationalMap);

        function statusColor(status) {
            return status === 'maintenance' ? '#f2a900' : (status === 'inactive' ? '#ef4444' : '#0aa574');
        }

        function markerIcon(farm) {
            return L.divIcon({
                className: 'rmgs-marker',
                html: `<span class="map-marker-dot ${farm.status}"></span>`,
                iconSize: [22, 22],
                iconAnchor: [11, 11],
            });
        }

        mapFarms.forEach(farm => {
            const marker = L.marker([farm.lat, farm.lng], { icon: markerIcon(farm) })
                .bindPopup(`<strong>${farm.name}</strong><br>${farm.municipality}, ${farm.department}<br>${Number(farm.capacity_kw).toFixed(1)} kW<br>${Number(farm.panels).toLocaleString()} paneles`);

            marker.on('click', () => focusFarm(farm.id, { fly: true, scroll: true }));
            markers.set(Number(farm.id), marker);
        });

        function selectedFarms() {
            const selectedDepartment = departmentFilter.value;
            const query = searchInput.value.trim().toLowerCase();

            return mapFarms.filter(farm => (!selectedDepartment || farm.department === selectedDepartment)
                && (!query || `${farm.name} ${farm.department} ${farm.municipality}`.toLowerCase().includes(query)));
        }

        function setActiveFarm(farmId) {
            activeFarmId = Number(farmId);

            currentFarmCards().forEach(row => row.classList.toggle('active', Number(row.dataset.mapFarmId) === activeFarmId));
            markers.forEach((marker, id) => {
                marker.getElement()?.classList.toggle('marker-active', id === activeFarmId);
            });
        }

        function currentFarmCards() {
            return [...farmList.querySelectorAll('[data-map-farm-id]')];
        }

        function statusLabel(status) {
            return status === 'maintenance' ? 'Mantenimiento' : (status === 'inactive' ? 'Inactiva' : 'Activa');
        }

        function escapeHtml(value) {
            const element = document.createElement('span');
            element.textContent = value ?? '';

            return element.innerHTML;
        }

        function renderFarmList(visible) {
            const farmsToRender = visible.slice(0, listLimit);
            farmList.querySelectorAll('[data-map-farm-id]').forEach(row => row.remove());
            emptyState.style.display = visible.length ? 'none' : 'block';

            farmsToRender.forEach(farm => {
                const button = document.createElement('button');
                button.className = 'farm-card';
                button.type = 'button';
                button.dataset.mapFarmId = farm.id;
                button.innerHTML = `
                    <img src="${farmThumb}" alt="">
                    <span>
                        <strong>${escapeHtml(farm.name)}</strong>
                        <span>${escapeHtml(farm.municipality)}, ${escapeHtml(farm.department)}</span>
                        <span class="farm-status ${farm.status}">${statusLabel(farm.status)}</span>
                    </span>
                    <em>${Number(farm.capacity_kw).toLocaleString(undefined, { maximumFractionDigits: 1 })} kW</em>
                `;
                button.addEventListener('click', () => focusFarm(farm.id, { fly: true, scroll: false }));
                farmList.insertBefore(button, emptyState);
            });

            listNote.textContent = visible.length > listLimit
                ? `Mostrando ${listLimit} de ${visible.length}. Usa filtros o búsqueda para acotar resultados.`
                : `Mostrando ${visible.length} de ${visible.length}.`;

            if (activeFarmId) {
                setActiveFarm(activeFarmId);
            }
        }

        function focusFarm(farmId, options = {}) {
            const marker = markers.get(Number(farmId));
            const row = currentFarmCards().find(item => Number(item.dataset.mapFarmId) === Number(farmId));

            if (!marker) {
                return;
            }

            setActiveFarm(farmId);
            nationalMap.closePopup();

            if (row && options.scroll !== false) {
                row.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
            }

            const flyToMarker = () => {
                if (options.fly === false) {
                    marker.openPopup();
                    return;
                }

                nationalMap.flyTo(marker.getLatLng(), Math.max(nationalMap.getZoom(), 13), {
                    animate: true,
                    duration: 1.05,
                    easeLinearity: .18,
                });
                nationalMap.once('moveend', () => marker.openPopup());
            };

            if (markerLayer.zoomToShowLayer) {
                markerLayer.zoomToShowLayer(marker, () => {
                    window.requestAnimationFrame(flyToMarker);
                });
            } else {
                flyToMarker();
            }
        }

        function updateMap() {
            const visible = selectedFarms();
            const visibleIds = new Set(visible.map(farm => Number(farm.id)));

            markerLayer.clearLayers();
            visible.forEach(farm => markerLayer.addLayer(markers.get(Number(farm.id))));
            renderFarmList(visible);

            if (activeFarmId && !visibleIds.has(activeFarmId)) {
                activeFarmId = null;
                currentFarmCards().forEach(row => row.classList.remove('active'));
                markers.forEach(marker => marker.getElement()?.classList.remove('marker-active'));
            } else if (activeFarmId) {
                setActiveFarm(activeFarmId);
            }

            document.getElementById('visible-map-count').textContent = `${visible.length} visibles`;
            document.getElementById('department-name').textContent = departmentFilter.value || 'Todos los departamentos';
            document.getElementById('department-caption').textContent = departmentFilter.value ? 'Resumen departamental' : 'Vista general de Guatemala';
            document.getElementById('map-stat-farms').textContent = visible.length;
            document.getElementById('map-stat-panels').textContent = visible.reduce((sum, farm) => sum + Number(farm.panels), 0).toLocaleString();
            document.getElementById('map-stat-capacity').textContent = `${visible.reduce((sum, farm) => sum + Number(farm.capacity_kw), 0).toLocaleString(undefined, { maximumFractionDigits: 1 })} kW`;
            document.getElementById('map-stat-co2').textContent = `${visible.reduce((sum, farm) => sum + Number(farm.co2_tons), 0).toLocaleString(undefined, { maximumFractionDigits: 1 })} t`;

            if (visible.length) {
                nationalMap.fitBounds(L.latLngBounds(visible.map(farm => [farm.lat, farm.lng])).pad(.32), { maxZoom: 10 });
            } else {
                nationalMap.setView([15.2, -90.35], 7);
            }
        }

        departmentFilter.addEventListener('input', updateMap);
        searchInput.addEventListener('input', updateMap);

        updateMap();
        window.setTimeout(() => nationalMap.invalidateSize(), 100);
    </script>
@endsection
