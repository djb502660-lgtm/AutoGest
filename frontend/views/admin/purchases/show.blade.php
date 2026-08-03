@extends('layouts.admin')

@section('title', 'Detalle de compra')
@section('heading', 'Detalle de compra')
@section('subheading', 'Información detallada de la orden de compra.')

@section('top-actions')
    @if ($purchase->status === 'pendiente')
        <a href="{{ route('purchases.edit', $purchase) }}" class="btn btn-secondary">Editar</a>
        <form method="POST" action="{{ route('purchases.receive', $purchase) }}" style="display: inline;" onsubmit="return confirm('¿Recibir esta compra y actualizar el stock?')">
            @csrf
            <button type="submit" class="btn btn-success">Recibir compra</button>
        </form>
    @endif
    <a href="{{ route('purchases.index') }}" class="btn btn-secondary">Volver</a>
@endsection

@section('content')
    <div class="panel">
        <div class="detail-grid">
            <div>
                <strong>Número:</strong>
                <p>{{ $purchase->purchase_number }}</p>
            </div>
            <div>
                <strong>Proveedor:</strong>
                <p>{{ $purchase->supplier->name }}</p>
            </div>
            <div>
                <strong>Fecha:</strong>
                <p>{{ $purchase->purchase_date->format('d/m/Y') }}</p>
            </div>
            <div>
                <strong>Estado:</strong>
                <p>
                    <span class="badge {{ $purchase->status === 'recibida' ? 'green' : ($purchase->status === 'cancelada' ? 'red' : 'yellow') }}">
                        {{ ucfirst($purchase->status) }}
                    </span>
                </p>
            </div>
        </div>

        @if ($purchase->notes)
            <div class="form-group">
                <strong>Notas:</strong>
                <p>{{ $purchase->notes }}</p>
            </div>
        @endif

        <h3>Productos</h3>
        <table class="table">
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Cantidad</th>
                    <th>Precio unitario</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($purchase->items as $item)
                    <tr>
                        <td>{{ $item->product->name }} ({{ $item->product->sku }})</td>
                        <td>{{ $item->quantity }}</td>
                        <td>${{ number_format($item->unit_price, 2) }}</td>
                        <td>${{ number_format($item->total, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3"><strong>Subtotal:</strong></td>
                    <td><strong>${{ number_format($purchase->subtotal, 2) }}</strong></td>
                </tr>
                <tr>
                    <td colspan="3"><strong>IVA (16%):</strong></td>
                    <td><strong>${{ number_format($purchase->tax, 2) }}</strong></td>
                </tr>
                <tr>
                    <td colspan="3"><strong>Total:</strong></td>
                    <td><strong>${{ number_format($purchase->total, 2) }}</strong></td>
                </tr>
            </tfoot>
        </table>
    </div>
@endsection
