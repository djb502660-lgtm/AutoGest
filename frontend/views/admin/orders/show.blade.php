@extends('layouts.admin')

@section('title', 'Orden ' . $order->order_number)
@section('heading', 'Detalle de orden')
@section('subheading')
    Datos de la orden de servicio y el vehículo asociado.
@endsection

@section('top-actions')
    <a href="{{ route('admin.orders.index') }}" class="btn btn-secondary">← Lista</a>
    <a href="{{ route('admin.orders.invoice', $order) }}" class="btn btn-primary">Factura</a>
@endsection

@section('content')
    <div class="grid-2">
        <div class="panel">
            <h3>Información del vehículo</h3>
            <p><strong>Placa:</strong> {{ $order->vehicle->plate }}</p>
            <p><strong>Marca / Modelo:</strong> {{ $order->vehicle->brand }} {{ $order->vehicle->model }}</p>
            <p><strong>Año:</strong> {{ $order->vehicle->year }}</p>
            <p><strong>Cliente:</strong> {{ $order->client->name }}</p>
            <p><strong>Teléfono:</strong> {{ $order->client->phone ?? '—' }}</p>
        </div>
        <div class="panel">
            <h3>Detalles de la orden</h3>
            <p><strong>Orden:</strong> {{ $order->order_number }}</p>
            <p><strong>Descripción:</strong> {{ $order->description }}</p>
            <p><strong>Estado:</strong> <span class="badge {{ $order->statusBadgeClass() }}">{{ $order->statusLabel() }}</span></p>
            <p><strong>Prioridad:</strong> {{ ucfirst($order->priority) }}</p>
            <p><strong>Mecánico:</strong> {{ $order->mechanic?->name ?? 'Sin asignar' }}</p>
            <p><strong>Programada:</strong> {{ $order->scheduled_at?->format('d/m/Y H:i') ?? '—' }}</p>
            <p><strong>Costo estimado:</strong> ${{ number_format($order->estimated_cost ?? 0, 2) }}</p>
            <p><strong>Total:</strong> ${{ number_format($order->total_cost ?? 0, 2) }}</p>
        </div>
    </div>

    @if ($order->maintenances->isNotEmpty())
        <div class="panel">
            <h3>Mantenimientos vinculados</h3>
            <table class="table">
                <thead><tr><th>Fecha</th><th>Descripción</th><th>Costo</th><th>Estado</th></tr></thead>
                <tbody>
                    @foreach ($order->maintenances as $maintenance)
                        <tr>
                            <td>{{ $maintenance->performed_at?->format('d/m/Y') ?? '—' }}</td>
                            <td>{{ $maintenance->description }}</td>
                            <td>${{ number_format($maintenance->cost, 2) }}</td>
                            <td>{{ ucfirst($maintenance->status) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
@endsection
