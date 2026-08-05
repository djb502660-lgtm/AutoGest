@extends('layouts.advisor')

@section('title', 'Orden '.$order->order_number)
@section('heading', 'Detalle de orden de trabajo')
@section('subheading')
    {{ $order->vehicle->brand }} {{ $order->vehicle->model }} · {{ $order->vehicle->plate }}
@endsection

@section('top-actions')
    <a href="{{ route('advisor.orders.invoice', $order) }}" class="btn btn-primary">Factura</a>
    <a href="{{ route('advisor.orders.edit', $order) }}" class="btn btn-secondary">Editar</a>
    <a href="{{ route('advisor.orders.index') }}" class="btn btn-secondary">← Lista</a>
@endsection

@section('content')
    <div class="grid-2">
        <div class="panel">
            <h3 style="margin:0 0 12px;">Información del vehículo</h3>
            <p><strong>Placa:</strong> {{ $order->vehicle->plate }}</p>
            <p><strong>Marca / Modelo:</strong> {{ $order->vehicle->brand }} {{ $order->vehicle->model }} ({{ $order->vehicle->year }})</p>
            <p><strong>Cliente:</strong> {{ $order->client->name }}</p>
            <p><strong>Teléfono:</strong> {{ $order->client->phone ?? '—' }}</p>
        </div>
        <div class="panel">
            <h3 style="margin:0 0 12px;">Información del servicio</h3>
            <p><strong>Orden:</strong> {{ $order->order_number }}</p>
            <p><strong>Descripción:</strong> {{ $order->description }}</p>
            <p><strong>Prioridad:</strong> {{ ucfirst($order->priority) }}</p>
            <p><strong>Estado:</strong> <span class="badge {{ $order->statusBadgeClass() }}">{{ $order->statusLabel() }}</span></p>
            <p><strong>Progreso taller:</strong> {{ $order->progress ?? 0 }}%</p>
            <p><strong>Programada:</strong> {{ $order->scheduled_at?->format('d/m/Y H:i') ?? '—' }}</p>
            <p><strong>Mecánico:</strong> {{ $order->mechanic?->name ?? 'Sin asignar' }}</p>
            @if ($order->diagnosis)
                <p style="margin-top:12px;"><strong>Diagnóstico (taller):</strong> {{ $order->diagnosis }}</p>
            @endif
        </div>
    </div>

    @if (! in_array($order->status, ['completada', 'entregada', 'cancelada'], true))
        <div class="panel">
            <h3 style="margin:0 0 12px;">{{ $order->mechanic_id ? 'Reasignar mecánico' : 'Asignar mecánico' }}</h3>
            <p style="color:var(--muted);font-size:0.88rem;margin:0 0 12px;">
                {{ $order->mechanic_id ? 'Puedes cambiar el responsable del trabajo si la carga operativa del taller lo requiere.' : 'Al asignar un mecánico, la orden aparecerá en su panel de órdenes de servicio.' }}
            </p>
            <form method="POST" action="{{ route('advisor.orders.assign', $order) }}" class="filters">
                @csrf
                @method('PUT')
                <select name="mechanic_id" required>
                    <option value="">Seleccionar mecánico...</option>
                    @foreach ($mechanics as $mechanic)
                        <option value="{{ $mechanic->id }}" @selected($order->mechanic_id === $mechanic->id)>{{ $mechanic->name }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn btn-primary">{{ $order->mechanic_id ? 'Actualizar asignación' : 'Asignar al taller' }}</button>
            </form>
        </div>
    @elseif ($order->mechanic)
        <div class="panel">
            <h3 style="margin:0 0 8px;">Mecánico asignado</h3>
            <p style="margin:0;"><strong>{{ $order->mechanic->name }}</strong> — {{ $order->mechanic->email }}</p>
            <p style="color:var(--muted);font-size:0.84rem;margin:8px 0 0;">
                El mecánico gestiona diagnóstico, avance y cierre técnico desde su módulo.
            </p>
        </div>
    @endif

    @if ($order->maintenances->isNotEmpty())
        <div class="panel">
            <h3 style="margin:0 0 12px;">Mantenimientos vinculados</h3>
            <table class="table">
                <thead><tr><th>Fecha</th><th>Descripción</th><th>Costo</th><th>Estado</th></tr></thead>
                <tbody>
                    @foreach ($order->maintenances as $maintenance)
                        <tr>
                            <td>{{ $maintenance->performed_at?->format('d/m/Y') ?? '—' }}</td>
                            <td>{{ $maintenance->description }}</td>
                            <td>${{ number_format($maintenance->cost, 2) }}</td>
                            <td><span class="badge {{ $maintenance->statusBadgeClass() }}">{{ $maintenance->statusLabel() }}</span></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    @if ($order->comments->isNotEmpty())
        <div class="panel">
            <h3 style="margin:0 0 12px;">Observaciones del taller</h3>
            @foreach ($order->comments as $comment)
                <div class="comment">
                    {{ $comment->comment }}
                    <small>{{ $comment->user->name }} · {{ $comment->created_at->format('d/m/Y H:i') }}</small>
                </div>
            @endforeach
        </div>
    @endif

    <!-- Evidencias Fotográficas (Sprint 5A.4) -->
    @if ($order->photos->isNotEmpty())
        <div class="panel">
            <h3 style="margin:0 0 12px;">Evidencias Fotográficas</h3>
            <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(200px, 1fr)); gap:12px;">
                @foreach ($order->photos as $photo)
                    <div style="border:1px solid #e2e8f0; border-radius:8px; overflow:hidden;">
                        <img src="{{ asset('storage/' . $photo->photo_path) }}" alt="{{ $photo->description ?? 'Foto' }}" style="width:100%; height:150px; object-fit:cover; cursor:pointer;" onclick="window.open('{{ asset('storage/' . $photo->photo_path) }}', '_blank')">
                        <div style="padding:8px; background:#f8fafc; border-top:1px solid #e2e8f0;">
                            <div style="font-size:0.75rem; font-weight:700; color:#64748b;">{{ $photo->type_label }}</div>
                            @if ($photo->description)
                                <div style="font-size:0.7rem; color:#475569; margin-top:4px;">{{ $photo->description }}</div>
                            @endif
                            <div style="font-size:0.65rem; color:#94a3b8; margin-top:4px;">{{ $photo->user->name }} · {{ $photo->created_at->format('d/m/Y H:i') }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
@endsection
