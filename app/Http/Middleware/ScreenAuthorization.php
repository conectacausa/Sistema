<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ScreenAuthorization
{
    public function handle(Request $request, Closure $next, string $screenId): Response
    {
        // Aqui entra sua regra real de permissão por tela.
        // Exemplo futuro:
        // if (!auth()->user()->canAccessScreen((int)$screenId)) abort(403);

        if (!auth()->check()) {
            abort(401);
        }

        // útil para logs/depuração
        $request->attributes->set('screen_id', (int) $screenId);

        return $next($request);
    }
}
