<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            Route::middleware('web')->group(base_path('routes/gerente.php'));
            Route::middleware('web')->group(__DIR__.'/../routes/cliente.php');
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->validateCsrfTokens(except: [
            'api/*',
        ]);
        $middleware->append(\App\Http\Middleware\TenantIdentifier::class);
        $middleware->append(\App\Http\Middleware\CheckSiteStatus::class);
        
        // Multi-Tenant: registra o alias 'tenant' para uso nas rotas admin
        $middleware->alias([
            'tenant' => \App\Http\Middleware\TenantMiddleware::class,
            'admin' => \App\Http\Middleware\EnsureIsAdmin::class,
            'activity' => \App\Http\Middleware\UpdateLastActivity::class,
            'seller' => \App\Http\Middleware\EnsureIsSeller::class,
            'manager' => \App\Http\Middleware\EnsureIsManager::class,
            'client' => \App\Http\Middleware\EnsureIsClient::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
