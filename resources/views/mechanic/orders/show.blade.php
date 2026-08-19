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
    .card-info { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 10px; padding: 18px; margin-bottom: 20px; min-width: 0; max-width: 100%; overflow: hidden; }
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

    /* Galería de fotos organizada por tipo (Sprint 5A.3) */
    .photo-gallery-row {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(min(180px, 100%), 1fr));
        gap: 12px;
        margin-top: 8px;
        min-width: 0;
        width: 100%;
    }

    .photo-item {
        position: relative;
        border-radius: 8px;
        overflow: hidden;
        border: 1px solid #e2e8f0;
        background: white;
        min-width: 0;
        max-width: 100%;
        display: flex;
        flex-direction: column;
        box-sizing: border-box;
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .photo-item:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }

    .photo-item img {
        width: 100%;
        height: 140px;
        object-fit: cover;
        display: block;
        cursor: pointer;
        background: #e2e8f0;
        color: transparent;
        font-size: 0;
    }

    .photo-item img.is-missing {
        object-fit: contain;
        background: #f1f5f9;
    }

    .photo-info {
        padding: 8px 36px 8px 8px;
        background: #f8fafc;
        border-top: 1px solid #e2e8f0;
        min-width: 0;
    }

    .photo-desc {
        display: block;
        font-size: 0.75rem;
        color: #475569;
        margin-bottom: 4px;
        font-style: italic;
        overflow-wrap: anywhere;
    }

    .photo-user {
        display: block;
        font-size: 0.7rem;
        color: #94a3b8;
        overflow-wrap: anywhere;
    }

    .photo-delete {
        position: absolute;
        top: 8px;
        right: 8px;
        z-index: 2;
        width: 28px;
        height: 28px;
        background: rgba(239, 68, 68, 0.9);
        color: white;
        border: none;
        border-radius: 50%;
        cursor: pointer;
        font-size: 1.2rem;
        font-weight: bold;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        transition: background 0.2s;
    }

    .photo-delete:hover {
        background: rgba(220, 38, 38, 1);
    }
</style>
@endpush

@section('content')
    <form action="{{ route('mechanic.orders.status', $order) }}" method="POST" enctype="multipart/form-data" class="mechanic-order-page">
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
                @foreach (['recibida' => 'En Espera', 'en_proceso' => 'En Proceso de Reparación', 'completada' => 'Trabajo Terminado'] as $val => $label)
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
          </div>
        </div>
      </div>

      <button type="submit" class="btn-submit" style="margin-bottom: 20px;">
        <i class="fa-solid fa-floppy-disk"></i> Guardar Cambios y Actualizar Orden
      </button>
    </form>

      <!-- SECCIÓN 3: REGISTRO FOTOGRÁFICO INTEGRADO CON DIAGNÓSTICO (Sprint 5A.3) -->
      <div class="card-info">
        <div class="card-title-bar" style="background: #7c3aed;"><i class="fa-solid fa-camera"></i> Evidencias Fotográficas del Diagnóstico</div>

        <!-- Resumen de fotos -->
        <div style="display:flex; gap:16px; margin-bottom:20px; flex-wrap:wrap;">
          <div style="flex:1; min-width:140px; background:#f8fafc; padding:12px; border-radius:8px; border:1px solid #e2e8f0;">
            <div style="font-size:0.7rem; color:var(--text-muted); font-weight:700; text-transform:uppercase;">Total Fotos</div>
            <div style="font-size:1.5rem; font-weight:700; color:#7c3aed;" data-photo-count="total">{{ $photoSummary['total'] ?? 0 }}</div>
          </div>
          <div style="flex:1; min-width:140px; background:#f8fafc; padding:12px; border-radius:8px; border:1px solid #e2e8f0;">
            <div style="font-size:0.7rem; color:var(--text-muted); font-weight:700; text-transform:uppercase;">Recepción</div>
            <div style="font-size:1.5rem; font-weight:700; color:#0ea5e9;" data-photo-count="reception">{{ $photoSummary['by_type']['reception'] ?? 0 }}</div>
          </div>
          <div style="flex:1; min-width:140px; background:#f8fafc; padding:12px; border-radius:8px; border:1px solid #e2e8f0;">
            <div style="font-size:0.7rem; color:var(--text-muted); font-weight:700; text-transform:uppercase;">Antes</div>
            <div style="font-size:1.5rem; font-weight:700; color:#f59e0b;" data-photo-count="before">{{ $photoSummary['by_type']['before'] ?? 0 }}</div>
          </div>
          <div style="flex:1; min-width:140px; background:#f8fafc; padding:12px; border-radius:8px; border:1px solid #e2e8f0;">
            <div style="font-size:0.7rem; color:var(--text-muted); font-weight:700; text-transform:uppercase;">Después</div>
            <div style="font-size:1.5rem; font-weight:700; color:#10b981;" data-photo-count="after">{{ $photoSummary['by_type']['after'] ?? 0 }}</div>
          </div>
          <div style="flex:1; min-width:140px; background:#f8fafc; padding:12px; border-radius:8px; border:1px solid #e2e8f0;">
            <div style="font-size:0.7rem; color:var(--text-muted); font-weight:700; text-transform:uppercase;">Evidencia</div>
            <div style="font-size:1.5rem; font-weight:700; color:#8b5cf6;" data-photo-count="evidence">{{ $photoSummary['by_type']['evidence'] ?? 0 }}</div>
          </div>
        </div>

        <!-- Alertas de validación -->
        @if(in_array($order->status, ['completada', 'entregada']))
          @if(!($photoSummary['has_initial'] ?? false))
            <div style="background:#fef3c7; border:1px solid #f59e0b; color:#92400e; padding:12px; border-radius:8px; margin-bottom:16px; font-size:0.85rem;">
              <i class="fa-solid fa-triangle-exclamation"></i> <strong>Atención:</strong> Esta orden no tiene evidencias fotográficas iniciales (recepción o antes del trabajo).
            </div>
          @endif
          @if(!($photoSummary['has_final'] ?? false))
            <div style="background:#fef3c7; border:1px solid #f59e0b; color:#92400e; padding:12px; border-radius:8px; margin-bottom:16px; font-size:0.85rem;">
              <i class="fa-solid fa-triangle-exclamation"></i> <strong>Atención:</strong> Esta orden no tiene evidencias fotográficas finales (después del trabajo).
            </div>
          @endif
        @endif

        <!-- Subir nuevas fotos -->
        <div class="form-group">
          <label>Subir Evidencia Fotográfica</label>
          <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#photoUploadModal">
            <i class="fa-solid fa-upload"></i> Agregar foto
          </button>
          <span style="display:block; font-size:0.75rem; color:var(--text-muted); margin-top:8px;">Máximo 10MB por foto. Formatos: JPG, PNG, GIF.</span>
        </div>

        <div class="modal fade" id="photoUploadModal" tabindex="-1" aria-labelledby="photoUploadModalTitle" aria-hidden="true"
            data-order-id="{{ $order->id }}"
            data-photos-index="{{ route('mechanic.orders.photos.index', $order) }}"
            data-photos-store="{{ route('mechanic.orders.photos.store', $order) }}"
            data-photos-destroy="{{ url('/mecanico/fotos') }}">
          <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title" id="photoUploadModalTitle">Nueva evidencia fotográfica</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
              </div>
              <div class="modal-body">
                <div class="field mb-3">
                  <label class="form-label" for="photoType">Tipo *</label>
                  <select id="photoType" class="form-control">
                    <option value="reception">Recepción</option>
                    <option value="before">Antes del trabajo</option>
                    <option value="after">Después del trabajo</option>
                    <option value="evidence">Evidencia de diagnóstico</option>
                  </select>
                </div>
                <div class="field mb-3">
                  <label class="form-label" for="photoDescription">Descripción técnica</label>
                  <input type="text" id="photoDescription" class="form-control" placeholder="Ej: Golpe en parachoques delantero">
                </div>
                <div class="field">
                  <label class="form-label" for="photoInput">Archivos *</label>
                  <input type="file" id="photoInput" class="form-control" accept="image/*" multiple>
                  <span class="form-text">Puedes seleccionar varias fotos. Máximo 10MB cada una.</span>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
              </div>
            </div>
          </div>
        </div>

        <!-- Galería organizada por tipo -->
        <div class="form-group">
          <label>Evidencias por Tipo</label>
          
          <!-- Recepción -->
          <div style="margin-bottom:16px;">
            <div style="background:#e0f2fe; color:#0369a1; padding:8px 12px; border-radius:6px; font-weight:700; font-size:0.85rem; margin-bottom:8px; display:flex; align-items:center; gap:8px;">
              <i class="fa-solid fa-clipboard-check"></i> Recepción (<span data-photo-count="reception">{{ $photoSummary['by_type']['reception'] ?? 0 }}</span>)
            </div>
            <div id="gallery-reception" class="photo-gallery-row">
              @forelse ($photos->where('type', 'reception') as $photo)
                @include('mechanic.orders.partials.photo-item', ['photo' => $photo])
              @empty
                <p class="photo-empty-msg" style="color:var(--text-muted);font-size:0.8rem;font-style:italic;">No hay fotos en esta categoría.</p>
              @endforelse
            </div>
          </div>

          <!-- Antes del trabajo -->
          <div style="margin-bottom:16px;">
            <div style="background:#fef3c7; color:#92400e; padding:8px 12px; border-radius:6px; font-weight:700; font-size:0.85rem; margin-bottom:8px; display:flex; align-items:center; gap:8px;">
              <i class="fa-solid fa-wrench"></i> Antes del trabajo (<span data-photo-count="before">{{ $photoSummary['by_type']['before'] ?? 0 }}</span>)
            </div>
            <div id="gallery-before" class="photo-gallery-row">
              @forelse ($photos->where('type', 'before') as $photo)
                @include('mechanic.orders.partials.photo-item', ['photo' => $photo])
              @empty
                <p class="photo-empty-msg" style="color:var(--text-muted);font-size:0.8rem;font-style:italic;">No hay fotos en esta categoría.</p>
              @endforelse
            </div>
          </div>

          <!-- Evidencia de diagnóstico -->
          <div style="margin-bottom:16px;">
            <div style="background:#f3e8ff; color:#7c3aed; padding:8px 12px; border-radius:6px; font-weight:700; font-size:0.85rem; margin-bottom:8px; display:flex; align-items:center; gap:8px;">
              <i class="fa-solid fa-stethoscope"></i> Evidencia de diagnóstico (<span data-photo-count="evidence">{{ $photoSummary['by_type']['evidence'] ?? 0 }}</span>)
            </div>
            <div id="gallery-evidence" class="photo-gallery-row">
              @forelse ($photos->where('type', 'evidence') as $photo)
                @include('mechanic.orders.partials.photo-item', ['photo' => $photo])
              @empty
                <p class="photo-empty-msg" style="color:var(--text-muted);font-size:0.8rem;font-style:italic;">No hay fotos en esta categoría.</p>
              @endforelse
            </div>
          </div>

          <!-- Después del trabajo -->
          <div style="margin-bottom:16px;">
            <div style="background:#dcfce7; color:#15803d; padding:8px 12px; border-radius:6px; font-weight:700; font-size:0.85rem; margin-bottom:8px; display:flex; align-items:center; gap:8px;">
              <i class="fa-solid fa-check-circle"></i> Después del trabajo (<span data-photo-count="after">{{ $photoSummary['by_type']['after'] ?? 0 }}</span>)
            </div>
            <div id="gallery-after" class="photo-gallery-row">
              @forelse ($photos->where('type', 'after') as $photo)
                @include('mechanic.orders.partials.photo-item', ['photo' => $photo])
              @empty
                <p class="photo-empty-msg" style="color:var(--text-muted);font-size:0.8rem;font-style:italic;">No hay fotos en esta categoría.</p>
              @endforelse
            </div>
          </div>
        </div>
      </div>
@endsection

@push('scripts')
<script>
    const photoConfig = document.getElementById('photoUploadModal');
    const photoInput = document.getElementById('photoInput');
    const photoType = document.getElementById('photoType');
    const photoDescription = document.getElementById('photoDescription');
    const orderId = Number(photoConfig?.dataset.orderId || 0);
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    const photosIndexUrl = photoConfig?.dataset.photosIndex || '';
    const photosStoreUrl = photoConfig?.dataset.photosStore || '';
    const photosDestroyUrl = photoConfig?.dataset.photosDestroy || '';

    // Galerías por tipo (Sprint 5A.3)
    const galleries = {
        'reception': document.getElementById('gallery-reception'),
        'before': document.getElementById('gallery-before'),
        'evidence': document.getElementById('gallery-evidence'),
        'after': document.getElementById('gallery-after'),
    };

    // Cargar fotos existentes
    async function loadPhotos() {
        try {
            const res = await fetch(photosIndexUrl, {
                credentials: 'same-origin',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
            });

            if (!res.ok) {
                throw new Error(`Error ${res.status} al cargar fotos`);
            }

            const photos = await res.json();
            renderPhotosByType(photos);
        } catch (error) {
            console.error('Error loading photos:', error);
            showToast('Error al cargar las fotos', 'error');
        }
    }

    function renderPhotosByType(photos) {
        const photosByType = {
            'reception': [],
            'before': [],
            'evidence': [],
            'after': [],
        };

        (photos || []).forEach((photo) => {
            if (photosByType[photo.type]) {
                photosByType[photo.type].push(photo);
            }
        });

        Object.keys(photosByType).forEach(type => {
            document.querySelectorAll(`[data-photo-count="${type}"]`).forEach((el) => {
                el.textContent = String(photosByType[type].length);
            });
        });
        document.querySelectorAll('[data-photo-count="total"]').forEach((el) => {
            el.textContent = String((photos || []).length);
        });

        Object.values(galleries).forEach(gallery => {
            if (gallery) gallery.innerHTML = '';
        });

        Object.keys(photosByType).forEach(type => {
            const gallery = galleries[type];
            if (!gallery) return;

            const typePhotos = photosByType[type];
            if (typePhotos.length === 0) {
                gallery.innerHTML = '<p class="photo-empty-msg" style="color:var(--text-muted);font-size:0.8rem;font-style:italic;">No hay fotos en esta categoría.</p>';
                return;
            }

            gallery.innerHTML = typePhotos.map(photo => `
                <div class="photo-item autogest-fade-in" data-id="${photo.id}">
                    ${window.AutoGestLightbox.imgTag(photo, 'order-' + orderId)}
                    <div class="photo-info">
                        ${photo.description ? `<span class="photo-desc">${photo.description}</span>` : ''}
                        <span class="photo-user">${photo.user} - ${photo.created_at}</span>
                    </div>
                    <button type="button" class="photo-delete" data-photo-id="${photo.id}" aria-label="Eliminar foto">×</button>
                </div>
            `).join('');
        });
    }

    document.addEventListener('click', (event) => {
        const deleteButton = event.target.closest('.photo-delete[data-photo-id]');
        if (!deleteButton) {
            return;
        }

        event.preventDefault();
        deletePhoto(Number(deleteButton.dataset.photoId));
    });

    // Función para mostrar notificaciones toast
    function showToast(message, type = 'success') {
        const toast = document.createElement('div');
        toast.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 16px 24px;
            background: ${type === 'success' ? '#10b981' : '#ef4444'};
            color: white;
            border-radius: 8px;
            font-weight: 600;
            z-index: 10000;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            animation: autogest-slide-in 0.3s ease-out;
        `;
        toast.textContent = message;
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.style.animation = 'autogest-slide-out 0.3s ease-out';
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }

    // Subir fotos (Sprint 5A.3 - con descripción técnica)
    if (photoInput) {
    photoInput.addEventListener('change', async (e) => {
        const files = Array.from(e.target.files);
        let successCount = 0;
        let errorCount = 0;

        for (const file of files) {
            if (file.size > 10 * 1024 * 1024) {
                showToast(`El archivo ${file.name} excede el límite de 10MB`, 'error');
                errorCount++;
                continue;
            }

            // Mostrar indicador de carga
            const uploadStatus = document.createElement('div');
            uploadStatus.style.cssText = `
                position: fixed;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                background: rgba(0,0,0,0.8);
                color: white;
                padding: 20px 30px;
                border-radius: 8px;
                z-index: 10001;
                font-weight: 600;
            `;
            uploadStatus.textContent = `Subiendo ${file.name}...`;
            document.body.appendChild(uploadStatus);

            const formData = new FormData();
            formData.append('photo', file);
            formData.append('type', photoType.value);
            formData.append('description', photoDescription.value || '');

            try {
                if (!csrfToken) {
                    throw new Error('Token CSRF no encontrado. Recarga la página.');
                }

                const res = await fetch(photosStoreUrl, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    body: formData,
                });

                if (!res.ok) {
                    const errorData = await res.json().catch(() => ({}));
                    throw new Error(errorData.message || `Error ${res.status} al subir la foto`);
                }

                const data = await res.json();
                
                uploadStatus.remove();
                
                if (data.success) {
                    successCount++;
                    showToast(`✓ ${file.name} subida correctamente`, 'success');
                    // Recargar fotos inmediatamente después de cada subida exitosa
                    await loadPhotos();
                } else {
                    errorCount++;
                    showToast(`✗ Error al subir ${file.name}: ${data.message || 'Error desconocido'}`, 'error');
                }
            } catch (error) {
                console.error('Error uploading photo:', error);
                uploadStatus.remove();
                errorCount++;
                showToast(`✗ Error al subir ${file.name}: ${error.message}`, 'error');
            }
        }
        
        photoInput.value = '';
        photoDescription.value = '';

        if (successCount > 0) {
            const uploadModal = document.getElementById('photoUploadModal');
            if (uploadModal && window.bootstrap?.Modal) {
                window.bootstrap.Modal.getInstance(uploadModal)?.hide();
            }
        }
        
        // Resumen final
        if (files.length > 1) {
            setTimeout(() => {
                showToast(`Subida completada: ${successCount} exitosas, ${errorCount} con errores`, 
                    errorCount === 0 ? 'success' : 'error');
            }, 500);
        }
    });
    }

    // Eliminar foto
    async function deletePhoto(photoId) {
        const confirmed = await window.AutoGestConfirm.ask({
            title: 'Eliminar evidencia',
            message: '¿Eliminar esta foto? Esta acción no se puede deshacer.',
            confirmLabel: 'Eliminar',
            danger: true,
        });
        if (!confirmed) return;

        try {
            const res = await fetch(`${photosDestroyUrl}/${photoId}`, {
                method: 'DELETE',
                credentials: 'same-origin',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                },
            });
            const data = await res.json();
            if (data.success) {
                showToast('Foto eliminada correctamente', 'success');
                await loadPhotos();
            } else {
                showToast('Error al eliminar la foto', 'error');
            }
        } catch (error) {
            console.error('Error deleting photo:', error);
            showToast('Error al eliminar la foto', 'error');
        }
    }

    // Cargar fotos al inicio
    if (photoInput) {
        loadPhotos();
    }
</script>
@endpush
