<?php

namespace App\Core\Unified;

use App\Models\User;
use App\Models\UserMarketAdjustment;
use App\Models\UserOddAdjustment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * 👑 UserManagement - Lógica Unificada de Gerenciamento de Usuários
 * 
 * Centraliza a criação e atualização de Gerentes e Cambistas, garantindo:
 * 1. Consistência de campos (legado vs novo).
 * 2. Replicação de configurações de Odds/Mercados.
 * 3. Gestão de saldos entre hierarquias.
 */
class UserManagement
{
    /**
     * Cria um novo usuário (Gerente ou Cambista)
     */
    public static function create(array $data, User $creator)
    {
        return DB::transaction(function () use ($data, $creator) {
            $siteId = config('tenant.site_id', 1);
            $role = $data['role'] ?? 'seller'; // 'manager' ou 'seller'

            // Se o criador for gerente e estiver criando um cambista, desconta o saldo
            if ($creator->role === 'manager' && $role === 'seller') {
                $totalToDeduct = ($data['balance'] ?? 0) + ($data['balance_bonus'] ?? 0);
                if ($creator->balance < $totalToDeduct) {
                    throw new \Exception('Saldo do gerente insuficiente.');
                }
                $creator->decrement('balance', $totalToDeduct);
            }

            // Mapeia campos do legado para o novo se necessário
            $userData = [
                'name'             => $data['name'],
                'username'         => $data['username'],
                'password'         => Hash::make($data['password']),
                'role'             => $role,
                'nivel'            => $role === 'manager' ? 'gerente' : 'cambista',
                'site_id'          => $siteId,
                'gerente_id'       => ($role === 'seller') ? ($data['manager_id'] ?? $creator->id) : null,
                'status'           => 1,
                'contato'          => $data['contato'] ?? null,
                'address'          => $data['address'] ?? null,
                'balance'          => $data['balance'] ?? 0,
                'manager_commission_rate' => $data['manager_commission_rate'] ?? 0,
                'prize_paid_percent'      => $data['prize_paid_percent'] ?? 0,
                'region_id'        => $data['region_id'] ?? null,
            ];

            // Comissões (1-10 jogos)
            for ($i = 1; $i <= 10; $i++) {
                $userData["comissao{$i}"] = $data["comissao{$i}"] ?? 0;
            }

            $user = User::create($userData);

            // Replicar configurações de Mercado/Odd do Admin ou Gerente
            self::replicateSettings($creator, $user);

            return $user;
        });
    }

    /**
     * Atualiza um usuário existente
     */
    public static function update(User $user, array $data)
    {
        if (isset($data['password']) && !empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $user->update($data);
        return $user;
    }

    /**
     * Replicar configurações de Odds e Mercados de um usuário para outro
     */
    public static function replicateSettings(User $from, User $to)
    {
        // Mercados
        $adjustments = UserMarketAdjustment::where('user_id', $from->id)->get();
        foreach ($adjustments as $adj) {
            UserMarketAdjustment::create([
                'site_id'            => $to->site_id,
                'user_id'            => $to->id,
                'market_name'        => $adj->market_name,
                'adjustment_percent' => $adj->adjustment_percent,
                'status'             => $adj->status
            ]);
        }

        // Odds
        $oddAdjustments = UserOddAdjustment::where('user_id', $from->id)->get();
        foreach ($oddAdjustments as $adj) {
            UserOddAdjustment::create([
                'site_id'            => $to->site_id,
                'user_id'            => $to->id,
                'market_name'        => $adj->market_name,
                'odd_label'          => $adj->odd_label,
                'adjustment_percent' => $adj->adjustment_percent,
                'status'             => $adj->status
            ]);
        }
    }
}
