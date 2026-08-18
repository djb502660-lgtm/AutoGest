@extends('layouts.advisor')

@section('title', 'Clientes')
@section('heading', 'Gestión de clientes')
@section('subheading', 'Administra la información de los clientes.')

@section('top-actions')
    <a href="{{ route('advisor.clients.create') }}" class="btn btn-primary">+ Nuevo cliente</a>
@endsection

@section('content')
    <div class="panel">
        <form method="GET" action="{{ route('advisor.clients.index') }}" class="filters">
            <input type="text" name="search" value="{{ $search }}" placeholder="Buscar por nombre, email o teléfono...">
            <button type="submit" class="btn btn-secondary">Filtrar</button>
            @if ($search)
                <a href="{{ route('advisor.clients.index') }}" class="btn btn-secondary">Limpiar</a>
            @endif
        </form>

        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>Teléfono</th>
                        <th>Vehículos</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($clients as $client)
                        <tr>
                            <td>{{ $client->name }}</td>
                            <td>{{ $client->email }}</td>
                            <td>{{ $client->phone ?? '—' }}</td>
                            <td>{{ $client->vehicles->count() }}</td>
                            <td>
                                <span class="badge {{ $client->status === 'activo' ? 'green' : 'red' }}">
                                    {{ ucfirst($client->status) }}
                                </span>
                            </td>
                            <td>
                                <div class="actions-inline">
                                    <a href="{{ route('advisor.clients.show', $client) }}" class="btn btn-secondary btn-sm">Ver</a>
                                    <a href="{{ route('advisor.clients.edit', $client) }}" class="btn btn-secondary btn-sm">Editar</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">No se encontraron clientes.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pagination">
            {{ $clients->links('pagination.simple') }}
        </div>
    </div>
@endsection
