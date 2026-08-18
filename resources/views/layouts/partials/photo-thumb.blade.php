@php
    /** @var \App\Models\ServicePhoto $photo */
    $gallery = $gallery ?? 'photos';
    $class = $class ?? null;
@endphp
<img
    src="{{ $photo->url }}"
    alt="{{ $photo->description ?: $photo->lightboxCaption() }}"
    class="js-photo-lightbox {{ $class ?? '' }}"
    data-lightbox="{{ $gallery }}"
    data-lightbox-src="{{ $photo->url }}"
    data-lightbox-caption="{{ $photo->lightboxCaption() }}"
    data-lightbox-meta="{{ $photo->lightboxMeta() }}"
    @if ($photo->description) data-lightbox-description="{{ $photo->description }}" @endif
    role="button"
    tabindex="0"
>
