@extends('layouts.mechanic')

@section('title', 'Dashboard')
@section('heading', 'Panel de Trabajo del Mecánico')
@section('subheading', 'Agenda general del taller y resumen del estado de los servicios.')

@push('styles')
<style>
    /* METRICAS KPI */
    .kpi-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 24px; }
    .kpi-card { background: var(--bg-card); border: 1px solid var(--border-color); padding: 16px; border-radius: 10px; display: flex; align-items: center; justify-content: space-between; }
    .kpi-info label { font-size: 0.72rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; margin:0; }
    .kpi-info .val { font-size: 1.5rem; font-weight: 700; margin-top: 4px; }
    .kpi-icon { width: 40px; height: 40px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; }

    /* SECCIÓN AGENDA DEL TALLER */
    .calendar-container { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 12px; padding: 24px; }
    .calendar-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
    .calendar-controls { display: flex; align-items: center; gap: 12px; }
    .btn-cal { padding: 6px 12px; background: #f1f5f9; border: 1px solid var(--border-color); border-radius: 6px; font-size: 0.8rem; font-weight: 600; cursor: pointer; }

    .calendar-grid-wrapper { display: grid; grid-template-columns: 1fr 280px; gap: 24px; }
    
    /* Malla del Calendario */
    .days-header { display: grid; grid-template-columns: repeat(7, 1fr); text-align: center; font-size: 0.75rem; font-weight: 700; color: var(--text-muted); margin-bottom: 10px; }
    .days-grid { display: grid; grid-template-columns: repeat(7, 1fr); gap: 6px; }
    .day-cell { border: 1px solid var(--border-color); border-radius: 8px; min-height: 70px; padding: 6px; font-size: 0.8rem; background: #fafafa; }
    .day-cell.today { border-color: var(--accent); background: #fff; box-shadow: 0 0 0 1px var(--accent); }
    .day-number { font-weight: 700; color: var(--text-main); margin-bottom: 4px; }
    .event-tag { font-size: 0.68rem; background: var(--primary-light); color: var(--primary); padding: 2px 4px; border-radius: 4px; font-weight: 700; display: block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; margin-bottom: 2px; }

    /* Panel Lateral de Próximos Trabajos */
    .upcoming-box { background: #fafafa; border: 1px solid var(--border-color); border-radius: 10px; padding: 16px; }
    .upcoming-box h4 { font-size: 0.9rem; margin-bottom: 12px; color: var(--text-main); }
    .upcoming-item { background: white; border: 1px solid var(--border-color); padding: 10px; border-radius: 8px; margin-bottom: 10px; }
    .upcoming-item h5 { font-size: 0.85rem; color: var(--text-main); margin:0 0 4px; }
    .upcoming-item p { font-size: 0.75rem; color: var(--text-muted); margin:0; }

    .legend { display: flex; gap: 16px; font-size: 0.75rem; color: var(--text-muted); margin-top: 16px; }
    .legend-dot { width: 8px; height: 8px; border-radius: 50%; display: inline-block; margin-right: 4px; }
    
    @media (max-width: 768px) {
        .calendar-grid-wrapper { grid-template-columns: 1fr; }
        .kpi-grid { grid-template-columns: repeat(2, 1fr); }
    }
    
    @media (max-width: 576px) {
        .kpi-grid { grid-template-columns: 1fr; }
        .calendar-header { flex-direction: column; align-items: flex-start; gap: 1rem; }
        .calendar-controls { width: 100%; justify-content: space-between; }
    }
</style>
@endpush

@section('content')
    <!-- TARJETAS DE MÉTRICAS -->
    <div class="kpi-grid">
      <div class="kpi-card">
        <div class="kpi-info">
          <label>Mis Órdenes Activas</label>
          <div class="val">{{ $stats['asignadas'] ?? 0 }}</div>
        </div>
        <div class="kpi-icon" style="background: #e0f2fe; color: #0284c7;"><i class="fa-solid fa-list"></i></div>
      </div>

      <div class="kpi-card">
        <div class="kpi-info">
          <label>En Reparación</label>
          <div class="val">{{ $stats['en_proceso'] ?? 0 }}</div>
        </div>
        <div class="kpi-icon" style="background: #fef3c7; color: #b45309;"><i class="fa-solid fa-spinner"></i></div>
      </div>

      <div class="kpi-card">
        <div class="kpi-info">
          <label>Completados Hoy</label>
          <div class="val">{{ $stats['completadas'] ?? 0 }}</div>
        </div>
        <div class="kpi-icon" style="background: #dcfce7; color: #15803d;"><i class="fa-solid fa-check-double"></i></div>
      </div>
    </div>

    <!-- CALENDARIO Y AGENDA DEL TALLER -->
    <div class="calendar-container">
      <div class="calendar-header">
        <div>
          <h3 style="font-size: 1.1rem; margin:0;">Agenda del taller</h3>
          <p style="font-size: 0.8rem; color: var(--text-muted); margin:0;">Calendario de programación y fechas de entrega.</p>
        </div>
        <div class="calendar-controls">
          @if (!empty($calendarWidget['prev_url']))
            <a href="{{ $calendarWidget['prev_url'] }}" class="btn-cal" style="text-decoration: none; color: inherit;">← Anterior</a>
          @else
            <button class="btn-cal" disabled style="opacity: 0.5;">← Anterior</button>
          @endif
          
          <strong style="font-size: 0.9rem; text-transform: capitalize;">{{ $calendarWidget['current']->translatedFormat('F Y') }}</strong>
          
          @if (!empty($calendarWidget['next_url']))
            <a href="{{ $calendarWidget['next_url'] }}" class="btn-cal" style="text-decoration: none; color: inherit;">Siguiente →</a>
          @else
            <button class="btn-cal" disabled style="opacity: 0.5;">Siguiente →</button>
          @endif
        </div>
      </div>

      <div class="calendar-grid-wrapper">
        <!-- Rejilla del Mes -->
        <div>
          <div class="days-header">
            <div>LUN</div><div>MAR</div><div>MIÉ</div><div>JUE</div><div>VIE</div><div>SÁB</div><div>DOM</div>
          </div>
          <div class="days-grid">
            @foreach ($calendarWidget['weeks'] as $week)
                @foreach ($week as $day)
                    <div class="day-cell {{ $day['is_today'] ? 'today' : '' }}" style="{{ !$day['in_month'] ? 'opacity: 0.5; background: transparent;' : '' }}">
                        <div class="day-number">{{ $day['date']->format('d') }}</div>
                        @foreach ($day['events']->take(3) as $event)
                            @if ($event['url'])
                                <a href="{{ $event['url'] }}" class="event-tag" title="{{ $event['meta'] }}" style="text-decoration: none; display: block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ $event['label'] }}</a>
                            @else
                                <span class="event-tag" title="{{ $event['meta'] }}">{{ $event['label'] }}</span>
                            @endif
                        @endforeach
                        @if ($day['events']->count() > 3)
                            <span class="event-tag" style="background: #f1f5f9; color: #64748b;">+{{ $day['events']->count() - 3 }}</span>
                        @endif
                    </div>
                @endforeach
            @endforeach
          </div>

          @if (!empty($calendarWidget['legend']))
          <div class="legend">
            @foreach ($calendarWidget['legend'] as $item)
                <span>
                    @php
                        $dotColor = '#0284c7';
                        if(str_contains($item['variant'], 'danger')) $dotColor = '#ef4444';
                        elseif(str_contains($item['variant'], 'success')) $dotColor = '#10b981';
                        elseif(str_contains($item['variant'], 'warning')) $dotColor = '#f59e0b';
                    @endphp
                    <span class="legend-dot" style="background: {{ $dotColor }};"></span> {{ $item['label'] }}
                </span>
            @endforeach
          </div>
          @endif
        </div>

        <!-- Columna de Próximos Trabajos -->
        <div class="upcoming-box">
          <h4>Próximos trabajos</h4>
          
          @forelse ($calendarWidget['upcoming'] as $event)
              <div class="upcoming-item">
                <h5>{{ $event['label'] }}</h5>
                <p>{{ $event['date']->translatedFormat('d M.') }} • {{ $event['meta'] ?? 'Pendiente' }}</p>
              </div>
          @empty
              <p style="color: var(--text-muted); font-size: 0.8rem;">No hay trabajos próximos.</p>
          @endforelse
        </div>
      </div>
    </div>
@endsection
