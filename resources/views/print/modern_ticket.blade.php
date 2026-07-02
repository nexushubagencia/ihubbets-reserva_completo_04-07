<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bilhete #{{ $bet->pin }} - {{ $site->name ?? 'IHUB BETS' }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <style>
        :root {
            --primary: #00f2fe;
            --secondary: #4facfe;
            --bg-page: #0a0e17;
            --bg-card: #151b26;
            --bg-item: #1c2533;
            --text-main: #ffffff;
            --text-muted: #94a3b8;
            --success: #10b981;
            --danger: #ef4444;
            --warning: #f59e0b;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            background-color: var(--bg-page);
            font-family: 'Outfit', sans-serif;
            color: var(--text-main);
            padding: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            min-height: 100vh;
        }

        .ticket-wrapper {
            width: 100%;
            max-width: 450px;
            position: relative;
        }

        /* Glassmorphism Card */
        .ticket-card {
            background: var(--bg-card);
            border-radius: 24px;
            border: 1px solid rgba(255, 255, 255, 0.05);
            overflow: hidden;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            position: relative;
        }

        .ticket-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary), var(--secondary));
        }

        /* Header */
        .header {
            padding: 30px 20px;
            text-align: center;
            background: linear-gradient(to bottom, rgba(255,255,255,0.02), transparent);
        }

        .logo {
            font-size: 28px;
            font-weight: 800;
            letter-spacing: -1px;
            background: linear-gradient(135deg, #fff 0%, #a5b4fc 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 5px;
        }

        .site-url {
            font-size: 12px;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        /* Main Info Grid */
        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            padding: 20px;
            background: rgba(0,0,0,0.2);
        }

        .info-item {
            display: flex;
            flex-direction: column;
        }

        .info-item label {
            font-size: 11px;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 4px;
        }

        .info-item span {
            font-size: 15px;
            font-weight: 600;
        }

        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 99px;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .status-aberto { background: rgba(245, 158, 11, 0.1); color: var(--warning); border: 1px solid rgba(245, 158, 11, 0.2); }
        .status-ganhou { background: rgba(16, 185, 129, 0.1); color: var(--success); border: 1px solid rgba(16, 185, 129, 0.2); }
        .status-perdeu { background: rgba(239, 68, 68, 0.1); color: var(--danger); border: 1px solid rgba(239, 68, 68, 0.2); }

        /* Match Items */
        .matches-container {
            padding: 10px 20px;
        }

        .match-card {
            background: var(--bg-item);
            border-radius: 16px;
            margin-bottom: 12px;
            padding: 16px;
            border: 1px solid rgba(255, 255, 255, 0.03);
            transition: transform 0.2s;
        }

        .match-card:hover {
            transform: translateY(-2px);
            background: #242e3d;
        }

        .match-league {
            font-size: 11px;
            color: var(--primary);
            font-weight: 600;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .match-teams {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
        }

        .team-name {
            font-weight: 600;
            font-size: 14px;
            flex: 1;
        }

        .vs {
            font-size: 10px;
            color: var(--text-muted);
            padding: 0 10px;
        }

        .market-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 10px;
            border-top: 1px solid rgba(255, 255, 255, 0.05);
        }

        .market-name {
            font-size: 12px;
            color: var(--text-muted);
        }

        .selection {
            background: rgba(0, 242, 254, 0.1);
            color: var(--primary);
            padding: 4px 10px;
            border-radius: 6px;
            font-weight: 700;
            font-size: 13px;
        }

        /* Financial Footer */
        .financial-footer {
            padding: 20px;
            background: linear-gradient(to top, rgba(0,0,0,0.3), transparent);
        }

        .row-summary {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            font-size: 14px;
        }

        .row-total {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: baseline;
        }

        .total-label { font-weight: 800; font-size: 16px; color: var(--secondary); }
        .total-value { font-weight: 800; font-size: 24px; color: var(--text-main); }

        /* QR Code & Actions */
        .qr-section {
            text-align: center;
            padding: 30px;
            background: rgba(0,0,0,0.1);
        }

        .qr-container {
            background: #fff;
            display: inline-block;
            padding: 12px;
            border-radius: 16px;
            margin-bottom: 15px;
        }

        .qr-container img { width: 120px; height: 120px; display: block; }

        .scan-msg { font-size: 11px; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px; }

        .btn-group {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-top: 25px;
        }

        .btn {
            padding: 14px;
            border-radius: 14px;
            border: none;
            font-weight: 700;
            font-size: 14px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.2s;
            text-decoration: none;
        }

        .btn-whatsapp { background: #25d366; color: #fff; }
        .btn-download { background: rgba(255,255,255,0.1); color: #fff; }
        .btn-download:hover { background: rgba(255,255,255,0.2); }

        @media print {
            .btn-group, .scan-msg { display: none !important; }
            body { padding: 0; background: #fff; color: #000; }
            .ticket-card { box-shadow: none; border: 1px solid #eee; }
        }
    </style>
</head>
<body>

    <div class="ticket-wrapper" id="capture">
        <div class="ticket-card">
            <!-- Header -->
            <div class="header">
                @if(!empty($site->logo_path))
                    <img src="{{ asset('storage/' . $site->logo_path) }}" alt="Logo" style="max-height: 70px; width: auto; object-fit: contain; margin-bottom: 10px; filter: drop-shadow(0 0 10px rgba(0,242,254,0.3));">
                @else
                    <div class="logo">{{ $site->name ?? 'IHUB BETS' }}</div>
                @endif
                <div class="site-url">{{ $site->domain ?? 'www.ihubbets.com' }}</div>
                
                <div style="margin-top: 15px; display: inline-block; padding: 5px 15px; background: rgba(0,242,254,0.1); border-radius: 99px; border: 1px solid rgba(0,242,254,0.2);">
                    <span style="font-size: 10px; color: var(--primary); font-weight: 800; text-transform: uppercase; letter-spacing: 1px;">Bilhete Premiado</span>
                </div>
            </div>

            <!-- General Info -->
            <div class="info-grid">
                <div class="info-item">
                    <label>Código do Bilhete</label>
                    <span style="color: var(--primary); font-size: 20px; font-weight: 800; letter-spacing: 2px; text-shadow: 0 0 10px rgba(0,242,254,0.5);">{{ $bet->pin }}</span>
                </div>
                <div class="info-item" style="text-align: right;">
                    <label>Status</label>
                    <div>
                        <span class="status-badge status-{{ strtolower($bet->status) }}">
                            {{ $bet->status }}
                        </span>
                    </div>
                </div>
                <div class="info-item">
                    <label>Data/Hora</label>
                    <span>{{ $bet->created_at->format('d/m/Y H:i') }}</span>
                </div>
                <div class="info-item" style="text-align: right;">
                    <label>Cliente</label>
                    <span>{{ $bet->client_name ?? 'Cliente' }}</span>
                </div>
                <div class="info-item">
                    <label>Vendedor</label>
                    <span>{{ $bet->user->name ?? 'Site' }}</span>
                </div>
                <div class="info-item" style="text-align: right;">
                    <label>Tipo</label>
                    <span>{{ count($bet->items) > 1 ? 'Múltipla' : 'Simples' }}</span>
                </div>
            </div>

            <!-- Matches -->
            <div class="matches-container">
                <div style="font-size: 11px; color: var(--text-muted); margin-bottom: 12px; text-transform: uppercase; font-weight: 700;">
                    Seleções ({{ count($bet->items) }})
                </div>

                @foreach($bet->items as $item)
                <div class="match-card">
                    <div class="match-league">
                        <i class="fas fa-trophy"></i> {{ $item->league_name }}
                    </div>
                    <div class="match-teams">
                        <span class="team-name">{{ $item->home_team }}</span>
                        <span class="vs">VS</span>
                        <span class="team-name" style="text-align: right;">{{ $item->away_team }}</span>
                    </div>
                    <div class="market-info">
                        <div class="market-name">{{ $item->market_name }}</div>
                        <div class="selection">{{ $item->selection_label }} @ {{ number_format($item->selection_odd, 2) }}</div>
                    </div>

                    @if($item->live)
                    <div class="live-stats" style="margin-top: 15px; padding-top: 10px; border-top: 1px dashed rgba(255,255,255,0.05); display: flex; flex-direction: column; gap: 8px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; font-size: 13px;">
                            <span style="color: var(--primary); font-weight: 700; font-size: 11px;">AO VIVO</span>
                            <span style="font-weight: 800; letter-spacing: 1px;">{{ $item->live->score ?? '0-0' }}</span>
                        </div>
                        
                        <div style="display: flex; justify-content: space-between; align-items: center; font-size: 11px; color: var(--text-muted);">
                            <span><i class="fas fa-flag"></i> Escanteios</span>
                            <span style="color: var(--text-main);">{{ $item->live->numberOfCornersHome ?? 0 }} - {{ $item->live->numberOfCornersAway ?? 0 }}</span>
                        </div>

                        <div style="display: flex; justify-content: space-between; align-items: center; font-size: 11px; color: var(--text-muted);">
                            <span><i class="fas fa-copy" style="color: #fbbf24;"></i> Cartões Amarelos</span>
                            <span style="color: var(--text-main);">{{ $item->live->numberOfYellowCardsHome ?? 0 }} - {{ $item->live->numberOfYellowCardsAway ?? 0 }}</span>
                        </div>

                        @if(($item->live->numberOfRedCardsHome ?? 0) > 0 || ($item->live->numberOfRedCardsAway ?? 0) > 0)
                        <div style="display: flex; justify-content: space-between; align-items: center; font-size: 11px; color: var(--text-muted);">
                            <span><i class="fas fa-copy" style="color: #ef4444;"></i> Cartões Vermelhos</span>
                            <span style="color: var(--text-main);">{{ $item->live->numberOfRedCardsHome ?? 0 }} - {{ $item->live->numberOfRedCardsAway ?? 0 }}</span>
                        </div>
                        @endif
                    </div>
                    @endif

                    @if($item->match_date)
                    <div style="font-size: 9px; color: var(--text-muted); margin-top: 8px; text-align: center;">
                        Partida: {{ \Carbon\Carbon::parse($item->match_date)->format('d/m H:i') }}
                    </div>
                    @endif
                </div>
                @endforeach
            </div>

            <!-- Financial Summary -->
            <div class="financial-footer">
                <div class="row-summary">
                    <span style="color: var(--text-muted);">Valor Apostado</span>
                    <span>R$ {{ number_format($bet->amount, 2, ',', '.') }}</span>
                </div>
                <div class="row-summary">
                    <span style="color: var(--text-muted);">Cotação Total</span>
                    <span>{{ number_format($bet->potential_payout / $bet->amount, 2) }}</span>
                </div>
                <div class="row-total">
                    <span class="total-label">RETORNO POSSÍVEL</span>
                    <span class="total-value">R$ {{ number_format($bet->potential_payout, 2, ',', '.') }}</span>
                </div>
            </div>

            <!-- QR Code -->
            <div class="qr-section">
                <div class="qr-container">
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data={{ url('/view-ticket/'.$bet->pin) }}" alt="QR Code">
                </div>
                <div class="scan-msg">Aponte a câmera para conferir o bilhete</div>
                
                @if(isset($configuracao) && !empty($configuracao->texto_rodape))
                    <div style="margin-top: 15px; font-size: 11px; color: var(--text-muted); padding: 0 15px; text-align: center; border-top: 1px dashed rgba(255,255,255,0.1); padding-top: 15px;">
                        {{ $configuracao->texto_rodape }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Actions -->
    <div class="ticket-wrapper no-print">
        <div class="btn-group">
            <button class="btn btn-download" onclick="window.print()" style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1);">
                <i class="fas fa-print"></i> Imprimir
            </button>
            <button class="btn btn-download" onclick="downloadPDF()" style="background: rgba(239, 68, 68, 0.2); color: #f87171;">
                <i class="fas fa-file-pdf"></i> Baixar PDF
            </button>
            <button class="btn btn-download" onclick="downloadTicket()" style="background: rgba(14, 165, 233, 0.2); color: #38bdf8;">
                <i class="fas fa-camera"></i> Baixar Imagem
            </button>
            <a href="?layout=classic" class="btn btn-download" style="background: rgba(251, 191, 36, 0.2); color: #fbbf24;">
                <i class="fas fa-print"></i> Versão Padrão
            </a>
            <a href="https://api.whatsapp.com/send?text=Confira meu bilhete na {{ $site->name ?? 'IHUB BETS' }}: {{ url('/view-ticket/'.$bet->pin) }}" 
               target="_blank" class="btn btn-whatsapp" style="grid-column: span 2;">
                <i class="fab fa-whatsapp"></i> Compartilhar no WhatsApp
            </a>
        </div>
        
        <div style="text-align: center; margin-top: 20px;">
            <a href="/" style="color: var(--text-muted); font-size: 13px; text-decoration: none;">
                <i class="fas fa-arrow-left"></i> Voltar para o Site
            </a>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <script>
        // Live Tracking do Bilhete (Auto-Refresh inteligente)
        setInterval(function() {
            fetch(window.location.href)
                .then(res => res.text())
                .then(html => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    const newCard = doc.querySelector('.ticket-card');
                    if (newCard) {
                        document.querySelector('.ticket-card').innerHTML = newCard.innerHTML;
                    }
                });
        }, 15000); // Atualiza a cada 15 segundos

        function downloadPDF() {
            const element = document.getElementById('capture');
            const opt = {
                margin:       0,
                filename:     'bilhete-{{ $bet->pin }}.pdf',
                image:        { type: 'jpeg', quality: 0.98 },
                html2canvas:  { scale: 2, useCORS: true, backgroundColor: "#0a0e17" },
                jsPDF:        { unit: 'in', format: 'a4', orientation: 'portrait' }
            };
            html2pdf().set(opt).from(element).save();
        }

        function downloadTicket() {
            const btn = document.querySelector('.btn-download');
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Gerando...';
            
            html2canvas(document.querySelector("#capture"), {
                backgroundColor: "#0a0e17",
                scale: 2,
                logging: false,
                useCORS: true
            }).then(canvas => {
                const link = document.createElement('a');
                link.download = 'bilhete-{{ $bet->pin }}.png';
                link.href = canvas.toDataURL("image/png");
                link.click();
                btn.innerHTML = '<i class="fas fa-camera"></i> Salvar Foto';
            });
        }
    </script>
</body>
</html>
