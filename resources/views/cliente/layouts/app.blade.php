<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'IHUB BETS') }} - @yield('title', 'Área do Cliente')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #1a73e8;
            --primary-dark: #1557b0;
            --primary-light: #e8f0fe;
            --bg: #f5f7fa;
            --card-bg: #ffffff;
            --text: #202124;
            --text-muted: #5f6368;
            --border: #dadce0;
            --success: #34a853;
            --danger: #ea4335;
            --warning: #fbbc04;
        }
        * { box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: var(--bg);
            color: var(--text);
            margin: 0;
            padding: 0;
            padding-bottom: 70px;
            min-height: 100vh;
        }
        .navbar-client {
            background: var(--primary);
            color: #fff;
            padding: 0;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 2px 8px rgba(0,0,0,0.12);
        }
        .navbar-client .nav-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 16px;
        }
        .navbar-client .brand {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            color: #fff;
            font-weight: 700;
            font-size: 18px;
        }
        .navbar-client .brand img {
            height: 32px;
        }
        .navbar-client .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .navbar-client .user-name {
            color: rgba(255,255,255,0.9);
            font-size: 13px;
            text-align: right;
        }
        .navbar-client .user-name strong {
            display: block;
            color: #fff;
            font-size: 14px;
        }
        .saldo-bar {
            background: rgba(0,0,0,0.15);
            padding: 8px 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 13px;
        }
        .saldo-bar .saldo-valor {
            color: #fff;
            font-weight: 600;
            font-size: 16px;
        }
        .saldo-bar .saldo-label {
            color: rgba(255,255,255,0.7);
        }
        .bottom-nav {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: var(--card-bg);
            border-top: 1px solid var(--border);
            display: flex;
            justify-content: space-around;
            padding: 6px 0 10px;
            z-index: 1000;
            box-shadow: 0 -2px 8px rgba(0,0,0,0.08);
        }
        .bottom-nav a {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-decoration: none;
            color: var(--text-muted);
            font-size: 11px;
            gap: 2px;
            padding: 4px 12px;
            transition: color 0.2s;
        }
        .bottom-nav a i { font-size: 20px; }
        .bottom-nav a.active, .bottom-nav a:hover {
            color: var(--primary);
        }
        .page-content { padding: 16px; max-width: 600px; margin: 0 auto; }
        .card-client {
            background: var(--card-bg);
            border-radius: 12px;
            border: 1px solid var(--border);
            padding: 16px;
            margin-bottom: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.06);
        }
        .card-client .card-title {
            font-size: 12px;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 6px;
        }
        .card-client .card-value {
            font-size: 24px;
            font-weight: 700;
            color: var(--text);
        }
        .card-client .card-value.text-primary { color: var(--primary) !important; }
        .card-client .card-value.text-success { color: var(--success) !important; }
        .card-client .card-value.text-warning { color: var(--warning) !important; }
        .badge-status {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            display: inline-block;
        }
        .badge-aberto { background: #e8f0fe; color: #1a73e8; }
        .badge-ganhou { background: #e6f4ea; color: #34a853; }
        .badge-perdeu { background: #fce8e6; color: #ea4335; }
        .badge-cancelado { background: #f1f3f4; color: #5f6368; }
        .btn-primary {
            background: var(--primary);
            border-color: var(--primary);
            border-radius: 8px;
            font-weight: 600;
            padding: 10px 20px;
        }
        .btn-primary:hover {
            background: var(--primary-dark);
            border-color: var(--primary-dark);
        }
        .btn-outline-primary {
            color: var(--primary);
            border-color: var(--primary);
            border-radius: 8px;
            font-weight: 600;
        }
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
            margin-bottom: 16px;
        }
        .quick-action {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 6px;
            padding: 14px 8px;
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 10px;
            text-decoration: none;
            color: var(--text);
            font-size: 12px;
            font-weight: 500;
            transition: all 0.2s;
        }
        .quick-action i {
            font-size: 22px;
            color: var(--primary);
        }
        .quick-action:hover {
            background: var(--primary-light);
            border-color: var(--primary);
            color: var(--primary);
        }
        .list-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid #f1f3f4;
            text-decoration: none;
            color: inherit;
        }
        .list-item:last-child { border-bottom: none; }
        .list-item:hover { background: #f8f9fa; margin: 0 -16px; padding: 12px 16px; border-radius: 8px; }
        .nav-tabs .nav-link {
            border: none;
            color: var(--text-muted);
            font-weight: 500;
            font-size: 13px;
            padding: 8px 12px;
        }
        .nav-tabs .nav-link.active {
            color: var(--primary);
            border-bottom: 2px solid var(--primary);
            background: transparent;
        }
        .table { font-size: 13px; }
        .table th { font-weight: 600; color: var(--text-muted); font-size: 11px; text-transform: uppercase; letter-spacing: 0.3px; }
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: var(--text-muted);
        }
        .empty-state i { font-size: 48px; margin-bottom: 12px; color: var(--border); }
        @media (max-width: 576px) {
            .page-content { padding: 12px; }
            .card-client .card-value { font-size: 20px; }
        }
    </style>
    @stack('styles')
</head>
<body>
    <nav class="navbar-client">
        <div class="nav-top">
            <a href="{{ route('cliente.home') }}" class="brand">
                <i class="fas fa-bolt"></i>
                {{ config('app.name', 'IHUB BETS') }}
            </a>
            <div class="user-info">
                <div class="user-name">
                    Olá, <strong>{{ Auth::user()->name }}</strong>
                </div>
                <a href="{{ route('cliente.perfil') }}" style="color:#fff; font-size:18px;">
                    <i class="fas fa-user-circle"></i>
                </a>
            </div>
        </div>
        @php
            $saldoUser = Auth::user();
            $saldo = (float) ($saldoUser->credito ?? $saldoUser->balance ?? 0);
        @endphp
        <div class="saldo-bar">
            <span class="saldo-label">Saldo Disponível</span>
            <span class="saldo-valor">R$ {{ number_format($saldo, 2, ',', '.') }}</span>
        </div>
    </nav>

    <div class="page-content">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert" style="border-radius:8px; font-size:13px;">
                <i class="fas fa-check-circle me-1"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert" style="border-radius:8px; font-size:13px;">
                <i class="fas fa-exclamation-circle me-1"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @yield('content')
    </div>

    <nav class="bottom-nav">
        <a href="{{ route('cliente.home') }}" class="{{ request()->routeIs('cliente.home') ? 'active' : '' }}">
            <i class="fas fa-home"></i>
            Home
        </a>
        <a href="{{ route('cliente.apostas') }}" class="{{ request()->routeIs('cliente.apostas') || request()->routeIs('cliente.aposta-detail') ? 'active' : '' }}">
            <i class="fas fa-ticket-alt"></i>
            Apostas
        </a>
        <a href="{{ route('cliente.financeiro') }}" class="{{ request()->routeIs('cliente.financeiro') || request()->routeIs('cliente.depositos') || request()->routeIs('cliente.saques') ? 'active' : '' }}">
            <i class="fas fa-wallet"></i>
            Financeiro
        </a>
        <a href="{{ route('cliente.perfil') }}" class="{{ request()->routeIs('cliente.perfil') ? 'active' : '' }}">
            <i class="fas fa-user"></i>
            Perfil
        </a>
    </nav>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script>
        toastr.options = { positionClass: 'toast-top-right', timeOut: 3000, progressBar: true };
        $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });
        @if(session('success'))
            toastr.success('{{ session("success") }}');
        @endif
        @if(session('error'))
            toastr.error('{{ session("error") }}');
        @endif
    </script>
    @stack('js')
</body>
</html>
