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
        <label for="insurance_expiry">Vencimiento seguro</label>
        <input type="date" id="insurance_expiry" name="insurance_expiry" value="{{ old('insurance_expiry', $vehicle?->insurance_expiry?->format('Y-m-d')) }}">
        @error('insurance_expiry')<span class="field-error">{{ $message }}</span>@enderror
    </div>
    <div class="field">
        <label for="inspection_expiry">Vencimiento revisión técnica</label>
        <input type="date" id="inspection_expiry" name="inspection_expiry" value="{{ old('inspection_expiry', $vehicle?->inspection_expiry?->format('Y-m-d')) }}">
        @error('inspection_expiry')<span class="field-error">{{ $message }}</span>@enderror
    </div>
    <div class="field" style="grid-column:1/-1;">
        <label for="notes">Observaciones</label>
        <input type="text" id="notes" name="notes" value="{{ old('notes', $vehicle?->notes) }}">
        @error('notes')<span class="field-error">{{ $message }}</span>@enderror
    </div>
</div>
