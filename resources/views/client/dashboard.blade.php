@extends('layouts.client')

@section('title', 'Dashboard')
@section('heading', 'Dashboard principal')
@section('subheading')
    Resumen de tus vehículos, servicios y alertas.
@endsection

@section('content')
    <div class="stats">
        <div class="stat"><span>Vehículos</span><strong>{{ $stats['vehiculos'] }}</strong></div>
        <div class="stat"><span>Próximo servicio</span><strong>{{ $stats['proximo_servicio'] }}</strong></div>
        <div class="stat"><span>Servicios realizados</span><strong>{{ $stats['servicios_realizados'] }}</strong></div>
        <div class="stat"><span>Gastos totales</span><strong>${{ number_format($stats['gastos_totales'], 0) }}</strong></div>
    </div>

    <div class="panel">
        <h3 style="margin:0 0 12px;font-size:1rem;">Estado de mis vehículos</h3>
        @forelse ($vehicles as $vehicle)
            @php $next = $vehicle->maintenanceSchedules->first(); @endphp
            <div class="vehicle-card">
                <div class="vehicle-thumb">🚗</div>
                <div>
                    <h4>{{ $vehicle->brand }} {{ $vehicle->model }} {{ $vehicle->year }}</h4>
                    <p>{{ $vehicle->plate }} · {{ number_format($vehicle->mileage) }} km</p>
                    <p>
                        <span class="badge {{ $vehicle->statusBadgeClass() }}">{{ $vehicle->statusLabel() }}</span>
                        @if ($next)
                            · Próximo: {{ $next->scheduled_date->format('d/m/Y') }}
                        @endif
                    </p>
                </div>
                <a href="{{ route('client.vehicles.show', $vehicle) }}" class="btn btn-secondary btn-sm" style="margin-left:auto;align-self:center;">Ver</a>
            </div>
        @empty
            <p style="color:var(--muted);font-size:0.84rem;">No tienes vehículos registrados.</p>
        @endforelse
        <a href="{{ route('client.vehicles.index') }}" class="btn btn-secondary btn-sm">Ver todos</a>
    </div>

    <div class="panel">
        <h3 style="margin:0 0 12px;font-size:1rem;">Órdenes de servicio recientes</h3>
        <div class="table-responsive">
            <table class="table">
                <thead><tr><th>Orden</th><th>Vehículo</th><th>Servicio</th><th>Estado</th><th></th></tr></thead>
                <tbody>
                    @forelse ($recentOrders as $order)
                        <tr>
                            <td>{{ $order->order_number }}</td>
                            <td>{{ $order->vehicle->plate }}</td>
                            <td>{{ Str::limit($order->description, 35) }}</td>
                            <td><span class="badge {{ $order->statusBadgeClass() }}">{{ $order->statusLabel() }}</span></td>
                            <td><a href="{{ route('client.orders.show', $order) }}" class="btn btn-secondary btn-sm">Ver</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="5">No hay órdenes registradas.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
