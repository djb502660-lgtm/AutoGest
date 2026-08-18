@extends('layouts.advisor')

@section('title', 'Editar vehículo')
@section('heading', 'Editar vehículo')
@section('subheading', 'Modifica la información del vehículo.')

@section('content')
    <div class="panel">
        <form method="POST" action="{{ route('advisor.vehicles.update', $vehicle) }}">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label for="client_id">Cliente *</label>
                <select id="client_id" name="client_id" required>
                    <option value="">Seleccionar cliente</option>
                    @foreach ($clients as $client)
                        <option value="{{ $client->id }}" {{ $vehicle->client_id == $client->id ? 'selected' : '' }}>{{ $client->name }} ({{ $client->email }})</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label for="plate">Placa *</label>
                <input type="text" id="plate" name="plate" value="{{ $vehicle->plate }}" required>
            </div>
            <div class="form-group">
                <label for="brand">Marca *</label>
                <input type="text" id="brand" name="brand" value="{{ $vehicle->brand }}" required>
            </div>
            <div class="form-group">
                <label for="model">Modelo *</label>
                <input type="text" id="model" name="model" value="{{ $vehicle->model }}" required>
            </div>
            <div class="form-group">
                <label for="year">Año</label>
                <input type="number" id="year" name="year" value="{{ $vehicle->year }}" min="1900" max="{{ date('Y') + 1 }}">
            </div>
            <div class="form-group">
                <label for="color">Color</label>
                <input type="text" id="color" name="color" value="{{ $vehicle->color }}">
            </div>
            <div class="form-group">
                <label for="mileage">Kilometraje *</label>
                <input type="number" id="mileage" name="mileage" value="{{ $vehicle->mileage }}" min="0" required>
            </div>
            <div class="form-group">
                <label for="vin">VIN</label>
                <input type="text" id="vin" name="vin" value="{{ $vehicle->vin }}">
            </div>
            <div class="form-group">
                <label for="status">Estado *</label>
                <select id="status" name="status" required>
                    <option value="activo" {{ $vehicle->status === 'activo' ? 'selected' : '' }}>Activo</option>
                    <option value="inactivo" {{ $vehicle->status === 'inactivo' ? 'selected' : '' }}>Inactivo</option>
                    <option value="en_taller" {{ $vehicle->status === 'en_taller' ? 'selected' : '' }}>En taller</option>
                </select>
            </div>
            <div class="form-group">
                <label for="insurance_expiry">Vencimiento seguro</label>
                <input type="date" id="insurance_expiry" name="insurance_expiry" value="{{ $vehicle->insurance_expiry?->format('Y-m-d') }}">
            </div>
            <div class="form-group">
                <label for="inspection_expiry">Vencimiento inspección</label>
                <input type="date" id="inspection_expiry" name="inspection_expiry" value="{{ $vehicle->inspection_expiry?->format('Y-m-d') }}">
            </div>
            <div class="form-group">
                <label for="notes">Notas</label>
                <textarea id="notes" name="notes" rows="3">{{ $vehicle->notes }}</textarea>
            </div>
            <div class="form-actions">
                <a href="{{ route('advisor.vehicles.index') }}" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary">Actualizar</button>
            </div>
        </form>
    </div>
@endsection
