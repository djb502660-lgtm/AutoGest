<?php

namespace App\Services;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Maintenance;
use App\Models\MaintenanceSchedule;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Vehicle;

class ReportService
{
    private $auditService;

    public function __construct(AuditService $auditService)
    {
        $this->auditService = $auditService;
    }

    public function getMaintenanceReport(?int $vehicleId = null, ?string $from = null, ?string $to = null): array
    {
        $query = Maintenance::with(['vehicle', 'mechanic'])
            ->when($vehicleId, fn ($q) => $q->where('vehicle_id', $vehicleId))
            ->when($from, fn ($q) => $q->whereDate('performed_at', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('performed_at', '<=', $to))
            ->orderByDesc('performed_at');

        $items = $query->get();

        return [
            'title' => 'Reporte de mantenimientos',
            'summary' => [
                'Total registros' => $items->count(),
                'Completados' => $items->where('status', 'completado')->count(),
                'Costo total' => '$'.number_format($items->sum('cost'), 2),
            ],
            'columns' => ['Fecha', 'Vehículo', 'Tipo', 'Descripción', 'Mecánico', 'Costo', 'Estado'],
            'rows' => $items->map(function ($m) {
                return [
                    $m->performed_at?->format('Y-m-d') ?? 'N/A',
                    $m->vehicle->plate.' — '.$m->vehicle->brand.' '.$m->vehicle->model,
                    $m->typeLabel(),
                    $m->description,
                    $m->mechanic->name ?? 'N/A',
                    '$'.number_format($m->cost, 2),
                    $m->statusLabel(),
                ];
            })->toArray(),
        ];
    }

    public function getExpensesReport(?int $vehicleId = null, ?string $from = null, ?string $to = null): array
    {
        $query = Maintenance::with(['vehicle', 'serviceOrder'])
            ->where('status', 'completado')
            ->when($vehicleId, fn ($q) => $q->where('vehicle_id', $vehicleId))
            ->when($from, fn ($q) => $q->whereDate('performed_at', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('performed_at', '<=', $to))
            ->orderByDesc('performed_at');

        $items = $query->get();

        return [
            'title' => 'Reporte de gastos',
            'summary' => [
                'Total registros' => $items->count(),
                'Total gastos' => '$'.number_format($items->sum('cost'), 2),
                'Gasto promedio' => '$'.number_format($items->avg('cost'), 2),
            ],
            'columns' => ['Fecha', 'Vehículo', 'Descripción', 'Orden de servicio', 'Costo'],
            'rows' => $items->map(function ($m) {
                return [
                    $m->performed_at?->format('Y-m-d') ?? 'N/A',
                    $m->vehicle->plate.' — '.$m->vehicle->brand.' '.$m->vehicle->model,
                    $m->description,
                    $m->serviceOrder->reference ?? 'N/A',
                    '$'.number_format($m->cost, 2),
                ];
            })->toArray(),
        ];
    }

    public function getVehiclesReport(?int $vehicleId = null): array
    {
        $query = Vehicle::orderBy('plate')
            ->when($vehicleId, fn ($q) => $q->where('id', $vehicleId));

        $items = $query->get();

        return [
            'title' => 'Reporte de vehículos',
            'summary' => [
                'Total vehículos' => $items->count(),
                'Activos' => $items->where('status', 'activo')->count(),
                'Kilometraje promedio' => number_format($items->avg('mileage')).' km',
            ],
            'columns' => ['Placa', 'Vehículo', 'Año', 'Kilometraje', 'Estado', 'Cliente'],
            'rows' => $items->map(function ($v) {
                return [
                    $v->plate,
                    $v->brand.' '.$v->model,
                    $v->year,
                    number_format($v->mileage).' km',
                    $v->statusLabel(),
                    $v->client->name ?? 'N/A',
                ];
            })->toArray(),
        ];
    }

    public function getPendingReport(?int $vehicleId = null): array
    {
        $query = MaintenanceSchedule::with('vehicle')
            ->whereIn('status', ['programado', 'vencido'])
            ->when($vehicleId, fn ($q) => $q->where('vehicle_id', $vehicleId))
            ->orderBy('scheduled_date');

        $items = $query->get();

        return [
            'title' => 'Reporte de pendientes',
            'summary' => [
                'Total pendientes' => $items->count(),
                'Programados' => $items->where('status', 'programado')->count(),
                'Vencidos' => $items->where('status', 'vencido')->count(),
            ],
            'columns' => ['ID', 'Vehículo', 'Título', 'Prioridad', 'Fecha programada', 'Estado'],
            'rows' => $items->map(function ($s) {
                return [
                    $s->id,
                    $s->vehicle->plate.' — '.$s->vehicle->brand,
                    $s->title,
                    'Media',
                    $s->scheduled_date?->format('Y-m-d') ?? 'N/A',
                    $s->statusLabel(),
                ];
            })->toArray(),
        ];
    }

    public function getFiltersLabel(array $filters): string
    {
        $types = [
            'inventario' => 'Inventario',
            'productos' => 'Productos',
            'movimientos' => 'Movimientos de Stock',
            'categorias' => 'Categorías y Marcas',
        ];

        $parts = ['Tipo: '.($types[$filters['type']] ?? $filters['type'])];

        if (! empty($filters['category_id'])) {
            $category = Category::find($filters['category_id']);
            $parts[] = 'Categoría: '.($category?->name ?? $filters['category_id']);
        }

        if (! empty($filters['brand_id'])) {
            $brand = Brand::find($filters['brand_id']);
            $parts[] = 'Marca: '.($brand?->name ?? $filters['brand_id']);
        }

        if (! empty($filters['stock_status']) && $filters['stock_status'] !== 'all') {
            $statusLabels = ['low' => 'Stock bajo', 'out' => 'Sin stock'];
            $parts[] = 'Estado: '.($statusLabels[$filters['stock_status']] ?? $filters['stock_status']);
        }

        if (! empty($filters['from'])) {
            $parts[] = 'Desde: '.date('d/m/Y', strtotime($filters['from']));
        }

        if (! empty($filters['to'])) {
            $parts[] = 'Hasta: '.date('d/m/Y', strtotime($filters['to']));
        }

        return implode(' · ', $parts);
    }

    public function buildReportData(array $validated): array
    {
        $type = $validated['type'];
        $categoryId = $validated['category_id'] ?? null;
        $brandId = $validated['brand_id'] ?? null;
        $stockStatus = $validated['stock_status'] ?? null;
        $from = $validated['from'] ?? null;
        $to = $validated['to'] ?? null;

        // Los expedientes de vehículos usan sus propios métodos
        if (in_array($type, ['vehiculo_detalle', 'vehiculo_general'])) {
            throw new \InvalidArgumentException("Tipo de expediente '{$type}' debe usar métodos específicos (getVehicleDetailReport o getVehicleFleetReport)");
        }

        $data = match ($type) {
            'mantenimientos' => $this->getMaintenanceReport($validated['vehicle_id'] ?? null, $from, $to),
            'gastos' => $this->getExpensesReport($validated['vehicle_id'] ?? null, $from, $to),
            'vehiculos' => $this->getVehiclesReport($validated['vehicle_id'] ?? null),
            'pendientes' => $this->getPendingReport($validated['vehicle_id'] ?? null),
            'inventario' => $this->getInventoryReport($categoryId, $brandId, $stockStatus),
            'productos' => $this->getProductsReport($categoryId, $brandId),
            'movimientos' => $this->getStockMovementsReport($categoryId, $from, $to),
            'categorias' => $this->getCategoriesReport(),
        };

        $data['type'] = $type;
        $data['filters_label'] = $this->getFiltersLabel($validated);

        $this->auditService->logReportAction(
            'report_generated',
            "Expediente de tipo {$type} generado con filtros: {$data['filters_label']}",
            auth()->id(),
            null,
            ['type' => $type, 'filters' => $validated]
        );

        return $data;
    }

    /**
     * Obtiene el expediente completo de un vehículo específico
     */
    public function getVehicleDetailReport(int $vehicleId): array
    {
        $vehicle = Vehicle::with([
            'client',
            'serviceOrders' => function ($query) {
                $query->with([
                    'mechanic',
                    'advisor',
                    'creator',
                    'maintenances' => function ($mQuery) {
                        $mQuery->with('mechanic');
                    },
                    'comments.user',
                    'photos.user',
                    'stockMovements' => function ($smQuery) {
                        $smQuery->with('product');
                    },
                ])->orderByDesc('created_at');
            },
            'maintenances' => function ($query) {
                $query->with(['mechanic', 'serviceOrder'])
                    ->orderByDesc('performed_at');
            },
            'maintenanceSchedules' => function ($query) {
                $query->with('assignedMechanic')->orderBy('scheduled_date');
            },
            'alerts' => function ($query) {
                $query->with('user')->orderByDesc('created_at');
            },
            'appointmentRequests' => function ($query) {
                $query->with(['advisor', 'serviceOrder'])->orderByDesc('requested_date');
            },
        ])->findOrFail($vehicleId);

        return [
            'title' => 'Expediente Completo del Vehículo',
            'vehicle' => $vehicle,
            'summary' => $this->buildVehicleSummary($vehicle),
        ];
    }

    /**
     * Obtiene el expediente completo de toda la flota con filtros
     */
    public function getVehicleFleetReport(array $filters = []): array
    {
        $query = Vehicle::with([
            'client',
            'serviceOrders' => function ($query) {
                $query->with([
                    'mechanic',
                    'advisor',
                    'creator',
                    'maintenances' => function ($mQuery) {
                        $mQuery->with('mechanic');
                    },
                    'comments.user',
                    'photos.user',
                    'stockMovements' => function ($smQuery) {
                        $smQuery->with('product');
                    },
                ])->orderByDesc('created_at');
            },
            'maintenances' => function ($query) {
                $query->with(['mechanic', 'serviceOrder'])
                    ->orderByDesc('performed_at');
            },
            'maintenanceSchedules' => function ($query) {
                $query->with('assignedMechanic')->orderBy('scheduled_date');
            },
            'alerts' => function ($query) {
                $query->with('user')->orderByDesc('created_at');
            },
            'appointmentRequests' => function ($query) {
                $query->with(['advisor', 'serviceOrder'])->orderByDesc('requested_date');
            },
        ]);

        // Aplicar filtros
        $query->when($filters['client_id'] ?? null, fn ($q) => $q->where('client_id', $filters['client_id']))
            ->when($filters['status'] ?? null, fn ($q) => $q->where('status', $filters['status']));

        $vehicles = $query->orderBy('plate')->get();

        $vehiclesData = $vehicles->map(function ($vehicle) {
            return [
                'vehicle' => $vehicle,
                'summary' => $this->buildVehicleSummary($vehicle),
            ];
        })->toArray();

        return [
            'title' => 'Expediente Completo de la Flota',
            'vehicles' => $vehiclesData,
            'fleet_summary' => [
                'total_vehicles' => $vehicles->count(),
                'active_vehicles' => $vehicles->where('status', 'activo')->count(),
                'total_orders' => $vehicles->sum(fn ($v) => $v->serviceOrders->count()),
                'total_maintenances' => $vehicles->sum(fn ($v) => $v->maintenances->count()),
            ],
            'filters_applied' => $filters,
        ];
    }

    /**
     * Construye el resumen de un vehículo
     */
    private function buildVehicleSummary(Vehicle $vehicle): array
    {
        $totalStockMovements = $vehicle->serviceOrders->sum(fn ($order) => $order->stockMovements->count());
        $totalPartsCost = $vehicle->serviceOrders->sum(fn ($order) => $order->stockMovements->sum('quantity'));

        return [
            'total_maintenances' => $vehicle->maintenances->count(),
            'total_orders' => $vehicle->serviceOrders->count(),
            'total_schedules' => $vehicle->maintenanceSchedules->count(),
            'total_alerts' => $vehicle->alerts->count(),
            'total_appointments' => $vehicle->appointmentRequests->count(),
            'total_photos' => $vehicle->serviceOrders->sum(fn ($order) => $order->photos->count()),
            'total_comments' => $vehicle->serviceOrders->sum(fn ($order) => $order->comments->count()),
            'total_stock_movements' => $totalStockMovements,
            'total_cost' => $vehicle->maintenances->sum('cost'),
            'total_labor_cost' => $vehicle->maintenances->sum('labor_cost'),
            'total_parts_cost' => $vehicle->maintenances->sum('parts_cost'),
            'last_maintenance' => $vehicle->maintenances->first()?->performed_at?->format('d/m/Y') ?? 'N/A',
            'next_maintenance' => $vehicle->maintenanceSchedules
                ->whereIn('status', ['programado', 'vencido'])
                ->first()?->scheduled_date?->format('d/m/Y') ?? 'N/A',
            'current_status' => $vehicle->statusLabel(),
        ];
    }

    /**
     * Mejora el reporte de mantenimientos con más filtros
     */
    public function getMaintenanceReportEnhanced(array $filters): array
    {
        $query = Maintenance::with(['vehicle.client', 'mechanic', 'serviceOrder'])
            ->when($filters['vehicle_id'] ?? null, fn ($q) => $q->where('vehicle_id', $filters['vehicle_id']))
            ->when($filters['client_id'] ?? null, fn ($q) => $q->whereHas('vehicle', fn ($q) => $q->where('client_id', $filters['client_id'])))
            ->when($filters['from'] ?? null, fn ($q) => $q->whereDate('performed_at', '>=', $filters['from']))
            ->when($filters['to'] ?? null, fn ($q) => $q->whereDate('performed_at', '<=', $filters['to']))
            ->when($filters['status'] ?? null, fn ($q) => $q->where('status', $filters['status']))
            ->when($filters['mechanic_id'] ?? null, fn ($q) => $q->where('mechanic_id', $filters['mechanic_id']))
            ->when($filters['maintenance_type'] ?? null, fn ($q) => $q->where('type', $filters['maintenance_type']))
            ->orderByDesc('performed_at');

        $items = $query->get();

        return [
            'title' => 'Reporte de mantenimientos',
            'summary' => [
                'Total registros' => $items->count(),
                'Completados' => $items->where('status', 'completado')->count(),
                'Costo total' => '$'.number_format($items->sum('cost'), 2),
            ],
            'columns' => ['Fecha', 'Vehículo', 'Cliente', 'Tipo', 'Descripción', 'Mecánico', 'Costo', 'Estado'],
            'rows' => $items->map(function ($m) {
                return [
                    $m->performed_at?->format('Y-m-d') ?? 'N/A',
                    $m->vehicle->plate.' — '.$m->vehicle->brand.' '.$m->vehicle->model,
                    $m->vehicle->client->name ?? 'N/A',
                    $m->typeLabel(),
                    $m->description,
                    $m->mechanic->name ?? 'N/A',
                    '$'.number_format($m->cost, 2),
                    $m->statusLabel(),
                ];
            })->toArray(),
        ];
    }

    /**
     * Mejora el reporte de gastos con más detalles
     */
    public function getExpensesReportEnhanced(array $filters): array
    {
        $query = Maintenance::with(['vehicle.client', 'serviceOrder'])
            ->where('status', 'completado')
            ->when($filters['vehicle_id'] ?? null, fn ($q) => $q->where('vehicle_id', $filters['vehicle_id']))
            ->when($filters['client_id'] ?? null, fn ($q) => $q->whereHas('vehicle', fn ($q) => $q->where('client_id', $filters['client_id'])))
            ->when($filters['from'] ?? null, fn ($q) => $q->whereDate('performed_at', '>=', $filters['from']))
            ->when($filters['to'] ?? null, fn ($q) => $q->whereDate('performed_at', '<=', $filters['to']))
            ->orderByDesc('performed_at');

        $items = $query->get();

        return [
            'title' => 'Reporte de gastos',
            'summary' => [
                'Total registros' => $items->count(),
                'Total gastos' => '$'.number_format($items->sum('cost'), 2),
                'Gasto promedio' => '$'.number_format($items->avg('cost'), 2),
            ],
            'columns' => ['Fecha', 'Vehículo', 'Cliente', 'Descripción', 'Orden de servicio', 'Costo'],
            'rows' => $items->map(function ($m) {
                return [
                    $m->performed_at?->format('Y-m-d') ?? 'N/A',
                    $m->vehicle->plate.' — '.$m->vehicle->brand.' '.$m->vehicle->model,
                    $m->vehicle->client->name ?? 'N/A',
                    $m->description,
                    $m->serviceOrder->order_number ?? 'N/A',
                    '$'.number_format($m->cost, 2),
                ];
            })->toArray(),
        ];
    }

    /**
     * Reporte de inventario general
     */
    public function getInventoryReport(?int $categoryId = null, ?int $brandId = null, ?string $stockStatus = null): array
    {
        $query = Product::with(['category', 'brand'])
            ->when($categoryId, fn ($q) => $q->where('category_id', $categoryId))
            ->when($brandId, fn ($q) => $q->where('brand_id', $brandId))
            ->when($stockStatus === 'low', fn ($q) => $q->whereColumn('stock_quantity', '<=', 'min_stock'))
            ->when($stockStatus === 'out', fn ($q) => $q->where('stock_quantity', '<=', 0))
            ->orderBy('name');

        $items = $query->get();

        return [
            'title' => 'Reporte de Inventario General',
            'summary' => [
                'Total productos' => $items->count(),
                'Valor total' => '$'.number_format($items->sum(fn ($p) => $p->stock_quantity * $p->purchase_price), 2),
                'Stock bajo' => $items->filter(fn ($p) => $p->stock_quantity > 0 && $p->stock_quantity <= $p->min_stock)->count(),
                'Sin stock' => $items->where('stock_quantity', '<=', 0)->count(),
            ],
            'columns' => ['SKU', 'Nombre', 'Categoría', 'Marca', 'Stock', 'Stock Mín', 'Precio Compra', 'Estado'],
            'rows' => $items->map(function ($p) {
                $status = $p->stock_quantity <= 0 ? 'Sin stock' : ($p->stock_quantity <= $p->min_stock ? 'Stock bajo' : 'OK');

                return [
                    $p->sku ?? 'N/A',
                    $p->name,
                    $p->category->name ?? 'N/A',
                    $p->brand->name ?? 'N/A',
                    $p->stock_quantity,
                    $p->min_stock,
                    '$'.number_format($p->purchase_price, 2),
                    $status,
                ];
            })->toArray(),
        ];
    }

    /**
     * Reporte de productos
     */
    public function getProductsReport(?int $categoryId = null, ?int $brandId = null): array
    {
        $query = Product::with(['category', 'brand'])
            ->when($categoryId, fn ($q) => $q->where('category_id', $categoryId))
            ->when($brandId, fn ($q) => $q->where('brand_id', $brandId))
            ->orderBy('name');

        $items = $query->get();

        return [
            'title' => 'Reporte de Productos',
            'summary' => [
                'Total productos' => $items->count(),
                'Categorías' => $items->pluck('category.name')->unique()->count(),
                'Marcas' => $items->pluck('brand.name')->unique()->count(),
            ],
            'columns' => ['SKU', 'Nombre', 'Marca', 'Categoría', 'Stock', 'Precio Compra', 'Precio Venta', 'Unidad'],
            'rows' => $items->map(function ($p) {
                return [
                    $p->sku ?? 'N/A',
                    $p->name,
                    $p->brand->name ?? 'N/A',
                    $p->category->name ?? 'N/A',
                    $p->stock_quantity,
                    '$'.number_format($p->purchase_price, 2),
                    '$'.number_format($p->sale_price, 2),
                    $p->unit ?? 'N/A',
                ];
            })->toArray(),
        ];
    }

    /**
     * Reporte de movimientos de stock
     */
    public function getStockMovementsReport(?int $categoryId = null, ?string $from = null, ?string $to = null): array
    {
        $query = StockMovement::with(['product', 'product.category', 'movementable'])
            ->when($categoryId, fn ($q) => $q->whereHas('product', fn ($q) => $q->where('category_id', $categoryId)))
            ->when($from, fn ($q) => $q->whereDate('created_at', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('created_at', '<=', $to))
            ->orderByDesc('created_at');

        $items = $query->get();

        return [
            'title' => 'Reporte de Movimientos de Stock',
            'summary' => [
                'Total movimientos' => $items->count(),
                'Entradas' => $items->where('type', 'in')->count(),
                'Salidas' => $items->where('type', 'out')->count(),
            ],
            'columns' => ['Fecha', 'Producto', 'Categoría', 'Tipo', 'Cantidad', 'Stock Anterior', 'Stock Nuevo', 'Referencia'],
            'rows' => $items->map(function ($m) {
                $reference = $m->movementable_type
                    ? class_basename($m->movementable_type).' #'.$m->movementable_id
                    : 'Manual';

                return [
                    $m->created_at->format('Y-m-d H:i'),
                    $m->product->name,
                    $m->product->category->name ?? 'N/A',
                    $m->type === 'in' ? 'Entrada' : 'Salida',
                    $m->quantity,
                    $m->previous_stock,
                    $m->new_stock,
                    $reference,
                ];
            })->toArray(),
        ];
    }

    /**
     * Reporte de categorías y marcas
     */
    public function getCategoriesReport(): array
    {
        $categories = Category::with(['products', 'products.brand'])
            ->withCount('products')
            ->orderBy('name')
            ->get();

        return [
            'title' => 'Reporte de Categorías y Marcas',
            'summary' => [
                'Total categorías' => $categories->count(),
                'Total productos' => $categories->sum('products_count'),
            ],
            'columns' => ['Categoría', 'Productos', 'Marcas', 'Valor Total'],
            'rows' => $categories->map(function ($c) {
                $brands = $c->products->pluck('brand.name')->filter()->unique()->count();
                $totalValue = $c->products->sum(fn ($p) => $p->stock_quantity * $p->purchase_price);

                return [
                    $c->name,
                    $c->products_count,
                    $brands,
                    '$'.number_format($totalValue, 2),
                ];
            })->toArray(),
        ];
    }
}
