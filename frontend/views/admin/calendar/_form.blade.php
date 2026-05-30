@props(['schedule' => null, 'vehicles', 'mechanics', 'selectedDate' => null])

<div class="form-grid">
    <div class="field">
        <label for="vehicle_id">Vehículo</label>
        <select id="vehicle_id" name="vehicle_id" required>
            <option value="">Seleccionar vehículo</option>
            @foreach ($vehicles as $vehicle)
                <option value="{{ $vehicle->id }}" @selected(old('vehicle_id', $schedule?->vehicle_id) == $vehicle->id)>
                    {{ $vehicle->plate }} — {{ $vehicle->brand }} {{ $vehicle->model }}
                </option>
            @endforeach
        </select>
        @error('vehicle_id')<span class="field-error">{{ $message }}</span>@enderror
    </div>
    <div class="field">
        <label for="title">Título del evento</label>
        <input type="text" id="title" name="title" value="{{ old('title', $schedule?->title) }}" required placeholder="Ej: Cambio de aceite">
        @error('title')<span class="field-error">{{ $message }}</span>@enderror
    </div>
    <div class="field">
        <label for="maintenance_type">Tipo de mantenimiento</label>
        <input type="text" id="maintenance_type" name="maintenance_type" value="{{ old('maintenance_type', $schedule?->maintenance_type) }}" placeholder="Preventivo / Correctivo">
        @error('maintenance_type')<span class="field-error">{{ $message }}</span>@enderror
    </div>
    <div class="field">
        <label for="scheduled_date">Fecha programada</label>
        <input type="date" id="scheduled_date" name="scheduled_date" value="{{ old('scheduled_date', $schedule?->scheduled_date?->format('Y-m-d') ?? $selectedDate) }}" required>
        @error('scheduled_date')<span class="field-error">{{ $message }}</span>@enderror
    </div>
    <div class="field">
        <label for="assigned_mechanic_id">Mecánico asignado</label>
        <select id="assigned_mechanic_id" name="assigned_mechanic_id">
            <option value="">Sin asignar</option>
            @foreach ($mechanics as $mechanic)
                <option value="{{ $mechanic->id }}" @selected(old('assigned_mechanic_id', $schedule?->assigned_mechanic_id) == $mechanic->id)>{{ $mechanic->name }}</option>
            @endforeach
        </select>
        @error('assigned_mechanic_id')<span class="field-error">{{ $message }}</span>@enderror
    </div>
    <div class="field">
        <label for="mileage_target">Kilometraje objetivo</label>
        <input type="number" id="mileage_target" name="mileage_target" value="{{ old('mileage_target', $schedule?->mileage_target) }}" min="0">
        @error('mileage_target')<span class="field-error">{{ $message }}</span>@enderror
    </div>
    <div class="field">
        <label for="status">Estado</label>
        <select id="status" name="status" required>
            <option value="programado" @selected(old('status', $schedule?->status ?? 'programado') === 'programado')>Programado</option>
            <option value="completado" @selected(old('status', $schedule?->status) === 'completado')>Completado</option>
            <option value="vencido" @selected(old('status', $schedule?->status) === 'vencido')>Vencido</option>
            <option value="cancelado" @selected(old('status', $schedule?->status) === 'cancelado')>Cancelado</option>
        </select>
        @error('status')<span class="field-error">{{ $message }}</span>@enderror
    </div>
    <div class="field" style="grid-column:1/-1;">
        <label for="notes">Notas</label>
        <input type="text" id="notes" name="notes" value="{{ old('notes', $schedule?->notes) }}">
        @error('notes')<span class="field-error">{{ $message }}</span>@enderror
    </div>
</div>
