<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=600">
<style>
  * { margin: 0; padding: 0; box-sizing: border-box; }
  body { 
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
    background: transparent; 
    width: 600px;
  }
  .banner {
    width: 600px;
    background: linear-gradient(135deg, {{ $theme ?? '#1aa6d0' }}, {{ $theme_dark ?? '#0d7a9e' }});
    border-radius: 16px;
    overflow: hidden;
    color: #fff;
    position: relative;
  }
  .banner-header {
    background: rgba(0,0,0,0.25);
    padding: 12px 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
  }
  .banner-header .league {
    font-size: 14px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1px;
    opacity: 0.9;
  }
  .banner-header .sport-badge {
    background: rgba(255,255,255,0.2);
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
  }
  .banner-body {
    padding: 30px 20px;
    display: flex;
    align-items: center;
    justify-content: space-around;
  }
  .team {
    text-align: center;
    width: 160px;
  }
  .team img {
    width: 80px;
    height: 80px;
    object-fit: contain;
    margin-bottom: 10px;
    filter: drop-shadow(0 4px 8px rgba(0,0,0,0.3));
  }
  .team .name {
    font-size: 15px;
    font-weight: 700;
    text-transform: uppercase;
    line-height: 1.2;
  }
  .vs {
    font-size: 28px;
    font-weight: 900;
    color: rgba(255,255,255,0.5);
    text-shadow: 0 2px 10px rgba(0,0,0,0.3);
  }
  .banner-odds {
    background: rgba(0,0,0,0.3);
    padding: 16px 20px;
    display: flex;
    justify-content: center;
    gap: 20px;
  }
  .odd-box {
    background: rgba(255,255,255,0.15);
    border: 1px solid rgba(255,255,255,0.25);
    border-radius: 10px;
    padding: 10px 24px;
    text-align: center;
    min-width: 100px;
  }
  .odd-box .label {
    font-size: 11px;
    text-transform: uppercase;
    opacity: 0.8;
    margin-bottom: 4px;
  }
  .odd-box .value {
    font-size: 22px;
    font-weight: 800;
  }
  .banner-footer {
    background: rgba(0,0,0,0.4);
    padding: 10px 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-size: 12px;
  }
  .banner-footer .date {
    opacity: 0.7;
  }
  .banner-footer .brand {
    font-weight: 700;
    font-size: 14px;
    letter-spacing: 1px;
  }
</style>
</head>
<body>
<div class="banner" id="banner-capture">
  <div class="banner-header">
    <span class="league">{{ $league ?? 'Liga' }}</span>
    <span class="sport-badge">{{ $sport ?? 'Futebol' }}</span>
  </div>
  <div class="banner-body">
    <div class="team">
      <img src="{{ $flag_home ?? asset('img/placeholders/shield.png') }}" alt="Home" onerror="this.src='data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%2280%22 height=%2280%22><rect fill=%22%23ffffff22%22 width=%2280%22 height=%2280%22 rx=%2210%22/></svg>'">
      <div class="name">{{ $home ?? 'Time A' }}</div>
    </div>
    <div class="vs">VS</div>
    <div class="team">
      <img src="{{ $flag_away ?? asset('img/placeholders/shield.png') }}" alt="Away" onerror="this.src='data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%2280%22 height=%2280%22><rect fill=%22%23ffffff22%22 width=%2280%22 height=%2280%22 rx=%2210%22/></svg>'">
      <div class="name">{{ $away ?? 'Time B' }}</div>
    </div>
  </div>
  @if(!empty($odds))
  <div class="banner-odds">
    @php $oddsArr = explode('|', $odds); @endphp
    @if(count($oddsArr) >= 3)
    <div class="odd-box">
      <div class="label">Casa</div>
      <div class="value">{{ trim($oddsArr[0]) }}</div>
    </div>
    <div class="odd-box">
      <div class="label">Empate</div>
      <div class="value">{{ trim($oddsArr[1]) }}</div>
    </div>
    <div class="odd-box">
      <div class="label">Fora</div>
      <div class="value">{{ trim($oddsArr[2]) }}</div>
    </div>
    @endif
  </div>
  @endif
  <div class="banner-footer">
    <span class="date">{{ $match_date ?? now()->format('d/m/Y H:i') }}</span>
    <span class="brand">{{ $site_name ?? 'IHUB BETS' }}</span>
  </div>
</div>
</body>
</html>
