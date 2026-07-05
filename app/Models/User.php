<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'master_users';

    /**
     * The attributes that are mass assignable.
     * Combinação dos campos do sistema antigo + Nexus Hub
     */
    protected $fillable = [
        'name',
        'email',
        'username',
        'password',
        'role',           // Nexus Hub: super_admin, admin, manager, seller, user
        'nivel',          // Sistema antigo: adm, gerente, cambista
        'situacao',       // ativo/inativo
        'status',         // 1=ativo, 0=inativo
        'site_id',
        'adm_id',
        'gerente_id',
        'region_id',
        'contato',
        'endereco',
        'address',

        // Comissões (sistema antigo)
        'comissao1', 'comissao2', 'comissao3', 'comissao4', 'comissao5',
        'comissao6', 'comissao7', 'comissao8', 'comissao9', 'comissao10',
        'comissao_gerente',
        'comissao_cambistas',
        'comissao_loto',
        // Saldos
        'saldo_casadinha', 'saldo_loto', 'saldo_simples', 'saldo_gerente', 'saldo_bolao',
        // Financeiro
        'entradas', 'entrada_loto', 'entrada_bolao', 'entradas_abertas',
        'entrada_casadinha', 'entrada_simples',
        'saidas', 'comissoes', 'lancamentos',
        'quantidade_aposta',
        // Nexus Hub + Afiliados v2.1
        'avatar', 'is_active', 'phone', 'balance', 'commission_rate',
        'pix_key', 'pix_key_type', 'cpf', 'birth_date', 'parent_id',
        'cambista_id', 'manager_commission_rate', 'can_create_coupons',
        'theme_preference',
        'comissao_online', 
        'comissao_gerente_online',
        'affiliate_id',
        'referred_by_id',
        'last_activity',
        // REI BET additions
        'credito',
        'saldo_bonus',
        'rollover_meta',
        'rollover_atual',
        'promocao_ativa_id',
        'verified',
        'nascimento',
        // Online commissions (10-tier)
        'online_comissao1', 'online_comissao2', 'online_comissao3',
        'online_comissao4', 'online_comissao5', 'online_comissao6',
        'online_comissao7', 'online_comissao8', 'online_comissao9',
        'online_comissao10',
        // Flat online commissions
        'flat_online_comissao',
        // Balance bonus
        'balance_bonus',
        // Bolao commission
        'comissao_bolao',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    /**
     * The attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'last_activity' => 'datetime',
        ];
    }

    // ==========================================
    // RELATIONSHIPS (sistema antigo)
    // ==========================================

    public function apostas()
    {
        return $this->hasMany(Aposta::class, 'user_id');
    }

    public function gerente()
    {
        return $this->belongsTo(User::class, 'gerente_id');
    }

    public function cambistas()
    {
        return $this->hasMany(User::class, 'gerente_id');
    }

    public function region()
    {
        return $this->belongsTo(Region::class, 'region_id');
    }

    /**
     * AdminLTE profile image
     */
    public function adminlte_image()
    {
        if ($this->avatar) {
            return asset($this->avatar);
        }
        return 'https://ui-avatars.com/api/?name='.urlencode($this->name).'&background=4e73df&color=fff&size=128';
    }

    /**
     * AdminLTE profile URL
     */
    public function adminlte_profile_url()
    {
        return 'admin/edit-user/' . $this->id;
    }

    /**
     * AdminLTE user description
     */
    public function adminlte_desc()
    {
        return $this->role . ' - ' . ($this->email ?? $this->username);
    }
}
