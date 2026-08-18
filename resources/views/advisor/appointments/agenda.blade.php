@extends('layouts.advisor')

@section('title', 'Agenda de citas')
@section('heading', 'Agenda de citas')
@section('subheading', 'Gestiona las citas programadas.')

@section('top-actions')
    <a href="{{ route('advisor.appointments.create') }}" class="btn btn-primary">+ Nueva cita</a>
    <a href="{{ route('advisor.appointments.calendar') }}" class="btn btn-secondary">Vista calendario</a>
@endsection

@section('content')
    <div class="panel">
        <form method="GET" action="{{ route('advisor.appointments.index') }}" class="filters">
            <input type="date" name="date" value="{{ $date }}">
            <select name="status">
                <option value="">Todos los estados</option>
                <option value="pendiente" @selected($status === 'pendiente')">Pendiente</option>
                <option value="confirmada" @selected($status === 'confirmada')">Confirmada</option>
                <option value="completada" @selected($status === 'completada')">Completada</option>
                <option value="cancelada" @selected($status === 'cancelada')">Cancelada</option>
            </select>
            <button type="submit" class="btn btn-secondary">Filtrar</button>
            @if ($date || $status)
                <a href="{{ route('advisor.appointments.index') }}" class="btn btn-secondary">Limpiar</a>
            @endif
        </form>

        <table class="table">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Hora</th>
                    <th>Cliente</th>
                    <th>Vehículo</th>
                    <th>Tipo de servicio</th>
                    <th>Prioridad</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($appointments as $appointment)
                    <tr>
                        <td>{{ $appointment->requested_date->format('d/m/Y') }}</td>
                        <td>{{ $appointment->preferred_time ?? '—' }}</td>
                        <td>{{ $appointment->client->name }}</td>
                        <td>{{ $appointment->vehicle->plate }} - {{ $appointment->vehicle->brand }} {{ $appointment->vehicle->model }}</td>
                        <td>{{ $appointment->service_type }}</td>
                        <td>
                            <span class="badge {{ $appointment->priority === 'urgente' ? 'red' : ($appointment->priority === 'alta' ? 'orange' : 'green') }}">
                                {{ ucfirst($appointment->priority) }}
                            </span>
                        </td>
                        <td>
                            <span class="badge {{ $appointment->status === 'completada' ? 'green' : ($appointment->status === 'cancelada' ? 'red' : 'yellow') }}">
                                {{ ucfirst($appointment->status) }}
                            </span>
                        </td>
                        <td>
                            <div class="actions-inline">
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
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8">No se encontraron citas.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="pagination">
            {{ $appointments->links('pagination.simple') }}
        </div>
    </div>
@endsection
