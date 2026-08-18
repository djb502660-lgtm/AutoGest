@extends('layouts.admin')

@section('title', 'Marcas')
@section('heading', 'Gestión de marcas')
@section('subheading', 'Administra las marcas de productos del inventario.')

@section('top-actions')
    <a href="{{ route('brands.create') }}" class="btn btn-primary">+ Nueva marca</a>
@endsection

@section('content')
    <div class="panel">
        <form method="GET" action="{{ route('brands.index') }}" class="filters">
            <input type="text" name="search" value="{{ $search }}" placeholder="Buscar por nombre o descripción...">
            <button type="submit" class="btn btn-secondary">Filtrar</button>
            @if ($search)
                <a href="{{ route('brands.index') }}" class="btn btn-secondary">Limpiar</a>
            @endif
        </form>

        <table class="table">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Slug</th>
                    <th>Descripción</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($brands as $brand)
                    <tr>
                        <td>{{ $brand->name }}</td>
                        <td>{{ $brand->slug }}</td>
                        <td>{{ Str::limit($brand->description, 50) ?? '—' }}</td>
                        <td>
                            <span class="badge {{ $brand->is_active ? 'green' : 'red' }}">
                                {{ $brand->is_active ? 'Activo' : 'Inactivo' }}
                            </span>
                        </td>
                        <td>
                            <div class="actions-inline">
                                <a href="{{ route('brands.edit', $brand) }}" class="btn btn-secondary btn-sm">Editar</a>
                                <form method="POST" action="{{ route('brands.destroy', $brand) }}" data-confirm="¿Eliminar esta marca?" data-confirm-title="Eliminar marca" data-confirm-label="Eliminar">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">No se encontraron marcas.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="pagination">
            {{ $brands->links('pagination.simple') }}
        </div>
    </div>
@endsection
