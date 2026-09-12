@extends('layouts.app')

@section('content')
    <section class="card">
        <div class="card-title">
            <h2>Editar modelo de panel</h2>
            <i data-lucide="grid-2x2"></i>
        </div>
        <p class="muted">Actualiza la ficha tecnica del modelo usado por las granjas solares.</p>

        <form method="post" action="{{ route('panels.update', $panel) }}" class="section">
            @csrf
            @method('PUT')
            <div class="form-grid">
                <label>Marca
                    <input name="brand" value="{{ old('brand', $panel->brand) }}" required>
                    @error('brand')<span class="error">{{ $message }}</span>@enderror
                </label>
                <label>Modelo
                    <input name="model" value="{{ old('model', $panel->model) }}" required>
                    @error('model')<span class="error">{{ $message }}</span>@enderror
                </label>
                <label>Potencia nominal (kW)
                    <input name="nominal_power_kw" type="number" step="0.001" min="0.001" value="{{ old('nominal_power_kw', $panel->nominal_power_kw) }}" required>
                    @error('nominal_power_kw')<span class="error">{{ $message }}</span>@enderror
                </label>
                <label>Estado
                    <select name="status" required>
                        <option value="active" @selected(old('status', $panel->status) === 'active')>Activo</option>
                        <option value="inactive" @selected(old('status', $panel->status) === 'inactive')>Inactivo</option>
                    </select>
                    @error('status')<span class="error">{{ $message }}</span>@enderror
                </label>
            </div>
            <div class="nav section">
                <button class="btn primary" type="submit"><i data-lucide="save"></i>Guardar cambios</button>
                <a class="btn" href="{{ route('panels.index') }}">Cancelar</a>
            </div>
        </form>
    </section>
@endsection
