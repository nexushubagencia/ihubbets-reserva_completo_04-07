<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ACOMPANHAMENTO AO VIVO #{{ $bet->pin }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/all.min.css">
    <style>
        :root {
            --primary: #00f2fe;
            --secondary: #4facfe;
            --bg: #0f172a;
            --card-bg: #1e293b;
            --text: #f8fafc;
            --text-muted: #94a3b8;
            --success: #10b981;
            --danger: #ef4444;
            --warning: #f59e0b;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            background-color: var(--bg);
            font-family: 'Outfit', sans-serif;
            color: var(--text);
            padding: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            min-height: 100vh;
        }

        .container {
            width: 100%;
            max-width: 500px;
        }

        /* Top Header */
        .header {
            text-align: center;
            padding: 30px 0;
            background: linear-gradient(135deg, rgba(0,242,254,0.1), rgba(79,172,254,0.1));
            border-radius: 20px;
            margin-bottom: 25px;
            border: 1px solid rgba(255,255,255,0.05);
        }

        .logo-text {
            font-size: 28px;
            font-weight: 800;
            background: linear-gradient(to right, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 5px;
        }

        .ticket-id {
            font-size: 14px;
            color: var(--text-muted);
            letter-spacing: 2px;
            text-transform: uppercase;
        }

        /* Status Summary */
        .summary-card {
            background: var(--card-bg);
            padding: 20px;
            border-radius: 20px;
            margin-bottom: 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-left: 4px solid var(--primary);
        }

        .summary-item label {
            display: block;
            font-size: 11px;
            color: var(--text-muted);
            text-transform: uppercase;
            margin-bottom: 4px;
        }

        .summary-item span {
            font-size: 18px;
            font-weight: 700;
        }

        .status-badge {
            padding: 6px 15px;
            border-radius: 99px;
            font-size: 12px;
            font-weight: 800;
            text-transform: uppercase;
        }

        .badge-open { background: rgba(0,242,254,0.1); color: var(--primary); border: 1px solid var(--primary); }
        .badge-won { background: rgba(16,185,129,0.1); color: var(--success); border: 1px solid var(--success); }
        .badge-lost { background: rgba(239,68,68,0.1); color: var(--danger); border: 1px solid var(--danger); }

        /* Match Cards */
        .match-card {
            background: var(--card-bg);
            border-radius: 24px;
            padding: 20px;
            margin-bottom: 20px;
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(255,255,255,0.03);
            transition: transform 0.3s;
        }

        .match-card:hover {
            transform: translateY(-5px);
            background: #243045;
        }

        .match-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            font-size: 11px;
            font-weight: 600;
            color: var(--primary);
        }

        .live-indicator {
            display: flex;
            align-items: center;
            gap: 6px;
            color: var(--danger);
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.5; }
            100% { opacity: 1; }
        }

        .teams-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .team {
            flex: 1;
            text-align: center;
        }

        .team-name {
            font-weight: 700;
            font-size: 15px;
            margin-top: 8px;
        }

        .score-box {
            background: rgba(0,0,0,0.3);
            padding: 10px 20px;
            border-radius: 15px;
            font-size: 24px;
            font-weight: 800;
            color: #fff;
            min-width: 100px;
            text-align: center;
        }

        /* Stats Bar */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid rgba(255,255,255,0.05);
        }

        .stat-item {
            text-align: center;
        }

        .stat-icon {
            font-size: 14px;
            margin-bottom: 4px;
            color: var(--text-muted);
        }

        .stat-value {
            font-size: 13px;
            font-weight: 700;
        }

        /* Selection Info */
        .selection-box {
            background: rgba(255,255,255,0.03);
            padding: 15px;
            border-radius: 15px;
            margin-top: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .selection-label {
            font-size: 12px;
            color: var(--text-muted);
        }

        .selection-value {
            font-weight: 800;
            color: var(--primary);
        }

        .selection-odd {
            background: var(--primary);
            color: var(--bg);
            padding: 2px 8px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 800;
            margin-left: 10px;
        }

        /* Floating Buttons */
        .actions {
            position: fixed;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            width: calc(100% - 40px);
            max-width: 500px;
            display: flex;
            gap: 10px;
            z-index: 100;
        }

        .btn {
            flex: 1;
            padding: 15px;
            border-radius: 15px;
            border: none;
            font-weight: 800;
            font-size: 14px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            text-decoration: none;
            color: #fff;
            box-shadow: 0 10px 20px rgba(0,0,0,0.3);
        }

        .btn-wa { background: #25d366; }
        .btn-back { background: rgba(255,255,255,0.1); backdrop-filter: blur(10px); }

        @media (max-width: 480px) {
            .score-box { min-width: 80px; font-size: 20px; }
            .team-name { font-size: 13px; }
        }
    </style>
</head>
<body>

    <div class="container">
        <div class="header">
            <div class="logo-text">{{ $site->name ?? 'IHUB BETS' }}</div>
            <div class="ticket-id">Bilhete #{{ $bet->pin }}</div>
        </div>

        @php
            $status = strtolower($bet->status);
            $bLabel = 'EM ABERTO';
            $bClass = 'badge-open';
            if($status == 'won' || $status == 'ganhou') { $bLabel = 'GANHOU'; $bClass = 'badge-won'; }
            if($status == 'lost' || $status == 'perdeu') { $bLabel = 'PERDEU'; $bClass = 'badge-lost'; }
        @endphp

        <div class="summary-card">
            <div class="summary-item">
                <label>Retorno Possível</label>
                <span>R$ {{ number_format($bet->potential_payout, 2, ',', '.') }}</span>
            </div>
            <div class="status-badge {{ $bClass }}">
                {{ $bLabel }}
            </div>
        </div>

        @foreach($bet->items as $item)
        <div class="match-card">
            <div class="match-header">
                <span><i class="fas fa-trophy"></i> {{ $item->league_name }}</span>
                @if($item->live)
                <span class="live-indicator"><i class="fas fa-circle"></i> AO VIVO</span>
                @else
                <span style="color: var(--text-muted);">FINALIZADO</span>
                @endif
            </div>

            <div class="teams-row">
                <div class="team">
                    <div class="team-name">{{ $item->home_team }}</div>
                </div>
                <div class="score-box">
                    {{ $item->live->score ?? '0 - 0' }}
                </div>
                <div class="team">
                    <div class="team-name">{{ $item->away_team }}</div>
                </div>
            </div>

            @if($item->live)
            <div class="stats-grid">
                <div class="stat-item">
                    <div class="stat-icon"><i class="fas fa-flag"></i></div>
                    <div class="stat-value">{{ $item->live->numberOfCornersHome ?? 0 }} - {{ $item->live->numberOfCornersAway ?? 0 }}</div>
                </div>
                <div class="stat-item">
                    <div class="stat-icon"><i class="fas fa-copy" style="color: var(--warning);"></i></div>
                    <div class="stat-value">{{ $item->live->numberOfYellowCardsHome ?? 0 }} - {{ $item->live->numberOfYellowCardsAway ?? 0 }}</div>
                </div>
                <div class="stat-item">
                    <div class="stat-icon"><i class="fas fa-copy" style="color: var(--danger);"></i></div>
                    <div class="stat-value">{{ $item->live->numberOfRedCardsHome ?? 0 }} - {{ $item->live->numberOfRedCardsAway ?? 0 }}</div>
                </div>
            </div>
            @endif

            <div class="selection-box">
                <div>
                    <div class="selection-label">{{ $item->market_name }}</div>
                    <div class="selection-value">
                        {{ $item->selection_label }}
                        <span class="selection-odd">{{ number_format($item->selection_odd, 2) }}</span>
                    </div>
                </div>
                
                @php
                    $iStat = strtolower($item->status);
                    $iIcon = 'fa-clock';
                    $iColor = 'var(--warning)';
                    if($iStat == 'won' || $iStat == 'ganhou') { $iIcon = 'fa-check-circle'; $iColor = 'var(--success)'; }
                    if($iStat == 'lost' || $iStat == 'perdeu') { $iIcon = 'fa-times-circle'; $iColor = 'var(--danger)'; }
                @endphp
                <i class="fas {{ $iIcon }}" style="color: {{ $iColor }}; font-size: 20px;"></i>
            </div>
        </div>
        @endforeach

        <div style="height: 100px;"></div> <!-- Spacer for fixed buttons -->
    </div>

    <div class="actions">
        <a href="https://api.whatsapp.com/send?text=Acompanhe meu bilhete ao vivo: {{ url('/view-ticket/'.$bet->pin.'?layout=live') }}" target="_blank" class="btn btn-wa">
            <i class="fab fa-whatsapp"></i> COMPARTILHAR
        </a>
        <a href="/" class="btn btn-back">
            <i class="fas fa-home"></i> INÍCIO
        </a>
    </div>

    <script>
        // Auto-refresh logic
        setInterval(function() {
            location.reload();
        }, 30000); // 30 seconds
    </script>
</body>
</html>
