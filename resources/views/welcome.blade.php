<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>{{ $site_info['complete_name'] ?? $site_info['company_name'] ?? config('app.name', 'IHUB BETS') }}</title>
    <!-- Tell the browser to be responsive to screen width -->
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Favicon dinâmico --}}
    @if(!empty($site_info['favicon_url']))
        <link rel="icon" type="image/png" href="{{ $site_info['favicon_url'] }}?v={{ time() }}">
        <link rel="shortcut icon" href="{{ $site_info['favicon_url'] }}?v={{ time() }}">
    @else
        <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    @endif

    <!-- ====== CSS DO LINDO V1.2 ====== -->
    <!-- Bootstrap 3.3.7 -->
    <link rel="stylesheet" href="/bower_components/bootstrap/dist/css/bootstrap.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="/bower_components/font-awesome/css/font-awesome.min.css">
    <!-- Ionicons -->
    <link rel="stylesheet" href="/bower_components/Ionicons/css/ionicons.min.css">
    <!-- Theme style (AdminLTE) -->
    <link rel="stylesheet" href="/dist/css/AdminLTE.min.css">
    <!-- AdminLTE Skins -->
    <link rel="stylesheet" href="/dist/css/skins/_all-skins.min.css">
    <!-- Morris chart -->
    <link rel="stylesheet" href="/bower_components/morris.js/morris.css">
    <!-- jvectormap -->
    <link rel="stylesheet" href="/bower_components/jvectormap/jquery-jvectormap.css">
    <!-- Date Picker -->
    <link rel="stylesheet" href="/bower_components/bootstrap-datepicker/dist/css/bootstrap-datepicker.min.css">
    <!-- Daterange picker -->
    <link rel="stylesheet" href="/bower_components/bootstrap-daterangepicker/daterangepicker.css">
    <!-- bootstrap wysihtml5 - text editor -->
    <link rel="stylesheet" href="/plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.min.css">
    <!-- Google Font -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic">
    <!-- Customização IHUB (overrides de tema) -->
    <link rel="stylesheet" href="/dist/css/custom.css?v={{ time() }}">
    <!-- CSS Dinâmico Gerado pelo Master Panel (ÚNICA FONTE DE VERDADE para cores) -->
    <link rel="stylesheet" href="{{ route('generate-css') }}?v={{ time() }}">
    
    <script>
        window.site_info = @json($site_info);
        window.configuracoes = window.site_info.configuracoes;
        window.banners = @json($banners ?? []);
        window.is_admin = {{ auth()->check() && auth()->user()->nivel === 'admin' ? 'true' : 'false' }};
    </script>
</head>

<body class="sidebar-mini wysihtml5-supported fixed skin-default skin-blue"
      data-default-background-img="/img/bg.jpg"
      data-overlay="true"
      data-overlay-opacity="0.35">

    <div id="app" data-site-language="pt-br" data-show-language-selector="0">
        <geral-component
            :igame_terms_acepted='false'
            :app_env='"production"'
            :site_info='@json($site_info)'
            :configuracoes='@json($site_info["configuracoes"] ?? [])'
            :account='@json($account ?? [])'>
        </geral-component>
    </div>

    <!-- ====== SCRIPTS DO LINDO V1.2 ====== -->
    <!-- jQuery 3 -->
    <script src="/bower_components/jquery/dist/jquery.min.js"></script>
    <!-- jQuery UI 1.11.4 -->
    <script src="/bower_components/jquery-ui/jquery-ui.min.js"></script>
    <!-- Resolve conflict in jQuery UI tooltip with Bootstrap tooltip -->
    <!-- Socket.io -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/socket.io/4.2.0/socket.io.js"></script>
    <script>
        if ($ && $.widget) $.widget.bridge('uibutton', $.ui.button);
    </script>
    <!-- Bootstrap 3.3.7 -->
    <script src="/bower_components/bootstrap/dist/js/bootstrap.min.js"></script>
    <!-- Morris.js charts -->
    <script src="/bower_components/raphael/raphael.min.js"></script>
    <script src="/bower_components/morris.js/morris.min.js"></script>
    <!-- Sparkline -->
    <script src="/bower_components/jquery-sparkline/dist/jquery.sparkline.min.js"></script>
    <!-- jvectormap -->
    <script src="/plugins/jvectormap/jquery-jvectormap-1.2.2.min.js"></script>
    <script src="/plugins/jvectormap/jquery-jvectormap-world-mill-en.js"></script>
    <!-- jQuery Knob Chart -->
    <script src="/bower_components/jquery-knob/dist/jquery.knob.min.js"></script>
    <!-- daterangepicker -->
    <script src="/bower_components/moment/min/moment.min.js"></script>
    <script src="/bower_components/bootstrap-daterangepicker/daterangepicker.js"></script>
    <!-- datepicker -->
    <script src="/bower_components/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js"></script>
    <!-- Bootstrap WYSIHTML5 -->
    <script src="/plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.all.min.js"></script>
    <!-- Slimscroll -->
    <script src="/bower_components/jquery-slimscroll/jquery.slimscroll.min.js"></script>
    <!-- FastClick -->
    <script src="/bower_components/fastclick/lib/fastclick.js"></script>
    <!-- AdminLTE App -->
    <script src="/dist/js/adminlte.min.js"></script>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script src="/js/banner-engine.js?v={{ time() }}"></script>
    <script src="/js/front_nexus_hibrido.js?v={{ time() }}"></script>
</body>

</html>
