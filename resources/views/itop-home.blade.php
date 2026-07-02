<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>IHUB V4 PRO - APOSTAS ESPORTIVAS</title>
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <meta property="og:title" content="IHUB V4 PRO">
    <meta property="og:description" content="A melhor plataforma de apostas esportivas.">
    <meta property="og:type" content="website">

    <!-- Cores e Estilos Locais (DESCONTAMINADOS) -->
    <link rel="stylesheet" href="/dist/css/verde-claro.css?v={{ time() }}">
    <link rel="stylesheet" href="/bower_components/bootstrap/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="/bower_components/font-awesome/css/font-awesome.min.css">
    <link rel="stylesheet" href="/bower_components/Ionicons/css/ionicons.min.css">
    <link rel="stylesheet" href="/dist/css/AdminLTE.min.css">
    <link rel="stylesheet" href="/dist/css/custom.css?v={{ time() }}">
    <link rel="stylesheet" href="/dist/css/skins/_all-skins.min.css">

    <!-- Google Font -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic">
</head>

<body class="sidebar-mini fixed skin-default skin-blue">

    <div id="app">
        <geral-component 
            :site_info='@json($site_info)'
            :banners='@json($banners)'
            :account='[]'
            :app_env='"production"'>
        </geral-component>
    </div>

    <!-- Scripts Locais (DESCONTAMINADOS) -->
    <script src="/bower_components/jquery/dist/jquery.min.js"></script>
    <script src="/bower_components/jquery-ui/jquery-ui.min.js"></script>
    <script src="/bower_components/bootstrap/dist/js/bootstrap.min.js"></script>
    <script src="/dist/js/adminlte.min.js"></script>
    
    <!-- Script Principal -->
    <script src="/js/app.js?v={{ time() }}"></script>
</body>

</html>
l>