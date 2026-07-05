<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Configurações do Nexus Clone V.1
    | Clone fiel do sistema antigo
    |--------------------------------------------------------------------------
    */

    'title_adm_geral' => 'IHUB BETS - Área Administrativa - Sistema de Apostas Esportivas',
    'title' => 'IHUB BETS - Sistema de Apostas Esportivas',
    'title_prefix' => '',
    'title_postfix' => ' | IHUB BETS',
    'version_system' => '2.1.0',
    'name_site' => 'IHUB',
    'url_system' => 'IHUB BETS',
    'year_system' => '2026',

    /*
    |--------------------------------------------------------------------------
    | Logo
    |--------------------------------------------------------------------------
    */
    'logo' => '<b>IHUB </b>BETS',
    'logo_mini' => '<b>I</b>B',
    'logo_img' => '',
    'logo_img_class' => 'brand-image elevation-3',
    'logo_img_xl' => null,
    'logo_img_xl_class' => 'brand-image-xs',
    'logo_img_alt' => 'IHUB Logo',

    /*
    |--------------------------------------------------------------------------
    | Skin Color - blue-light como o original
    |--------------------------------------------------------------------------
    */
    'skin' => 'blue-light',

    /*
    |--------------------------------------------------------------------------
    | User Menu
    |--------------------------------------------------------------------------
    */
    'usermenu_enabled' => true,
    'usermenu_header' => true,
    'usermenu_header_class' => 'bg-primary',
    'usermenu_image' => false,
    'usermenu_profile_url' => false,

    /*
    |--------------------------------------------------------------------------
    | Layout
    |--------------------------------------------------------------------------
    */
    'layout_topnav' => null,
    'layout_boxed' => null,
    'layout_fixed_sidebar' => null,
    'layout_fixed_navbar' => null,
    'layout_fixed_footer' => null,
    'layout_dark_mode' => null,

    /*
    |--------------------------------------------------------------------------
    | Classes
    |--------------------------------------------------------------------------
    */
    'classes_auth_card' => 'card-outline card-primary',
    'classes_auth_header' => '',
    'classes_auth_body' => '',
    'classes_auth_footer' => '',
    'classes_auth_icon' => '',
    'classes_auth_btn' => 'btn-flat btn-primary',

    'classes_body' => '',
    'classes_brand' => '',
    'classes_brand_text' => '',
    'classes_content_wrapper' => '',
    'classes_content_header' => '',
    'classes_content' => '',
    'classes_sidebar' => 'sidebar-dark-primary',
    'classes_sidebar_nav' => '',
    'classes_topnav' => 'navbar-white navbar-light',
    'classes_topnav_nav' => 'navbar-expand',
    'classes_topnav_container' => 'container',

    /*
    |--------------------------------------------------------------------------
    | Sidebar
    |--------------------------------------------------------------------------
    */
    'sidebar_mini' => 'lg',
    'sidebar_collapse' => false,
    'sidebar_collapse_auto_size' => false,
    'sidebar_collapse_remember' => false,
    'sidebar_collapse_remember_no_transition' => true,
    'sidebar_scrollbar_theme' => 'os-theme-light',
    'sidebar_scrollbar_auto_hide' => 'l',
    'sidebar_nav_accordion' => true,
    'sidebar_nav_animation_speed' => 300,

    /*
    |--------------------------------------------------------------------------
    | URLs
    |--------------------------------------------------------------------------
    */
    'use_route_url' => false,
    'dashboard_url' => 'admin',
    'logout_url' => false,
    'login_url' => 'login',
    'register_url' => 'register',
    'password_reset_url' => 'password/reset',
    'password_email_url' => 'password/email',
    'profile_url' => false,

    /*
    |--------------------------------------------------------------------------
    | Laravel Mix
    |--------------------------------------------------------------------------
    */
    'enabled_laravel_mix' => false,
    'laravel_mix_css_path' => 'css/app.css',
    'laravel_mix_js_path' => 'js/app.js',

    /*
    |--------------------------------------------------------------------------
    | Menu Items
    |--------------------------------------------------------------------------
    */
    'menu' => [
        [
            'type' => 'navbar-search',
            'text' => 'search',
            'topnav_right' => true,
        ],
        [
            'type' => 'fullscreen-widget',
            'topnav_right' => true,
        ],
        [
            'text' => '',
            'topnav_right' => true,
            'url'  => '#',
            'icon' => 'fas fa-moon',
            'id'   => 'global-theme-toggle',
            'class' => 'nav-link btn-theme-toggle',
        ],

        // ============================================================
        // 1. MASTER (super_admin)
        // ============================================================
        ['header' => 'MASTER', 'can' => 'isSuperAdmin'],
        [
            'text' => 'Dashboard Master',
            'url'  => 'admin/master/dashboard',
            'icon' => 'fas fa-fw fa-chess-king',
            'can'  => 'isSuperAdmin',
        ],
        [
            'text' => 'Gerenciar Bancas',
            'url'  => 'admin/master/bancas',
            'icon' => 'fas fa-fw fa-network-wired',
            'can'  => 'isSuperAdmin',
        ],
        [
            'text' => 'Gerenciar Temas',
            'url'  => 'admin/master/temas',
            'icon' => 'fas fa-fw fa-palette',
            'can'  => 'isSuperAdmin',
        ],
        [
            'text' => 'Financeiro Global',
            'icon' => 'fas fa-fw fa-money-bill-wave',
            'can'  => 'isSuperAdmin',
            'submenu' => [
                [
                    'text' => 'Caixa Master',
                    'url'  => 'admin/master/financeiro',
                    'icon' => 'fas fa-fw fa-university',
                ],
                [
                    'text' => 'Stats Financeiros',
                    'url'  => 'admin/master/financeiro-stats',
                    'icon' => 'fas fa-fw fa-chart-pie',
                ],
                [
                    'text' => 'Extrato Global',
                    'url'  => 'admin/master/extrato-global',
                    'icon' => 'fas fa-fw fa-file-invoice',
                ],
                [
                    'text' => 'Ranking Global',
                    'url'  => 'admin/master/ranking',
                    'icon' => 'fas fa-fw fa-trophy',
                ],
            ]
        ],
        [
            'text' => 'Multi-Tenant',
            'icon' => 'fas fa-fw fa-server',
            'can'  => 'isSuperAdmin',
            'submenu' => [
                [
                    'text' => 'Gerenciar Sites',
                    'url'  => 'admin/gerenciador/sites',
                    'icon' => 'fas fa-fw fa-globe',
                ],
                [
                    'text' => 'Correção Global Odds',
                    'url'  => 'admin/gerenciador/odds',
                    'icon' => 'fas fa-fw fa-percentage',
                ],
            ]
        ],

        // ============================================================
        // 2. PRINCIPAL
        // ============================================================
        ['header' => 'PRINCIPAL'],
        [
            'text' => 'Dashboard',
            'url'  => 'admin',
            'icon' => 'fas fa-fw fa-tachometer-alt',
        ],

        // ============================================================
        // 3. USUÁRIOS
        // ============================================================
        ['header' => 'USUÁRIOS'],
        [
            'text' => 'Clientes',
            'url'  => 'admin/clientes',
            'icon' => 'fas fa-fw fa-user',
            'can'  => 'adm',
        ],
        [
            'text' => 'Gerentes',
            'url'  => 'admin/gerentes',
            'icon' => 'fas fa-fw fa-user-tie',
            'can'  => 'adm',
        ],
        [
            'text' => 'Cambistas',
            'url'  => 'admin/cambistas',
            'icon' => 'fas fa-fw fa-users',
        ],

        // ============================================================
        // 4. FINANCEIRO (Depósitos, Saques, Lançamentos, Caixas, Relatórios)
        // ============================================================
        ['header' => 'FINANCEIRO'],
        [
            'text' => 'Caixa do Dia',
            'url'  => 'admin/caixa-do-dia',
            'icon' => 'fas fa-fw fa-calendar-day',
            'can'  => 'adm',
        ],
        [
            'text' => 'Depósitos',
            'icon' => 'fas fa-fw fa-money-bill-wave',
            'can'  => 'adm',
            'module' => 'gateway_deposito',
            'submenu' => [
                [
                    'text' => 'Configuração Gateway',
                    'url'  => 'admin/financeiro-gateways',
                    'icon' => 'fas fa-fw fa-qrcode',
                ],
                [
                    'text' => 'Histórico de Depósitos',
                    'url'  => 'admin/depositos',
                    'icon' => 'fas fa-fw fa-list-alt',
                ],
                [
                    'text' => 'PIX Usuários',
                    'url'  => 'admin/financeiro-pix-usuarios',
                    'icon' => 'fas fa-fw fa-mobile-alt',
                ],
            ]
        ],
        [
            'text' => 'Saques',
            'url'  => 'admin/saques-admin',
            'icon' => 'fas fa-fw fa-university',
            'can'  => 'adm',
        ],
        [
            'text' => 'Lançamentos',
            'url'  => 'admin/lancamentos',
            'icon' => 'fas fa-fw fa-exchange-alt',
            'can'  => 'adm',
            'module' => 'lancamentos',
        ],
        [
            'text' => 'Ajustes Financeiros',
            'url'  => 'admin/finance/adjustments',
            'icon' => 'fas fa-fw fa-sliders-h',
            'can'  => 'adm',
        ],
        [
            'text' => 'Caixas',
            'icon' => 'fas fa-fw fa-university',
            'can'  => 'adm',
            'submenu' => [
                [
                    'text' => 'Caixa Gerente',
                    'url'  => 'admin/caixa-adm-gerente',
                    'icon' => 'fas fa-fw fa-user-tie',
                ],
                [
                    'text' => 'Caixa Cambista',
                    'url'  => 'admin/caixa-adm-cambista',
                    'icon' => 'fas fa-fw fa-wallet',
                ],
            ]
        ],
        [
            'text' => 'Relatórios',
            'icon' => 'fas fa-fw fa-chart-line',
            'can'  => 'adm',
            'submenu' => [
                [
                    'text' => 'Relatório Cambista',
                    'url'  => 'admin/relatorio-cambista',
                    'icon' => 'fas fa-fw fa-file-invoice-dollar',
                ],
                [
                    'text' => 'Relatório Gerente',
                    'url'  => 'admin/relatorio-gerente',
                    'icon' => 'fas fa-fw fa-file-invoice-dollar',
                ],
                [
                    'text' => 'Extrato de Transações',
                    'url'  => 'admin/relatorio-transacoes',
                    'icon' => 'fas fa-fw fa-list-alt',
                    'module' => 'extrato',
                ],
            ]
        ],
        [
            'text' => 'Estatísticas',
            'icon' => 'fas fa-fw fa-chart-bar',
            'can'  => 'adm',
            'submenu' => [
                [
                    'text' => 'Diárias',
                    'url'  => 'admin/statistics/daily',
                    'icon' => 'fas fa-fw fa-calendar-check',
                ],
                [
                    'text' => 'Por Cambista',
                    'url'  => 'admin/statistics/by-seller',
                    'icon' => 'fas fa-fw fa-user-tag',
                ],
                [
                    'text' => 'Por Gerente',
                    'url'  => 'admin/statistics/by-manager',
                    'icon' => 'fas fa-fw fa-user-tie',
                ],
                [
                    'text' => 'Ao Vivo',
                    'url'  => 'admin/statistics/live',
                    'icon' => 'fas fa-fw fa-broadcast-tower',
                ],
            ]
        ],

        // ============================================================
        // 5. APOSTAS (Core do sistema)
        // ============================================================
        ['header' => 'APOSTAS'],
        [
            'text' => 'Validar PIN',
            'url'  => 'admin/validar-pin',
            'icon' => 'fas fa-fw fa-barcode',
        ],
        [
            'text' => 'Bilhetes',
            'url'  => 'admin/bilhetes',
            'icon' => 'fas fa-fw fa-receipt',
        ],
        [
            'text' => 'Eventos Pendentes',
            'url'  => 'admin/pending-events',
            'icon' => 'fas fa-fw fa-clock',
            'can'  => 'adm',
        ],
        [
            'text' => 'Mapa de Apostas',
            'url'  => 'admin/mapa-apostas',
            'icon' => 'fas fa-fw fa-map',
            'can'  => 'adm',
        ],
        [
            'text' => 'Loto',
            'url'  => 'admin/loto',
            'icon' => 'fas fa-fw fa-star',
            'can'  => 'adm',
            'module' => 'loto',
        ],
        [
            'text' => 'Cash Out',
            'url'  => 'admin/cashout',
            'icon' => 'fas fa-fw fa-hand-holding-usd',
            'can'  => 'adm',
        ],
        [
            'text' => 'Bolão',
            'url'  => 'admin/bolao',
            'icon' => 'fas fa-fw fa-futbol',
            'can'  => 'adm',
        ],
        [
            'text' => 'Risco',
            'icon' => 'fas fa-fw fa-shield-alt',
            'can'  => 'adm',
            'module' => 'riscos',
            'submenu' => [
                [
                    'text' => 'Gerenciar Risco',
                    'url'  => 'admin/gerenciamento-riscos',
                    'icon' => 'fas fa-fw fa-cogs',
                ],
                [
                    'text' => 'Dashboard de Risco',
                    'url'  => 'admin/risk-dashboard',
                    'icon' => 'fas fa-fw fa-exclamation-triangle',
                ],
                [
                    'text' => 'Mapa de Risco',
                    'url'  => 'admin/risk-map',
                    'icon' => 'fas fa-fw fa-map-marked-alt',
                ],
            ]
        ],

        // ============================================================
        // 6. CONFIGURAÇÕES
        // ============================================================
        ['header' => 'CONFIGURAÇÕES', 'module' => 'configuracoes'],
        [
            'text' => 'Configurações de Sistema',
            'url'  => 'admin/configuracoes',
            'icon' => 'fas fa-fw fa-cogs',
            'can'  => 'adm',
            'module' => 'configuracoes',
        ],
        [
            'text' => 'Configurações Gerais',
            'url'  => 'admin/settings/general',
            'icon' => 'fas fa-fw fa-sliders-h',
            'can'  => 'adm',
        ],
        [
            'text' => 'Mercados',
            'icon' => 'fas fa-fw fa-list-ul',
            'can'  => 'adm',
            'module' => 'configuracoes',
            'submenu' => [
                [
                    'text' => 'Mercados Geral',
                    'url'  => 'admin/mercados',
                    'icon' => 'fas fa-fw fa-globe',
                ],
                [
                    'text' => 'Mercados Cambista',
                    'url'  => 'admin/mercados-user',
                    'icon' => 'fas fa-fw fa-user-tag',
                ],
            ]
        ],
        [
            'text' => 'Odds',
            'icon' => 'fas fa-fw fa-percentage',
            'can'  => 'adm',
            'module' => 'configuracoes',
            'submenu' => [
                [
                    'text' => 'Odds Geral',
                    'url'  => 'admin/odds',
                    'icon' => 'fas fa-fw fa-chart-line',
                ],
                [
                    'text' => 'Odds Cambista',
                    'url'  => 'admin/odds-user',
                    'icon' => 'fas fa-fw fa-sliders-h',
                ],
                [
                    'text' => 'Gestão Avançada',
                    'url'  => 'admin/markets/odds',
                    'icon' => 'fas fa-fw fa-cogs',
                ],
            ]
        ],
        [
            'text' => 'Ligas & Confrontos',
            'icon' => 'fas fa-fw fa-futbol',
            'can'  => 'adm',
            'module' => 'configuracoes',
            'submenu' => [
                [
                    'text' => 'Todas as Ligas',
                    'url'  => 'admin/adm-ligas-list',
                    'icon' => 'fas fa-fw fa-list',
                ],
                [
                    'text' => 'Ligas Principais',
                    'url'  => 'admin/gerenciar-ligas-principais',
                    'icon' => 'fas fa-fw fa-trophy',
                ],
                [
                    'text' => 'Ligas Bloqueadas',
                    'url'  => 'admin/gerenciar-ligas',
                    'icon' => 'fas fa-fw fa-ban',
                ],
                [
                    'text' => 'Partidas Bloqueadas',
                    'url'  => 'admin/list-matchs-bloqueadas',
                    'icon' => 'fas fa-fw fa-lock',
                ],
                [
                    'text' => 'Confrontos Bloqueados',
                    'url'  => 'admin/gerenciar-matchs',
                    'icon' => 'fas fa-fw fa-hand-paper',
                ],
                [
                    'text' => 'Confrontos Ao-Vivo',
                    'url'  => 'admin/confrontos-aovivo',
                    'icon' => 'fas fa-fw fa-broadcast-tower',
                ],
                [
                    'text' => 'Todos Confrontos',
                    'url'  => 'admin/confrontos',
                    'icon' => 'fas fa-fw fa-list-alt',
                ],
                [
                    'text' => 'Partidas Personalizadas',
                    'url'  => 'admin/partidas-personalizadas',
                    'icon' => 'fas fa-fw fa-plus-circle',
                ],
            ]
        ],
        [
            'text' => 'Regiões',
            'url'  => 'admin/regions',
            'icon' => 'fas fa-fw fa-globe-americas',
            'can'  => 'adm',
        ],
        [
            'text' => 'Regulamento',
            'url'  => 'admin/regulamento',
            'icon' => 'fas fa-fw fa-gavel',
            'can'  => 'adm',
        ],

        // ============================================================
        // 7. INTEGRAÇÕES & DADOS
        // ============================================================
        ['header' => 'INTEGRAÇÕES'],
        [
            'text'    => 'Integrações (APIs)',
            'icon'    => 'fas fa-fw fa-plug',
            'can'     => 'adm',
            'submenu' => [
                [
                    'text' => 'BetsAPI (Principal)',
                    'url'  => 'admin/betsapi',
                    'icon' => 'fas fa-fw fa-crown',
                    'can'  => 'adm',
                ],
                [
                    'text' => 'API-Football (Reserva)',
                    'url'  => 'admin/api-football',
                    'icon' => 'fas fa-fw fa-futbol',
                    'can'  => 'adm',
                ],
                [
                    'text' => 'Scraper Jogadinha',
                    'url'  => 'admin/scraper',
                    'icon' => 'fas fa-fw fa-spider',
                    'can'  => 'adm',
                ],
                [
                    'text' => 'Config. API (Básica)',
                    'url'  => 'admin/env-config',
                    'icon' => 'fas fa-fw fa-terminal',
                    'can'  => 'isSuperAdmin',
                ],
            ],
        ],

        // ============================================================
        // 8. MARKETING
        // ============================================================
        ['header' => 'MARKETING'],
        [
            'text' => 'Jogos em Destaque',
            'url'  => 'admin/featured-matches',
            'icon' => 'fas fa-fw fa-star',
            'can'  => 'adm',
            'module' => 'marketing',
        ],
        [
            'text' => 'Promoções e Bônus',
            'url'  => 'admin/promocoes',
            'icon' => 'fas fa-fw fa-gift',
            'can'  => 'adm',
        ],
        [
            'text' => 'Gestão de Bônus',
            'url'  => 'admin/bonus',
            'icon' => 'fas fa-fw fa-percentage',
            'can'  => 'adm',
            'module' => 'bonus',
        ],
        [
            'text' => 'Banners',
            'icon' => 'fas fa-fw fa-images',
            'can'  => 'adm',
            'module' => 'marketing',
            'submenu' => [
                [
                    'text' => 'Banners Home',
                    'url'  => 'admin/banners',
                    'icon' => 'fas fa-fw fa-images',
                ],
                [
                    'text' => 'Templates',
                    'url'  => 'admin/banner-templates',
                    'icon' => 'fas fa-fw fa-object-group',
                ],
                [
                    'text' => 'Gerador de Banner',
                    'url'  => 'admin/banner-generator',
                    'icon' => 'fas fa-fw fa-magic',
                ],
            ]
        ],

        // ============================================================
        // 9. CASSINO (desativado por padrão, master ativa)
        // ============================================================
        ['header' => 'CASSINO', 'module' => 'cassino'],
        [
            'text' => 'Gerenciar Cassino',
            'icon' => 'fas fa-fw fa-dice',
            'can'  => 'adm',
            'module' => 'cassino',
            'submenu' => [
                [
                    'text' => 'Jogos',
                    'url'  => 'admin/casino/games',
                    'icon' => 'fas fa-fw fa-gamepad',
                    'module' => 'cassino',
                ],
                [
                    'text' => 'Provedores',
                    'url'  => 'admin/casino/providers',
                    'icon' => 'fas fa-fw fa-cubes',
                    'module' => 'cassino',
                ],
                [
                    'text' => 'Categorias',
                    'url'  => 'admin/casino/categories',
                    'icon' => 'fas fa-fw fa-tags',
                    'module' => 'cassino',
                ],
                [
                    'text' => 'Apostas',
                    'url'  => 'admin/casino/orders',
                    'icon' => 'fas fa-fw fa-history',
                    'module' => 'cassino',
                ],
                [
                    'text' => 'Chaves de API',
                    'url'  => 'admin/casino/keys',
                    'icon' => 'fas fa-fw fa-key',
                    'module' => 'cassino',
                ],
            ]
        ],

        // ============================================================
        // 10. PERSONALIZAÇÃO
        // ============================================================
        ['header' => 'PERSONALIZAÇÃO'],
        [
            'text' => 'Layout e Cores',
            'url'  => 'admin/settings/layout',
            'icon' => 'fas fa-fw fa-paint-brush',
            'can'  => 'adm',
        ],
        [
            'text' => 'Traduções',
            'url'  => 'admin/traducoes',
            'icon' => 'fas fa-fw fa-language',
            'can'  => 'adm',
        ],
        [
            'text' => 'Sobre Nós',
            'url'  => 'admin/about-us',
            'icon' => 'fas fa-fw fa-info-circle',
            'can'  => 'adm',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Menu Filters
    |--------------------------------------------------------------------------
    */
    'filters' => [
        JeroenNoten\LaravelAdminLte\Menu\Filters\GateFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\HrefFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\SearchFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\ActiveFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\ClassesFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\LangFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\DataFilter::class,
        App\Menu\Filters\ModuleFilter::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Plugins Initialization
    |--------------------------------------------------------------------------
    */
    'preloader' => [
        'enabled' => false,
    ],
    'plugins' => [
        'Datatables' => [
            'active' => true,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js',
                ],
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdn.datatables.net/1.10.19/js/dataTables.bootstrap4.min.js',
                ],
                [
                    'type' => 'css',
                    'asset' => false,
                    'location' => '//cdn.datatables.net/1.10.19/css/dataTables.bootstrap4.min.css',
                ],
            ],
        ],
        'Select2' => [
            'active' => true,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.min.js',
                ],
                [
                    'type' => 'css',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/css/select2.css',
                ],
            ],
        ],
        'Chartjs' => [
            'active' => true,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.0/Chart.bundle.min.js',
                ],
            ],
        ],
        'Sweetalert2' => [
            'active' => true,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdn.jsdelivr.net/npm/sweetalert2@11',
                ],
            ],
        ],
        'Toastr' => [
            'active' => true,
            'files' => [
                [
                    'type' => 'css',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css',
                ],
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js',
                ],
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | IFrame
    |--------------------------------------------------------------------------
    */
    'iframe' => [
        'default_tab' => [
            'url' => null,
            'title' => null,
        ],
        'buttons' => [
            'close' => true,
            'close_all' => true,
            'close_all_other' => true,
            'scroll_left' => true,
            'scroll_right' => true,
            'fullscreen' => true,
        ],
        'options' => [
            'loading_screen' => 1000,
            'auto_show_new_tab' => true,
            'use_navbar_items' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Livewire
    |--------------------------------------------------------------------------
    */
    'livewire' => false,
];
