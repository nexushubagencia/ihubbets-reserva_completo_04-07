<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:master_users'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:master_users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @return User
     */
    protected function create(array $data)
    {
        $siteId = config('tenant.site_id', 1);
        $referralCode = session('referral_code');
        $affiliateId = null;
        $referredById = null;

        if ($referralCode) {
            $affiliate = \Illuminate\Support\Facades\DB::table('affiliates')
                ->where('code', $referralCode)
                ->where('site_id', $siteId)
                ->first();

            if ($affiliate) {
                $affiliateId = $affiliate->id;
                $referredById = $affiliate->user_id;
                
                // Increment registrations count
                \Illuminate\Support\Facades\DB::table('affiliates')
                    ->where('id', $affiliate->id)
                    ->increment('registrations');
            }
        }

        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'username' => $data['username'] ?? explode('@', $data['email'])[0],
            'password' => Hash::make($data['password']),
            'site_id' => $siteId,
            'role' => 'user',
            'affiliate_id' => $affiliateId,
            'referred_by_id' => $referredById,
            'status' => 1,
            'situacao' => 'ativo',
            'balance' => 0,
        ]);
    }
}
