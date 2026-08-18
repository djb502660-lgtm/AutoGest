@extends('layouts.admin')

@section('title', 'Categorías')
@section('heading', 'Gestión de categorías')
@section('subheading', 'Administra las categorías de productos del inventario.')

@section('top-actions')
    <a href="{{ route('categories.create') }}" class="btn btn-primary">+ Nueva categoría</a>
@endsection

@section('content')
    <div class="panel">
        <form method="GET" action="{{ route('categories.index') }}" class="filters">
            <input type="text" name="search" value="{{ $search }}" placeholder="Buscar por nombre o descripción...">
            <button type="submit" class="btn btn-secondary">Filtrar</button>
            @if ($search)
                <a href="{{ route('categories.index') }}" class="btn btn-secondary">Limpiar</a>
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
                @forelse ($categories as $category)
                    <tr>
                        <td>{{ $category->name }}</td>
                        <td>{{ $category->slug }}</td>
                        <td>{{ Str::limit($category->description, 50) ?? '—' }}</td>
                        <td>
                            <span class="badge {{ $category->is_active ? 'green' : 'red' }}">
                                {{ $category->is_active ? 'Activo' : 'Inactivo' }}
                            </span>
                        </td>
                        <td>
                            <div class="actions-inline">
                                <a href="{{ route('categories.edit', $category) }}" class="btn btn-secondary btn-sm">Editar</a>
                                <form method="POST" action="{{ route('categories.destroy', $category) }}" onsubmit="return confirm('¿Eliminar esta categoría?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">No se encontraron categorías.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="pagination">
            {{ $categories->links('pagination.simple') }}
        </div>
    </div>
@endsection
