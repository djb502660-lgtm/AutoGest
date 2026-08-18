@extends('layouts.admin')

@section('title', 'Productos')
@section('heading', 'Gestión de productos')
@section('subheading', 'Administra el inventario de productos.')

@section('top-actions')
    <a href="{{ route('products.create') }}" class="btn btn-primary">+ Nuevo producto</a>
@endsection

@section('content')
    <div class="panel">
        <form method="GET" action="{{ route('products.index') }}" class="filters">
            <input type="text" name="search" value="{{ $search }}" placeholder="Buscar por nombre, SKU o descripción...">
            <select name="category">
                <option value="">Todas las categorías</option>
                @foreach ($categories as $cat)
                    <option value="{{ $cat->id }}" @selected($category == $cat->id)>{{ $cat->name }}</option>
                @endforeach
            </select>
            <select name="brand">
                <option value="">Todas las marcas</option>
                @foreach ($brands as $br)
                    <option value="{{ $br->id }}" @selected($brand == $br->id)>{{ $br->name }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-secondary">Filtrar</button>
            @if ($search || $category || $brand)
                <a href="{{ route('products.index') }}" class="btn btn-secondary">Limpiar</a>
            @endif
        </form>

        <table class="table">
            <thead>
                <tr>
                    <th>SKU</th>
                    <th>Nombre</th>
                    <th>Categoría</th>
                    <th>Marca</th>
                    <th>Stock</th>
                    <th>Precio venta</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($products as $product)
                    <tr>
                        <td>{{ $product->sku }}</td>
                        <td>{{ $product->name }}</td>
                        <td>{{ $product->category?->name ?? '—' }}</td>
                        <td>{{ $product->brand?->name ?? '—' }}</td>
                        <td>
                            <span class="badge {{ $product->stock_quantity <= $product->min_stock ? 'red' : 'green' }}">
                                {{ $product->stock_quantity }}
                            </span>
                        </td>
                        <td>${{ number_format($product->sale_price, 2) }}</td>
                        <td>
                            <span class="badge {{ $product->is_active ? 'green' : 'red' }}">
                                {{ $product->is_active ? 'Activo' : 'Inactivo' }}
                            </span>
                        </td>
                        <td>
                            <div class="actions-inline">
                                <a href="{{ route('products.edit', $product) }}" class="btn btn-secondary btn-sm">Editar</a>
                                <form method="POST" action="{{ route('products.destroy', $product) }}" data-confirm="¿Eliminar este producto?" data-confirm-title="Eliminar producto" data-confirm-label="Eliminar">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8">No se encontraron productos.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="pagination">
            {{ $products->links('pagination.simple') }}
        </div>
    </div>
@endsection
