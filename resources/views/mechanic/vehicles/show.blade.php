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
            <h3 style="margin:0 0 12px;">Historial de Mantenimientos</h3>
            <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Fecha de Ingreso</th>
                        <th>N° de Orden</th>
                        <th>Trabajo / Detalle del Servicio</th>
                        <th>Kilometraje</th>
                        <th>Mecánico Asignado</th>
                        <th>Estado</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($orders as $order)
                        <tr>
                            <td>{{ $order->created_at?->format('d/m/Y') ?? '—' }}</td>
                            <td>{{ $order->order_number }}</td>
                            <td>{{ \Illuminate\Support\Str::limit($order->description, 40) }}</td>
                            <td>{{ $order->mileage ?? '—' }}</td>
                            <td>{{ $order->mechanic->name ?? '—' }}</td>
                            <td><span class="badge {{ $order->statusBadgeClass() }}">{{ $order->statusLabel() }}</span></td>
                            <td><a href="{{ route('mechanic.orders.show', $order) }}" class="btn btn-sm btn-primary">Ver Orden</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center py-3">Sin historial de mantenimientos.</td></tr>
                    @endforelse
                </tbody>
            </table>
            </div>
        </div>
    @else
        <div class="grid-2">
            <div class="panel">
                <h3 style="margin:0 0 12px;">Información General y Ficha Técnica</h3>
                <p><strong>Marca:</strong> {{ $vehicle->brand ?? '—' }}</p>
                <p><strong>Modelo:</strong> {{ $vehicle->model ?? '—' }}</p>
                <p><strong>Submodelo:</strong> {{ $vehicle->sub_model ?? '—' }}</p>
                <p><strong>Año:</strong> {{ $vehicle->year ?? '—' }}</p>
                <p><strong>Color:</strong> {{ $vehicle->color ?? '—' }}</p>
                <p><strong>Placa:</strong> {{ $vehicle->plate ?? '—' }}</p>
                <p><strong>Kilometraje actual:</strong> {{ number_format($vehicle->mileage ?? 0) }} km</p>
                <p><strong>VIN/Chasis:</strong> {{ $vehicle->vin ?? '—' }}</p>
                <p><strong>Número de Motor:</strong> {{ $vehicle->engine_number ?? '—' }}</p>
                <p><strong>Tipo de Caja de Cambios:</strong> {{ $vehicle->transmission_type ?? '—' }}</p>
                <p><strong>Medidas de Neumáticos:</strong> {{ $vehicle->tire_size ?? '—' }}</p>
                <p><strong>Referencia de Pintura:</strong> {{ $vehicle->paint_reference ?? '—' }}</p>
                <p><strong>Transponder:</strong> {{ $vehicle->transponder ?? '—' }}</p>
                <p><strong>Código de Radio:</strong> {{ $vehicle->radio_code ?? '—' }}</p>
                <p><strong>Cliente:</strong> {{ $vehicle->client->name ?? '—' }}</p>
            </div>
            <div class="panel">
                <h3 style="margin:0 0 12px;">Alertas y Fechas</h3>
                <p><strong>Vencimiento de Seguro:</strong> {{ $vehicle->insurance_expiry?->format('d/m/Y') ?? '—' }}</p>
                <p><strong>Vencimiento de Revisión Técnica:</strong> {{ $vehicle->inspection_expiry?->format('d/m/Y') ?? '—' }}</p>
                @if ($vehicle->notes)
                    <p><strong>Notas:</strong> {{ $vehicle->notes }}</p>
                @endif
            </div>
        </div>
    @endif
@endsection
