@extends('layouts.admin')

@section('title', 'Reportes')
@section('heading', 'Reportes del sistema')
@section('subheading', 'Genera reportes de mantenimientos, gastos, vehículos y pendientes.')


@section('content')
    <div class="report-layout">
        <div class="report-cards">
            <div class="report-card active" data-type="mantenimientos">
                <div class="report-icon">🛠️</div>
                <h3>Reporte de mantenimientos</h3>
                <p>Historial de servicios realizados con filtros por fecha y vehículo.</p>
            </div>
            <div class="report-card" data-type="gastos">
                <div class="report-icon">💰</div>
                <h3>Reporte de gastos</h3>
                <p>Resumen de costos por vehículo y servicios completados.</p>
            </div>
            <div class="report-card" data-type="vehiculos">
                <div class="report-icon">🚗</div>
                <h3>Reporte de vehículos</h3>
                <p>Estado general de la flota registrada en el sistema.</p>
            </div>
            <div class="report-card" data-type="pendientes">
                <div class="report-icon">⏳</div>
                <h3>Reporte de pendientes</h3>
                <p>Mantenimientos y órdenes de servicio abiertas.</p>
            </div>
        </div>

        <div class="panel filter-panel">
            <h3 style="margin:0 0 14px;">Filtros del reporte</h3>
            <form method="GET" action="{{ route('reports.generate') }}">
                <input type="hidden" name="type" id="report-type" value="mantenimientos">
                <div class="field">
                    <label for="vehicle_id">Vehículo</label>
                    <select id="vehicle_id" name="vehicle_id">
                        <option value="">Todos los vehículos</option>
                        @foreach ($vehicles as $vehicle)
                            <option value="{{ $vehicle->id }}">{{ $vehicle->plate }} — {{ $vehicle->brand }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label for="from">Fecha desde</label>
                    <input type="date" id="from" name="from">
                </div>
                <div class="field">
                    <label for="to">Fecha hasta</label>
                    <input type="date" id="to" name="to">
                </div>
                <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;">Generar reporte</button>
            </form>
        </div>
    </div>

    <script>
        document.querySelectorAll('.report-card').forEach(card => {
            card.addEventListener('click', () => {
                document.querySelectorAll('.report-card').forEach(c => c.classList.remove('active'));
                card.classList.add('active');
                document.getElementById('report-type').value = card.dataset.type;
            });
        });
    </script>
@endsection
