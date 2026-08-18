@extends('layouts.client')

@section('title', 'Orden '.$order->order_number)
@section('heading', 'Detalle de orden')
@section('subheading')
    {{ $order->vehicle->brand }} {{ $order->vehicle->model }} · {{ $order->vehicle->plate }}
@endsection

@section('content')
    <div class="grid-2">
        <div class="panel">
            <h3 style="margin:0 0 12px;">Información del servicio</h3>
            <p><strong>Orden:</strong> {{ $order->order_number }}</p>
            <p><strong>Servicio:</strong> {{ $order->description }}</p>
            <p><strong>Estado:</strong> <span class="badge {{ $order->statusBadgeClass() }}">{{ $order->statusLabel() }}</span></p>
            <p><strong>Progreso:</strong> {{ $order->progress ?? 0 }}%</p>
            <div class="progress-bar">
                <span @style(['width' => ($order->progress ?? 0).'%'])></span>
            </div>
            @if ($order->mechanic)
                <p style="margin-top:12px;"><strong>Mecánico:</strong> {{ $order->mechanic->name }}</p>
            @endif
            @if ($order->diagnosis)
                <p><strong>Diagnóstico:</strong> {{ $order->diagnosis }}</p>
            @endif
            @if ($order->recommendations)
                <p><strong>Recomendaciones:</strong> {{ $order->recommendations }}</p>
            @endif
        </div>
        <div class="panel">
            <h3 style="margin:0 0 12px;">Fechas</h3>
            <p><strong>Creada:</strong> {{ $order->created_at->format('d/m/Y H:i') }}</p>
            <p><strong>Iniciada:</strong> {{ $order->started_at?->format('d/m/Y H:i') ?? '—' }}</p>
            <p><strong>Completada:</strong> {{ $order->completed_at?->format('d/m/Y H:i') ?? '—' }}</p>
            @if ($order->total_cost > 0)
                <p><strong>Costo total:</strong> ${{ number_format($order->total_cost, 2) }}</p>
            @endif
        </div>
    </div>

    @if ($order->maintenances->isNotEmpty())
    <div class="panel">
        <h3 style="margin:0 0 12px;">Trabajos realizados</h3>
        <table class="table">
            <thead><tr><th>Fecha</th><th>Descripción</th><th>Costo</th><th>Estado</th></tr></thead>
            <tbody>
                @foreach ($order->maintenances as $m)
                    <tr>
                        <td>{{ $m->performed_at?->format('d/m/Y') ?? '—' }}</td>
                        <td>{{ $m->description }}</td>
                        <td>${{ number_format($m->cost, 2) }}</td>
                        <td><span class="badge {{ $m->statusBadgeClass() }}">{{ $m->statusLabel() }}</span></td>
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
            <div class="notif-item">
                {{ $comment->comment }}
                <small>{{ $comment->user->name }} · {{ $comment->created_at->format('d/m/Y H:i') }}</small>
            </div>
        @endforeach
    </div>
    @endif

    <!-- Evidencias Fotográficas (Sprint 5A.4 - Cliente Read-Only) -->
    @if ($order->photos->isNotEmpty())
    <div class="panel">
        <h3 style="margin:0 0 12px;">Evidencias Fotográficas</h3>
        <p style="color:var(--muted);font-size:0.85rem;margin:0 0 12px;">Registro visual del trabajo realizado en su vehículo.</p>
        <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(200px, 1fr)); gap:12px;">
            @foreach ($order->photos as $photo)
                <div style="border:1px solid #e2e8f0; border-radius:8px; overflow:hidden;">
                    @include('layouts.partials.photo-thumb', [
                        'photo' => $photo,
                        'gallery' => 'order-'.$order->id,
                        'class' => 'photo-thumb-cover',
                    ])
                    <div style="padding:8px; background:#f8fafc; border-top:1px solid #e2e8f0;">
                        <div style="font-size:0.75rem; font-weight:700; color:#64748b;">{{ $photo->type_label }}</div>
                        @if ($photo->description)
                            <div style="font-size:0.7rem; color:#475569; margin-top:4px;">{{ $photo->description }}</div>
                        @endif
                        <div style="font-size:0.65rem; color:#94a3b8; margin-top:4px;">{{ $photo->created_at->format('d/m/Y H:i') }}</div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    @endif
@endsection
