@extends('layouts.admin')

@section('title', 'Proveedores')
@section('heading', 'Gestión de proveedores')
@section('subheading', 'Administra los proveedores de productos.')

@section('top-actions')
    <a href="{{ route('suppliers.create') }}" class="btn btn-primary">+ Nuevo proveedor</a>
@endsection

@section('content')
    <div class="panel">
        <form method="GET" action="{{ route('suppliers.index') }}" class="filters">
            <input type="text" name="search" value="{{ $search }}" placeholder="Buscar por nombre, contacto, email o teléfono...">
            <button type="submit" class="btn btn-secondary">Filtrar</button>
            @if ($search)
                <a href="{{ route('suppliers.index') }}" class="btn btn-secondary">Limpiar</a>
            @endif
        </form>

        <table class="table">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Contacto</th>
                    <th>Email</th>
                    <th>Teléfono</th>
                    <th>Ciudad</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($suppliers as $supplier)
                    <tr>
                        <td>{{ $supplier->name }}</td>
                        <td>{{ $supplier->contact_person ?? '—' }}</td>
                        <td>{{ $supplier->email ?? '—' }}</td>
                        <td>{{ $supplier->phone ?? '—' }}</td>
                        <td>{{ $supplier->city ?? '—' }}</td>
                        <td>
                            <span class="badge {{ $supplier->is_active ? 'green' : 'red' }}">
                                {{ $supplier->is_active ? 'Activo' : 'Inactivo' }}
                            </span>
                        </td>
                        <td>
                            <div class="actions-inline">
                                <a href="{{ route('suppliers.edit', $supplier) }}" class="btn btn-secondary btn-sm">Editar</a>
                                <form method="POST" action="{{ route('suppliers.destroy', $supplier) }}" data-confirm="¿Eliminar este proveedor?" data-confirm-title="Eliminar proveedor" data-confirm-label="Eliminar">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">No se encontraron proveedores.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="pagination">
            {{ $suppliers->links('pagination.simple') }}
        </div>
    </div>
@endsection
