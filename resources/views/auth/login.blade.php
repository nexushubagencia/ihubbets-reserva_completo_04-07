<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>IHUB BETS | Login Administrativo</title>
    
    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="/bower_components/font-awesome/css/font-awesome.min.css">
    <!-- Bootstrap 3.3.7 -->
    <link rel="stylesheet" href="/bower_components/bootstrap/dist/css/bootstrap.min.css">

    <style>
        body, html {
            height: 100%;
            margin: 0;
            font-family: 'Source Sans Pro', sans-serif;
            overflow: hidden;
        }

        .bg-stadium {
            background-image: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.8)), url('/img/login_bg.png');

            height: 100%;
            background-position: center;
            background-repeat: no-repeat;
            background-size: cover;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-box {
            width: 400px;
            padding: 40px;
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.5);
            text-align: center;
            color: #fff;
            animation: fadeIn 1s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .login-logo img {
            max-width: 180px;
            margin-bottom: 25px;
            filter: drop-shadow(0 0 10px rgba(255,255,255,0.3));
        }

        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }

        .form-control {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: #fff;
            height: 50px;
            border-radius: 10px;
            padding-left: 15px;
            transition: all 0.3s;
        }

        .form-control:focus {
            background: rgba(255, 255, 255, 0.2);
            border-color: #3498db;
            box-shadow: none;
            color: #fff;
        }

        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.5);
        }

        .btn-login {
            width: 100%;
            height: 50px;
            background: linear-gradient(135deg, #3498db, #2980b9);
            border: none;
            border-radius: 10px;
            color: #fff;
            font-size: 18px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            cursor: pointer;
            transition: transform 0.3s, box-shadow 0.3s;
            margin-top: 10px;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(52, 152, 219, 0.4);
            color: #fff;
        }

        .footer-text {
            margin-top: 30px;
            font-size: 13px;
            color: rgba(255, 255, 255, 0.5);
        }

        .error-message {
            background: rgba(231, 76, 60, 0.2);
            border: 1px solid #e74c3c;
            color: #ff9f9f;
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }
    </style>
</head>
<body>

<div class="bg-stadium">
    <div class="login-box">
        <div class="login-logo">
            @php 
                $siteId = app('tenant.site_id');
                $site = \App\Models\Site::find($siteId); 
                $logo = $site->logo_path ?? 'dist/img/logo.png';
                $nomeBanca = $site->name ?? 'IHUB BETS';
            @endphp
            <img src="/{{ $logo }}" alt="{{ $nomeBanca }}" onerror="this.src='/dist/img/logo_default.png'">
            <h2 style="font-weight: 300; margin-top: 0;">Painel <b style="font-weight: 700;">Admin</b></h2>
        </div>


        <form method="POST" action="{{ route('login') }}">
            @csrf

            @if($errors->any())
                <div class="error-message">
                    <i class="fa fa-exclamation-triangle"></i> Usuário ou senha incorretos.
                </div>
            @endif

            <div class="form-group">
                <input type="text" name="username" class="form-control" placeholder="Usuário" required autofocus>
            </div>

            <div class="form-group">
                <input type="password" name="password" class="form-control" placeholder="Senha" required>
            </div>

            <button type="submit" class="btn btn-login">
                Entrar no Sistema
            </button>
        </form>

        <div class="footer-text">
            &copy; {{ date('Y') }} {{ $nomeBanca ?? 'IHUB BETS' }} - Todos os direitos reservados.
        </div>
    </div>
</div>

</body>
</html>
