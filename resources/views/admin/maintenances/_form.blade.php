@props(['maintenance' => null, 'vehicles', 'mechanics', 'orders'])

<div class="form-section">
    <h3>1. Información General y Asignación</h3>
    <div class="form-grid">
        <div class="field">
            <label for="vehicle_id">Vehículo *</label>
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
            <label for="mechanic_id">Mecánico Responsable *</label>
            <select id="mechanic_id" name="mechanic_id" required>
                <option value="">Seleccionar técnico</option>
                @foreach ($mechanics as $mechanic)
                    <option value="{{ $mechanic->id }}" @selected(old('mechanic_id', $maintenance?->mechanic_id) == $mechanic->id)>{{ $mechanic->name }}</option>
                @endforeach
            </select>
            @error('mechanic_id')<span class="field-error">{{ $message }}</span>@enderror
        </div>
        <div class="field">
            <label for="service_order_id">Orden de Servicio Vinculada</label>
            <select id="service_order_id" name="service_order_id">
                <option value="">Directo (Sin orden previa)</option>
                @foreach ($orders as $order)
                    <option value="{{ $order->id }}" @selected(old('service_order_id', $maintenance?->service_order_id) == $order->id)">
                        #{{ $order->id }} — {{ $order->vehicle->plate }}
                    </option>
                @endforeach
            </select>
            @error('service_order_id')<span class="field-error">{{ $message }}</span>@enderror
        </div>
        <div class="field" style="grid-column: 1 / -1;">
            <label for="description">Descripción General del Trabajo *</label>
            <input type="text" id="description" name="description" value="{{ old('description', $maintenance?->description) }}" required placeholder="Ej: Mantenimiento preventivo 50,000 km">
            @error('description')<span class="field-error">{{ $message }}</span>@enderror
        </div>
        <div class="field">
            <label for="type">Tipo de Servicio *</label>
            <select id="type" name="type" required>
                <option value="preventivo" @selected(old('type', $maintenance?->type ?? 'preventivo') === 'preventivo')">Preventivo (Rutina)</option>
                <option value="correctivo" @selected(old('type', $maintenance?->type) === 'correctivo')">Correctivo (Reparación)</option>
                <option value="garantia" @selected(old('type', $maintenance?->type) === 'garantia')">Garantía / Revisión</option>
            </select>
            @error('type')<span class="field-error">{{ $message }}</span>@enderror
        </div>
    </div>
</div>

<div class="form-section">
    <h3>2. Inspección y Recepción del Vehículo</h3>
    <div class="form-grid">
        <div class="field">
            <label for="mileage_at_service">Kilometraje de Ingreso *</label>
            <input type="number" id="mileage_at_service" name="mileage_at_service" value="{{ old('mileage_at_service', $maintenance?->mileage_at_service) }}" min="0" required>
            @error('mileage_at_service')<span class="field-error">{{ $message }}</span>@enderror
        </div>
        <div class="field">
            <label for="fuel_level">Nivel de Combustible *</label>
            <select id="fuel_level" name="fuel_level" required>
                <option value="Reserva" @selected(old('fuel_level', $maintenance?->fuel_level) === 'Reserva')">Reserva / Muy Bajo</option>
                <option value="1/4" @selected(old('fuel_level', $maintenance?->fuel_level) === '1/4')">1/4 de Tanque</option>
                <option value="1/2" @selected(old('fuel_level', $maintenance?->fuel_level ?? '1/2') === '1/2')">1/2 Tanque</option>
                <option value="3/4" @selected(old('fuel_level', $maintenance?->fuel_level) === '3/4')">3/4 de Tanque</option>
                <option value="Lleno" @selected(old('fuel_level', $maintenance?->fuel_level) === 'Lleno')">Tanque Lleno</option>
            </select>
            @error('fuel_level')<span class="field-error">{{ $message }}</span>@enderror
        </div>
        <div class="field">
            <label for="performed_at">Fecha / Hora de Ingreso *</label>
            <input type="datetime-local" id="performed_at" name="performed_at" value="{{ old('performed_at', $maintenance?->performed_at?->format('Y-m-d\TH:i')) }}" required>
            @error('performed_at')<span class="field-error">{{ $message }}</span>@enderror
        </div>
        <div class="field">
            <label for="status">Estado de la Reparación *</label>
            <select id="status" name="status" required>
                <option value="pendiente" @selected(old('status', $maintenance?->status ?? 'pendiente') === 'pendiente')">Pendiente</option>
                <option value="en_proceso" @selected(old('status', $maintenance?->status) === 'en_proceso')">En Proceso</option>
                <option value="completado" @selected(old('status', $maintenance?->status) === 'completado')">Completado / Listo</option>
                <option value="cancelado" @selected(old('status', $maintenance?->status) === 'cancelado')">Cancelado</option>
            </select>
            @error('status')<span class="field-error">{{ $message }}</span>@enderror
        </div>
    </div>
    <div class="field" style="margin-top: 1rem;">
        <label>Inventario / Pertenencias Dejadas en el Auto</label>
        <div class="checkbox-grid">
            <label class="checkbox-item">
                <input type="checkbox" name="inventory_spare_wheel" value="1" @checked(old('inventory_spare_wheel', $maintenance?->inventory_spare_wheel ?? true))> Rueda de repuesto
            </label>
            <label class="checkbox-item">
                <input type="checkbox" name="inventory_tools" value="1" @checked(old('inventory_tools', $maintenance?->inventory_tools ?? true))> Gata hidráulica / Herramientas
            </label>
            <label class="checkbox-item">
                <input type="checkbox" name="inventory_radio" value="1" @checked(old('inventory_radio', $maintenance?->inventory_radio ?? true))> Radio / Pantalla original
            </label>
            <label class="checkbox-item">
                <input type="checkbox" name="inventory_documents" value="1" @checked(old('inventory_documents', $maintenance?->inventory_documents ?? false))> Documentos del vehículo
            </label>
        </div>
    </div>
</div>

<div class="form-section">
    <h3>3. Repuestos, Costos y Diagnóstico Técnico</h3>
    <div class="form-grid">
        <div class="field" style="grid-column: 1 / -1;">
            <label for="parts_used">Repuestos Utilizados (Insumos / Stock)</label>
            <textarea id="parts_used" name="parts_used" rows="3" placeholder="Detalle repuestos consumidos...">{{ old('parts_used', $maintenance?->parts_used) }}</textarea>
            @error('parts_used')<span class="field-error">{{ $message }}</span>@enderror
        </div>
        <div class="field" style="grid-column: 1 / -1;">
            <label for="technical_notes">Notas Técnicas / Observaciones del Mecánico</label>
            <textarea id="technical_notes" name="technical_notes" rows="3" placeholder="Observaciones sobre fallas o recomendaciones...">{{ old('technical_notes', $maintenance?->technical_notes) }}</textarea>
            @error('technical_notes')<span class="field-error">{{ $message }}</span>@enderror
        </div>
    </div>
    <div class="cost-grid">
        <div class="field">
            <label for="parts_cost">Costo Total Repuestos ($)</label>
            <input type="number" id="parts_cost" name="parts_cost" value="{{ old('parts_cost', $maintenance?->parts_cost ?? 0) }}" min="0" step="0.01">
            @error('parts_cost')<span class="field-error">{{ $message }}</span>@enderror
        </div>
        <div class="field">
            <label for="labor_cost">Costo Mano de Obra ($)</label>
            <input type="number" id="labor_cost" name="labor_cost" value="{{ old('labor_cost', $maintenance?->labor_cost ?? 0) }}" min="0" step="0.01">
            @error('labor_cost')<span class="field-error">{{ $message }}</span>@enderror
        </div>
    </div>
</div>
