@extends('layouts.admin')

@section('title', 'Usuarios')
@section('heading', 'Gestión de usuarios')
@section('subheading', 'Administra cuentas, roles y estados del sistema.')

@section('top-actions')
    <a href="{{ route('users.create') }}" class="btn btn-primary">+ Nuevo usuario</a>
@endsection

@section('content')
    <div class="panel">
        <form method="GET" action="{{ route('users.index') }}" class="filters">
            <input type="text" name="search" value="{{ $search }}" placeholder="Buscar por nombre o correo...">
            <select name="role">
                <option value="">Todos los roles</option>
                @foreach ($roles as $roleOption)
                    <option value="{{ $roleOption->value }}" @selected($role === $roleOption->value)>{{ $roleOption->label() }}</option>
                @endforeach
            </select>
            <select name="status">
                <option value="">Todos los estados</option>
                <option value="activo" @selected($status === 'activo')>Activo</option>
                <option value="inactivo" @selected($status === 'inactivo')>Inactivo</option>
            </select>
            <button type="submit" class="btn btn-secondary">Filtrar</button>
            @if ($search || $role || $status)
                <a href="{{ route('users.index') }}" class="btn btn-secondary">Limpiar</a>
            @endif
        </form>

        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Correo</th>
                        <th>Rol</th>
                        <th>Estado</th>
                        <th>Último acceso</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>
                                @php
                                    $roleClass = match ($user->role->value) {
                                        'admin' => 'blue',
                                        'asesor' => 'blue',
                                        'mecanico' => 'yellow',
                                        default => 'green',
                                    };
                                @endphp
                                <span class="badge {{ $roleClass }}">{{ $user->role->label() }}</span>
                            </td>
                            <td>
                                <span class="badge {{ $user->status === 'activo' ? 'green' : 'red' }}">
                                    {{ ucfirst($user->status) }}
                                </span>
                            </td>
                            <td>{{ $user->last_login_at?->format('d/m/Y H:i') ?? '—' }}</td>
                            <td>
                                <div class="actions-inline">
                                    <a href="{{ route('users.edit', $user) }}" class="btn btn-secondary btn-sm">Editar</a>
                                    @if ($user->id !== auth()->id())
                                        <form method="POST" action="{{ route('users.destroy', $user) }}" onsubmit="return confirm('¿Eliminar o desactivar este usuario?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">No se encontraron usuarios.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pagination">
            {{ $users->links('pagination.simple') }}
        </div>
    </div>
@endsection
