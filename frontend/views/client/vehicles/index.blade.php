@extends('layouts.client')

@section('title', 'Mis vehículos')
@section('heading', 'Mis vehículos')
@section('subheading')
    Consulta la información de tus vehículos registrados.
@endsection

@section('content')
    <div class="panel">
        <form method="GET" class="filters">
            <input type="text" name="search" value="{{ $search }}" placeholder="Buscar placa, marca...">
            <button type="submit" class="btn btn-secondary">Buscar</button>
        </form>

        @forelse ($vehicles as $vehicle)
            <div class="vehicle-card">
                <div class="vehicle-thumb">🚗</div>
                <div style="flex:1;">
                    <h4>{{ $vehicle->brand }} {{ $vehicle->model }} {{ $vehicle->year }}</h4>
                    <p>Placa: {{ $vehicle->plate }} · {{ number_format($vehicle->mileage) }} km</p>
                    <span class="badge {{ $vehicle->statusBadgeClass() }}">{{ $vehicle->statusLabel() }}</span>
                </div>
                <a href="{{ route('client.vehicles.show', $vehicle) }}" class="btn btn-primary btn-sm" style="align-self:center;">Ver ficha</a>
            </div>
        @empty
            <p style="color:var(--muted);">No tienes vehículos registrados. Contacta al taller para registrarlos.</p>
        @endforelse

        <div style="margin-top:12px;">{{ $vehicles->links('pagination.simple') }}</div>
    </div>
@endsection
