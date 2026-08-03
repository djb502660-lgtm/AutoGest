@extends('layouts.advisor')

@section('title', 'Nuevo vehículo')
@section('heading', 'Nuevo vehículo')
@section('subheading', 'Registra un nuevo vehículo en el sistema.')

@section('content')
    <div class="panel">
        <form method="POST" action="{{ route('advisor.vehicles.store') }}">
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
                <label for="plate">Placa *</label>
                <input type="text" id="plate" name="plate" required>
            </div>
            <div class="form-group">
                <label for="brand">Marca *</label>
                <input type="text" id="brand" name="brand" required>
            </div>
            <div class="form-group">
                <label for="model">Modelo *</label>
                <input type="text" id="model" name="model" required>
            </div>
            <div class="form-group">
                <label for="year">Año</label>
                <input type="number" id="year" name="year" min="1900" max="{{ date('Y') + 1 }}">
            </div>
            <div class="form-group">
                <label for="color">Color</label>
                <input type="text" id="color" name="color">
            </div>
            <div class="form-group">
                <label for="mileage">Kilometraje *</label>
                <input type="number" id="mileage" name="mileage" min="0" required>
            </div>
            <div class="form-group">
                <label for="vin">VIN</label>
                <input type="text" id="vin" name="vin">
            </div>
            <div class="form-group">
                <label for="status">Estado *</label>
                <select id="status" name="status" required>
                    <option value="activo" selected>Activo</option>
                    <option value="inactivo">Inactivo</option>
                    <option value="en_taller">En taller</option>
                </select>
            </div>
            <div class="form-group">
                <label for="insurance_expiry">Vencimiento seguro</label>
                <input type="date" id="insurance_expiry" name="insurance_expiry">
            </div>
            <div class="form-group">
                <label for="inspection_expiry">Vencimiento inspección</label>
                <input type="date" id="inspection_expiry" name="inspection_expiry">
            </div>
            <div class="form-group">
                <label for="notes">Notas</label>
                <textarea id="notes" name="notes" rows="3"></textarea>
            </div>
            <div class="form-actions">
                <a href="{{ route('advisor.vehicles.index') }}" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary">Guardar</button>
            </div>
        </form>
    </div>
@endsection
