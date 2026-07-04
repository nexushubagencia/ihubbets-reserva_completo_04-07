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

    <!-- PROFESSIONAL: Intercepta setInterval para evitar refresh agressivo -->
    <script>
    (function() {
        'use strict';
        var _origSetInterval = window.setInterval;
        var _origClearInterval = window.clearInterval;
        var _stats = { homeChanged: 0, liveChanged: 0, logoChanged: 0, total: 0 };

        window.setInterval = function(fn, delay) {
            var fnStr = fn.toString();
            var newDelay = delay;
            _stats.total++;

            // Home: 30s → 300s (5 minutos) - evita piscar
            if (fnStr.indexOf('loadMatchHoje') !== -1 && delay === 30000) {
                newDelay = 300000;
                _stats.homeChanged++;
            }
            // Ao vivo: 15s → 30s (ainda rápido)
            if (fnStr.indexOf('loadVivo') !== -1 && delay === 15000) {
                newDelay = 30000;
                _stats.liveChanged++;
            }
            // Logo fix: 200ms → 500ms (menos agressivo)
            if (fnStr.indexOf('_forceLogoVisible') !== -1 && delay === 200) {
                newDelay = 500;
                _stats.logoChanged++;
            }

            return _origSetInterval.call(window, fn, newDelay);
        };

        window.clearInterval = _origClearInterval;

        // Exibe diagnóstico no console quando o DOM estiver pronto
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                console.log('╔═══════════════════════════════════╗');
                console.log('║  IHUB - REFRESH PROFISSIONAL      ║');
                console.log('╠═══════════════════════════════════╣');
                console.log('║ Home:  30s → 300s (5min)          ║');
                console.log('║ Live:  15s →  30s                 ║');
                console.log('║ Logo: 200ms → 500ms               ║');
                console.log('╠═══════════════════════════════════╣');
                console.log('║ Total intervals: ' + _stats.total + '                ║');
                console.log('║ Modificados:     ' + (_stats.homeChanged + _stats.liveChanged + _stats.logoChanged) + '                ║');
                console.log('╚═══════════════════════════════════╝');
            }, 2000);
        });
    })();
    </script>

    <script src="/js/front_nexus_hibrido.js?v={{ time() }}"></script>

    <!-- FIX: Bloqueia display:flex e width forçados no logo que causam deslocamento ao abrir modal -->
    <script>
    (function(){
        var logo = document.querySelector('.main-header .logo');
        var logoLg = document.querySelector('.main-header .logo .logo-lg');
        var logoMini = document.querySelector('.main-header .logo .logo-mini');
        if (!logo) return;

        function fixLogo() {
            var propsToRemove = ['display', 'width', 'min-width', 'align-items'];
            propsToRemove.forEach(function(p) {
                if (logo.style.getPropertyPriority(p) === 'important') {
                    logo.style.removeProperty(p);
                }
            });
        }

        var observer = new MutationObserver(fixLogo);
        observer.observe(logo, { attributes: true, attributeFilter: ['style'] });
        if (logoLg) observer.observe(logoLg, { attributes: true, attributeFilter: ['style'] });
        if (logoMini) observer.observe(logoMini, { attributes: true, attributeFilter: ['style'] });
        fixLogo();
    })();
    </script>

    <!-- ATUALIZAÇÃO PROFISSIONAL: Polling inteligente sem piscar -->
    @if(config('app.debug'))
    <script>console.log('[IHUB] Polling profissional ativo. Home:5min | Ao vivo:30s | Logo:500ms');</script>
    @endif

    <!-- CALCULA LARGURA DA SCROLLBAR PARA EVITAR DESLOCAMENTO DO HEADER -->
    <script>
    (function(){
        function calculateScrollbar() {
            var d = document.createElement('div');
            d.style.cssText = 'width:100px;height:100px;overflow:scroll;position:absolute;top:-9999px';
            document.body.appendChild(d);
            var sw = d.offsetWidth - d.clientWidth;
            document.body.removeChild(d);
            document.documentElement.style.setProperty('--scrollbar-w', sw + 'px');
        }
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', calculateScrollbar);
        } else {
            calculateScrollbar();
        }
    })();
    </script>

    <!-- THEME OVERRIDE - V2.3.0: Cores 100% editaveis por tema -->
    <style id="theme-text-override">

        /* ============================
           1. NOMES DOS TIMES
           ============================ */
        .team-name {
            color: var(--team_name_text--color, #333333) !important;
        }

        /* ============================
           2. COTACOES (C, E, F)
           Fundo e texto dos botoes de odd
           ============================ */
        .btn-home,
        .btn-home-nexus {
            background: var(--odd_button_bg--color, #ffffff) !important;
            color: var(--odd_button_text--color, #333333) !important;
            border: 1px solid rgba(0,0,0,0.1) !important;
            transition: none !important;
        }
        .btn-home strong,
        .btn-home-nexus strong {
            color: var(--odd_button_text--color, #333333) !important;
        }
        /* Hover: NAO muda nada - so quando selecionada */
        .btn-home:hover,
        .btn-home-nexus:hover {
            background: var(--odd_button_bg--color, #ffffff) !important;
            color: var(--odd_button_text--color, #333333) !important;
        }

        /* ============================
           3. COTACAO SELECIONADA
           Cor quando o usuario clica
           ============================ */
        .btn-home.selecionado,
        .btn-home-nexus.selecionado,
        .selecionado {
            background: var(--odd_btn_hover_bg--color, #35aa71) !important;
            color: var(--odd_btn_hover_text--color, #ffffff) !important;
            border: 1px solid var(--btn_selecionado-border-color, #35aa71) !important;
        }
        .btn-home.selecionado strong,
        .btn-home-nexus.selecionado strong {
            color: var(--odd_btn_hover_text--color, #ffffff) !important;
        }

        /* ============================
           4. BOTAO +0 (MAIS MERCADOS)
           ============================ */
        .plus-odd,
        .plus-odd-nexus,
        .btn-plus-odd {
            background: var(--odds_plus_button--color, #1aa6d0) !important;
            color: #ffffff !important;
            border: none !important;
        }
        .plus-odd:hover,
        .plus-odd-nexus:hover,
        .btn-plus-odd:hover {
            background: var(--odds_plus_button--color, #1aa6d0) !important;
            color: #ffffff !important;
        }

        /* ============================
           5. BOTAO COMPARTILHAR
           Fundo e icone separados
           ============================ */
        .btn-share,
        .btn-share-nexus {
            background: var(--share_button_bg--color, #1aa6d0) !important;
            color: var(--share_button_icon--color, #ffffff) !important;
            border: none !important;
        }
        .btn-share i,
        .btn-share-nexus i,
        .fa-picture-o {
            color: var(--share_button_icon--color, #ffffff) !important;
        }

        /* === CUPOM VALOR BOTOES === */
        .btn-valor-rapido,
        .valores-rapidos .btn,
        .btn-valor,
        .value-btn-group .btn-valor,
        .ticket-values .btn {
            color: var(--cupom_valor_btn_text--color, #ffffff) !important;
        }

        /* === ACTIVE SPORT TAB === */
        .modality-item-demo a.ativo {
            color: var(--modalidade_ativa_text--color, var(--menu_active_text--color, #fff)) !important;
        }
        .day-tab.active a,
        .day-tab.active span,
        .day-tab.active strong {
            color: var(--menu_active_text--color, #fff) !important;
        }
        .menu-jogos li.ativo a {
            color: var(--menu_active_text--color, #fff) !important;
        }

        /* === SIDEBAR === */
        .sidebar-menu .header {
            color: var(--sidebar_header_text--color, #FFF) !important;
        }
        .menu-jogos li a,
        .nav-esportes li a,
        .sports-menu-item {
            color: var(--sidebar_text--color, #fff) !important;
        }

        /* === CAROUSEL / DESTAQUE === */
        .carousel-header {
            color: var(--destaque_header_text--color, #fff) !important;
        }
        .control-btn {
            color: var(--destaque_header_text--color, #fff) !important;
        }
        .carousel-card .offer-badge {
            color: var(--destaque_btn_text--color, #fff) !important;
        }
        .header-campeonato-cupon {
            color: var(--card_header_text--color, #fff) !important;
        }

        /* === ICONS ON COLORED BG === */
        .fa-trophy,
        .fa-star {
            color: var(--menu_active_text--color, #fff) !important;
        }

        /* === SEARCH ICON === */
        .input-group-btn .btn,
        .search-btn,
        .sidebar-form .btn,
        .input-group-search .btn,
        .input-group-search .input-group-addon,
        .input-group-btn .btn-flat {
            color: var(--search_icon_text--color, #fff) !important;
        }

        /* === BOTAO CADASTRE-SE === */
        .btn-cadastrar-demo,
        .btn-cadastro,
        .btn-register,
        a.btn-cadastrar-demo,
        a[data-target="#modal-register"] {
            background-color: var(--btn_cadastrar--color) !important;
            color: var(--btn_cadastrar_text--color, #fff) !important;
            border: none !important;
            transition: all 0.3s ease !important;
            filter: none !important;
        }
        .btn-cadastrar-demo:hover,
        .btn-cadastro:hover,
        .btn-register:hover,
        a.btn-cadastrar-demo:hover,
        a[data-target="#modal-register"]:hover {
            background-color: var(--btn_cadastrar_hover--color, var(--btn_cadastrar--color)) !important;
            color: var(--btn_cadastrar_text--color, #fff) !important;
            filter: none !important;
            box-shadow: 0 2px 8px rgba(0,0,0,0.15) !important;
            transform: translateY(-1px) !important;
        }

        /* === BOTAO ENTRAR === */
        .btn-acessar-demo,
        a.btn-acessar-demo,
        a[data-target="#modal-login"] {
            background-color: var(--btn_entrar--color) !important;
            color: var(--btn_entrar_text--color, #fff) !important;
            border: none !important;
            transition: all 0.3s ease !important;
            filter: none !important;
        }
        .btn-acessar-demo:hover,
        .btn-acessar-demo:focus,
        .btn-acessar-demo:active,
        a.btn-acessar-demo:hover,
        a[data-target="#modal-login"]:hover {
            background-color: var(--btn_entrar_hover--color, var(--btn_entrar--color)) !important;
            color: var(--btn_entrar_text--color, #fff) !important;
            filter: none !important;
            box-shadow: 0 2px 8px rgba(0,0,0,0.15) !important;
            transform: translateY(-1px) !important;
        }
    </style>
</body>

</html>
