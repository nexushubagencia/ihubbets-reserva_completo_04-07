<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Banner;
use App\Models\Site;
use App\Models\User;
use App\Models\Game;
use App\Models\Mercado;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use App\Traits\MatchFormattingTrait;

class ApiController extends Controller
{
    use MatchFormattingTrait;
    
    /**
     * 🚀 Rota Principal (SPA)
     * Carrega o esqueleto do site e as configurações iniciais
     */
    public function index()
    {
        $site = Site::where('id', config('tenant.site_id', 1))->first() ?? Site::first();
        if (!$site) abort(404, 'Site não configurado.');

        $siteInfo = $this->siteInfoData($site);
        $banners = Banner::where('site_id', $site->id)->where('status', 1)->get()->map(function($b) {
            $imgUrl = (str_starts_with($b->image_path, 'http'))
                ? $b->image_path
                : asset($b->image_path);
            return [
                'id'    => $b->id,
                'image' => $imgUrl,
                'img'   => $imgUrl,
                'foto'  => $imgUrl,
                'link'  => $b->link_url ?? $b->link ?? '#',
                'position' => $b->position ?? 'home_main',
            ];
        });
        
        // Dados de conta se logado
        $account = [];
        if (Auth::check()) {
            $user = Auth::user();
            $account = [
                'id' => $user->id,
                'username' => $user->username ?? $user->name,
                'balance' => (float)$user->balance,
                'balance_bonus' => (float)$user->balance_bonus,
                'nivel' => $user->nivel ?? $user->role ?? 'cambista'
            ];
        }

        return view('welcome', [
            'site_info' => $siteInfo,
            'banners'   => $banners,
            'account'   => $account
        ]);
    }

    protected function siteInfoData($site)
    {
        if (!$site) return [];

        $settings = \App\Models\SiteSetting::where('site_id', $site->id)->first();
        if (!$settings) {
            $settings = new \App\Models\SiteSetting();
        }

        // Logo: suporta caminhos com ou sem prefixo 'storage/'
        $logoPath = $site->logo_path ?? '';
        if ($logoPath && !str_starts_with($logoPath, 'http')) {
            $logo = asset($logoPath);
        } else {
            $logo = $logoPath ?: null;
        }

        // Favicon
        $faviconPath = $site->favicon_path ?? '';
        if ($faviconPath && !str_starts_with($faviconPath, 'http')) {
            $favicon = asset($faviconPath);
        } else {
            $favicon = $faviconPath ?: asset('favicon.ico');
        }

        // Tema: admin salva em site->theme_color (layout_theme), SiteSetting tem theme_name (legado)
        $themeColor = $site->theme_color ?? $site->layout_theme ?? $settings->theme_name ?? 'verde-claro';

        // Cores personalizadas: admin salva em site->*_color; fallback para SiteSetting
        $customColorsEnabled = (bool)($site->active_custom_colors ?? $site->custom_colors_enabled ?? $settings->custom_colors_enabled ?? 0);

        return [
            "id" => $site->id,
            "site_id" => $site->uuid ?? 'default',
            "adm_id" => 1,
            "domain" => $site->domain ?? request()->getHost(),
            "status" => 1,
            "max_sellers" => 10,
            "due_day" => 10,
            "app_version" => "1.1.5",
            "payment_gateway" => $site->payment_gateway ?? "mercado_pago",
            "pix_module" => $site->pix_module ?? 0,
            "active_new_ticket_realtime" => 0,
            "active_online_user" => $site->active_online_user ?? 0,
            "active_financial_manager" => 0,
            "active_casino" => $site->active_casino ?? 0,
            "active_sports" => 1,
            "sports" => 1,
            "display_modalities" => "sports",
            "meta_pixel_id" => null,
            "active_meta_pixel" => 0,
            "infrabets_fee" => null,
            "advanced_sharing" => 1,
            "active_notifications" => 1,
            "apk_name" => $site->apk_name ?? 'app.apk',
            "complete_name" => $site->complete_name ?? $site->name,
            "first_name" => $site->first_name ?? $site->name,
            "second_name" => $site->second_name ?? '',
            "first_letter" => substr($site->name, 0, 1),
            "second_letter" => substr($site->name, 1, 1),
            "site_template" => "default",
            "theme_color" => $themeColor,
            "active_custom_colors" => $customColorsEnabled ? 1 : 0,
            "custom_colors" => [
                "sidebar_color"                 => $site->sidebar_color ?? $settings->sidebar_color ?? "#173133",
                "container_jogos"               => $site->game_container_color ?? $settings->game_container_color ?? "#35aa71",
                "logo_color"                    => $site->logo_container_color ?? $settings->logo_container_color ?? "#329d6a",
                "btn_color"                     => $site->odds_plus_button_color ?? $settings->button_odds_color ?? "#1aa6d0",
                "btn_cef_color"                 => $site->bet_main_buttons_color ?? $settings->button_home_draw_away_color ?? "#1e262a",
                "fundo_colors"                  => $site->background_color ?? $settings->background_color ?? "#dddddd",
                "linhas_color"                  => $site->border_color ?? $settings->lines_color ?? "#1aa6d0",
                "botao_selecionado_color"        => $site->button_selected_color ?? $settings->button_selected_color ?? "#2976a3",
                "botao_selecionado_border_color" => $site->button_selected_border_color ?? $settings->button_selected_border_color ?? "#2976a3",
                "hover_menu_color"              => $site->menu_hover_color ?? $settings->hover_menu_color ?? "#339063",
                "btn_menu_principal_color"       => $site->menu_button_color ?? $settings->main_menu_button_color ?? "#000000",
                "btn_salvar_hover_color"         => $site->action_button_color ?? $settings->save_button_color ?? "#1093bb"
            ],
            "language" => "pt-br",
            "show_language_selector" => 0,
            "currency" => "BRL",
            "timezone" => config('app.timezone', 'America/Fortaleza'),
            "logo_url" => $logo,
            "favicon_url" => $favicon,
            "logo_path" => $logo,
            "company_name" => $site->company_name ?? $settings->nome_banca ?? $site->name,
            "whatsapp_number" => $settings->whatsapp_number ?? $site->whatsapp_number ?? null,
            "texto_rodape" => $settings->footer_text ?? ($site->name . " - Aposta com Confiança"),
            "social_instagram" => $settings->instagram_link ?? $site->social_instagram ?? null,
            "social_facebook" => $site->social_facebook ?? null,
            "social_twitter" => $site->social_twitter ?? null,
            "social_youtube" => $site->social_youtube ?? null,
            "marketing_image_1" => $site->marketing_image_1 ? asset($site->marketing_image_1) : null,
            "marketing_image_2" => $site->marketing_image_2 ? asset($site->marketing_image_2) : null,
            "regulamento" => $site->regulation ?? "Regras Gerais...",
            "about_us" => $site->about_us ?? $settings->about_us ?? '',
            "valor_mini_aposta" => $site->valor_mini_aposta ?? 5,
            "valor_max_aposta" => $site->valor_max_aposta ?? 1000,
            "premio_max" => $site->premio_max ?? 5000,
            "cotacao_mini_bilhete" => $site->cotacao_mini_bilhete ?? 1.01,
            "cotacao_max_bilhete" => $site->cotacao_max_bilhete ?? 1000,
            "quantidade_jogos_max_bilhete" => $site->quantidade_jogos_max_bilhete ?? 20,
            "quantidade_jogos_mini_bilhete" => $site->quantidade_jogos_mini_bilhete ?? 1,
            "aposta_ativa" => $site->aposta_ativa ?? 1,
            "active_seller_user" => 1,
            "config" => is_array($site->configuracoes) ? $site->configuracoes : (json_decode($site->configuracoes, true) ?? []),
            "active_carrossel" => 1,
            "carrossel_home" => 1,
            "banners_home" => 1,
            "active_banners" => 1,
            "configuracoes" => array_merge(
                is_array($site->configuracoes) ? $site->configuracoes : (json_decode($site->configuracoes, true) ?? []),
                [
                    "sobrenos_texto" => $site->about_us ?? $settings->about_us ?? '',
                    "texto_rodape" => $site->texto_rodape ?? $site->about_us ?? '',
                    "op_quininha" => ($site->active_loto ?? 1) ? ($site->op_quininha ?? 'Não') : 'Não',
                    "op_seninha" => ($site->active_loto ?? 1) ? ($site->op_seninha ?? 'Não') : 'Não',
                    "op_basquete" => $site->op_basquete ?? 'Sim',
                    "op_tenis" => $site->op_tenis ?? 'Sim',
                    "op_ufcbox" => $site->op_ufcbox ?? 'Sim',
                    "op_futebol" => $site->op_futebol ?? 'Sim',
                    "op_volei" => $site->op_volei ?? 'Sim',
                    "op_cassino" => $site->op_cassino ?? 'Não',
                ]
            ),
            "settings" => [
                "request_document" => 0,
                "active_carrossel" => 1,
                "carrossel_home" => 1,
                "banners_home" => 1
            ],
            "user_document" => null,
            "img_exist_S3" => false,
            "active_sports" => 1,
            "0" => [
                "request_document" => 0,
                "carrosel_ativado" => 1,
                "carrossel_home" => 1,
                "banners_home" => 1,
                "active_banners" => 1
            ]
        ];
    }

    public function siteInfo()
    {
        $site = Site::where('id', config('tenant.site_id', 1))->first() ?? Site::first();
        if (!$site) return response()->json(['error' => 'site_not_found'], 404);

        $settings = \App\Models\SiteSetting::where('site_id', $site->id)->first();
        if (!$settings) {
            $settings = new \App\Models\SiteSetting();
        }

        // Formato legado que o frontend espera (array com um único item)
        $legacySettings = [
            'id' => $site->id,
            'limite_apostas_iguais' => 0,
            'valor_mini_aposta' => $site->valor_mini_aposta ?? $settings->valor_mini_aposta ?? 1,
            'valor_max_aposta' => $site->valor_max_aposta ?? $settings->valor_max_aposta ?? 1000,
            'premio_max' => $site->premio_max ?? $settings->premio_max ?? 50000,
            'cotacao_mini_bilhete' => $site->cotacao_mini_bilhete ?? $settings->cotacao_mini_bilhete ?? 1.4,
            'cotacao_max_bilhete' => $site->cotacao_max_bilhete ?? $settings->cotacao_max_bilhete ?? 1000,
            'quantidade_jogos_mini_bilhete' => $site->quantidade_jogos_mini_bilhete ?? $settings->quantidade_jogos_mini_bilhete ?? 1,
            'quantidade_jogos_max_bilhete' => $site->quantidade_jogos_max_bilhete ?? $settings->quantidade_jogos_max_bilhete ?? 12,
            'quantidade_times_visitantes_mesmo_camp' => 5,
            'texto_rodape' => $settings->footer_text ?? ($site->name . " - Boa sorte!!!"),
            'sobrenos_texto' => $site->about_us ?? $settings->about_us ?? '',
            'social_facebook' => $site->social_facebook ?? null,
            'social_youtube' => $site->social_youtube ?? null,
            'social_instagram' => $settings->instagram_link ?? $site->social_instagram ?? null,
            'social_twitter' => $site->social_twitter ?? null,
            'whatsapp_number' => $settings->whatsapp_number ?? $site->whatsapp_number ?? null,
            'suport_phone' => null,
            'alerta_aposta_acima' => 50,
            'bloquear_odd_abaixo' => $settings->block_odds_below ?? 1,
            'travar_odd_acima' => $settings->odd_max_pre ?? 100,
            'cambista_pode_cancelar' => 'Sim',
            'tempo_limite_camb_cancela_aposta' => $settings->cancel_time_minutes ?? 5,
            'aposta_ativa' => $site->aposta_ativa ?? 'Sim',
            'bloq_aposta_madrugada' => 'Não',
            'data_limite_jogos' => '2050-06-19',
            'hours_limit_date' => '23:59:59',
            'minutos_antes_inicio' => $settings->min_before_game ?? 0,
            'pin_validation_minutes' => $settings->min_valid_pin ?? 500,
            'pin_unico' => 0,
            'pin_update_config' => 0,
            'cambista_paga' => 0,
            'percentual_paga' => 0,
            'gerente_cancela' => 1,
            'gerente_remove_cambista' => 1,
            'gerente_edita_cambista' => 1,
            'gerente_cria_cambista' => 1,
            'limite_tempo_aovivo' => $settings->accept_bet_until ?? 90,
            'live_quantidade_jogos_mini_bilhete' => $settings->qtd_min_live ?? 1,
            'live_quantidade_jogos_max_bilhete' => 10,
            'live_valor_mini_aposta' => $settings->val_min_live ?? 2,
            'live_valor_max_aposta' => $settings->val_max_live ?? 500,
            'live_premio_max' => $settings->prem_max_live ?? 10000,
            'live_cotacao_mini_bilhete' => $settings->cot_min_live ?? 2,
            'live_cotacao_max_bilhete' => $settings->cot_max_live ?? 1000,
            'live_cotacao_mini_gerar_comissao' => $settings->cot_min_comm ?? 2,
            'live_maximo_cotacao' => $settings->odd_max_live ?? 100,
            'percentage_live' => $settings->alt_cot_live ?? 0,
            // Modalidades
            'op_futebol' => $site->op_futebol ?? 'Sim',
            'modo_listagem' => 'todos',
            'op_ufcbox' => $site->op_ufcbox ?? 'Sim',
            'op_volei' => $site->op_volei ?? 'Sim',
            'op_basquete' => $site->op_basquete ?? 'Sim',
            'op_tenis' => $site->op_tenis ?? 'Sim',
            'op_quininha' => ($site->active_loto ?? 1) ? ($site->op_quininha ?? 'Não') : 'Não',
            'op_seninha' => ($site->active_loto ?? 1) ? ($site->op_seninha ?? 'Não') : 'Não',
            'op_cassino' => $site->op_cassino ?? 'Não',
            // Datas
            'op_hoje' => 'sim',
            'op_amanha' => 'sim',
            'op_depois_amanha' => 'sim',
            'op_aovivo' => 'sim',
            'op_app_cliente' => 1,
            'op_outras_loterias' => ($site->active_loto ?? 1) ? 'sim' : 'nao',
            'carrosel_ativado' => ($site->active_marketing ?? 1) ? ($site->carrosel_ativado ?? 1) : 0,
            'mesclar_apostas' => $settings->merge_pre_live ?? 1,
            'futebol_ao_vivo' => 'Não',
            'site_id' => $site->uuid ?? 'default',
            'pix_status' => $site->pix_module ?? 0,
            'term_accepted' => 0,
            'request_document' => 0,
            'created_at' => $site->created_at,
            'updated_at' => $site->updated_at,
            'pix_mode' => 'checkbox',
            'bankizi_status' => 0,
            'regulamento' => $site->regulation ?? "Regras Gerais...",
            'about_us' => $site->about_us,
        ];

        // Busca banners ativos
        $banners = Banner::where('site_id', $site->id)->where('status', 1)->orderBy('order_index', 'asc')->get()->map(function($b) {
            return [
                'id'         => $b->id,
                'title'      => $b->title,
                'image'      => str_starts_with($b->image_path, 'http') ? $b->image_path : asset($b->image_path),
                'image_path' => str_starts_with($b->image_path, 'http') ? $b->image_path : asset($b->image_path),
                'link'       => $b->link_url ?? $b->link ?? '#',
                'position'   => $b->position ?? 'home_main'
            ];
        });

        $legacySettings['banners'] = $banners;

        return response()->json([$legacySettings]);
    }

    public function userLogado()
    {
        if (Auth::check()) {
            $user = Auth::user();
            $user->saldo_simples = $user->balance ?? 0;
            $user->saldo_casadinha = $user->balance_bonus ?? 0;
            $user->cpf = $user->cpf;
            $user->pix_key = $user->pix_key;
            $user->pix_key_type = $user->pix_key_type;
            
            return response()->json($user->makeHidden(['password', 'remember_token', 'two_factor_secret', 'two_factor_recovery_codes']));
        }
        
        $site = Site::where('id', config('tenant.site_id', 1))->first() ?? Site::first();
        $siteInfo = $this->siteInfoData($site);
        
        return response()->json([
            'id' => 0,
            'username' => 'guest',
            'balance' => 0,
            'saldo_simples' => 0,
            'saldo_casadinha' => 0,
            'front_document' => null,
            'configuracoes' => $siteInfo['configuracoes'],
            '0' => $siteInfo['0']
        ]);
    }

    public function logout()
    {
        Auth::logout();
        return response()->json(['status' => 'success']);
    }

    public function login(Request $request)
    {
        $loginField = filter_var($request->username, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        $siteId = config('tenant.site_id', 1);

        if (Auth::attempt([$loginField => $request->username, 'password' => $request->password, 'site_id' => $siteId])) {
            $user = Auth::user();
            $isAdmin = in_array($user->role, ['super_admin', 'admin', 'manager']);

            return response()->json([
                'token' => 'session_auth_token_' . bin2hex(random_bytes(16)),
                'user' => $user->makeHidden(['password', 'remember_token', 'two_factor_secret', 'two_factor_recovery_codes']),
                'status' => 'success',
                'is_admin' => $isAdmin,
                'redirect' => $isAdmin ? '/home' : '/'
            ]);
        }

        return response()->json([
            'error' => 'invalid_credentials',
            'status' => 'error',
            'message' => 'Usuário ou senha incorretos.'
        ], 401);
    }
}
