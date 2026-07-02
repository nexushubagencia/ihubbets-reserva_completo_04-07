<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="theme-color" content="#ffffff">
    <link rel="manifest" href="/manifest-cambista.json">
    <title>{{ config('app.name', 'IHUB BETS') }} - App Cambista</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://code.iconify.design/2/2.1.0/iconify.min.js"></script>
    <script src="https://code.iconify.design/iconify-icon/1.0.7/iconify-icon.min.js"></script>
    <link rel="stylesheet" href="{{ mix('css/app.css') }}">
    <style>
        body, html {
            margin: 0;
            padding: 0;
            width: 100%;
            height: 100%;
            background-color: #f4f6f9;
            font-family: 'Inter', sans-serif;
            overflow-x: hidden;
            -webkit-tap-highlight-color: transparent;
        }
        #app-cambista {
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
        }
    </style>
</head>
<body>
    <div id="app-cambista">
        <router-view></router-view>
    </div>
    <script>
        window.Laravel = {!! json_encode([
            'csrfToken' => csrf_token(),
            'baseUrl' => url('/'),
            'textoRodape' => '© ' . date('Y') . ' ' . config('app.name', 'IHUB BETS') . ' Todos os direitos reservados.',
        ]) !!};
    </script>
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw-cambista.js')
                    .then(reg => console.log('Service Worker registrado!', reg))
                    .catch(err => console.error('Erro ao registrar Service Worker:', err));
            });
        }
    </script>
    <script src="{{ mix('js/app-cambista.js') }}"></script>
</body>
</html>
