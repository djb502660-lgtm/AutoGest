<div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content confirm-modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmModalTitle">Confirmar acción</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <p class="confirm-modal-message mb-0"></p>
                <div class="confirm-modal-prompt mt-3" hidden>
                    <label class="form-label" for="confirmModalPrompt"></label>
                    <textarea id="confirmModalPrompt" class="form-control" rows="3"></textarea>
                    <div class="invalid-feedback">Este campo es obligatorio.</div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" data-confirm-ok>Confirmar</button>
            </div>
        </div>
    </div>
</div>
<script>
window.AutoGestConfirm = window.AutoGestConfirm || {};

(function () {
    const modalEl = document.getElementById('confirmModal');
    const bootstrapUi = window.bootstrap;
    if (!modalEl || !bootstrapUi?.Modal) {
        return;
    }

    const modal = bootstrapUi.Modal.getOrCreateInstance(modalEl);
    const titleEl = modalEl.querySelector('#confirmModalTitle');
    const messageEl = modalEl.querySelector('.confirm-modal-message');
    const promptWrap = modalEl.querySelector('.confirm-modal-prompt');
    const promptLabel = modalEl.querySelector('.confirm-modal-prompt .form-label');
    const promptInput = modalEl.querySelector('#confirmModalPrompt');
    const okBtn = modalEl.querySelector('[data-confirm-ok]');

    let pending = null;

    function finish(result) {
        if (!pending) {
            return;
        }

        const resolve = pending;
        pending = null;
        modal.hide();
        resolve(result);
    }

    window.AutoGestConfirm.ask = function (options) {
        const opts = options || {};

        titleEl.textContent = opts.title || 'Confirmar acción';
        messageEl.textContent = opts.message || '¿Deseas continuar?';
        okBtn.textContent = opts.confirmLabel || 'Confirmar';
        okBtn.classList.toggle('btn-danger', opts.danger !== false);
        okBtn.classList.toggle('btn-primary', opts.danger === false);

        const needsPrompt = Boolean(opts.promptLabel);
        promptWrap.hidden = !needsPrompt;
        promptInput.value = '';
        promptInput.classList.remove('is-invalid');
        promptInput.required = needsPrompt;
        if (needsPrompt) {
            promptLabel.textContent = opts.promptLabel;
        }

        return new Promise((resolve) => {
            pending = resolve;
            modal.show();
            if (needsPrompt) {
                setTimeout(() => promptInput.focus(), 200);
            } else {
                setTimeout(() => okBtn.focus(), 200);
            }
        });
    };

    okBtn.addEventListener('click', () => {
        if (promptWrap.hidden === false && !promptInput.value.trim()) {
            promptInput.classList.add('is-invalid');
            promptInput.focus();
            return;
        }

        finish({
            prompt: promptWrap.hidden ? null : promptInput.value.trim(),
        });
    });

    modalEl.addEventListener('hidden.bs.modal', () => {
        if (pending) {
            const resolve = pending;
            pending = null;
            resolve(null);
        }
    });

    document.addEventListener('submit', (event) => {
        const form = event.target.closest('form[data-confirm]');
        if (!form) {
            return;
        }

        if (form.dataset.confirmed === '1') {
            delete form.dataset.confirmed;
            return;
        }

        event.preventDefault();

        window.AutoGestConfirm.ask({
            title: form.getAttribute('data-confirm-title') || 'Confirmar acción',
            message: form.getAttribute('data-confirm') || '¿Deseas continuar?',
            confirmLabel: form.getAttribute('data-confirm-label') || 'Confirmar',
            danger: form.getAttribute('data-confirm-danger') !== '0',
            promptLabel: form.getAttribute('data-confirm-prompt') || null,
        }).then((result) => {
            if (!result) {
                return;
            }

            const promptName = form.getAttribute('data-confirm-prompt-name');
            if (promptName && result.prompt != null) {
                let field = form.querySelector('[name="' + promptName + '"]');
                if (!field) {
                    field = document.createElement('input');
                    field.type = 'hidden';
                    field.name = promptName;
                    form.appendChild(field);
                }
                field.value = result.prompt;
            }

            form.dataset.confirmed = '1';
            if (typeof form.requestSubmit === 'function') {
                form.requestSubmit();
            } else {
                form.submit();
            }
        });
    });
})();
</script>
