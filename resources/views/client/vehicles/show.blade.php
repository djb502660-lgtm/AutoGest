@extends('layouts.client')

@section('title', $vehicle->plate)
@section('heading', $vehicle->brand.' '.$vehicle->model)
@section('subheading')
    Placa {{ $vehicle->plate }} · {{ number_format($vehicle->mileage) }} km
@endsection

@section('content')
    <div class="grid-2">
        <div class="panel">
            <h3 style="margin:0 0 12px;">Información general</h3>
            <p><strong>Año:</strong> {{ $vehicle->year ?? '—' }}</p>
            <p><strong>Color:</strong> {{ $vehicle->color ?? '—' }}</p>
            <p><strong>VIN:</strong> {{ $vehicle->vin ?? '—' }}</p>
            <p><strong>Estado:</strong> <span class="badge {{ $vehicle->statusBadgeClass() }}">{{ $vehicle->statusLabel() }}</span></p>
            <p><strong>Seguro vence:</strong> {{ $vehicle->insurance_expiry?->format('d/m/Y') ?? '—' }}</p>
            <p><strong>Revisión técnica:</strong> {{ $vehicle->inspection_expiry?->format('d/m/Y') ?? '—' }}</p>
        </div>
        <div class="panel">
            <h3 style="margin:0 0 12px;">Próximos mantenimientos</h3>
            @forelse ($vehicle->maintenanceSchedules as $schedule)
                <div class="schedule-card">
                    <h4>{{ $schedule->title }}</h4>
                    <p>{{ $schedule->scheduled_date->format('d/m/Y') }} · {{ $schedule->statusLabel() }}</p>
                </div>
            @empty
                <p style="color:var(--muted);font-size:0.84rem;">Sin mantenimientos programados.</p>
            @endforelse
        </div>
    </div>

    <div class="panel">
        <h3 style="margin:0 0 12px;">Últimos mantenimientos</h3>
        <table class="table">
            <thead><tr><th>Fecha</th><th>Servicio</th><th>Km</th><th>Costo</th><th>Estado</th></tr></thead>
            <tbody>
                @forelse ($vehicle->maintenances as $m)
                    <tr>
                        <td>{{ $m->performed_at?->format('d/m/Y') ?? '—' }}</td>
                        <td>{{ $m->description }}</td>
                        <td>{{ $m->mileage_at_service ? number_format($m->mileage_at_service).' km' : '—' }}</td>
                        <td>${{ number_format($m->cost, 2) }}</td>
                        <td><span class="badge {{ $m->statusBadgeClass() }}">{{ $m->statusLabel() }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="5">Sin historial de mantenimientos.</td></tr>
                @endforelse
            </tbody>
        </table>
        <a href="{{ route('client.maintenances.history') }}" class="btn btn-secondary btn-sm" style="margin-top:8px;">Ver historial completo</a>
    </div>
@endsection
