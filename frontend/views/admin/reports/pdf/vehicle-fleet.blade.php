<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Expediente Completo de la Flota</title>
    <style>
        body { 
            font-family: DejaVu Sans, sans-serif; 
            font-size: 9px; 
            color: #1e293b; 
            line-height: 1.3;
        }
        
        .header { 
            border-bottom: 2px solid #0284c7; 
            padding-bottom: 10px; 
            margin-bottom: 12px; 
        }
        
        .brand { 
            font-size: 14px; 
            font-weight: bold; 
            color: #0284c7; 
            margin: 0; 
        }
        
        .meta { 
            font-size: 8px; 
            color: #64748b; 
            margin-top: 3px; 
        }
        
        h1 { 
            font-size: 12px; 
            margin: 6px 0 3px; 
            color: #0f172a;
        }
        
        h2 { 
            font-size: 11px; 
            margin: 10px 0 6px; 
            color: #0284c7;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 3px;
        }
        
        h3 { 
            font-size: 10px; 
            margin: 6px 0 3px; 
            color: #334155;
        }
        
        .vehicle-section {
            margin-bottom: 20px;
            page-break-inside: avoid;
        }
        
        .vehicle-header {
            border-bottom: 2px solid #0284c7;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }
        
        .vehicle-header h2 {
            color: #0284c7;
            margin: 0;
            border: none;
            padding: 0;
        }
        
        .vehicle-header p {
            margin: 3px 0 0;
            color: #64748b;
            font-size: 8px;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
            margin-bottom: 8px;
        }
        
        .info-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 3px;
            padding: 6px;
        }
        
        .info-box p {
            margin: 1px 0;
            font-size: 8px;
        }
        
        .info-box strong {
            color: #475569;
        }
        
        .fleet-summary {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 6px;
            margin-bottom: 12px;
        }
        
        .summary-box {
            background: #f0f9ff;
            border: 1px solid #bae6fd;
            border-radius: 3px;
            padding: 6px;
            text-align: center;
        }
        
        .summary-box span {
            display: block;
            font-size: 7px;
            color: #64748b;
            text-transform: uppercase;
        }
        
        .summary-box strong {
            display: block;
            font-size: 12px;
            margin-top: 2px;
            color: #0284c7;
        }
        
        .vehicle-summary {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 6px;
            margin-bottom: 8px;
        }
        
        table { 
            width: 100%; 
            border-collapse: collapse; 
            font-size: 8px;
            margin-bottom: 8px;
        }
        
        table th { 
            background: #0284c7; 
            color: #fff; 
            padding: 4px 3px; 
            text-align: left; 
            font-size: 7px;
            font-weight: 600;
        }
        
        table td { 
            border: 1px solid #e2e8f0; 
            padding: 3px; 
            vertical-align: top;
        }
        
        table tr:nth-child(even) td { 
            background: #f8fafc; 
        }
        
        .order-item {
            border: 1px solid #e2e8f0;
            border-radius: 3px;
            padding: 6px;
            margin-bottom: 6px;
        }
        
        .photo-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 6px;
            margin-top: 6px;
        }
        
        .photo-item {
            border: 1px solid #e2e8f0;
            border-radius: 3px;
            overflow: hidden;
            text-align: center;
            padding: 3px;
        }
        
        .photo-item img {
            display: block;
            margin: 0 auto;
        }
        
        .photo-info {
            padding: 3px;
            background: #f8fafc;
            border-top: 1px solid #e2e8f0;
            font-size: 6px;
        }
        
        .vehicle-footer {
            border-top: 1px solid #e2e8f0;
            padding-top: 6px;
            margin-top: 12px;
            text-align: center;
            color: #94a3b8;
            font-size: 7px;
        }
        
        .page-break {
            page-break-before: always;
        }
        
        .footer { 
            margin-top: 16px; 
            font-size: 7px; 
            color: #94a3b8; 
            text-align: center; 
            border-top: 1px solid #e2e8f0;
            padding-top: 6px;
        }
    </style>
</head>
<body>
    <div class="header">
        <p class="brand">AutoGest</p>
        <h1>Expediente Completo de la Flota</h1>
        <p class="meta">Generado: {{ $generatedAt }} | Usuario: {{ $user->name }}</p>
    </div>

    <!-- Resumen General de la Flota -->
    <div class="fleet-summary">
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

    <!-- Expedientes Individuales -->
    @foreach($report['vehicles'] as $index => $vehicleData)
    @if($index > 0)
    <div class="page-break"></div>
    @endif
    
    <div class="vehicle-section">
        <div class="vehicle-header">
            <h2>VEHÍCULO {{ $vehicleData['vehicle']->plate }}</h2>
            <p>{{ $vehicleData['vehicle']->brand }} {{ $vehicleData['vehicle']->model }} ({{ $vehicleData['vehicle']->year }})</p>
        </div>

        <!-- Información del Vehículo -->
        <h3>Información del Vehículo</h3>
        <div class="info-grid">
            <div class="info-box">
                <p><strong>Placa:</strong> {{ $vehicleData['vehicle']->plate }}</p>
                <p><strong>Marca:</strong> {{ $vehicleData['vehicle']->brand }}</p>
                <p><strong>Modelo:</strong> {{ $vehicleData['vehicle']->model }}</p>
                <p><strong>Año:</strong> {{ $vehicleData['vehicle']->year }}</p>
                <p><strong>Kilometraje:</strong> {{ number_format($vehicleData['vehicle']->mileage) }} km</p>
                <p><strong>Estado:</strong> {{ $vehicleData['vehicle']->statusLabel() }}</p>
            </div>
            <div class="info-box">
                <p><strong>Propietario:</strong> {{ $vehicleData['vehicle']->client?->name ?? 'Sin asignar' }}</p>
                <p><strong>VIN:</strong> {{ $vehicleData['vehicle']->vin ?? 'N/A' }}</p>
                <p><strong>Motor:</strong> {{ $vehicleData['vehicle']->engine_number ?? 'N/A' }}</p>
                <p><strong>Transmisión:</strong> {{ $vehicleData['vehicle']->transmission_type ?? 'N/A' }}</p>
            </div>
        </div>

        <!-- Resumen del Vehículo -->
        <h3>Resumen del Vehículo</h3>
        <div class="vehicle-summary">
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

        <!-- Citas -->
        @if($vehicleData['vehicle']->appointmentRequests->isNotEmpty())
        <h3>Historial de Citas</h3>
        <table>
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
        @endif

        <!-- Órdenes de Servicio -->
        @if($vehicleData['vehicle']->serviceOrders->isNotEmpty())
        <h3>Órdenes de Servicio</h3>
        @foreach($vehicleData['vehicle']->serviceOrders as $order)
        <div class="order-item">
            <p><strong>Orden #{{ $order->order_number }}</strong> - {{ $order->statusLabel() }}</p>
            <p><strong>Descripción:</strong> {{ $order->description }}</p>
            <p><strong>Mecánico:</strong> {{ $order->mechanic?->name ?? 'Sin asignar' }}</p>
            <p><strong>Costo:</strong> ${{ number_format($order->total_cost ?? 0, 2) }}</p>
        </div>
        @endforeach
        @endif

        <!-- Mantenimientos -->
        @if($vehicleData['vehicle']->maintenances->isNotEmpty())
        <h3>Mantenimientos Realizados</h3>
        <table>
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
                    <td>{{ Str::limit($maintenance->description, 30) }}</td>
                    <td>{{ $maintenance->mechanic?->name ?? 'N/A' }}</td>
                    <td>${{ number_format($maintenance->cost, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif

        <!-- Materiales -->
        @if($vehicleData['vehicle']->serviceOrders->sum(fn($order) => $order->stockMovements->count()) > 0)
        <h3>Materiales y Repuestos Utilizados</h3>
        <table>
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
        @endif

        <!-- Fotografías -->
        @if($vehicleData['vehicle']->serviceOrders->sum(fn($order) => $order->photos->count()) > 0)
        <h3>Evidencia Fotográfica</h3>
        <div class="photo-grid">
            @foreach($vehicleData['vehicle']->serviceOrders as $order)
                @foreach($order->photos as $photo)
                <div class="photo-item">
                    @include('admin.reports.pdf.partials.photo', ['photo' => $photo, 'maxWidth' => 90, 'maxHeight' => 65])
                    <div class="photo-info">
                        <div>{{ $photo->type_label }}</div>
                        <div>{{ $photo->created_at->format('d/m/Y') }}</div>
                    </div>
                </div>
                @endforeach
            @endforeach
        </div>
        @endif

        <!-- Próximos Mantenimientos -->
        @if($vehicleData['vehicle']->maintenanceSchedules->isNotEmpty())
        <h3>Próximos Mantenimientos</h3>
        <table>
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
        @endif

        <!-- Alertas -->
        @if($vehicleData['vehicle']->alerts->isNotEmpty())
        <h3>Alertas</h3>
        @foreach($vehicleData['vehicle']->alerts as $alert)
        <div style="border: 1px solid #e2e8f0; border-radius: 3px; padding: 4px; margin-bottom: 4px;">
            <p><strong>{{ $alert->title }}</strong> - {{ $alert->message }}</p>
            <small>{{ $alert->due_date?->format('d/m/Y') }} | {{ ucfirst($alert->severity) }}</small>
        </div>
        @endforeach
        @endif

        <div class="vehicle-footer">
            FIN DEL EXPEDIENTE DEL VEHÍCULO {{ $vehicleData['vehicle']->plate }}
        </div>
    </div>
    @endforeach

    <div class="footer">
        <p>AutoGest - Sistema de Gestión de Mantenimiento Vehicular</p>
        <p>Generado el {{ $generatedAt }} por {{ $user->name }}</p>
    </div>
</body>
</html>