@extends('layouts.app')

@section('content')
    <style>
        .farm-detail-screen { display: grid; gap: 12px; }
        .farm-detail-header { display: grid; grid-template-columns: minmax(0, 1fr) auto; gap: 14px; align-items: end; }
        .farm-detail-header h1 { font-size: clamp(1.8rem, 2.4vw, 2.45rem); line-height: 1.05; }
        .farm-detail-meta { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 10px; }
        .farm-detail-status { display: inline-flex; align-items: center; gap: 7px; padding: 7px 10px; border-radius: 999px; background: var(--mint); color: var(--green-dark); font-size: .78rem; font-weight: 900; }
        .farm-detail-status::before { content: ''; width: 8px; height: 8px; border-radius: 50%; background: var(--green); }
        .farm-detail-status.maintenance { background: #fff8e5; color: #9a6b00; }
        .farm-detail-status.maintenance::before { background: var(--amber); }
        .farm-detail-status.inactive { background: #fff1f0; color: var(--red); }
        .farm-detail-status.inactive::before { background: var(--red); }
        .detail-kpis { grid-template-columns: repeat(4, minmax(0, 1fr)); }
        .detail-kpi { display: grid; gap: 6px; }
        .detail-kpi strong { font-size: 1.45rem; line-height: 1; }
        .detail-layout { display: grid; grid-template-columns: minmax(0, 1.1fr) minmax(360px, .9fr); gap: 12px; }
        .info-list { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; }
        .info-item { padding: 10px; border: 1px solid var(--line); border-radius: 8px; background: #f7fbff; }
        .info-item span { display: block; margin-bottom: 5px; color: var(--muted); font-size: .74rem; font-weight: 800; }
        .inline-form { display: flex; gap: 8px; align-items: center; }
        .inline-form input { width: 92px; min-height: 34px; }
        .compact-actions { display: flex; gap: 6px; justify-content: flex-end; align-items: center; }
        .compact-actions form { margin: 0; }
        .empty-box { padding: 18px; border: 1px dashed var(--line); border-radius: 8px; color: var(--muted); text-align: center; }
        @media (max-width: 1050px) {
            .farm-detail-header, .detail-layout, .detail-kpis, .info-list { grid-template-columns: 1fr; }
        }
    </style>

    <div class="farm-detail-screen">
        <section class="card farm-detail-header">
            <div>
                <p class="muted">Ficha tecnica de granja solar</p>
                <h1>{{ $farm->name }}</h1>
                <div class="farm-detail-meta">
                    <span class="farm-detail-status {{ $farm->status }}">
                        {{ $farm->status === 'maintenance' ? 'En mantenimiento' : ($farm->status === 'active' ? 'Activa' : 'Inactiva') }}
                    </span>
                    <span class="pill ok">{{ $farm->department->name }}</span>
                    <span class="pill ok">{{ $farm->municipality }}</span>
                </div>
            </div>
            <div class="nav">
                <a class="btn" href="{{ route('farms.index') }}"><i data-lucide="arrow-left"></i>Volver</a>
                <a class="btn primary" href="{{ route('farms.edit', $farm) }}"><i data-lucide="pencil"></i>Editar</a>
            </div>
        </section>

        <section class="grid detail-kpis">
            <article class="card detail-kpi"><span class="muted">Paneles instalados</span><strong>{{ number_format($stats['panels']) }}</strong></article>
            <article class="card detail-kpi"><span class="muted">Capacidad instalada</span><strong>{{ number_format($stats['capacity_kw'], 2) }} kW</strong></article>
            <article class="card detail-kpi"><span class="muted">Generacion registrada</span><strong>{{ number_format($stats['actual_kwh'], 1) }} kWh</strong></article>
            <article class="card detail-kpi"><span class="muted">CO2 evitado</span><strong>{{ number_format($stats['co2_tons'], 2) }} t</strong></article>
        </section>

        <section class="detail-layout">
            <article class="card">
                <div class="card-title"><h2>Informacion general</h2><i data-lucide="landmark"></i></div>
                <div class="info-list">
                    <div class="info-item"><span>Propietario</span><strong>{{ $farm->owner ?: 'Sin propietario registrado' }}</strong></div>
                    <div class="info-item"><span>Familias beneficiadas</span><strong>{{ number_format($farm->families_benefited) }}</strong></div>
                    <div class="info-item"><span>Latitud</span><strong>{{ $farm->latitude }}</strong></div>
                    <div class="info-item"><span>Longitud</span><strong>{{ $farm->longitude }}</strong></div>
                    <div class="info-item"><span>Proyeccion mensual</span><strong>{{ number_format($stats['projection'], 1) }} kWh</strong></div>
                    <div class="info-item"><span>Alertas registradas</span><strong>{{ number_format($farm->alerts->count()) }}</strong></div>
                </div>
            </article>

            <article class="card">
                <div class="card-title"><h2>Agregar paneles</h2><i data-lucide="plus"></i></div>
                <form method="post" action="{{ route('farms.panels.store', $farm) }}">
                    @csrf
                    <div class="form-grid">
                        <label>Modelo de panel
                            <select name="panel_model_id" required>
                                @foreach ($panelModels as $panelModel)
                                    <option value="{{ $panelModel->id }}">{{ $panelModel->brand }} {{ $panelModel->model }} - {{ number_format($panelModel->nominal_power_kw * 1000) }} Wp</option>
                                @endforeach
                            </select>
                            @error('panel_model_id')<span class="error">{{ $message }}</span>@enderror
                        </label>
                        <label>Cantidad
                            <input name="quantity" type="number" min="1" value="{{ old('quantity', 1) }}" required>
                            @error('quantity')<span class="error">{{ $message }}</span>@enderror
                        </label>
                    </div>
                    <div class="nav section"><button class="btn primary" type="submit"><i data-lucide="save"></i>Agregar</button></div>
                </form>
            </article>
        </section>

        <section class="card">
            <div class="card-title"><h2>Paneles instalados</h2><i data-lucide="grid-2x2"></i></div>
            @if ($farm->farmPanels->isEmpty())
                <div class="empty-box">Esta granja aun no tiene paneles asignados.</div>
            @else
                <table>
                    <thead><tr><th>Modelo</th><th>Potencia unitaria</th><th>Cantidad</th><th>Capacidad</th><th style="text-align:right">Acciones</th></tr></thead>
                    <tbody>
                        @foreach ($farm->farmPanels as $installation)
                            <tr>
                                <td><strong>{{ $installation->panelModel->brand }} {{ $installation->panelModel->model }}</strong></td>
                                <td>{{ number_format($installation->panelModel->nominal_power_kw * 1000) }} Wp</td>
                                <td>
                                    <form class="inline-form" method="post" action="{{ route('farms.panels.update', [$farm, $installation]) }}">
                                        @csrf
                                        @method('PUT')
                                        <input name="quantity" type="number" min="1" value="{{ $installation->quantity }}" required>
                                        <button class="btn" type="submit"><i data-lucide="check"></i></button>
                                    </form>
                                </td>
                                <td>{{ number_format($installation->quantity * $installation->panelModel->nominal_power_kw, 2) }} kW</td>
                                <td>
                                    <div class="compact-actions">
                                        <form method="post" action="{{ route('farms.panels.destroy', [$farm, $installation]) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn danger" type="submit"><i data-lucide="trash-2"></i>Retirar</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </section>

        <section class="detail-layout">
            <article class="card">
                <div class="card-title"><h2>Generacion historica</h2><i data-lucide="bar-chart-3"></i></div>
                <table>
                    <thead><tr><th>Periodo</th><th>Esperado</th><th>Real</th><th>Desviacion</th></tr></thead>
                    <tbody>
                        @forelse ($farm->energyRecords->take(6) as $record)
                            <tr>
                                <td>{{ $record->period->format('Y-m') }}</td>
                                <td>{{ number_format($record->expected_kwh, 1) }} kWh</td>
                                <td>{{ number_format($record->actual_kwh, 1) }} kWh</td>
                                <td>{{ number_format($record->deviationPercent(), 1) }}%</td>
                            </tr>
                        @empty
                            <tr><td colspan="4">No hay registros de generacion para esta granja.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </article>

            <article class="card">
                <div class="card-title"><h2>Alertas recientes</h2><i data-lucide="bell"></i></div>
                <table>
                    <thead><tr><th>Periodo</th><th>Desviacion</th><th>Estado</th></tr></thead>
                    <tbody>
                        @forelse ($farm->alerts->take(6) as $alert)
                            <tr>
                                <td>{{ $alert->period->format('Y-m') }}</td>
                                <td>{{ number_format($alert->deviation_percent, 1) }}%</td>
                                <td>{{ $alert->status }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3">Sin alertas recientes.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </article>
        </section>
    </div>
@endsection
