/**
 * BrainFlow — Alpine.js bootstrap
 *
 * Questo file viene caricato PRIMA di Alpine.js (che usa defer).
 * Tutti i componenti Alpine sono registrati dentro l'evento 'alpine:init',
 * che Alpine spara prima di processare il DOM.
 */

// ── Toast manager ─────────────────────────────────────────────────────────────

document.addEventListener('alpine:init', () => {
    Alpine.data('toastManager', () => ({
        toasts: [],

        addToast({ type = 'success', message = '', duration = 3500 }) {
            const id = Date.now();
            this.toasts.push({ id, type, message });
            setTimeout(() => this.removeToast(id), duration);
        },

        removeToast(id) {
            this.toasts = this.toasts.filter(t => t.id !== id);
        },
    }));
});

// ── CSRF helper ───────────────────────────────────────────────────────────────

function csrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.content || '';
}

/**
 * Wrapper per fetch() con CSRF e JSON automatici.
 * Esempio: apiFetch('/tasks/1', 'PATCH', { title: 'Nuovo titolo' })
 */
async function apiFetch(url, method = 'GET', body = null) {
    const opts = {
        method,
        headers: {
            'Content-Type':     'application/json',
            'Accept':           'application/json',
            'X-CSRF-TOKEN':     csrfToken(),
            'X-Requested-With': 'XMLHttpRequest',
        },
    };
    if (body) opts.body = JSON.stringify(body);
    const res = await fetch(url, opts);
    if (!res.ok) throw new Error(`HTTP ${res.status}`);
    return res.json().catch(() => null);
}

// ── Sortable re-init after Alpine re-renders ──────────────────────────────────

/**
 * Chiamato dal componente projectBoard dopo ogni cambiamento di colonne.
 * Usa MutationObserver per rilevare nuove .column__body e inizializzare Sortable.
 */
function watchBoardForNewColumns(boardEl, onEnd) {
    if (typeof Sortable === 'undefined') return;

    const initialized = new WeakSet();

    function initEl(el) {
        if (initialized.has(el)) return;
        initialized.add(el);
        Sortable.create(el, {
            group:      'tasks',
            animation:  150,
            ghostClass: 'is-dragging',
            onEnd,
        });
    }

    boardEl.querySelectorAll('.column__body').forEach(initEl);

    const obs = new MutationObserver(() => {
        boardEl.querySelectorAll('.column__body').forEach(initEl);
    });
    obs.observe(boardEl, { childList: true, subtree: true });

    return obs;
}

// ── Utility: string → deterministic color ─────────────────────────────────────

function stringToColor(str) {
    const palette = [
        '#6366f1','#8b5cf6','#ec4899','#f43f5e',
        '#f97316','#22c55e','#14b8a6','#3b82f6',
        '#06b6d4','#a855f7','#d946ef','#84cc16',
    ];
    let h = 0;
    for (const c of (str || '')) h = c.charCodeAt(0) + ((h << 5) - h);
    return palette[Math.abs(h) % palette.length];
}

// ── Utility: user initials ────────────────────────────────────────────────────

function userInitials(name) {
    if (!name) return '?';
    return name.split(' ').map(p => p[0].toUpperCase()).join('').slice(0, 2);
}

// ── Utility: date formatting (IT) ────────────────────────────────────────────

function fmtDate(d) {
    if (!d) return '';
    return new Date(d).toLocaleDateString('it-IT', { day: '2-digit', month: 'short' });
}

function fmtRelative(d) {
    if (!d) return '';
    const diff = (Date.now() - new Date(d)) / 1000;
    if (diff < 60)    return 'adesso';
    if (diff < 3600)  return `${Math.floor(diff / 60)} min fa`;
    if (diff < 86400) return `${Math.floor(diff / 3600)} h fa`;
    return `${Math.floor(diff / 86400)} g fa`;
}
