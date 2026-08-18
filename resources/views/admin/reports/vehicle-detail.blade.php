@extends('layouts.admin')

@section('title', 'Expediente del Vehículo ' . $report['vehicle']->plate)
@section('heading', 'Expediente Completo del Vehículo')
@section('subheading', 'Historial completo de servicios, mantenimientos y evidencias.')

@section('top-actions')
    <a href="{{ route('reports.index') }}" class="btn btn-secondary">← Volver a reportes</a>
    <a href="{{ route('reports.vehicle.detail.pdf', $report['vehicle']->id) }}" class="btn btn-primary">📄 Descargar PDF</a>
@endsection

@section('content')
    <!-- Información del Vehículo -->
    <div class="panel">
        <h3>1. Información del Vehículo</h3>
        <div class="grid-2">
            <div>
                <p><strong>Placa:</strong> {{ $report['vehicle']->plate }}</p>
                <p><strong>Marca:</strong> {{ $report['vehicle']->brand }}</p>
                <p><strong>Modelo:</strong> {{ $report['vehicle']->model }}</p>
                <p><strong>Submodelo:</strong> {{ $report['vehicle']->sub_model ?? 'N/A' }}</p>
                <p><strong>Año:</strong> {{ $report['vehicle']->year }}</p>
                <p><strong>Color:</strong> {{ $report['vehicle']->color ?? 'N/A' }}</p>
                <p><strong>VIN:</strong> {{ $report['vehicle']->vin ?? 'N/A' }}</p>
                <p><strong>Motor:</strong> {{ $report['vehicle']->engine_number ?? 'N/A' }}</p>
                <p><strong>Transmisión:</strong> {{ $report['vehicle']->transmission_type ?? 'N/A' }}</p>
                <p><strong>Kilometraje:</strong> {{ number_format($report['vehicle']->mileage) }} km</p>
                <p><strong>Estado:</strong> <span class="badge {{ $report['vehicle']->statusBadgeClass() }}">{{ $report['vehicle']->statusLabel() }}</span></p>
            </div>
            <div>
                <p><strong>Matrícula:</strong> {{ $report['vehicle']->registration_date?->format('d/m/Y') ?? 'N/A' }}</p>
                <p><strong>Revisión técnica:</strong> {{ $report['vehicle']->inspection_expiry?->format('d/m/Y') ?? 'N/A' }}</p>
                <p><strong>Seguro:</strong> {{ $report['vehicle']->insurance_expiry?->format('d/m/Y') ?? 'N/A' }}</p>
                <p><strong>Referencia pintura:</strong> {{ $report['vehicle']->paint_reference ?? 'N/A' }}</p>
                <p><strong>Transponder:</strong> {{ $report['vehicle']->transponder ?? 'N/A' }}</p>
                <p><strong>Código radio:</strong> {{ $report['vehicle']->radio_code ?? 'N/A' }}</p>
                <p><strong>Observaciones:</strong> {{ $report['vehicle']->notes ?? 'Sin observaciones' }}</p>
            </div>
        </div>
    </div>

    <!-- Información del Propietario -->
    <div class="panel">
        <h3>2. Información del Propietario</h3>
        @if($report['vehicle']->client)
            <p><strong>Nombre:</strong> {{ $report['vehicle']->client->name }}</p>
            <p><strong>Email:</strong> {{ $report['vehicle']->client->email }}</p>
            <p><strong>Teléfono:</strong> {{ $report['vehicle']->client->phone ?? 'N/A' }}</p>
            <p><strong>Rol:</strong> {{ $report['vehicle']->client->role->label() }}</p>
        @else
            <p class="text-muted">Sin propietario asignado</p>
        @endif
    </div>

    <!-- Resumen Histórico -->
    <div class="panel">
        <h3>3. Resumen Histórico</h3>
        <div class="summary-grid">
            <div class="summary-box">
                <span>Total Mantenimientos</span>
                <strong>{{ $report['summary']['total_maintenances'] }}</strong>
            </div>
            <div class="summary-box">
                <span>Total Órdenes</span>
                <strong>{{ $report['summary']['total_orders'] }}</strong>
            </div>
            <div class="summary-box">
                <span>Total Citas</span>
                <strong>{{ $report['summary']['total_appointments'] }}</strong>
            </div>
            <div class="summary-box">
                <span>Total Alertas</span>
                <strong>{{ $report['summary']['total_alerts'] }}</strong>
            </div>
            <div class="summary-box">
                <span>Total Fotos</span>
                <strong>{{ $report['summary']['total_photos'] }}</strong>
            </div>
            <div class="summary-box">
                <span>Total Costo</span>
                <strong>${{ number_format($report['summary']['total_cost'], 2) }}</strong>
            </div>
            <div class="summary-box">
                <span>Último Mantenimiento</span>
                <strong>{{ $report['summary']['last_maintenance'] }}</strong>
            </div>
            <div class="summary-box">
                <span>Próximo Mantenimiento</span>
                <strong>{{ $report['summary']['next_maintenance'] }}</strong>
            </div>
        </div>
    </div>

    <!-- Historial de Citas -->
    @if($report['vehicle']->appointmentRequests->isNotEmpty())
    <div class="panel">
        <h3>4. Historial de Citas</h3>
        <table class="table">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Hora</th>
                    <th>Tipo</th>
                    <th>Estado</th>
                    <th>Asesor</th>
                    <th>Orden Relacionada</th>
                    <th>Descripción</th>
                </tr>
            </thead>
            <tbody>
                @foreach($report['vehicle']->appointmentRequests as $appointment)
                <tr>
                    <td>{{ $appointment->requested_date?->format('d/m/Y') ?? 'N/A' }}</td>
                    <td>{{ $appointment->requested_time ?? 'N/A' }}</td>
                    <td>{{ $appointment->service_type ?? 'N/A' }}</td>
                    <td><span class="badge {{ $appointment->statusBadgeClass() }}">{{ $appointment->statusLabel() }}</span></td>
                    <td>{{ $appointment->advisor?->name ?? 'N/A' }}</td>
                    <td>{{ $appointment->serviceOrder?->order_number ?? 'N/A' }}</td>
                    <td>{{ Str::limit($appointment->description, 50) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <!-- Órdenes de Servicio -->
    @if($report['vehicle']->serviceOrders->isNotEmpty())
    <div class="panel">
        <h3>5. Órdenes de Servicio</h3>
        @foreach($report['vehicle']->serviceOrders as $order)
        <div class="order-item" style="border: 1px solid #e2e8f0; border-radius: 8px; padding: 16px; margin-bottom: 16px;">
            <h4>Orden #{{ $order->order_number }}</h4>
            <div class="grid-2">
                <div>
                    <p><strong>Estado:</strong> <span class="badge {{ $order->statusBadgeClass() }}">{{ $order->statusLabel() }}</span></p>
                    <p><strong>Prioridad:</strong> {{ ucfirst($order->priority) }}</p>
                    <p><strong>Fecha Ingreso:</strong> {{ $order->created_at?->format('d/m/Y H:i') }}</p>
                    <p><strong>Fecha Salida:</strong> {{ $order->completed_at?->format('d/m/Y H:i') ?? 'N/A' }}</p>
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
            
            <!-- Comentarios de la orden -->
            @if($order->comments->isNotEmpty())
            <div style="margin-top: 12px; padding: 12px; background: #f8fafc; border-radius: 6px;">
                <strong>Comentarios:</strong>
                @foreach($order->comments as $comment)
                <p style="margin: 4px 0; font-size: 0.85rem;">
                    <strong>{{ $comment->user->name }}:</strong> {{ $comment->comment }}
                    <small class="text-muted">({{ $comment->created_at->format('d/m/Y H:i') }})</small>
                </p>
                @endforeach
            </div>
            @endif
        </div>
        @endforeach
    </div>
    @endif

    <!-- Detalle de Mantenimientos -->
    @if($report['vehicle']->maintenances->isNotEmpty())
    <div class="panel">
        <h3>6. Detalle de Mantenimientos</h3>
        @foreach($report['vehicle']->maintenances as $maintenance)
        <div class="maintenance-item" style="border: 1px solid #e2e8f0; border-radius: 8px; padding: 16px; margin-bottom: 16px;">
            <h4>{{ $maintenance->typeLabel() }} - {{ $maintenance->performed_at?->format('d/m/Y') }}</h4>
            <div class="grid-2">
                <div>
                    <p><strong>Estado:</strong> <span class="badge {{ $maintenance->statusBadgeClass() }}">{{ $maintenance->statusLabel() }}</span></p>
                    <p><strong>Kilometraje:</strong> {{ number_format($maintenance->mileage_at_service) }} km</p>
                    <p><strong>Mecánico:</strong> {{ $maintenance->mechanic?->name ?? 'N/A' }}</p>
                    <p><strong>Orden Relacionada:</strong> {{ $maintenance->serviceOrder?->order_number ?? 'N/A' }}</p>
                </div>
                <div>
                    <p><strong>Descripción:</strong> {{ $maintenance->description }}</p>
                    <p><strong>Notas Técnicas:</strong> {{ $maintenance->technical_notes ?? 'Sin notas' }}</p>
                    <p><strong>Repuestos Usados:</strong> {{ $maintenance->parts_used ?? 'No especificados' }}</p>
                    <p><strong>Costo:</strong> ${{ number_format($maintenance->cost, 2) }}</p>
                    <p><strong>Costo Repuestos:</strong> ${{ number_format($maintenance->parts_cost ?? 0, 2) }}</p>
                    <p><strong>Costo Mano de Obra:</strong> ${{ number_format($maintenance->labor_cost ?? 0, 2) }}</p>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @endif

    <!-- Materiales y Repuestos -->
    @if($report['vehicle']->serviceOrders->sum(fn($order) => $order->stockMovements->count()) > 0)
    <div class="panel">
        <h3>7. Materiales y Repuestos Utilizados</h3>
        <table class="table">
            <thead>
                <tr>
                    <th>Orden</th>
                    <th>Producto</th>
                    <th>Cantidad</th>
                    <th>Stock Anterior</th>
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

    <!-- Evidencia Fotográfica -->
    @if($report['vehicle']->serviceOrders->sum(fn($order) => $order->photos->count()) > 0)
    <div class="panel">
        <h3>8. Evidencia Fotográfica</h3>
        @foreach($report['vehicle']->serviceOrders as $order)
            @if($order->photos->isNotEmpty())
            <div style="margin-bottom: 24px;">
                <h4>Orden #{{ $order->order_number }}</h4>
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 12px;">
                    @foreach($order->photos as $photo)
                    <div style="border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden;">
                        @include('layouts.partials.photo-thumb', [
                            'photo' => $photo,
                            'gallery' => 'order-'.$order->id,
                            'class' => 'photo-thumb-cover-sm',
                        ])
                        <div style="padding: 8px; background: #f8fafc; border-top: 1px solid #e2e8f0;">
                            <div style="font-size: 0.75rem; font-weight: 700; color: #64748b;">{{ $photo->type_label }}</div>
                            @if($photo->description)
                            <div style="font-size: 0.7rem; color: #475569; margin-top: 4px;">{{ Str::limit($photo->description, 30) }}</div>
                            @endif
                            <div style="font-size: 0.65rem; color: #94a3b8; margin-top: 4px;">{{ $photo->user->name }} · {{ $photo->created_at->format('d/m/Y') }}</div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        @endforeach
    </div>
    @else
    <div class="panel">
        <h3>8. Evidencia Fotográfica</h3>
        <p class="text-muted">Sin evidencia fotográfica registrada para este vehículo.</p>
    </div>
    @endif

    <!-- Próximos Mantenimientos -->
    @if($report['vehicle']->maintenanceSchedules->isNotEmpty())
    <div class="panel">
        <h3>9. Próximos Mantenimientos</h3>
        <table class="table">
            <thead>
                <tr>
                    <th>Título</th>
                    <th>Tipo</th>
                    <th>Fecha Programada</th>
                    <th>Horario</th>
                    <th>Kilometraje Objetivo</th>
                    <th>Mecánico Asignado</th>
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
                    <td><span class="badge {{ $schedule->colorClass() }}">{{ $schedule->statusLabel() }}</span></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <!-- Alertas -->
    @if($report['vehicle']->alerts->isNotEmpty())
    <div class="panel">
        <h3>10. Alertas</h3>
        @foreach($report['vehicle']->alerts as $alert)
        <div style="border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px; margin-bottom: 8px;">
            <p><strong>{{ $alert->title }}</strong> - {{ $alert->message }}</p>
            <p><small>Fecha: {{ $alert->due_date?->format('d/m/Y') }} | Severidad: {{ ucfirst($alert->severity) }} | Estado: {{ $alert->is_resolved ? 'Resuelta' : 'Pendiente' }}</small></p>
        </div>
        @endforeach
    </div>
    @endif

    <div class="panel" style="text-align: center; color: #64748b; font-size: 0.85rem;">
        <p>Reporte generado el {{ $generatedAt }} por {{ auth()->user()->name }}</p>
        <p>AutoGest - Sistema de Gestión de Mantenimiento Vehicular</p>
    </div>
@endsection
