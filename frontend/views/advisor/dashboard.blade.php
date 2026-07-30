@extends('layouts.advisor')

@section('title', 'Dashboard')
@section('heading', 'Dashboard principal')
@section('subheading')
    Resumen de órdenes registradas y pendientes de asignar al taller.
@endsection

@section('top-actions')
    <a href="{{ route('advisor.orders.create') }}" class="btn btn-primary">Nueva orden</a>
@endsection

@section('content')
    <div class="stats">
        <div class="stat"><span>Total órdenes</span><strong>{{ $stats['total'] }}</strong></div>
        <div class="stat"><span>Solicitudes chatbot</span><strong>{{ $stats['solicitudes_chatbot'] }}</strong></div>
        <div class="stat"><span>Sin mecánico</span><strong>{{ $stats['sin_mecanico'] }}</strong></div>
        <div class="stat"><span>En proceso</span><strong>{{ $stats['en_proceso'] }}</strong></div>
    </div>

    <div class="grid-2">
        <div class="panel">
            <h3 style="margin:0 0 12px;font-size:1rem;">Órdenes recientes</h3>
            <table class="table">
                <thead>
                    <tr><th>Orden</th><th>Vehículo</th><th>Mecánico</th><th>Estado</th><th></th></tr>
                </thead>
                <tbody>
                    @forelse ($recentOrders as $order)
                        <tr>
                            <td>{{ $order->order_number }}</td>
                            <td>{{ $order->vehicle->plate }}</td>
                            <td>{{ $order->mechanic?->name ?? '—' }}</td>
                            <td><span class="badge {{ $order->statusBadgeClass() }}">{{ $order->statusLabel() }}</span></td>
                            <td><a href="{{ route('advisor.orders.show', $order) }}" class="btn btn-secondary btn-sm">Ver</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="5">Aún no has registrado órdenes.</td></tr>
                    @endforelse
                </tbody>
            </table>
            <a href="{{ route('advisor.orders.index') }}" class="btn btn-secondary btn-sm" style="margin-top:10px;">Ver todas</a>
        </div>

        <div class="panel">
            <h3 style="margin:0 0 12px;font-size:1rem;">Pendientes de asignar mecánico</h3>
            @forelse ($unassigned as $order)
                <div class="reminder">
                    <strong>{{ $order->order_number }}</strong> · {{ $order->vehicle->plate }} — {{ Str::limit($order->description, 50) }}
                    <div style="margin-top:6px;">
                        <a href="{{ route('advisor.orders.show', $order) }}" class="btn btn-primary btn-sm">Asignar</a>
                    </div>
                </div>
            @empty
                <p style="color:var(--muted);font-size:0.84rem;margin:0;">Todas las órdenes activas tienen mecánico asignado.</p>
            @endforelse
        </div>
    </div>

    @if ($pendingAppointments->isNotEmpty())
    <div class="panel" style="margin-top:1rem;">
        <h3 style="margin:0 0 12px;font-size:1rem;">Solicitudes de cita (chatbot)</h3>
        <table class="table">
            <thead><tr><th>Fecha</th><th>Cliente</th><th>Placa</th><th>Servicio</th><th></th></tr></thead>
            <tbody>
                @foreach ($pendingAppointments as $item)
                    <tr>
                        <td>{{ $item->requested_date->format('d/m/Y') }}</td>
                        <td>{{ $item->client->name }}</td>
                        <td>{{ $item->vehicle->plate }}</td>
                        <td>{{ Str::limit($item->service_type, 40) }} @if($item->requires_approval)<span class="badge yellow">Extra</span>@endif</td>
                        <td><a href="{{ route('advisor.appointments.show', $item) }}" class="btn btn-primary btn-sm">Revisar</a></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <a href="{{ route('advisor.appointments.index') }}" class="btn btn-secondary btn-sm" style="margin-top:10px;">Ver todas</a>
    </div>
    @endif

    <div style="margin-top:1rem;">
        @include('components.dashboard-calendar', ['calendar' => $calendarWidget])
    </div>
@endsection
