<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'IHUB BETS') }} - @yield('title', 'Gerente')</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastr@2.1.4/build/toastr.min.css">
    <style>
        .sidebar-dark-primary .nav-sidebar>.nav-item>.nav-link.active {
            background-color: #007bff;
            color: #fff;
        }
        .nav-sidebar .nav-link {
            color: rgba(255,255,255,.8);
            font-size: 0.88rem;
        }
        .nav-sidebar .nav-link:hover {
            color: #fff;
            background-color: rgba(255,255,255,.1);
        }
        .nav-sidebar .nav-link.active {
            color: #fff;
        }
        .brand-link {
            border-bottom: 1px solid rgba(255,255,255,.1);
        }
        .user-panel {
            border-bottom: 1px solid rgba(255,255,255,.1);
        }
        .content-wrapper {
            background-color: #f4f6f9;
        }
        .card-header {
            border-bottom: 1px solid rgba(0,0,0,.05);
        }
        .small-box {
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,.08);
        }
        .small-box .inner h3 {
            font-size: 1.8rem;
        }
        .small-box .inner p {
            font-size: 0.85rem;
        }
        .info-box {
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,.08);
        }
        @media (max-width: 768px) {
            .small-box .inner h3 { font-size: 1.3rem; }
            .content-header h1 { font-size: 1.2rem !important; }
        }
    </style>
    @stack('styles')
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">
    <nav class="main-header navbar navbar-expand navbar-white navbar-light">
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
            </li>
            <li class="nav-item d-none d-sm-inline-block">
                <span class="nav-link text-muted" style="font-size:0.85rem;">
                    <i class="fas fa-clock mr-1"></i>
                    <span id="live-clock"></span>
                </span>
            </li>
        </ul>
        <ul class="navbar-nav ml-auto">
            <li class="nav-item">
                <span class="nav-link text-success font-weight-bold" style="font-size:0.85rem;">
                    <i class="fas fa-user-tie mr-1"></i> {{ auth()->user()->name }}
                </span>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('gerente.perfil') }}" title="Meu Perfil">
                    <i class="fas fa-cog"></i>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('logout.get') }}" title="Sair">
                    <i class="fas fa-sign-out-alt"></i>
                </a>
            </li>
        </ul>
    </nav>

    <aside class="main-sidebar sidebar-dark-primary elevation-4" style="min-height: 100vh;">
        <a href="{{ route('gerente.home') }}" class="brand-link text-center" style="background: rgba(0,0,0,.15);">
            <span class="brand-text font-weight-bold" style="color: #fff; font-size: 1.1rem;">
                <i class="fas fa-tachometer-alt mr-1"></i> GERENTE
            </span>
        </a>
        <div class="sidebar">
            <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                <div class="info">
                    <a href="{{ route('gerente.perfil') }}" class="d-block text-white font-weight-bold" style="font-size:0.9rem;">
                        {{ auth()->user()->name }}
                    </a>
                    <small class="text-muted" style="font-size:0.75rem;">Gerente</small>
                </div>
            </div>
            <nav class="mt-2">
                <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                    <li class="nav-item">
                        <a href="{{ route('gerente.home') }}" class="nav-link {{ request()->routeIs('gerente.home') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-home"></i>
                            <p>Dashboard</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('gerente.cambistas') }}" class="nav-link {{ request()->routeIs('gerente.cambistas*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-users"></i>
                            <p>Cambistas</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('gerente.caixa') }}" class="nav-link {{ request()->routeIs('gerente.caixa*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-university"></i>
                            <p>Caixa</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('gerente.bilhetes') }}" class="nav-link {{ request()->routeIs('gerente.bilhetes*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-receipt"></i>
                            <p>Bilhetes</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('gerente.relatorio') }}" class="nav-link {{ request()->routeIs('gerente.relatorio*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-chart-bar"></i>
                            <p>Relatório</p>
                        </a>
                    </li>
                    <li class="nav-header" style="color: rgba(255,255,255,.4); font-size: 0.75rem;">CONTA</li>
                    <li class="nav-item">
                        <a href="{{ route('gerente.perfil') }}" class="nav-link {{ request()->routeIs('gerente.perfil*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-user-cog"></i>
                            <p>Meu Perfil</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('logout.get') }}" class="nav-link">
                            <i class="nav-icon fas fa-sign-out-alt"></i>
                            <p>Sair</p>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    </aside>

    <div class="content-wrapper" style="min-height: calc(100vh - 57px);">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 style="font-weight: 700; font-size: 1.4rem; color: #1e293b;">
                            @yield('content_header', 'Página')
                        </h1>
                    </div>
                </div>
            </div>
        </section>
        <section class="content">
            <div class="container-fluid">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                        {{ session('success') }}
                    </div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                        {{ session('error') }}
                    </div>
                @endif
                @yield('content')
            </div>
        </section>
    </div>

    <footer class="main-footer text-center" style="font-size: 0.8rem; color: #6c757d;">
        <strong>IHUB BETS</strong> v2.1.0 - Painel do Gerente
    </footer>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/toastr@2.1.4/build/toastr.min.js"></script>
<script>
    toastr.options = { positionClass: 'toast-top-right', timeOut: 3000, progressBar: true };
    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

    (function tick() {
        var el = document.getElementById('live-clock');
        if (el) {
            var now = new Date();
            el.textContent = now.toLocaleTimeString('pt-BR') + ' - ' + now.toLocaleDateString('pt-BR', { weekday: 'long', day: '2-digit', month: 'long', year: 'numeric' });
        }
        setTimeout(tick, 1000);
    })();
</script>
@stack('js')
@yield('js')
</body>
</html>
