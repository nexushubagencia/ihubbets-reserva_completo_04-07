<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Site; // Site settings are inside 'sites' or a separate 'site_settings'
use App\Models\SiteSetting;

class SettingsController extends Controller
{
    public function index()
    {
        if (auth()->user()->role !== 'admin' && auth()->user()->role !== 'super_admin') {
            abort(403, 'Acesso restrito ao Administrador Master.');
        }
        
        $siteId = config('tenant.site_id', 1);
        $settings = Site::where('id', $siteId)->first();
        return view('admin.settings.general', compact('settings'));
    }

    public function update(Request $request)
    {
        if (auth()->user()->role !== 'admin' && auth()->user()->role !== 'super_admin') {
            abort(403, 'Acesso negado.');
        }

        $siteId = config('tenant.site_id', 1);

        // Pegar apenas colunas que realmente existem na tabela sites
        $validColumns = \Illuminate\Support\Facades\Schema::getColumnListing('sites');

        // Campos protegidos que NUNCA devem ser sobrescritos pelo formulário
        $protected = ['id', 'uuid', 'domain', 'status',
                       'billing_status', 'due_value', 'billing_day', 'next_due_date',
                       'created_at', 'updated_at', 'pix_gateway', 'pix_client_id',
                       'pix_client_secret', 'logo_path', 'favicon_path',
                       'facebook_pixel_id', 'facebook_access_token'];

        $allowedColumns = array_diff($validColumns, $protected);
        $data = $request->only($allowedColumns);

        // Handle checkbox/boolean logic (checkboxes não enviam valor quando desmarcados)
        $booleans = [
            'seniha_enabled', 'queniha_enabled', 'bonus_enabled', 'active_bonus', 'loto_enabled',
            'active_bets', 'merge_pre_live', 'ga_enabled', 'pixel_enabled',
            'lang_selector',
        ];

        foreach ($booleans as $field) {
            if (in_array($field, $allowedColumns)) {
                $data[$field] = $request->has($field) ? 1 : 0;
            }
        }

        // Remover chaves vazias/null para não apagar dados existentes
        $data = array_filter($data, function ($value) {
            return $value !== null && $value !== '';
        });

        // Garantir que booleans desmarcados sejam salvos como 0
        foreach ($booleans as $field) {
            if (in_array($field, $allowedColumns) && !isset($data[$field])) {
                $data[$field] = 0;
            }
        }

        Site::where('id', $siteId)->update($data);

        return redirect()->back()->with('success', 'Configurações e Módulos atualizados com sucesso!');
    }

    public function integrationsView()
    {
        $siteId = config('tenant.site_id', 1);
        $settings = Site::where('id', $siteId)->first();
        return view('admin.settings.integrations', compact('settings'));
    }

    public function updateIntegrations(Request $request)
    {
        if (auth()->user()->role !== 'admin' && auth()->user()->role !== 'super_admin') {
            abort(403, 'Acesso negado.');
        }

        $siteId = config('tenant.site_id', 1);
        $data = $request->validate([
            'pix_gateway' => 'required|string',
            'pix_client_id' => 'nullable|string',
            'pix_client_secret' => 'nullable|string',
        ]);

        Site::where('id', $siteId)->update($data);

        return redirect()->back()->with('success', 'Integração atualizada com sucesso!');
    }

    public function cashoutView()
    {
        $siteId = config('tenant.site_id', 1);
        $settings = Site::where('id', $siteId)->first();
        return view('admin.settings.cashout', compact('settings'));
    }

    public function updateCashout(Request $request)
    {
        if (auth()->user()->role !== 'admin' && auth()->user()->role !== 'super_admin') {
            abort(403, 'Acesso negado.');
        }

        $siteId = config('tenant.site_id', 1);
        $data = $request->validate([
            'cashout_enabled' => 'nullable|string',
            'cashout_tax' => 'required|numeric|min:0',
            'cashout_delay_seconds' => 'required|integer|min:0',
        ]);

        $data['cashout_enabled'] = $request->has('cashout_enabled');

        Site::where('id', $siteId)->update($data);

        return redirect()->back()->with('success', 'Regras de Cash Out atualizadas!');
    }

    public function withdrawalView()
    {
        $siteId = config('tenant.site_id', 1);
        $settings = Site::where('id', $siteId)->first();
        return view('admin.settings.withdrawal', compact('settings'));
    }

    public function updateWithdrawal(Request $request)
    {
        if (auth()->user()->role !== 'admin' && auth()->user()->role !== 'super_admin') {
            abort(403, 'Acesso negado.');
        }

        $siteId = config('tenant.site_id', 1);
        $data = $request->validate([
            'min_withdrawal' => 'required|numeric|min:0',
            'max_withdrawal' => 'required|numeric|min:0',
            'daily_withdrawal_limit' => 'required|numeric|min:0',
        ]);

        Site::where('id', $siteId)->update($data);

        return redirect()->back()->with('success', 'Limites de Saque atualizados!');
    }

    public function layoutView()
    {
        $siteId = config('tenant.site_id', 1);
        $settings = Site::where('id', $siteId)->first();
        $globalThemes = \App\Models\GlobalTheme::where('is_active', true)->orderBy('is_base', 'desc')->orderBy('name', 'asc')->get();
        
        $jsThemeColors = [];
        foreach($globalThemes as $gt) {
            $jsThemeColors[$gt->slug] = is_array($gt->colors) ? $gt->colors : json_decode($gt->colors, true);
        }
        
        $customThemes = $settings->custom_themes ? json_decode($settings->custom_themes, true) : [];
        if (is_array($customThemes)) {
            foreach($customThemes as $slug => $data) {
                // If data has colors nested
                if (isset($data['colors'])) {
                    $jsThemeColors[$slug] = $data['colors'];
                } else {
                    $jsThemeColors[$slug] = $data;
                }
            }
        }

        return view('admin.settings.layout', compact('settings', 'globalThemes', 'jsThemeColors'));
    }

    public function updateLayout(Request $request)
    {
        if (auth()->user()->role !== 'admin' && auth()->user()->role !== 'super_admin') {
            abort(403, 'Acesso negado.');
        }

        $siteId = config('tenant.site_id', 1);
        $site = Site::findOrFail($siteId);

        $request->validate([
            'layout_theme'    => 'nullable|string',
            'logo_file'       => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:4096',
            'favicon_file'    => 'nullable|image|mimes:png,ico,jpg,jpeg|max:512',
            'regulation'      => 'nullable|string',
        ]);

        // Campos de texto/opção diretos — usa nomes EXATOS das colunas da tabela 'sites'
        $fields = [
            'layout_theme',
            'regulation',
            'primary_color',
            'secondary_color',
            'sidebar_color',
            'game_container_color',
            'logo_container_color',
            'odds_button_color',
            'odds_plus_button_color',
            'share_button_bg_color',
            'bet_button_color',
            'background_color',
            'border_color',
            'button_selected_color',
            'button_selected_border_color',
            'menu_hover_color',
            'menu_button_color',
            'action_button_color',
            'cupom_valor_btn_color',
            'cupom_header_color',
            'cupom_apostar_btn_color',
            'modalidade_ativa_color',
            'btn_entrar_color',
            'btn_cadastrar_color',
            'cupom_apostar_btn_hover_color',
            'cupom_valor_btn_hover_color',
            'odds_plus_button_hover_color',
            'footer_bg_color',
            'footer_text_color',
            'tab_active_bg_color',
            'tab_active_text_color',
            'menu_item_active_bg_color',
            'menu_item_active_text_color',
            'btn_primary_text_color',
            'btn_login_border_color',
            'destaque_header_bg_color',
            'destaque_header_text_color',
            'destaque_btn_bg_color',
            'destaque_btn_text_color',
            'texto_rodape_bilhete',
            'ticket_model',
            'sidebar_text_color',
            'card_header_bg_color',
            'card_header_text_color',
            'odd_button_bg_color',
            'odd_button_text_color',
            'btn_entrar_text_color',
            'btn_cadastrar_text_color',
            'search_bar_bg_color',
            'search_bar_text_color',
            'menu_hover_text_color',
            'odd_button_hover_bg_color',
            'odd_button_hover_text_color',
            'btn_login_border_color',
            'name',
            'complete_name',
        ];

        $updateData = $request->only($fields);

        // Sincroniza theme_color com layout_theme para o ApiController ler de qualquer campo
        if ($request->has('layout_theme')) {
            $updateData['theme_color'] = $request->layout_theme;
        }

        // Booleans
        $updateData['active_custom_colors']    = $request->boolean('active_custom_colors');
        $updateData['bluetooth_print_enabled'] = $request->boolean('bluetooth_print_enabled');

        // Logo (suporta PNG, JPG, SVG, WEBP até 5MB)
        if ($request->hasFile('logo_file')) {
            $file = $request->file('logo_file');
            $maxSize = 5 * 1024 * 1024; // 5MB
            
            if ($file->getSize() <= $maxSize) {
                $ext = $file->getClientOriginalExtension() ?: 'png';
                $fileName = 'logo_' . uniqid() . '_' . time() . '.' . $ext;
                $tenantDir = 'storage/tenant_' . $siteId . '/logos';
                if (!file_exists(public_path($tenantDir))) { mkdir(public_path($tenantDir), 0755, true); }
                
                // Remove o logo anterior
                if ($site->logo_path && file_exists(public_path($site->logo_path))) {
                    @unlink(public_path($site->logo_path));
                }
                
                $file->move(public_path($tenantDir), $fileName);
                $updateData['logo_path'] = $tenantDir . '/' . $fileName;
            }
        }

        // Favicon (suporta ICO, PNG, JPG, SVG, GIF até 5MB)
        if ($request->hasFile('favicon_file')) {
            $file = $request->file('favicon_file');
            
            // Validação de tamanho e tipo
            $allowedMimes = ['image/x-icon', 'image/vnd.microsoft.icon', 'image/png', 'image/jpeg', 'image/gif', 'image/svg+xml', 'image/webp'];
            $maxSize = 5 * 1024 * 1024; // 5MB
            
            if ($file->getSize() <= $maxSize) {
                $ext = $file->getClientOriginalExtension() ?: 'png';
                $fileName = 'favicon_' . uniqid() . '_' . time() . '.' . $ext;
                $tenantDir = 'storage/tenant_' . $siteId . '/logos';
                if (!file_exists(public_path($tenantDir))) { mkdir(public_path($tenantDir), 0755, true); }
                
                // Remove o favicon anterior para não acumular arquivos
                if ($site->favicon_path && file_exists(public_path($site->favicon_path))) {
                    @unlink(public_path($site->favicon_path));
                }
                
                $file->move(public_path($tenantDir), $fileName);
                $updateData['favicon_path'] = $tenantDir . '/' . $fileName;
            }
        }

        $site->update($updateData);

        // Limpa o cache do CSS para refletir na Home imediatamente
        \Illuminate\Support\Facades\Cache::forget("tenant_css_{$siteId}");

        return redirect()->back()->with('success', 'Layout e Cores atualizados com sucesso!');
    }

    public function deleteCustomTheme(Request $request)
    {
        if (auth()->user()->role !== 'admin' && auth()->user()->role !== 'super_admin') {
            abort(403, 'Acesso negado.');
        }

        $siteId = config('tenant.site_id', 1);
        $site = Site::findOrFail($siteId);

        if ($request->has('theme_slug')) {
            $slug = $request->theme_slug;
            $customThemes = $site->custom_themes ? json_decode($site->custom_themes, true) : [];
            
            if (isset($customThemes[$slug])) {
                unset($customThemes[$slug]);
                $site->custom_themes = empty($customThemes) ? null : json_encode($customThemes);
                
                if ($site->layout_theme === $slug) {
                    $site->layout_theme = 'azul-escuro';
                    $site->theme_color = 'azul-escuro';
                }
                
                $site->save();
                return response()->json(['success' => true, 'message' => 'Tema excluído com sucesso!']);
            }
        }
        
        return response()->json(['success' => false, 'message' => 'Tema não encontrado.'], 404);
    }

    public function logoView()
    {
        $siteId = config('tenant.site_id', 1);
        $settings = Site::where('id', $siteId)->first();
        return view('admin.settings.logo', compact('settings'));
    }

    public function regulamentoView()
    {
        $siteId = config('tenant.site_id', 1);
        $settings = Site::where('id', $siteId)->first();
        return view('admin.regulamento', compact('settings'));
    }

    public function aboutView()
    {
        $siteId = config('tenant.site_id', 1);
        $settings = Site::where('id', $siteId)->first();
        return view('admin.settings.about', compact('settings'));
    }

    public function updateAbout(Request $request)
    {
        $siteId = config('tenant.site_id', 1);
        $site = Site::find($siteId);
        if (!$site) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Site não encontrado.'], 404);
            }
            return redirect()->back()->with('error', 'Site não encontrado.');
        }
        $site->about_us = $request->input('about_us', '');
        $site->save();

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Sobre Nós atualizado!']);
        }
        return redirect()->back()->with('success', 'Sobre Nós atualizado com sucesso!');
    }


    public function updateMarketing(Request $request)
    {
        $siteId = config('tenant.site_id', 1);
        $site = Site::findOrFail($siteId);

        $data = [];

        if ($request->hasFile('marketing_image_1')) {
            $file = $request->file('marketing_image_1');
            $fileName = 'mkt1_' . time() . '.' . $file->getClientOriginalExtension();
            $tenantDir = 'storage/tenant_' . $siteId . '/marketing';
            if (!file_exists(public_path($tenantDir))) { mkdir(public_path($tenantDir), 0755, true); }
            $file->move(public_path($tenantDir), $fileName);
            $data['marketing_image_1'] = '/' . $tenantDir . '/' . $fileName;
        }

        if ($request->hasFile('marketing_image_2')) {
            $file = $request->file('marketing_image_2');
            $fileName = 'mkt2_' . time() . '.' . $file->getClientOriginalExtension();
            $tenantDir = 'storage/tenant_' . $siteId . '/marketing';
            if (!file_exists(public_path($tenantDir))) { mkdir(public_path($tenantDir), 0755, true); }
            $file->move(public_path($tenantDir), $fileName);
            $data['marketing_image_2'] = '/' . $tenantDir . '/' . $fileName;
        }

        if (!empty($data)) {
            $site->update($data);
        }

        return redirect()->back()->with('success', 'Marketing atualizado com sucesso!');
    }

    public function regulamentoList()
    {
        $siteId = config('tenant.site_id', 1);
        // Retorna no formato array de objetos [ { regulamento: "...", id: 1 } ] como o Vue espera
        $site = Site::where('id', $siteId)->first(['id', 'regulation as regulamento']);
        return response()->json([$site]);
    }

    public function regulamentoUpdate(Request $request, $id)
    {
        $siteId = config('tenant.site_id', 1);
        Site::where('id', $siteId)->where('id', $id)->update([
            'regulation' => $request->regulamento
        ]);
        return response()->json(['success' => true]);
    }

    public function regulamentoUploadImage(Request $request)
    {
        if (!$request->hasFile('image')) {
            return response()->json(['error' => 'Nenhuma imagem enviada'], 400);
        }

        $siteId = config('tenant.site_id', 1);
        $file = $request->file('image');
        $fileName = 'reg_' . time() . '_' . rand(1000, 9999) . '.' . $file->getClientOriginalExtension();
        $tenantDir = 'storage/tenant_' . $siteId . '/regulamento';

        if (!file_exists(public_path($tenantDir))) {
            mkdir(public_path($tenantDir), 0755, true);
        }

        $file->move(public_path($tenantDir), $fileName);
        return response()->json('/' . $tenantDir . '/' . $fileName);
    }

    /**
     * Gera o CSS dinâmico baseado nas configurações da banca.
     * Suporta Cores Personalizadas ou Temas Pré-definidos.
     */
    public function generateCss()
    {
        $siteId = config('tenant.site_id', 1);
        
        $css = (function() use ($siteId) {
            $site = Site::where('id', $siteId)->first();

            // Mapa de Temas - Puxa SEMPRE do banco de dados (Master)
            $themes = [];
            
            // Carrega Temas Globais do Banco
            $globalThemes = \App\Models\GlobalTheme::where('is_active', true)->get();
            foreach($globalThemes as $gt) {
                $gtColors = is_array($gt->colors) ? $gt->colors : json_decode($gt->colors, true);
                if ($gtColors) {
                    $themes[$gt->slug] = $gtColors;
                }
            }

            // Merge custom themes from database
            $customThemes = $site->custom_themes ? json_decode($site->custom_themes, true) : [];
            if (is_array($customThemes)) {
                $themes = array_merge($themes, $customThemes);
            }

            $themeSlug = $site->layout_theme ?? 'azul-escuro';
            $selectedTheme = $themes[$themeSlug] ?? $themes['azul-escuro'] ?? [];

            // Coletor de cores — SEMPRE do tema selecionado (sem modo personalizado)
            $c = function($field, $default = '#000000') use ($selectedTheme) {
                return $selectedTheme[$field] ?? $default;
            };

            // 1. Cores de Estrutura
            $primary        = $c('primary_color', '#35aa71');
            $headerBg       = $c('header_bg_color', $c('sidebar_color', '#173133'));
            $headerLogoText = $c('header_logo_text_color', '#ffffff');
            $headerHamburger = $c('header_hamburger_color', '#162b2d');
            $headerHamburgerHover = $c('header_hamburger_hover_color', $this->adjustBrightness($headerHamburger, -15));
            $sidebar        = $c('sidebar_color', '#173133');
            $gameCont       = $c('game_container_color', '#ffffff');
            $logoCont       = $c('logo_container_color', $sidebar);
            $background     = $c('background_color', '#f9f9f9');
            $border         = $c('border_color', '#dddddd');

            // 2. Sidebar e Busca
            $sidebarText    = $c('sidebar_text_color', '#ffffff');
            $menuHover      = $c('menu_hover_color', '#33a26c');
            $menuHoverText  = $c('menu_hover_text_color', '#ffffff');
            $menuBtn        = $c('menu_button_color', 'transparent');
            $menuActiveBg   = $c('menu_item_active_bg_color', $primary);
            $menuActiveText = $c('menu_item_active_text_color', '#ffffff');
            
            $searchBarBg    = $c('search_bar_bg_color', '#ffffff');
            $searchBarText  = $c('search_bar_text_color', '#333333');
            $searchIconBg   = $c('search_icon_bg_color', $primary);
            $searchIconText = $c('search_icon_text_color', '#ffffff');

            // 3. Jogos e Odds
            $cardHeaderBg       = $c('card_header_bg_color', $primary);
            $cardHeaderText     = $c('card_header_text_color', '#ffffff');
            $teamNameText       = $c('team_name_text_color', '#333333');
            $oddBtnBg           = $c('odd_button_bg_color', '#ffffff');
            $oddBtnText         = $c('odd_button_text_color', '#333333');
            $oddBtnHoverBg      = $c('odd_button_hover_bg_color', $primary);
            $oddBtnHoverText    = $c('odd_button_hover_text_color', '#ffffff');
            $btnSel             = $c('button_selected_color', '#0692bc');
            $btnSelBord         = $c('button_selected_border_color', $this->adjustBrightness($btnSel, -30));
            $oddsPlus           = $c('odds_plus_button_color', '#1aa6d0');
            $oddsPlusHover      = $c('odds_plus_button_hover_color', $this->adjustBrightness($oddsPlus, -20));
            $shareBtnBg         = $c('share_button_bg_color', '#1aa6d0');
            $shareBtnIcon       = $c('share_button_icon_color', '#ffffff');
            $modAtiva           = $c('modalidade_ativa_color', $primary);

            // 4. Destaques
            $destaqueHeaderBg   = $c('destaque_header_bg_color', $primary);
            $destaqueHeaderText = $c('destaque_header_text_color', '#ffffff');
            $destaqueBtnBg      = $c('destaque_btn_bg_color', $oddsPlus);
            $destaqueBtnText    = $c('destaque_btn_text_color', '#ffffff');

            // 5. Cupom
            $cupomHeader       = $c('cupom_header_color', $sidebar);
            $cupomValor        = $c('cupom_valor_btn_color', $oddsPlus);
            $cupomValorHover   = $c('cupom_valor_btn_hover_color', $this->adjustBrightness($cupomValor, -20));
            $cupomApostar      = $c('cupom_apostar_btn_color', $primary);
            $cupomApostarHover = $c('cupom_apostar_btn_hover_color', $this->adjustBrightness($cupomApostar, -20));

            // 6. Auth / Botões Gerais
            $btnEntrar        = $c('btn_entrar_color', $sidebar);
            $btnEntrarText    = $c('btn_entrar_text_color', '#ffffff');
            $btnCadastrar     = $c('btn_cadastrar_color', '#074b34');
            $btnCadastrarText = $c('btn_cadastrar_text_color', '#ffffff');
            $btnLoginBorder   = $c('btn_login_border_color', $primary);
            $btnPrimaryText   = $c('btn_primary_text_color', '#ffffff');
            $actionBtn        = $c('action_button_color', $oddsPlus);
            $actionBtnHover   = $c('action_button_hover_color', $this->adjustBrightness($actionBtn, -20));

            // 7. Rodapé
            $footerBg      = $c('footer_bg_color', $sidebar);
            $footerText    = $c('footer_text_color', '#555555');
            $tabActiveBg   = $c('tab_active_bg_color', '#ffffff');
            $tabActiveText = $c('tab_active_text_color', '#333333');

            // 8. Extras (campos do editor que antes eram hardcoded)
            $sidebarHeader       = $c('sidebar_header_color', $primary);
            $sidebarHeaderText   = $c('sidebar_header_text_color', '#ffffff');
            $ticketConsultBg     = $c('ticket_consult_bg_color', $sidebar);
            $liveColor           = $c('live_color', '#cc3333');
            $cupomTrash          = $c('cupom_trash_color', '#ff0000');
            $cupomValorBtnText   = $c('cupom_valor_btn_text_color', '#ffffff');
            $cupomBodyBg         = $c('cupom_body_bg_color', '#ffffff');
            $modalidadeAtivaText = $c('modalidade_ativa_text_color', '#ffffff');
            $modalBg             = $c('modal_bg_color', '#ffffff');
            $btnEntrarHover      = $c('btn_entrar_hover_color', $this->adjustBrightness($btnEntrar, -20));
            $btnCadastrarHover   = $c('btn_cadastrar_hover_color', $this->adjustBrightness($btnCadastrar, -20));
            $oddsPlusHover       = $c('odds_plus_button_hover_color', $this->adjustBrightness($oddsPlus, -20));
            $btnSelBorder        = $c('button_selected_border_color', $this->adjustBrightness($btnSel, -30));
            $actionBtnHover      = $c('action_button_hover_color', $this->adjustBrightness($actionBtn, -20));
            $modalBg           = $background;
            $cupomBodyBg       = '#ffffff';
            $cardBg            = '#ffffff';

            $css = "
            /* IHUB DYNAMIC THEME SYSTEM - V2.3.0 - " . time() . " */
            :root {
                /* Estrutura */
                --primary-color: {$primary};
                --header_bg--color: {$headerBg};
                --header_logo_text--color: {$headerLogoText};
                --header_hamburger--color: {$headerHamburger};
                --header_hamburger_hover--color: {$headerHamburgerHover};
                --sidebar--color: {$sidebar};
                --sidebar_text--color: {$sidebarText};
                --container_jogos--color: {$gameCont};
                --logo--color: {$logoCont};
                --fundo--color: {$background};
                --linhas--color: {$border};

                /* Sidebar e Menus */
                --hover_menu--color: {$menuHover};
                --menu_hover_text--color: {$menuHoverText};
                --btn_menu_principal--color: {$menuBtn};
                --menu_active_bg--color: {$menuActiveBg};
                --menu_active_text--color: {$menuActiveText};
                --sidebar_header--color: {$sidebarHeader};
                --sidebar_header_text--color: {$sidebarHeaderText};
                --search_bar_bg--color: {$searchBarBg};
                --search_bar_text--color: {$searchBarText};
                --search_icon_bg--color: {$searchIconBg};
                --search_icon_text--color: {$searchIconText};

                /* Jogos e Odds */
                --card_header_bg--color: {$cardHeaderBg};
                --card_header_text--color: {$cardHeaderText};
                --card_bg--color: {$cardBg};
                --team_name_text--color: {$teamNameText};
                --odd_button_bg--color: {$oddBtnBg};
                --odd_button_text--color: {$oddBtnText};
                --odd_btn_hover_bg--color: {$oddBtnHoverBg};
                --odd_btn_hover_text--color: {$oddBtnHoverText};
                --btn_selecionado-color: {$btnSel};
                --btn_selecionado-border-color: {$btnSelBord};
                --odds_plus_button--color: {$oddsPlus};
                --odds_plus_button_hover--color: {$oddsPlusHover};
                --share_button_bg--color: {$shareBtnBg};
                --share_button_icon--color: {$shareBtnIcon};
                --modalidade_ativa--color: {$modAtiva};
                --modalidade_ativa_text--color: {$modalidadeAtivaText};
                --live-color: {$liveColor};
                --live_text--color: {$liveColor};

                /* Destaques */
                --destaque_header_bg--color: {$destaqueHeaderBg};
                --destaque_header_text--color: {$destaqueHeaderText};
                --destaque_btn_bg--color: {$destaqueBtnBg};
                --destaque_btn_text--color: {$destaqueBtnText};

                /* Cupom / Bilhete */
                --cupom_header--color: {$cupomHeader};
                --cupom_body_bg--color: {$cupomBodyBg};
                --cupom_valor_btn--color: {$cupomValor};
                --cupom_valor_btn_text--color: {$cupomValorBtnText};
                --cupom_valor_btn_hover--color: {$cupomValorHover};
                --cupom_apostar_btn--color: {$cupomApostar};
                --cupom_apostar_btn_hover--color: {$cupomApostarHover};
                --cupom_trash--color: {$cupomTrash};
                --ticket_consult_bg--color: {$ticketConsultBg};

                /* Auth & Ações */
                --btn_entrar--color: {$btnEntrar};
                --btn_entrar_text--color: {$btnEntrarText};
                --btn_entrar_hover--color: {$btnEntrarHover};
                --btn_cadastrar--color: {$btnCadastrar};
                --btn_cadastrar_text--color: {$btnCadastrarText};
                --btn_cadastrar_hover--color: {$btnCadastrarHover};
                --btn_login_border--color: {$btnLoginBorder};
                --btn_salvar--color: {$actionBtn};
                --btn_salvar_hover--color: {$actionBtnHover};

                /* Rodapé */
                --footer_bg--color: {$footerBg};
                --footer_text--color: {$footerText};
                --tab_active_bg--color: {$tabActiveBg};
                --tab_active_text--color: {$tabActiveText};

                /* Modais */
                --modal_bg--color: {$modalBg};

                /* IHUB Gradient */
                --ihub-gradient: linear-gradient(135deg, {$primary} 0%, " . $this->adjustBrightness($primary, -30) . " 100%);
            }

            .sidebar-menu > li.active > a { border-left-color: var(--primary-color) !important; background: var(--ihub-gradient) !important; }
            .btn-primary { background: var(--ihub-gradient) !important; border: none !important; box-shadow: 0 4px 15px rgba(0,0,0,0.3); }
        ";
            return $css;
        })();

        return response($css)->header('Content-Type', 'text/css')->header('Cache-Control', 'no-cache, no-store, must-revalidate')->header('Pragma', 'no-cache');
    }

    /**
     * Salva um novo tema personalizado para a banca via AJAX
     */
    public function saveCustomTheme(Request $request)
    {
        $siteId = config('tenant.site_id', 1);
        $site = Site::findOrFail($siteId);
        
        $name = $request->name;
        $colors = $request->colors;
        $slug = \Illuminate\Support\Str::slug($name);

        $customThemes = $site->custom_themes ? json_decode($site->custom_themes, true) : [];
        
        $customThemes[$slug] = [
            'name' => $name,
            'colors' => $colors
        ];

        $site->custom_themes = json_encode($customThemes);
        $site->save();

        return response()->json(['success' => true]);
    }

    /**
     * Helper para ajustar brilho de HEX colors
     */
    private function adjustBrightness($hex, $steps) {
        $steps = max(-255, min(255, $steps));
        $hex = str_replace('#', '', $hex);
        if (strlen($hex) == 3) {
            $hex = str_repeat(substr($hex, 0, 1), 2) . str_repeat(substr($hex, 1, 1), 2) . str_repeat(substr($hex, 2, 1), 2);
        }
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));

        $r = max(0, min(255, $r + $steps));
        $g = max(0, min(255, $g + $steps));  
        $b = max(0, min(255, $b + $steps));

        return '#' . str_pad(dechex($r), 2, '0', STR_PAD_LEFT) . str_pad(dechex($g), 2, '0', STR_PAD_LEFT) . str_pad(dechex($b), 2, '0', STR_PAD_LEFT);
    }
}

