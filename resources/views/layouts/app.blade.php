<!doctype html>
<html lang="en" data-bs-theme="light">

<head>
    @include('include.head')
</head>

<body class="layout-navbar-fixed layout-menu-fixed layout-compact">

    <!-- Layout wrapper -->
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">

            <!-- Sidebar -->
            <aside id="layout-menu" class="layout-menu menu-vertical bg-menu-theme">
                <div class="app-brand">
                    <a href="{{ url('/') }}" class="app-brand-link">
                        <span class="app-brand-text fw-bold">My App</span>
                    </a>
                </div>

                <ul class="menu-inner py-1">

                    <!-- Quotation -->
                    <li class="menu-item">
                        <a href="javascript:void(0);" class="menu-link menu-toggle">
                            <i class="menu-icon ti ti-file-text"></i>
                            <div>Quotations</div>
                        </a>
                        <ul class="menu-sub">
                            <li class="menu-item">
                                <button class="btn btn-primary" data-bs-toggle="offcanvas"
                                    data-bs-target="#add-new-quotation">
                                    <i class="ti ti-plus me-1"></i> Create Quotation
                                </button>
                            </li>

                            <li class="menu-item"><a href="{{ url('/quotations/drafts') }}" class="menu-link">Drafts</a>
                            </li>
                            <li class="menu-item"><a href="{{ url('/quotations/archives') }}"
                                    class="menu-link">Archives</a></li>
                        </ul>
                    </li>

                    <!-- Projects -->
                    <li class="menu-item">
                        <a href="javascript:void(0);" class="menu-link menu-toggle">
                            <i class="menu-icon ti ti-briefcase"></i>
                            <div>Projects</div>
                        </a>
                        <ul class="menu-sub">
                            <li class="menu-item"><a href="{{ url('/projects/current') }}" class="menu-link">Current
                                    Projects</a></li>
                            <li class="menu-item"><a href="{{ url('/projects/drafts') }}" class="menu-link">Drafts</a>
                            </li>
                            <li class="menu-item"><a href="{{ url('/projects/archives') }}"
                                    class="menu-link">Archives</a></li>
                        </ul>
                    </li>

                    <!-- Material List -->
                    <li class="menu-item">
                        <a href="{{ url('/materials') }}" class="menu-link">
                            <i class="menu-icon ti ti-list"></i>
                            <div>Material List</div>
                        </a>
                    </li>

                </ul>
            </aside>


            <!-- Layout container -->
            <div class="layout-page">

                <!-- Navbar -->
                <nav class="layout-navbar navbar navbar-expand-xl align-items-center bg-navbar-theme">
                    <div class="container-fluid">
                        <a href="{{ url('/') }}" class="navbar-brand">My App</a>
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
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body flex-grow-1">
            <form class="row g-2" id="form-add-quotation" onsubmit="return false">

                <!-- Quotation Info -->
                <div class="col-sm-12">
                    <label class="form-label">Subject</label>
                    <input type="text" name="subject" class="form-control" placeholder="Renovation Project" required>
                </div>

                <div class="col-sm-12">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="3" placeholder="Details about the quotation"></textarea>
                </div>

                <!-- Client Info (fields instead of dropdown) -->
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
            headers: { "X-CSRF-TOKEN": '{{ csrf_token() }}' },
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    title: data.message,
                    icon: "success"
                }).then(() => {
                    // redirect to quotation details page with ID
                    window.location.href = "/quotations/" + data.quotation.id;
                });
            } else {
                Swal.fire("Failed to create quotation", "", "error");
            }
        })
        .catch(error => {
            console.error("Error:", error);
            Swal.fire("Something went wrong!", "", "error");
        });
    }
}
const addQuotation = new AddQuotation();
</script>



<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
