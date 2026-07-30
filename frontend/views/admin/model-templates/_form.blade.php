<div class="form-grid">
    <div class="field">
        <label for="brand">Marca *</label>
        <input type="text" id="brand" name="brand" value="{{ old('brand', optional($template)->brand) }}" required placeholder="Toyota">
        @error('brand')<span class="field-error">{{ $message }}</span>@enderror
    </div>
    <div class="field">
        <label for="model">Modelo *</label>
        <input type="text" id="model" name="model" value="{{ old('model', optional($template)->model) }}" required placeholder="Corolla">
        @error('model')<span class="field-error">{{ $message }}</span>@enderror
    </div>
    <div class="field">
        <label for="maintenance_type">Tipo *</label>
        <select id="maintenance_type" name="maintenance_type" required>
            <option value="preventivo" @selected(old('maintenance_type', optional($template)->maintenance_type ?? 'preventivo') === 'preventivo')>Preventivo</option>
            <option value="correctivo" @selected(old('maintenance_type', optional($template)->maintenance_type) === 'correctivo')>Correctivo</option>
        </select>
    </div>
    <div class="field">
        <label for="title">Título del servicio *</label>
        <input type="text" id="title" name="title" value="{{ old('title', optional($template)->title) }}" required placeholder="Cambio de aceite 10.000 km">
    </div>
    <div class="field">
        <label for="interval_km">Intervalo (km)</label>
        <input type="number" id="interval_km" name="interval_km" min="1000" step="1000" value="{{ old('interval_km', optional($template)->interval_km) }}">
    </div>
    <div class="field">
        <label for="interval_months">Intervalo (meses)</label>
        <input type="number" id="interval_months" name="interval_months" min="1" max="60" value="{{ old('interval_months', optional($template)->interval_months) }}">
    </div>
    <div class="field">
        <label for="sort_order">Orden</label>
        <input type="number" id="sort_order" name="sort_order" min="0" value="{{ old('sort_order', optional($template)->sort_order ?? 0) }}">
    </div>
    <div class="field">
        <label><input type="checkbox" name="is_active" value="1" @checked(old('is_active', optional($template)->is_active ?? true))> Activa</label>
    </div>
    <div class="field" style="grid-column:1/-1;">
        <label for="description">Descripción</label>
        <textarea id="description" name="description" rows="3">{{ old('description', optional($template)->description) }}</textarea>
    </div>
</div>
