<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8" />
        @php $siteName = $siteSettings['site_name'] ?? config('app.name'); @endphp
        <title>@yield('title', $siteName) | {{ $siteName }}</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <!-- App favicon -->
        @if(!empty($siteSettings['favicon_path']))
            <link rel="shortcut icon" href="{{ Storage::url($siteSettings['favicon_path']) }}">
        @else
            <link rel="shortcut icon" href="{{ asset('assets/images/favicon.ico') }}">
        @endif
        <!-- Bootstrap Css -->
        <link href="{{ asset('assets/css/bootstrap.min.css') }}" rel="stylesheet" type="text/css" />
        <!-- Icons Css -->
        <link href="{{ asset('assets/css/icons.min.css') }}" rel="stylesheet" type="text/css" />
        <!-- App Css-->
        <link href="{{ asset('assets/css/app.min.css') }}" rel="stylesheet" type="text/css" />
        <!-- DataTables -->
        <link href="{{ asset('assets/libs/datatables.net-bs4/css/dataTables.bootstrap4.min.css') }}" rel="stylesheet" type="text/css" />
        <link href="{{ asset('assets/libs/datatables.net-buttons-bs4/css/buttons.bootstrap4.min.css') }}" rel="stylesheet" type="text/css" />
        <link href="{{ asset('assets/libs/datatables.net-responsive-bs4/css/responsive.bootstrap4.min.css') }}" rel="stylesheet" type="text/css" />
        <!-- SweetAlert2 -->
        <link href="{{ asset('assets/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css" />
        @stack('styles')
        <style>
            /* ── Smooth scroll global ── */
            html { scroll-behavior: smooth; scroll-padding-top: 5rem; }
            @media (prefers-reduced-motion: reduce) {
                html { scroll-behavior: auto; }
            }

            /* ── Override biru → kuning #C9A800 ── */
            body[data-topbar=colored] #page-topbar {
                background-color: #C9A800 !important;
                box-shadow: 0 2px 8px rgba(232,201,0,0.3) !important;
            }
            body[data-topbar=colored] .navbar-brand-box {
                background: #b89500 !important;
            }
            body[data-topbar=colored] .header-item,
            body[data-topbar=colored] .header-item:hover {
                color: #18181b !important;
            }
            body[data-topbar=colored] .noti-icon i {
                color: #18181b !important;
            }
            body[data-topbar=colored] .dropdown.show .header-item {
                background-color: rgba(0,0,0,0.08) !important;
            }
            body[data-topbar=colored] .app-search .form-control {
                background-color: rgba(0,0,0,0.08) !important;
                color: #18181b !important;
            }
            body[data-topbar=colored] .app-search input.form-control::-webkit-input-placeholder,
            body[data-topbar=colored] .app-search span {
                color: rgba(0,0,0,0.45) !important;
            }
            .page-title-box {
                background-color: #C9A800 !important;
            }
            .page-title-box .page-title {
                color: #18181b !important;
            }
            .page-title-box .breadcrumb-item > a,
            .page-title-box .breadcrumb-item.active {
                color: rgba(0,0,0,0.55) !important;
            }
            .right-bar .rightbar-title {
                background-color: #C9A800 !important;
                color: #18181b !important;
            }
            /* Sidebar active icon & link color */
            :root, [data-bs-theme=light] {
                --bs-sidebar-menu-item-icon-color: #b89e00;
                --bs-sidebar-menu-item-active-color: #b89e00;
            }
            #sidebar-menu ul li a.active,
            .mm-active > a,
            .mm-active .active {
                color: #b89e00 !important;
            }
        </style>
    </head>

    <body data-topbar="colored">

        <!-- Begin page -->
        <div id="layout-wrapper">

            <header id="page-topbar">
                <div class="navbar-header">
                    <div class="d-flex">
                        <!-- LOGO -->
                        <div class="navbar-brand-box">
                            @if(!empty($siteSettings['logo_path']))
                                @php $logoUrl = Storage::url($siteSettings['logo_path']); @endphp
                                <a href="{{ route('dashboard') }}" class="logo logo-dark">
                                    <span class="logo-sm">
                                        <img src="{{ $logoUrl }}" alt="{{ $siteName }}" height="90">
                                    </span>
                                    <span class="logo-lg">
                                        <img src="{{ $logoUrl }}" alt="{{ $siteName }}" height="90">
                                    </span>
                                </a>
                                <a href="{{ route('dashboard') }}" class="logo logo-light">
                                    <span class="logo-sm">
                                        <img src="{{ $logoUrl }}" alt="{{ $siteName }}" height="90">
                                    </span>
                                    <span class="logo-lg">
                                        <img src="{{ $logoUrl }}" alt="{{ $siteName }}" height="90">
                                    </span>
                                </a>
                            @else
                                <a href="{{ route('dashboard') }}" class="logo logo-dark">
                                    <span class="logo-sm">
                                        <img src="{{ asset('assets/images/logo-sm-dark.png') }}" alt="" height="22">
                                    </span>
                                    <span class="logo-lg">
                                        <img src="{{ asset('assets/images/logo-dark.png') }}" alt="" height="20">
                                    </span>
                                </a>
                                <a href="{{ route('dashboard') }}" class="logo logo-light">
                                    <span class="logo-sm">
                                        <img src="{{ asset('assets/images/logo-sm-light.png') }}" alt="" height="22">
                                    </span>
                                    <span class="logo-lg">
                                        <img src="{{ asset('assets/images/logo-light.png') }}" alt="" height="20">
                                    </span>
                                </a>
                            @endif
                        </div>

                        <button type="button" class="btn btn-sm px-3 font-size-24 header-item waves-effect" id="vertical-menu-btn">
                            <i class="fas fa-bars"></i>
                        </button>

                        <!-- App Search-->
                        <form class="app-search d-none d-lg-block" method="GET" action="{{ route('transactions.index') }}">
                            <div class="position-relative mt-3">
                                <input type="text" name="q" class="form-control" placeholder="Cari transaksi..." value="{{ request('q') }}" autocomplete="off">
                                <span class="fas fa-search"></span>
                            </div>
                        </form>
                    </div>

                    <div class="d-flex">
                        <div class="dropdown d-inline-block">
                            <button type="button" class="btn header-item user text-start d-flex align-items-center"
                                id="page-header-user-dropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                @if(auth()->user()->avatar_path)
                                    <img class="rounded-circle header-profile-user" src="{{ Storage::url(auth()->user()->avatar_path) }}" alt="Avatar">
                                @else
                                    <span class="rounded-circle flex-shrink-0 d-inline-flex align-items-center justify-content-center"
                                        style="width:36px;height:36px;background:rgba(255,255,255,0.2);border:1.5px solid rgba(255,255,255,0.35);">
                                        <i class="fas fa-user" style="font-size:1.05rem;color:#fff;line-height:1;"></i>
                                    </span>
                                @endif
                                <span class="d-none d-sm-inline-block ms-2 lh-1">
                                    <span class="d-block" style="font-size:.85rem;">{{ auth()->user()->name }}</span>
                                    <span class="d-block text-white text-opacity-60" style="font-size:.7rem;">{{ ucfirst(auth()->user()->role) }}</span>
                                </span>
                                <i class="fas fa-chevron-down d-none d-sm-inline-block ms-1"></i>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end">
                                <a class="dropdown-item" href="{{ route('profile.edit') }}"><i class="fas fa-user-edit font-size-16 align-middle me-1"></i> Edit Profil</a>
                                <div class="dropdown-divider"></div>
                                <form method="POST" action="{{ route('logout') }}" id="logout-form">
                                    @csrf
                                    <button type="submit" class="dropdown-item">
                                        <i class="fas fa-sign-out-alt font-size-16 align-middle me-1"></i> Logout
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- ========== Left Sidebar Start ========== -->
            <div class="vertical-menu">
                <div data-simplebar class="h-100">
                    <div id="sidebar-menu">
                        <ul class="metismenu list-unstyled" id="side-menu">

                            {{-- ─── Overview ─── --}}
                            <li class="menu-title">Overview</li>

                            <li>
                                <a href="{{ route('dashboard') }}" class="waves-effect {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                                    <div class="d-inline-block icons-sm me-1"><i class="fas fa-th-large"></i></div>
                                    <span>Dashboard</span>
                                </a>
                            </li>

                            {{-- ─── Katalog ─── --}}
                            <li class="menu-title">Katalog</li>

                            @if(auth()->user()->isAdmin())
                            <li>
                                <a href="{{ route('branches.index') }}" class="waves-effect {{ request()->routeIs('branches.*') ? 'active' : '' }}">
                                    <div class="d-inline-block icons-sm me-1"><i class="fas fa-store"></i></div>
                                    <span>Cabang</span>
                                </a>
                            </li>
                            @endif

                            <li>
                                <a href="{{ route('templates.index') }}" class="waves-effect {{ request()->routeIs('templates.*') ? 'active' : '' }}">
                                    <div class="d-inline-block icons-sm me-1"><i class="far fa-images"></i></div>
                                    <span>Frame Foto</span>
                                </a>
                            </li>

                            {{-- Paket Foto disembunyikan: harga dikelola di Pengaturan Booth, paket dipakai internal saja. --}}

                            {{-- ─── Operasional ─── --}}
                            <li class="menu-title">Operasional</li>

                            @if(auth()->user()->branch)
                            <li>
                                <a href="{{ route('booth.show', auth()->user()->branch->code) }}" target="_blank" rel="noopener" class="waves-effect">
                                    <div class="d-inline-block icons-sm me-1"><i class="fas fa-desktop"></i></div>
                                    <span>Buka Booth <i class="fas fa-external-link-alt ms-1" style="font-size: 9px; opacity: 0.6;"></i></span>
                                </a>
                            </li>
                            @endif

                            <li>
                                <a href="{{ route('transactions.index') }}" class="waves-effect {{ request()->routeIs('transactions.*') ? 'active' : '' }}">
                                    <div class="d-inline-block icons-sm me-1"><i class="far fa-credit-card"></i></div>
                                    <span>Transaksi</span>
                                </a>
                            </li>

                            <li>
                                <a href="{{ route('photo-sessions.index') }}" class="waves-effect {{ request()->routeIs('photo-sessions.*') ? 'active' : '' }}">
                                    <div class="d-inline-block icons-sm me-1"><i class="fas fa-camera"></i></div>
                                    <span>Sesi Foto</span>
                                </a>
                            </li>

                            @if(auth()->user()->isAdmin())
                            <li>
                                <a href="{{ route('vouchers.index') }}" class="waves-effect {{ request()->routeIs('vouchers.*') ? 'active' : '' }}">
                                    <div class="d-inline-block icons-sm me-1"><i class="fas fa-ticket-alt"></i></div>
                                    <span>Voucher Diskon</span>
                                </a>
                            </li>
                            @endif

                            <li>
                                <a href="{{ route('printers.index') }}" class="waves-effect {{ request()->routeIs('printers.*') ? 'active' : '' }}">
                                    <div class="d-inline-block icons-sm me-1"><i class="fas fa-print"></i></div>
                                    <span>Printer</span>
                                </a>
                            </li>

                            {{-- ─── Laporan ─── --}}
                            <li class="menu-title">Laporan</li>

                            <li>
                                <a href="{{ route('reports.revenue') }}" class="waves-effect {{ request()->routeIs('reports.revenue') ? 'active' : '' }}">
                                    <div class="d-inline-block icons-sm me-1"><i class="fas fa-chart-line"></i></div>
                                    <span>Pendapatan</span>
                                </a>
                            </li>

                            @if(auth()->user()->isAdmin())
                            <li>
                                <a href="{{ route('reports.branches') }}" class="waves-effect {{ request()->routeIs('reports.branches') ? 'active' : '' }}">
                                    <div class="d-inline-block icons-sm me-1"><i class="far fa-chart-bar"></i></div>
                                    <span>Performa Cabang</span>
                                </a>
                            </li>

                            {{-- ─── Konten Website ─── --}}
                            <li class="menu-title">Konten Website</li>

                            <li>
                                <a href="{{ route('galleries.index') }}" class="waves-effect {{ request()->routeIs('galleries.*') ? 'active' : '' }}">
                                    <div class="d-inline-block icons-sm me-1"><i class="far fa-image"></i></div>
                                    <span>Galeri</span>
                                </a>
                            </li>

                            <li>
                                <a href="{{ route('features.index') }}" class="waves-effect {{ request()->routeIs('features.*') ? 'active' : '' }}">
                                    <div class="d-inline-block icons-sm me-1"><i class="far fa-star"></i></div>
                                    <span>Fitur Unggulan</span>
                                </a>
                            </li>

                            <li>
                                <a href="{{ route('steps.index') }}" class="waves-effect {{ request()->routeIs('steps.*') ? 'active' : '' }}">
                                    <div class="d-inline-block icons-sm me-1"><i class="fas fa-list-ol"></i></div>
                                    <span>Cara Kerja</span>
                                </a>
                            </li>

                            <li>
                                <a href="{{ route('faqs.index') }}" class="waves-effect {{ request()->routeIs('faqs.*') ? 'active' : '' }}">
                                    <div class="d-inline-block icons-sm me-1"><i class="far fa-question-circle"></i></div>
                                    <span>FAQ</span>
                                </a>
                            </li>

                            {{-- ─── Pengaturan ─── --}}
                            <li class="menu-title">Pengaturan</li>

                            <li>
                                <a href="{{ route('settings.general') }}" class="waves-effect {{ request()->routeIs('settings.*') ? 'active' : '' }}">
                                    <div class="d-inline-block icons-sm me-1"><i class="fas fa-cog"></i></div>
                                    <span>Pengaturan Sistem</span>
                                </a>
                            </li>

                            <li>
                                <a href="{{ route('users.index') }}" class="waves-effect {{ request()->routeIs('users.*') ? 'active' : '' }}">
                                    <div class="d-inline-block icons-sm me-1"><i class="fas fa-users"></i></div>
                                    <span>Pengguna</span>
                                </a>
                            </li>
                            @endif

                        </ul>
                    </div>
                </div>
            </div>
            <!-- Left Sidebar End -->

            <!-- Start right Content here -->
            <div class="main-content">
                <div class="page-content">

                    <!-- Page-Title -->
                    <div class="page-title-box">
                        <div class="container-fluid">
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <h4 class="page-title mb-1">@yield('page-title', 'Dashboard')</h4>
                                    <ol class="breadcrumb m-0">
                                        @yield('breadcrumb')
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="page-content-wrapper">
                        <div class="container-fluid py-4">
                            @yield('content')
                        </div>
                    </div>
                </div>

                <footer class="footer">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-sm-6">
                                {{ date('Y') }} &copy; {{ config('app.name') }}.
                            </div>
                        </div>
                    </div>
                </footer>
            </div>
        </div>
        <!-- END layout-wrapper -->

        <!-- JAVASCRIPT -->
        <script src="{{ asset('assets/libs/jquery/jquery.min.js') }}"></script>
        <script src="{{ asset('assets/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
        <script src="{{ asset('assets/libs/metismenu/metisMenu.min.js') }}"></script>
        <script src="{{ asset('assets/libs/simplebar/simplebar.min.js') }}"></script>
        <script src="{{ asset('assets/libs/node-waves/waves.min.js') }}"></script>
        <script src="https://unicons.iconscout.com/release/v2.0.1/script/monochrome/bundle.js"></script>
        <!-- DataTables -->
        <script src="{{ asset('assets/libs/datatables.net/js/jquery.dataTables.min.js') }}"></script>
        <script src="{{ asset('assets/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
        <script src="{{ asset('assets/libs/datatables.net-responsive/js/dataTables.responsive.min.js') }}"></script>
        <script src="{{ asset('assets/libs/datatables.net-responsive-bs4/js/responsive.bootstrap4.min.js') }}"></script>
        <!-- SweetAlert2 -->
        <script src="{{ asset('assets/libs/sweetalert2/sweetalert2.min.js') }}"></script>
        <script src="{{ asset('assets/js/app.js') }}"></script>

        {{-- SweetAlert dari session flash --}}
        @if (session('success'))
            <script>
                Swal.fire({ icon: 'success', title: 'Berhasil!', text: '{{ session('success') }}', timer: 2500, showConfirmButton: false });
            </script>
        @endif
        @if (session('error'))
            <script>
                Swal.fire({ icon: 'error', title: 'Gagal!', text: '{{ session('error') }}' });
            </script>
        @endif

        @stack('scripts')
    </body>
</html>
