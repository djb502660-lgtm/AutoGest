@extends($layout ?? 'layouts.advisor')

@section('title', 'Solicitudes chatbot')
@section('heading', 'Solicitudes de cita (chatbot)')
@section('subheading')
    Revisa las citas y alertas generadas por el asistente del cliente.
@endsection

@section('content')
    @php
        $showRoute = $showRoute ?? 'advisor.chatbot-appointments.show';
        $statusCounts = collect($statusCounts ?? []);
        $convertedCount = (int) ($statusCounts['convertida'] ?? 0);
        $cancelledCount = (int) ($statusCounts['cancelada'] ?? 0);
        $rejectedCount = (int) ($statusCounts['rechazada'] ?? 0);
        $pendingCount = (int) ($statusCounts['pendiente'] ?? 0);
        $otherCount = $convertedCount + $cancelledCount + $rejectedCount;
    @endphp

    @if (! empty($chatbotAlerts) && $chatbotAlerts->isNotEmpty())
        <div class="panel" style="margin-bottom:1rem;">
            <h3 style="margin:0 0 12px;font-size:1rem;">Alertas recientes del chatbot</h3>
            <ul class="compact-list">
                @foreach ($chatbotAlerts as $alert)
                    @php
                        $alertUrl = $alert->appointment_request_id
                            ? route($showRoute, $alert->appointment_request_id)
                            : null;
                    @endphp
                    <li>
                        @if ($alertUrl)
                            <a href="{{ $alertUrl }}">
                                <strong>{{ $alert->title }}</strong>
                                <span>{{ $alert->message }}</span>
                            </a>
                        @else
                            <strong>{{ $alert->title }}</strong>
                            <span>{{ $alert->message }}</span>
                        @endif
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="panel">
        <form method="GET" class="filters">
            <select name="status">
                <option value="pendiente" @selected($status === 'pendiente')>Pendientes ({{ $pendingCount }})</option>
                <option value="confirmada" @selected($status === 'confirmada')>Confirmadas ({{ (int) ($statusCounts['confirmada'] ?? 0) }})</option>
                <option value="cancelada" @selected($status === 'cancelada')>Canceladas ({{ $cancelledCount }})</option>
                <option value="convertida" @selected($status === 'convertida')>Convertidas ({{ $convertedCount }})</option>
                <option value="rechazada" @selected($status === 'rechazada')>Rechazadas ({{ $rejectedCount }})</option>
                <option value="todas" @selected($status === 'todas')>Todas ({{ $statusCounts->sum() }})</option>
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
                        <td><a href="{{ route($showRoute, $item) }}" class="btn btn-primary btn-sm">Ver</a></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">
                            No hay solicitudes con ese filtro.
                            @if ($status === 'pendiente' && $otherCount > 0)
                                @if ($convertedCount > 0)
                                    Hay {{ $convertedCount }} convertida(s) a orden.
                                @endif
                                <a href="{{ route($indexRoute ?? 'advisor.chatbot-appointments.index', ['status' => 'todas']) }}">Ver todas</a>
                            @endif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div style="margin-top:12px;">{{ $requests->links('pagination.simple') }}</div>
    </div>
@endsection
