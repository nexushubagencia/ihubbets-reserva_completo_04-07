<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\VerifyUser;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;

class VerificationController extends Controller
{
    public function verify($code)
    {
        $verify = VerifyUser::where('code', $code)
            ->where('used', false)
            ->where('type', 'email')
            ->first();

        if (!$verify) {
            return redirect('/')->with('warning', 'Link de verificação inválido ou já utilizado.');
        }

        if ($verify->expires_at && $verify->expires_at->isPast()) {
            return redirect('/')->with('warning', 'Link de verificação expirado.');
        }

        $user = User::find($verify->user_id);
        if (!$user) {
            return redirect('/')->with('warning', 'Usuário não encontrado.');
        }

        $user->verified = 1;
        $user->save();

        $verify->used = true;
        $verify->save();

        return redirect('/')->with('success', 'Email verificado com sucesso! Agora você pode fazer login.');
    }

    public static function sendVerification(User $user)
    {
        $code = Str::random(40);

        VerifyUser::create([
            'user_id' => $user->id,
            'code' => $code,
            'type' => 'email',
            'used' => false,
            'expires_at' => now()->addHours(24),
        ]);

        $verifyUrl = url('/verificar/' . $code);

        try {
            Mail::raw("Clique no link para verificar seu email: {$verifyUrl}", function ($message) use ($user) {
                $message->to($user->email)
                        ->subject('Verifique seu email - IHUB BETS');
            });
        } catch (\Exception $e) {
            \Log::error('Erro ao enviar email de verificação: ' . $e->getMessage());
        }
    }
}
