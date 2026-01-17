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
            // Define a empresa (tenant) a partir do subdomínio
            'tenant' => \App\Http\Middleware\SetTenantFromSubdomain::class,

            // Garante que o usuário pertence à empresa do subdomínio
            'tenant.user' => \App\Http\Middleware\EnsureUserBelongsToTenant::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    ->create();
