import './bootstrap';
import { Livewire } from '../../vendor/livewire/livewire/dist/livewire.esm';

let isLocked = false;

function ensureLoadingOverlay() {
    let loadingOverlay = document.querySelector('.page-loading-overlay');

    if (!loadingOverlay) {
        loadingOverlay = document.createElement('div');
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
    }

    return loadingOverlay;
}

function lockPage(options = {}) {
    if (isLocked) {
        return;
    }

    const loadingOverlay = ensureLoadingOverlay();
    const titleElement = loadingOverlay.querySelector('.page-loading-title');
    const messageElement = loadingOverlay.querySelector('.page-loading-message');

    isLocked = true;
    titleElement.textContent = options.title || 'Sedang diproses';
    messageElement.textContent = options.message || 'Mohon tunggu sebentar...';
    loadingOverlay.classList.add('is-visible');
    loadingOverlay.setAttribute('aria-hidden', 'false');
    document.body.classList.add('page-loading-active');
}

function unlockPage() {
    const loadingOverlay = document.querySelector('.page-loading-overlay');

    isLocked = false;
    document.body.classList.remove('page-loading-active');

    if (!loadingOverlay) {
        return;
    }

    loadingOverlay.classList.remove('is-visible');
    loadingOverlay.setAttribute('aria-hidden', 'true');
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

function initializePageLoading() {
    ensureLoadingOverlay();
    unlockPage();
}

if (!window.__samawarunPageLoadingInitialized) {
    document.addEventListener('submit', (event) => {
        const form = event.target;

        if (!(form instanceof HTMLFormElement)) {
            return;
        }

        if (event.defaultPrevented || isLocked || shouldSkipForm(form)) {
            return;
        }

        const submitter = event.submitter || form.querySelector('button[type="submit"], input[type="submit"]');

        setSubmittingState(form, submitter);
        lockPage({
            title: form.dataset.loadingTitle || 'Sedang diproses',
            message: form.dataset.loadingMessage || 'Mohon tunggu sebentar...'
        });
    });

    document.addEventListener('livewire:navigated', () => {
        initializePageLoading();
    });

    window.__samawarunPageLoadingInitialized = true;
}

initializePageLoading();

Livewire.start();
