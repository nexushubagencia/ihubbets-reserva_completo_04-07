<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('casino.title') }} - {{ config('app.name') }}</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #1a73e8;
            --primary-dark: #1557b0;
            --primary-light: #e8f0fe;
            --bg: #f0f2f5;
            --card-bg: #ffffff;
            --text: #1a1d21;
            --text-secondary: #606770;
            --border: #e4e6eb;
            --sidebar-w: 230px;
            --navbar-h: 52px;
            --radius: 10px;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
        }
        a { text-decoration: none; color: inherit; }

        .topbar {
            background: var(--card-bg);
            height: var(--navbar-h);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 20px;
            position: sticky;
            top: 0;
            z-index: 1000;
            border-bottom: 1px solid var(--border);
        }
        .topbar-left { display: flex; align-items: center; gap: 16px; }
        .topbar-brand {
            display: flex; align-items: center; gap: 8px;
            font-weight: 800; font-size: 17px; color: var(--primary);
        }
        .topbar-brand i { font-size: 20px; }
        .topbar-nav { display: flex; gap: 4px; }
        .topbar-nav a {
            padding: 8px 16px; border-radius: 8px;
            font-size: 13px; font-weight: 600; color: var(--text-secondary);
            transition: all 0.15s; display: flex; align-items: center; gap: 6px;
        }
        .topbar-nav a:hover { background: var(--primary-light); color: var(--primary); }
        .topbar-nav a.active { background: var(--primary); color: #fff; }
        .topbar-right { display: flex; align-items: center; gap: 10px; }
        .lang-btn {
            background: var(--card-bg); border: 1px solid var(--border);
            padding: 5px 8px; border-radius: 6px;
            font-size: 12px; font-weight: 700; color: var(--text-secondary); cursor: pointer;
            font-family: inherit;
        }
        .lang-btn:hover { border-color: var(--primary); color: var(--primary); }
        .lang-btn option { color: #333; background: #fff; }
        .btn-login {
            padding: 7px 18px; border-radius: 8px; font-size: 13px; font-weight: 600;
            color: var(--primary); border: 1px solid var(--primary);
            transition: all 0.15s; cursor: pointer; background: transparent;
        }
        .btn-login:hover { background: var(--primary); color: #fff; }
        .btn-reg {
            padding: 7px 18px; border-radius: 8px; font-size: 13px; font-weight: 600;
            background: var(--primary); color: #fff; border: none;
            transition: all 0.15s; cursor: pointer;
        }
        .btn-reg:hover { background: var(--primary-dark); }
        .saldo-badge {
            background: var(--primary-light); padding: 6px 14px; border-radius: 50px;
            font-size: 13px; font-weight: 700; color: var(--primary);
            display: flex; align-items: center; gap: 6px;
        }
        .hamburger-btn {
            display: none; background: none; border: none; font-size: 18px;
            color: var(--text); cursor: pointer;
        }

        .layout { display: flex; min-height: calc(100vh - var(--navbar-h)); }

        .sidebar {
            width: var(--sidebar-w); background: var(--card-bg);
            border-right: 1px solid var(--border);
            position: sticky; top: var(--navbar-h);
            height: calc(100vh - var(--navbar-h));
            overflow-y: auto; flex-shrink: 0; padding: 10px 0;
        }
        .sidebar::-webkit-scrollbar { width: 4px; }
        .sidebar::-webkit-scrollbar-thumb { background: var(--border); border-radius: 4px; }
        .sb-label {
            font-size: 10px; font-weight: 700; text-transform: uppercase;
            letter-spacing: 0.6px; color: var(--text-secondary);
            padding: 10px 14px 6px; opacity: 0.7;
        }
        .sb-item {
            display: flex; align-items: center; gap: 10px;
            padding: 9px 14px; font-size: 13px; font-weight: 500;
            color: var(--text); cursor: pointer; transition: all 0.12s;
            border-left: 3px solid transparent;
        }
        .sb-item:hover { background: var(--primary-light); color: var(--primary); }
        .sb-item.active {
            background: var(--primary-light); color: var(--primary);
            font-weight: 600; border-left-color: var(--primary);
        }
        .sb-item i { width: 18px; text-align: center; font-size: 14px; opacity: 0.7; }
        .sb-count {
            margin-left: auto; font-size: 11px; color: var(--text-secondary);
            background: var(--bg); padding: 1px 8px; border-radius: 10px;
        }
        .sb-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.4); z-index: 1001; }

        .content { flex: 1; min-width: 0; padding: 20px 24px; }

        .search-box { position: relative; max-width: 480px; margin-bottom: 20px; }
        .search-box input {
            width: 100%; padding: 10px 14px 10px 40px;
            border: 1px solid var(--border); border-radius: 10px;
            background: var(--card-bg); font-size: 14px; color: var(--text);
            outline: none; transition: border-color 0.2s; font-family: inherit;
        }
        .search-box input:focus { border-color: var(--primary); }
        .search-box input::placeholder { color: var(--text-secondary); }
        .search-box i {
            position: absolute; left: 14px; top: 50%;
            transform: translateY(-50%); color: var(--text-secondary); font-size: 14px;
        }

        .provider-section { margin-bottom: 30px; }
        .section-header {
            display: flex; align-items: center; justify-content: space-between;
            margin-bottom: 12px;
        }
        .section-title {
            font-size: 17px; font-weight: 800; color: var(--text);
            display: flex; align-items: center; gap: 8px;
        }
        .title-icon {
            width: 28px; height: 28px; border-radius: 8px;
            background: var(--primary); color: #fff;
            display: flex; align-items: center; justify-content: center;
            font-size: 13px;
        }
        .see-all {
            font-size: 12px; font-weight: 600; color: var(--primary);
            padding: 5px 14px; border: 1px solid var(--primary);
            border-radius: 50px; transition: all 0.15s;
            display: inline-flex; align-items: center; gap: 4px;
        }
        .see-all:hover { background: var(--primary); color: #fff; }

        .carousel-wrap { position: relative; }
        .games-row {
            display: flex; gap: 12px; overflow-x: auto;
            scroll-behavior: smooth; scrollbar-width: none; padding: 4px 0;
        }
        .games-row::-webkit-scrollbar { display: none; }
        .arrow-btn {
            position: absolute; top: 40%; transform: translateY(-50%);
            width: 36px; height: 36px; border-radius: 50%;
            background: var(--card-bg); border: 1px solid var(--border);
            color: var(--text); cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            z-index: 10; transition: all 0.15s;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1); font-size: 12px;
        }
        .arrow-btn:hover { background: var(--primary); color: #fff; border-color: var(--primary); }
        .arrow-l { left: -16px; }
        .arrow-r { right: -16px; }

        .game-card {
            flex: 0 0 160px; border-radius: var(--radius); overflow: hidden;
            background: var(--card-bg); border: 1px solid var(--border);
            cursor: pointer; position: relative;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .game-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 28px rgba(0,0,0,0.12);
        }
        .card-img {
            aspect-ratio: 3/4; background: linear-gradient(135deg, #e8f0fe 0%, #f0f2f5 100%);
            display: flex; align-items: center; justify-content: center;
            overflow: hidden; position: relative;
        }
        .card-img img {
            width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s;
        }
        .game-card:hover .card-img img { transform: scale(1.06); }
        .ph-icon { font-size: 40px; color: #c4c9d1; }
        .ph-icon.hidden { display: none; }
        .card-overlay {
            position: absolute; inset: 0;
            background: linear-gradient(180deg, rgba(0,0,0,0) 40%, rgba(0,0,0,0.7) 100%);
            display: flex; align-items: flex-end; justify-content: center;
            padding-bottom: 14px; opacity: 0; transition: opacity 0.2s;
        }
        .game-card:hover .card-overlay { opacity: 1; }
        .card-play {
            background: var(--primary); color: #fff;
            padding: 8px 20px; border-radius: 50px;
            font-weight: 700; font-size: 12px;
            display: flex; align-items: center; gap: 6px;
            box-shadow: 0 4px 14px rgba(26,115,232,0.45);
            transform: translateY(8px); transition: transform 0.2s;
        }
        .game-card:hover .card-play { transform: translateY(0); }
        .card-body { padding: 10px 12px; }
        .card-name {
            font-size: 12px; font-weight: 600; color: var(--text);
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
            margin-bottom: 2px;
        }
        .card-prov { font-size: 10px; font-weight: 500; color: var(--text-secondary); }
        .card-rank {
            position: absolute; top: 0; left: 0;
            background: var(--primary); color: #fff;
            font-size: 10px; font-weight: 800;
            padding: 3px 8px; border-radius: 0 0 8px 0; z-index: 2;
        }

        .empty-state { text-align: center; padding: 60px 20px; color: var(--text-secondary); }
        .empty-state i { font-size: 48px; margin-bottom: 12px; color: var(--border); display: block; }

        .footer-bar {
            background: var(--card-bg); border-top: 1px solid var(--border);
            padding: 20px; text-align: center; margin-top: 20px;
        }
        .footer-logos { display: flex; justify-content: center; gap: 16px; margin-bottom: 8px; flex-wrap: wrap; }
        .footer-logos span { font-size: 11px; color: var(--text-secondary); font-weight: 600; }
        .footer-copy { font-size: 11px; color: var(--text-secondary); }

        .toast-msg {
            position: fixed; bottom: 24px; left: 50%; transform: translateX(-50%);
            background: #1a1d21; color: #fff; padding: 10px 24px; border-radius: 10px;
            font-size: 13px; z-index: 2000; display: none;
            box-shadow: 0 6px 20px rgba(0,0,0,0.3);
        }

        @media (max-width: 991px) {
            .sidebar {
                position: fixed; left: -260px; top: 0; height: 100vh;
                z-index: 1002; transition: left 0.3s; width: 260px;
                padding-top: var(--navbar-h); box-shadow: 4px 0 20px rgba(0,0,0,0.15);
            }
            .sidebar.open { left: 0; }
            .sb-overlay.show { display: block; }
            .hamburger-btn { display: block; }
            .topbar-nav { display: none; }
            .content { padding: 16px 12px; }
            .game-card { flex: 0 0 130px; }
            .arrow-btn { display: none; }
        }
        @media (max-width: 576px) {
            .game-card { flex: 0 0 120px; }
            .card-body { padding: 8px; }
            .card-name { font-size: 11px; }
            .topbar { padding: 0 12px; }
            .btn-login, .btn-reg { padding: 6px 12px; font-size: 12px; }
        }
    </style>
</head>
<body>
    <header class="topbar">
        <div class="topbar-left">
            <button class="hamburger-btn" onclick="toggleSidebar()"><i class="fas fa-bars"></i></button>
            <a href="{{ url('/') }}" class="topbar-brand">
                <i class="fas fa-bolt"></i>
                {{ config('app.name') }}
            </a>
            <nav class="topbar-nav">
                <a href="{{ url('/') }}"><i class="fas fa-futbol"></i> {{ __('casino.sports') }}</a>
                <a href="{{ route('cassino.index') }}" class="active"><i class="fas fa-dice"></i> {{ __('casino.title') }}</a>
            </nav>
        </div>
        <div class="topbar-right">
            <form id="lang-form" method="GET" action="{{ route('cassino.index') }}" style="display:none;">
                <input type="hidden" name="lang" id="lang-input">
                @if(request('search'))<input type="hidden" name="search" value="{{ request('search') }}">@endif
                @if(request('provider'))<input type="hidden" name="provider" value="{{ request('provider') }}">@endif
            </form>
            <select class="lang-btn" onchange="document.getElementById('lang-input').value=this.value;document.getElementById('lang-form').submit();">
                <option value="pt_BR" {{ app()->getLocale()=='pt_BR'?'selected':'' }}>PT</option>
                <option value="en" {{ app()->getLocale()=='en'?'selected':'' }}>EN</option>
                <option value="es" {{ app()->getLocale()=='es'?'selected':'' }}>ES</option>
            </select>
            @auth
                <div class="saldo-badge">
                    <i class="fas fa-wallet"></i>
                    R$ {{ number_format(auth()->user()->credito ?? auth()->user()->balance ?? 0, 2, ',', '.') }}
                </div>
            @endauth
            @guest
                <a href="{{ url('/login') }}" class="btn-login">{{ __('casino.login') }}</a>
                <a href="{{ url('/register') }}" class="btn-reg">{{ __('casino.register') }}</a>
            @endguest
        </div>
    </header>

    <div class="sb-overlay" id="sbOverlay" onclick="toggleSidebar()"></div>

    <div class="layout">
        <aside class="sidebar" id="sidebar">
            <div class="sb-label">{{ __('casino.menu_main') }}</div>
            <a href="{{ route('cassino.index') }}" class="sb-item {{ !request('provider') ? 'active' : '' }}">
                <i class="fas fa-home"></i> {{ __('casino.home') }}
            </a>
            <a href="{{ route('cassino.index') }}#top-games" class="sb-item">
                <i class="fas fa-fire" style="color:#e74c3c;"></i> {{ __('casino.popular') }}
            </a>

            <div class="sb-label" style="margin-top:10px;">{{ __('casino.providers') }}</div>
            @foreach($providers as $prov)
                <a href="{{ route('cassino.index', ['provider' => $prov->name]) }}" class="sb-item {{ request('provider') == $prov->name ? 'active' : '' }}">
                    <i class="fas fa-gamepad"></i>
                    <span>{{ $prov->name }}</span>
                    <span class="sb-count">{{ $prov->games_count }}</span>
                </a>
            @endforeach
        </aside>

        <main class="content">
            <form method="GET" action="{{ route('cassino.index') }}" class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('casino.search_placeholder') }}">
            </form>

            @if(!request('search') && !request('provider') && $topGames->count() > 0)
                <div class="provider-section" id="top-games">
                    <div class="section-header">
                        <h2 class="section-title">
                            <span class="title-icon"><i class="fas fa-fire"></i></span>
                            {{ __('casino.top_games') }}
                        </h2>
                    </div>
                    <div class="carousel-wrap">
                        <button type="button" class="arrow-btn arrow-l" onclick="scrollRow(this,-1)"><i class="fas fa-chevron-left"></i></button>
                        <div class="games-row">
                            @foreach($topGames as $i => $game)
                                <div class="game-card" onclick="launchGame({{ $game->id }})">
                                    <div class="card-img">
                                        @if($i < 3)<span class="card-rank">#{{ $i+1 }}</span>@endif
                                        @if($game->cover)
                                            <img src="{{ asset('storage/' . $game->cover) }}" alt="{{ $game->game_name }}" loading="lazy" onerror="this.style.display='none';this.nextElementSibling.classList.remove('hidden');">
                                            <i class="fas fa-gamepad ph-icon hidden"></i>
                                        @else
                                            <i class="fas fa-gamepad ph-icon"></i>
                                        @endif
                                        <div class="card-overlay">
                                            <div class="card-play"><i class="fas fa-play"></i> {{ __('casino.play_now') }}</div>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div class="card-name" title="{{ $game->game_name }}">{{ $game->game_name }}</div>
                                        <div class="card-prov">{{ $game->provider->name ?? '' }}</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <button type="button" class="arrow-btn arrow-r" onclick="scrollRow(this,1)"><i class="fas fa-chevron-right"></i></button>
                    </div>
                </div>
            @endif

            @forelse($gamesByProvider as $provName => $provGames)
                <div class="provider-section">
                    <div class="section-header">
                        <h2 class="section-title">
                            <span class="title-icon"><i class="fas fa-gamepad"></i></span>
                            {{ $provName }}
                        </h2>
                        <a href="{{ route('cassino.index', ['provider' => $provName]) }}" class="see-all">{{ __('casino.see_all') }} &rarr;</a>
                    </div>
                    <div class="carousel-wrap">
                        <button type="button" class="arrow-btn arrow-l" onclick="scrollRow(this,-1)"><i class="fas fa-chevron-left"></i></button>
                        <div class="games-row">
                            @foreach($provGames as $game)
                                <div class="game-card" onclick="launchGame({{ $game->id }})">
                                    <div class="card-img">
                                        @if($game->cover)
                                            <img src="{{ asset('storage/' . $game->cover) }}" alt="{{ $game->game_name }}" loading="lazy" onerror="this.style.display='none';this.nextElementSibling.classList.remove('hidden');">
                                            <i class="fas fa-gamepad ph-icon hidden"></i>
                                        @else
                                            <i class="fas fa-gamepad ph-icon"></i>
                                        @endif
                                        <div class="card-overlay">
                                            <div class="card-play"><i class="fas fa-play"></i> {{ __('casino.play_now') }}</div>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div class="card-name" title="{{ $game->game_name }}">{{ $game->game_name }}</div>
                                        <div class="card-prov">{{ $game->provider->name ?? '' }}</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <button type="button" class="arrow-btn arrow-r" onclick="scrollRow(this,1)"><i class="fas fa-chevron-right"></i></button>
                    </div>
                </div>
            @empty
                <div class="empty-state">
                    <i class="fas fa-search"></i>
                    <h3>{{ __('casino.no_games_found') }}</h3>
                </div>
            @endforelse
        </main>
    </div>

    <footer class="footer-bar">
        <div class="footer-logos">
            <span>18+</span>
            <span>GamCare</span>
            <span>BeGambleAware</span>
        </div>
        <div class="footer-text">{{ __('casino.responsible') }}</div>
        <div class="footer-copy" style="margin-top:6px;">&copy; {{ date('Y') }} {{ config('app.name') }} {{ __('casino.all_rights') }}</div>
    </footer>

    <div class="toast-msg" id="toast"></div>

    <script>
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('open');
            document.getElementById('sbOverlay').classList.toggle('show');
        }

        function scrollRow(btn, dir) {
            var strip = btn.parentElement.querySelector('.games-row');
            var scrollAmt = strip.clientWidth * 0.75;
            strip.scrollBy({ left: dir * scrollAmt, behavior: 'smooth' });
        }

        function launchGame(gameId) {
            @guest
                window.location.href = '/login';
                return;
            @endguest

            fetch('/api/casino/games/' + gameId + '/launch', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.gameUrl) {
                    window.open(data.gameUrl, '_blank');
                } else {
                    showToast(data.error || '{{ __("casino.game_launch_error") }}');
                }
            })
            .catch(function() { showToast('{{ __("casino.game_launch_error") }}'); });
        }

        function showToast(msg) {
            var t = document.getElementById('toast');
            t.textContent = msg;
            t.style.display = 'block';
            setTimeout(function() { t.style.display = 'none'; }, 3500);
        }
    </script>
</body>
</html>