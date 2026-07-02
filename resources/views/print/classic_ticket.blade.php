<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bilhete - {{ $bet->pin }}</title>
    <style>
        @page { margin: 0; }
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 12px;
            color: #000;
            margin: 0;
            padding: 5mm;
            width: 58mm; /* Padrão Mobile */
            background: #fff;
        }
        @media print {
            body { width: 100%; padding: 0; }
            .no-print { display: none; }
        }
        .header { text-align: center; border-bottom: 1px dashed #000; padding-bottom: 5px; margin-bottom: 10px; }
        .logo { font-weight: bold; font-size: 16px; text-transform: uppercase; }
        .info-header { margin-bottom: 10px; }
        .info-header p { margin: 2px 0; }
        
        .match-item { border-bottom: 1px dashed #ccc; padding: 5px 0; margin-bottom: 5px; }
        .match-item:last-child { border-bottom: none; }
        .match-info { font-weight: bold; }
        .market-info { display: flex; justify-content: space-between; margin-top: 3px; }
        
        .footer { border-top: 1px dashed #000; padding-top: 10px; margin-top: 10px; }
        .footer-row { display: flex; justify-content: space-between; font-weight: bold; margin-bottom: 3px; }
        .qr-code { text-align: center; margin-top: 15px; }
        .qr-code img { width: 40mm; height: 40mm; }
        .footer-msg { text-align: center; font-size: 10px; margin-top: 10px; }

        /* Ajuste para 80mm */
        @media screen and (min-width: 80mm) {
            body { width: 80mm; }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo">{{ $site->name ?? 'IHUB BETS' }}</div>
        <div>{{ $site->url ?? 'www.ihubbets.com' }}</div>
    </div>

    <div class="info-header">
        <p><b>PIN:</b> {{ $bet->pin }}</p>
        <p><b>DATA:</b> {{ \Carbon\Carbon::parse($bet->created_at)->format('d/m/Y H:i') }}</p>
        <p><b>CAMBISTA:</b> {{ $bet->user->name ?? 'Online' }}</p>
        <p><b>CLIENTE:</b> {{ $bet->client_name ?? 'Anônimo' }}</p>
    </div>

    <div class="matches">
        @foreach($bet->items as $item)
        <div class="match-item">
            <div class="match-info">{{ $item->home_team }} x {{ $item->away_team }}</div>
            <div class="league-info" style="font-size: 10px;">{{ $item->league_name }} - {{ \Carbon\Carbon::parse($item->match_date)->format('d/m H:i') }}</div>
            <div class="market-info">
                <span>{{ $item->market_name }}: <b>{{ $item->selection_label }}</b></span>
                <span>Odd: <b>{{ number_format($item->selection_odd, 2, ',', '.') }}</b></span>
            </div>
        </div>
        @endforeach
    </div>

    <div class="footer">
        <div class="footer-row">
            <span>QTDE JOGOS:</span>
            <span>{{ count($bet->items) }}</span>
        </div>
        <div class="footer-row">
            <span>VALOR APOSTADO:</span>
            <span>R$ {{ number_format($bet->amount, 2, ',', '.') }}</span>
        </div>
        <div class="footer-row">
            <span>COTAÇÃO TOTAL:</span>
            <span>{{ number_format($bet->potential_payout / ($bet->amount > 0 ? $bet->amount : 1), 2, ',', '.') }}</span>
        </div>
        <div class="footer-row" style="font-size: 14px; border-top: 1px solid #000; padding-top: 5px;">
            <span>RETORNO POSSÍVEL:</span>
            <span>R$ {{ number_format($bet->potential_payout, 2, ',', '.') }}</span>
        </div>
        
        <div class="qr-code">
            <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data={{ url('/check/' . $bet->pin) }}" alt="QR Code">
            <p style="font-size: 9px;">Aponte a câmera para conferir</p>
        </div>

        <div class="footer-msg">
            {{ $configuracao->texto_rodape ?? 'Obrigado e boa sorte! Consulte as regras no site.' }}
        </div>
    </div>

    <div class="no-print" style="margin-top: 20px; text-align: center;">
        <button onclick="window.print()" style="padding: 10px 20px; background: #000; color: #fff; border: none; border-radius: 5px; cursor: pointer;">
            IMPRIMIR BILHETE
        </button>
    </div>
</body>
</html>
