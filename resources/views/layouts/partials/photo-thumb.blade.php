@php
    /** @var \App\Models\ServicePhoto $photo */
    $gallery = $gallery ?? 'photos';
    $class = $class ?? null;
    $fallback = 'data:image/svg+xml,'.rawurlencode('<svg xmlns="http://www.w3.org/2000/svg" width="320" height="180" viewBox="0 0 320 180"><rect fill="#e2e8f0" width="320" height="180"/><text x="50%" y="50%" text-anchor="middle" dy=".3em" fill="#94a3b8" font-family="sans-serif" font-size="14">Sin imagen</text></svg>');
@endphp
<img
    src="{{ $photo->url }}"
    alt="{{ $photo->description ?: $photo->lightboxCaption() }}"
    class="js-photo-lightbox {{ $class ?? '' }}"
    data-lightbox="{{ $gallery }}"
    data-lightbox-src="{{ $photo->url }}"
    data-lightbox-caption="{{ $photo->lightboxCaption() }}"
    data-lightbox-meta="{{ $photo->lightboxMeta() }}"
    data-fallback="{{ $fallback }}"
    onerror="this.onerror=null;this.src=this.dataset.fallback;this.classList.add('is-missing');"
    @if ($photo->description) data-lightbox-description="{{ $photo->description }}" @endif
    role="button"
    tabindex="0"
>
