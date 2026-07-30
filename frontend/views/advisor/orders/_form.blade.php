<div class="form-grid">
    @php $order = $order ?? null; @endphp
    <div class="field">
        <label for="vehicle_id">Vehículo *</label>
        <select name="vehicle_id" id="vehicle_id" required>
            <option value="">Seleccionar vehículo...</option>
            @foreach ($vehicles as $vehicle)
                <option value="{{ $vehicle->id }}" @selected(old('vehicle_id', optional($order)->vehicle_id) == $vehicle->id)>
                    {{ $vehicle->plate }} — {{ $vehicle->brand }} {{ $vehicle->model }} ({{ $vehicle->client->name }})
                </option>
            @endforeach
        </select>
    </div>

    <div class="field">
        <label for="mechanic_id">Mecánico asignado</label>
        <select name="mechanic_id" id="mechanic_id">
            <option value="">Sin asignar (asignar después)</option>
            @foreach ($mechanics as $mechanic)
                <option value="{{ $mechanic->id }}" @selected(old('mechanic_id', optional($order)->mechanic_id) == $mechanic->id)>
                    {{ $mechanic->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="field">
        <label for="priority">Prioridad *</label>
        <select name="priority" id="priority" required>
            @foreach (['baja' => 'Baja', 'normal' => 'Normal', 'alta' => 'Alta', 'urgente' => 'Urgente'] as $value => $label)
                <option value="{{ $value }}" @selected(old('priority', optional($order)->priority ?? 'normal') === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>

    <div class="field">
        <label for="scheduled_at">Fecha programada</label>
        <input type="datetime-local" name="scheduled_at" id="scheduled_at"
            value="{{ old('scheduled_at', isset($order) && $order->scheduled_at ? $order->scheduled_at->format('Y-m-d\TH:i') : '') }}">
    </div>

    <div class="field">
        <label for="estimated_cost">Costo estimado ($)</label>
        <input type="number" name="estimated_cost" id="estimated_cost" min="0" step="0.01"
            value="{{ old('estimated_cost', optional($order)->estimated_cost) }}">
    </div>

    @if (isset($order))
        <div class="field">
            <label for="status">Estado</label>
            <select name="status" id="status">
                @foreach (['recibida','en_proceso','completada','entregada','cancelada'] as $st)
                    <option value="{{ $st }}" @selected(old('status', $order->status) === $st)>{{ ucfirst(str_replace('_', ' ', $st)) }}</option>
                @endforeach
            </select>
        </div>
    @endif

    <div class="field" style="grid-column:1/-1;">
        <label for="description">Descripción del servicio *</label>
        <textarea name="description" id="description" rows="3" required placeholder="Ej: Cambio de aceite, revisión de frenos...">{{ old('description', optional($order)->description) }}</textarea>
    </div>
</div>
