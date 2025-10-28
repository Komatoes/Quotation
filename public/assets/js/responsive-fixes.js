/* Modal improvements and fixes */
document.addEventListener('DOMContentLoaded', function() {
    // Fix for stuck backdrops
    document.querySelectorAll('.modal').forEach(modal => {
        modal.addEventListener('hidden.bs.modal', function() {
            // Remove any stuck backdrops
            document.querySelectorAll('.modal-backdrop').forEach(backdrop => {
                backdrop.remove();
            });
            // Re-enable scrolling if no modals are visible
            if (!document.querySelector('.modal.show')) {
                document.body.classList.remove('modal-open');
                document.body.style.paddingRight = '';
            }
        });
    });

    // Fix for scrolling in nested modals
    document.querySelectorAll('[data-bs-toggle="modal"]').forEach(trigger => {
        trigger.addEventListener('click', function() {
            const currentModal = this.closest('.modal');
            if (currentModal) {
                currentModal.style.display = 'none';
                currentModal.setAttribute('data-bs-backdrop', 'false');
            }
        });
    });

    // Restore parent modal when nested modal is closed
    document.querySelectorAll('.modal').forEach(modal => {
        modal.addEventListener('hidden.bs.modal', function() {
            const parentModal = document.querySelector('.modal[data-bs-backdrop="false"]');
            if (parentModal) {
                parentModal.style.display = '';
                parentModal.setAttribute('data-bs-backdrop', 'true');
            }
        });
    });

    // Improve search bars
    document.querySelectorAll('.search-input').forEach(input => {
        const wrapper = document.createElement('div');
        wrapper.className = 'search-wrapper position-relative';
        input.parentNode.insertBefore(wrapper, input);
        wrapper.appendChild(input);
        
        const icon = document.createElement('i');
        icon.className = 'ti ti-search search-icon';
        wrapper.insertBefore(icon, input);
    });
});

// Sidebar navigation improvements
function initializeSidebar() {
    const sidebarLinks = document.querySelectorAll('.sidebar .menu-link');
    
    sidebarLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            const href = this.getAttribute('href');
            if (href === 'javascript:void(0);') return;

            if (window.location.pathname === '/') {
                // On homepage, scroll to section
                e.preventDefault();
                const targetId = href.split('/').pop();
                const targetElement = document.getElementById(targetId);
                if (targetElement) {
                    targetElement.scrollIntoView({ behavior: 'smooth' });
                }
            } else if (!href.startsWith('javascript:')) {
                // Not on homepage, navigate and then scroll
                sessionStorage.setItem('scrollTarget', href.split('/').pop());
            }
        });
    });

    // Check for scroll target on page load
    const scrollTarget = sessionStorage.getItem('scrollTarget');
    if (scrollTarget) {
        sessionStorage.removeItem('scrollTarget');
        const targetElement = document.getElementById(scrollTarget);
        if (targetElement) {
            setTimeout(() => {
                targetElement.scrollIntoView({ behavior: 'smooth' });
            }, 500);
        }
    }
}

// Table responsiveness improvements
function initializeResponsiveTables() {
    document.querySelectorAll('.table-responsive').forEach(wrapper => {
        const table = wrapper.querySelector('table');
        if (!table) return;

        // Add horizontal scroll indicators
        const scrollIndicator = document.createElement('div');
        scrollIndicator.className = 'scroll-indicator';
        wrapper.appendChild(scrollIndicator);

        wrapper.addEventListener('scroll', function() {
            const maxScroll = this.scrollWidth - this.clientWidth;
            const scrollPercent = (this.scrollLeft / maxScroll) * 100;
            scrollIndicator.style.width = `${scrollPercent}%`;
        });

        // Make sure action columns stay visible
        const actionCells = table.querySelectorAll('td:last-child, th:last-child');
        actionCells.forEach(cell => {
            cell.style.position = 'sticky';
            cell.style.right = '0';
            cell.style.backgroundColor = 'var(--bs-body-bg)';
            cell.style.zIndex = '1';
        });
    });
}