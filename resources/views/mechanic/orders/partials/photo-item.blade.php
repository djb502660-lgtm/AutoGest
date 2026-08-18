@php
    /** @var \App\Models\ServicePhoto $photo */
    /** @var \App\Models\ServiceOrder $order */
@endphp
<div class="photo-item" data-id="{{ $photo->id }}">
    @include('layouts.partials.photo-thumb', [
        'photo' => $photo,
        'gallery' => 'order-'.$order->id,
    ])
    <div class="photo-info">
        @if ($photo->description)
            <span class="photo-desc">{{ $photo->description }}</span>
        @endif
        <span class="photo-user">{{ $photo->user?->name ?? 'Sistema' }} - {{ $photo->created_at?->format('d/m/Y H:i') }}</span>
    </div>
    <button type="button" class="photo-delete" data-photo-id="{{ $photo->id }}" aria-label="Eliminar foto">×</button>
</div>
