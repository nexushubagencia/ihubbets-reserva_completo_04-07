<div class="game-card" onclick="launchGame({{ $game->id }})">
    <div class="game-thumb">
        @if($game->cover)
            <img src="{{ asset('storage/' . $game->cover) }}" alt="{{ $game->game_name }}" loading="lazy" onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
            <i class="fas fa-gamepad placeholder-icon" style="display:none;"></i>
        @else
            <i class="fas fa-gamepad placeholder-icon"></i>
        @endif
        <div class="game-overlay">
            <div class="play-btn">
                <i class="fas fa-play"></i> {{ __('casino.play_now') }}
            </div>
        </div>
    </div>
    <div class="game-info">
        <div class="game-name" title="{{ $game->game_name }}">{{ $game->game_name }}</div>
        @if(($showProvider ?? false) && $game->provider)
            <div class="game-provider-name">{{ $game->provider->name }}</div>
        @endif
    </div>
</div>
