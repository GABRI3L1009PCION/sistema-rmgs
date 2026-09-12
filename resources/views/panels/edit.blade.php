@extends('layouts.app')

@section('content')
    <style>
        .panel-edit-card { padding: 18px 20px; }
        .panel-edit-card .section { margin-top: 10px; }
        .panel-edit-grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 9px 12px; }
        .panel-edit-grid label { gap: 3px; font-size: .72rem; }
        .panel-edit-grid input, .panel-edit-grid select { min-height: 38px; height: 38px; }
        .installation-editor { max-height: 112px; overflow-y: auto; display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 7px 12px; padding-right: 4px; }
        .installation-edit-row { display: grid; grid-template-columns: minmax(0, 1fr) 130px; align-items: center; gap: 10px; padding: 7px 9px; border: 1px solid var(--line); border-radius: 6px; background: var(--table-head); }
        .installation-edit-row span { min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; font-size: .7rem; font-weight: 700; }
        .installation-edit-row input { min-height: 32px; height: 32px; }
        .panel-edit-actions { margin-top: 10px; }
        @media(max-width:1100px){.panel-edit-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.installation-editor{grid-template-columns:1fr}}
        @media(max-width:650px){.panel-edit-grid{grid-template-columns:1fr}}
    </style>

    <section class="card panel-edit-card">
        <div class="card-title"><h2>Editar modelo de panel</h2><i data-lucide="grid-2x2"></i></div>
        <p class="muted">Actualiza la ficha tecnica y las cantidades instaladas sin salir del modelo.</p>
        <form method="post" action="{{ route('panels.update', $panel) }}" class="section">
            @csrf
            @method('PUT')
            <div class="panel-edit-grid">
                <label>Marca<input name="brand" value="{{ old('brand', $panel->brand) }}" required maxlength="100">@error('brand')<span class="error">{{ $message }}</span>@enderror</label>
                <label>Modelo<input name="model" value="{{ old('model', $panel->model) }}" required maxlength="150">@error('model')<span class="error">{{ $message }}</span>@enderror</label>
                <label>Potencia nominal (kW)<input name="nominal_power_kw" type="number" step="0.001" min="0.001" value="{{ old('nominal_power_kw', $panel->nominal_power_kw) }}" required>@error('nominal_power_kw')<span class="error">{{ $message }}</span>@enderror</label>
                <label>Estado<select name="status" required><option value="active" @selected(old('status', $panel->status) === 'active')>Activo</option><option value="inactive" @selected(old('status', $panel->status) === 'inactive')>Inactivo</option></select>@error('status')<span class="error">{{ $message }}</span>@enderror</label>
                <label>Tecnología<input name="technology" value="{{ old('technology', $panel->technology ?? 'Monocristalino') }}" required maxlength="100">@error('technology')<span class="error">{{ $message }}</span>@enderror</label>
                <label>Tipo de panel<input name="panel_type" value="{{ old('panel_type', $panel->panel_type ?? 'Modulo fotovoltaico') }}" required maxlength="100">@error('panel_type')<span class="error">{{ $message }}</span>@enderror</label>
                <label>Eficiencia (%)<input name="efficiency_percent" type="number" step="0.01" min="0.01" max="100" value="{{ old('efficiency_percent', $panel->efficiency_percent) }}" placeholder="Ej. 21.50">@error('efficiency_percent')<span class="error">{{ $message }}</span>@enderror</label>
                <label>Dimensiones<input name="dimensions" value="{{ old('dimensions', $panel->dimensions) }}" maxlength="100" placeholder="2278 x 1134 x 30 mm">@error('dimensions')<span class="error">{{ $message }}</span>@enderror</label>
                <label>Peso (kg)<input name="weight_kg" type="number" step="0.01" min="0" value="{{ old('weight_kg', $panel->weight_kg) }}" placeholder="Ej. 28.60">@error('weight_kg')<span class="error">{{ $message }}</span>@enderror</label>
                <label>Garantía (años)<input name="warranty_years" type="number" min="1" max="100" value="{{ old('warranty_years', $panel->warranty_years) }}" placeholder="Ej. 25">@error('warranty_years')<span class="error">{{ $message }}</span>@enderror</label>
            </div>

            @if($panel->farmPanels->isNotEmpty())
                <h3 class="section">Cantidades instaladas por granja</h3>
                <div class="installation-editor">
                    @foreach($panel->farmPanels as $installation)
                        <label class="installation-edit-row">
                            <span title="{{ $installation->solarFarm->name }}">{{ $installation->solarFarm->name }}</span>
                            <input name="installations[{{ $installation->id }}][quantity]" type="number" min="1" value="{{ old('installations.'.$installation->id.'.quantity', $installation->quantity) }}" aria-label="Cantidad instalada en {{ $installation->solarFarm->name }}" required>
                        </label>
                    @endforeach
                </div>
            @endif

            <div class="nav panel-edit-actions"><button class="btn primary" type="submit"><i data-lucide="save"></i>Guardar cambios</button><a class="btn" href="{{ route('panels.index') }}">Cancelar</a></div>
        </form>
    </section>
@endsection
