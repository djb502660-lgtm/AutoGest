@extends('layouts.client')

@section('title', 'Órdenes')
@section('heading', 'Órdenes de servicio')
@section('subheading')
    Seguimiento de tus órdenes en el taller.
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

        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr><th>N° Orden</th><th>Vehículo</th><th>Servicio</th><th>Fecha</th><th>Estado</th><th></th></tr>
                </thead>
                <tbody>
                    @forelse ($orders as $order)
                        <tr>
                            <td>{{ $order->order_number }}</td>
                            <td>{{ $order->vehicle->plate }}</td>
                            <td>{{ Str::limit($order->description, 40) }}</td>
                            <td>{{ $order->created_at->format('d/m/Y') }}</td>
                            <td><span class="badge {{ $order->statusBadgeClass() }}">{{ $order->statusLabel() }}</span></td>
                            <td><a href="{{ route('client.orders.show', $order) }}" class="btn btn-primary btn-sm">Ver</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="6">No hay órdenes registradas.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div style="margin-top:12px;">{{ $orders->links('pagination.simple') }}</div>
    </div>
@endsection
