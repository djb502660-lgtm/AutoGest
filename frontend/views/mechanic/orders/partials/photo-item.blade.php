<div class="photo-item" data-id="{{ $photo->id }}">
    <img src="{{ asset('storage/' . $photo->photo_path) }}" alt="{{ $photo->description ?? 'Foto' }}" onclick="window.open('{{ asset('storage/' . $photo->photo_path) }}', '_blank')">
    <div class="photo-info">
        @if ($photo->description)
            <span class="photo-desc">{{ $photo->description }}</span>
        @endif
        <span class="photo-user">{{ $photo->user->name }} - {{ $photo->created_at->format('d/m/Y H:i') }}</span>
    </div>
    <button type="button" class="photo-delete" onclick="deletePhoto({{ $photo->id }})">×</button>
</div>
