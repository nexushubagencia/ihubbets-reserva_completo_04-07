<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>

    {{-- ⚡ CRITICAL: Apply theme BEFORE any rendering to prevent white flash --}}
    <script>
        (function() {
            var t = localStorage.getItem('ihub-theme');
            if (!t) t = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
            document.documentElement.setAttribute('data-theme', t);
        })();
    </script>

    {{-- Base Meta Tags --}}
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Custom Meta Tags --}}
    @yield('meta_tags')

    {{-- Title --}}
    <title>
        @yield('title_prefix', config('adminlte.title_prefix', ''))
        @yield('title', config('adminlte.title', 'AdminLTE 3'))
        @yield('title_postfix', config('adminlte.title_postfix', ''))
    </title>

    {{-- IFrame Preloader Removal Workaround --}}
    <!-- IFrame Preloader Removal Workaround -->
    <style type="text/css">
        body.iframe-mode .preloader {
            display: none !important;
        }
    </style>

    {{-- Custom stylesheets (pre AdminLTE) --}}
    @yield('adminlte_css_pre')

    {{-- Base Stylesheets (depends on Laravel asset bundling tool) --}}
    @if(config('adminlte.enabled_laravel_mix', false))
        <link rel="stylesheet" href="{{ mix(config('adminlte.laravel_mix_css_path', 'css/app.css')) }}">
    @else
        @switch(config('adminlte.laravel_asset_bundling', false))
            @case('mix')
                <link rel="stylesheet" href="{{ mix(config('adminlte.laravel_css_path', 'css/app.css')) }}">
            @break

            @case('vite')
                @vite([config('adminlte.laravel_css_path', 'resources/css/app.css'), config('adminlte.laravel_js_path', 'resources/js/app.js')])
            @break

            @case('vite_js_only')
                @vite(config('adminlte.laravel_js_path', 'resources/js/app.js'))
            @break

            @default
                <link rel="stylesheet" href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}">
                <link rel="stylesheet" href="{{ asset('vendor/overlayScrollbars/css/OverlayScrollbars.min.css') }}">
                <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/adminlte.min.css') }}">

                @if(config('adminlte.google_fonts.allowed', true))
                    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic">
                @endif
        @endswitch
    @endif

    {{-- 🎨 IHUB DESIGN SYSTEM v3 — Premium Sports Betting Dark Mod    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

        /* 🎨 IHUB DESIGN SYSTEM v4 — Premium Sports Betting */
        :root {
            /* Light Mode Default */
            --bg-body: #f1f5f9;
            --bg-card: #ffffff;
            --border: #e2e8f0;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --primary: #2563eb;
            --primary-h: #1d4ed8;
            --success: #10b981;
            --danger: #ef4444;
            --warning: #f59e0b;
            --info: #0ea5e9;
            --sidebar-bg: #ffffff;
            --sidebar-border: #e2e8f0;
            --sidebar-text: #475569;
            --sidebar-text-hover: #0f172a;
            --sidebar-active-bg: #eff6ff;
            --sidebar-active-text: #2563eb;
            --radius: 12px;
            --radius-sm: 8px;
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
            --shadow-md: 0 10px 15px -3px rgba(0, 0, 0, 0.08);
            --glass-bg: rgba(255, 255, 255, 0.85);
            --input-bg: #f8fafc;
            --table-hover: #f1f5f9;
            --table-striped: #f8fafc;
            --nav-link-bg: rgba(0,0,0,0.03);
            --nav-link-hover: rgba(0,0,0,0.06);
        }

        .dark-mode, [data-theme="dark"] {
            /* Color Palette Premium Dark */
            --bg-body: #0b1120;
            --bg-card: #141f32;
            --border: #1e293b;
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --primary: #3b82f6;
            --primary-h: #2563eb;
            --success: #10b981;
            --danger: #ef4444;
            --warning: #f59e0b;
            --info: #0ea5e9;
            --sidebar-bg: #111f2d;
            --sidebar-border: rgba(255,255,255,0.05);
            --sidebar-text: #94a3b8;
            --sidebar-text-hover: #f8fafc;
            --sidebar-active-bg: linear-gradient(135deg, var(--primary), var(--primary-h));
            --sidebar-active-text: #ffffff;
            --radius: 12px;
            --radius-sm: 8px;
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.3);
            --shadow-md: 0 10px 15px -3px rgba(0, 0, 0, 0.4);
            --glass-bg: rgba(20, 31, 50, 0.7);
            --input-bg: rgba(0,0,0,0.2);
            --table-hover: rgba(59,130,246,0.1);
            --table-striped: rgba(0,0,0,0.15);
            --nav-link-bg: rgba(255,255,255,0.03);
            --nav-link-hover: rgba(59,130,246,0.15);
        }


        /* ═══ GLOBAL ═══ */
        body { font-family: 'Inter', -apple-system, sans-serif; background: var(--bg-body) !important; color: var(--text-main) !important; font-size: 0.95rem; }
        .wrapper, .content-wrapper { background: var(--bg-body) !important; }
        .content-header h1 { font-weight: 700 !important; font-size: 1.8rem !important; color: var(--text-main) !important; letter-spacing: -0.5px; }
        a { color: var(--primary); text-decoration: none; } a:hover { color: var(--primary-h); }
        
        /* ═══ NAVBAR (Top) ═══ */
        .main-header.navbar { background: var(--glass-bg) !important; backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); border-bottom: 1px solid var(--border) !important; box-shadow: var(--shadow) !important; }
        .main-header .nav-link { color: var(--text-muted) !important; font-weight: 500; transition: color 0.2s; }
        .main-header .nav-link:hover { color: var(--text-main) !important; }

        .navbar-nav .nav-item { display: flex; align-items: center; }
        .navbar-nav .nav-item .nav-link:not(.dropdown-toggle):not([data-toggle="dropdown"]) { display: flex; align-items: center; justify-content: center; width: 40px; height: 40px; border-radius: 50%; margin: 0 4px; background: var(--nav-link-bg); border: 1px solid var(--border); transition: all 0.2s; }
        .navbar-nav .nav-item .nav-link:not(.dropdown-toggle):not([data-toggle="dropdown"]):hover { background: var(--nav-link-hover); color: var(--primary) !important; border-color: rgba(59,130,246,0.3); transform: translateY(-2px); }
        
        .navbar-nav .nav-item .dropdown-toggle { display: flex !important; align-items: center; gap: 8px; padding: 6px 16px !important; border-radius: 30px; background: var(--nav-link-bg); border: 1px solid var(--border); color: var(--text-muted) !important; transition: all 0.2s; height: 40px; margin-left: 10px; }
        .navbar-nav .nav-item .dropdown-toggle:hover { background: var(--nav-link-hover); color: var(--primary) !important; border-color: rgba(59,130,246,0.3); }

        /* ═══ SIDEBAR ═══ */
        .main-sidebar, .main-sidebar::before { background: var(--sidebar-bg) !important; box-shadow: var(--shadow) !important; border-right: 1px solid var(--sidebar-border) !important; }
        .brand-link { background: var(--sidebar-bg) !important; border-bottom: 1px solid var(--sidebar-border) !important; padding: 20px 15px !important; display: flex !important; justify-content: center !important; align-items: center !important; height: 57px; }
        .brand-link img { display: none !important; }
        .brand-text { font-family: 'Inter', sans-serif !important; font-weight: 800 !important; color: var(--text-main) !important; font-size: 1.4rem !important; letter-spacing: 1px; text-align: center !important; width: 100%; display: block !important; }
        .brand-text span { color: var(--primary); }
        .brand-link .mini-text { display: none !important; }
        .brand-link .full-text { display: block !important; text-align: center !important; width: 100%; }
        
        body.sidebar-collapse .brand-link { justify-content: center !important; align-items: center !important; }
        body.sidebar-collapse .brand-link .full-text {
            display: none !important;
        }
        body.sidebar-collapse .brand-link .mini-text {
            display: block !important;
            font-family: 'Inter', sans-serif !important;
            font-weight: 800 !important;
            color: var(--text-main) !important;
            font-size: 1.2rem !important;
            text-align: center !important;
            width: 100%;
        }
        
        /* Ocultar a Logo do painel de usuário antigo e Painel do admin */
        .sidebar .user-panel, .sidebar .image, .sidebar .info { display: none !important; }

        /* Sidebar Navigation */
        .sidebar .nav-link { border-radius: 8px; margin: 4px 12px; padding: 12px 15px !important; transition: all 0.2s ease; }
        .sidebar .nav-link p { color: var(--sidebar-text) !important; font-size: 0.95rem; font-weight: 500; transition: color 0.2s; }
        .sidebar .nav-link i:not(.fa-angle-left) { color: var(--sidebar-text) !important; font-size: 1.1rem; width: 25px; text-align: center; transition: all 0.2s; }
        
        .sidebar .nav-link:hover { background: var(--nav-link-bg) !important; transform: translateX(4px); }
        .sidebar .nav-link:hover p, .sidebar .nav-link:hover i:not(.fa-angle-left) { color: var(--sidebar-text-hover) !important; }
        
        .sidebar .nav-link.active, .sidebar .nav-item > .nav-link.active { background: var(--sidebar-active-bg) !important; box-shadow: 0 4px 10px rgba(59, 130, 246, 0.2) !important; }
        .sidebar .nav-link.active p, .sidebar .nav-link.active i:not(.fa-angle-left) { color: var(--sidebar-active-text) !important; font-weight: 600; }
        
        .nav-header { color: var(--text-muted) !important; font-size: 0.75rem !important; font-weight: 700 !important; letter-spacing: 1.2px; padding: 25px 20px 8px !important; text-transform: uppercase; }
        .sidebar .nav-treeview { background: var(--input-bg) !important; border-radius: 8px; margin: 4px 12px; padding: 8px 0; }
        .sidebar .nav-treeview .nav-link { padding: 10px 12px 10px 20px !important; margin: 0 8px; }
        .sidebar .nav-treeview .nav-link i { font-size: 0.85rem !important; }

        /* ═══ CARDS & CONTAINERS ═══ */
        .card { background: var(--bg-card) !important; border: 1px solid var(--border) !important; border-radius: var(--radius) !important; box-shadow: var(--shadow) !important; transition: transform 0.2s, box-shadow 0.2s; margin-bottom: 24px; }
        .card:hover { box-shadow: var(--shadow-md) !important; }
        .card-header { background: transparent !important; border-bottom: 1px solid var(--border) !important; padding: 20px 24px !important; }
        .card-title { font-weight: 600 !important; font-size: 1.1rem !important; color: var(--text-main) !important; }
        .card-body { padding: 24px !important; color: var(--text-muted); }
        .card-footer { background: transparent !important; border-top: 1px solid var(--border) !important; padding: 20px 24px !important; }
        .card-outline { border-top: 3px solid var(--primary) !important; }

        /* ═══ INFO-BOXES (Dashboard KPIs) ═══ */
        .info-box { background: var(--bg-card) !important; border-radius: var(--radius) !important; box-shadow: var(--shadow) !important; border: 1px solid var(--border); overflow: hidden; transition: transform .2s, box-shadow .2s; min-height: 100px !important; margin-bottom: 24px !important; display: flex !important; align-items: stretch !important; padding: 0 !important; }
        .info-box:hover { transform: translateY(-4px); box-shadow: var(--shadow-md) !important; border-color: var(--primary); }
        .info-box-icon { border-radius: 0 !important; width: 90px !important; display: flex !important; align-items: center !important; justify-content: center !important; font-size: 2rem !important; color: #fff !important; }
        .info-box-content { display: flex !important; flex-direction: column !important; justify-content: center !important; padding: 15px 24px !important; }
        .info-box-text { color: var(--text-muted) !important; font-size: 0.85rem !important; font-weight: 600 !important; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px; }
        .info-box-number { color: var(--text-main) !important; font-size: 1.8rem !important; font-weight: 700 !important; line-height: 1; }

        /* ═══ TEXT COLORS & BG ═══ */
        .text-success { color: var(--success) !important; }
        .text-danger { color: var(--danger) !important; }
        .text-primary { color: var(--primary) !important; }
        .text-warning { color: var(--warning) !important; }
        .bg-info { background: var(--info) !important; color: #fff !important; }
        .bg-success { background: var(--success) !important; color: #fff !important; }
        .bg-warning { background: var(--warning) !important; color: #fff !important; }
        .bg-danger { background: var(--danger) !important; color: #fff !important; }
        .bg-primary { background: var(--primary) !important; color: #fff !important; }

        /* ═══ TABLES ═══ */
        .table-responsive { border-radius: var(--radius); overflow-x: auto; }
        .table { color: var(--text-main) !important; font-size: 0.95rem; width: 100%; margin-bottom: 0; }
        .table thead th { background: var(--input-bg); border-bottom: 1px solid var(--border) !important; border-top: none !important; font-weight: 600; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px; color: var(--text-muted); padding: 16px 20px !important; white-space: nowrap; }
        .table td { border-top: 1px solid var(--border) !important; padding: 16px 20px !important; vertical-align: middle !important; color: var(--text-main); font-weight: 500; }
        .table-striped tbody tr:nth-of-type(odd) { background: var(--table-striped); }
        .table-hover tbody tr:hover { background: var(--table-hover) !important; transition: background 0.2s; }

        /* ═══ FORMS ═══ */
        .form-control { background: var(--input-bg) !important; border-radius: var(--radius-sm) !important; border: 1px solid var(--border) !important; padding: 12px 16px !important; font-size: 0.95rem !important; font-weight: 500; transition: all 0.2s; color: var(--text-main) !important; height: auto !important; }
        .form-control:focus { border-color: var(--primary) !important; box-shadow: 0 0 0 3px rgba(37,99,235,0.15) !important; outline: 0; background: var(--bg-card) !important; }
        .form-control:read-only, .form-control:disabled { background: var(--nav-link-bg) !important; color: var(--text-muted) !important; opacity: 1; }
        select.form-control { appearance: auto; }
        label:not(.btn) { font-weight: 600; font-size: 0.9rem; color: var(--text-main); margin-bottom: 8px; }

        /* ═══ BUTTONS ═══ */
        .btn { border-radius: var(--radius-sm) !important; font-weight: 600 !important; font-size: 0.95rem !important; padding: 10px 24px !important; transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1) !important; letter-spacing: 0.3px; display: inline-flex; align-items: center; justify-content: center; gap: 8px; }
        .btn-primary { background: var(--primary) !important; border: none !important; color: #fff !important; }
        .btn-primary:hover { background: var(--primary-h) !important; box-shadow: 0 4px 12px rgba(37,99,235,0.3) !important; transform: translateY(-2px); }
        .btn-success { background: var(--success) !important; border: none !important; color: #fff !important; }
        .btn-success:hover { background: #059669 !important; transform: translateY(-2px); box-shadow: 0 4px 12px rgba(16,185,129,0.3) !important; }
        .btn-danger { background: var(--danger) !important; border: none !important; color: #fff !important; }
        .btn-danger:hover { background: #dc2626 !important; transform: translateY(-2px); box-shadow: 0 4px 12px rgba(239,68,68,0.3) !important; }
        .btn-warning { background: var(--warning) !important; border: none !important; color: #fff !important; }
        .btn-info { background: var(--info) !important; border: none !important; color: #fff !important; }
        
        /* Remover a barra cinza do rodapé */
        .main-footer, footer, .control-sidebar { display: none !important; border: none !important; background: transparent !important; }
        body, .wrapper, .content-wrapper { border-bottom: none !important; box-shadow: none !important; }

        /* ═══ BADGES ═══ */
        .badge { font-weight: 600; font-size: 0.75rem; padding: 6px 12px; border-radius: 20px; letter-spacing: 0.3px; display: inline-flex; align-items: center; justify-content: center; }
        .badge-success { background: rgba(16,185,129,0.15) !important; color: var(--success) !important; }
        .badge-danger { background: rgba(239,68,68,0.15) !important; color: var(--danger) !important; }
        .badge-warning { background: rgba(245,158,11,0.15) !important; color: var(--warning) !important; }
        .badge-primary { background: rgba(37,99,235,0.15) !important; color: var(--primary) !important; }
        .badge-info { background: rgba(14,165,233,0.15) !important; color: var(--info) !important; }

        /* ═══ BREADCRUMBS ═══ */
        .content-header .breadcrumb { background: transparent; padding: 0; margin-bottom: 0; float: left; /* Traz para a esquerda */ margin-top: 8px; }
        .breadcrumb-item a { color: var(--text-muted); font-weight: 500; font-size: 0.9rem; }
        .breadcrumb-item.active { color: var(--primary); font-weight: 600; font-size: 0.9rem; }
        .breadcrumb-item + .breadcrumb-item::before { color: var(--text-muted); content: "›"; font-size: 1.2rem; line-height: 0.8; }

        /* ═══ RESPONSIVE ═══ */
        @media(max-width:768px){
            .content-header { display: flex; flex-direction: column; align-items: flex-start; }
            .content-header h1 { font-size: 1.4rem !important; }
            .content-header .breadcrumb { float: none; margin-top: 10px; }
            .card-body { padding: 16px !important; }
            .info-box { min-height: 80px !important; }
            .info-box-icon { width: 70px !important; font-size: 1.5rem !important; }
            .info-box-number { font-size: 1.4rem !important; }
            .table { font-size: 0.85rem; }
            .table td, .table th { padding: 12px 16px !important; }
        }



    {{-- Extra Configured Plugins Stylesheets --}}
    @include('adminlte::plugins', ['type' => 'css'])

    {{-- Livewire Styles --}}
    @if(config('adminlte.livewire'))
        @if(intval(app()->version()) >= 7)
            @livewireStyles
        @else
            <livewire:styles />
        @endif
    @endif

    {{-- Custom Stylesheets (post AdminLTE) --}}
    @yield('adminlte_css')

    {{-- Favicon --}}
    @if(config('adminlte.use_ico_only'))
        <link rel="shortcut icon" href="{{ asset('favicons/favicon.ico') }}" />
    @elseif(config('adminlte.use_full_favicon'))
        <link rel="shortcut icon" href="{{ asset('favicons/favicon.ico') }}" />
        <link rel="apple-touch-icon" sizes="57x57" href="{{ asset('favicons/apple-icon-57x57.png') }}">
        <link rel="apple-touch-icon" sizes="60x60" href="{{ asset('favicons/apple-icon-60x60.png') }}">
        <link rel="apple-touch-icon" sizes="72x72" href="{{ asset('favicons/apple-icon-72x72.png') }}">
        <link rel="apple-touch-icon" sizes="76x76" href="{{ asset('favicons/apple-icon-76x76.png') }}">
        <link rel="apple-touch-icon" sizes="114x114" href="{{ asset('favicons/apple-icon-114x114.png') }}">
        <link rel="apple-touch-icon" sizes="120x120" href="{{ asset('favicons/apple-icon-120x120.png') }}">
        <link rel="apple-touch-icon" sizes="144x144" href="{{ asset('favicons/apple-icon-144x144.png') }}">
        <link rel="apple-touch-icon" sizes="152x152" href="{{ asset('favicons/apple-icon-152x152.png') }}">
        <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('favicons/apple-icon-180x180.png') }}">
        <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicons/favicon-16x16.png') }}">
        <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicons/favicon-32x32.png') }}">
        <link rel="icon" type="image/png" sizes="96x96" href="{{ asset('favicons/favicon-96x96.png') }}">
        <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('favicons/android-icon-192x192.png') }}">
        <link rel="manifest" crossorigin="use-credentials" href="{{ asset('favicons/manifest.json') }}">
        <meta name="msapplication-TileColor" content="#ffffff">
        <meta name="msapplication-TileImage" content="{{ asset('favicons/ms-icon-144x144.png') }}">
    @endif

    {{-- ⚡ DYNAMIC THEME ENGINE — CSS Variable Injection --}}
    <link rel="stylesheet" href="{{ route('generate-css') }}?v={{ time() }}">

    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>

<body class="@yield('classes_body') {{ (auth()->check() && auth()->user()->theme_preference === 'dark') ? 'dark-mode' : '' }}" @yield('body_data')>

    {{-- Body Content --}}
    @yield('body')

    {{-- Base Scripts (depends on Laravel asset bundling tool) --}}
    @if(config('adminlte.enabled_laravel_mix', false))
        <script src="{{ mix(config('adminlte.laravel_mix_js_path', 'js/app.js')) }}"></script>
    @else
        @switch(config('adminlte.laravel_asset_bundling', false))
            @case('mix')
                <script src="{{ mix(config('adminlte.laravel_js_path', 'js/app.js')) }}"></script>
            @break

            @case('vite')
            @case('vite_js_only')
            @break

            @default
                <script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
                <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
                <script src="{{ asset('vendor/overlayScrollbars/js/jquery.overlayScrollbars.min.js') }}"></script>
                <script src="{{ asset('vendor/adminlte/dist/js/adminlte.min.js') }}"></script>
        @endswitch
    @endif

    {{-- Global CSRF for ALL AJAX requests --}}
    <script>$.ajaxSetup({headers:{'X-CSRF-TOKEN':$('meta[name="csrf-token"]').attr('content')}});</script>

    {{-- Extra Configured Plugins Scripts --}}
    @include('adminlte::plugins', ['type' => 'js'])

    {{-- Livewire Script --}}
    @if(config('adminlte.livewire'))
        @if(intval(app()->version()) >= 7)
            @livewireScripts
        @else
            <livewire:scripts />
        @endif
    @endif

    {{-- Custom Scripts --}}
    @yield('adminlte_js')


    <script>
        $(document).ready(function() {
            // Lógica de Toggle de Tema Global (Ultra-Robusta + LocalStorage Fallback)
            $(document).on('click', '#global-theme-toggle, .btn-theme-toggle, .fa-moon, .fa-sun', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                let $icon = $(this).find('i');
                if (!$icon.length) $icon = $(this).is('i') ? $(this) : $(this).find('.fa-moon, .fa-sun');
                if (!$icon.length) $icon = $('.btn-theme-toggle i, #global-theme-toggle i'); // last resort
                
                const isDark = $('body').hasClass('dark-mode');
                const newTheme = isDark ? 'light' : 'dark';
                
                // 1. Atualiza a UI Imediatamente
                if (newTheme === 'dark') {
                    $('body').addClass('dark-mode');
                    document.documentElement.setAttribute('data-theme', 'dark');
                    $icon.removeClass('fa-moon').addClass('fa-sun');
                } else {
                    $('body').removeClass('dark-mode');
                    document.documentElement.setAttribute('data-theme', 'light');
                    $icon.removeClass('fa-sun').addClass('fa-moon');
                }

                // 2. Salva no LocalStorage (Fallback imediato para recargas rápidas)
                localStorage.setItem('ihub-theme', newTheme);

                // 3. Persiste no Banco de Dados via AJAX
                $.ajax({
                    url: "{{ url('/admin/update-theme') }}",
                    method: "POST",
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content'),
                        theme: newTheme
                    },
                    success: function() {
                        console.log('Tema persistido na base de dados: ' + newTheme);
                    },
                    error: function(xhr) {
                        console.error('Falha ao salvar tema no banco de dados. Usando LocalStorage.', xhr);
                    }
                });
            });

            // Sincroniza o ícone no carregamento baseado no estado atual da Body ou LocalStorage
            const storedTheme = localStorage.getItem('ihub-theme');
            if (storedTheme === 'dark') {
                $('body').addClass('dark-mode');
                document.documentElement.setAttribute('data-theme', 'dark');
            } else if (storedTheme === 'light') {
                $('body').removeClass('dark-mode');
                document.documentElement.setAttribute('data-theme', 'light');
            }

            if ($('body').hasClass('dark-mode')) {
                $('.fa-moon').removeClass('fa-moon').addClass('fa-sun');
            }
        });
    </script>
</body>

</html>
