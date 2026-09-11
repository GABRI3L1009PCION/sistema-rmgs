@extends('layouts.app')

@section('content')
    <section class="card">
        <h2>Registrar nueva granja solar</h2>
        <p class="muted">El registro crea la granja, asocia paneles, guarda generacion inicial y genera alerta si la desviacion es de 20% o mas.</p>

        <form method="post" action="{{ route('farms.store') }}" class="section">
            @csrf
            <div class="form-grid">
                <label>Nombre
                    <input name="name" value="{{ old('name') }}" required>
                    @error('name') <span class="error">{{ $message }}</span> @enderror
                </label>
                <label>Responsable
                    <input name="owner" value="{{ old('owner') }}">
                </label>
                <label>Departamento
                    <select name="department_id" required>
                        @foreach ($departments as $department)
                            <option value="{{ $department->id }}" @selected(old('department_id') == $department->id)>{{ $department->name }}</option>
                        @endforeach
                    </select>
                </label>
                <label>Municipio
                    <input name="municipality" value="{{ old('municipality') }}" required>
                </label>
                <label>Latitud
                    <input name="latitude" type="number" step="0.0000001" value="{{ old('latitude', '15.7300000') }}" required>
                </label>
                <label>Longitud
                    <input name="longitude" type="number" step="0.0000001" value="{{ old('longitude', '-88.5900000') }}" required>
                </label>
                <label>Familias beneficiadas
                    <input name="families_benefited" type="number" min="0" value="{{ old('families_benefited', 100) }}" required>
                </label>
                <label>Modelo de panel
                    <select name="panel_model_id" required>
                        @foreach ($panelModels as $panel)
                            <option value="{{ $panel->id }}" @selected(old('panel_model_id') == $panel->id)>{{ $panel->brand }} {{ $panel->model }} - {{ $panel->nominal_power_kw }} kW</option>
                        @endforeach
                    </select>
                </label>
                <label>Cantidad de paneles
                    <input name="quantity" type="number" min="1" value="{{ old('quantity', 100) }}" required>
                </label>
                <label>Generacion esperada kWh
                    <input name="expected_kwh" type="number" step="0.01" min="1" value="{{ old('expected_kwh', 12000) }}" required>
                </label>
                <label>Generacion real kWh
                    <input name="actual_kwh" type="number" step="0.01" min="0" value="{{ old('actual_kwh', 10000) }}" required>
                </label>
            </div>

            @if ($errors->any())
                <div class="error section">Revisa los campos marcados antes de guardar.</div>
            @endif

            <div class="nav section">
                <button class="btn primary" type="submit">Guardar granja</button>
                <a class="btn" href="/">Cancelar</a>
            </div>
        </form>
    </section>
@endsection
