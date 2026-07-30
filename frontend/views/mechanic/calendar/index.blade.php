@extends('layouts.mechanic')

@section('title', 'Calendario')
@section('heading', 'Calendario de trabajo')
@section('subheading')
    Vista de calendario para tu carga de trabajo y citas programadas en el mes.
@endsection

@section('top-actions')
    <div class="pill">Solo lectura</div>
@endsection

@section('content')
    <div class="calendar-layout">
        <div class="panel">
            <div class="calendar-toolbar">
                <div class="calendar-nav">
                    <a href="{{ route('mechanic.calendar.index', ['month' => $prev->month, 'year' => $prev->year]) }}">← Anterior</a>
                    <span class="calendar-title">{{ $current->translatedFormat('F Y') }}</span>
                    <a href="{{ route('mechanic.calendar.index', ['month' => $next->month, 'year' => $next->year]) }}">Siguiente →</a>
                </div>
                <a href="{{ route('mechanic.calendar.index') }}" class="btn btn-secondary btn-sm">Hoy</a>
            </div>

            <div class="calendar-grid">
                @foreach (['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'] as $weekday)
                    <div class="calendar-weekday">{{ $weekday }}</div>
                @endforeach

                @foreach ($weeks as $week)
                    @foreach ($week as $day)
                        <div class="calendar-day {{ $day['in_month'] ? '' : 'outside' }} {{ $day['is_today'] ? 'today' : '' }}">
                            <div class="day-number"><span>{{ $day['date']->day }}</span></div>

                            @foreach ($day['schedules'] as $schedule)
                                <span class="event-chip {{ $schedule->colorClass() }}" title="{{ $schedule->title }}">
                                    {{ $schedule->vehicle->plate }} · {{ Str::limit($schedule->title, 18) }}
                                </span>
                            @endforeach

                            @foreach ($day['orders'] as $order)
                                <span class="event-chip event-purple" title="{{ $order->description }}">
                                    {{ $order->vehicle->plate }} · {{ $order->order_number }}
                                </span>
                            @endforeach
                        </div>
                    @endforeach
                @endforeach
            </div>

            <div class="calendar-legend">
                <span><i style="background:#10b981"></i> Mantenimiento asignado</span>
                <span><i style="background:#a855f7"></i> Orden programada</span>
            </div>
        </div>

        <aside class="panel">
            <h3 style="margin:0;font-size:1rem;">Próximos registros</h3>
            <p style="margin:6px 0 0;color:var(--muted);font-size:0.82rem;">Tus próximas citas y trabajos programados.</p>

            <div class="upcoming-list">
                @forelse ($upcomingSchedules as $schedule)
                    <div class="upcoming-item">
                        <strong>{{ $schedule->title }}</strong>
                        <span>{{ $schedule->scheduled_date->translatedFormat('d M Y') }} · {{ $schedule->vehicle->plate }}</span>
                        <span>{{ $schedule->statusLabel() }}</span>
                    </div>
                @empty
                    <p style="color:var(--muted);font-size:0.82rem;margin:0;">No hay mantenimientos próximos.</p>
                @endforelse

                @foreach ($upcomingOrders as $order)
                    <div class="upcoming-item">
                        <strong>{{ $order->order_number }}</strong>
                        <span>{{ $order->scheduled_at->format('d M Y') }} · {{ $order->vehicle->plate }}</span>
                        <span>{{ $order->statusLabel() }}</span>
                    </div>
                @endforeach
            </div>
        </aside>
    </div>
@endsection
