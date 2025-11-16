/**
 * Global Modal Handler
 * Ensures proper cleanup of modals and backdrops
 */
class ModalHandler {
    constructor() {
        this.init();
    }

    init() {
        // Handle all modal hidden events
        // Run cleanup slightly delayed to avoid racing with Bootstrap's internal DOM updates
        document.addEventListener('hidden.bs.modal', event => {
            setTimeout(() => this.cleanupModal(event.target), 50);
        }, false);

        // Instrumentation: observe backdrop additions/removals and modal show/hide events
        // This is temporary and intended to help trace duplicate backdrop creation.
        try {
            const report = (msg, o) => console.log('[MODAL-HANDLER-TRACE]', msg, o || '');

            // Log modal lifecycle events globally
            ['show.bs.modal', 'shown.bs.modal', 'hide.bs.modal', 'hidden.bs.modal'].forEach(evt => {
                document.addEventListener(evt, function(e) {
                    report(evt, e.target && e.target.id ? e.target.id : e.target);
                }, true);
            });

            // NOTE: removed aggressive show-time cleanup because it can remove
            // Bootstrap's own backdrop before the modal gains the `.show` class.
            // Cleanup will be handled on `hidden.bs.modal` instead.

            // MutationObserver for backdrop nodes
            const mo = new MutationObserver(mutations => {
                mutations.forEach(m => {
                    m.addedNodes.forEach(n => {
                        if (n && n.classList && n.classList.contains('modal-backdrop')) {
                            report('BACKDROP_ADDED', { node: n, total: document.querySelectorAll('.modal-backdrop').length });
                        }
                    });
                    m.removedNodes.forEach(n => {
                        if (n && n.classList && n.classList.contains('modal-backdrop')) {
                            report('BACKDROP_REMOVED', { node: n, total: document.querySelectorAll('.modal-backdrop').length });
                        }
                    });
                });
            });
            mo.observe(document.body, { childList: true });
            window.__modalHandlerMutationObserver = mo;
        } catch (err) {
            console.error('Modal handler instrumentation failed', err);
        }

        // Handle all offcanvas hidden events
        document.addEventListener('hidden.bs.offcanvas', event => {
            this.cleanupOffcanvas(event.target);
        }, false);

        // Handle escape key for any lingering backdrops
        document.addEventListener('keydown', event => {
            if (event.key === 'Escape') {
                this.cleanupAllBackdrops();
            }
        });

        // Additional cleanup on any modal/offcanvas close button click
        document.addEventListener('click', event => {
            if (event.target.matches('[data-bs-dismiss="modal"], [data-bs-dismiss="offcanvas"]')) {
                setTimeout(() => this.cleanupAllBackdrops(), 300);
            }
        });
    }

    cleanupModal(modalElement) {
        if (!modalElement) return;
        // Safer cleanup: do not aggressively remove classes or backdrops.
        // Only remove extra backdrops (if any) and ensure body.modal-open
        // is only removed when no modals remain open.

        const openModals = document.querySelectorAll('.modal.show');
        const backdrops = document.getElementsByClassName('modal-backdrop');

        // If there are more backdrops than open modals, remove extras
        if (backdrops.length > openModals.length) {
            const extra = backdrops.length - openModals.length;
            for (let i = 0; i < extra; i++) {
                const last = document.querySelector('.modal-backdrop:last-of-type');
                if (last) last.remove();
            }
        }

        // If no modals are open, remove modal-open and reset body padding
        if (openModals.length === 0) {
            document.body.classList.remove('modal-open');
            document.body.style.paddingRight = '';
        }
    }

    cleanupOffcanvas(offcanvasElement) {
        if (!offcanvasElement) return;

        // Remove offcanvas-backdrop
        const backdrops = document.getElementsByClassName('offcanvas-backdrop');
        Array.from(backdrops).forEach(backdrop => backdrop.remove());

        // Reset body classes
        document.body.classList.remove('overflow-hidden');
        
        // Clear the padding-right
        document.body.style.paddingRight = '';
    }

    cleanupAllBackdrops() {
        // Safer global cleanup: only remove extra backdrops and clear body when appropriate
        const openModals = document.querySelectorAll('.modal.show');
        const modalBackdrops = document.querySelectorAll('.modal-backdrop');
        const offcanvasBackdrops = document.querySelectorAll('.offcanvas-backdrop');

        // Remove extra modal backdrops if more than open modals
        if (modalBackdrops.length > openModals.length) {
            const extra = modalBackdrops.length - openModals.length;
            for (let i = 0; i < extra; i++) {
                const last = document.querySelector('.modal-backdrop:last-of-type');
                if (last) last.remove();
            }
        }

        // If no modals open, clear modal related body classes
        if (openModals.length === 0) {
            document.body.classList.remove('modal-open');
            document.body.style.paddingRight = '';
        }

        // Remove offcanvas backdrops only if no offcanvas elements are open
        const openOffcanvas = document.querySelectorAll('.offcanvas.show');
        if (offcanvasBackdrops.length > openOffcanvas.length) {
            const extraOc = offcanvasBackdrops.length - openOffcanvas.length;
            for (let i = 0; i < extraOc; i++) {
                const lastOc = document.querySelector('.offcanvas-backdrop:last-of-type');
                if (lastOc) lastOc.remove();
            }
        }

        if (openOffcanvas.length === 0) {
            document.body.classList.remove('overflow-hidden');
        }
    }
}

// Initialize the handler
window.modalHandler = new ModalHandler();