{{-- Include Font Awesome and SweetAlert2 --}}
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="app-brand" style="padding: 50px 0; border-bottom: 1px solid rgba(0,0,0,0.1); text-align: center;">
    <a href="{{ url('/') }}" class="app-brand-link d-flex align-items-center justify-content-center w-100">
        <img src="{{ asset('Image/LOGO.png') }}" alt="Company Logo"
            style="max-width: 220px; height: auto; object-fit: contain; display: block; margin: 0 auto;"
            onerror="this.onerror=null; this.alt='Logo not found';">
    </a>
</div>

<ul class="menu-inner py-1">
    <!-- Quotations -->
    <li class="menu-item">
        <a href="javascript:void(0);" class="menu-link menu-toggle">
            <i class="fa-solid fa-file-invoice-dollar menu-icon"></i>
            <div>Quotations</div>
        </a>
        <ul class="menu-sub">
            <li class="menu-item">
                <a href="javascript:void(0);" class="menu-link"
                    id="{{ isset($mobile) && $mobile ? 'btn-add-quotation-mobile' : 'btn-add-quotation' }}">
                    <i class="fa-solid fa-circle-plus sub-icon"></i>
                    Create Quotation
                </a>
            </li>
            <li class="menu-item">
                <a href="javascript:void(0);" class="menu-link scroll-to-section" data-target="draft-quotations">
                    <i class="fa-solid fa-pencil sub-icon"></i>
                    Drafts
                </a>
            </li>
        </ul>
    </li>

    <!-- Projects (no dropdown) -->
    <li class="menu-item">
        <a href="javascript:void(0);" class="menu-link scroll-to-section" data-target="current-projects">
            <i class="fa-solid fa-diagram-project menu-icon"></i>
            <div>Projects</div>
        </a>
    </li>

    <!-- Archives -->
    <li class="menu-item">
        <a href="javascript:void(0);" class="menu-link scroll-to-section" data-target="archived-projects">
            <i class="fa-solid fa-box-archive menu-icon"></i>
            <div>Archives</div>
        </a>
    </li>

    <!-- Material List -->
    <li class="menu-item">
        <a href="javascript:void(0);" class="menu-link scroll-to-section" data-target="material-list">
            <i class="fa-solid fa-list-check menu-icon"></i>
            <div>Material List</div>
        </a>
    </li>
</ul>

<!-- Sign Out -->
<div style="flex: 1 1 auto;"></div>
<form id="logoutForm" method="POST" action="{{ route('logout') }}"
    style="position: absolute; bottom: 0; left: 0; width: 100%; 
             border-top: 1px solid rgba(255,255,255,0.15); background: transparent; padding: 15px 0; margin: 0;">
    @csrf
    <button type="button" id="logoutBtn" class="menu-link d-flex align-items-center justify-content-center"
        style="color: #ffffff; width: 100%; border: none; background: none; padding: 10px 15px; text-align: left; font-size: 1.08rem;">
        <i class="fa-solid fa-right-from-bracket menu-icon"></i>
        <div>Sign Out</div>
    </button>
</form>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // 🔹 Handle logout
        const logoutBtn = document.getElementById('logoutBtn');
        if (logoutBtn) {
            logoutBtn.addEventListener('click', function() {
                const mobileSidebar = document.getElementById('mobileSidebar');

                function showLogoutSwal() {
                    Swal.fire({
                        title: 'Confirm Sign Out',
                        text: 'Are you sure you want to sign out?',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#42955c',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Yes, sign out',
                        cancelButtonText: 'Cancel',
                        backdrop: true,
                        allowOutsideClick: false,
                        didOpen: () => {
                            document.querySelectorAll(
                                    '.swal2-container, .swal2-backdrop-show')
                                .forEach(el => {
                                    el.style.zIndex = '99999';
                                    el.style.position = 'fixed';
                                });
                        }
                    }).then(result => {
                        if (result.isConfirmed) {
                            document.getElementById('logoutForm').submit();
                        }
                    });
                }

                if (mobileSidebar && mobileSidebar.classList.contains('show')) {
                    const sidebarInstance = bootstrap.Offcanvas.getOrCreateInstance(mobileSidebar);
                    const handler = function() {
                        showLogoutSwal();
                        mobileSidebar.removeEventListener('hidden.bs.offcanvas', handler);
                    };
                    mobileSidebar.addEventListener('hidden.bs.offcanvas', handler);
                    sidebarInstance.hide();
                } else {
                    showLogoutSwal();
                }
            });
        }

        // 🔹 Handle scroll-to-section links
        document.querySelectorAll('.scroll-to-section').forEach(link => {
            link.addEventListener('click', e => {
                e.preventDefault();
                const targetId = link.getAttribute('data-target');
                const targetSection = document.getElementById(targetId);

                if (window.location.pathname === '/dashboard') {
                    // Already on dashboard, scroll directly
                    if (targetSection) {
                        const elementRect = targetSection.getBoundingClientRect();
                        const absoluteElementTop = elementRect.top + window.pageYOffset;
                        const middleOfViewport = window.innerHeight / 2;
                        const elementMiddle = elementRect.height / 2;
                        const scrollToPosition = absoluteElementTop - middleOfViewport +
                            elementMiddle - 50; // offset 50px

                        window.scrollTo({
                            top: scrollToPosition,
                            behavior: 'smooth'
                        });
                    }
                } else {
                    // Not on dashboard: save and redirect
                    localStorage.setItem('scrollTo', targetId);
                    window.location.href = '/dashboard';
                }
            });
        });


        // 🔹 Scroll to section if redirected
        const scrollTo = localStorage.getItem('scrollTo');
        if (scrollTo && window.location.pathname === '/dashboard') {
            setTimeout(() => {
                const el = document.getElementById(scrollTo);
                if (el) {
                    const elementRect = el.getBoundingClientRect();
                    const absoluteElementTop = elementRect.top + window.pageYOffset;
                    const middleOfViewport = window.innerHeight / 2;
                    const elementMiddle = elementRect.height / 2;
                    const scrollToPosition = absoluteElementTop - middleOfViewport + elementMiddle -
                        50; // offset 50px

                    window.scrollTo({
                        top: scrollToPosition,
                        behavior: 'smooth'
                    });
                }
                localStorage.removeItem('scrollTo');
            }, 700);
        }

    });
</script>

<style>
    /* SweetAlert top priority */
    .swal2-container {
        z-index: 99999 !important;
    }

    .swal2-backdrop-show {
        z-index: 99998 !important;
    }

    /* Sidebar icons & links */
    .menu-icon,
    .sub-icon {
        width: 22px;
        text-align: center;
        margin-right: 10px;
        color: #ffffff;
        transition: color 0.2s ease, transform 0.2s ease;
    }

    .menu-link {
        color: #ffffff;
        display: flex;
        align-items: center;
        padding: 10px 15px;
        border-radius: 6px;
        text-decoration: none;
    }

    .menu-link:hover {
        background-color: rgba(255, 255, 255, 0.1);
    }

    .menu-link:hover .menu-icon,
    .menu-link:hover .sub-icon {
        color: #42955c !important;
        transform: scale(1.1);
    }

    .menu-item.active>.menu-link .menu-icon {
        color: #42955c !important;
    }
</style>
