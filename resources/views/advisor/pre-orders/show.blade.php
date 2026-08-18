@extends('layouts.advisor')

@section('title', 'Detalle de preorden')
@section('heading', 'Detalle de preorden')
@section('subheading', 'Información detallada de la preorden.')

@section('top-actions')
    @if ($preOrder->status === 'pendiente')
        <a href="{{ route('advisor.pre-orders.edit', $preOrder) }}" class="btn btn-secondary">Editar</a>
        <form method="POST" action="{{ route('advisor.pre-orders.confirm', $preOrder) }}" style="display: inline;">
            @csrf
            <button type="submit" class="btn btn-success">Confirmar</button>
        </form>
        <form method="POST" action="{{ route('advisor.pre-orders.reject', $preOrder) }}" style="display: inline;" onsubmit="return confirm('¿Rechazar esta preorden?')">
            @csrf
            <button type="submit" class="btn btn-danger">Rechazar</button>
        </form>
    @endif
    @if (in_array($preOrder->status, ['pendiente', 'confirmada']))
        <form method="POST" action="{{ route('advisor.pre-orders.convert', $preOrder) }}" style="display: inline;" onsubmit="return confirm('¿Convertir esta preorden en orden de servicio?')">
            @csrf
            <button type="submit" class="btn btn-primary">Convertir a orden</button>
        </form>
    @endif
    <a href="{{ route('advisor.pre-orders.index') }}" class="btn btn-secondary">Volver</a>
@endsection

@section('content')
    <div class="panel">
        <div class="detail-grid">
            <div>
                <strong>Cliente:</strong>
                <p>{{ $preOrder->client->name }} ({{ $preOrder->client->email }})</p>
            </div>
            <div>
                <strong>Vehículo:</strong>
                <p>{{ $preOrder->vehicle->plate }} - {{ $preOrder->vehicle->brand }} {{ $preOrder->vehicle->model }}</p>
            </div>
            <div>
                <strong>Fecha solicitada:</strong>
                <p>{{ $preOrder->requested_date->format('d/m/Y') }} @if($preOrder->preferred_time) - {{ $preOrder->preferred_time }} @endif</p>
            </div>
            <div>
                <strong>Tipo de servicio:</strong>
                <p>{{ $preOrder->service_type }}</p>
            </div>
            <div>
                <strong>Prioridad:</strong>
                <p>
                    <span class="badge {{ $preOrder->priority === 'urgente' ? 'red' : ($preOrder->priority === 'alta' ? 'orange' : 'green') }}">
                        {{ ucfirst($preOrder->priority) }}
                    </span>
                </p>
            </div>
            <div>
                <strong>Fuente:</strong>
                <p>
                    <span class="badge {{ $preOrder->source === 'chatbot' ? 'blue' : 'gray' }}">
                        {{ ucfirst($preOrder->source) }}
                    </span>
                </p>
            </div>
            <div>
                <strong>Estado:</strong>
                <p>
                    <span class="badge {{ $preOrder->status === 'convertida' ? 'green' : ($preOrder->status === 'rechazada' ? 'red' : 'yellow') }}">
                        {{ $preOrder->statusLabel() }}
                    </span>
                </p>
            </div>
        </div>

        <div class="form-group">
            <strong>Descripción:</strong>
            <p>{{ $preOrder->description }}</p>
        </div>

        @if ($preOrder->notes)
            <div class="form-group">
                <strong>Notas:</strong>
                <p>{{ $preOrder->notes }}</p>
            </div>
        @endif

        @if ($preOrder->service_order_id)
            <div class="alert alert-success">
                <strong>Esta preorden fue convertida a la orden de servicio #{{ $preOrder->serviceOrder->order_number }}</strong>
                <a href="{{ route('advisor.orders.show', $preOrder->serviceOrder) }}" class="btn btn-secondary btn-sm">Ver orden</a>
            </div>
        @endif
    </div>
@endsection
