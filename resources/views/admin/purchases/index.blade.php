@extends('layouts.admin')

@section('title', 'Compras')
@section('heading', 'Gestión de compras')
@section('subheading', 'Administra las órdenes de compra a proveedores.')

@section('top-actions')
    <a href="{{ route('purchases.create') }}" class="btn btn-primary">+ Nueva compra</a>
@endsection

@section('content')
    <div class="panel">
        <form method="GET" action="{{ route('purchases.index') }}" class="filters">
            <input type="text" name="search" value="{{ $search }}" placeholder="Buscar por número o proveedor...">
            <select name="status">
                <option value="">Todos los estados</option>
                <option value="pendiente" @selected($status === 'pendiente')">Pendiente</option>
                <option value="recibida" @selected($status === 'recibida')">Recibida</option>
                <option value="cancelada" @selected($status === 'cancelada')">Cancelada</option>
            </select>
            <button type="submit" class="btn btn-secondary">Filtrar</button>
            @if ($search || $status)
                <a href="{{ route('purchases.index') }}" class="btn btn-secondary">Limpiar</a>
            @endif
        </form>

        <table class="table">
            <thead>
                <tr>
                    <th>Número</th>
                    <th>Proveedor</th>
                    <th>Fecha</th>
                    <th>Subtotal</th>
                    <th>IVA</th>
                    <th>Total</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($purchases as $purchase)
                    <tr>
                        <td>{{ $purchase->purchase_number }}</td>
                        <td>{{ $purchase->supplier->name }}</td>
                        <td>{{ $purchase->purchase_date->format('d/m/Y') }}</td>
                        <td>${{ number_format($purchase->subtotal, 2) }}</td>
                        <td>${{ number_format($purchase->tax, 2) }}</td>
                        <td>${{ number_format($purchase->total, 2) }}</td>
                        <td>
                            <span class="badge {{ $purchase->status === 'recibida' ? 'green' : ($purchase->status === 'cancelada' ? 'red' : 'yellow') }}">
                                {{ ucfirst($purchase->status) }}
                            </span>
                        </td>
                        <td>
                            <div class="actions-inline">
                                <a href="{{ route('purchases.show', $purchase) }}" class="btn btn-secondary btn-sm">Ver</a>
                                @if ($purchase->status === 'pendiente')
                                    <a href="{{ route('purchases.edit', $purchase) }}" class="btn btn-secondary btn-sm">Editar</a>
                                    <form method="POST" action="{{ route('purchases.receive', $purchase) }}" data-confirm="¿Recibir esta compra y actualizar el stock?" data-confirm-title="Recibir compra" data-confirm-label="Recibir" data-confirm-danger="0">
                                        @csrf
                                        <button type="submit" class="btn btn-success btn-sm">Recibir</button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8">No se encontraron compras.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="pagination">
            {{ $purchases->links('pagination.simple') }}
        </div>
    </div>
@endsection
