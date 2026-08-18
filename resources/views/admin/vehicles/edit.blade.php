@extends('layouts.admin')

@section('title', 'Editar vehículo')
@section('heading', 'Editar vehículo')
@section('subheading')
    Actualiza los datos del vehículo {{ $vehicle->plate }}.
@endsection

@section('top-actions')
    <a href="{{ route('vehicles.index') }}" class="btn btn-secondary">← Volver</a>
@endsection

@section('content')
    <div class="panel">
        <form method="POST" action="{{ route('vehicles.update', $vehicle) }}">
            @csrf @method('PUT')
            @include('admin.vehicles._form', ['vehicle' => $vehicle, 'clients' => $clients])
            <div class="actions">
                <button type="submit" class="btn btn-primary">Actualizar vehículo</button>
                <a href="{{ route('vehicles.index') }}" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>
@endsection
