@extends('layouts.admin')

@section('title', 'Orden ' . $order->order_number)
@section('heading', 'Detalle de orden')
@section('subheading')
    Datos de la orden de servicio y el vehículo asociado.
@endsection

@section('top-actions')
    <a href="{{ route('admin.orders.index') }}" class="btn btn-secondary">← Lista</a>
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

    <!-- Evidencias Fotográficas (Sprint 5A.4 - Admin Full Access) -->
    @if ($order->photos->isNotEmpty())
        <div class="panel">
            <h3>Evidencias Fotográficas</h3>
            <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(200px, 1fr)); gap:12px;">
                @foreach ($order->photos as $photo)
                    <div style="border:1px solid #e2e8f0; border-radius:8px; overflow:hidden;">
                        @include('layouts.partials.photo-thumb', [
                            'photo' => $photo,
                            'gallery' => 'order-'.$order->id,
                            'class' => 'photo-thumb-cover',
                        ])
                        <div style="padding:8px; background:#f8fafc; border-top:1px solid #e2e8f0;">
                            <div style="font-size:0.75rem; font-weight:700; color:#64748b;">{{ $photo->type_label }}</div>
                            @if ($photo->description)
                                <div style="font-size:0.7rem; color:#475569; margin-top:4px;">{{ $photo->description }}</div>
                            @endif
                            <div style="font-size:0.65rem; color:#94a3b8; margin-top:4px;">{{ $photo->user->name }} · {{ $photo->created_at->format('d/m/Y H:i') }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
@endsection
