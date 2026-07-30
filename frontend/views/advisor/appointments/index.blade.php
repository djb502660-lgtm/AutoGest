@extends('layouts.advisor')

@section('title', 'Solicitudes chatbot')
@section('heading', 'Solicitudes de cita (chatbot)')
@section('subheading')
    Revisa y confirma las citas generadas por el asistente del cliente.
@endsection

@section('content')
    <div class="panel">
        <form method="GET" class="filters">
            <select name="status">
                <option value="pendiente" @selected($status === 'pendiente')>Pendientes</option>
                <option value="convertida" @selected($status === 'convertida')>Convertidas</option>
                <option value="rechazada" @selected($status === 'rechazada')>Rechazadas</option>
                <option value="todas" @selected($status === 'todas')>Todas</option>
            </select>
            <button type="submit" class="btn btn-secondary">Filtrar</button>
        </form>

        <table class="table">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Cliente</th>
                    <th>Vehículo</th>
                    <th>Servicio</th>
                    <th>Extra</th>
                    <th>Estado</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($requests as $item)
                    <tr>
                        <td>{{ $item->requested_date->format('d/m/Y') }}</td>
                        <td>{{ $item->client->name }}</td>
                        <td>{{ $item->vehicle->plate }}</td>
                        <td>{{ Str::limit($item->service_type, 30) }}</td>
                        <td>
                            @if ($item->requires_approval)
                                <span class="badge yellow">Revisar</span>
                            @else
                                —
                            @endif
                        </td>
                        <td><span class="badge {{ $item->statusBadgeClass() }}">{{ $item->statusLabel() }}</span></td>
                        <td><a href="{{ route('advisor.appointments.show', $item) }}" class="btn btn-primary btn-sm">Ver</a></td>
                    </tr>
                @empty
                    <tr><td colspan="7">No hay solicitudes con ese filtro.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div style="margin-top:12px;">{{ $requests->links('pagination.simple') }}</div>
    </div>
@endsection
