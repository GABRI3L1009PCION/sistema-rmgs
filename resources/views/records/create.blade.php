@extends('layouts.app')

@section('content')
    <section class="card">
        <h2>Registrar generacion mensual</h2>
        <p class="muted">Guarda un dato historico, recalcula CO2 con factor 0.40 y genera alerta si la generacion real queda 20% o mas debajo de la esperada.</p>

        <form method="post" action="{{ route('records.store') }}" class="section">
            @csrf
            <div class="form-grid">
                <label>Granja solar
                    <select name="solar_farm_id" required>
                        @foreach ($farms as $farm)
                            <option value="{{ $farm->id }}" @selected(old('solar_farm_id') == $farm->id)>{{ $farm->name }} - {{ $farm->department->name }}</option>
                        @endforeach
                    </select>
                    @error('solar_farm_id') <span class="error">{{ $message }}</span> @enderror
                </label>
                <label>Periodo
                    <input name="period" type="date" value="{{ old('period', now()->startOfMonth()->toDateString()) }}" required>
                    @error('period') <span class="error">{{ $message }}</span> @enderror
                </label>
                <label>Generacion esperada kWh
                    <input name="expected_kwh" type="number" step="0.01" min="1" value="{{ old('expected_kwh', 12000) }}" required>
                    @error('expected_kwh') <span class="error">{{ $message }}</span> @enderror
                </label>
                <label>Generacion real kWh
                    <input name="actual_kwh" type="number" step="0.01" min="0" value="{{ old('actual_kwh', 9600) }}" required>
                    @error('actual_kwh') <span class="error">{{ $message }}</span> @enderror
                </label>
                <label>Notas
                    <input name="notes" value="{{ old('notes') }}" placeholder="Medicion mensual, mantenimiento, clima, etc.">
                    @error('notes') <span class="error">{{ $message }}</span> @enderror
                </label>
            </div>

            @if ($errors->any())
                <div class="error section">Revisa los campos marcados antes de guardar.</div>
            @endif

            <div class="nav section">
                <button class="btn primary" type="submit">Guardar generacion</button>
                <a class="btn" href="{{ route('records.index') }}">Cancelar</a>
            </div>
        </form>
    </section>
@endsection
