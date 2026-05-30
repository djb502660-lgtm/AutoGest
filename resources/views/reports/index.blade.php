@extends('layouts.admin')

@section('title', 'Reportes')
@section('heading', 'Reportes del sistema')
@section('subheading', 'Genera reportes de mantenimientos, gastos, vehículos y pendientes.')

@push('styles')
<style>
    .report-layout { display:grid; grid-template-columns:1.4fr 0.6fr; gap:16px; }
    .report-cards { display:grid; grid-template-columns:1fr 1fr; gap:12px; }
    .report-card {
        border-radius:16px; padding:18px; cursor:pointer;
        background:rgba(8,15,29,0.72); border:1px solid rgba(148,163,184,0.12);
        transition:border-color .2s, transform .2s;
    }
    .report-card.active { border-color:rgba(34,197,94,0.35); transform:translateY(-2px); }
    .report-card h3 { margin:0 0 6px; font-size:1rem; color:#f8fafc; }
    .report-card p { margin:0; color:var(--muted); font-size:0.82rem; line-height:1.4; }
    .report-icon { font-size:1.6rem; margin-bottom:10px; }
    .filter-panel { position:sticky; top:20px; }
    @media (max-width:900px) { .report-layout, .report-cards { grid-template-columns:1fr; } }
</style>
@endpush

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
