<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\User;
use App\Services\ApiProviderService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(ApiProviderService::class, function ($app) {
            return new ApiProviderService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::define('isSuperAdmin', function (User $user) {
            return $user->role === 'super_admin' && $user->site_id == 1;
        });

        Gate::define('is-admin', function (User $user) {
            return in_array($user->role, ['super_admin', 'admin']);
        });

        Gate::define('is-manager', function (User $user) {
            return $user->role === 'manager';
        });

        Gate::define('is-seller', function (User $user) {
            return $user->role === 'seller';
        });

        Gate::define('is-admin-or-manager', function (User $user) {
            return in_array($user->role, ['super_admin', 'admin', 'manager']);
        });

        // Gates do sistema antigo - usadas no menu AdminLTE (can => 'adm', can => 'gerente')
        Gate::define('adm', function (User $user) {
            return in_array($user->nivel, ['adm']) || in_array($user->role, ['super_admin', 'admin']);
        });

        Gate::define('gerente', function (User $user) {
            return in_array($user->nivel, ['gerente']) || $user->role === 'manager';
        });
    }
}
