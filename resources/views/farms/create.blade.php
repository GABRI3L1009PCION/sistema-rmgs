@extends('layouts.app')

@section('content')
    <section class="card">
        <h2>Registrar nueva granja solar</h2>
        <p class="muted">El registro crea la granja, asocia paneles, sugiere la generacion esperada y guarda la lectura real inicial.</p>

        <div class="section" style="display:grid; grid-template-columns: minmax(0, 1fr); gap: 12px;">
            <article style="padding:14px; border:1px solid var(--line); border-radius:8px; background:var(--surface-soft);">
                <p class="muted">Capacidad estimada</p>
                <strong id="capacity-preview" style="font-size:1.35rem;">0.0 kW</strong>
            </article>
        </div>

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
                    <select name="department_id" data-department-select required>
                        @foreach ($departments as $department)
                            <option value="{{ $department->id }}" @selected(old('department_id') == $department->id)>{{ $department->name }}</option>
                        @endforeach
                    </select>
                </label>
                <label>Municipio
                    <select name="municipality" data-municipality-select data-current="{{ old('municipality') }}" required>
                        <option value="">Selecciona un municipio</option>
                    </select>
                    @error('municipality') <span class="error">{{ $message }}</span> @enderror
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
                    <select name="panel_model_id" data-panel-model-select required>
                        @foreach ($panelModels as $panel)
                            <option value="{{ $panel->id }}" data-power="{{ $panel->nominal_power_kw }}" @selected(old('panel_model_id') == $panel->id)>{{ $panel->brand }} {{ $panel->model }} - {{ $panel->nominal_power_kw }} kW</option>
                        @endforeach
                    </select>
                </label>
                <label>Cantidad de paneles
                    <input name="quantity" type="number" min="1" value="{{ old('quantity', 100) }}" data-panel-quantity required>
                </label>
                <label>Generacion esperada kWh
                    <input name="expected_kwh" type="number" step="0.01" min="1" value="{{ old('expected_kwh') }}" data-expected-kwh required>
                </label>
                <label>Generacion real kWh
                    <input name="actual_kwh" type="number" step="0.01" min="0" value="{{ old('actual_kwh') }}" placeholder="Lectura real del periodo" required>
                </label>
            </div>

            @if ($errors->any())
                <div class="error section">Revisa los campos marcados antes de guardar.</div>
            @endif

            <div class="nav section">
                <button class="btn primary" type="submit">Guardar granja</button>
                <a class="btn" href="{{ route('farms.index') }}">Cancelar</a>
            </div>
        </form>
    </section>

    <script>
        const municipalitiesByDepartment = @json($municipalitiesByDepartment);
        const municipalityCoordinates = @json($municipalityCoordinates);
        const departmentSelect = document.querySelector('[data-department-select]');
        const municipalitySelect = document.querySelector('[data-municipality-select]');
        const latitudeInput = document.querySelector('[name="latitude"]');
        const longitudeInput = document.querySelector('[name="longitude"]');
        const panelModelSelect = document.querySelector('[data-panel-model-select]');
        const panelQuantityInput = document.querySelector('[data-panel-quantity]');
        const expectedKwhInput = document.querySelector('[data-expected-kwh]');
        const capacityPreview = document.getElementById('capacity-preview');
        let expectedWasEdited = Boolean(expectedKwhInput.value);

        function refreshMunicipalities() {
            const current = municipalitySelect.dataset.current || municipalitySelect.value;
            const municipalities = municipalitiesByDepartment[departmentSelect.value] || [];
            municipalitySelect.innerHTML = '<option value="">Selecciona un municipio</option>';

            municipalities.forEach(municipality => {
                const option = document.createElement('option');
                option.value = municipality;
                option.textContent = municipality;
                option.selected = municipality === current;
                municipalitySelect.appendChild(option);
            });

            if (!municipalitySelect.value && municipalities.length > 0) {
                municipalitySelect.value = municipalities[0];
            }

            municipalitySelect.dataset.current = municipalitySelect.value;
        }

        function applyMunicipalityCoordinates() {
            const coordinates = municipalityCoordinates[departmentSelect.value]?.[municipalitySelect.value];

            if (!coordinates) return;

            latitudeInput.value = Number(coordinates.lat).toFixed(7);
            longitudeInput.value = Number(coordinates.lng).toFixed(7);
        }

        function calculateExpectedGeneration() {
            const selectedPanel = panelModelSelect.selectedOptions[0];
            const nominalPower = Number(selectedPanel?.dataset.power || 0);
            const quantity = Number(panelQuantityInput.value || 0);
            const installedCapacity = nominalPower * quantity;
            const peakSunHours = 5;
            const days = 30;
            const performanceFactor = 0.80;
            const expectedKwh = installedCapacity * peakSunHours * days * performanceFactor;

            capacityPreview.textContent = `${installedCapacity.toLocaleString('es-GT', {
                minimumFractionDigits: 1,
                maximumFractionDigits: 1,
            })} kW`;

            if (!expectedWasEdited) {
                expectedKwhInput.value = expectedKwh > 0 ? expectedKwh.toFixed(2) : '';
            }
        }

        departmentSelect.addEventListener('change', () => {
            municipalitySelect.dataset.current = '';
            refreshMunicipalities();
            applyMunicipalityCoordinates();
        });
        municipalitySelect.addEventListener('change', applyMunicipalityCoordinates);
        panelModelSelect.addEventListener('change', calculateExpectedGeneration);
        panelQuantityInput.addEventListener('input', calculateExpectedGeneration);
        expectedKwhInput.addEventListener('input', () => {
            expectedWasEdited = true;
        });
        refreshMunicipalities();
        applyMunicipalityCoordinates();
        calculateExpectedGeneration();
    </script>
@endsection
