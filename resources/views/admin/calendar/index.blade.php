@extends('layouts.admin')

@section('title', 'Calendario')
@section('heading', 'Calendario de mantenimientos')
@section('subheading')
    Programación visual de servicios y órdenes del mes {{ ucfirst($current->translatedFormat('F Y')) }}.
@endsection

@section('top-actions')
    <a href="{{ route('calendar.create') }}" class="btn btn-primary">+ Nuevo evento</a>
@endsection


@section('content')
    <div class="calendar-layout">
        <div class="panel">
            <div class="calendar-toolbar">
                <div class="calendar-nav">
                    <a href="{{ route('calendar.index', ['month' => $prev->month, 'year' => $prev->year]) }}">← Anterior</a>
                    <span class="calendar-title">{{ $current->translatedFormat('F Y') }}</span>
                    <a href="{{ route('calendar.index', ['month' => $next->month, 'year' => $next->year]) }}">Siguiente →</a>
                </div>
                <a href="{{ route('calendar.index') }}" class="btn btn-secondary btn-sm">Hoy</a>
            </div>

            <div class="calendar-grid">
                @foreach (['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'] as $weekday)
                    <div class="calendar-weekday">{{ $weekday }}</div>
                @endforeach

                @foreach ($weeks as $week)
                    @foreach ($week as $day)
                        <div class="calendar-day {{ $day['in_month'] ? '' : 'outside' }} {{ $day['is_today'] ? 'today' : '' }}">
                            <div class="day-number">
                                <span>{{ $day['date']->day }}</span>
                                @if ($day['in_month'])
                                    <a href="{{ route('calendar.create', ['date' => $day['date']->format('Y-m-d')]) }}" title="Agregar evento">+</a>
                                @endif
                            </div>

                            @foreach ($day['schedules'] as $schedule)
                                <a href="{{ route('calendar.edit', $schedule) }}" class="event-chip {{ $schedule->colorClass() }}" title="{{ $schedule->title }}">
                                    {{ $schedule->vehicle->plate }} · {{ $schedule->title }}
                                </a>
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
                <span><i style="background:#10b981"></i> Programado</span>
                <span><i style="background:#ef4444"></i> Vencido</span>
                <span><i style="background:#a855f7"></i> Orden de servicio</span>
            </div>
        </div>

        <aside class="panel">
            <h3 style="margin:0;font-size:1rem;">Próximos eventos</h3>
            <p style="margin:6px 0 0;color:var(--muted);font-size:0.82rem;">Mantenimientos programados y pendientes.</p>

            <div class="upcoming-list">
                @forelse ($upcoming as $item)
                    <a href="{{ route('calendar.edit', $item) }}" class="upcoming-item" style="text-decoration:none;color:inherit;">
                        <strong>{{ $item->title }}</strong>
                        <span>{{ $item->scheduled_date->translatedFormat('d M Y') }} · {{ $item->vehicle->plate }}</span>
                        <span>{{ $item->statusLabel() }}</span>
                    </a>
                @empty
                    <p style="color:var(--muted);font-size:0.82rem;margin:0;">No hay eventos próximos.</p>
                @endforelse
            </div>
        </aside>
    </div>
@endsection
