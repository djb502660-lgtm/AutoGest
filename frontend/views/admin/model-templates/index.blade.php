@extends('layouts.admin')

@section('title', 'Plantillas por modelo')
@section('heading', 'Mantenimiento por modelo')
@section('subheading', 'Define el tipo de servicio sugerido según marca y modelo del vehículo.')

@section('top-actions')
    <a href="{{ route('model-templates.create') }}" class="btn btn-primary">+ Nueva plantilla</a>
@endsection

@section('content')
    <div class="panel">
        <form method="GET" class="filters">
            <input type="text" name="search" value="{{ $search }}" placeholder="Buscar marca, modelo o servicio...">
            <select name="type">
                <option value="">Todos los tipos</option>
                <option value="preventivo" @selected(($type ?? '') === 'preventivo')>Preventivo</option>
                <option value="correctivo" @selected(($type ?? '') === 'correctivo')>Correctivo</option>
            </select>
            <select name="state">
                <option value="">Todos los estados</option>
                <option value="active" @selected(($state ?? '') === 'active')>Activas</option>
                <option value="inactive" @selected(($state ?? '') === 'inactive')>Inactivas</option>
            </select>
            <button type="submit" class="btn btn-secondary">Filtrar</button>
        </form>

        <table class="table">
            <thead>
                <tr>
                    <th>Marca</th>
                    <th>Modelo</th>
                    <th>Servicio</th>
                    <th>Tipo</th>
                    <th>Intervalo</th>
                    <th>Estado</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($templates as $template)
                    <tr>
                        <td>{{ $template->brand }}</td>
                        <td>{{ $template->model }}</td>
                        <td>{{ $template->title }}</td>
                        <td>{{ $template->maintenanceTypeLabel() }}</td>
                        <td>
                            @if ($template->interval_km){{ number_format($template->interval_km) }} km @endif
                            @if ($template->interval_months){{ $template->interval_months }} meses @endif
                        </td>
                        <td><span class="badge {{ $template->is_active ? 'green' : 'red' }}">{{ $template->is_active ? 'Activa' : 'Inactiva' }}</span></td>
                        <td>
                            <a href="{{ route('model-templates.edit', $template) }}" class="btn btn-secondary btn-sm">Editar</a>
                            <form method="POST" action="{{ route('model-templates.destroy', $template) }}" class="d-inline" onsubmit="return confirm('¿Eliminar plantilla?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7">No hay plantillas registradas.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div style="margin-top:12px;">{{ $templates->links('pagination.simple') }}</div>
    </div>
@endsection
