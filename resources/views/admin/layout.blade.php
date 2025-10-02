<!DOCTYPE html>
<html lang="en" dir="ltr" data-startbar="light" data-bs-theme="light">

<head>
    <meta charset="utf-8" />
    <title>Swaai | Web - Portal</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta content="Swaai B2C Web Platform" name="description" />
    <meta content="" name="author" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />

    <!-- App favicon -->

  <link rel="shortcut icon" href="{{ url('public/assets/images/favicon.ico') }}">
    <link rel="stylesheet" href="{{ url('public/assets/css/jsvectormap.min.css') }}">
    <link rel="stylesheet" href="{{ url('public/assets/css/style.css') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- App css -->
    <link rel="stylesheet" href="{{ url('public/assets/css/bootstrap.min.css') }}" type="text/css" />
    <link rel="stylesheet" href="{{ url('public/assets/css/icons.min.css') }}" type="text/css" />
    <link rel="stylesheet" href="{{ url('public/assets/css/app.min.css') }}" type="text/css" />

</head>

<body>

    <!-- Top Bar Start -->
    <div class="topbar d-print-none">
        <div class="container-xxl">
            <nav class="topbar-custom d-flex justify-content-between" id="topbar-custom">


                <ul class="topbar-item list-unstyled d-inline-flex align-items-center mb-0">
                    <li>
                        <button class="nav-link mobile-menu-btn nav-icon" id="togglemenu">
                            <i class="iconoir-menu-scale"></i>
                        </button>
                    </li>
                    <li class="mx-3 welcome-text">
                        <h3 class="mb-0 fw-bold text-truncate">
                            Welcome {{ auth()->user()->first_name . ' ' . auth()->user()->last_name ?? 'Add a name' }}
                        </h3>
                    </li>
                </ul>
                <ul class="topbar-item list-unstyled d-inline-flex align-items-center mb-0">
                    {{-- <li class="topbar-item">
                        <a class="nav-link nav-icon" href="javascript:void(0);" id="light-dark-mode">
                            <i class="icofont-moon dark-mode"></i>
                            <i class="icofont-sun light-mode"></i>
                        </a>
                    </li> --}}

                    <li class="dropdown topbar-item">
                        <a class="nav-link dropdown-toggle arrow-none nav-icon" data-bs-toggle="dropdown" href="#"
                            role="button" aria-haspopup="false" aria-expanded="false">
                            <img src="{{ url('public/assets/images/user-avatar.jpg') }}" alt=""
                                class="thumb-lg rounded-circle">
                        </a>
                        <div class="dropdown-menu dropdown-menu-end py-0">
                            <div class="d-flex align-items-center dropdown-item py-2 bg-secondary-subtle">
                                <div class="flex-shrink-0">
                                    <img src="{{ url('public/assets/images/user-avatar.jpg') }}" alt=""
                                        class="thumb-md rounded-circle">
                                </div>
                                <div class="flex-grow-1 ms-2 text-truncate align-self-center">
                                    <h6 class="my-0 fw-medium text-dark fs-13">{{ auth()->user()->first_name }}</h6>
                                    <small
                                        class="text-muted mb-0">Admin</small>
                                </div><!--end media-body-->
                            </div>
                            <div class="dropdown-divider mt-0"></div>
                            <small class="text-muted px-2 pb-1 d-block">Account</small>
                            <!--<a class="dropdown-item" href=""><i-->
                            <!--        class="las la-user fs-18 me-1 align-text-bottom"></i> Profile</a>-->
                            <div class="dropdown-divider mb-0"></div>
                            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="dropdown-item text-danger">
                                    <i class="las la-power-off fs-18 me-1 align-text-bottom"></i> Logout
                                </button>
                            </form>
                        </div>
                    </li>
                </ul><!--end topbar-nav-->
            </nav>
            <!-- end navbar-->
        </div>
    </div>
    <!-- Top Bar End -->
    <!-- leftbar-tab-menu -->
    <div class="startbar d-print-none">
        <!--start brand-->
        <div class="brand">
            <a href="{{ route('admin.dashboard') }}" class="logo">
                <span>
    @if (!empty($settings) && !empty($settings->logo) && file_exists(public_path('media/setting/' . $settings->logo)))
        <img src="{{ asset('public/media/setting/' . $settings->logo) }}" alt="logo" class="logo-sm"
             style="height:100px; width:100px;">
    @else
        <img src="{{ asset('public/media/web-hero2.png') }}" alt="logo" class="logo-sm"
             style="height:100px; width:100px;">
    @endif
</span>
            </a>
        </div>
        <!--end brand-->
        <!--start startbar-menu-->
        <div class="startbar-menu">
            <div class="startbar-collapse" id="startbarCollapse" data-simplebar>
                <div class="d-flex align-items-start flex-column w-100">
                    <!-- Navigation -->
                    <ul class="navbar-nav mb-auto w-100">
                        <li class="menu-label pt-0 mt-0">
                            <span>Main Menu</span>
                        </li>

                        <!-- Dashboards -->

                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"
                                href="#sidebarDashboards" data-bs-toggle="collapse" role="button"
                                aria-expanded="{{ request()->routeIs('admin.dashboard') ? 'true' : 'false' }}"
                                aria-controls="sidebarDashboards">
                                <i class="iconoir-home-simple menu-icon"></i>
                                <span>Dashboards</span>
                            </a>
                            <div class="collapse {{ request()->routeIs('admin.dashboard') ? 'show' : '' }}"
                                id="sidebarDashboards">
                                <ul class="nav flex-column">
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"
                                            href="{{ route('admin.dashboard') }}">Analytics</a>
                                    </li>
                                </ul>
                            </div>
                        </li>

                        <!-- Users -->
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('users.index') ? 'active' : '' }}"
                                href="#sidebarApplications" data-bs-toggle="collapse" role="button"
                                aria-expanded="{{ request()->routeIs('users.index') ? 'true' : 'false' }}"
                                aria-controls="sidebarApplications">
                                <i class="fas fa-id-badge menu-icon"></i>
                                <span>Users</span>
                            </a>
                            <div class="collapse {{ request()->routeIs('users.index') ? 'show' : '' }}"
                                id="sidebarApplications">
                                <ul class="nav flex-column">
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->routeIs('users.index') ? 'active' : '' }}"
                                            href="{{ route('users.index') }}">All Users</a>
                                    </li>
                                    {{-- <li class="nav-item">
                                            <a class="nav-link {{ request()->routeIs('users.create') ? 'active' : '' }}"
                                                href="{{ route('users.create') }}">Add User</a>
                                        </li> --}}
                                </ul>
                            </div>
                        </li>

                        <!-- Deals -->
                        @php
                            $categoryRoutes = ['categories.index', 'categories.create', 'categories.edit'];
                        @endphp

                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs(...$categoryRoutes) ? 'active' : '' }}"
                                href="#sidebarProjects" data-bs-toggle="collapse" role="button"
                                aria-expanded="{{ request()->routeIs(...$categoryRoutes) ? 'true' : 'false' }}"
                                aria-controls="sidebarProjects">
                                <i class="fas fa-suitcase menu-icon"></i>
                                <span>Categories</span>
                            </a>

                            <div class="collapse {{ request()->routeIs(...$categoryRoutes) ? 'show' : '' }}"
                                id="sidebarProjects">
                                <ul class="nav flex-column">
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->routeIs('categories.index') ? 'active' : '' }}"
                                            href="{{ route('categories.index') }}">All Categories</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->routeIs('categories.create') ? 'active' : '' }}"
                                            href="{{ route('categories.create') }}">Add Categories</a>
                                    </li>
                                </ul>
                            </div>
                        </li>


                        @php
                            $businessRoutes = ['businesstypes.index', 'businesstypes.create', 'businesstypes.edit'];
                        @endphp

                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs(...$businessRoutes) ? 'active' : '' }}"
                                href="#sidebarBusinessTypes" data-bs-toggle="collapse" role="button"
                                aria-expanded="{{ request()->routeIs(...$businessRoutes) ? 'true' : 'false' }}"
                                aria-controls="sidebarBusinessTypes">
                                <i class="fas fa-building menu-icon"></i>
                                <span>Business Types</span>
                            </a>

                            <div class="collapse {{ request()->routeIs(...$businessRoutes) ? 'show' : '' }}"
                                id="sidebarBusinessTypes">
                                <ul class="nav flex-column">
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->routeIs('businesstypes.index') ? 'active' : '' }}"
                                            href="{{ route('businesstypes.index') }}">All Business Types</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->routeIs('businesstypes.create') ? 'active' : '' }}"
                                            href="{{ route('businesstypes.create') }}">Add Business Types</a>
                                    </li>
                                </ul>
                            </div>
                        </li>



                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('settings.index*') ? 'active' : '' }}"
                                href="#sidebarDealSettings" data-bs-toggle="collapse" role="button"
                                aria-expanded="{{ request()->routeIs('settings.index*') ? 'true' : 'false' }}"
                                aria-controls="sidebarDealSettings">
                                <i class="fas fa-cogs menu-icon"></i>
                                <span>Settings</span>
                            </a>
                            <div class="collapse {{ request()->routeIs('settings.index*') ? 'show' : '' }}"
                                id="sidebarDealSettings">
                                <ul class="nav flex-column">
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->routeIs('settings.index') ? 'active' : '' }}"
                                            href="{{ route('settings.index') }}">General</a>
                                    </li>
                                </ul>
                            </div>
                        </li>

                    </ul>
                </div>
            </div>
        </div>
        <!--end startbar-menu-->
    </div><!--end startbar-->
    <div class="startbar-overlay d-print-none"></div>
    <!-- end leftbar-tab-menu-->

    <div class="page-wrapper">
        <div class="page-content">
            @yield('admin-dasboard-content')
            @yield('admin-user-index-content')
            @yield('admin-category-index-content')
            @yield('admin-category-add-content')
            @yield('admin-category-edit-content')
            @yield('admin-buisnesstype-index-content')
            @yield('admin-businesstype-add-content')
            @yield('admin-businesstypes-edit-content')
            @yield('admin-setting-content')


            <footer class="footer text-center text-sm-start d-print-none">
                <div class="container-xxl">
                    <div class="row">
                        <div class="col-12">
                            <div class="card mb-0 rounded-bottom-0">
                                <div class="card-body">
                                    <p class="text-muted mb-0">
                                        ©
                                        <script>
                                            document.write(new Date().getFullYear())
                                        </script>
                                        Swaai
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </footer>
        </div>
    </div>


    <!-- end page-wrapper -->

    <!-- Javascript  -->
    <!-- vendor js -->

    <script src="{{ url('public/assets/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ url('public/assets/js/simplebar.min.js') }}"></script>
    @if (!request()->is('admin/deal-requests') && !request()->is('admin/users'))
        <script src="{{ url('public/assets/js/simple-datatables.js') }}"></script>
        <script src="{{ url('public/assets/js/datatable.init.js') }}"></script>
    @endif
    <script src="{{ url('public/assets/js/apexcharts.min.js') }}"></script>
    <script src="{{ url('public/assets/js/stock-prices.js') }}"></script>
    <script src="{{ url('public/assets/js/jsvectormap.min.js') }}"></script>
    <script src="{{ url('public/assets/js/world.js') }}"></script>
    <script src="{{ url('public/assets/js/index.init.js') }}"></script>
    <script src="{{ url('public/assets/js/app.js') }}"></script>
    <script src="{{ url('public/assets/js/form-validation.js') }}"></script>

</body>
<!--end body-->

</html>
