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

        $middleware->alias([
            'tenant' => \App\Http\Middleware\SetTenantFromSubdomain::class,
            'tenant.user' => \App\Http\Middleware\EnsureUserBelongsToTenant::class,
            'auth' => \App\Http\Middleware\Authenticate::class,

            // ✅ Autorizações por tela
            'screen' => \App\Http\Middleware\ScreenAuthorization::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    ->create();
