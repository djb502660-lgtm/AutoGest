@extends('layouts.mechanic')

@section('title', 'Órdenes')
@section('heading', 'Órdenes de servicio')
@section('subheading')
    Consulta y gestiona las órdenes asignadas a ti.
@endsection

@section('content')
    <div class="panel">
        <form method="GET" class="filters">
            <input type="text" name="search" value="{{ $search }}" placeholder="Buscar orden o vehículo...">
            <select name="status">
                <option value="">Todos los estados</option>
                <option value="recibida" @selected($status === 'recibida')>Recibida</option>
                <option value="en_proceso" @selected($status === 'en_proceso')>En proceso</option>
                <option value="completada" @selected($status === 'completada')>Completada</option>
                <option value="entregada" @selected($status === 'entregada')>Entregada</option>
            </select>
            <button type="submit" class="btn btn-secondary">Filtrar</button>
        </form>

        <table class="table">
            <thead>
                <tr>
                    <th>N° Orden</th>
                    <th>Vehículo</th>
                    <th>Cliente</th>
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
                        <td>{{ Str::limit($order->description, 40) }}</td>
                        <td><span class="badge {{ $order->statusBadgeClass() }}">{{ $order->statusLabel() }}</span></td>
                        <td><a href="{{ route('mechanic.orders.show', $order) }}" class="btn btn-primary btn-sm">Ver detalle</a></td>
                    </tr>
                @empty
                    <tr><td colspan="6">No hay órdenes asignadas.</td></tr>
                @endforelse
            </tbody>
        </table>

        <div style="margin-top:12px;">{{ $orders->links('pagination.simple') }}</div>
    </div>
@endsection
