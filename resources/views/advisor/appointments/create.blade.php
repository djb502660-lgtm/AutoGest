@extends('layouts.advisor')

@section('title', 'Nueva cita')
@section('heading', 'Nueva cita')
@section('subheading', 'Agenda una nueva cita.')

@section('content')
    <div class="panel">
        <form method="POST" action="{{ route('advisor.appointments.store') }}">
            @csrf
            <div class="form-group">
                <label for="client_id">Cliente *</label>
                <select id="client_id" name="client_id" required>
                    <option value="">Seleccionar cliente</option>
                    @foreach ($clients as $client)
                        <option value="{{ $client->id }}">{{ $client->name }} ({{ $client->email }})</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label for="vehicle_id">Vehículo *</label>
                <select id="vehicle_id" name="vehicle_id" required>
                    <option value="">Seleccionar vehículo</option>
                    @foreach ($vehicles as $vehicle)
                        <option value="{{ $vehicle->id }}">{{ $vehicle->plate }} - {{ $vehicle->brand }} {{ $vehicle->model }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label for="requested_date">Fecha *</label>
                <input type="date" id="requested_date" name="requested_date" required>
            </div>
            <div class="form-group">
                <label for="preferred_time">Hora preferida</label>
                <input type="text" id="preferred_time" name="preferred_time" placeholder="Ej: 10:00 AM">
            </div>
            <div class="form-group">
                <label for="service_type">Tipo de servicio *</label>
                <input type="text" id="service_type" name="service_type" required>
            </div>
            <div class="form-group">
                <label for="description">Descripción *</label>
                <textarea id="description" name="description" rows="3" required></textarea>
            </div>
            <div class="form-group">
                <label for="priority">Prioridad *</label>
                <select id="priority" name="priority" required>
                    <option value="baja">Baja</option>
                    <option value="normal" selected>Normal</option>
                    <option value="alta">Alta</option>
                    <option value="urgente">Urgente</option>
                </select>
            </div>
            <div class="form-group">
                <label for="notes">Notas</label>
                <textarea id="notes" name="notes" rows="3"></textarea>
            </div>
            <div class="form-actions">
                <a href="{{ route('advisor.appointments.index') }}" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary">Guardar</button>
            </div>
        </form>
    </div>
@endsection
