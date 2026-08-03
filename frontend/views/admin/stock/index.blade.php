@extends('layouts.admin')

@section('title', 'Movimientos de stock')
@section('heading', 'Movimientos de stock')
@section('subheading', 'Historial de movimientos de inventario.')

@section('top-actions')
    <a href="{{ route('stock.create') }}" class="btn btn-primary">+ Nuevo movimiento</a>
    <a href="{{ route('stock.low') }}" class="btn btn-warning">Stock bajo</a>
@endsection

@section('content')
    <div class="panel">
        <form method="GET" action="{{ route('stock.index') }}" class="filters">
            <input type="text" name="search" value="{{ $search }}" placeholder="Buscar por producto o notas...">
            <select name="type">
                <option value="">Todos los tipos</option>
                <option value="entrada" @selected($type === 'entrada')">Entrada</option>
                <option value="salida" @selected($type === 'salida')">Salida</option>
                <option value="ajuste" @selected($type === 'ajuste')">Ajuste</option>
            </select>
            <button type="submit" class="btn btn-secondary">Filtrar</button>
            @if ($search || $type)
                <a href="{{ route('stock.index') }}" class="btn btn-secondary">Limpiar</a>
            @endif
        </form>

        <table class="table">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Producto</th>
                    <th>Tipo</th>
                    <th>Cantidad</th>
                    <th>Stock anterior</th>
                    <th>Stock nuevo</th>
                    <th>Notas</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($movements as $movement)
                    <tr>
                        <td>{{ $movement->created_at->format('d/m/Y H:i') }}</td>
                        <td>{{ $movement->product->name }} ({{ $movement->product->sku }})</td>
                        <td>
                            <span class="badge {{ $movement->type === 'entrada' ? 'green' : ($movement->type === 'salida' ? 'red' : 'yellow') }}">
                                {{ ucfirst($movement->type) }}
                            </span>
                        </td>
                        <td>{{ $movement->quantity }}</td>
                        <td>{{ $movement->previous_stock }}</td>
                        <td>{{ $movement->new_stock }}</td>
                        <td>{{ $movement->notes }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">No se encontraron movimientos.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="pagination">
            {{ $movements->links('pagination.simple') }}
        </div>
    </div>
@endsection
