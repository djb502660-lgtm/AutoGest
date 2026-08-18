<div class="modal fade photo-lightbox" id="photoLightbox" tabindex="-1" aria-labelledby="photoLightboxCaption" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl photo-lightbox-dialog">
        <div class="modal-content photo-lightbox-content">
            <button type="button" class="btn-close btn-close-white photo-lightbox-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            <button type="button" class="photo-lightbox-nav photo-lightbox-prev" data-lightbox-prev aria-label="Foto anterior">
                <i class="fa-solid fa-chevron-left" aria-hidden="true"></i>
            </button>
            <button type="button" class="photo-lightbox-nav photo-lightbox-next" data-lightbox-next aria-label="Foto siguiente">
                <i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
            </button>
            <img class="photo-lightbox-img" alt="">
            <div class="photo-lightbox-footer">
                <div class="photo-lightbox-copy">
                    <div class="photo-lightbox-caption" id="photoLightboxCaption"></div>
                    <div class="photo-lightbox-description"></div>
                    <div class="photo-lightbox-meta"></div>
                </div>
                <div class="photo-lightbox-counter" aria-live="polite"></div>
            </div>
        </div>
    </div>
</div>
<script>
window.AutoGestLightbox = window.AutoGestLightbox || {};

window.AutoGestLightbox.escape = function (value) {
    return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
};

window.AutoGestLightbox.imgTag = function (photo, gallery) {
    const esc = window.AutoGestLightbox.escape;
    const caption = photo.type_label || '';
    const meta = [photo.user, photo.created_at].filter(Boolean).join(' · ');
    const description = photo.description || '';
    const alt = description || caption || 'Evidencia fotográfica';
    const descAttr = description ? ` data-lightbox-description="${esc(description)}"` : '';

    return `<img src="${esc(photo.url)}" alt="${esc(alt)}" class="js-photo-lightbox" data-lightbox="${esc(gallery)}" data-lightbox-src="${esc(photo.url)}" data-lightbox-caption="${esc(caption)}" data-lightbox-meta="${esc(meta)}"${descAttr} role="button" tabindex="0">`;
};

(function () {
    const modalEl = document.getElementById('photoLightbox');
    const bootstrapUi = window.bootstrap;
    if (!modalEl || !bootstrapUi?.Modal) {
        return;
    }

    const img = modalEl.querySelector('.photo-lightbox-img');
    const caption = modalEl.querySelector('.photo-lightbox-caption');
    const description = modalEl.querySelector('.photo-lightbox-description');
    const meta = modalEl.querySelector('.photo-lightbox-meta');
    const counter = modalEl.querySelector('.photo-lightbox-counter');
    const prevBtn = modalEl.querySelector('[data-lightbox-prev]');
    const nextBtn = modalEl.querySelector('[data-lightbox-next]');
    const modal = bootstrapUi.Modal.getOrCreateInstance(modalEl);

    let items = [];
    let index = 0;

    function collect(trigger) {
        const group = trigger.getAttribute('data-lightbox') || '';
        items = Array.from(document.querySelectorAll('[data-lightbox]'))
            .filter((el) => el.getAttribute('data-lightbox') === group);
        index = Math.max(0, items.indexOf(trigger));
    }

    function show(i) {
        if (!items.length) {
            return;
        }

        index = (i + items.length) % items.length;
        const el = items[index];
        const src = el.getAttribute('data-lightbox-src') || el.getAttribute('src') || '';
        const desc = el.getAttribute('data-lightbox-description') || '';

        img.src = src;
        img.alt = el.getAttribute('alt') || 'Evidencia fotográfica';
        caption.textContent = el.getAttribute('data-lightbox-caption') || '';
        description.textContent = desc;
        description.hidden = desc === '';
        meta.textContent = el.getAttribute('data-lightbox-meta') || '';
        counter.textContent = items.length > 1 ? (index + 1) + ' / ' + items.length : '';

        const many = items.length > 1;
        prevBtn.hidden = !many;
        nextBtn.hidden = !many;
        prevBtn.disabled = !many;
        nextBtn.disabled = !many;
    }

    function open(trigger) {
        collect(trigger);
        show(index);
        modal.show();
    }

    document.addEventListener('click', (event) => {
        if (event.target.closest('.photo-delete, [data-bs-dismiss="modal"]')) {
            return;
        }

        const trigger = event.target.closest('[data-lightbox]');
        if (!trigger) {
            return;
        }

        event.preventDefault();
        open(trigger);
    });

    document.addEventListener('keydown', (event) => {
        const trigger = event.target.closest?.('[data-lightbox]');
        if (trigger && (event.key === 'Enter' || event.key === ' ')) {
            event.preventDefault();
            open(trigger);
            return;
        }

        if (!modalEl.classList.contains('show') || items.length < 2) {
            return;
        }

        if (event.key === 'ArrowLeft') {
            event.preventDefault();
            show(index - 1);
        } else if (event.key === 'ArrowRight') {
            event.preventDefault();
            show(index + 1);
        }
    });

    prevBtn?.addEventListener('click', (event) => {
        event.preventDefault();
        show(index - 1);
    });

    nextBtn?.addEventListener('click', (event) => {
        event.preventDefault();
        show(index + 1);
    });

    modalEl.addEventListener('hidden.bs.modal', () => {
        img.removeAttribute('src');
        items = [];
        index = 0;
    });
})();
</script>
