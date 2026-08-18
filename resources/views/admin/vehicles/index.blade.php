@extends('layouts.admin')

@section('title', 'Vehículos')
@section('heading', 'Gestión de vehículos')
@section('subheading', 'Registra y administra la flota vehicular del sistema.')

@section('top-actions')
    <a href="{{ route('vehicles.create') }}" class="btn btn-primary">+ Nuevo vehículo</a>
@endsection

@section('content')
    <div class="panel">
        <form method="GET" class="filters">
            <input type="text" name="search" value="{{ $search }}" placeholder="Buscar por placa, marca o modelo...">
            <select name="status">
                <option value="">Todos los estados</option>
                <option value="activo" @selected($status === 'activo')>Activo</option>
                <option value="inactivo" @selected($status === 'inactivo')>Inactivo</option>
                <option value="en_taller" @selected($status === 'en_taller')>En taller</option>
            </select>
            <button type="submit" class="btn btn-secondary">Filtrar</button>
            @if ($search || $status)
                <a href="{{ route('vehicles.index') }}" class="btn btn-secondary">Limpiar</a>
            @endif
        </form>

        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Placa</th>
                        <th>Marca</th>
                        <th>Modelo</th>
                        <th>Año</th>
                        <th>Kilometraje</th>
                        <th>Cliente</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($vehicles as $vehicle)
                        <tr>
                            <td>{{ $vehicle->plate }}</td>
                            <td>{{ $vehicle->brand }}</td>
                            <td>{{ $vehicle->model }}</td>
                            <td>{{ $vehicle->year ?? '—' }}</td>
                            <td>{{ number_format($vehicle->mileage) }} km</td>
                            <td>{{ $vehicle->client->name }}</td>
                            <td><span class="badge {{ $vehicle->statusBadgeClass() }}">{{ $vehicle->statusLabel() }}</span></td>
                            <td>
                                <div class="actions-inline">
                                    <a href="{{ route('vehicles.edit', $vehicle) }}" class="btn btn-secondary btn-sm">Editar</a>
                                    <form method="POST" action="{{ route('vehicles.destroy', $vehicle) }}" data-confirm="¿Eliminar o desactivar este vehículo?" data-confirm-title="Eliminar vehículo" data-confirm-label="Eliminar">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8">No hay vehículos registrados.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pagination">{{ $vehicles->links('pagination.simple') }}</div>
    </div>
@endsection
