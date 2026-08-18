@extends($layout ?? 'layouts.advisor')

@section('title', 'Solicitud #'.$appointment->id)
@section('heading', 'Solicitud de cita vía chatbot')
@section('subheading')
    {{ $appointment->client->name }} · {{ $appointment->vehicle->plate }}
@endsection

@section('top-actions')
    <a href="{{ route($indexRoute ?? 'advisor.chatbot-appointments.index') }}" class="btn btn-secondary">← Volver</a>
@endsection

@section('content')
    <div class="grid-2">
        <div class="panel">
            <h3 style="margin:0 0 12px;">Datos de la solicitud</h3>
            <p><strong>Fecha solicitada:</strong> {{ $appointment->requested_date->format('d/m/Y') }}</p>
            <p><strong>Hora:</strong> {{ $appointment->requested_time ? substr($appointment->requested_time, 0, 5) : 'No indicada' }}</p>
            <p><strong>Servicio:</strong> {{ $appointment->service_type }}</p>
            <p><strong>Descripción:</strong> {{ $appointment->description }}</p>
            @if ($appointment->additional_work)
                <p style="margin-top:12px;"><strong>Trabajo adicional:</strong> {{ $appointment->additional_work }}</p>
                <p class="badge yellow">Requiere aprobación del asesor antes de confirmar trabajos extra.</p>
            @endif
            <p><strong>Estado:</strong> <span class="badge {{ $appointment->statusBadgeClass() }}">{{ $appointment->statusLabel() }}</span></p>
        </div>
        <div class="panel">
            <h3 style="margin:0 0 12px;">Vehículo y cliente</h3>
            <p><strong>Placa:</strong> {{ $appointment->vehicle->plate }}</p>
            <p><strong>Vehículo:</strong> {{ $appointment->vehicle->brand }} {{ $appointment->vehicle->model }}</p>
            <p><strong>Cliente:</strong> {{ $appointment->client->name }}</p>
            <p><strong>Teléfono:</strong> {{ $appointment->client->phone ?? '—' }}</p>
        </div>
    </div>

    @if ($appointment->status === 'pendiente')
        <div class="grid-2">
            <div class="panel">
                <h3 style="margin:0 0 12px;">Confirmar y crear orden</h3>
                <form method="POST" action="{{ route($confirmRoute ?? 'advisor.chatbot-appointments.confirm', $appointment) }}">
                    @csrf
                    <div class="field">
                        <label for="mechanic_id">Mecánico (opcional)</label>
                        <select name="mechanic_id" id="mechanic_id">
                            <option value="">Asignar después</option>
                            @foreach ($mechanics as $mechanic)
                                <option value="{{ $mechanic->id }}">{{ $mechanic->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="field">
                        <label for="advisor_notes">Notas internas</label>
                        <textarea name="advisor_notes" id="advisor_notes" rows="2">{{ old('advisor_notes') }}</textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Confirmar cita → Orden de trabajo</button>
                </form>
            </div>
            <div class="panel">
                <h3 style="margin:0 0 12px;">Rechazar solicitud</h3>
                <form method="POST" action="{{ route($rejectRoute ?? 'advisor.chatbot-appointments.reject', $appointment) }}" data-confirm="¿Rechazar esta solicitud? El cliente será notificado con el motivo indicado." data-confirm-title="Rechazar solicitud" data-confirm-label="Rechazar">
                    @csrf
                    <div class="field">
                        <label for="reject_notes">Motivo (se notifica al cliente) *</label>
                        <textarea name="advisor_notes" id="reject_notes" rows="3" required placeholder="Explique el motivo al cliente..."></textarea>
                    </div>
                    <button type="submit" class="btn btn-danger">Rechazar solicitud</button>
                </form>
            </div>
        </div>
    @elseif ($appointment->serviceOrder)
        <div class="panel">
            <p>Orden generada: <a href="{{ route($orderRoute ?? 'advisor.orders.show', $appointment->serviceOrder) }}">{{ $appointment->serviceOrder->order_number }}</a></p>
        </div>
    @endif
@endsection
