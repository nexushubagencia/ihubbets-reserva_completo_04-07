<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $provider->name }} - {{ __('casino.title') }} - {{ config('app.name') }}</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
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
            --sidebar-w: 220px;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
        }
        a { text-decoration: none; color: inherit; }

        .navbar-casino {
            background: var(--primary);
            color: #fff;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 2px 8px rgba(0,0,0,0.12);
        }
        .navbar-casino .nav-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px 16px;
        }
        .navbar-casino .brand {
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 700;
            font-size: 18px;
            color: #fff;
        }
        .navbar-casino .nav-links {
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .navbar-casino .nav-links a {
            color: rgba(255,255,255,0.85);
            padding: 6px 14px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 500;
            transition: all 0.2s;
        }
        .navbar-casino .nav-links a:hover,
        .navbar-casino .nav-links a.active {
            background: rgba(255,255,255,0.15);
            color: #fff;
        }
        .navbar-casino .nav-right {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .navbar-casino .lang-select {
            background: rgba(255,255,255,0.15);
            border: 1px solid rgba(255,255,255,0.2);
            color: #fff;
            padding: 5px 8px;
            border-radius: 6px;
            font-size: 12px;
            cursor: pointer;
        }
        .navbar-casino .lang-select option { color: #333; background: #fff; }
        .navbar-casino .btn-auth {
            color: #fff;
            padding: 6px 14px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            transition: all 0.2s;
        }
        .navbar-casino .btn-auth:hover { background: rgba(255,255,255,0.15); }
        .navbar-casino .btn-auth-reg { background: rgba(255,255,255,0.2); }
        .saldo-pill {
            background: rgba(0,0,0,0.2);
            padding: 6px 14px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 13px;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .hamburger {
            display: none;
            background: none;
            border: none;
            color: #fff;
            font-size: 20px;
            cursor: pointer;
        }

        .layout-wrapper { display: flex; min-height: calc(100vh - 50px); }

        .sidebar {
            width: var(--sidebar-w);
            background: var(--card-bg);
            border-right: 1px solid var(--border);
            position: sticky;
            top: 50px;
            height: calc(100vh - 50px);
            overflow-y: auto;
            flex-shrink: 0;
            padding: 12px 0;
        }
        .sidebar-menu-header {
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: var(--text-muted);
            padding: 8px 16px 6px;
        }
        .sidebar-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 9px 16px;
            font-size: 13px;
            font-weight: 500;
            color: var(--text);
            transition: all 0.15s;
        }
        .sidebar-item:hover { background: var(--primary-light); color: var(--primary); }
        .sidebar-item.active { background: var(--primary-light); color: var(--primary); font-weight: 600; }
        .sidebar-item i { width: 18px; text-align: center; font-size: 14px; }
        .sidebar-item .count {
            margin-left: auto;
            font-size: 11px;
            color: var(--text-muted);
            background: var(--bg);
            padding: 2px 7px;
            border-radius: 10px;
        }
        .sidebar-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.4); z-index: 1001; }

        .main-content { flex: 1; min-width: 0; padding: 20px; }

        .provider-page-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 20px;
        }
        .provider-page-header .back-btn {
            display: flex;
            align-items: center;
            gap: 6px;
            color: var(--primary);
            font-weight: 600;
            font-size: 14px;
        }
        .provider-page-header h1 {
            font-size: 22px;
            font-weight: 700;
        }

        .games-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(145px, 1fr));
            gap: 12px;
        }
        .game-card {
            border-radius: 10px;
            overflow: hidden;
            background: var(--card-bg);
            border: 1px solid var(--border);
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .game-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 24px rgba(0,0,0,0.12);
        }
        .game-thumb {
            aspect-ratio: 1 / 1;
            background: linear-gradient(135deg, #e8f0fe, #f5f7fa);
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            position: relative;
        }
        .game-thumb img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s; }
        .game-card:hover .game-thumb img { transform: scale(1.05); }
        .game-thumb .placeholder-icon { font-size: 36px; color: var(--border); }
        .game-overlay {
            position: absolute; inset: 0;
            background: rgba(0,0,0,0.6);
            display: flex; align-items: center; justify-content: center;
            opacity: 0; transition: opacity 0.2s;
        }
        .game-card:hover .game-overlay { opacity: 1; }
        .play-btn {
            background: var(--primary); color: #fff;
            padding: 8px 18px; border-radius: 50px;
            font-weight: 700; font-size: 12px;
            display: flex; align-items: center; gap: 5px;
            box-shadow: 0 4px 12px rgba(26,115,232,0.4);
        }
        .game-info { padding: 8px 10px; }
        .game-name { font-size: 12px; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

        .pagination-wrap {
            display: flex;
            justify-content: center;
            margin-top: 28px;
            gap: 6px;
        }
        .pagination-wrap a, .pagination-wrap span {
            padding: 8px 14px;
            border-radius: 8px;
            background: var(--card-bg);
            color: var(--text);
            text-decoration: none;
            border: 1px solid var(--border);
            font-size: 13px;
        }
        .pagination-wrap .active { background: var(--primary); border-color: var(--primary); color: #fff; }

        .empty-state { text-align: center; padding: 60px 20px; color: var(--text-muted); }
        .empty-state i { font-size: 48px; margin-bottom: 12px; color: var(--border); display: block; }

        @media (max-width: 991px) {
            .sidebar { position: fixed; left: -260px; top: 0; height: 100vh; z-index: 1002; transition: left 0.3s; width: 250px; padding-top: 60px; }
            .sidebar.open { left: 0; }
            .sidebar-overlay.show { display: block; }
            .hamburger { display: block; }
            .navbar-casino .nav-links { display: none; }
            .main-content { padding: 16px 12px; }
            .games-grid { grid-template-columns: repeat(auto-fill, minmax(120px, 1fr)); gap: 8px; }
        }
    </style>
</head>
<body>
    <header class="navbar-casino">
        <div class="nav-top">
            <div style="display:flex;align-items:center;gap:12px;">
                <button class="hamburger" onclick="toggleSidebar()"><i class="fas fa-bars"></i></button>
                <a href="{{ url('/') }}" class="brand"><i class="fas fa-bolt"></i> {{ config('app.name') }}</a>
            </div>
            <div class="nav-links">
                <a href="{{ url('/') }}"><i class="fas fa-futbol"></i> {{ __('casino.sports') }}</a>
                <a href="{{ route('cassino.index') }}" class="active"><i class="fas fa-dice"></i> {{ __('casino.title') }}</a>
            </div>
            <div class="nav-right">
                <form id="lang-form" method="GET" action="{{ route('cassino.index', ['provider' => $provider->code]) }}" style="display:none;">
                    <input type="hidden" name="lang" id="lang-input">
                </form>
                <select class="lang-select" onchange="document.getElementById('lang-input').value=this.value;document.getElementById('lang-form').submit();">
                    <option value="pt_BR" {{ app()->getLocale()=='pt_BR'?'selected':'' }}>PT</option>
                    <option value="en" {{ app()->getLocale()=='en'?'selected':'' }}>EN</option>
                    <option value="es" {{ app()->getLocale()=='es'?'selected':'' }}>ES</option>
                </select>
                @auth
                    <div class="saldo-pill"><i class="fas fa-wallet"></i> R$ {{ number_format(auth()->user()->credito ?? auth()->user()->balance ?? 0, 2, ',', '.') }}</div>
                @endauth
                @guest
                    <a href="{{ url('/login') }}" class="btn-auth">{{ __('casino.login') }}</a>
                    <a href="{{ url('/register') }}" class="btn-auth btn-auth-reg">{{ __('casino.register') }}</a>
                @endguest
            </div>
        </div>
    </header>

    <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

    <div class="layout-wrapper">
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-menu-header">{{ __('casino.menu_main') }}</div>
            <a href="{{ route('cassino.index') }}" class="sidebar-item">
                <i class="fas fa-home"></i> {{ __('casino.home') }}
            </a>
            <div class="sidebar-menu-header" style="margin-top:12px;">{{ __('casino.providers') }}</div>
            @foreach($providers as $prov)
                <a href="{{ route('cassino.index', ['provider' => $prov->code]) }}" class="sidebar-item {{ $provider->code == $prov->code ? 'active' : '' }}">
                    <i class="fas fa-gamepad"></i>
                    <span>{{ $prov->name }}</span>
                    <span class="count">{{ $prov->games_count }}</span>
                </a>
            @endforeach
        </aside>

        <main class="main-content">
            <div class="provider-page-header">
                <a href="{{ route('cassino.index') }}" class="back-btn"><i class="fas fa-arrow-left"></i> {{ __('casino.back') }}</a>
                <h1>{{ $provider->name }}</h1>
            </div>

            @if($games->count() > 0)
                <div class="games-grid">
                    @foreach($games as $game)
                        @include('cassino._game_card', ['game' => $game, 'showProvider' => false])
                    @endforeach
                </div>

                @if($games->hasPages())
                    <div class="pagination-wrap">
                        @if($games->currentPage() > 1)
                            <a href="{{ $games->url($games->currentPage() - 1) }}">{!! __('casino.prev') !!}</a>
                        @endif
                        @foreach($games->getUrlRange(max(1, $games->currentPage() - 2), min($games->lastPage(), $games->currentPage() + 2)) as $page => $url)
                            <a href="{{ $url }}" class="{{ $page == $games->currentPage() ? 'active' : '' }}">{{ $page }}</a>
                        @endforeach
                        @if($games->currentPage() < $games->lastPage())
                            <a href="{{ $games->url($games->currentPage() + 1) }}">{!! __('casino.next') !!}</a>
                        @endif
                    </div>
                @endif
            @else
                <div class="empty-state">
                    <i class="fas fa-gamepad"></i>
                    <h3>{{ __('casino.no_games_found') }}</h3>
                </div>
            @endif
        </main>
    </div>

    <script>
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('open');
            document.getElementById('sidebarOverlay').classList.toggle('show');
        }
        function launchGame(gameId) {
            @guest window.location.href='/login'; return; @endguest
            fetch('/api/casino/games/'+gameId+'/launch',{method:'POST',headers:{'X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content,'Accept':'application/json','Content-Type':'application/json'}})
            .then(function(r){return r.json();}).then(function(d){if(d.gameUrl){window.open(d.gameUrl,'_blank');}else{showToast(d.error||'{{ __("casino.game_launch_error") }}');}}).catch(function(){showToast('{{ __("casino.game_launch_error") }}');});
        }
        function showToast(m){var t=document.getElementById('toast');t.textContent=m;t.style.display='block';setTimeout(function(){t.style.display='none';},3500);}
    </script>
    <div class="toast-msg" id="toast" style="position:fixed;bottom:24px;left:50%;transform:translateX(-50%);background:#323232;color:#fff;padding:10px 24px;border-radius:10px;font-size:13px;z-index:2000;display:none;box-shadow:0 6px 20px rgba(0,0,0,0.3);"></div>
</body>
</html>
