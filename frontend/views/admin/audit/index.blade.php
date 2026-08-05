@extends('layouts.admin')

@section('title', 'Auditoría')
@section('heading', 'Registro de auditoría')
@section('subheading', 'Trazabilidad de acciones realizadas en el sistema.')

@section('content')
    <div class="panel">
        <form method="GET" action="{{ route('audit.index') }}" class="filters">
            <input type="text" name="search" value="{{ request()->input('search') }}" placeholder="Buscar en descripción...">
            <select name="module">
                <option value="">Todos los módulos</option>
                @foreach ($modules as $moduleOption)
                    <option value="{{ $moduleOption }}" @selected($module === $moduleOption)>{{ ucfirst($moduleOption) }}</option>
                @endforeach
            </select>
            <select name="action">
                <option value="">Todas las acciones</option>
                @foreach ($actions as $actionOption)
                    <option value="{{ $actionOption }}" @selected($action === $actionOption)>{{ ucfirst($actionOption) }}</option>
                @endforeach
            </select>
            <select name="user_id">
                <option value="">Todos los usuarios</option>
                @foreach ($users as $userOption)
                    <option value="{{ $userOption->id }}" @selected($userId == $userOption->id)>{{ $userOption->name }}</option>
                @endforeach
            </select>
            <select name="days">
                <option value="7" @selected($days === 7)>Últimos 7 días</option>
                <option value="30" @selected($days === 30)>Últimos 30 días</option>
                <option value="90" @selected($days === 90)>Últimos 90 días</option>
                <option value="365" @selected($days === 365)>Último año</option>
            </select>
            <button type="submit" class="btn btn-secondary">Filtrar</button>
            @if ($module || $action || $userId || request()->input('search'))
                <a href="{{ route('audit.index') }}" class="btn btn-secondary">Limpiar</a>
            @endif
        </form>

        <table class="table">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Usuario</th>
                    <th>Módulo</th>
                    <th>Acción</th>
                    <th>Descripción</th>
                    <th>IP</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($query as $auditLog)
                    <tr>
                        <td>{{ $auditLog->created_at->format('d/m/Y H:i') }}</td>
                        <td>
                            @if ($auditLog->user)
                                {{ $auditLog->user->name }}
                            @else
                                <span class="text-muted">Sistema</span>
                            @endif
                        </td>
                        <td>
                            @php
                                $moduleClass = match ($auditLog->module) {
                                    'users' => 'blue',
                                    'orders' => 'green',
                                    'inventory' => 'yellow',
                                    'reports' => 'purple',
                                    'vehicles' => 'orange',
                                    default => 'gray',
                                };
                            @endphp
                            <span class="badge {{ $moduleClass }}">{{ ucfirst($auditLog->module) }}</span>
                        </td>
                        <td>{{ ucfirst(str_replace('_', ' ', $auditLog->action)) }}</td>
                        <td>{{ Str::limit($auditLog->description, 80) }}</td>
                        <td>{{ $auditLog->ip_address ?? '—' }}</td>
                        <td>
                            <div class="actions-inline">
                                <a href="{{ route('audit.show', $auditLog) }}" class="btn btn-secondary btn-sm">Ver detalle</a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">No se encontraron registros de auditoría.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="pagination">
            {{ $query->links('pagination.simple') }}
        </div>
    </div>
@endsection