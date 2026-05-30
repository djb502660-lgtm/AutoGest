@extends('layouts.mechanic')

@section('title', 'Historial')
@section('heading', 'Historial de intervenciones')
@section('subheading')
    Mantenimientos registrados por ti.
@endsection

@section('content')
    <div class="panel">
        <table class="table">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Vehículo</th>
                    <th>Orden</th>
                    <th>Servicio</th>
                    <th>Tipo</th>
                    <th>Costo</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($maintenances as $m)
                    <tr>
                        <td>{{ $m->performed_at?->format('d/m/Y') ?? '—' }}</td>
                        <td>{{ $m->vehicle->plate }}</td>
                        <td>{{ $m->serviceOrder?->order_number ?? '—' }}</td>
                        <td>{{ $m->description }}</td>
                        <td>{{ $m->typeLabel() }}</td>
                        <td>${{ number_format($m->cost, 2) }}</td>
                        <td><span class="badge {{ $m->statusBadgeClass() }}">{{ $m->statusLabel() }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="7">No has registrado mantenimientos aún.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div style="margin-top:12px;">{{ $maintenances->links('pagination.simple') }}</div>
    </div>
@endsection
