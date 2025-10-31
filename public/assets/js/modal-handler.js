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
        document.addEventListener('hidden.bs.modal', event => {
            this.cleanupModal(event.target);
        }, false);

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
        
        // Remove modal-open class from body
        document.body.classList.remove('modal-open');
        
        // Remove any lingering backdrop
        const backdrops = document.getElementsByClassName('modal-backdrop');
        Array.from(backdrops).forEach(backdrop => backdrop.remove());
        
        // Reset modal styling
        modalElement.style.display = 'none';
        modalElement.setAttribute('aria-hidden', 'true');
        modalElement.removeAttribute('aria-modal');
        modalElement.removeAttribute('role');
        
        // Clear the padding-right added by Bootstrap
        document.body.style.paddingRight = '';
        
        // Remove specific modal classes
        modalElement.classList.remove('show');
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
        // Remove all possible backdrops
        const allBackdrops = document.querySelectorAll('.modal-backdrop, .offcanvas-backdrop');
        allBackdrops.forEach(backdrop => backdrop.remove());

        // Reset body
        document.body.classList.remove('modal-open', 'overflow-hidden');
        document.body.style.paddingRight = '';
    }
}

// Initialize the handler
window.modalHandler = new ModalHandler();