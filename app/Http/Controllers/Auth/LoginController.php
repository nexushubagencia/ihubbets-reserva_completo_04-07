<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    protected $redirectTo = '/admin';

    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->middleware('auth')->only('logout');
    }

    public function username()
    {
        return 'username';
    }

    protected function redirectTo(): string
    {
        $user = Auth::user();

        if (!$user) {
            return '/admin';
        }

        $role = $user->role ?? '';
        $nivel = $user->nivel ?? '';

        if ($role === 'client' || $nivel === 'cliente') {
            return '/cliente';
        }

        if ($role === 'seller' || $nivel === 'cambista') {
            return '/cambista';
        }

        if ($role === 'manager' || $nivel === 'gerente') {
            return '/gerente';
        }

        return '/admin';
    }
}
