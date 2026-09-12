@extends('layouts.app')

@section('content')
    <section class="card">
        <div class="card-title"><h2>Registrar nuevo modelo de panel</h2><i data-lucide="grid-2x2"></i></div>
        <p class="muted">Agrega la ficha tecnica del modelo para poder asignarlo a las granjas solares.</p>
        <form method="post" action="{{ route('panels.store') }}" class="section">
            @csrf
            <div class="form-grid">
                <label>Marca<input name="brand" value="{{ old('brand') }}" required maxlength="100">@error('brand')<span class="error">{{ $message }}</span>@enderror</label>
                <label>Modelo<input name="model" value="{{ old('model') }}" required maxlength="150">@error('model')<span class="error">{{ $message }}</span>@enderror</label>
                <label>Potencia nominal (kW)<input name="nominal_power_kw" type="number" step="0.001" min="0.001" value="{{ old('nominal_power_kw', '0.450') }}" required>@error('nominal_power_kw')<span class="error">{{ $message }}</span>@enderror</label>
                <label>Estado<select name="status" required><option value="active">Activo</option><option value="inactive">Inactivo</option></select></label>
                <label>Tecnologia<input name="technology" value="{{ old('technology', 'Monocristalino') }}" required maxlength="100">@error('technology')<span class="error">{{ $message }}</span>@enderror</label>
                <label>Tipo de panel<input name="panel_type" value="{{ old('panel_type', 'Modulo fotovoltaico') }}" required maxlength="100">@error('panel_type')<span class="error">{{ $message }}</span>@enderror</label>
                <label>Eficiencia (%)<input name="efficiency_percent" type="number" step="0.01" min="0.01" max="100" value="{{ old('efficiency_percent') }}">@error('efficiency_percent')<span class="error">{{ $message }}</span>@enderror</label>
                <label>Dimensiones<input name="dimensions" value="{{ old('dimensions') }}" maxlength="100" placeholder="2278 x 1134 x 30 mm">@error('dimensions')<span class="error">{{ $message }}</span>@enderror</label>
                <label>Peso (kg)<input name="weight_kg" type="number" step="0.01" min="0" value="{{ old('weight_kg') }}">@error('weight_kg')<span class="error">{{ $message }}</span>@enderror</label>
                <label>Garantia (anos)<input name="warranty_years" type="number" min="1" max="100" value="{{ old('warranty_years') }}">@error('warranty_years')<span class="error">{{ $message }}</span>@enderror</label>
            </div>
            <div class="nav section"><button class="btn primary" type="submit"><i data-lucide="save"></i>Guardar panel</button><a class="btn" href="{{ route('panels.index') }}">Cancelar</a></div>
        </form>
    </section>
@endsection
