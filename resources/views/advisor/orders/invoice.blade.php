@extends('layouts.advisor')

@section('title', 'Factura ' . $order->order_number)
@section('heading', 'Factura de servicio')
@section('subheading')
    Imprime o guarda el comprobante de la orden de trabajo.
@endsection

@section('top-actions')
    <a href="{{ route('advisor.orders.show', $order) }}" class="btn btn-secondary">← Volver</a>
    <button type="button" class="btn btn-primary" onclick="window.print()">Imprimir</button>
@endsection

@section('content')
    <div class="panel" id="invoiceContent">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2>Factura</h2>
                <p class="mb-0">Orden {{ $order->order_number }}</p>
                <p class="mb-0">Fecha: {{ now()->format('d/m/Y') }}</p>
            </div>
            <div class="text-end">
                <p class="mb-1"><strong>Cliente:</strong> {{ $order->client->name }}</p>
                <p class="mb-1"><strong>Teléfono:</strong> {{ $order->client->phone ?? '—' }}</p>
                <p class="mb-0"><strong>Vehículo:</strong> {{ $order->vehicle->plate }} — {{ $order->vehicle->brand }} {{ $order->vehicle->model }}</p>
            </div>
        </div>

        <div class="mb-3">
            <p><strong>Descripción:</strong> {{ $order->description }}</p>
            <p><strong>Programada:</strong> {{ $order->scheduled_at?->format('d/m/Y H:i') ?? '—' }}</p>
            <p><strong>Mecánico:</strong> {{ $order->mechanic?->name ?? 'Sin asignar' }}</p>
        </div>

        <table class="table">
            <thead>
                <tr>
                    <th>Concepto</th>
                    <th class="text-end">Valor</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Presupuesto preliminar</td>
                    <td class="text-end">${{ number_format($order->estimated_cost ?? 0, 2) }}</td>
                </tr>
                <tr>
                    <td>Total final</td>
                    <td class="text-end">${{ number_format($order->total_cost ?? $order->estimated_cost ?? 0, 2) }}</td>
                </tr>
            </tbody>
        </table>

        <div class="mt-4">
            <p class="mb-1"><strong>Estado de orden:</strong> {{ $order->statusLabel() }}</p>
            <p class="mb-0"><strong>Generado por:</strong> {{ $order->advisor?->name ?? 'Sistema' }}</p>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        @media print {
            .top-actions, .btn { display:none !important; }
            body { background: #fff; }
            .panel { border:none; box-shadow:none; }
        }
    </style>
@endpush
