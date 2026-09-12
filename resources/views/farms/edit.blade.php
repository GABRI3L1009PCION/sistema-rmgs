@extends('layouts.app')

@section('content')
    <section class="card">
        <h2>Editar granja solar</h2>
        <p class="muted">Actualiza datos generales, ubicacion, familias beneficiadas y estado operativo.</p>

        <form method="post" action="{{ route('farms.update', $farm) }}" class="section">
            @csrf
            @method('PUT')
            <div class="form-grid">
                <label>Nombre
                    <input name="name" value="{{ old('name', $farm->name) }}" required>
                    @error('name') <span class="error">{{ $message }}</span> @enderror
                </label>
                <label>Responsable
                    <input name="owner" value="{{ old('owner', $farm->owner) }}">
                    @error('owner') <span class="error">{{ $message }}</span> @enderror
                </label>
                <label>Departamento
                    <select name="department_id" required>
                        @foreach ($departments as $department)
                            <option value="{{ $department->id }}" @selected(old('department_id', $farm->department_id) == $department->id)>{{ $department->name }}</option>
                        @endforeach
                    </select>
                    @error('department_id') <span class="error">{{ $message }}</span> @enderror
                </label>
                <label>Municipio
                    <input name="municipality" value="{{ old('municipality', $farm->municipality) }}" required>
                    @error('municipality') <span class="error">{{ $message }}</span> @enderror
                </label>
                <label>Latitud
                    <input name="latitude" type="number" step="0.0000001" value="{{ old('latitude', $farm->latitude) }}" required>
                    @error('latitude') <span class="error">{{ $message }}</span> @enderror
                </label>
                <label>Longitud
                    <input name="longitude" type="number" step="0.0000001" value="{{ old('longitude', $farm->longitude) }}" required>
                    @error('longitude') <span class="error">{{ $message }}</span> @enderror
                </label>
                <label>Familias beneficiadas
                    <input name="families_benefited" type="number" min="0" value="{{ old('families_benefited', $farm->families_benefited) }}" required>
                    @error('families_benefited') <span class="error">{{ $message }}</span> @enderror
                </label>
                <label>Estado
                    <select name="status" required>
                        <option value="active" @selected(old('status', $farm->status) === 'active')>Activa</option>
                        <option value="maintenance" @selected(old('status', $farm->status) === 'maintenance')>Mantenimiento</option>
                        <option value="inactive" @selected(old('status', $farm->status) === 'inactive')>Inactiva</option>
                    </select>
                    @error('status') <span class="error">{{ $message }}</span> @enderror
                </label>
                <label>Notas
                    <input name="notes" value="{{ old('notes', $farm->notes) }}">
                    @error('notes') <span class="error">{{ $message }}</span> @enderror
                </label>
            </div>

            @if ($errors->any())
                <div class="error section">Revisa los campos marcados antes de guardar.</div>
            @endif

            <div class="nav section">
                <button class="btn primary" type="submit">Actualizar granja</button>
                <a class="btn" href="{{ route('farms.index') }}">Cancelar</a>
            </div>
        </form>
    </section>
@endsection
