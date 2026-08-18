@extends('layouts.mechanic')

@section('title', 'Dashboard')
@section('heading', 'Panel de Trabajo del Mecánico')
@section('subheading', 'Agenda general del taller y resumen del estado de los servicios.')

@section('content')
    <div class="stats-grid cols-4">
        <article class="stat-card">
            <div class="stat-header"><span>Órdenes activas</span><span>↗</span></div>
            <div class="stat-value">{{ $stats['asignadas'] ?? 0 }}</div>
            <div class="stat-trend trend-up">Asignadas a ti</div>
        </article>
        <article class="stat-card">
            <div class="stat-header"><span>En reparación</span><span>⚠</span></div>
            <div class="stat-value">{{ $stats['en_proceso'] ?? 0 }}</div>
            <div class="stat-trend trend-warn">En proceso ahora</div>
        </article>
        <article class="stat-card">
            <div class="stat-header"><span>Pendientes</span><span>●</span></div>
            <div class="stat-value">{{ $stats['pendientes'] ?? 0 }}</div>
            <div class="stat-trend">Por iniciar</div>
        </article>
        <article class="stat-card">
            <div class="stat-header"><span>Completadas</span><span>✓</span></div>
            <div class="stat-value">{{ $stats['completadas'] ?? 0 }}</div>
            <div class="stat-trend trend-up">Cerradas o entregadas</div>
        </article>
    </div>

    @if (!empty($reminders) && $reminders->isNotEmpty())
        <div class="panel">
            @foreach ($reminders as $reminder)
                <p class="subtle" style="margin:0.25rem 0;">{{ $reminder }}</p>
            @endforeach
        </div>
    @endif

    <section class="panel">
        <h3>Órdenes recientes</h3>
        <p class="subtle">Últimos trabajos asignados a tu bandeja.</p>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Orden</th>
                        <th>Vehículo</th>
                        <th>Servicio</th>
                        <th>Estado</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($recentOrders as $order)
                        <tr>
                            <td>{{ $order->order_number }}</td>
                            <td>{{ $order->vehicle?->plate ?? '—' }}</td>
                            <td>{{ Str::limit($order->description, 48) }}</td>
                            <td><span class="badge {{ $order->statusBadgeClass() }}">{{ $order->statusLabel() }}</span></td>
                            <td><a href="{{ route('mechanic.orders.show', $order) }}" class="btn btn-secondary btn-sm">Ver</a></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">No hay órdenes asignadas.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <div style="margin-top:1rem;">
        @include('components.dashboard-calendar', ['calendar' => $calendarWidget])
    </div>
@endsection
