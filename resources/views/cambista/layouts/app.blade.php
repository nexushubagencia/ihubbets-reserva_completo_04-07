<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'IHUB BETS') }} - Painel Cambista</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #1a73e8;
            --primary-dark: #1557b0;
            --sidebar-bg: #1e293b;
            --sidebar-text: #94a3b8;
            --sidebar-active: #3b82f6;
            --body-bg: #f1f5f9;
            --card-shadow: 0 1px 3px rgba(0,0,0,.08);
        }
        * { box-sizing: border-box; }
        body { margin: 0; padding: 0; background: var(--body-bg); font-family: 'Segoe UI', system-ui, -apple-system, sans-serif; font-size: 14px; }
        
        .sidebar {
            position: fixed; top: 0; left: 0; width: 240px; height: 100vh;
            background: var(--sidebar-bg); color: var(--sidebar-text); z-index: 1000;
            display: flex; flex-direction: column; transition: transform .2s;
        }
        .sidebar-brand {
            padding: 20px; font-size: 18px; font-weight: 700; color: #fff;
            border-bottom: 1px solid rgba(255,255,255,.08);
        }
        .sidebar-brand small { display: block; font-size: 11px; font-weight: 400; color: var(--sidebar-text); margin-top: 2px; }
        .sidebar-nav { flex: 1; padding: 12px 0; overflow-y: auto; }
        .sidebar-nav a {
            display: flex; align-items: center; gap: 12px; padding: 11px 20px;
            color: var(--sidebar-text); text-decoration: none; font-size: 14px; transition: all .15s;
        }
        .sidebar-nav a:hover { background: rgba(255,255,255,.06); color: #e2e8f0; }
        .sidebar-nav a.active { background: rgba(59,130,246,.15); color: var(--sidebar-active); border-right: 3px solid var(--sidebar-active); }
        .sidebar-nav a i { width: 20px; text-align: center; font-size: 16px; }
        .sidebar-footer { padding: 16px 20px; border-top: 1px solid rgba(255,255,255,.08); font-size: 12px; }
        .sidebar-footer a { color: #ef4444; text-decoration: none; }
        .sidebar-footer a:hover { color: #f87171; }

        .main-content { margin-left: 240px; min-height: 100vh; }
        .topbar {
            background: #fff; padding: 12px 24px; border-bottom: 1px solid #e2e8f0;
            display: flex; align-items: center; justify-content: space-between;
        }
        .topbar-user { display: flex; align-items: center; gap: 8px; font-weight: 500; }
        .topbar-user i { color: var(--primary); }
        .page-content { padding: 24px; }

        .mobile-toggle {
            display: none; background: none; border: none; font-size: 20px; color: #333; cursor: pointer;
        }

        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.show { transform: translateX(0); }
            .main-content { margin-left: 0; }
            .mobile-toggle { display: block; }
            .sidebar-overlay {
                display: none; position: fixed; inset: 0; background: rgba(0,0,0,.4); z-index: 999;
            }
            .sidebar-overlay.show { display: block; }
        }
    </style>
    @stack('styles')
</head>
<body>
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>
    
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <i class="fas fa-ticket-alt"></i> {{ config('app.name', 'IHUB BETS') }}
            <small>Painel do Cambista</small>
        </div>
        <nav class="sidebar-nav">
            <a href="{{ route('cambista.home') }}" class="{{ request()->routeIs('cambista.home') ? 'active' : '' }}">
                <i class="fas fa-home"></i> Início
            </a>
            <a href="{{ route('cambista.bilhetes') }}" class="{{ request()->routeIs('cambista.bilhete*') ? 'active' : '' }}">
                <i class="fas fa-receipt"></i> Bilhetes
            </a>
            <a href="{{ route('cambista.relatorio') }}" class="{{ request()->routeIs('cambista.relatorio') ? 'active' : '' }}">
                <i class="fas fa-chart-bar"></i> Relatório
            </a>
            <a href="{{ route('cambista.perfil') }}" class="{{ request()->routeIs('cambista.perfil') ? 'active' : '' }}">
                <i class="fas fa-user-circle"></i> Perfil
            </a>
        </nav>
        <div class="sidebar-footer">
            <a href="{{ route('logout.get') }}"><i class="fas fa-sign-out-alt"></i> Sair</a>
        </div>
    </aside>

    <div class="main-content">
        <div class="topbar">
            <div class="d-flex align-items-center gap-3">
                <button class="mobile-toggle" onclick="toggleSidebar()"><i class="fas fa-bars"></i></button>
                <div class="topbar-user">
                    <i class="fas fa-user-circle"></i>
                    Cambista: <strong>{{ $user->name ?? Auth::user()->name }}</strong>
                </div>
            </div>
            <div>
                <a href="{{ route('cambista.perfil') }}" class="text-decoration-none text-muted me-3">
                    <i class="fas fa-cog"></i>
                </a>
                <a href="{{ route('logout.get') }}" class="text-decoration-none text-danger">
                    <i class="fas fa-sign-out-alt"></i> Sair
                </a>
            </div>
        </div>
        <div class="page-content">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @yield('content')
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script>
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('show');
            document.getElementById('sidebarOverlay').classList.toggle('show');
        }
        toastr.options = { positionClass: 'toast-top-right', timeOut: 3000, progressBar: true };
        $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });
    </script>
    @stack('scripts')
</body>
</html>
