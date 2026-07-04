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
        // MASTER / SUPER ADMIN (sempre visível para super_admin)
        // ============================================================
        ['header' => 'MASTER / SUPER ADMIN', 'can' => 'isSuperAdmin'],
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
            'text' => 'Caixa Master',
            'url'  => 'admin/master/financeiro',
            'icon' => 'fas fa-fw fa-hand-holding-usd',
            'can'  => 'isSuperAdmin',
        ],

        // ============================================================
        // DASHBOARD (sempre visível)
        // ============================================================
        ['header' => 'DASHBOARD'],
        [
            'text' => 'Página Inicial',
            'url'  => 'admin',
            'icon' => 'fas fa-fw fa-tachometer-alt',
        ],

        // ============================================================
        // COLABORADORES (sempre visível)
        // ============================================================
        ['header' => 'COLABORADORES'],
        [
            'text' => 'Usuários',
            'icon' => 'fas fa-fw fa-users-cog',
            'submenu' => [
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
            ]
        ],

        // ============================================================
        // FINANCEIRO & DEPÓSITOS
        // ============================================================
        ['header' => 'FINANCEIRO & DEPÓSITOS'],
        [
            'text' => 'Configuração Mercado Pago',
            'url'  => 'admin/financeiro-gateways',
            'icon' => 'fas fa-fw fa-qrcode',
            'can'  => 'adm',
            'module' => 'gateway_deposito',
        ],
        [
            'text' => 'Histórico de Depósitos',
            'url'  => 'admin/depositos',
            'icon' => 'fas fa-fw fa-money-bill-wave',
            'can'  => 'adm',
            'module' => 'gateway_deposito',
        ],
        [
            'text' => 'Solicitações de Saque',
            'url'  => 'admin/saques',
            'icon' => 'fas fa-fw fa-money-check-alt',
            'can'  => 'adm',
            'module' => 'payments',
        ],
        [
            'text' => 'Lançamentos',
            'url'  => 'admin/lancamentos',
            'icon' => 'fas fa-fw fa-exchange-alt',
            'can'  => 'adm',
            'module' => 'lancamentos',
        ],

        // ============================================================
        // CAIXAS & RELATÓRIOS (sempre visível)
        // ============================================================
        ['header' => 'CAIXAS & RELATÓRIOS'],
        [
            'text' => 'Caixa Gerente',
            'url'  => 'admin/caixa-adm-gerente',
            'icon' => 'fas fa-fw fa-university',
            'can'  => 'adm',
        ],
        [
            'text' => 'Caixa Cambista',
            'url'  => 'admin/caixa-adm-cambista',
            'icon' => 'fas fa-fw fa-wallet',
        ],
        [
            'text' => 'Relatórios',
            'icon' => 'fas fa-fw fa-chart-line',
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
                    'can'  => 'adm',
                ],
                [
                    'text' => 'Extrato de Transações',
                    'url'  => 'admin/relatorio-transacoes',
                    'icon' => 'fas fa-fw fa-list-alt',
                    'module' => 'extrato',
                ],
            ]
        ],

        // ============================================================
        // APOSTAS & JOGOS (sempre visível - core do sistema)
        // ============================================================
        ['header' => 'APOSTAS & JOGOS'],
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
            'text' => 'Loto (Quininha/Seninha)',
            'url'  => 'admin/loto',
            'icon' => 'fas fa-fw fa-star',
            'can'  => 'adm',
        ],
        [
            'text' => 'Cash Out (Antecipar)',
            'url'  => 'admin/cashout',
            'icon' => 'fas fa-fw fa-hand-holding-usd',
            'can'  => 'adm',
        ],
        [
            'text' => 'Bolao (Pool Betting)',
            'url'  => 'admin/bolao',
            'icon' => 'fas fa-fw fa-futbol',
            'can'  => 'adm',
        ],
        [
            'text' => 'Mapa de Apostas',
            'url'  => 'admin/mapa-apostas',
            'icon' => 'fas fa-fw fa-map',
            'can'  => 'adm',
        ],
        [
            'text' => 'Gerenc. Risco',
            'url'  => 'admin/gerenciamento-riscos',
            'icon' => 'fas fa-fw fa-shield-alt',
            'can'  => 'adm',
            'module' => 'riscos',
        ],

        // ============================================================
        // CONFIGURAÇÕES TÉCNICAS (module: configuracoes)
        // ============================================================
        ['header' => 'CONFIGURAÇÕES TÉCNICAS', 'module' => 'configuracoes'],
        [
            'text' => 'Gerenciar Mercados',
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
            'text' => 'Gerenciar Odds',
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
            ]
        ],
        [
            'text' => 'Ligas & Confrontos',
            'icon' => 'fas fa-fw fa-futbol',
            'can'  => 'adm',
            'module' => 'configuracoes',
            'submenu' => [
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
                    'text' => 'Confrontos Bloqueados',
                    'url'  => 'admin/gerenciar-matchs',
                    'icon' => 'fas fa-fw fa-lock',
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
            'text' => 'Configurações de Sistema',
            'url'  => 'admin/configuracoes',
            'icon' => 'fas fa-fw fa-cogs',
            'can'  => 'adm',
            'module' => 'configuracoes',
        ],
        [
            'text' => 'Regulamento',
            'url'  => 'admin/regulamento',
            'icon' => 'fas fa-fw fa-gavel',
            'can'  => 'adm',
        ],

        // ============================================================
        // MARKETING & DESTAQUES (module: marketing)
        // ============================================================
        ['header' => 'MARKETING & DESTAQUES'],
        [
            'text' => 'Jogos em Destaque',
            'url'  => 'admin/featured-matches',
            'icon' => 'fas fa-fw fa-star',
            'can'  => 'adm',
            'module' => 'marketing',
        ],
        [
            'text' => 'Gestão de Bônus',
            'url'  => 'admin/bonus',
            'icon' => 'fas fa-fw fa-gift',
            'can'  => 'adm',
            'module' => 'bonus',
        ],

        // ============================================================
        // PERSONALIZAÇÃO
        // ============================================================
        ['header' => 'PERSONALIZAÇÃO'],
        [
            'text' => 'Layout e Cores',
            'url'  => 'admin/settings/layout',
            'icon' => 'fas fa-fw fa-paint-brush',
            'can'  => 'adm',
        ],
        [
            'text' => 'Banners Home',
            'url'  => 'admin/banners',
            'icon' => 'fas fa-fw fa-images',
            'can'  => 'adm',
            'module' => 'marketing',
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
