/* Modal improvements and fixes */
document.addEventListener('DOMContentLoaded', function() {
    // NOTE: Removed per-modal hidden handlers that duplicated backdrop cleanup.
    // The centralized `modal-handler.js` is the authoritative source for backdrop
    // and body class cleanup to avoid races when nested modals are used.

    // Nested modals: do not hide parent modals or toggle backdrop attributes.
    // Bootstrap 5 handles nested modals/backdrop stacking. Avoid manual DOM hacks here.

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