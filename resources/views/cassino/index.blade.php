<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Cassino | {{ $site->name ?? config('app.name', 'IHUB BETS') }}</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary: #2563eb;
            --primary-dark: #1d4ed8;
            --dark: #0f172a;
            --dark-light: #1e293b;
            --text: #f8fafc;
            --muted: #94a3b8;
            --card-bg: #1e293b;
            --hover: #334155;
        }

        * { font-family: 'Inter', sans-serif; }

        body {
            background: var(--dark);
            color: var(--text);
            min-height: 100vh;
        }

        .casino-header {
            background: linear-gradient(135deg, #1e3a5f 0%, #2563eb 100%);
            padding: 25px 0;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .casino-logo {
            font-size: 1.6rem;
            font-weight: 800;
            color: #fff;
            text-decoration: none;
        }

        .casino-logo:hover { color: #fff; }

        .btn-back-sports {
            background: rgba(255,255,255,0.15);
            border: 1px solid rgba(255,255,255,0.2);
            color: #fff;
            border-radius: 30px;
            padding: 8px 18px;
            font-weight: 600;
            transition: all 0.2s;
        }

        .btn-back-sports:hover {
            background: rgba(255,255,255,0.25);
            color: #fff;
        }

        .section-title {
            font-size: 1.4rem;
            font-weight: 800;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .provider-pill {
            background: var(--card-bg);
            border: 1px solid var(--hover);
            color: var(--text);
            border-radius: 30px;
            padding: 8px 18px;
            font-weight: 600;
            font-size: 0.85rem;
            cursor: pointer;
            transition: all 0.2s;
            white-space: nowrap;
        }

        .provider-pill:hover,
        .provider-pill.active {
            background: var(--primary);
            border-color: var(--primary);
            color: #fff;
        }

        .game-card {
            background: var(--card-bg);
            border-radius: 16px;
            overflow: hidden;
            transition: transform 0.2s, box-shadow 0.2s;
            cursor: pointer;
            border: 1px solid rgba(255,255,255,0.05);
            height: 100%;
        }

        .game-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(37, 99, 235, 0.25);
        }

        .game-image {
            width: 100%;
            aspect-ratio: 4/3;
            object-fit: cover;
            background: var(--dark-light);
        }

        .game-info {
            padding: 15px;
        }

        .game-title {
            font-size: 0.95rem;
            font-weight: 700;
            margin-bottom: 5px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .game-provider {
            font-size: 0.8rem;
            color: var(--muted);
        }

        .play-overlay {
            position: absolute;
            inset: 0;
            background: rgba(15, 23, 42, 0.7);
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.2s;
        }

        .game-card:hover .play-overlay {
            opacity: 1;
        }

        .btn-play {
            background: var(--primary);
            color: #fff;
            border-radius: 30px;
            padding: 10px 24px;
            font-weight: 700;
            border: none;
        }

        .btn-play:hover {
            background: var(--primary-dark);
            color: #fff;
        }

        .search-box {
            background: var(--card-bg);
            border: 1px solid var(--hover);
            color: var(--text);
            border-radius: 30px;
            padding: 10px 20px;
        }

        .search-box:focus {
            background: var(--card-bg);
            color: var(--text);
            border-color: var(--primary);
            box-shadow: none;
        }

        .badge-popular {
            position: absolute;
            top: 10px;
            left: 10px;
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: #fff;
            font-size: 0.7rem;
            font-weight: 700;
            padding: 4px 10px;
            border-radius: 20px;
            z-index: 2;
        }

        .game-wrapper {
            position: relative;
        }

        .pagination .page-link {
            background: var(--card-bg);
            border-color: var(--hover);
            color: var(--text);
        }

        .pagination .page-link:hover,
        .pagination .page-item.active .page-link {
            background: var(--primary);
            border-color: var(--primary);
            color: #fff;
        }

        .launch-modal .modal-content {
            background: var(--dark);
            border: 1px solid var(--hover);
        }

        .launch-modal .modal-header {
            border-bottom: 1px solid var(--hover);
        }

        .launch-modal .btn-close {
            filter: invert(1);
        }

        .launch-iframe {
            width: 100%;
            height: 70vh;
            border: none;
            border-radius: 8px;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: var(--muted);
        }

        .empty-state i {
            font-size: 3rem;
            margin-bottom: 20px;
            opacity: 0.5;
        }
    </style>
</head>
<body>

    <header class="casino-header">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <a href="/" class="casino-logo">
                    <i class="fas fa-dice me-2"></i>{{ $site->name ?? 'IHUB BETS' }}
                </a>
                <div class="d-flex align-items-center gap-3">
                    @auth
                        <div class="text-end d-none d-md-block">
                            <div class="small text-white-50">Saldo</div>
                            <div class="fw-bold">R$ {{ number_format(auth()->user()->balance ?? 0, 2, ',', '.') }}</div>
                        </div>
                    @endauth
                    <a href="/" class="btn btn-back-sports">
                        <i class="fas fa-arrow-left me-2"></i>Voltar aos Esportes
                    </a>
                </div>
            </div>
        </div>
    </header>

    <main class="container py-4">
        {{-- Provedores --}}
        <section class="mb-5">
            <h2 class="section-title"><i class="fas fa-gamepad text-primary"></i> Provedores</h2>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('cassino.index') }}" class="provider-pill {{ !request('provider') ? 'active' : '' }}">
                    Todos
                </a>
                @foreach($providers as $provider)
                    <a href="{{ route('cassino.index', ['provider' => $provider->name]) }}"
                       class="provider-pill {{ request('provider') == $provider->name ? 'active' : '' }}">
                        {{ $provider->name }}
                    </a>
                @endforeach
            </div>
        </section>

        {{-- Busca --}}
        <section class="mb-4">
            <form method="GET" action="{{ route('cassino.index') }}" class="d-flex gap-2">
                @if(request('provider'))
                    <input type="hidden" name="provider" value="{{ request('provider') }}">
                @endif
                <input type="text" name="search" class="form-control search-box"
                       placeholder="Buscar jogos..." value="{{ request('search') }}">
                <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold">
                    <i class="fas fa-search"></i>
                </button>
            </form>
        </section>

        {{-- Jogos Populares --}}
        @if($popularGames->count() > 0 && !request('provider') && !request('search'))
            <section class="mb-5">
                <h2 class="section-title"><i class="fas fa-fire text-danger"></i> Populares</h2>
                <div class="row g-3">
                    @foreach($popularGames as $game)
                        <div class="col-6 col-md-4 col-lg-2">
                            @include('cassino.partials.game-card', ['game' => $game])
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- Todos os Jogos --}}
        <section>
            <h2 class="section-title">
                <i class="fas fa-th-large text-primary"></i>
                {{ request('provider') ? request('provider') : 'Todos os Jogos' }}
            </h2>

            @if($games->count() > 0)
                <div class="row g-3">
                    @foreach($games as $game)
                        <div class="col-6 col-md-4 col-lg-2">
                            @include('cassino.partials.game-card', ['game' => $game])
                        </div>
                    @endforeach
                </div>

                <div class="d-flex justify-content-center mt-4">
                    {{ $games->links() }}
                </div>
            @else
                <div class="empty-state">
                    <i class="fas fa-search"></i>
                    <h4>Nenhum jogo encontrado</h4>
                    <p>Tente buscar por outro nome ou provedor.</p>
                </div>
            @endif
        </section>
    </main>

    {{-- Modal Launch --}}
    <div class="modal fade launch-modal" id="launchModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold"><i class="fas fa-gamepad me-2"></i><span id="launchGameTitle">Jogo</span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-2">
                    <iframe id="launchIframe" class="launch-iframe" src=""></iframe>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script>
        const launchModal = new bootstrap.Modal(document.getElementById('launchModal'));

        function launchGame(gameCode, gameName) {
            document.getElementById('launchGameTitle').textContent = gameName;
            document.getElementById('launchIframe').src = '';

            fetch('/api/playfiver/launch', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ game_code: gameCode })
            })
            .then(r => r.json())
            .then(data => {
                if (data.url) {
                    document.getElementById('launchIframe').src = data.url;
                    launchModal.show();
                } else {
                    alert(data.error || 'Erro ao abrir o jogo. Tente novamente.');
                }
            })
            .catch(err => {
                console.error(err);
                alert('Erro ao conectar com o cassino.');
            });
        }

        document.getElementById('launchModal').addEventListener('hidden.bs.modal', () => {
            document.getElementById('launchIframe').src = '';
        });
    </script>
</body>
</html>
