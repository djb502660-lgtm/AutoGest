@extends('layouts.admin')

@section('title', 'Órdenes de servicio')
@section('heading', 'Órdenes de servicio')
@section('subheading', 'Revisa y gestiona todas las órdenes de servicio desde el panel administrativo.')

@section('top-actions')
    <a href="{{ route('admin.orders.index') }}" class="btn btn-secondary">Recargar</a>
@endsection

@section('content')
    <div class="panel">
        <form method="GET" class="filters">
            <input type="text" name="search" value="{{ $search }}" placeholder="Buscar orden o placa...">
            <select name="status">
                <option value="">Todos los estados</option>
                <option value="recibida" @selected($status === 'recibida')>Recibida</option>
                <option value="en_proceso" @selected($status === 'en_proceso')>En proceso</option>
                <option value="completada" @selected($status === 'completada')>Completada</option>
                <option value="entregada" @selected($status === 'entregada')>Entregada</option>
                <option value="cancelada" @selected($status === 'cancelada')>Cancelada</option>
            </select>
            <button type="submit" class="btn btn-secondary">Filtrar</button>
        </form>

        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>N° Orden</th>
                        <th>Vehículo</th>
                        <th>Cliente</th>
                        <th>Mecánico</th>
                        <th>Servicio</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($orders as $order)
                        <tr>
                            <td>{{ $order->order_number }}</td>
                            <td>{{ $order->vehicle->plate }}</td>
                            <td>{{ $order->client->name }}</td>
                            <td>{{ $order->mechanic?->name ?? 'Sin asignar' }}</td>
                            <td>{{ Str::limit($order->description, 35) }}</td>
                            <td><span class="badge {{ $order->statusBadgeClass() }}">{{ $order->statusLabel() }}</span></td>
                            <td>
                                <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-primary btn-sm">Ver</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7">No hay órdenes registradas.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="margin-top:12px;">{{ $orders->links('pagination.simple') }}</div>
    </div>
@endsection
