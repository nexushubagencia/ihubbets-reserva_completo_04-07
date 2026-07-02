<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$themes = App\Models\GlobalTheme::all();
foreach($themes as $theme) {
    $selectedTheme = is_array($theme->colors) ? $theme->colors : json_decode($theme->colors, true);
    
    $c = function($field, $default) use ($selectedTheme) {
        return $selectedTheme[$field] ?? $default;
    };
    
    $primary    = $c('primary_color', '#285e3d');
    $sidebar    = $c('sidebar_color', '#031808');
    $gameCont   = $c('game_container_color', '#ffffff');
    $logoCont   = $c('logo_container_color', $sidebar);
    $background = $c('background_color', '#f4f6f9');
    $border     = $c('border_color', '#ddd');

    $oddsPlus   = $c('odds_plus_button_color', '#1aa6d0');
    $betMain    = $c('bet_main_buttons_color', $sidebar);
    $btnSel     = $c('button_selected_color', $primary);
    
    $menuHover  = $c('menu_hover_color', $primary);
    $menuBtn    = $c('menu_button_color', 'transparent');
    $actionBtn  = $c('action_button_color', $primary);
    $menuActiveBg = $c('menu_item_active_bg_color', $primary);
    $menuActiveText = $c('menu_item_active_text_color', '#ffffff');
    
    $cupomValor   = $c('cupom_valor_btn_color', $oddsPlus);
    $cupomHeader  = $c('cupom_header_color', $sidebar);
    $cupomApostar = $c('cupom_apostar_btn_color', $primary);
    $modAtiva     = $c('modalidade_ativa_color', $primary);

    $btnEntrar    = $c('btn_entrar_color', $sidebar);
    $btnCadastrar = $c('btn_cadastrar_color', $primary);
    $btnPrimaryText = $c('btn_primary_text_color', '#ffffff');
    $btnLoginBorder = $c('btn_login_border_color', $primary);

    $footerBg     = $c('footer_bg_color', '#ffffff');
    $footerText   = $c('footer_text_color', '#555555');
    $tabActiveBg  = $c('tab_active_bg_color', '#ffffff');
    $tabActiveText = $c('tab_active_text_color', '#333333');
    $destaqueHeaderBg = $c('destaque_header_bg_color', $primary);
    $destaqueHeaderText = $c('destaque_header_text_color', '#ffffff');
    $destaqueBtnBg = $c('destaque_btn_bg_color', $oddsPlus);
    $destaqueBtnText = $c('destaque_btn_text_color', '#ffffff');
    $sidebarText  = $c('sidebar_text_color', '#ffffff');
    $cardHeaderBg = $c('card_header_bg_color', $primary);
    $cardHeaderText = $c('card_header_text_color', '#ffffff');
    $oddButtonBg  = $c('odd_button_bg_color', '#ffffff');
    $oddButtonText = $c('odd_button_text_color', '#333333');
    $btnEntrarText = $c('btn_entrar_text_color', '#ffffff');
    $btnCadastrarText = $c('btn_cadastrar_text_color', '#ffffff');
    $searchBarBg  = $c('search_bar_bg_color', '#ffffff');
    $searchBarText = $c('search_bar_text_color', '#333333');
    $menuHoverText = $c('menu_hover_text_color', '#ffffff');

    $fields = [
        'primary_color' => $primary,
        'sidebar_color' => $sidebar,
        'game_container_color' => $gameCont,
        'logo_container_color' => $logoCont,
        'background_color' => $background,
        'border_color' => $border,
        'odds_plus_button_color' => $oddsPlus,
        'bet_main_buttons_color' => $betMain,
        'button_selected_color' => $btnSel,
        'menu_hover_color' => $menuHover,
        'menu_button_color' => $menuBtn,
        'action_button_color' => $actionBtn,
        'menu_item_active_bg_color' => $menuActiveBg,
        'menu_item_active_text_color' => $menuActiveText,
        'cupom_valor_btn_color' => $cupomValor,
        'cupom_header_color' => $cupomHeader,
        'cupom_apostar_btn_color' => $cupomApostar,
        'modalidade_ativa_color' => $modAtiva,
        'btn_entrar_color' => $btnEntrar,
        'btn_cadastrar_color' => $btnCadastrar,
        'btn_primary_text_color' => $btnPrimaryText,
        'btn_login_border_color' => $btnLoginBorder,
        'footer_bg_color' => $footerBg,
        'footer_text_color' => $footerText,
        'tab_active_bg_color' => $tabActiveBg,
        'tab_active_text_color' => $tabActiveText,
        'destaque_header_bg_color' => $destaqueHeaderBg,
        'destaque_header_text_color' => $destaqueHeaderText,
        'destaque_btn_bg_color' => $destaqueBtnBg,
        'destaque_btn_text_color' => $destaqueBtnText,
        'sidebar_text_color' => $sidebarText,
        'card_header_bg_color' => $cardHeaderBg,
        'card_header_text_color' => $cardHeaderText,
        'odd_button_bg_color' => $oddButtonBg,
        'odd_button_text_color' => $oddButtonText,
        'btn_entrar_text_color' => $btnEntrarText,
        'btn_cadastrar_text_color' => $btnCadastrarText,
        'search_bar_bg_color' => $searchBarBg,
        'search_bar_text_color' => $searchBarText,
        'menu_hover_text_color' => $menuHoverText,
    ];

    foreach($fields as $k => $v) {
        $selectedTheme[$k] = $v;
    }
    
    $theme->colors = $selectedTheme;
    $theme->save();
}
echo "Full themes synchronized.\n";
