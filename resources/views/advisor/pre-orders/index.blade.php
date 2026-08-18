@extends('layouts.advisor')

@section('title', 'Preórdenes')
@section('heading', 'Gestión de preórdenes')
@section('subheading', 'Administra preórdenes manuales y generadas por el chatbot.')

@section('top-actions')
    <a href="{{ route('advisor.pre-orders.create') }}" class="btn btn-primary">+ Nueva preorden</a>
@endsection

@section('content')
    <div class="panel">
        <form method="GET" action="{{ route('advisor.pre-orders.index') }}" class="filters">
            <input type="text" name="search" value="{{ $search }}" placeholder="Buscar por cliente o vehículo...">
            <select name="source">
                <option value="">Todas las fuentes</option>
                <option value="manual" @selected($source === 'manual')">Manual</option>
                <option value="chatbot" @selected($source === 'chatbot')">Chatbot</option>
            </select>
            <select name="status">
                <option value="">Todos los estados</option>
                <option value="pendiente" @selected($status === 'pendiente')">Pendiente</option>
                <option value="confirmada" @selected($status === 'confirmada')">Confirmada</option>
                <option value="rechazada" @selected($status === 'rechazada')">Rechazada</option>
                <option value="convertida" @selected($status === 'convertida')">Convertida</option>
            </select>
            <button type="submit" class="btn btn-secondary">Filtrar</button>
            @if ($search || $source || $status)
                <a href="{{ route('advisor.pre-orders.index') }}" class="btn btn-secondary">Limpiar</a>
            @endif
        </form>

        <table class="table">
            <thead>
                <tr>
                    <th>Cliente</th>
                    <th>Vehículo</th>
                    <th>Fecha solicitada</th>
                    <th>Tipo de servicio</th>
                    <th>Prioridad</th>
                    <th>Fuente</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($preOrders as $preOrder)
                    <tr>
                        <td>{{ $preOrder->client->name }}</td>
                        <td>{{ $preOrder->vehicle->plate }} - {{ $preOrder->vehicle->brand }} {{ $preOrder->vehicle->model }}</td>
                        <td>{{ $preOrder->requested_date->format('d/m/Y') }}</td>
                        <td>{{ $preOrder->service_type }}</td>
                        <td>
                            <span class="badge {{ $preOrder->priority === 'urgente' ? 'red' : ($preOrder->priority === 'alta' ? 'orange' : 'green') }}">
                                {{ ucfirst($preOrder->priority) }}
                            </span>
                        </td>
                        <td>
                            <span class="badge {{ $preOrder->source === 'chatbot' ? 'blue' : 'gray' }}">
                                {{ ucfirst($preOrder->source) }}
                            </span>
                        </td>
                        <td>
                            <span class="badge {{ $preOrder->status === 'convertida' ? 'green' : ($preOrder->status === 'rechazada' ? 'red' : 'yellow') }}">
                                {{ $preOrder->statusLabel() }}
                            </span>
                        </td>
                        <td>
                            <div class="actions-inline">
                                <a href="{{ route('advisor.pre-orders.show', $preOrder) }}" class="btn btn-secondary btn-sm">Ver</a>
                                @if ($preOrder->status === 'pendiente')
                                    <a href="{{ route('advisor.pre-orders.edit', $preOrder) }}" class="btn btn-secondary btn-sm">Editar</a>
                                    <form method="POST" action="{{ route('advisor.pre-orders.confirm', $preOrder) }}" style="display: inline;">
                                        @csrf
                                        <button type="submit" class="btn btn-success btn-sm">Confirmar</button>
                                    </form>
                                    <form method="POST" action="{{ route('advisor.pre-orders.reject', $preOrder) }}" style="display: inline;" onsubmit="return confirm('¿Rechazar esta preorden?')">
                                        @csrf
                                        <button type="submit" class="btn btn-danger btn-sm">Rechazar</button>
                                    </form>
                                @endif
                                @if (in_array($preOrder->status, ['pendiente', 'confirmada']))
                                    <form method="POST" action="{{ route('advisor.pre-orders.convert', $preOrder) }}" style="display: inline;" onsubmit="return confirm('¿Convertir esta preorden en orden de servicio?')">
                                        @csrf
                                        <button type="submit" class="btn btn-primary btn-sm">Convertir</button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8">No se encontraron preórdenes.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="pagination">
            {{ $preOrders->links('pagination.simple') }}
        </div>
    </div>
@endsection
