<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Expediente del Vehículo {{ $report['vehicle']->plate }}</title>
    <style>
        body { 
            font-family: DejaVu Sans, sans-serif; 
            font-size: 10px; 
            color: #1e293b; 
            line-height: 1.4;
        }
        
        .header { 
            border-bottom: 2px solid #0284c7; 
            padding-bottom: 12px; 
            margin-bottom: 16px; 
        }
        
        .brand { 
            font-size: 16px; 
            font-weight: bold; 
            color: #0284c7; 
            margin: 0; 
        }
        
        .meta { 
            font-size: 9px; 
            color: #64748b; 
            margin-top: 4px; 
        }
        
        h1 { 
            font-size: 14px; 
            margin: 8px 0 4px; 
            color: #0f172a;
        }
        
        h2 { 
            font-size: 12px; 
            margin: 12px 0 8px; 
            color: #0284c7;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 4px;
        }
        
        h3 { 
            font-size: 11px; 
            margin: 8px 0 4px; 
            color: #334155;
        }
        
        .section {
            margin-bottom: 16px;
            page-break-inside: avoid;
        }
        
        .grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }
        
        .info-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            padding: 8px;
            margin-bottom: 8px;
        }
        
        .info-box p {
            margin: 2px 0;
            font-size: 9px;
        }
        
        .info-box strong {
            color: #475569;
        }
        
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 8px;
            margin-bottom: 12px;
        }
        
        .summary-box {
            background: #f0f9ff;
            border: 1px solid #bae6fd;
            border-radius: 4px;
            padding: 8px;
            text-align: center;
        }
        
        .summary-box span {
            display: block;
            font-size: 8px;
            color: #64748b;
            text-transform: uppercase;
        }
        
        .summary-box strong {
            display: block;
            font-size: 14px;
            margin-top: 2px;
            color: #0284c7;
        }
        
        table { 
            width: 100%; 
            border-collapse: collapse; 
            font-size: 9px;
            margin-bottom: 12px;
        }
        
        table th { 
            background: #0284c7; 
            color: #fff; 
            padding: 6px 4px; 
            text-align: left; 
            font-size: 8px;
            font-weight: 600;
        }
        
        table td { 
            border: 1px solid #e2e8f0; 
            padding: 4px; 
            vertical-align: top;
        }
        
        table tr:nth-child(even) td { 
            background: #f8fafc; 
        }
        
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: 600;
        }
        
        .badge-green { background: #dcfce7; color: #166534; }
        .badge-yellow { background: #fef9c3; color: #854d0e; }
        .badge-red { background: #fee2e2; color: #991b1b; }
        .badge-blue { background: #dbeafe; color: #1e40af; }
        
        .order-item, .maintenance-item {
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            padding: 8px;
            margin-bottom: 8px;
            page-break-inside: avoid;
        }
        
        .photo-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 8px;
            margin-top: 8px;
        }
        
        .photo-item {
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            overflow: hidden;
            text-align: center;
            padding: 4px;
        }
        
        .photo-item img {
            display: block;
            margin: 0 auto;
        }
        
        .photo-info {
            padding: 4px;
            background: #f8fafc;
            border-top: 1px solid #e2e8f0;
            font-size: 7px;
        }
        
        .footer { 
            margin-top: 20px; 
            font-size: 8px; 
            color: #94a3b8; 
            text-align: center; 
            border-top: 1px solid #e2e8f0;
            padding-top: 8px;
        }
        
        .page-break {
            page-break-before: always;
        }
    </style>
</head>
<body>
    <div class="header">
        <p class="brand">AutoGest</p>
        <h1>Expediente Completo del Vehículo</h1>
        <p class="meta">Vehículo: {{ $report['vehicle']->plate }} | Generado: {{ $generatedAt }} | Usuario: {{ $user->name }}</p>
    </div>

    <!-- 1. Información del Vehículo -->
    <div class="section">
        <h2>1. Información del Vehículo</h2>
        <div class="grid-2">
            <div class="info-box">
                <p><strong>Placa:</strong> {{ $report['vehicle']->plate }}</p>
                <p><strong>Marca:</strong> {{ $report['vehicle']->brand }}</p>
                <p><strong>Modelo:</strong> {{ $report['vehicle']->model }}</p>
                <p><strong>Submodelo:</strong> {{ $report['vehicle']->sub_model ?? 'N/A' }}</p>
                <p><strong>Año:</strong> {{ $report['vehicle']->year }}</p>
                <p><strong>Color:</strong> {{ $report['vehicle']->color ?? 'N/A' }}</p>
            </div>
            <div class="info-box">
                <p><strong>VIN:</strong> {{ $report['vehicle']->vin ?? 'N/A' }}</p>
                <p><strong>Motor:</strong> {{ $report['vehicle']->engine_number ?? 'N/A' }}</p>
                <p><strong>Transmisión:</strong> {{ $report['vehicle']->transmission_type ?? 'N/A' }}</p>
                <p><strong>Kilometraje:</strong> {{ number_format($report['vehicle']->mileage) }} km</p>
                <p><strong>Estado:</strong> {{ $report['vehicle']->statusLabel() }}</p>
                <p><strong>Matrícula:</strong> {{ $report['vehicle']->registration_date?->format('d/m/Y') ?? 'N/A' }}</p>
            </div>
        </div>
    </div>

    <!-- 2. Información del Propietario -->
    <div class="section">
        <h2>2. Información del Propietario</h2>
        @if($report['vehicle']->client)
        <div class="info-box">
            <p><strong>Nombre:</strong> {{ $report['vehicle']->client->name }}</p>
            <p><strong>Email:</strong> {{ $report['vehicle']->client->email }}</p>
            <p><strong>Teléfono:</strong> {{ $report['vehicle']->client->phone ?? 'N/A' }}</p>
            <p><strong>Rol:</strong> {{ $report['vehicle']->client->role->label() }}</p>
        </div>
        @else
        <p style="color: #64748b;">Sin propietario asignado</p>
        @endif
    </div>

    <!-- 3. Resumen Histórico -->
    <div class="section">
        <h2>3. Resumen Histórico</h2>
        <div class="summary-grid">
            <div class="summary-box">
                <span>Mantenimientos</span>
                <strong>{{ $report['summary']['total_maintenances'] }}</strong>
            </div>
            <div class="summary-box">
                <span>Órdenes</span>
                <strong>{{ $report['summary']['total_orders'] }}</strong>
            </div>
            <div class="summary-box">
                <span>Citas</span>
                <strong>{{ $report['summary']['total_appointments'] }}</strong>
            </div>
            <div class="summary-box">
                <span>Alertas</span>
                <strong>{{ $report['summary']['total_alerts'] }}</strong>
            </div>
            <div class="summary-box">
                <span>Fotos</span>
                <strong>{{ $report['summary']['total_photos'] }}</strong>
            </div>
            <div class="summary-box">
                <span>Costo Total</span>
                <strong>${{ number_format($report['summary']['total_cost'], 2) }}</strong>
            </div>
            <div class="summary-box">
                <span>Último Mant.</span>
                <strong>{{ $report['summary']['last_maintenance'] }}</strong>
            </div>
            <div class="summary-box">
                <span>Próximo Mant.</span>
                <strong>{{ $report['summary']['next_maintenance'] }}</strong>
            </div>
        </div>
    </div>

    <!-- 4. Historial de Citas -->
    @if($report['vehicle']->appointmentRequests->isNotEmpty())
    <div class="section">
        <h2>4. Historial de Citas</h2>
        <table>
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Hora</th>
                    <th>Tipo</th>
                    <th>Estado</th>
                    <th>Asesor</th>
                    <th>Orden</th>
                </tr>
            </thead>
            <tbody>
                @foreach($report['vehicle']->appointmentRequests as $appointment)
                <tr>
                    <td>{{ $appointment->requested_date?->format('d/m/Y') ?? 'N/A' }}</td>
                    <td>{{ $appointment->requested_time ?? 'N/A' }}</td>
                    <td>{{ $appointment->service_type ?? 'N/A' }}</td>
                    <td>{{ $appointment->statusLabel() }}</td>
                    <td>{{ $appointment->advisor?->name ?? 'N/A' }}</td>
                    <td>{{ $appointment->serviceOrder?->order_number ?? 'N/A' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <!-- 5. Órdenes de Servicio -->
    @if($report['vehicle']->serviceOrders->isNotEmpty())
    <div class="section page-break">
        <h2>5. Órdenes de Servicio</h2>
        @foreach($report['vehicle']->serviceOrders as $order)
        <div class="order-item">
            <h3>Orden #{{ $order->order_number }}</h3>
            <div class="grid-2">
                <div>
                    <p><strong>Estado:</strong> {{ $order->statusLabel() }}</p>
                    <p><strong>Prioridad:</strong> {{ ucfirst($order->priority) }}</p>
                    <p><strong>Ingreso:</strong> {{ $order->created_at?->format('d/m/Y H:i') }}</p>
                    <p><strong>Salida:</strong> {{ $order->completed_at?->format('d/m/Y H:i') ?? 'N/A' }}</p>
                    <p><strong>Asesor:</strong> {{ $order->advisor?->name ?? 'N/A' }}</p>
                    <p><strong>Mecánico:</strong> {{ $order->mechanic?->name ?? 'Sin asignar' }}</p>
                </div>
                <div>
                    <p><strong>Descripción:</strong> {{ $order->description }}</p>
                    <p><strong>Diagnóstico:</strong> {{ $order->diagnosis ?? 'Sin diagnóstico' }}</p>
                    <p><strong>Recomendaciones:</strong> {{ $order->recommendations ?? 'Sin recomendaciones' }}</p>
                    <p><strong>Costo Estimado:</strong> ${{ number_format($order->estimated_cost ?? 0, 2) }}</p>
                    <p><strong>Costo Total:</strong> ${{ number_format($order->total_cost ?? 0, 2) }}</p>
                </div>
            </div>
            
            @if($order->comments->isNotEmpty())
            <div style="margin-top: 8px; padding: 6px; background: #f8fafc; border-radius: 4px;">
                <strong>Comentarios:</strong>
                @foreach($order->comments as $comment)
                <p style="margin: 2px 0; font-size: 8px;">
                    <strong>{{ $comment->user->name }}:</strong> {{ $comment->comment }}
                    <small>({{ $comment->created_at->format('d/m/Y H:i') }})</small>
                </p>
                @endforeach
            </div>
            @endif
        </div>
        @endforeach
    </div>
    @endif

    <!-- 6. Detalle de Mantenimientos -->
    @if($report['vehicle']->maintenances->isNotEmpty())
    <div class="section">
        <h2>6. Detalle de Mantenimientos</h2>
        @foreach($report['vehicle']->maintenances as $maintenance)
        <div class="maintenance-item">
            <h3>{{ $maintenance->typeLabel() }} - {{ $maintenance->performed_at?->format('d/m/Y') }}</h3>
            <div class="grid-2">
                <div>
                    <p><strong>Estado:</strong> {{ $maintenance->statusLabel() }}</p>
                    <p><strong>Kilometraje:</strong> {{ number_format($maintenance->mileage_at_service) }} km</p>
                    <p><strong>Mecánico:</strong> {{ $maintenance->mechanic?->name ?? 'N/A' }}</p>
                    <p><strong>Orden:</strong> {{ $maintenance->serviceOrder?->order_number ?? 'N/A' }}</p>
                </div>
                <div>
                    <p><strong>Descripción:</strong> {{ $maintenance->description }}</p>
                    <p><strong>Notas:</strong> {{ $maintenance->technical_notes ?? 'Sin notas' }}</p>
                    <p><strong>Repuestos:</strong> {{ $maintenance->parts_used ?? 'No especificados' }}</p>
                    <p><strong>Costo:</strong> ${{ number_format($maintenance->cost, 2) }}</p>
                    <p><strong>Repuestos:</strong> ${{ number_format($maintenance->parts_cost ?? 0, 2) }}</p>
                    <p><strong>Mano de obra:</strong> ${{ number_format($maintenance->labor_cost ?? 0, 2) }}</p>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @endif

    <!-- 7. Materiales y Repuestos -->
    @if($report['vehicle']->serviceOrders->sum(fn($order) => $order->stockMovements->count()) > 0)
    <div class="section">
        <h2>7. Materiales y Repuestos Utilizados</h2>
        <table>
            <thead>
                <tr>
                    <th>Orden</th>
                    <th>Producto</th>
                    <th>Cantidad</th>
                    <th>Stock Ant.</th>
                    <th>Stock Nuevo</th>
                    <th>Tipo</th>
                </tr>
            </thead>
            <tbody>
                @foreach($report['vehicle']->serviceOrders as $order)
                    @foreach($order->stockMovements as $movement)
                    <tr>
                        <td>{{ $order->order_number }}</td>
                        <td>{{ $movement->product?->name ?? 'N/A' }}</td>
                        <td>{{ $movement->quantity }}</td>
                        <td>{{ $movement->previous_stock }}</td>
                        <td>{{ $movement->new_stock }}</td>
                        <td>{{ ucfirst($movement->type) }}</td>
                    </tr>
                    @endforeach
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <!-- 8. Evidencia Fotográfica -->
    @if($report['vehicle']->serviceOrders->sum(fn($order) => $order->photos->count()) > 0)
    <div class="section page-break">
        <h2>8. Evidencia Fotográfica</h2>
        @foreach($report['vehicle']->serviceOrders as $order)
            @if($order->photos->isNotEmpty())
            <div style="margin-bottom: 16px;">
                <h3>Orden #{{ $order->order_number }}</h3>
                <div class="photo-grid">
                    @foreach($order->photos as $photo)
                    <div class="photo-item">
                        @include('admin.reports.pdf.partials.photo', ['photo' => $photo, 'maxWidth' => 110, 'maxHeight' => 80])
                        <div class="photo-info">
                            <div style="font-weight: 700;">{{ $photo->type_label }}</div>
                            @if($photo->description)
                            <div>{{ Str::limit($photo->description, 25) }}</div>
                            @endif
                            <div>{{ $photo->user->name }} · {{ $photo->created_at->format('d/m/Y') }}</div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        @endforeach
    </div>
    @else
    <div class="section">
        <h2>8. Evidencia Fotográfica</h2>
        <p style="color: #64748b;">Sin evidencia fotográfica registrada para este vehículo.</p>
    </div>
    @endif

    <!-- 9. Próximos Mantenimientos -->
    @if($report['vehicle']->maintenanceSchedules->isNotEmpty())
    <div class="section">
        <h2>9. Próximos Mantenimientos</h2>
        <table>
            <thead>
                <tr>
                    <th>Título</th>
                    <th>Tipo</th>
                    <th>Fecha</th>
                    <th>Horario</th>
                    <th>Km Objetivo</th>
                    <th>Mecánico</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                @foreach($report['vehicle']->maintenanceSchedules as $schedule)
                <tr>
                    <td>{{ $schedule->title }}</td>
                    <td>{{ $schedule->service_type ?? 'N/A' }}</td>
                    <td>{{ $schedule->scheduled_date?->format('d/m/Y') ?? 'N/A' }}</td>
                    <td>{{ $schedule->start_time }} - {{ $schedule->end_time }}</td>
                    <td>{{ number_format($schedule->mileage_target) }} km</td>
                    <td>{{ $schedule->assignedMechanic?->name ?? 'Sin asignar' }}</td>
                    <td>{{ $schedule->statusLabel() }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <!-- 10. Alertas -->
    @if($report['vehicle']->alerts->isNotEmpty())
    <div class="section">
        <h2>10. Alertas</h2>
        @foreach($report['vehicle']->alerts as $alert)
        <div style="border: 1px solid #e2e8f0; border-radius: 4px; padding: 6px; margin-bottom: 6px;">
            <p><strong>{{ $alert->title }}</strong> - {{ $alert->message }}</p>
            <p><small>Fecha: {{ $alert->due_date?->format('d/m/Y') }} | Severidad: {{ ucfirst($alert->severity) }} | {{ $alert->is_resolved ? 'Resuelta' : 'Pendiente' }}</small></p>
        </div>
        @endforeach
    </div>
    @endif

    <div class="footer">
        <p>AutoGest - Sistema de Gestión de Mantenimiento Vehicular</p>
        <p>Generado el {{ $generatedAt }} por {{ $user->name }} | Página 1 de 1</p>
    </div>
</body>
</html>