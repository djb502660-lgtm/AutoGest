@props(['maintenance' => null, 'vehicles', 'mechanics', 'orders'])

<div class="form-grid">
    <div class="field">
        <label for="vehicle_id">Vehículo</label>
        <select id="vehicle_id" name="vehicle_id" required>
            <option value="">Seleccionar vehículo</option>
            @foreach ($vehicles as $vehicle)
                <option value="{{ $vehicle->id }}" @selected(old('vehicle_id', $maintenance?->vehicle_id) == $vehicle->id)>
                    {{ $vehicle->plate }} — {{ $vehicle->brand }} {{ $vehicle->model }}
                </option>
            @endforeach
        </select>
        @error('vehicle_id')<span class="field-error">{{ $message }}</span>@enderror
    </div>
    <div class="field">
        <label for="mechanic_id">Mecánico</label>
        <select id="mechanic_id" name="mechanic_id" required>
            <option value="">Seleccionar mecánico</option>
            @foreach ($mechanics as $mechanic)
                <option value="{{ $mechanic->id }}" @selected(old('mechanic_id', $maintenance?->mechanic_id) == $mechanic->id)>{{ $mechanic->name }}</option>
            @endforeach
        </select>
        @error('mechanic_id')<span class="field-error">{{ $message }}</span>@enderror
    </div>
    <div class="field">
        <label for="service_order_id">Orden de servicio (opcional)</label>
        <select id="service_order_id" name="service_order_id">
            <option value="">Sin orden vinculada</option>
            @foreach ($orders as $order)
                <option value="{{ $order->id }}" @selected(old('service_order_id', $maintenance?->service_order_id) == $order->id)>
                    {{ $order->order_number }} — {{ $order->vehicle->plate }}
                </option>
            @endforeach
        </select>
        @error('service_order_id')<span class="field-error">{{ $message }}</span>@enderror
    </div>
    <div class="field">
        <label for="type">Tipo</label>
        <select id="type" name="type" required>
            <option value="preventivo" @selected(old('type', $maintenance?->type ?? 'preventivo') === 'preventivo')>Preventivo</option>
            <option value="correctivo" @selected(old('type', $maintenance?->type) === 'correctivo')>Correctivo</option>
        </select>
        @error('type')<span class="field-error">{{ $message }}</span>@enderror
    </div>
    <div class="field">
        <label for="description">Descripción</label>
        <input type="text" id="description" name="description" value="{{ old('description', $maintenance?->description) }}" required placeholder="Ej: Cambio de aceite">
        @error('description')<span class="field-error">{{ $message }}</span>@enderror
    </div>
    <div class="field">
        <label for="performed_at">Fecha de servicio</label>
        <input type="datetime-local" id="performed_at" name="performed_at" value="{{ old('performed_at', $maintenance?->performed_at?->format('Y-m-d\TH:i')) }}">
        @error('performed_at')<span class="field-error">{{ $message }}</span>@enderror
    </div>
    <div class="field">
        <label for="mileage_at_service">Kilometraje al servicio</label>
        <input type="number" id="mileage_at_service" name="mileage_at_service" value="{{ old('mileage_at_service', $maintenance?->mileage_at_service) }}" min="0">
        @error('mileage_at_service')<span class="field-error">{{ $message }}</span>@enderror
    </div>
    <div class="field">
        <label for="cost">Costo ($)</label>
        <input type="number" id="cost" name="cost" value="{{ old('cost', $maintenance?->cost ?? 0) }}" min="0" step="0.01" required>
        @error('cost')<span class="field-error">{{ $message }}</span>@enderror
    </div>
    <div class="field">
        <label for="status">Estado</label>
        <select id="status" name="status" required>
            <option value="pendiente" @selected(old('status', $maintenance?->status ?? 'pendiente') === 'pendiente')>Pendiente</option>
            <option value="en_proceso" @selected(old('status', $maintenance?->status) === 'en_proceso')>En proceso</option>
            <option value="completado" @selected(old('status', $maintenance?->status) === 'completado')>Completado</option>
            <option value="cancelado" @selected(old('status', $maintenance?->status) === 'cancelado')>Cancelado</option>
        </select>
        @error('status')<span class="field-error">{{ $message }}</span>@enderror
    </div>
    <div class="field" style="grid-column:1/-1;">
        <label for="parts_used">Repuestos utilizados</label>
        <input type="text" id="parts_used" name="parts_used" value="{{ old('parts_used', $maintenance?->parts_used) }}">
        @error('parts_used')<span class="field-error">{{ $message }}</span>@enderror
    </div>
    <div class="field" style="grid-column:1/-1;">
        <label for="technical_notes">Notas técnicas</label>
        <input type="text" id="technical_notes" name="technical_notes" value="{{ old('technical_notes', $maintenance?->technical_notes) }}">
        @error('technical_notes')<span class="field-error">{{ $message }}</span>@enderror
    </div>
</div>
