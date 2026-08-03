@extends('layouts.advisor')

@section('title', 'Detalle de cliente')
@section('heading', 'Detalle de cliente')
@section('subheading', 'Información detallada del cliente.')

@section('top-actions')
    <a href="{{ route('advisor.clients.edit', $client) }}" class="btn btn-secondary">Editar</a>
    <a href="{{ route('advisor.clients.index') }}" class="btn btn-secondary">Volver</a>
@endsection

@section('content')
    <div class="panel">
        <div class="detail-grid">
            <div>
                <strong>Nombre:</strong>
                <p>{{ $client->name }}</p>
            </div>
            <div>
                <strong>Email:</strong>
                <p>{{ $client->email }}</p>
            </div>
            <div>
                <strong>Teléfono:</strong>
                <p>{{ $client->phone ?? '—' }}</p>
            </div>
            <div>
                <strong>Estado:</strong>
                <p>
                    <span class="badge {{ $client->status === 'activo' ? 'green' : 'red' }}">
                        {{ ucfirst($client->status) }}
                    </span>
                </p>
            </div>
        </div>

        <h3>Vehículos del cliente</h3>
        @forelse ($client->vehicles as $vehicle)
            <div class="card">
                <h4>{{ $vehicle->plate }} - {{ $vehicle->brand }} {{ $vehicle->model }}</h4>
                <p>Año: {{ $vehicle->year ?? '—' }} | Kilometraje: {{ $vehicle->mileage }} km</p>
                <a href="{{ route('advisor.vehicles.show', $vehicle) }}" class="btn btn-secondary btn-sm">Ver vehículo</a>
            </div>
        @empty
            <p>Este cliente no tiene vehículos registrados.</p>
        @endforelse
    </div>
@endsection
