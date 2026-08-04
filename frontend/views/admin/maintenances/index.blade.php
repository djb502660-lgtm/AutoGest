@extends('layouts.admin')

@section('title', 'Mantenimientos')
@section('heading', 'Gestión de mantenimientos')
@section('subheading', 'Historial y control de servicios preventivos y correctivos.')

@section('top-actions')
    <a href="{{ route('maintenances.create') }}" class="btn btn-primary">+ Nuevo mantenimiento</a>
@endsection

@section('content')
    <div class="panel">
        <form method="GET" class="filters">
            <select name="vehicle_id">
                <option value="">Todos los vehículos</option>
                @foreach ($vehicles as $vehicle)
                    <option value="{{ $vehicle->id }}" @selected($vehicleId == $vehicle->id)>{{ $vehicle->plate }} — {{ $vehicle->brand }}</option>
                @endforeach
            </select>
            <select name="type">
                <option value="">Todos los tipos</option>
                <option value="preventivo" @selected($type === 'preventivo')>Preventivo</option>
                <option value="correctivo" @selected($type === 'correctivo')>Correctivo</option>
            </select>
            <select name="status">
                <option value="">Todos los estados</option>
                <option value="pendiente" @selected($status === 'pendiente')>Pendiente</option>
                <option value="en_proceso" @selected($status === 'en_proceso')>En proceso</option>
                <option value="completado" @selected($status === 'completado')>Completado</option>
                <option value="cancelado" @selected($status === 'cancelado')>Cancelado</option>
            </select>
            <input type="date" name="from" value="{{ $from }}" title="Desde">
            <input type="date" name="to" value="{{ $to }}" title="Hasta">
            <button type="submit" class="btn btn-secondary">Filtrar</button>
        </form>

        <table class="table">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Vehículo</th>
                    <th>Tipo</th>
                    <th>Descripción</th>
                    <th>Kilometraje</th>
                    <th>Costo</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($maintenances as $maintenance)
                    <tr>
                        <td>{{ $maintenance->performed_at?->format('d/m/Y') ?? '—' }}</td>
                        <td>{{ $maintenance->vehicle->plate }}</td>
                        <td>{{ $maintenance->typeLabel() }}</td>
                        <td>{{ $maintenance->description }}</td>
                        <td>{{ $maintenance->mileage_at_service ? number_format($maintenance->mileage_at_service).' km' : '—' }}</td>
                        <td>${{ number_format($maintenance->cost, 2) }}</td>
                        <td><span class="badge {{ $maintenance->statusBadgeClass() }}">{{ $maintenance->statusLabel() }}</span></td>
                        <td>
                            <div class="actions-inline">
                                <button type="button" class="btn btn-secondary btn-sm" onclick="viewMaintenanceDetails({{ $maintenance->id }})">Ver</button>
                                <a href="{{ route('maintenances.edit', $maintenance) }}" class="btn btn-secondary btn-sm">Editar</a>
                                <form method="POST" action="{{ route('maintenances.destroy', $maintenance) }}" onsubmit="return confirm('¿Eliminar este mantenimiento?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8">No hay mantenimientos registrados.</td></tr>
                @endforelse
            </tbody>
        </table>

        <div class="pagination">{{ $maintenances->links('pagination.simple') }}</div>
    </div>

    <!-- Modal de detalles de mantenimiento -->
    <div id="maintenanceModal" class="modal" style="display:none;">
        <div class="modal-content" style="max-width:800px;">
            <div class="modal-header">
                <h3>Detalles del mantenimiento</h3>
                <button type="button" class="modal-close" onclick="closeMaintenanceModal()">×</button>
            </div>
            <div class="modal-body" id="maintenanceDetails">
                <!-- Contenido dinámico -->
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    async function viewMaintenanceDetails(maintenanceId) {
        try {
            const res = await fetch(`{{ url('/mantenimientos') }}/${maintenanceId}`);
            const html = await res.text();
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const details = doc.querySelector('.panel')?.innerHTML || '';
            document.getElementById('maintenanceDetails').innerHTML = details;
            document.getElementById('maintenanceModal').style.display = 'flex';
        } catch (error) {
            console.error('Error loading maintenance details:', error);
            alert('Error al cargar los detalles del mantenimiento');
        }
    }

    function closeMaintenanceModal() {
        document.getElementById('maintenanceModal').style.display = 'none';
    }

    // Cerrar modal al hacer clic fuera
    document.getElementById('maintenanceModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeMaintenanceModal();
        }
    });
</script>
@endpush
