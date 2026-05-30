@extends('layouts.client')

@section('title', 'Historial')
@section('heading', 'Historial de mantenimientos')
@section('subheading')
    Servicios realizados en tus vehículos.
@endsection

@section('content')
    <div class="panel">
        <table class="table">
            <thead>
                <tr><th>Fecha</th><th>Vehículo</th><th>Servicio</th><th>Km</th><th>Costo</th><th>Estado</th></tr>
            </thead>
            <tbody>
                @forelse ($maintenances as $m)
                    <tr>
                        <td>{{ $m->performed_at?->format('d/m/Y') ?? '—' }}</td>
                        <td>{{ $m->vehicle->plate }}</td>
                        <td>{{ $m->description }}</td>
                        <td>{{ $m->mileage_at_service ? number_format($m->mileage_at_service).' km' : '—' }}</td>
                        <td>${{ number_format($m->cost, 2) }}</td>
                        <td><span class="badge {{ $m->statusBadgeClass() }}">{{ $m->statusLabel() }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="6">No hay mantenimientos registrados.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div style="margin-top:12px;">{{ $maintenances->links('pagination.simple') }}</div>
    </div>
@endsection
