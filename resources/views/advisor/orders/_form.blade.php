<div class="form-grid">
    @php $order = $order ?? null; @endphp
    <div class="field">
        <label for="vehicle_id">Vehículo *</label>
        <select name="vehicle_id" id="vehicle_id" required>
            <option value="">Seleccionar vehículo...</option>
            @foreach ($vehicles as $vehicle)
                <option value="{{ $vehicle->id }}" @selected(old('vehicle_id', optional($order)->vehicle_id) == $vehicle->id)>
                    {{ $vehicle->plate }} — {{ $vehicle->brand }} {{ $vehicle->model }} ({{ $vehicle->client->name }})
                </option>
            @endforeach
        </select>
    </div>

    <div class="field">
        <label for="mechanic_id">Mecánico asignado</label>
        <select name="mechanic_id" id="mechanic_id">
            <option value="">Sin asignar (asignar después)</option>
            @foreach ($mechanics as $mechanic)
                <option value="{{ $mechanic->id }}" @selected(old('mechanic_id', optional($order)->mechanic_id) == $mechanic->id)>
                    {{ $mechanic->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="field">
        <label for="priority">Prioridad *</label>
        <select name="priority" id="priority" required>
            @foreach (['baja' => 'Baja', 'normal' => 'Normal', 'alta' => 'Alta', 'urgente' => 'Urgente'] as $value => $label)
                <option value="{{ $value }}" @selected(old('priority', optional($order)->priority ?? 'normal') === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>

    <div class="field">
        <label for="scheduled_at">Fecha programada</label>
        <input type="datetime-local" name="scheduled_at" id="scheduled_at"
            value="{{ old('scheduled_at', isset($order) && $order->scheduled_at ? $order->scheduled_at->format('Y-m-d\TH:i') : '') }}">
    </div>

    <div class="field">
        <label for="estimated_cost">Costo estimado ($)</label>
        <input type="number" name="estimated_cost" id="estimated_cost" min="0" step="0.01"
            value="{{ old('estimated_cost', optional($order)->estimated_cost) }}">
    </div>

    @if (isset($order))
        <div class="field">
            <label for="status">Estado</label>
            <select name="status" id="status">
                @foreach (['recibida','en_proceso','completada','entregada','cancelada'] as $st)
                    <option value="{{ $st }}" @selected(old('status', $order->status) === $st)>{{ ucfirst(str_replace('_', ' ', $st)) }}</option>
                @endforeach
            </select>
        </div>
    @endif

    <div class="field" style="grid-column:1/-1;">
        <label for="description">Descripción del servicio *</label>
        <textarea name="description" id="description" rows="3" required placeholder="Ej: Cambio de aceite, revisión de frenos...">{{ old('description', optional($order)->description) }}</textarea>
    </div>

    @if (isset($order))
        <div class="field" style="grid-column:1/-1;">
            <label>Fotos del vehículo (Recepción)</label>
            <div id="photoUploadSection"
                data-order-id="{{ $order->id }}"
                data-photos-index="{{ route('advisor.orders.photos.index', $order) }}"
                data-photos-store="{{ route('advisor.orders.photos.store', $order) }}"
                data-photos-destroy="{{ url('/asesor/fotos') }}">
                <div class="photo-upload-area">
                    <input type="file" id="photoInput" accept="image/*" multiple style="display:none;">
                    <button type="button" class="btn btn-secondary" onclick="document.getElementById('photoInput').click()">
                        📷 Agregar fotos de recepción
                    </button>
                    <span style="font-size:0.8rem;color:var(--muted);margin-left:8px;">Máximo 10MB por foto</span>
                </div>
                <div id="photoGallery" class="photo-gallery"></div>
            </div>
        </div>
    @endif
</div>

@if (isset($order))
@push('scripts')
<script>
    const photoConfig = document.getElementById('photoUploadSection');
    const photoInput = document.getElementById('photoInput');
    const photoGallery = document.getElementById('photoGallery');
    const orderId = Number(photoConfig?.dataset.orderId || 0);
    const photosIndexUrl = photoConfig?.dataset.photosIndex || '';
    const photosStoreUrl = photoConfig?.dataset.photosStore || '';
    const photosDestroyUrl = photoConfig?.dataset.photosDestroy || '';
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

    // Cargar fotos existentes
    async function loadPhotos() {
        try {
            const res = await fetch(photosIndexUrl);
            const photos = await res.json();
            renderPhotos(photos);
        } catch (error) {
            console.error('Error loading photos:', error);
        }
    }

    function renderPhotos(photos) {
        if (!photos || photos.length === 0) {
            photoGallery.innerHTML = '<p style="color:var(--muted);font-size:0.85rem;">No hay fotos registradas.</p>';
            return;
        }

        photoGallery.innerHTML = photos.map(photo => `
            <div class="photo-item autogest-fade-in" data-id="${photo.id}">
                ${window.AutoGestLightbox.imgTag(photo, 'order-' + orderId)}
                <div class="photo-info">
                    <span class="photo-type">${photo.type_label}</span>
                    ${photo.description ? `<span class="photo-desc">${photo.description}</span>` : ''}
                    <span class="photo-user">${photo.user} - ${photo.created_at}</span>
                </div>
                <button type="button" class="photo-delete" data-photo-id="${photo.id}" aria-label="Eliminar foto">×</button>
            </div>
        `).join('');
    }

    document.addEventListener('click', (event) => {
        const deleteButton = event.target.closest('#photoGallery .photo-delete[data-photo-id]');
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
            formData.append('type', 'reception');
            formData.append('description', '');

            try {
                if (!csrfToken) {
                    throw new Error('Token CSRF no encontrado. Recarga la página.');
                }

                const res = await fetch(photosStoreUrl, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
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
    loadPhotos();
</script>
@endpush

@push('styles')
<style>
    .photo-upload-area {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 12px;
    }

    .photo-gallery {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
        gap: 16px;
        margin-top: 16px;
    }

    .photo-item {
        position: relative;
        border: 2px solid var(--border);
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
@endif
