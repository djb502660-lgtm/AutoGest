@extends('layouts.admin')

@section('title', 'Detalle de auditoría')
@section('heading', 'Detalle de evento')
@section('subheading', 'Información completa del registro de auditoría.')

@section('top-actions')
    <a href="{{ route('audit.index', array_filter(request()->only(['module', 'action', 'user_id', 'days', 'search']))) }}" class="btn btn-secondary">Volver al listado</a>
@endsection

@section('content')
    <div class="panel">
        <div class="audit-detail">
            <div class="detail-row">
                <strong>ID:</strong>
                <span>{{ $auditLog->id }}</span>
            </div>

            <div class="detail-row">
                <strong>Fecha:</strong>
                <span>{{ $auditLog->created_at->format('d/m/Y H:i:s') }}</span>
            </div>

            <div class="detail-row">
                <strong>Usuario:</strong>
                <span>
                    @if ($auditLog->user)
                        {{ $auditLog->user->name }} ({{ $auditLog->user->email }})
                    @else
                        <span class="text-muted">Sistema</span>
                    @endif
                </span>
            </div>

            <div class="detail-row">
                <strong>Módulo:</strong>
                <span>
                    @php
                        $moduleClass = match ($auditLog->module) {
                            'users' => 'blue',
                            'orders' => 'green',
                            'inventory' => 'yellow',
                            'reports' => 'purple',
                            'vehicles' => 'orange',
                            default => 'gray',
                        };
                    @endphp
                    <span class="badge {{ $moduleClass }}">{{ ucfirst($auditLog->module) }}</span>
                </span>
            </div>

            <div class="detail-row">
                <strong>Acción:</strong>
                <span>{{ ucfirst(str_replace('_', ' ', $auditLog->action)) }}</span>
            </div>

            <div class="detail-row">
                <strong>Descripción:</strong>
                <span>{{ $auditLog->description }}</span>
            </div>

            <div class="detail-row">
                <strong>Dirección IP:</strong>
                <span>{{ $auditLog->ip_address ?? '—' }}</span>
            </div>

            @if ($auditLog->old_values)
                <div class="detail-section">
                    <h4>Valores anteriores</h4>
                    <pre>{{ json_encode($auditLog->old_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                </div>
            @endif

            @if ($auditLog->new_values)
                <div class="detail-section">
                    <h4>Valores nuevos</h4>
                    <pre>{{ json_encode($auditLog->new_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                </div>
            @endif
        </div>
    </div>

    <style>
        .audit-detail {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .detail-row {
            display: flex;
            gap: 1rem;
            padding: 0.5rem 0;
            border-bottom: 1px solid #eee;
        }

        .detail-row strong {
            min-width: 150px;
            font-weight: 600;
        }

        .detail-section {
            margin-top: 1.5rem;
            padding: 1rem;
            background: #f9f9f9;
            border-radius: 4px;
        }

        .detail-section h4 {
            margin: 0 0 0.5rem 0;
            font-size: 1rem;
            color: #333;
        }

        .detail-section pre {
            margin: 0;
            padding: 0.5rem;
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 0.875rem;
            overflow-x: auto;
        }
    </style>
@endsection