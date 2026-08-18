@props(['schedule' => null, 'clients', 'vehicles', 'mechanics', 'selectedDate' => null])

<div class="form-section">
    <h3>Información General y Asignación</h3>
    <div class="form-grid">
        <div class="field">
            <label for="client_id">Cliente (Propietario)</label>
            <select id="client_id" name="client_id">
                <option value="">Seleccionar cliente...</option>
                @foreach ($clients as $client)
                    <option value="{{ $client->id }}" @selected(old('client_id', $schedule?->client_id) == $client->id)>{{ $client->name }}</option>
                @endforeach
            </select>
            @error('client_id')<span class="field-error">{{ $message }}</span>@enderror
        </div>
        <div class="field">
            <label for="vehicle_id">Vehículo *</label>
            <select id="vehicle_id" name="vehicle_id" required>
                <option value="">Seleccionar vehículo...</option>
                @foreach ($vehicles as $vehicle)
                    <option value="{{ $vehicle->id }}" @selected(old('vehicle_id', $schedule?->vehicle_id) == $vehicle->id)">
                        {{ $vehicle->plate }} — {{ $vehicle->brand }} {{ $vehicle->model }}
                    </option>
                @endforeach
            </select>
            @error('vehicle_id')<span class="field-error">{{ $message }}</span>@enderror
        </div>
        <div class="field" style="grid-column: 1 / -1;">
            <label for="title">Motivo / Título de la Cita *</label>
            <input type="text" id="title" name="title" value="{{ old('title', $schedule?->title) }}" required placeholder="Ej: Mantenimiento Preventivo 50,000 Km">
            @error('title')<span class="field-error">{{ $message }}</span>@enderror
        </div>
        <div class="field">
            <label for="service_type">Tipo de Servicio *</label>
            <select id="service_type" name="service_type" required>
                <option value="preventivo" @selected(old('service_type', $schedule?->service_type ?? 'preventivo') === 'preventivo')">Preventivo (Rutina)</option>
                <option value="correctivo" @selected(old('service_type', $schedule?->service_type) === 'correctivo')">Correctivo (Reparación)</option>
                <option value="diagnostico" @selected(old('service_type', $schedule?->service_type) === 'diagnostico')">Diagnóstico / Revisión</option>
                <option value="garantia" @selected(old('service_type', $schedule?->service_type) === 'garantia')">Garantía / Retorno</option>
            </select>
            @error('service_type')<span class="field-error">{{ $message }}</span>@enderror
        </div>
    </div>
</div>

<div class="form-section">
    <h3>Fecha y Horario</h3>
    <div class="form-grid">
        <div class="field">
            <label for="scheduled_date">Fecha *</label>
            <input type="date" id="scheduled_date" name="scheduled_date" value="{{ old('scheduled_date', $schedule?->scheduled_date?->format('Y-m-d') ?? $selectedDate) }}" required>
            @error('scheduled_date')<span class="field-error">{{ $message }}</span>@enderror
        </div>
        <div class="field">
            <label for="start_time">Hora Inicio</label>
            <input type="time" id="start_time" name="start_time" value="{{ old('start_time', $schedule?->start_time ?? '08:00') }}">
            @error('start_time')<span class="field-error">{{ $message }}</span>@enderror
        </div>
        <div class="field">
            <label for="duration_minutes">Duración</label>
            <select id="duration_minutes" name="duration_minutes">
                <option value="30" @selected(old('duration_minutes', $schedule?->duration_minutes) === 30)">30 min</option>
                <option value="60" @selected(old('duration_minutes', $schedule?->duration_minutes) === 60)">60 min</option>
                <option value="90" @selected(old('duration_minutes', $schedule?->duration_minutes ?? 90) === 90)">90 min</option>
                <option value="120" @selected(old('duration_minutes', $schedule?->duration_minutes) === 120)">2 horas</option>
            </select>
            @error('duration_minutes')<span class="field-error">{{ $message }}</span>@enderror
        </div>
        <div class="field">
            <label>Hora Fin Est.</label>
            <input type="text" id="end_time_preview" class="readonly" value="{{ $schedule?->end_time ? \Carbon\Carbon::createFromFormat('H:i', $schedule->end_time)->format('h:i A') : '09:30 AM' }}" readonly>
        </div>
    </div>
</div>

<div class="form-section">
    <h3>Asignación y Detalles</h3>
    <div class="form-grid">
        <div class="field">
            <label for="assigned_mechanic_id">Mecánico / Asesor Asignado</label>
            <select id="assigned_mechanic_id" name="assigned_mechanic_id">
                <option value="">Sin asignar</option>
                @foreach ($mechanics as $mechanic)
                    <option value="{{ $mechanic->id }}" @selected(old('assigned_mechanic_id', $schedule?->assigned_mechanic_id) == $mechanic->id)>{{ $mechanic->name }}</option>
                @endforeach
            </select>
            @error('assigned_mechanic_id')<span class="field-error">{{ $message }}</span>@enderror
        </div>
        <div class="field">
            <label for="mileage_target">Kilometraje Actual / Objetivo</label>
            <input type="number" id="mileage_target" name="mileage_target" value="{{ old('mileage_target', $schedule?->mileage_target) }}" placeholder="Ej: 50000">
            @error('mileage_target')<span class="field-error">{{ $message }}</span>@enderror
        </div>
        <div class="field">
            <label for="status">Estado de la Cita *</label>
            <select id="status" name="status" required>
                <option value="programado" @selected(old('status', $schedule?->status ?? 'programado') === 'programado')">Programado</option>
                <option value="confirmado" @selected(old('status', $schedule?->status) === 'confirmado')">Confirmado</option>
                <option value="en_taller" @selected(old('status', $schedule?->status) === 'en_taller')">En Taller</option>
                <option value="cancelado" @selected(old('status', $schedule?->status) === 'cancelado')">Cancelado</option>
            </select>
            @error('status')<span class="field-error">{{ $message }}</span>@enderror
        </div>
        <div class="field" style="grid-column: 1 / -1;">
            <label for="notes">Notas Técnicas / Observaciones</label>
            <textarea id="notes" name="notes" rows="3" placeholder="Detalles o reportes del cliente...">{{ old('notes', $schedule?->notes) }}</textarea>
            @error('notes')<span class="field-error">{{ $message }}</span>@enderror
        </div>
    </div>
</div>

<script>
    const startTimeInput = document.getElementById('start_time');
    const durationSelect = document.getElementById('duration_minutes');
    const endTimePreview = document.getElementById('end_time_preview');

    function calculateEndTime() {
        const timeValue = startTimeInput.value;
        const duration = parseInt(durationSelect.value);

        if (timeValue && duration) {
            const [hours, minutes] = timeValue.split(':').map(Number);
            const date = new Date();
            date.setHours(hours, minutes + duration);

            let hh = date.getHours();
            let mm = date.getMinutes();
            const ampm = hh >= 12 ? 'PM' : 'AM';
            
            hh = hh % 12 || 12;
            mm = mm < 10 ? '0' + mm : mm;

            endTimePreview.value = `${hh}:${mm} ${ampm}`;
        }
    }

    startTimeInput.addEventListener('change', calculateEndTime);
    durationSelect.addEventListener('change', calculateEndTime);
    calculateEndTime();
</script>
