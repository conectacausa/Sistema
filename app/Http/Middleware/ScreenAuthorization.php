<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ScreenAuthorization
{
    public function handle(Request $request, Closure $next, string $screenId): Response
    {
        // ✅ Ponto de integração:
        // Se você já tem um sistema de permissões por tela, valide aqui.
        // Exemplo (pseudo):
        // if (!auth()->user()->canAccessScreen((int)$screenId)) abort(403);

        // Por enquanto: exige usuário logado (auth) + permite prosseguir.
        // Ajuste conforme a regra real do seu projeto.
        if (!auth()->check()) {
            abort(401);
        }

        // Opcional: disponibiliza para views/logs
        $request->attributes->set('screen_id', (int)$screenId);

        return $next($request);
    }
}
