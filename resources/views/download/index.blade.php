<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Baixe o App - IHUB BETS</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/all.min.css">
    <style>
        :root {
            --primary: #00ff88;
            --dark: #0f172a;
            --darkery: #020617;
            --grey: #94a3b8;
        }
        body {
            margin: 0;
            padding: 0;
            font-family: 'Outfit', sans-serif;
            background-color: var(--darkery);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            text-align: center;
        }
        .container {
            max-width: 500px;
            padding: 40px 20px;
        }
        .logo {
            font-size: 2.5rem;
            font-weight: 800;
            color: var(--primary);
            margin-bottom: 20px;
            letter-spacing: -1px;
        }
        .app-mockup {
            width: 80%;
            max-width: 250px;
            margin: 20px auto;
            border-radius: 30px;
            box-shadow: 0 0 50px rgba(0, 255, 136, 0.2);
            border: 4px solid #1e293b;
        }
        h1 {
            font-size: 1.8rem;
            margin-bottom: 10px;
        }
        p {
            color: var(--grey);
            margin-bottom: 40px;
            line-height: 1.6;
        }
        .btn-download {
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--primary);
            color: var(--darkery);
            text-decoration: none;
            padding: 18px;
            border-radius: 12px;
            font-weight: 700;
            font-size: 1.1rem;
            margin-bottom: 15px;
            transition: all 0.3s ease;
        }
        .btn-download:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0, 255, 136, 0.3);
        }
        .btn-ios {
            background: transparent;
            border: 2px solid #1e293b;
            color: white;
        }
        .features {
            display: flex;
            justify-content: space-around;
            margin-top: 50px;
            font-size: 0.9rem;
            color: var(--grey);
        }
        .feature-item i {
            display: block;
            font-size: 1.5rem;
            color: var(--primary);
            margin-bottom: 8px;
        }
    </style>
</head>
<body>
    <div class="container text-center">
        <div class="logo">IHUB BETS</div>
        <h1>Tudo na palma da mão</h1>
        <p>Acesse as melhores odds, cash out instantâneo e acompanhe seus jogos favoritos de onde estiver.</p>
        
        <img src="https://via.placeholder.com/250x500/0f172a/00ff88?text=IHUB+APP" alt="App Mockup" class="app-mockup">

        <a href="/app/ihub-bets.apk" class="btn-download" download>
            <i class="fab fa-android mr-2" style="font-size: 1.5rem; margin-right: 12px;"></i> BAIXAR PARA ANDROID
        </a>
        
        <a href="#" class="btn-download btn-ios">
            <i class="fab fa-apple mr-2" style="font-size: 1.5rem; margin-right: 12px;"></i> INSTALAR NO IPHONE
        </a>

        <div class="features">
            <div class="feature-item">
                <i class="fas fa-bolt"></i>
                <span>Super Rápido</span>
            </div>
            <div class="feature-item">
                <i class="fas fa-shield-alt"></i>
                <span>100% Seguro</span>
            </div>
            <div class="feature-item">
                <i class="fas fa-chart-line"></i>
                <span>Melhores Odds</span>
            </div>
        </div>
    </div>
</body>
</html>
