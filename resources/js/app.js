import './bootstrap';

document.addEventListener('DOMContentLoaded', () => {
    const loadingOverlay = document.createElement('div');
    loadingOverlay.className = 'page-loading-overlay';
    loadingOverlay.setAttribute('aria-hidden', 'true');
    loadingOverlay.innerHTML = [
        '<div class="page-loading-card" role="status" aria-live="polite">',
        '  <span class="page-loading-spinner"></span>',
        '  <div class="page-loading-text-group">',
        '      <p class="page-loading-title">Sedang diproses</p>',
        '      <p class="page-loading-message">Mohon tunggu sebentar...</p>',
        '  </div>',
        '</div>'
    ].join('');

    document.body.appendChild(loadingOverlay);

    const titleElement = loadingOverlay.querySelector('.page-loading-title');
    const messageElement = loadingOverlay.querySelector('.page-loading-message');
    let isLocked = false;

    function lockPage(options = {}) {
        if (isLocked) {
            return;
        }

        isLocked = true;
        titleElement.textContent = options.title || 'Sedang diproses';
        messageElement.textContent = options.message || 'Mohon tunggu sebentar...';
        loadingOverlay.classList.add('is-visible');
        loadingOverlay.setAttribute('aria-hidden', 'false');
        document.body.classList.add('page-loading-active');
    }

    function shouldSkipForm(form) {
        if (form.dataset.skipLoading === 'true') {
            return true;
        }

        const action = (form.getAttribute('action') || '').toLowerCase();

        return action.includes('/export') || action.includes('/id-card');
    }

    function setSubmittingState(form, submitter) {
        const controls = form.querySelectorAll('button[type="submit"], input[type="submit"]');

        controls.forEach((control) => {
            if (control === submitter) {
                return;
            }

            control.disabled = true;
        });

        if (!submitter) {
            return;
        }

        if (!submitter.dataset.originalContent) {
            submitter.dataset.originalContent = submitter.innerHTML;
        }

        submitter.disabled = true;
        submitter.classList.add('is-loading');

        const loadingLabel = submitter.dataset.loadingLabel || 'Memproses...';

        submitter.innerHTML = [
            '<span class="button-loading-spinner" aria-hidden="true"></span>',
            `<span>${loadingLabel}</span>`
        ].join('');
    }

    document.querySelectorAll('form').forEach((form) => {
        form.addEventListener('submit', (event) => {
            if (event.defaultPrevented) {
                return;
            }

            if (isLocked || shouldSkipForm(form)) {
                return;
            }

            const submitter = event.submitter || form.querySelector('button[type="submit"], input[type="submit"]');

            setSubmittingState(form, submitter);
            lockPage({
                title: form.dataset.loadingTitle || 'Sedang diproses',
                message: form.dataset.loadingMessage || 'Mohon tunggu sebentar...'
            });
        });
    });
});
