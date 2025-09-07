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
                            <li class="menu-item"><a href="{{ url('/quotations/create') }}" class="menu-link">Create
                                    Quotation</a></li>
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

</body>

</html>
