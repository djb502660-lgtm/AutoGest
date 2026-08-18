@extends('layouts.admin')

@section('title', 'Dashboard')
@section('heading', 'Sistema administrativo')
@section('subheading', 'Vista táctica del mantenimiento vehicular inteligente.')

@section('top-actions')
    <div class="pill">Modo operativo activo</div>
@endsection

@section('content')
    <div class="stats-grid">
        <article class="stat-card">
            <div class="stat-header"><span>Vehículos</span><span>↗</span></div>
            <div class="stat-value">{{ $stats['vehiculos'] }}</div>
            <div class="stat-trend trend-up">Registrados en el sistema</div>
        </article>

        <article class="stat-card">
            <div class="stat-header"><span>Mantenimientos</span><span>↗</span></div>
            <div class="stat-value">{{ $stats['mantenimientos'] }}</div>
            <div class="stat-trend trend-up">Realizados este mes</div>
        </article>

        <article class="stat-card">
            <div class="stat-header"><span>Alertas</span><span>⚠</span></div>
            <div class="stat-value">{{ $stats['alertas'] }}</div>
            <div class="stat-trend trend-warn">{{ $stats['alertas_criticas'] }} críticas pendientes</div>
        </article>

        <article class="stat-card">
            <div class="stat-header"><span>Usuarios</span><span>✓</span></div>
            <div class="stat-value">{{ $stats['usuarios'] }}</div>
            <div class="stat-trend trend-up">Cuentas activas</div>
        </article>

        <article class="stat-card">
            <div class="stat-header"><span>Gastos mes</span><span>$</span></div>
            <div class="stat-value">${{ number_format($stats['gasto_mes'], 0, '.', ',') }}</div>
            <div class="stat-trend trend-up">Costos registrados este mes</div>
        </article>
    </div>

    <div class="panel-grid">
        <section class="panel">
            <h3>Órdenes de servicio recientes</h3>
            <p class="subtle">Seguimiento de trabajos abiertos y estados de ejecución.</p>

            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Vehículo</th>
                            <th>Servicio</th>
                            <th>Estado</th>
                            <th>Fecha</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($recentOrders as $order)
                        <tr>
                            <td>{{ $order->vehicle->plate }}</td>
                            <td>{{ $order->description }}</td>
                            <td><span class="badge {{ $order->statusBadgeClass() }}">{{ $order->statusLabel() }}</span></td>
                            <td>{{ $order->scheduled_at?->translatedFormat('d M') ?? $order->created_at->translatedFormat('d M') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4">No hay órdenes registradas.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="panel">
            <h3>Resumen operativo</h3>
            <p class="subtle">Distribución de incidencias y salud técnica del parque.</p>

            <div class="report-grid">
                <div class="report-box">
                    <div class="badge green">OK</div>
                    <div class="num">{{ $summary['flota_saludable'] }}%</div>
                    <div class="subtle">Flota saludable</div>
                    <div class="ruler"><span style="width:{{ $summary['flota_saludable'] }}%"></span></div>
                </div>
                <div class="report-box">
                    <div class="badge yellow">ATENCIÓN</div>
                    <div class="num">{{ $summary['tareas_proximas'] }}</div>
                    <div class="subtle">Tareas próximas</div>
                    <div class="ruler"><span style="width:{{ min($summary['tareas_proximas'] * 8, 100) }}%"></span></div>
                </div>
                <div class="report-box">
                    <div class="badge red">CRÍTICO</div>
                    <div class="num">{{ $summary['alertas_criticas'] }}</div>
                    <div class="subtle">Alertas urgentes</div>
                    <div class="ruler"><span style="width:{{ min($summary['alertas_criticas'] * 15, 100) }}%"></span></div>
                </div>
                <div class="report-box">
                    <div class="badge green">BITÁCORA</div>
                    <div class="num">{{ $summary['registros_hoy'] }}</div>
                    <div class="subtle">Registros del día</div>
                    <div class="ruler"><span style="width:{{ min($summary['registros_hoy'] * 10, 100) }}%"></span></div>
                </div>
            </div>

            <div class="chart-card">
                <h4>Costo mensual de mantenimiento</h4>
                <div class="chart-bars">
                    @foreach (range(1, now()->month) as $m)
                        @php $value = $monthlyCosts->get($m, 0); @endphp
                        <div class="chart-bar">
                            <span class="bar" style="height:{{ $value ? min(($value / max($monthlyCosts->max(), 1)) * 100, 100) : 10 }}%;"></span>
                            <small>{{ \Carbon\Carbon::create(now()->year, $m, 1)->format('M') }}</small>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    </div>

    <section class="panel" style="margin-top:1rem;">
        <h3>Actividad reciente</h3>
        <p class="subtle">Últimos eventos registrados en el sistema.</p>

        @if ($recentActivity->isNotEmpty())
            <ul class="compact-list">
                @foreach ($recentActivity as $activity)
                    <li>
                        <strong>{{ $activity->description }}</strong>
                        <span>{{ $activity->created_at->diffForHumans() }}</span>
                    </li>
                @endforeach
            </ul>
        @else
            <p style="color:var(--muted);font-size:0.84rem;">No hay actividad reciente.</p>
        @endif
    </section>

    <div style="margin-top:1rem;">
        @include('components.dashboard-calendar', ['calendar' => $calendarWidget])
    </div>
@endsection
