<div class="game-card" onclick="@auth launchGame('{{ $game->game_code }}', '{{ addslashes($game->name) }}') @else window.location.href = '/login' @endauth">
    <div class="game-wrapper">
        @if($game->is_popular)
            <span class="badge-popular"><i class="fas fa-fire me-1"></i>TOP</span>
        @endif

        <img src="{{ $game->image_url ?? '/img/casino-placeholder.svg' }}"
             alt="{{ $game->name }}"
             class="game-image"
             loading="lazy"
             onerror="this.src='/img/casino-placeholder.svg'">

        <div class="play-overlay">
            <button class="btn btn-play" onclick="event.stopPropagation(); @auth launchGame('{{ $game->game_code }}', '{{ addslashes($game->name) }}') @else window.location.href = '/login' @endauth">
                <i class="fas fa-play me-2"></i>Jogar
            </button>
        </div>
    </div>
    <div class="game-info">
        <div class="game-title" title="{{ $game->name }}">{{ $game->name }}</div>
        <div class="game-provider"><i class="fas fa-cube me-1"></i>{{ $game->provider }}</div>
    </div>
</div>
