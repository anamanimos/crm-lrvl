<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <title>{{ $title ?? 'CRM DAMAI JAYA' }}</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="icon" type="image/svg+xml" href="{{ asset('assets/media/logos/crm-logo-icon.svg') }}" />
    <link rel="shortcut icon" href="{{ asset('assets/media/logos/crm-logo-icon.svg') }}" />
    
    <!--begin::Fonts(mandatory for all pages)-->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700" />
    <!--end::Fonts-->

    <!--begin::Global Stylesheets Bundle(mandatory for all pages)-->
    <link href="{{ asset('assets/plugins/global/plugins.bundle.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/css/style.bundle.css') }}" rel="stylesheet" type="text/css" />
    <!--end::Global Stylesheets Bundle-->

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <style>
        .app-sidebar-logo {
            display: flex !important;
            align-items: center !important;
            position: relative !important;
        }
        .app-sidebar-logo a.crm-logo-wrapper {
            display: flex !important;
            align-items: center !important;
            text-decoration: none !important;
            max-width: calc(100% - 24px) !important;
            overflow: hidden !important;
        }
        .crm-brand-icon {
            width: 36px !important;
            height: 36px !important;
            min-width: 36px !important;
            max-width: 36px !important;
            max-height: 36px !important;
            object-fit: contain !important;
            flex-shrink: 0 !important;
        }
        .crm-brand-text-container {
            display: flex !important;
            flex-direction: column !important;
            justify-content: center !important;
            line-height: 1.15 !important;
            white-space: nowrap !important;
            overflow: hidden !important;
        }
        .crm-brand-title-main {
            font-size: 0.95rem !important;
            font-weight: 800 !important;
            color: #0F172A !important;
            letter-spacing: -0.2px !important;
            white-space: nowrap !important;
        }
        [data-bs-theme="dark"] .crm-brand-title-main {
            color: #F8FAFC !important;
        }
        .crm-brand-title-sub {
            font-size: 0.72rem !important;
            font-weight: 700 !important;
            color: #0284C7 !important;
            letter-spacing: 0.5px !important;
            text-transform: uppercase !important;
            white-space: nowrap !important;
        }
    </style>

    <script>
        var appUrl = "{{ url('/') }}";
        if (window.top != window.self) {
            window.top.location.replace(window.self.location.href);
        }
    </script>
    
    @stack('css')
</head>

<body id="kt_app_body" data-kt-app-layout="light-sidebar" data-kt-app-header-fixed="true" data-kt-app-sidebar-enabled="true" data-kt-app-sidebar-fixed="true" data-kt-app-sidebar-hoverable="true" data-kt-app-sidebar-push-header="true" data-kt-app-sidebar-push-toolbar="true" data-kt-app-sidebar-push-footer="true" data-kt-app-toolbar-enabled="true" class="app-default">
    <!--begin::Theme mode setup on page load-->
    <script>
        var defaultThemeMode = "light";
        var themeMode;
        if (document.documentElement) {
            if (document.documentElement.hasAttribute("data-bs-theme-mode")) {
                themeMode = document.documentElement.getAttribute("data-bs-theme-mode");
            } else {
                if (localStorage.getItem("data-bs-theme") !== null) {
                    themeMode = localStorage.getItem("data-bs-theme");
                } else {
                    themeMode = defaultThemeMode;
                }
            }
            if (themeMode === "system") {
                themeMode = window.matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light";
            }
            document.documentElement.setAttribute("data-bs-theme", themeMode);
        }
    </script>
    <!--end::Theme mode setup on page load-->

    <!--begin::App-->
    <div class="d-flex flex-column flex-root app-root" id="kt_app_root">
        <!--begin::Page-->
        <div class="app-page flex-column flex-column-fluid" id="kt_app_page">
            <!--begin::Header-->
            <div id="kt_app_header" class="app-header" data-kt-sticky="true" data-kt-sticky-activate="{default: true, lg: true}" data-kt-sticky-name="app-header-minimize" data-kt-sticky-offset="{default: '200px', lg: '0'}" data-kt-sticky-animation="false">
                <div class="app-container container-fluid d-flex align-items-stretch justify-content-between" id="kt_app_header_container">
                    <!--begin::Sidebar mobile toggle-->
                    <div class="d-flex align-items-center d-lg-none ms-n3 me-1 me-md-2" title="Show sidebar menu">
                        <div class="btn btn-icon btn-active-color-primary w-35px h-35px" id="kt_app_sidebar_mobile_toggle">
                            <i class="ki-outline ki-abstract-14 fs-2 fs-md-1"></i>
                        </div>
                    </div>
                    <!--end::Sidebar mobile toggle-->

                    <!--begin::Mobile logo-->
                    <div class="d-flex align-items-center flex-grow-1 flex-lg-grow-0">
                        <a href="{{ route('dashboard') }}" class="d-lg-none d-flex align-items-center gap-2">
                            <img src="{{ asset('assets/media/logos/crm-logo-icon.svg') }}" class="h-28px" alt="CRM Logo" />
                            <span class="fs-4 fw-bold text-primary">CRM</span>
                        </a>
                    </div>
                    <!--end::Mobile logo-->

                    <!--begin::Header wrapper-->
                    <div class="d-flex align-items-stretch justify-content-between flex-lg-grow-1" id="kt_app_header_wrapper">
                        <div class="app-header-menu app-header-mobile-drawer align-items-stretch" data-kt-drawer="true" data-kt-drawer-name="app-header-menu" data-kt-drawer-activate="{default: true, lg: false}" data-kt-drawer-overlay="true" data-kt-drawer-width="250px" data-kt-drawer-direction="end" data-kt-drawer-toggle="#kt_app_header_menu_toggle" data-kt-swapper="true" data-kt-swapper-mode="{default: 'append', lg: 'prepend'}" data-kt-swapper-parent="{default: '#kt_app_body', lg: '#kt_app_header_wrapper'}">
                        </div>

                        <!--begin::Navbar-->
                        <div class="app-navbar flex-shrink-0 align-items-center">
                            <!--begin::WhatsApp Live Status-->
                            <div class="app-navbar-item ms-1 ms-md-3">
                                <a href="{{ route('settings.section', ['section' => 'whatsapp', 'subsection' => 'koneksi']) }}" 
                                   id="header_wa_status_badge" 
                                   class="d-flex align-items-center gap-2 py-2 px-4 border border-secondary border-opacity-25 rounded-pill bg-light text-hover-primary transition-all" 
                                   data-bs-toggle="tooltip" 
                                   data-bs-placement="bottom" 
                                   data-bs-html="true"
                                   title="Status WhatsApp: <span class='text-warning'>Memeriksa...</span>">
                                    <span class="d-inline-flex position-relative me-1" style="width: 8px; height: 8px;">
                                        <span class="bullet bullet-dot bg-warning h-8px w-8px" id="header_wa_status_dot"></span>
                                        <span class="bullet bullet-dot bg-warning h-8px w-8px position-absolute top-0 start-0 animation-blink" id="header_wa_status_pulse"></span>
                                    </span>
                                    <i class="ki-outline ki-whatsapp fs-3 text-muted" id="header_wa_status_icon"></i>
                                    <span class="fs-7 fw-semibold text-muted d-none d-sm-inline" id="header_wa_status_text">Memeriksa...</span>
                                </a>
                            </div>
                            <!--end::WhatsApp Live Status-->

                            <!--begin::Theme mode-->
                            <div class="app-navbar-item ms-1 ms-md-4">
                                <a href="#" class="btn btn-icon btn-custom btn-icon-muted btn-active-light btn-active-color-primary w-35px h-35px" data-kt-menu-trigger="{default:'click', lg: 'hover'}" data-kt-menu-attach="parent" data-kt-menu-placement="bottom-end">
                                    <i class="ki-outline ki-night-day theme-light-show fs-1"></i>
                                    <i class="ki-outline ki-moon theme-dark-show fs-1"></i>
                                </a>
                                <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-title-gray-700 menu-icon-gray-500 menu-active-bg menu-state-color fw-semibold py-4 fs-base w-150px" data-kt-menu="true" data-kt-element="theme-mode-menu">
                                    <div class="menu-item px-3 my-0">
                                        <a href="#" class="menu-link px-3 py-2" data-kt-element="mode" data-kt-value="light">
                                            <span class="menu-icon" data-kt-element="icon"><i class="ki-outline ki-night-day fs-2"></i></span>
                                            <span class="menu-title">Light</span>
                                        </a>
                                    </div>
                                    <div class="menu-item px-3 my-0">
                                        <a href="#" class="menu-link px-3 py-2" data-kt-element="mode" data-kt-value="dark">
                                            <span class="menu-icon" data-kt-element="icon"><i class="ki-outline ki-moon fs-2"></i></span>
                                            <span class="menu-title">Dark</span>
                                        </a>
                                    </div>
                                    <div class="menu-item px-3 my-0">
                                        <a href="#" class="menu-link px-3 py-2" data-kt-element="mode" data-kt-value="system">
                                            <span class="menu-icon" data-kt-element="icon"><i class="ki-outline ki-screen fs-2"></i></span>
                                            <span class="menu-title">System</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <!--end::Theme mode-->

                            <!--begin::User menu-->
                            <div class="app-navbar-item ms-1 ms-md-4" id="kt_header_user_menu_toggle">
                                <div class="cursor-pointer symbol symbol-35px" data-kt-menu-trigger="{default: 'click', lg: 'hover'}" data-kt-menu-attach="parent" data-kt-menu-placement="bottom-end">
                                    <div class="symbol-label fs-5 fw-semibold bg-primary text-inverse-primary">{{ generate_initials(auth()->user()->name) }}</div>
                                </div>
                                <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-800 menu-state-bg menu-state-color fw-semibold py-4 fs-6 w-275px" data-kt-menu="true">
                                    <div class="menu-item px-3">
                                        <div class="menu-content d-flex align-items-center px-3">
                                            <div class="symbol symbol-50px me-5">
                                                <div class="symbol-label fs-2 fw-semibold bg-primary text-inverse-primary">{{ generate_initials(auth()->user()->name) }}</div>
                                            </div>
                                            <div class="d-flex flex-column">
                                                <div class="fw-bold d-flex align-items-center fs-5">
                                                    {{ auth()->user()->name }}
                                                </div>
                                                <a href="#" class="fw-semibold text-muted text-hover-primary fs-7">
                                                    {{ auth()->user()->username }}
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="separator my-2"></div>
                                    <div class="menu-item px-5 my-1">
                                        <a href="{{ route('profile.edit') }}" class="menu-link px-5">Pengaturan Akun</a>
                                    </div>
                                    <div class="menu-item px-5">
                                        <form method="POST" action="{{ route('logout') }}" id="logout-form">
                                            @csrf
                                            <a href="{{ route('logout') }}" class="menu-link px-5 js-logout" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                                Keluar
                                            </a>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <!--end::User menu-->
                        </div>
                        <!--end::Navbar-->
                    </div>
                    <!--end::Header wrapper-->
                </div>
            </div>
            <!--end::Header-->

            <!--begin::Wrapper-->
            <div class="app-wrapper flex-column flex-row-fluid" id="kt_app_wrapper">
                <!--begin::Sidebar-->
                <div id="kt_app_sidebar" class="app-sidebar flex-column" data-kt-drawer="true" data-kt-drawer-name="app-sidebar" data-kt-drawer-activate="{default: true, lg: false}" data-kt-drawer-overlay="true" data-kt-drawer-width="225px" data-kt-drawer-direction="start" data-kt-drawer-toggle="#kt_app_sidebar_mobile_toggle">
                    <!--begin::Logo-->
                    <div class="app-sidebar-logo px-6" id="kt_app_sidebar_logo">
                        <a href="{{ route('dashboard') }}" class="crm-logo-wrapper">
                            <img src="{{ asset('assets/media/logos/crm-logo-icon.svg') }}" class="crm-brand-icon me-3" alt="CRM Logo" />
                            <div class="crm-brand-text-container app-sidebar-logo-default">
                                <span class="crm-brand-title-main">CRM DAMAI JAYA</span>
                            </div>
                            <span class="fs-4 fw-bolder text-primary app-sidebar-logo-minimize">CRM</span>
                        </a>
                        <div id="kt_app_sidebar_toggle" class="app-sidebar-toggle btn btn-icon btn-shadow btn-sm btn-color-muted btn-active-color-primary h-30px w-30px position-absolute top-50 start-100 translate-middle rotate" data-kt-toggle="true" data-kt-toggle-state="active" data-kt-toggle-target="body" data-kt-toggle-name="app-sidebar-minimize">
                            <i class="ki-outline ki-black-left-line fs-3 rotate-180"></i>
                        </div>
                    </div>
                    <!--end::Logo-->

                    <!--begin::sidebar menu-->
                    <div class="app-sidebar-menu overflow-hidden flex-column-fluid">
                        <div id="kt_app_sidebar_menu_wrapper" class="app-sidebar-wrapper">
                            <div id="kt_app_sidebar_menu_scroll" class="scroll-y my-5 mx-3" data-kt-scroll="true" data-kt-scroll-activate="true" data-kt-scroll-height="auto" data-kt-scroll-dependencies="#kt_app_sidebar_logo, #kt_app_sidebar_footer" data-kt-scroll-wrappers="#kt_app_sidebar_menu" data-kt-scroll-offset="5px" data-kt-scroll-save-state="true">
                                <div class="menu menu-column menu-rounded menu-sub-indention fw-semibold fs-6" id="#kt_app_sidebar_menu" data-kt-menu="true" data-kt-menu-expand="false">
                                    @php
                                        $menuJson = file_get_contents(resource_path('json/menu.json'));
                                        $menuItems = json_decode($menuJson, true);
                                        $currentUrl = request()->path();
                                    @endphp

                                    @foreach($menuItems as $item)
                                        @if($item['type'] == 'heading')
                                            <div class="menu-item pt-5">
                                                <div class="menu-content">
                                                    <span class="menu-heading fw-bold text-uppercase fs-7">{{ $item['title'] }}</span>
                                                </div>
                                            </div>
                                        @elseif($item['type'] == 'link')
                                            @if(!isset($item['permission']) || auth()->user()->hasPermission($item['permission']))
                                            <div class="menu-item">
                                                <a class="menu-link {{ $currentUrl == $item['url'] ? 'active' : '' }}" href="{{ url($item['url']) }}">
                                                    <span class="menu-icon"><i class="ki-outline {{ $item['icon'] }} fs-2"></i></span>
                                                    <span class="menu-title">{{ $item['title'] }}</span>
                                                </a>
                                            </div>
                                            @endif
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--end::sidebar menu-->
                </div>
                <!--end::Sidebar-->

                <!--begin::Main-->
                <div class="app-main flex-column flex-row-fluid" id="kt_app_main">
                    <div class="d-flex flex-column flex-column-fluid">
                        {{ $slot }}
                    </div>

                    <!--begin::Footer-->
                    <div id="kt_app_footer" class="app-footer">
                        <div class="app-container container-fluid d-flex flex-column flex-md-row flex-center flex-md-stack py-3">
                            <div class="text-gray-900 order-2 order-md-1">
                                <span class="text-muted fw-semibold me-1">{{ date('Y') }} &copy;</span>
                                <a href="#" target="_blank" class="text-gray-800 text-hover-primary">CRM Damai Jaya</a>
                            </div>
                        </div>
                    </div>
                    <!--end::Footer-->
                </div>
                <!--end:::Main-->
            </div>
            <!--end::Wrapper-->
        </div>
    </div>

    <!--begin::Scrolltop-->
    <div id="kt_scrolltop" class="scrolltop" data-kt-scrolltop="true">
        <i class="ki-outline ki-arrow-up"></i>
    </div>
    <!--end::Scrolltop-->

    <!--begin::Javascript-->
    <script>
        var hostUrl = "{{ asset('assets/') }}";
    </script>
    <script src="{{ asset('assets/plugins/global/plugins.bundle.js') }}"></script>
    <script src="{{ asset('assets/js/scripts.bundle.js') }}"></script>
    
    <!--begin::WhatsApp Status Poller-->
    <script>
        (function() {
            const badge = document.getElementById('header_wa_status_badge');
            const dot = document.getElementById('header_wa_status_dot');
            const pulse = document.getElementById('header_wa_status_pulse');
            const icon = document.getElementById('header_wa_status_icon');
            const text = document.getElementById('header_wa_status_text');

            if (!badge) return;

            const updateHeaderWaBadge = (status, user = '') => {
                badge.classList.remove('bg-light-success', 'bg-light-danger', 'bg-light-warning', 'bg-light', 'border-success', 'border-danger', 'border-secondary');
                dot.classList.remove('bg-success', 'bg-danger', 'bg-warning');
                pulse.classList.remove('bg-success', 'bg-danger', 'bg-warning', 'd-none');
                icon.classList.remove('text-success', 'text-danger', 'text-warning', 'text-muted', 'ki-whatsapp', 'ki-disconnect');
                text.classList.remove('text-success', 'text-danger', 'text-warning', 'text-muted');

                if (status === 'connected') {
                    badge.classList.add('bg-light-success', 'border-success');
                    dot.classList.add('bg-success');
                    pulse.classList.add('bg-success');
                    icon.classList.add('ki-whatsapp', 'text-success');
                    text.classList.add('text-success');
                    text.textContent = 'WA Terhubung';
                    const tooltipMsg = `WhatsApp Terhubung ${user ? '(' + user + ')' : ''} - Klik untuk kelola`;
                    badge.setAttribute('data-bs-original-title', tooltipMsg);
                    badge.setAttribute('title', tooltipMsg);
                } else if (status === 'disconnected') {
                    badge.classList.add('bg-light-danger', 'border-danger');
                    dot.classList.add('bg-danger');
                    pulse.classList.add('bg-danger');
                    icon.classList.add('ki-disconnect', 'text-danger');
                    text.classList.add('text-danger');
                    text.textContent = 'WA Terputus';
                    const tooltipMsg = 'WhatsApp Terputus - Klik untuk tautkan perangkat';
                    badge.setAttribute('data-bs-original-title', tooltipMsg);
                    badge.setAttribute('title', tooltipMsg);
                } else {
                    badge.classList.add('bg-light', 'border-secondary');
                    dot.classList.add('bg-warning');
                    pulse.classList.add('bg-warning');
                    icon.classList.add('ki-whatsapp', 'text-muted');
                    text.classList.add('text-muted');
                    text.textContent = 'Memeriksa...';
                }
            };

            const checkStatus = () => {
                fetch("{{ route('settings.whatsapp.status') }}")
                    .then(r => r.json())
                    .then(data => {
                        let isConnected = false;
                        let user = '';
                        if (data && data.success && data.data) {
                            const results = data.data.results || data.data.data?.results;
                            if (results) {
                                isConnected = results.is_connected && results.is_logged_in;
                                user = results.device_id || '';
                            } else if (data.data.status === 'connected' || data.data.status === 'ready') {
                                isConnected = true;
                                user = data.data.user || data.data.number || '';
                            }
                        }
                        updateHeaderWaBadge(isConnected ? 'connected' : 'disconnected', user);
                    })
                    .catch(() => {
                        updateHeaderWaBadge('disconnected');
                    });
            };

            // Check immediately on page load
            checkStatus();

            // Periodic polling every 30 seconds
            setInterval(checkStatus, 30000);
        })();
    </script>
    <!--end::WhatsApp Status Poller-->

    @stack('js')
    @stack('scripts')
</body>
</html>
