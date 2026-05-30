@extends('layouts.mechanic')

@section('title', $vehicle->plate)
@section('heading', $vehicle->brand.' '.$vehicle->model)
@section('subheading')
    Placa {{ $vehicle->plate }} · {{ number_format($vehicle->mileage) }} km
@endsection

@section('content')
    <div class="tabs">
        <a class="tab {{ $tab === 'info' ? 'active' : '' }}" href="{{ route('mechanic.vehicles.show', [$vehicle, 'tab' => 'info']) }}">Información</a>
        <a class="tab {{ $tab === 'historial' ? 'active' : '' }}" href="{{ route('mechanic.vehicles.show', [$vehicle, 'tab' => 'historial']) }}">Historial</a>
    </div>

    @if ($tab === 'historial')
        <div class="panel">
            <h3 style="margin:0 0 12px;">Historial de mantenimientos</h3>
            <table class="table">
                <thead>
                    <tr><th>Fecha</th><th>Orden</th><th>Servicio</th><th>Km</th><th>Estado</th></tr>
                </thead>
                <tbody>
                    @forelse ($maintenances as $m)
                        <tr>
                            <td>{{ $m->performed_at?->format('d/m/Y') ?? '—' }}</td>
                            <td>{{ $m->serviceOrder?->order_number ?? '—' }}</td>
                            <td>{{ $m->description }}</td>
                            <td>{{ $m->mileage_at_service ? number_format($m->mileage_at_service).' km' : '—' }}</td>
                            <td><span class="badge {{ $m->statusBadgeClass() }}">{{ $m->statusLabel() }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="5">Sin historial de mantenimientos.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @else
        <div class="grid-2">
            <div class="panel">
                <h3 style="margin:0 0 12px;">Datos generales</h3>
                <p><strong>Año:</strong> {{ $vehicle->year ?? '—' }}</p>
                <p><strong>Color:</strong> {{ $vehicle->color ?? '—' }}</p>
                <p><strong>VIN:</strong> {{ $vehicle->vin ?? '—' }}</p>
                <p><strong>Estado:</strong> {{ $vehicle->statusLabel() }}</p>
                <p><strong>Cliente:</strong> {{ $vehicle->client->name }}</p>
            </div>
            <div class="panel">
                <h3 style="margin:0 0 12px;">Documentación</h3>
                <p><strong>Seguro vence:</strong> {{ $vehicle->insurance_expiry?->format('d/m/Y') ?? '—' }}</p>
                <p><strong>Revisión técnica:</strong> {{ $vehicle->inspection_expiry?->format('d/m/Y') ?? '—' }}</p>
                @if ($vehicle->notes)
                    <p><strong>Notas:</strong> {{ $vehicle->notes }}</p>
                @endif
            </div>
        </div>
    @endif
@endsection
