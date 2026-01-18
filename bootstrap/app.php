<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {

        // ===============================
        // ALIASES DE MIDDLEWARE (Laravel 12)
        // ===============================
        $middleware->alias([
            // Define empresa (tenant) pelo subdomínio
            'tenant' => \App\Http\Middleware\SetTenantFromSubdomain::class,

            // Garante que usuário pertence ao tenant
            'tenant.user' => \App\Http\Middleware\EnsureUserBelongsToTenant::class,

            // OVERRIDE do auth para evitar route('login') exigir {sub}
            'auth' => \App\Http\Middleware\Authenticate::class,

            // Controle de acesso por tela (screen_id)
            'screen' => \App\Http\Middleware\ScreenAuthorization::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    ->create();
