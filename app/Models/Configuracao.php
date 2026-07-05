<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Configuracao extends Model
{

   protected $table = 'configuracaos';

    protected $fillable = [
    // Bet Limits
    'valor_mini_aposta',
    'valor_max_aposta',
    'premio_max',
    // Loto Limits
    'menor_valor_loto',
    'max_valor_loto',
    // Ticket Config
    'cotacao_mini_bilhete',
    'cotacao_max_bilhete',
    'quantidade_jogos_mini_bilhete',
    'quantidade_jogos_max_bilhete',
    'quantidade_times_visitantes_mesmo_camp',
    // Odd Controls
    'bloquear_odd_abaixo',
    'travar_odd_acima',
    // Alerts
    'texto_rodape',
    'email_alerta',
    'alerta_aposta_acima',
    // Cambista Rules
    'cambista_pode_cancelar',
    'tempo_limite_camb_cancela_aposta',
    'gerente_pode_cancelar',
    // Operational
    'aposta_ativa',
    'bloq_aposta_madrugada',
    'data_limite_jogos',
    // Sports Toggles
    'op_futebol',
    'op_ufcbox',
    'op_quininha',
    'op_seninha',
    'op_basquete',
    'op_tenis',
    'op_volei',
    'op_e_sports',
    'op_cassino',
    // Live
    'futebol_ao_vivo',
    'time_live',
    'cotacao_live',
    // Multi-tenant
    'site_id',
    // Commissions
    'comissao_premio',
    // Bonus/Payment
    'max_bonus_conversion',
    'min_deposit',
    'max_deposit',
    'min_withdrawal',
    'max_withdrawal',
    'withdrawal_limit_day',
    // Affiliate %
    'perc_sub_lv1',
    'perc_sub_lv2',
    'perc_sub_lv3',
    // Payment Gateway
    'suitpay_client_id',
    'suitpay_client_secret',
    'active_deposit_gateway',
    'active_withdrawal_gateway',
    // Theme Colors
    'nome_plataforma',
    'cor_principal',
    'cor_secundaria',
    'cor_fundo',
    'cor_texto',
    'cor_botoes',
    'cor_botoes_perfil',
    'cor_fundo_campeonato',
    // Cash Out
    'cash_out_ativo',
    'cash_out_taxa',
    // Affiliate
    'affiliate_enabled',
    'affiliate_commission',
    // WhatsApp / Help
    'link_whatsapp',
    'status_whatsapp',
    'link_ajuda',
   ];

   public function dataLimite()
   {
      $siteId = config('tenant.site_id', 1);
      $config = Configuracao::where('site_id', $siteId)->first();

      return $config ? $config->data_limite_jogos : now()->addYear()->format('Y-m-d H:i:s');
   }
}
