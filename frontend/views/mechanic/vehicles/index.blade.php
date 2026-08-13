@extends('layouts.mechanic')

@section('title', 'Vehículos')
@section('heading', 'Consultar vehículos')
@section('subheading')
    Vehículos vinculados a tus órdenes y mantenimientos.
@endsection

@section('content')
    <div class="panel">
        <form method="GET" class="filters">
            <input type="text" name="search" value="{{ $search }}" placeholder="Buscar placa, marca...">
            <button type="submit" class="btn btn-secondary">Buscar</button>
        </form>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr><th>Placa</th><th>Marca</th><th>Modelo</th><th>Km</th><th>Estado</th><th></th></tr>
                </thead>
                <tbody>
                    @forelse ($vehicles as $vehicle)
                        <tr>
                            <td>{{ $vehicle->plate }}</td>
                            <td>{{ $vehicle->brand }}</td>
                            <td>{{ $vehicle->model }}</td>
                            <td>{{ number_format($vehicle->mileage) }} km</td>
                            <td><span class="badge {{ $vehicle->statusBadgeClass() }}">{{ $vehicle->statusLabel() }}</span></td>
                            <td><a href="{{ route('mechanic.vehicles.show', $vehicle) }}" class="btn btn-primary btn-sm">Ver ficha</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="6">No hay vehículos en tus órdenes asignadas.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div style="margin-top:12px;">{{ $vehicles->links('pagination.simple') }}</div>
    </div>
@endsection
