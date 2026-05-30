@extends('layouts.mechanic')

@section('title', 'Registrar mantenimiento')
@section('heading', 'Registrar mantenimiento')
@section('subheading')
    Crea un registro de servicio preventivo o correctivo.
@endsection

@section('content')
    <div class="panel">
        <form method="POST" action="{{ route('mechanic.maintenances.store') }}">
            @csrf
            <div class="form-grid">
                <div class="field">
                    <label>Orden vinculada (opcional)</label>
                    <select name="service_order_id" id="order_select">
                        <option value="">Sin orden</option>
                        @foreach ($orders as $order)
                            <option value="{{ $order->id }}" data-vehicle="{{ $order->vehicle_id }}" @selected(old('service_order_id', $selectedOrder) == $order->id)>
                                {{ $order->order_number }} — {{ $order->vehicle->plate }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label>Vehículo</label>
                    <select name="vehicle_id" id="vehicle_select" required>
                        <option value="">Seleccionar</option>
                        @foreach ($vehicles as $vehicle)
                            <option value="{{ $vehicle->id }}" @selected(old('vehicle_id') == $vehicle->id)>{{ $vehicle->plate }} — {{ $vehicle->brand }} {{ $vehicle->model }}</option>
                        @endforeach
                    </select>
                    @error('vehicle_id')<span style="color:#fda4af;font-size:0.78rem;">{{ $message }}</span>@enderror
                </div>
                <div class="field">
                    <label>Tipo</label>
                    <select name="type" required>
                        <option value="preventivo" @selected(old('type') === 'preventivo')>Preventivo</option>
                        <option value="correctivo" @selected(old('type') === 'correctivo')>Correctivo</option>
                    </select>
                </div>
                <div class="field">
                    <label>Estado</label>
                    <select name="status" required>
                        <option value="en_proceso" @selected(old('status', 'en_proceso') === 'en_proceso')>En proceso</option>
                        <option value="completado" @selected(old('status') === 'completado')>Completado</option>
                        <option value="pendiente" @selected(old('status') === 'pendiente')>Pendiente</option>
                    </select>
                </div>
                <div class="field" style="grid-column:1/-1;">
                    <label>Descripción del trabajo</label>
                    <input type="text" name="description" value="{{ old('description') }}" required placeholder="Ej: Cambio de aceite y filtros">
                    @error('description')<span style="color:#fda4af;font-size:0.78rem;">{{ $message }}</span>@enderror
                </div>
                <div class="field">
                    <label>Kilometraje</label>
                    <input type="number" name="mileage_at_service" value="{{ old('mileage_at_service') }}" min="0">
                </div>
                <div class="field">
                    <label>Costo ($)</label>
                    <input type="number" name="cost" value="{{ old('cost', 0) }}" min="0" step="0.01">
                </div>
                <div class="field">
                    <label>Fecha</label>
                    <input type="datetime-local" name="performed_at" value="{{ old('performed_at', now()->format('Y-m-d\TH:i')) }}">
                </div>
                <div class="field" style="grid-column:1/-1;">
                    <label>Repuestos utilizados</label>
                    <input type="text" name="parts_used" value="{{ old('parts_used') }}">
                </div>
                <div class="field" style="grid-column:1/-1;">
                    <label>Notas técnicas</label>
                    <textarea name="technical_notes" rows="3">{{ old('technical_notes') }}</textarea>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Guardar mantenimiento</button>
        </form>
    </div>
    <script>
        document.getElementById('order_select')?.addEventListener('change', function () {
            const opt = this.selectedOptions[0];
            if (opt?.dataset.vehicle) document.getElementById('vehicle_select').value = opt.dataset.vehicle;
        });
    </script>
@endsection
