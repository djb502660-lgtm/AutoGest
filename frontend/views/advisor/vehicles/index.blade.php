@extends('layouts.advisor')

@section('title', 'Vehículos')
@section('heading', 'Gestión de vehículos')
@section('subheading', 'Administra la información de los vehículos.')

@section('top-actions')
    <a href="{{ route('advisor.vehicles.create') }}" class="btn btn-primary">+ Nuevo vehículo</a>
@endsection

@section('content')
    <div class="panel">
        <form method="GET" action="{{ route('advisor.vehicles.index') }}" class="filters">
            <input type="text" name="search" value="{{ $search }}" placeholder="Buscar por placa, marca o modelo...">
            <select name="client">
                <option value="">Todos los clientes</option>
                @foreach ($clients as $client)
                    <option value="{{ $client->id }}" @selected($client == $client->id)>{{ $client->name }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-secondary">Filtrar</button>
            @if ($search || $client)
                <a href="{{ route('advisor.vehicles.index') }}" class="btn btn-secondary">Limpiar</a>
            @endif
        </form>

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
                        <td>{{ $vehicle->mileage }} km</td>
                        <td>{{ $vehicle->client->name }}</td>
                        <td>
                            <span class="badge {{ $vehicle->status === 'activo' ? 'green' : ($vehicle->status === 'en_taller' ? 'yellow' : 'red') }}">
                                {{ ucfirst($vehicle->status) }}
                            </span>
                        </td>
                        <td>
                            <div class="actions-inline">
                                <a href="{{ route('advisor.vehicles.show', $vehicle) }}" class="btn btn-secondary btn-sm">Ver</a>
                                <a href="{{ route('advisor.vehicles.edit', $vehicle) }}" class="btn btn-secondary btn-sm">Editar</a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8">No se encontraron vehículos.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="pagination">
            {{ $vehicles->links('pagination.simple') }}
        </div>
    </div>
@endsection
