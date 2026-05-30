@extends('layouts.mechanic')

@section('title', 'Orden '.$order->order_number)
@section('heading', 'Detalle de orden de servicio')
@section('subheading')
    {{ $order->vehicle->brand }} {{ $order->vehicle->model }} {{ $order->vehicle->year }} · {{ $order->vehicle->plate }}
@endsection

@section('top-actions')
    <a href="{{ route('mechanic.vehicles.show', $order->vehicle) }}" class="btn btn-secondary">Ver vehículo</a>
    <a href="{{ route('mechanic.maintenances.create', ['order_id' => $order->id]) }}" class="btn btn-primary">Registrar mantenimiento</a>
@endsection

@section('content')
    <div class="grid-2">
        <div class="panel">
            <h3 style="margin:0 0 12px;">Información del vehículo</h3>
            <p><strong>Placa:</strong> {{ $order->vehicle->plate }}</p>
            <p><strong>Marca / Modelo:</strong> {{ $order->vehicle->brand }} {{ $order->vehicle->model }}</p>
            <p><strong>Kilometraje:</strong> {{ number_format($order->vehicle->mileage) }} km</p>
            <p><strong>Cliente:</strong> {{ $order->client->name }}</p>
        </div>
        <div class="panel">
            <h3 style="margin:0 0 12px;">Información del servicio</h3>
            <p><strong>Orden:</strong> {{ $order->order_number }}</p>
            <p><strong>Servicio:</strong> {{ $order->description }}</p>
            <p><strong>Estado:</strong> <span class="badge {{ $order->statusBadgeClass() }}">{{ $order->statusLabel() }}</span></p>
            <p><strong>Progreso:</strong> {{ $order->progress ?? 0 }}%</p>
            <div class="progress-bar"><span style="width:{{ $order->progress ?? 0 }}%"></span></div>
            @if ($order->diagnosis)
                <p style="margin-top:12px;"><strong>Diagnóstico:</strong> {{ $order->diagnosis }}</p>
            @endif
            @if ($order->recommendations)
                <p><strong>Recomendaciones:</strong> {{ $order->recommendations }}</p>
            @endif
        </div>
    </div>

    <div class="grid-2">
        <div class="panel">
            <h3 style="margin:0 0 12px;">Actualizar estado</h3>
            <form method="POST" action="{{ route('mechanic.orders.status', $order) }}">
                @csrf @method('PUT')
                <div class="field">
                    <label>Nuevo estado</label>
                    <select name="status" required>
                        @foreach (['recibida','en_proceso','completada','entregada'] as $st)
                            <option value="{{ $st }}" @selected(old('status', $order->status) === $st)>{{ ucfirst(str_replace('_',' ',$st)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label>Progreso (%)</label>
                    <input type="number" name="progress" min="0" max="100" value="{{ old('progress', $order->progress ?? 0) }}" required>
                </div>
                <div class="field">
                    <label>Diagnóstico técnico</label>
                    <textarea name="diagnosis" rows="3">{{ old('diagnosis', $order->diagnosis) }}</textarea>
                </div>
                <div class="field">
                    <label>Recomendaciones</label>
                    <textarea name="recommendations" rows="2">{{ old('recommendations', $order->recommendations) }}</textarea>
                </div>
                <button type="submit" class="btn btn-primary">Guardar cambios</button>
            </form>
        </div>

        <div class="panel">
            <h3 style="margin:0 0 12px;">Informar avance</h3>
            <form method="POST" action="{{ route('mechanic.orders.progress', $order) }}">
                @csrf @method('PUT')
                <div class="field">
                    <label>Progreso del trabajo (%)</label>
                    <input type="range" name="progress" min="0" max="100" value="{{ $order->progress ?? 0 }}" oninput="this.nextElementSibling.value=this.value">
                    <output style="color:var(--muted);font-size:0.82rem;">{{ $order->progress ?? 0 }}%</output>
                </div>
                <div class="field">
                    <label>Comentario de avance</label>
                    <textarea name="comment" rows="3" placeholder="Describe el avance del trabajo..."></textarea>
                </div>
                <button type="submit" class="btn btn-warning">Actualizar avance</button>
            </form>

            <hr style="border-color:rgba(148,163,184,0.12);margin:16px 0;">

            <h3 style="margin:0 0 12px;">Observación técnica</h3>
            <form method="POST" action="{{ route('mechanic.orders.comments', $order) }}">
                @csrf
                <div class="field">
                    <textarea name="comment" rows="3" required placeholder="Registra una observación técnica..."></textarea>
                </div>
                <button type="submit" class="btn btn-secondary">Guardar observación</button>
            </form>
        </div>
    </div>

    <div class="panel">
        <h3 style="margin:0 0 12px;">Observaciones y comentarios</h3>
        @forelse ($order->comments as $comment)
            <div class="comment">
                {{ $comment->comment }}
                <small>{{ $comment->user->name }} · {{ $comment->created_at->format('d/m/Y H:i') }}</small>
            </div>
        @empty
            <p style="color:var(--muted);font-size:0.84rem;">Sin observaciones registradas.</p>
        @endforelse
    </div>

    @if ($order->maintenances->isNotEmpty())
    <div class="panel">
        <h3 style="margin:0 0 12px;">Mantenimientos vinculados</h3>
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
@endsection
