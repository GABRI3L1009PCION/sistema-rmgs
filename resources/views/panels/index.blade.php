@extends('layouts.app')

@section('content')
    @php
        $panelDetails = $panelModels->mapWithKeys(fn ($panel) => [$panel->id => [
            'id' => $panel->id,
            'brand' => $panel->brand,
            'model' => $panel->model,
            'power_kw' => (float) $panel->nominal_power_kw,
            'technology' => $panel->technology,
            'panel_type' => $panel->panel_type,
            'efficiency' => $panel->efficiency_percent ? (float) $panel->efficiency_percent : null,
            'dimensions' => $panel->dimensions,
            'weight_kg' => $panel->weight_kg ? (float) $panel->weight_kg : null,
            'warranty_years' => $panel->warranty_years,
            'status' => $panel->status,
            'quantity' => $panel->farmPanels->sum('quantity'),
            'farms' => $panel->farmPanels->map(fn ($item) => [
                'id' => $item->id,
                'name' => $item->solarFarm->name,
                'department' => $item->solarFarm->department->name,
                'quantity' => $item->quantity,
                'capacity_kw' => round($item->quantity * $panel->nominal_power_kw, 2),
            ])->values(),
        ]]);
    @endphp

    <style>
        body:has(.panels-screen) { overflow: hidden; }
        .panels-screen { height: calc(100dvh - 86px); min-height: 650px; display: grid; grid-template-rows: clamp(150px,18vh,185px) 64px minmax(430px,1fr); gap: 10px; }
        .panels-hero { position: relative; overflow: hidden; display: flex; align-items: center; padding: 22px 28px; border-radius: 8px; color: white; background: linear-gradient(90deg,rgba(4,25,55,.82),rgba(4,25,55,.28),rgba(4,25,55,.05)),url('{{ asset('images/dashboard-hero-guatemala.png') }}') center 58%/cover; box-shadow: var(--shadow); }
        .panels-hero .eyebrow { margin-bottom: 7px; font-size: .72rem; opacity: .95; }
        .panels-hero h1 { font-size: clamp(2rem,2.4vw,2.5rem); line-height: 1; }
        .panels-hero .subtitle { margin-top: 7px; font-size: .9rem; }
        .panels-hero .hero-note { position: absolute; top: 17px; right: 22px; display: flex; gap: 7px; max-width: 190px; font-size: .68rem; font-weight: 800; }
        .panels-hero .hero-note svg { width: 18px; }
        .panel-filters { display: grid; grid-template-columns: repeat(4,minmax(0,1fr)); gap: 12px; padding: 8px 14px; }
        .panel-filters label { gap: 3px; font-size: .65rem; }
        .panel-filter-box { position: relative; }
        .panel-filter-box svg { position: absolute; left: 10px; top: 50%; width: 16px; transform: translateY(-50%); color: var(--muted); pointer-events: none; }
        .panel-filters select, .panel-filters input { height: 34px; min-height: 34px; padding: 4px 9px 4px 32px; font-size: .7rem; }
        .panels-content { min-height: 0; display: grid; grid-template-columns: minmax(0,1.55fr) minmax(350px,.75fr); gap: 10px; }
        .panel-list, .panel-detail { min-height: 0; overflow: hidden; }
        .panel-list { display: grid; grid-template-rows: 34px minmax(0,1fr); padding: 12px 14px; }
        .panel-list-title, .panel-detail-title { display: flex; justify-content: space-between; align-items: flex-start; gap: 10px; }
        .panel-list-title h2, .panel-detail-title h2 { display: flex; align-items: center; gap: 8px; font-size: .95rem; }
        .panel-list-title svg, .panel-detail-title svg { width: 18px; color: var(--blue); }
        .panel-list-title span { font-size: .66rem; color: var(--muted); }
        .panel-list-meta { display: flex; align-items: center; gap: 8px; }
        .panel-add { width: 28px; height: 28px; display: inline-grid; place-items: center; border: 1px solid var(--line); border-radius: 6px; color: var(--green-dark); background: var(--mint); }
        .panel-add svg { width: 15px; }
        .panel-table-wrap { min-height: 0; overflow: hidden; border: 1px solid var(--line); border-radius: 7px; }
        .panel-table { table-layout: fixed; font-size: .67rem; }
        .panel-table th, .panel-table td { height: 37px; padding: 4px 8px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .panel-table th { background: #f5f9fd; }
        .panel-row { cursor: pointer; }
        .panel-row:hover, .panel-row.selected { background: linear-gradient(90deg,#e8f8ef,#f6fcf9); }
        .panel-name { display: flex; align-items: center; gap: 9px; min-width: 0; }
        .panel-mini, .panel-visual { border: 1px solid #99a8bc; background-color: #17304e; background-image: linear-gradient(rgba(255,255,255,.22) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.22) 1px,transparent 1px); background-size: 7px 7px; }
        .panel-mini { width: 26px; height: 31px; flex: 0 0 auto; }
        .panel-state { display: inline-flex; align-items: center; gap: 6px; }
        .panel-state::before { content: ''; width: 8px; height: 8px; border-radius: 50%; background: var(--green); }
        .panel-state.inactive { color: var(--muted); }
        .panel-state.inactive::before { background: #8ca1bd; }
        .panel-row-actions { display: flex; justify-content: flex-end; align-items: center; gap: 5px; }
        .panel-row-actions form { margin: 0; }
        .panel-action { width: 28px; height: 28px; display: inline-grid; place-items: center; border: 0; border-radius: 6px; background: transparent; color: #4d6591; cursor: pointer; }
        .panel-action:hover { color: var(--blue); background: var(--blue-soft); }
        .panel-action.danger:hover { color: var(--red); background: #fff1f0; }
        .panel-action svg { width: 16px; }
        .panel-detail { display: grid; grid-template-rows: 34px auto minmax(0,1fr); padding: 12px 16px; }
        .detail-head { display: grid; grid-template-columns: 68px minmax(0,1fr) auto; gap: 12px; align-items: center; padding: 8px 0 12px; border-bottom: 1px solid var(--line); }
        .panel-visual { width: 54px; height: 76px; margin: auto; background-size: 11px 11px; box-shadow: 0 8px 18px rgba(7,22,74,.15); }
        .detail-head h3 { font-size: 1rem; line-height: 1.15; }
        .detail-head p { margin-top: 4px; font-size: .68rem; color: var(--muted); }
        .detail-badge { align-self: start; display: inline-flex; align-items: center; gap: 6px; padding: 6px 9px; border-radius: 999px; background: var(--mint); color: var(--green-dark); font-size: .65rem; font-weight: 800; }
        .detail-badge::before { content: ''; width: 7px; height: 7px; border-radius: 50%; background: var(--green); }
        .detail-badge.inactive { background: #edf1f6; color: var(--muted); }
        .detail-badge.inactive::before { background: #8ca1bd; }
        .detail-body { min-height: 0; padding-top: 10px; }
        .spec-grid { display: grid; grid-template-columns: repeat(2,1fr); gap: 10px 16px; padding-bottom: 12px; border-bottom: 1px solid var(--line); }
        .spec { display: grid; grid-template-columns: 20px 1fr; gap: 7px; font-size: .68rem; color: var(--muted); }
        .spec svg { width: 16px; color: #5976aa; }
        .installations-title { margin: 12px 0 8px; font-size: .76rem; }
        .installation-list { display: grid; gap: 6px; }
        .installation-row { display: flex; justify-content: space-between; gap: 10px; padding: 7px 8px; border-radius: 6px; background: #f7fafe; font-size: .66rem; }
        .installation-row strong { white-space: nowrap; }
        @media(max-width:1180px){body:has(.panels-screen){overflow:auto}.panels-screen{height:auto;grid-template-rows:auto}.panel-filters{grid-template-columns:repeat(2,minmax(0,1fr))}.panels-content{grid-template-columns:1fr}.panel-list,.panel-detail{min-height:430px}}
        @media(max-width:700px){.panel-filters{grid-template-columns:1fr}.panels-hero .hero-note{display:none}}
    </style>

    <div class="panels-screen">
        <section class="panels-hero">
            <div>
                <p class="eyebrow">RMGS - Registro y Monitoreo de Generacion Solar Guatemala</p>
                <h1>Paneles solares</h1>
                <p class="subtitle">Tecnologia que impulsa un Guatemala mas limpio y sostenible.</p>
            </div>
            <div class="hero-note"><i data-lucide="grid-2x2"></i><span>Catalogo tecnico y disponibilidad instalada</span></div>
        </section>

        <section class="card panel-filters">
            <label>Marca<span class="panel-filter-box"><i data-lucide="tag"></i><select id="brand-filter"><option value="">Todas las marcas</option>@foreach($panelModels->pluck('brand')->unique() as $brand)<option value="{{ $brand }}">{{ $brand }}</option>@endforeach</select></span></label>
            <label>Estado<span class="panel-filter-box"><i data-lucide="circle"></i><select id="panel-status-filter"><option value="">Todos los estados</option><option value="active">Activos</option><option value="inactive">Inactivos</option></select></span></label>
            <label>Granja<span class="panel-filter-box"><i data-lucide="landmark"></i><select id="panel-farm-filter"><option value="">Todas las granjas</option>@foreach($farms as $farm)<option value="{{ $farm->name }}">{{ $farm->name }}</option>@endforeach</select></span></label>
            <label>Buscar paneles<span class="panel-filter-box"><i data-lucide="search"></i><input id="panel-search" type="search" placeholder="Buscar por modelo o marca..."></span></label>
        </section>

        <section class="panels-content">
            <article class="card panel-list">
                <header class="panel-list-title">
                    <h2><i data-lucide="grid-2x2"></i>Modelos de paneles solares</h2>
                    <div class="panel-list-meta">
                        <span>Mostrando <strong id="panel-visible-count">{{ $panelModels->sum(fn ($panel) => max(1, $panel->farmPanels->count())) }}</strong> registros</span>
                        <a class="panel-add" href="{{ route('panels.create') }}" title="Registrar nuevo modelo"><i data-lucide="plus"></i></a>
                    </div>
                </header>
                <div class="panel-table-wrap">
                    <table class="panel-table">
                        <thead>
                            <tr>
                                <th style="width:28%">Modelo</th>
                                <th style="width:13%">Potencia (Wp)</th>
                                <th style="width:16%">Cantidad instalada</th>
                                <th style="width:21%">Granja</th>
                                <th style="width:12%">Estado</th>
                                <th style="width:10%; text-align:right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($panelModels as $panel)
                                @forelse($panel->farmPanels as $installation)
                                    <tr class="panel-row" data-row-key="installation-{{ $installation->id }}" data-panel-id="{{ $panel->id }}" data-installation-id="{{ $installation->id }}" data-brand="{{ $panel->brand }}" data-status="{{ $panel->status }}" data-farm="{{ $installation->solarFarm->name }}" data-search="{{ strtolower($panel->brand.' '.$panel->model.' '.$installation->solarFarm->name) }}">
                                        <td><span class="panel-name"><span class="panel-mini"></span><strong>{{ $panel->brand }} {{ $panel->model }}</strong></span></td>
                                        <td>{{ number_format($panel->nominal_power_kw * 1000) }}</td>
                                        <td>{{ number_format($installation->quantity) }}</td>
                                        <td>{{ $installation->solarFarm->name }}</td>
                                        <td><span class="panel-state {{ $panel->status }}">{{ $panel->status === 'active' ? 'Activo' : 'Inactivo' }}</span></td>
                                        <td>
                                            <span class="panel-row-actions">
                                                <a class="panel-action" href="{{ route('panels.edit', $panel) }}" title="Editar panel"><i data-lucide="pencil"></i></a>
                                                @if($panel->status !== 'inactive')
                                                    <form method="post" action="{{ route('panels.deactivate', $panel) }}">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button class="panel-action danger" type="submit" title="Desactivar panel"><i data-lucide="power"></i></button>
                                                    </form>
                                                @endif
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr class="panel-row" data-row-key="model-{{ $panel->id }}" data-panel-id="{{ $panel->id }}" data-installation-id="" data-brand="{{ $panel->brand }}" data-status="{{ $panel->status }}" data-farm="" data-search="{{ strtolower($panel->brand.' '.$panel->model) }}">
                                        <td><span class="panel-name"><span class="panel-mini"></span><strong>{{ $panel->brand }} {{ $panel->model }}</strong></span></td>
                                        <td>{{ number_format($panel->nominal_power_kw * 1000) }}</td>
                                        <td>0</td>
                                        <td>Sin asignar</td>
                                        <td><span class="panel-state {{ $panel->status }}">{{ $panel->status === 'active' ? 'Activo' : 'Inactivo' }}</span></td>
                                        <td>
                                            <span class="panel-row-actions">
                                                <a class="panel-action" href="{{ route('panels.edit', $panel) }}" title="Editar panel"><i data-lucide="pencil"></i></a>
                                                @if($panel->status !== 'inactive')
                                                    <form method="post" action="{{ route('panels.deactivate', $panel) }}">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button class="panel-action danger" type="submit" title="Desactivar panel"><i data-lucide="power"></i></button>
                                                    </form>
                                                @endif
                                            </span>
                                        </td>
                                    </tr>
                                @endforelse
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </article>

            <aside class="card panel-detail">
                <header class="panel-detail-title"><h2><i data-lucide="grid-2x2"></i>Detalles del panel</h2></header>
                <div class="detail-head">
                    <div class="panel-visual"></div>
                    <div>
                        <h3 id="detail-model">Selecciona un panel</h3>
                        <p id="detail-brand">Modelo registrado</p>
                        <p>Panel solar fotovoltaico de alta eficiencia.</p>
                    </div>
                    <span class="detail-badge" id="detail-status">Activo</span>
                </div>
                <div class="detail-body">
                    <div class="spec-grid">
                        <div class="spec"><i data-lucide="zap"></i><span>Potencia nominal<br><strong id="detail-power">0 Wp</strong></span></div>
                        <div class="spec"><i data-lucide="layers-3"></i><span>Tecnologia<br><strong id="detail-technology">Sin especificar</strong></span></div>
                        <div class="spec"><i data-lucide="box"></i><span>Tipo<br><strong id="detail-type">Sin especificar</strong></span></div>
                        <div class="spec"><i data-lucide="activity"></i><span>Estado<br><strong id="detail-state">Activo</strong></span></div>
                        <div class="spec"><i data-lucide="gauge"></i><span>Eficiencia<br><strong id="detail-efficiency">Sin especificar</strong></span></div>
                        <div class="spec"><i data-lucide="ruler"></i><span>Dimensiones<br><strong id="detail-dimensions">Sin especificar</strong></span></div>
                        <div class="spec"><i data-lucide="weight"></i><span>Peso<br><strong id="detail-weight">Sin especificar</strong></span></div>
                        <div class="spec"><i data-lucide="shield-check"></i><span>Garantia<br><strong id="detail-warranty">Sin especificar</strong></span></div>
                    </div>
                    <h3 class="installations-title">Instalacion seleccionada</h3>
                    <div class="installation-list" id="installation-list"></div>
                </div>
            </aside>
        </section>
    </div>

    <script>
        const panelDetails = @json($panelDetails);
        const panelRows = [...document.querySelectorAll('.panel-row')];
        const brandFilter = document.getElementById('brand-filter');
        const statusFilter = document.getElementById('panel-status-filter');
        const farmFilter = document.getElementById('panel-farm-filter');
        const panelSearch = document.getElementById('panel-search');

        function escapeHtml(value) {
            const element = document.createElement('span');
            element.textContent = String(value ?? '');
            return element.innerHTML;
        }

        function showPanel(id, rowKey, installationId = null) {
            const panel = panelDetails[id];

            if (!panel) {
                return;
            }

            document.getElementById('detail-model').textContent = `${panel.brand} ${panel.model}`;
            document.getElementById('detail-brand').textContent = panel.brand;
            document.getElementById('detail-power').textContent = `${Math.round(panel.power_kw * 1000)} Wp`;
            document.getElementById('detail-status').textContent = panel.status === 'active' ? 'Activo' : 'Inactivo';
            document.getElementById('detail-status').classList.toggle('inactive', panel.status !== 'active');
            document.getElementById('detail-state').textContent = panel.status === 'active' ? 'Activo' : 'Inactivo';
            document.getElementById('detail-technology').textContent = panel.technology || 'Sin especificar';
            document.getElementById('detail-type').textContent = panel.panel_type || 'Sin especificar';
            document.getElementById('detail-efficiency').textContent = panel.efficiency ? `${panel.efficiency}%` : 'Sin especificar';
            document.getElementById('detail-dimensions').textContent = panel.dimensions || 'Sin especificar';
            document.getElementById('detail-weight').textContent = panel.weight_kg ? `${panel.weight_kg} kg` : 'Sin especificar';
            document.getElementById('detail-warranty').textContent = panel.warranty_years ? `${panel.warranty_years} anos` : 'Sin especificar';

            const selectedInstallation = panel.farms.find(farm => Number(farm.id) === Number(installationId));
            document.getElementById('installation-list').innerHTML = selectedInstallation
                ? `<div class="installation-row"><span>${escapeHtml(selectedInstallation.name)}<br><small>${escapeHtml(selectedInstallation.department)} · ${Number(selectedInstallation.capacity_kw).toLocaleString()} kW</small></span><strong>${Number(selectedInstallation.quantity).toLocaleString()} paneles</strong></div>`
                : '<div class="installation-row"><span>Sin instalaciones asociadas</span></div>';
            panelRows.forEach(row => row.classList.toggle('selected', row.dataset.rowKey === rowKey));
        }

        function filterPanels() {
            const query = panelSearch.value.trim().toLowerCase();
            let visible = 0;

            panelRows.forEach(row => {
                const show = (!brandFilter.value || row.dataset.brand === brandFilter.value)
                    && (!statusFilter.value || row.dataset.status === statusFilter.value)
                    && (!farmFilter.value || row.dataset.farm === farmFilter.value)
                    && (!query || row.dataset.search.includes(query));
                row.hidden = !show;

                if (show) {
                    visible++;
                }
            });

            document.getElementById('panel-visible-count').textContent = visible;

            const selectedRow = panelRows.find(row => row.classList.contains('selected'));
            const firstVisibleRow = panelRows.find(row => !row.hidden);
            if ((!selectedRow || selectedRow.hidden) && firstVisibleRow) {
                showPanel(firstVisibleRow.dataset.panelId, firstVisibleRow.dataset.rowKey, firstVisibleRow.dataset.installationId || null);
            }
        }

        panelRows.forEach(row => row.addEventListener('click', event => {
            if (event.target.closest('a, button, form')) return;
            showPanel(row.dataset.panelId, row.dataset.rowKey, row.dataset.installationId || null);
        }));
        [brandFilter, statusFilter, farmFilter, panelSearch].forEach(control => control.addEventListener('input', filterPanels));

        if (panelRows.length) {
            showPanel(panelRows[0].dataset.panelId, panelRows[0].dataset.rowKey, panelRows[0].dataset.installationId || null);
        }
    </script>
@endsection
