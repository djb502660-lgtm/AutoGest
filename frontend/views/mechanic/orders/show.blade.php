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

      <!-- SECCIÓN 3: REGISTRO FOTOGRÁFICO -->
      <div class="card-info">
        <div class="card-title-bar" style="background: #7c3aed;"><i class="fa-solid fa-camera"></i> Registro Fotográfico</div>

        <div class="form-group">
          <label>Subir Evidencia Fotográfica</label>
          <div style="display:flex; gap:12px; flex-wrap:wrap; margin-bottom:12px;">
            <button type="button" class="btn-submit" style="width:auto; background:#0ea5e9;" onclick="document.getElementById('photoInput').click()">
              <i class="fa-solid fa-upload"></i> Agregar Foto
            </button>
            <select id="photoType" class="form-control" style="width:auto; min-width:150px;">
              <option value="before">📷 Antes del trabajo</option>
              <option value="after">📷 Después del trabajo</option>
              <option value="evidence">📷 Evidencia general</option>
            </select>
          </div>
          <input type="file" id="photoInput" accept="image/*" multiple style="display:none;">
          <span style="font-size:0.75rem; color:var(--text-muted);">Máximo 10MB por foto. Formatos: JPG, PNG, GIF</span>
        </div>

        <div id="photoGallery" class="photo-gallery"></div>
      </div>
    </form>
@endsection

@push('scripts')
<script>
    const photoInput = document.getElementById('photoInput');
    const photoGallery = document.getElementById('photoGallery');
    const photoType = document.getElementById('photoType');
    const orderId = {{ $order->id }};

    // Cargar fotos existentes
    async function loadPhotos() {
        try {
            const res = await fetch(`{{ url('/mecanico/ordenes') }}/${orderId}/fotos`);
            const photos = await res.json();
            renderPhotos(photos);
        } catch (error) {
            console.error('Error loading photos:', error);
        }
    }

    function renderPhotos(photos) {
        if (!photos || photos.length === 0) {
            photoGallery.innerHTML = '<p style="color:var(--text-muted);font-size:0.85rem;">No hay fotos registradas.</p>';
            return;
        }

        photoGallery.innerHTML = photos.map(photo => `
            <div class="photo-item" data-id="${photo.id}" style="animation: fadeIn 0.3s ease-out;">
                <img src="${photo.url}" alt="${photo.description || 'Foto'}" onclick="window.open('${photo.url}', '_blank')">
                <div class="photo-info">
                    <span class="photo-type">${photo.type_label}</span>
                    ${photo.description ? `<span class="photo-desc">${photo.description}</span>` : ''}
                    <span class="photo-user">${photo.user} - ${photo.created_at}</span>
                </div>
                <button type="button" class="photo-delete" onclick="deletePhoto(${photo.id})">×</button>
            </div>
        `).join('');
        
        // Agregar animación de fade-in
        if (!document.getElementById('fadeInAnimation')) {
            const fadeStyle = document.createElement('style');
            fadeStyle.id = 'fadeInAnimation';
            fadeStyle.textContent = `
                @keyframes fadeIn {
                    from { opacity: 0; transform: scale(0.9); }
                    to { opacity: 1; transform: scale(1); }
                }
            `;
            document.head.appendChild(fadeStyle);
        }
    }

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
            animation: slideIn 0.3s ease-out;
        `;
        toast.textContent = message;
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.style.animation = 'slideOut 0.3s ease-out';
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }

    // Agregar animaciones CSS
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slideIn {
            from { transform: translateX(400px); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        @keyframes slideOut {
            from { transform: translateX(0); opacity: 1; }
            to { transform: translateX(400px); opacity: 0; }
        }
        .uploading {
            opacity: 0.6;
            pointer-events: none;
        }
    `;
    document.head.appendChild(style);

    // Subir fotos
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
            formData.append('description', '');

            try {
                const res = await fetch(`{{ url('/mecanico/ordenes') }}/${orderId}/fotos`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: formData,
                });
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
        
        // Resumen final
        if (files.length > 1) {
            setTimeout(() => {
                showToast(`Subida completada: ${successCount} exitosas, ${errorCount} con errores`, 
                    errorCount === 0 ? 'success' : 'error');
            }, 500);
        }
    });

    // Eliminar foto
    async function deletePhoto(photoId) {
        if (!confirm('¿Eliminar esta foto?')) return;

        try {
            const res = await fetch(`{{ url('/mecanico/fotos') }}/${photoId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
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
    loadPhotos();
</script>

@push('styles')
<style>
    .photo-gallery {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
        gap: 16px;
        margin-top: 16px;
    }

    .photo-item {
        position: relative;
        border: 2px solid var(--border-color);
        border-radius: 12px;
        overflow: hidden;
        aspect-ratio: 1;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .photo-item:hover {
        transform: translateY(-4px);
        box-shadow: 0 4px 16px rgba(0,0,0,0.2);
    }

    .photo-item img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        cursor: pointer;
        transition: transform 0.2s;
    }

    .photo-item:hover img {
        transform: scale(1.05);
    }

    .photo-info {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        background: linear-gradient(to top, rgba(0,0,0,0.85), rgba(0,0,0,0.6), transparent);
        color: white;
        padding: 12px 8px 8px;
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

    .photo-delete {
        position: absolute;
        top: 4px;
        right: 4px;
        background: rgba(255,0,0,0.8);
        color: white;
        border: none;
        border-radius: 50%;
        width: 24px;
        height: 24px;
        cursor: pointer;
        font-size: 16px;
        line-height: 1;
        display: none;
    }

    .photo-item:hover .photo-delete {
        display: block;
    }
</style>
@endpush
