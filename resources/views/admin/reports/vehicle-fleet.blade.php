@extends('layouts.admin')

@section('title', 'Expediente Completo de la Flota')
@section('heading', 'Expediente Completo de la Flota')
@section('subheading', 'Historial detallado de todos los vehículos del sistema.')

@section('top-actions')
    <a href="{{ route('reports.index') }}" class="btn btn-secondary">← Volver a reportes</a>
    <a href="{{ route('reports.vehicle.fleet.pdf') }}" class="btn btn-primary">📄 Descargar PDF</a>
@endsection

@section('content')
    <!-- Resumen General de la Flota -->
    <div class="panel">
        <h3>Resumen General de la Flota</h3>
        <div class="summary-grid">
            <div class="summary-box">
                <span>Total Vehículos</span>
                <strong>{{ $report['fleet_summary']['total_vehicles'] }}</strong>
            </div>
            <div class="summary-box">
                <span>Vehículos Activos</span>
                <strong>{{ $report['fleet_summary']['active_vehicles'] }}</strong>
            </div>
            <div class="summary-box">
                <span>Total Órdenes</span>
                <strong>{{ $report['fleet_summary']['total_orders'] }}</strong>
            </div>
            <div class="summary-box">
                <span>Total Mantenimientos</span>
                <strong>{{ $report['fleet_summary']['total_maintenances'] }}</strong>
            </div>
        </div>
    </div>

    <!-- Expedientes Individuales de Cada Vehículo -->
    @foreach($report['vehicles'] as $vehicleData)
    <div class="panel" style="margin-bottom: 32px; page-break-inside: avoid;">
        <div style="border-bottom: 2px solid #0284c7; padding-bottom: 12px; margin-bottom: 16px;">
            <h3 style="color: #0284c7; margin: 0;">VEHÍCULO {{ $vehicleData['vehicle']->plate }}</h3>
            <p style="margin: 4px 0 0; color: #64748b;">{{ $vehicleData['vehicle']->brand }} {{ $vehicleData['vehicle']->model }} ({{ $vehicleData['vehicle']->year }})</p>
        </div>

        <!-- Información del Vehículo -->
        <div style="margin-bottom: 16px;">
            <h4>Información del Vehículo</h4>
            <div class="grid-2">
                <div>
                    <p><strong>Placa:</strong> {{ $vehicleData['vehicle']->plate }}</p>
                    <p><strong>Marca:</strong> {{ $vehicleData['vehicle']->brand }}</p>
                    <p><strong>Modelo:</strong> {{ $vehicleData['vehicle']->model }}</p>
                    <p><strong>Año:</strong> {{ $vehicleData['vehicle']->year }}</p>
                    <p><strong>Kilometraje:</strong> {{ number_format($vehicleData['vehicle']->mileage) }} km</p>
                    <p><strong>Estado:</strong> <span class="badge {{ $vehicleData['vehicle']->statusBadgeClass() }}">{{ $vehicleData['vehicle']->statusLabel() }}</span></p>
                </div>
                <div>
                    <p><strong>Propietario:</strong> {{ $vehicleData['vehicle']->client?->name ?? 'Sin asignar' }}</p>
                    <p><strong>VIN:</strong> {{ $vehicleData['vehicle']->vin ?? 'N/A' }}</p>
                    <p><strong>Motor:</strong> {{ $vehicleData['vehicle']->engine_number ?? 'N/A' }}</p>
                    <p><strong>Transmisión:</strong> {{ $vehicleData['vehicle']->transmission_type ?? 'N/A' }}</p>
                </div>
            </div>
        </div>

        <!-- Resumen del Vehículo -->
        <div style="margin-bottom: 16px;">
            <h4>Resumen del Vehículo</h4>
            <div class="summary-grid" style="grid-template-columns: repeat(4, 1fr);">
                <div class="summary-box">
                    <span>Mantenimientos</span>
                    <strong>{{ $vehicleData['summary']['total_maintenances'] }}</strong>
                </div>
                <div class="summary-box">
                    <span>Órdenes</span>
                    <strong>{{ $vehicleData['summary']['total_orders'] }}</strong>
                </div>
                <div class="summary-box">
                    <span>Citas</span>
                    <strong>{{ $vehicleData['summary']['total_appointments'] }}</strong>
                </div>
                <div class="summary-box">
                    <span>Costo Total</span>
                    <strong>${{ number_format($vehicleData['summary']['total_cost'], 2) }}</strong>
                </div>
            </div>
        </div>

        <!-- Citas -->
        @if($vehicleData['vehicle']->appointmentRequests->isNotEmpty())
        <div style="margin-bottom: 16px;">
            <h4>Historial de Citas</h4>
            <table class="table">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Tipo</th>
                        <th>Estado</th>
                        <th>Asesor</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($vehicleData['vehicle']->appointmentRequests as $appointment)
                    <tr>
                        <td>{{ $appointment->requested_date?->format('d/m/Y') }}</td>
                        <td>{{ $appointment->service_type ?? 'N/A' }}</td>
                        <td>{{ $appointment->statusLabel() }}</td>
                        <td>{{ $appointment->advisor?->name ?? 'N/A' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        <!-- Órdenes de Servicio -->
        @if($vehicleData['vehicle']->serviceOrders->isNotEmpty())
        <div style="margin-bottom: 16px;">
            <h4>Órdenes de Servicio</h4>
            @foreach($vehicleData['vehicle']->serviceOrders as $order)
            <div style="border: 1px solid #e2e8f0; border-radius: 6px; padding: 12px; margin-bottom: 8px;">
                <p><strong>Orden #{{ $order->order_number }}</strong> - {{ $order->statusLabel() }}</p>
                <p><strong>Descripción:</strong> {{ $order->description }}</p>
                <p><strong>Mecánico:</strong> {{ $order->mechanic?->name ?? 'Sin asignar' }}</p>
                <p><strong>Costo:</strong> ${{ number_format($order->total_cost ?? 0, 2) }}</p>
            </div>
            @endforeach
        </div>
        @endif

        <!-- Mantenimientos -->
        @if($vehicleData['vehicle']->maintenances->isNotEmpty())
        <div style="margin-bottom: 16px;">
            <h4>Mantenimientos Realizados</h4>
            <table class="table">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Tipo</th>
                        <th>Descripción</th>
                        <th>Mecánico</th>
                        <th>Costo</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($vehicleData['vehicle']->maintenances as $maintenance)
                    <tr>
                        <td>{{ $maintenance->performed_at?->format('d/m/Y') }}</td>
                        <td>{{ $maintenance->typeLabel() }}</td>
                        <td>{{ Str::limit($maintenance->description, 40) }}</td>
                        <td>{{ $maintenance->mechanic?->name ?? 'N/A' }}</td>
                        <td>${{ number_format($maintenance->cost, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        <!-- Materiales -->
        @if($vehicleData['vehicle']->serviceOrders->sum(fn($order) => $order->stockMovements->count()) > 0)
        <div style="margin-bottom: 16px;">
            <h4>Materiales y Repuestos Utilizados</h4>
            <table class="table">
                <thead>
                    <tr>
                        <th>Orden</th>
                        <th>Producto</th>
                        <th>Cantidad</th>
                        <th>Tipo</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($vehicleData['vehicle']->serviceOrders as $order)
                        @foreach($order->stockMovements as $movement)
                        <tr>
                            <td>{{ $order->order_number }}</td>
                            <td>{{ $movement->product?->name ?? 'N/A' }}</td>
                            <td>{{ $movement->quantity }}</td>
                            <td>{{ ucfirst($movement->type) }}</td>
                        </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        <!-- Fotografías -->
        @if($vehicleData['vehicle']->serviceOrders->sum(fn($order) => $order->photos->count()) > 0)
        <div style="margin-bottom: 16px;">
            <h4>Evidencia Fotográfica</h4>
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(100px, 1fr)); gap: 8px;">
                @foreach($vehicleData['vehicle']->serviceOrders as $order)
                    @foreach($order->photos as $photo)
                    <div style="border: 1px solid #e2e8f0; border-radius: 4px; overflow: hidden;">
                        <img src="{{ $photo->url }}" alt="Foto" style="width: 100%; height: 80px; object-fit: cover;">
                        <div style="padding: 4px; background: #f8fafc; font-size: 7px;">
                            <div>{{ $photo->type_label }}</div>
                            <div>{{ $photo->created_at->format('d/m/Y') }}</div>
                        </div>
                    </div>
                    @endforeach
                @endforeach
            </div>
        </div>
        @endif

        <!-- Próximos Mantenimientos -->
        @if($vehicleData['vehicle']->maintenanceSchedules->isNotEmpty())
        <div style="margin-bottom: 16px;">
            <h4>Próximos Mantenimientos</h4>
            <table class="table">
                <thead>
                    <tr>
                        <th>Título</th>
                        <th>Fecha</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($vehicleData['vehicle']->maintenanceSchedules as $schedule)
                    <tr>
                        <td>{{ $schedule->title }}</td>
                        <td>{{ $schedule->scheduled_date?->format('d/m/Y') }}</td>
                        <td>{{ $schedule->statusLabel() }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        <!-- Alertas -->
        @if($vehicleData['vehicle']->alerts->isNotEmpty())
        <div style="margin-bottom: 16px;">
            <h4>Alertas</h4>
            @foreach($vehicleData['vehicle']->alerts as $alert)
            <div style="border: 1px solid #e2e8f0; border-radius: 4px; padding: 8px; margin-bottom: 4px;">
                <p><strong>{{ $alert->title }}</strong> - {{ $alert->message }}</p>
                <small>{{ $alert->due_date?->format('d/m/Y') }} | {{ ucfirst($alert->severity) }}</small>
            </div>
            @endforeach
        </div>
        @endif

        <div style="border-top: 1px solid #e2e8f0; padding-top: 8px; margin-top: 16px; text-align: center; color: #94a3b8; font-size: 0.8rem;">
            FIN DEL EXPEDIENTE DEL VEHÍCULO {{ $vehicleData['vehicle']->plate }}
        </div>
    </div>
    @endforeach

    <div class="panel" style="text-align: center; color: #64748b; font-size: 0.85rem;">
        <p>Reporte generado el {{ $generatedAt }} por {{ auth()->user()->name }}</p>
        <p>AutoGest - Sistema de Gestión de Mantenimiento Vehicular</p>
    </div>
@endsection
