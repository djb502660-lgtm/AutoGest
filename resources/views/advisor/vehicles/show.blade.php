@extends('layouts.advisor')

@section('title', 'Detalle de vehículo')
@section('heading', 'Detalle de vehículo')
@section('subheading', 'Información detallada del vehículo.')

@section('top-actions')
    <a href="{{ route('advisor.vehicles.edit', $vehicle) }}" class="btn btn-secondary">Editar</a>
    <a href="{{ route('advisor.vehicles.index') }}" class="btn btn-secondary">Volver</a>
@endsection

@section('content')
    <div class="panel">
        <div class="detail-grid">
            <div>
                <strong>Placa:</strong>
                <p>{{ $vehicle->plate }}</p>
            </div>
            <div>
                <strong>Marca:</strong>
                <p>{{ $vehicle->brand }}</p>
            </div>
            <div>
                <strong>Modelo:</strong>
                <p>{{ $vehicle->model }}</p>
            </div>
            <div>
                <strong>Año:</strong>
                <p>{{ $vehicle->year ?? '—' }}</p>
            </div>
            <div>
                <strong>Color:</strong>
                <p>{{ $vehicle->color ?? '—' }}</p>
            </div>
            <div>
                <strong>Kilometraje:</strong>
                <p>{{ $vehicle->mileage }} km</p>
            </div>
            <div>
                <strong>VIN:</strong>
                <p>{{ $vehicle->vin ?? '—' }}</p>
            </div>
            <div>
                <strong>Estado:</strong>
                <p>
                    <span class="badge {{ $vehicle->status === 'activo' ? 'green' : ($vehicle->status === 'en_taller' ? 'yellow' : 'red') }}">
                        {{ ucfirst($vehicle->status) }}
                    </span>
                </p>
            </div>
            <div>
                <strong>Vencimiento seguro:</strong>
                <p>{{ $vehicle->insurance_expiry?->format('d/m/Y') ?? '—' }}</p>
            </div>
            <div>
                <strong>Vencimiento inspección:</strong>
                <p>{{ $vehicle->inspection_expiry?->format('d/m/Y') ?? '—' }}</p>
            </div>
        </div>

        @if ($vehicle->notes)
            <div class="form-group">
                <strong>Notas:</strong>
                <p>{{ $vehicle->notes }}</p>
            </div>
        @endif

        <h3>Cliente</h3>
        <div class="card">
            <p><strong>{{ $vehicle->client->name }}</strong> ({{ $vehicle->client->email }})</p>
            <a href="{{ route('advisor.clients.show', $vehicle->client) }}" class="btn btn-secondary btn-sm">Ver cliente</a>
        </div>

        <h3>Órdenes de servicio recientes</h3>
        @forelse ($vehicle->serviceOrders as $order)
            <div class="card">
                <h4>{{ $order->order_number }}</h4>
                <p>Estado: {{ ucfirst($order->status) }} | Fecha: {{ $order->created_at->format('d/m/Y') }}</p>
                <a href="{{ route('advisor.orders.show', $order) }}" class="btn btn-secondary btn-sm">Ver orden</a>
            </div>
        @empty
            <p>Este vehículo no tiene órdenes de servicio.</p>
        @endforelse
    </div>
@endsection
