@extends('layouts.advisor')

@section('title', 'Calendario de citas')
@section('heading', 'Calendario de citas')
@section('subheading', 'Vista de calendario de las citas programadas.')

@section('top-actions')
    <a href="{{ route('advisor.appointments.create') }}" class="btn btn-primary">+ Nueva cita</a>
    <a href="{{ route('advisor.appointments.index') }}" class="btn btn-secondary">Vista lista</a>
@endsection

@section('content')
    <div class="panel">
        <form method="GET" action="{{ route('advisor.appointments.calendar') }}" class="filters">
            <input type="date" name="date" value="{{ $date }}">
            <button type="submit" class="btn btn-secondary">Ver fecha</button>
            <a href="{{ route('advisor.appointments.calendar', ['date' => today()->toDateString()]) }}" class="btn btn-secondary">Hoy</a>
        </form>

        <h3>Citas para {{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}</h3>

        @forelse ($appointments as $appointment)
            <div class="card">
                <div class="card-header">
                    <strong>{{ $appointment->preferred_time ?? 'Sin hora' }}</strong>
                    <span class="badge {{ $appointment->priority === 'urgente' ? 'red' : ($appointment->priority === 'alta' ? 'orange' : 'green') }}">
                        {{ ucfirst($appointment->priority) }}
                    </span>
                    <span class="badge {{ $appointment->status === 'completada' ? 'green' : ($appointment->status === 'cancelada' ? 'red' : 'yellow') }}">
                        {{ ucfirst($appointment->status) }}
                    </span>
                </div>
                <div class="card-body">
                    <p><strong>Cliente:</strong> {{ $appointment->client->name }}</p>
                    <p><strong>Vehículo:</strong> {{ $appointment->vehicle->plate }} - {{ $appointment->vehicle->brand }} {{ $appointment->vehicle->model }}</p>
                    <p><strong>Servicio:</strong> {{ $appointment->service_type }}</p>
                    <p>{{ Str::limit($appointment->description, 100) }}</p>
                </div>
                <div class="card-footer">
                    <a href="{{ route('advisor.appointments.show', $appointment) }}" class="btn btn-secondary btn-sm">Ver</a>
                    @if (!in_array($appointment->status, ['completada', 'cancelada']))
                        <a href="{{ route('advisor.appointments.edit', $appointment) }}" class="btn btn-secondary btn-sm">Editar</a>
                        <form method="POST" action="{{ route('advisor.appointments.reschedule', $appointment) }}" style="display: inline;">
                            @csrf
                            <button type="submit" class="btn btn-warning btn-sm">Reprogramar</button>
                        </form>
                        <form method="POST" action="{{ route('advisor.appointments.cancel', $appointment) }}" style="display: inline;" data-confirm="¿Cancelar esta cita?" data-confirm-title="Cancelar cita" data-confirm-label="Cancelar">
                            @csrf
                            <button type="submit" class="btn btn-danger btn-sm">Cancelar</button>
                        </form>
                    @endif
                </div>
            </div>
        @empty
            <p>No hay citas programadas para esta fecha.</p>
        @endforelse
    </div>
@endsection
