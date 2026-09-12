@extends('layouts.app')

@section('content')
    <section class="card">
        <h2>Editar generacion mensual</h2>
        <p class="muted">Al actualizar el registro se recalcula CO2 con factor 0.40 y se vuelve a evaluar la alerta de desempeno.</p>

        <form method="post" action="{{ route('records.update', $record) }}" class="section">
            @csrf
            @method('PUT')
            <div class="form-grid">
                <label>Granja solar
                    <select name="solar_farm_id" required>
                        @foreach ($farms as $farm)
                            <option value="{{ $farm->id }}" @selected(old('solar_farm_id', $record->solar_farm_id) == $farm->id)>{{ $farm->name }} - {{ $farm->department->name }}</option>
                        @endforeach
                    </select>
                    @error('solar_farm_id') <span class="error">{{ $message }}</span> @enderror
                </label>
                <label>Periodo
                    <input name="period" type="date" value="{{ old('period', $record->period->toDateString()) }}" required>
                    @error('period') <span class="error">{{ $message }}</span> @enderror
                </label>
                <label>Generacion esperada kWh
                    <input name="expected_kwh" type="number" step="0.01" min="1" value="{{ old('expected_kwh', $record->expected_kwh) }}" required>
                    @error('expected_kwh') <span class="error">{{ $message }}</span> @enderror
                </label>
                <label>Generacion real kWh
                    <input name="actual_kwh" type="number" step="0.01" min="0" value="{{ old('actual_kwh', $record->actual_kwh) }}" required>
                    @error('actual_kwh') <span class="error">{{ $message }}</span> @enderror
                </label>
                <label>Notas
                    <input name="notes" value="{{ old('notes', $record->notes) }}">
                    @error('notes') <span class="error">{{ $message }}</span> @enderror
                </label>
            </div>

            @if ($errors->any())
                <div class="error section">Revisa los campos marcados antes de guardar.</div>
            @endif

            <div class="nav section">
                <button class="btn primary" type="submit">Actualizar generacion</button>
                <a class="btn" href="{{ route('records.index') }}">Cancelar</a>
            </div>
        </form>
    </section>
@endsection
