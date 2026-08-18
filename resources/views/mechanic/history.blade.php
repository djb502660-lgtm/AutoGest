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
                    <th>Entrada</th>
                    <th>Salida</th>
                    <th>Vehículo</th>
                    <th>Orden</th>
                    <th>Servicio</th>
                    <th>Tipo</th>
                    <th>Costo</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($maintenances as $order)
                    <tr>
                        <td>{{ $order->created_at?->format('d/m/Y') ?? '—' }}</td>
                        <td>{{ $order->completed_at?->format('d/m/Y') ?? '—' }}</td>
                        <td>{{ $order->vehicle->plate }}</td>
                        <td>{{ $order->order_number }}</td>
                        <td>{{ \Illuminate\Support\Str::limit($order->description, 40) }}</td>
                        <td>Servicio</td>
                        <td>${{ number_format($order->total_cost ?? $order->estimated_cost ?? 0, 2) }}</td>
                        <td><span class="badge {{ $order->statusBadgeClass() }}">{{ $order->statusLabel() }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="8">No has registrado mantenimientos aún.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div style="margin-top:12px;">{{ $maintenances->links('pagination.simple') }}</div>
    </div>
@endsection
