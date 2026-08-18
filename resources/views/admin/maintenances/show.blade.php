@extends('layouts.admin')

@section('title', 'Detalles de mantenimiento')
@section('heading', 'Detalles del mantenimiento')

@section('top-actions')
    <a href="{{ route('maintenances.index') }}" class="btn btn-secondary">← Volver</a>
    <a href="{{ route('maintenances.edit', $maintenance) }}" class="btn btn-primary">Editar</a>
@endsection

@section('content')
    <div class="panel">
        <div class="form-grid">
            <div class="field">
                <label>Vehículo</label>
                <div>{{ $maintenance->vehicle->plate }} — {{ $maintenance->vehicle->brand }} {{ $maintenance->vehicle->model }}</div>
            </div>

            <div class="field">
                <label>Fecha de servicio</label>
                <div>{{ $maintenance->performed_at?->format('d/m/Y') ?? 'No especificada' }}</div>
            </div>

            <div class="field">
                <label>Tipo</label>
                <div>{{ $maintenance->typeLabel() }}</div>
            </div>

            <div class="field">
                <label>Estado</label>
                <div><span class="badge {{ $maintenance->statusBadgeClass() }}">{{ $maintenance->statusLabel() }}</span></div>
            </div>

            <div class="field">
                <label>Kilometraje</label>
                <div>{{ $maintenance->mileage_at_service ? number_format($maintenance->mileage_at_service).' km' : 'No especificado' }}</div>
            </div>

            <div class="field">
                <label>Costo</label>
                <div>${{ number_format($maintenance->cost, 2) }}</div>
            </div>

            <div class="field" style="grid-column:1/-1;">
                <label>Descripción</label>
                <div>{{ $maintenance->description }}</div>
            </div>

            @if ($maintenance->service_order_id)
                <div class="field" style="grid-column:1/-1;">
                    <label>Orden de servicio relacionada</label>
                    <div>
                        <a href="{{ route('admin.orders.show', $maintenance->service_order_id) }}" class="btn btn-secondary btn-sm">
                            Ver orden #{{ $maintenance->service_order_id }}
                        </a>
                    </div>
                </div>
            @endif

            @if ($maintenance->technical_notes)
                <div class="field" style="grid-column:1/-1;">
                    <label>Notas técnicas</label>
                    <div>{{ $maintenance->technical_notes }}</div>
                </div>
            @endif

            @if ($maintenance->parts_used)
                <div class="field" style="grid-column:1/-1;">
                    <label>Repuestos utilizados</label>
                    <div>{{ $maintenance->parts_used }}</div>
                </div>
            @endif

            <div class="field">
                <label>Nivel de combustible</label>
                <div>{{ $maintenance->fuel_level ?? 'No especificado' }}</div>
            </div>

            <div class="field">
                <label>Rueda de repuesto</label>
                <div>{{ $maintenance->inventory_spare_wheel ? 'Sí' : 'No' }}</div>
            </div>

            <div class="field">
                <label>Herramientas</label>
                <div>{{ $maintenance->inventory_tools ? 'Sí' : 'No' }}</div>
            </div>

            <div class="field">
                <label>Radio</label>
                <div>{{ $maintenance->inventory_radio ? 'Sí' : 'No' }}</div>
            </div>

            <div class="field">
                <label>Documentos</label>
                <div>{{ $maintenance->inventory_documents ? 'Sí' : 'No' }}</div>
            </div>

            <div class="field">
                <label>Costo de repuestos</label>
                <div>${{ number_format($maintenance->parts_cost ?? 0, 2) }}</div>
            </div>

            <div class="field">
                <label>Costo de mano de obra</label>
                <div>${{ number_format($maintenance->labor_cost ?? 0, 2) }}</div>
            </div>

            @if ($maintenance->mechanic_id)
                <div class="field">
                    <label>Mecánico</label>
                    <div>{{ $maintenance->mechanic->name }}</div>
                </div>
            @endif
        </div>

        @if ($maintenance->service_order_id && $maintenance->serviceOrder->photos->count() > 0)
            <div style="margin-top:24px;">
                <h3 style="margin:0 0 12px;">Registro fotográfico</h3>
                <div class="photo-gallery">
                    @foreach ($maintenance->serviceOrder->photos as $photo)
                        <div class="photo-item">
                            <img src="{{ Storage::url($photo->photo_path) }}" alt="{{ $photo->description ?? 'Foto' }}" onclick="window.open('{{ Storage::url($photo->photo_path) }}', '_blank')">
                            <div class="photo-info">
                                <span class="photo-type">{{ $photo->type_label }}</span>
                                @if ($photo->description)
                                    <span class="photo-desc">{{ $photo->description }}</span>
                                @endif
                                <span class="photo-user">{{ $photo->user->name }} - {{ $photo->created_at->format('d/m/Y H:i') }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
@endsection

@push('styles')
<style>
    .photo-gallery {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
        gap: 12px;
        margin-top: 12px;
    }

    .photo-item {
        position: relative;
        border: 1px solid var(--border);
        border-radius: 8px;
        overflow: hidden;
        aspect-ratio: 1;
    }

    .photo-item img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        cursor: pointer;
    }

    .photo-info {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        background: rgba(0,0,0,0.7);
        color: white;
        padding: 8px;
        font-size: 0.75rem;
        display: none;
    }

    .photo-item:hover .photo-info {
        display: block;
    }

    .photo-type {
        font-weight: bold;
        display: block;
    }

    .photo-desc {
        display: block;
        margin-top: 2px;
    }

    .photo-user {
        display: block;
        margin-top: 2px;
        opacity: 0.8;
        font-size: 0.7rem;
    }
</style>
@endpush
