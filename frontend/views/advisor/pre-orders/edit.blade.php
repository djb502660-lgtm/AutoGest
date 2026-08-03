@extends('layouts.advisor')

@section('title', 'Editar preorden')
@section('heading', 'Editar preorden')
@section('subheading', 'Modifica la información de la preorden.')

@section('content')
    <div class="panel">
        <form method="POST" action="{{ route('advisor.pre-orders.update', $preOrder) }}">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label for="client_id">Cliente *</label>
                <select id="client_id" name="client_id" required>
                    <option value="">Seleccionar cliente</option>
                    @foreach ($clients as $client)
                        <option value="{{ $client->id }}" {{ $preOrder->client_id == $client->id ? 'selected' : '' }}>{{ $client->name }} ({{ $client->email }})</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label for="vehicle_id">Vehículo *</label>
                <select id="vehicle_id" name="vehicle_id" required>
                    <option value="">Seleccionar vehículo</option>
                    @foreach ($vehicles as $vehicle)
                        <option value="{{ $vehicle->id }}" {{ $preOrder->vehicle_id == $vehicle->id ? 'selected' : '' }}>{{ $vehicle->plate }} - {{ $vehicle->brand }} {{ $vehicle->model }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label for="requested_date">Fecha solicitada *</label>
                <input type="date" id="requested_date" name="requested_date" value="{{ $preOrder->requested_date->format('Y-m-d') }}" required>
            </div>
            <div class="form-group">
                <label for="preferred_time">Hora preferida</label>
                <input type="text" id="preferred_time" name="preferred_time" value="{{ $preOrder->preferred_time }}" placeholder="Ej: 10:00 AM">
            </div>
            <div class="form-group">
                <label for="service_type">Tipo de servicio *</label>
                <input type="text" id="service_type" name="service_type" value="{{ $preOrder->service_type }}" required>
            </div>
            <div class="form-group">
                <label for="description">Descripción *</label>
                <textarea id="description" name="description" rows="3" required>{{ $preOrder->description }}</textarea>
            </div>
            <div class="form-group">
                <label for="priority">Prioridad *</label>
                <select id="priority" name="priority" required>
                    <option value="baja" {{ $preOrder->priority === 'baja' ? 'selected' : '' }}>Baja</option>
                    <option value="normal" {{ $preOrder->priority === 'normal' ? 'selected' : '' }}>Normal</option>
                    <option value="alta" {{ $preOrder->priority === 'alta' ? 'selected' : '' }}>Alta</option>
                    <option value="urgente" {{ $preOrder->priority === 'urgente' ? 'selected' : '' }}>Urgente</option>
                </select>
            </div>
            <div class="form-group">
                <label for="notes">Notas</label>
                <textarea id="notes" name="notes" rows="3">{{ $preOrder->notes }}</textarea>
            </div>
            <div class="form-actions">
                <a href="{{ route('advisor.pre-orders.index') }}" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary">Actualizar</button>
            </div>
        </form>
    </div>
@endsection
