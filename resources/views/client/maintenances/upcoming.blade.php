@extends('layouts.client')

@section('title', 'Próximos mantenimientos')
@section('heading', 'Próximos mantenimientos')
@section('subheading')
    Servicios programados para tus vehículos.
@endsection

@section('content')
    <div class="panel">
        @forelse ($schedules as $schedule)
            <div class="schedule-card">
                <h4>{{ $schedule->title }}</h4>
                <p>
                    <strong>{{ $schedule->vehicle->plate }}</strong> — {{ $schedule->vehicle->brand }} {{ $schedule->vehicle->model }}
                </p>
                <p>
                    Fecha: {{ $schedule->scheduled_date->format('d/m/Y') }}
                    @if ($schedule->mileage_target)
                        · Km estimado: {{ number_format($schedule->mileage_target) }}
                    @endif
                </p>
                <span class="badge yellow">{{ $schedule->statusLabel() }}</span>
            </div>
        @empty
            <p style="color:var(--muted);">No tienes mantenimientos programados próximamente.</p>
        @endforelse
        <div style="margin-top:12px;">{{ $schedules->links('pagination.simple') }}</div>
    </div>
@endsection
