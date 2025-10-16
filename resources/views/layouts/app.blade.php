    <!doctype html>
    <html lang="en" data-bs-theme="light">
    <style>
        /* ===== Overlay Drawer Sidebar (Mobile) ===== */

        .sidebar-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.3s ease, visibility 0.3s ease;
            z-index: 1040;
        }

        .sidebar-overlay.active {
            opacity: 1;
            visibility: visible;
        }

        .mobile-sidebar {
            position: fixed;
            top: 0;
            left: -260px;
            width: 260px;
            height: 100%;
            background: var(--bs-body-bg);
            box-shadow: 2px 0 8px rgba(0, 0, 0, 0.2);
            overflow-y: auto;
            transition: left 0.3s ease;
            z-index: 1050;
        }

        .mobile-sidebar.active {
            left: 0;
        }
    </style>

    <head>
        @include('include.head')
    </head>

    <body class="layout-navbar-fixed layout-menu-fixed layout-compact">

        <!-- Layout wrapper -->
        <div class="layout-wrapper layout-content-navbar">
            <div class="layout-container">


                <!-- Sidebar for desktop -->
                <aside id="layout-menu" class="layout-menu menu-vertical bg-menu-theme d-none d-xl-block">
                    @include('layouts.sidebar')
                </aside>
                <!-- Offcanvas Sidebar for mobile (Bootstrap) -->
                <div class="offcanvas offcanvas-start d-xl-none" tabindex="-1" id="mobileSidebar" aria-labelledby="mobileSidebarLabel">
                    <div class="offcanvas-header">
                        <h5 class="offcanvas-title" id="mobileSidebarLabel">Menu</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                    </div>
                    <div class="offcanvas-body">
                        @include('layouts.sidebar', ['mobile' => true])
                    </div>
                </div>



                <!-- Layout container -->
                <div class="layout-page">

                    <!-- Navbar -->
                    <nav class="layout-navbar navbar navbar-expand-xl align-items-center bg-navbar-theme">
                        <div class="container-fluid">
                            <a href="{{ url('/') }}" class="navbar-brand">My App</a>
                            <!-- Mobile sidebar toggle -->
                            <button class="btn btn-outline-primary d-xl-none ms-2" type="button"
                                data-bs-toggle="offcanvas" data-bs-target="#mobileSidebar"
                                aria-controls="mobileSidebar">
                                <i class="ti ti-menu-2"></i>
                            </button>

                            <ul class="navbar-nav ms-auto">
                                <li class="nav-item dropdown">
                                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                                        <img src="{{ asset('assets/img/avatars/1.png') }}" class="rounded-circle"
                                            width="30">
                                        <span class="ms-2">Hello, User</span>
                                    </a>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li><a class="dropdown-item" href="#">Profile</a></li>
                                        <li><a class="dropdown-item" href="/logout-user">Logout</a></li>
                                    </ul>
                                </li>
                            </ul>
                        </div>
                    </nav>

                    <!-- Content wrapper -->
                    <div class="content-wrapper">
                        @yield('content')
                    </div>

                </div>
                <!-- /Layout page -->

            </div>
            <!-- /Layout container -->
        </div>
        <!-- /Layout wrapper -->

        @include('include.scripts')


        <!-- Offcanvas: Create Quotation -->
        <div class="offcanvas offcanvas-end" id="add-new-quotation">
            <div class="offcanvas-header border-bottom">
                <h5 class="offcanvas-title">Add Quotation</h5>
                <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas"
                    aria-label="Close"></button>
            </div>
            <div class="offcanvas-body flex-grow-1">
                <form class="row g-2" id="form-add-quotation" onsubmit="return false">

                    <!-- Quotation Info -->
                    <div class="col-sm-12">
                        <label class="form-label">Subject</label>
                        <input type="text" name="subject" class="form-control" placeholder="Renovation Project"
                            required>
                    </div>

                    <div class="col-sm-12">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Details about the quotation"></textarea>
                    </div>

                    <div class="col-sm-6">
                        <label class="form-label">Client First Name</label>
                        <input type="text" name="client_first_name" class="form-control" placeholder="John" required>
                    </div>

                    <div class="col-sm-6">
                        <label class="form-label">Client Last Name</label>
                        <input type="text" name="client_last_name" class="form-control" placeholder="Doe" required>
                    </div>

                    <div class="col-sm-6">
                        <label class="form-label">Contact No</label>
                        <input type="text" name="client_contact_no" class="form-control" placeholder="09123456789"
                            required>
                    </div>

                    <div class="col-sm-12">
                        <label class="form-label">Address</label>
                        <textarea name="client_address" class="form-control" rows="2" placeholder="123 Main St, City" required></textarea>
                    </div>

                    <!-- Buttons -->
                    <div class="col-sm-12">
                        <button type="submit" class="btn btn-primary me-2"
                            onclick="addQuotation.add('form-add-quotation')">Save</button>
                        <button type="reset" class="btn btn-outline-secondary"
                            data-bs-dismiss="offcanvas">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </body>

    </html>

    <script>
        class AddQuotation {
            add(id) {
                const form = document.getElementById(id);
                const formData = new FormData(form);
                fetch("/add-quotation", {
                        method: "POST",
                        headers: {
                            "X-CSRF-TOKEN": '{{ csrf_token() }}',
                            "Accept": "application/json"
                        },
                        body: formData
                    })
                    .then(async res => {
                        const data = await res.json();
                        return {
                            ok: res.ok,
                            data
                        };
                    })
                    .then(({
                        ok,
                        data
                    }) => {
                        if (!ok) {
                            // 🧠 Laravel validation errors
                            if (data.errors) {
                                const messages = Object.values(data.errors)
                                    .flat()
                                    .map(msg => `<li>${msg}</li>`)
                                    .join("");
                                Swal.fire({
                                    title: "Validation Error",
                                    html: `<ul style='text-align:left; margin:0; padding-left:1.5em;'>${messages}</ul>`,
                                    icon: "warning"
                                });
                            } else {
                                Swal.fire({
                                    title: "Error",
                                    text: data.message || "Failed to create quotation.",
                                    icon: "error"
                                });
                            }
                            return;
                        }
                        // ✅ Success
                        Swal.fire({
                            title: data.message || "Quotation created successfully!",
                            icon: "success"
                        }).then(() => {
                            window.location.href = "/quotations/" + data.quotation_id;
                        });
                    })
                    .catch(error => {
                        console.error("Error:", error);
                        Swal.fire({
                            title: "Something went wrong!",
                            text: "Please try again later.",
                            icon: "error"
                        });
                    });
            }
        }
        const addQuotation = new AddQuotation();

        // Quotation modal open for desktop
        document.addEventListener("DOMContentLoaded", () => {
            const btnAddQuotation = document.getElementById("btn-add-quotation");
            if (btnAddQuotation) {
                btnAddQuotation.addEventListener("click", () => {
                    const offcanvasEl = document.getElementById("add-new-quotation");
                    const offcanvas = bootstrap.Offcanvas.getOrCreateInstance(offcanvasEl);
                    offcanvas.show();
                });
            }
            // Quotation modal open for mobile
            const btnAddQuotationMobile = document.getElementById("btn-add-quotation-mobile");
            if (btnAddQuotationMobile) {
                btnAddQuotationMobile.addEventListener("click", () => {
                    // Close sidebar first
                    const sidebar = document.getElementById("mobileSidebar");
                    const sidebarInstance = bootstrap.Offcanvas.getOrCreateInstance(sidebar);
                    sidebarInstance.hide();
                    // Open quotation modal after sidebar closes
                    setTimeout(() => {
                        const offcanvasEl = document.getElementById("add-new-quotation");
                        const offcanvas = bootstrap.Offcanvas.getOrCreateInstance(offcanvasEl);
                        offcanvas.show();
                    }, 350);
                });
            }
        });
    </script>


    <script>
    // Ensure only one Bootstrap offcanvas backdrop is present
    document.addEventListener("shown.bs.offcanvas", function () {
        // Remove extra backdrops if any
        const backdrops = document.querySelectorAll('.offcanvas-backdrop');
        if (backdrops.length > 1) {
            for (let i = 1; i < backdrops.length; i++) {
                backdrops[i].parentNode.removeChild(backdrops[i]);
            }
        }
    });
    </script>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
