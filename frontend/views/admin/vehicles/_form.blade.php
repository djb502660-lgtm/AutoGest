@props(['vehicle' => null, 'clients'])

<div class="form-grid">
    <div class="field">
        <label for="client_id">Cliente propietario</label>
        <select id="client_id" name="client_id" required>
            <option value="">Seleccionar cliente</option>
            @foreach ($clients as $client)
                <option value="{{ $client->id }}" @selected(old('client_id', $vehicle?->client_id) == $client->id)>{{ $client->name }}</option>
            @endforeach
        </select>
        @error('client_id')<span class="field-error">{{ $message }}</span>@enderror
    </div>
    <div class="field">
        <label for="plate">Placa</label>
        <input type="text" id="plate" name="plate" value="{{ old('plate', $vehicle?->plate) }}" required>
        @error('plate')<span class="field-error">{{ $message }}</span>@enderror
    </div>
    <div class="field">
        <label for="status">Estado</label>
        <select id="status" name="status" required>
            <option value="activo" @selected(old('status', $vehicle?->status ?? 'activo') === 'activo')>Activo</option>
            <option value="en_taller" @selected(old('status', $vehicle?->status) === 'en_taller')>En taller</option>
            <option value="inactivo" @selected(old('status', $vehicle?->status) === 'inactivo')>Inactivo</option>
        </select>
        @error('status')<span class="field-error">{{ $message }}</span>@enderror
    </div>
    <div class="field">
        <label for="brand">Marca</label>
        <input type="text" id="brand" name="brand" value="{{ old('brand', $vehicle?->brand) }}" required>
        @error('brand')<span class="field-error">{{ $message }}</span>@enderror
    </div>
    <div class="field">
        <label for="model">Modelo</label>
        <input type="text" id="model" name="model" value="{{ old('model', $vehicle?->model) }}" required>
        @error('model')<span class="field-error">{{ $message }}</span>@enderror
    </div>
    <div class="field">
        <label for="sub_model">Sub Modelo</label>
        <input type="text" id="sub_model" name="sub_model" value="{{ old('sub_model', $vehicle?->sub_model) }}">
        @error('sub_model')<span class="field-error">{{ $message }}</span>@enderror
    </div>
    <div class="field">
        <label for="year">Año</label>
        <input type="number" id="year" name="year" value="{{ old('year', $vehicle?->year) }}" min="1980">
        @error('year')<span class="field-error">{{ $message }}</span>@enderror
    </div>
    <div class="field">
        <label for="color">Color</label>
        <input type="text" id="color" name="color" value="{{ old('color', $vehicle?->color) }}">
        @error('color')<span class="field-error">{{ $message }}</span>@enderror
    </div>
    <div class="field">
        <label for="mileage">Kilometraje</label>
        <input type="number" id="mileage" name="mileage" value="{{ old('mileage', $vehicle?->mileage ?? 0) }}" min="0" required>
        @error('mileage')<span class="field-error">{{ $message }}</span>@enderror
    </div>
</div>

<div class="form-section">
    <h3>Alertas y Fechas de Control Preventivo</h3>
    <div class="form-grid">
        <div class="field">
            <label for="insurance_expiry">Vencimiento seguro</label>
            <input type="date" id="insurance_expiry" name="insurance_expiry" value="{{ old('insurance_expiry', $vehicle?->insurance_expiry?->format('Y-m-d')) }}">
            @error('insurance_expiry')<span class="field-error">{{ $message }}</span>@enderror
        </div>
        <div class="field">
            <label for="inspection_expiry">Vencimiento revisión técnica</label>
            <input type="date" id="inspection_expiry" name="inspection_expiry" value="{{ old('inspection_expiry', $vehicle?->inspection_expiry?->format('Y-m-d')) }}">
            @error('inspection_expiry')<span class="field-error">{{ $message }}</span>@enderror
        </div>
    </div>
</div>

<div class="form-section" x-data="{ fichaAvanzadaOpen: false }">
    <button type="button" @click="fichaAvanzadaOpen = !fichaAvanzadaOpen" class="accordion-toggle">
        <span>Ficha Técnica Avanzada (Datos para Repuestos y Taller)</span>
        <span x-text="fichaAvanzadaOpen ? '▲ Ocultar Ficha' : '▼ Desplegar Ficha'"></span>
    </button>
    <div x-show="fichaAvanzadaOpen" x-transition class="form-grid">
        <div class="field">
            <label for="vin">Chasis / N° VIN</label>
            <input type="text" id="vin" name="vin" value="{{ old('vin', $vehicle?->vin) }}">
            @error('vin')<span class="field-error">{{ $message }}</span>@enderror
        </div>
        <div class="field">
            <label for="engine_number">Número de Motor</label>
            <input type="text" id="engine_number" name="engine_number" value="{{ old('engine_number', $vehicle?->engine_number) }}">
            @error('engine_number')<span class="field-error">{{ $message }}</span>@enderror
        </div>
        <div class="field">
            <label for="transmission_type">Caja de Cambios</label>
            <select id="transmission_type" name="transmission_type">
                <option value="">Seleccionar...</option>
                <option value="Manual" @selected(old('transmission_type', $vehicle?->transmission_type) === 'Manual')">Manual</option>
                <option value="Automatica" @selected(old('transmission_type', $vehicle?->transmission_type) === 'Automatica')">Automática</option>
                <option value="CVT" @selected(old('transmission_type', $vehicle?->transmission_type) === 'CVT')">CVT</option>
            </select>
            @error('transmission_type')<span class="field-error">{{ $message }}</span>@enderror
        </div>
        <div class="field">
            <label for="tire_size">Medidas de Neumáticos</label>
            <input type="text" id="tire_size" name="tire_size" value="{{ old('tire_size', $vehicle?->tire_size) }}">
            @error('tire_size')<span class="field-error">{{ $message }}</span>@enderror
        </div>
        <div class="field">
            <label for="registration_date">Fecha de Matriculación</label>
            <input type="date" id="registration_date" name="registration_date" value="{{ old('registration_date', $vehicle?->registration_date?->format('Y-m-d')) }}">
            @error('registration_date')<span class="field-error">{{ $message }}</span>@enderror
        </div>
        <div class="field">
            <label for="paint_reference">Referencia de Pintura</label>
            <input type="text" id="paint_reference" name="paint_reference" value="{{ old('paint_reference', $vehicle?->paint_reference) }}">
            @error('paint_reference')<span class="field-error">{{ $message }}</span>@enderror
        </div>
        <div class="field">
            <label for="transponder">Transponder</label>
            <input type="text" id="transponder" name="transponder" value="{{ old('transponder', $vehicle?->transponder) }}">
            @error('transponder')<span class="field-error">{{ $message }}</span>@enderror
        </div>
        <div class="field">
            <label for="radio_code">Código de Radio</label>
            <input type="text" id="radio_code" name="radio_code" value="{{ old('radio_code', $vehicle?->radio_code) }}">
            @error('radio_code')<span class="field-error">{{ $message }}</span>@enderror
        </div>
    </div>
</div>

<div class="form-section">
    <div class="field" style="grid-column:1/-1;">
        <label for="notes">Observaciones / Notas de Ingreso</label>
        <textarea id="notes" name="notes" rows="3">{{ old('notes', $vehicle?->notes) }}</textarea>
        @error('notes')<span class="field-error">{{ $message }}</span>@enderror
    </div>
</div>
