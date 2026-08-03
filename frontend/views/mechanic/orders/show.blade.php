@extends('layouts.mechanic')

@section('title', 'Editar Orden de Servicio')
@section('heading', 'ORDEN DE SERVICIO ' . $order->order_number)
@section('subheading', 'Creado el ' . $order->created_at->format('d-m-Y H:i'))

@section('top-actions')
    <a href="{{ route('mechanic.orders.index') }}" class="btn-back" style="display:inline-flex; align-items:center; gap:8px; color:var(--text-muted); text-decoration:none; font-weight:600; font-size:0.9rem;">
        <i class="fa-solid fa-arrow-left"></i> Volver a órdenes
    </a>
    <span class="badge-status {{ $order->statusBadgeClass() }}" style="background:#dcfce7; color:#15803d; padding:4px 10px; border-radius:6px; font-weight:bold; font-size:0.8rem; margin-left:12px;">{{ $order->statusLabel() }}</span>
@endsection

@push('styles')
<style>
    /* Tarjetas de Información */
    .card-info { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 10px; padding: 18px; margin-bottom: 20px; }
    .card-title-bar { background: #334155; color: white; padding: 8px 14px; border-radius: 6px; font-size: 0.85rem; font-weight: bold; text-transform: uppercase; margin-bottom: 16px; display: flex; align-items: center; gap: 8px; }

    .grid-2col { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
    .grid-3col { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; }

    .read-field { margin-bottom: 10px; }
    .read-field label { display: block; font-size: 0.72rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; }
    .read-field div { background: #f1f5f9; padding: 8px 12px; border-radius: 6px; font-weight: 600; font-size: 0.88rem; border: 1px solid #e2e8f0; color: #1e293b; }

    /* Campos Editables */
    .form-group { margin-bottom: 16px; }
    .form-group label { display: block; font-size: 0.75rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; margin-bottom: 6px; }
    .form-control { width: 100%; padding: 10px 12px; border: 1px solid var(--border-color); border-radius: 8px; font-size: 0.9rem; background: white; outline: none; }
    .form-control:focus { border-color: var(--primary); }
    textarea.form-control { min-height: 85px; resize: vertical; }

    /* Slider Progreso */
    .progress-box { display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px; }
    .progress-val { font-weight: 700; color: var(--primary); font-size: 1.1rem; }
    .range-slider { width: 100%; height: 8px; border-radius: 4px; background: #e2e8f0; accent-color: var(--primary); cursor: pointer; }

    /* Botón Guardar */
    .btn-submit { width: 100%; background: var(--accent); color: white; border: none; padding: 14px; border-radius: 8px; font-weight: 700; font-size: 1rem; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; }
    .btn-submit:hover { background: #d97706; }
    
    .btn-back:hover { color: var(--primary) !important; }

    @media (max-width: 768px) { .grid-2col, .grid-3col { grid-template-columns: 1fr; } }
</style>
@endpush

@section('content')
    <form action="{{ route('mechanic.orders.status', $order) }}" method="POST">
      @csrf @method('PUT')

      <!-- SECCIÓN 1: DATOS DEL CLIENTE Y VEHÍCULO -->
      <div class="grid-2col">
        <!-- BLOQUE CLIENTE -->
        <div class="card-info">
          <div class="card-title-bar"><i class="fa-solid fa-user"></i> Datos del Cliente</div>
          <div class="grid-3col">
            <div class="read-field">
              <label>Identificación / NIF</label>
              <div>{{ $order->client->identification_number ?? 'N/A' }}</div>
            </div>
            <div class="read-field" style="grid-column: span 2;">
              <label>Nombre Completo</label>
              <div>{{ $order->client->name }}</div>
            </div>
          </div>
          <div class="grid-2col">
            <div class="read-field">
              <label>Teléfono / Móvil</label>
              <div>{{ $order->client->phone ?? 'N/A' }}</div>
            </div>
            <div class="read-field">
              <label>Correo Electrónico</label>
              <div>{{ $order->client->email ?? 'N/A' }}</div>
            </div>
          </div>
          <div class="read-field">
            <label>Dirección / Municipio</label>
            <div>{{ $order->client->address ?? 'N/A' }}</div>
          </div>
        </div>

        <!-- BLOQUE VEHÍCULO -->
        <div class="card-info">
          <div class="card-title-bar"><i class="fa-solid fa-car"></i> Datos del Vehículo</div>
          <div class="grid-3col">
            <div class="read-field">
              <label>Matrícula / Placa</label>
              <div>{{ $order->vehicle->plate }}</div>
            </div>
            <div class="read-field">
              <label>Marca</label>
              <div>{{ $order->vehicle->brand }}</div>
            </div>
            <div class="read-field">
              <label>Modelo</label>
              <div>{{ $order->vehicle->model }} {{ $order->vehicle->year }}</div>
            </div>
          </div>
          <div class="grid-3col">
            <div class="read-field">
              <label>Kilometraje</label>
              <div>{{ number_format($order->vehicle->mileage) }} km</div>
            </div>
            <div class="read-field">
              <label>Motor</label>
              <div>{{ $order->vehicle->engine_number ?? 'N/A' }}</div>
            </div>
            <div class="read-field">
              <label>Chasis</label>
              <div>{{ $order->vehicle->chassis_number ?? 'N/A' }}</div>
            </div>
          </div>
          <div class="read-field">
            <label>Detalle del Servicio</label>
            <div>{{ $order->description }}</div>
          </div>
        </div>
      </div>

      <!-- SECCIÓN 2: REGISTRO OPERATIVO Y AVANCE -->
      <div class="card-info">
        <div class="card-title-bar" style="background: #0284c7;"><i class="fa-solid fa-wrench"></i> Registro de Mantenimiento y Avance</div>
        
        <div class="grid-2col">
          <!-- Columna Izquierda -->
          <div>
            <div class="form-group">
              <label for="nuevo_estado">Actualizar Estado</label>
              <select id="nuevo_estado" name="status" class="form-control">
                @foreach (['recibida' => 'En Espera', 'en_proceso' => 'En Proceso de Reparación', 'pausado' => 'Pausado (Esperando Repuestos)', 'completada' => 'Trabajo Terminado'] as $val => $label)
                    <option value="{{ $val }}" @selected(old('status', $order->status) === $val)>{{ $label }}</option>
                @endforeach
              </select>
            </div>

            <div class="form-group">
              <label for="diagnostico">Diagnóstico Técnico</label>
              <textarea id="diagnostico" name="diagnosis" class="form-control" placeholder="Escribe las novedades encontradas en la revisión...">{{ old('diagnosis', $order->diagnosis) }}</textarea>
            </div>

            <div class="form-group">
              <label for="recomendaciones">Recomendaciones para el Cliente</label>
              <textarea id="recomendaciones" name="recommendations" class="form-control" placeholder="Escribe sugerencias de mantenimiento preventivo...">{{ old('recommendations', $order->recommendations) }}</textarea>
            </div>
          </div>

          <!-- Columna Derecha -->
          <div>
            <div class="form-group">
              <div class="progress-box">
                <label>Informar Avance del Trabajo (%)</label>
                <span class="progress-val" id="progresoTexto">{{ old('progress', $order->progress ?? 0) }}%</span>
              </div>
              <input 
                type="range" 
                name="progress"
                min="0" 
                max="100" 
                value="{{ old('progress', $order->progress ?? 0) }}" 
                class="range-slider" 
                id="progresoSlider"
                oninput="document.getElementById('progresoTexto').innerText = this.value + '%'"
              >
            </div>

            <div class="form-group">
              <label for="comentario_avance">Comentario de Avance (Opcional)</label>
              <textarea id="comentario_avance" name="comment" class="form-control" placeholder="Describe brevemente las tareas realizadas hasta ahora..."></textarea>
            </div>

            <div class="form-group">
              <label for="observacion_tecnica">Observación Técnica (Opcional)</label>
              <textarea id="observacion_tecnica" name="technical_observation" class="form-control" placeholder="Piezas cambiadas, calibraciones hechas, etc..."></textarea>
            </div>

            <button type="submit" class="btn-submit">
              <i class="fa-solid fa-floppy-disk"></i> Guardar Cambios y Actualizar Orden
            </button>
          </div>
        </div>
      </div>
    </form>
@endsection
