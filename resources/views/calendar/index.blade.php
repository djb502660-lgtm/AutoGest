@extends('layouts.admin')

@section('title', 'Calendario')
@section('heading', 'Calendario de mantenimientos')
@section('subheading')
    Programación visual de servicios y órdenes del mes {{ ucfirst($current->translatedFormat('F Y')) }}.
@endsection

@section('top-actions')
    <a href="{{ route('calendar.create') }}" class="btn btn-primary">+ Nuevo evento</a>
@endsection

@push('styles')
<style>
    .calendar-toolbar { display:flex; align-items:center; justify-content:space-between; gap:12px; margin-bottom:16px; flex-wrap:wrap; }
    .calendar-nav { display:flex; align-items:center; gap:8px; }
    .calendar-nav a { text-decoration:none; color:#dbeafe; padding:8px 12px; border-radius:10px; background:rgba(8,15,29,0.72); border:1px solid rgba(148,163,184,0.12); font-size:0.82rem; font-weight:700; }
    .calendar-title { font-size:1.1rem; font-weight:800; text-transform:capitalize; }
    .calendar-grid { display:grid; grid-template-columns:repeat(7,1fr); gap:8px; }
    .calendar-weekday { text-align:center; color:var(--muted); font-size:0.68rem; font-weight:800; letter-spacing:.12em; text-transform:uppercase; padding:8px 4px; }
    .calendar-day {
        min-height:110px; border-radius:14px; padding:8px; display:flex; flex-direction:column; gap:6px;
        background:rgba(8,15,29,0.55); border:1px solid rgba(148,163,184,0.08);
    }
    .calendar-day.outside { opacity:0.45; }
    .calendar-day.today { border-color:rgba(34,197,94,0.35); box-shadow:0 0 0 1px rgba(34,197,94,0.18) inset; }
    .day-number { font-size:0.82rem; font-weight:800; color:#dbeafe; display:flex; justify-content:space-between; align-items:center; }
    .day-number a { color:#86efac; text-decoration:none; font-size:0.72rem; opacity:0; transition:opacity .2s; }
    .calendar-day:hover .day-number a { opacity:1; }
    .event-chip {
        display:block; padding:4px 6px; border-radius:8px; font-size:0.62rem; font-weight:700;
        text-decoration:none; color:#f8fafc; line-height:1.25; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;
    }
    .event-green { background:rgba(34,197,94,0.28); border:1px solid rgba(34,197,94,0.35); }
    .event-blue { background:rgba(14,165,233,0.28); border:1px solid rgba(14,165,233,0.35); }
    .event-red { background:rgba(251,113,133,0.28); border:1px solid rgba(251,113,133,0.35); }
    .event-muted { background:rgba(148,163,184,0.18); border:1px solid rgba(148,163,184,0.22); color:#cbd5e1; }
    .event-purple { background:rgba(168,85,247,0.28); border:1px solid rgba(168,85,247,0.35); }
    .calendar-layout { display:grid; grid-template-columns:1fr 280px; gap:16px; }
    .upcoming-list { display:flex; flex-direction:column; gap:10px; margin-top:12px; }
    .upcoming-item { padding:10px 12px; border-radius:12px; background:rgba(8,15,29,0.72); border:1px solid rgba(148,163,184,0.08); font-size:0.8rem; }
    .upcoming-item strong { display:block; margin-bottom:4px; }
    .upcoming-item span { color:var(--muted); font-size:0.72rem; }
    .legend { display:flex; gap:10px; flex-wrap:wrap; margin-top:12px; font-size:0.72rem; color:var(--muted); }
    .legend span { display:inline-flex; align-items:center; gap:6px; }
    .legend i { width:10px; height:10px; border-radius:999px; display:inline-block; }
    @media (max-width:1000px) { .calendar-layout { grid-template-columns:1fr; } .calendar-day { min-height:90px; } }
</style>
@endpush

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

            <div class="legend">
                <span><i style="background:#22c55e"></i> Programado</span>
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
