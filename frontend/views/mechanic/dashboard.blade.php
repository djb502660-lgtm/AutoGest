@extends('layouts.mechanic')

@section('title', 'Dashboard')
@section('heading', 'Dashboard principal')
@section('subheading')
    Resumen de órdenes asignadas y recordatorios del día.
@endsection

@section('content')
    <div class="stats">
        <div class="stat"><span>Órdenes asignadas</span><strong>{{ $stats['asignadas'] }}</strong></div>
        <div class="stat"><span>En proceso</span><strong>{{ $stats['en_proceso'] }}</strong></div>
        <div class="stat"><span>Pendientes</span><strong>{{ $stats['pendientes'] }}</strong></div>
        <div class="stat"><span>Completadas</span><strong>{{ $stats['completadas'] }}</strong></div>
    </div>

    <div class="grid-2">
        <div class="panel">
            <h3 style="margin:0 0 12px;font-size:1rem;">Órdenes recientes</h3>
            <table class="table">
                <thead>
                    <tr><th>Orden</th><th>Vehículo</th><th>Estado</th><th></th></tr>
                </thead>
                <tbody>
                    @forelse ($recentOrders as $order)
                        <tr>
                            <td>{{ $order->order_number }}</td>
                            <td>{{ $order->vehicle->plate }}</td>
                            <td><span class="badge {{ $order->statusBadgeClass() }}">{{ $order->statusLabel() }}</span></td>
                            <td><a href="{{ route('mechanic.orders.show', $order) }}" class="btn btn-secondary btn-sm">Ver</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="4">Sin órdenes asignadas.</td></tr>
                    @endforelse
                </tbody>
            </table>
            <a href="{{ route('mechanic.orders.index') }}" class="btn btn-secondary btn-sm" style="margin-top:10px;">Ver todas</a>
        </div>

        <div class="panel">
            <h3 style="margin:0 0 12px;font-size:1rem;">Recordatorios</h3>
            @forelse ($reminders as $reminder)
                <div class="reminder">⚠ {{ $reminder }}</div>
            @empty
                <p style="color:var(--muted);font-size:0.84rem;margin:0;">Sin recordatorios pendientes.</p>
            @endforelse
        </div>
    </div>

    <div style="margin-top:1rem;">
        @include('components.dashboard-calendar', ['calendar' => $calendarWidget])
    </div>
@endsection
