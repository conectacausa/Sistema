<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class ScreenAuthorization
{
    public function handle(Request $request, Closure $next, ?string $screenId = null): Response
    {
        // Determina o screen_id:
        // - prioridade: parâmetro do middleware (screen:5)
        // - fallback: querystring screen_id=5 (usado pelos calls do JS)
        $screenId = $screenId ?? (string) $request->query('screen_id', '');

        // Se não tem screen_id, não bloqueia (evita quebrar rotas genéricas)
        if ($screenId === '') {
            return $next($request);
        }

        $screenIdInt = (int) $screenId;

        $user = $request->user();
        if (!$user) {
            // se não autenticado, deixa o auth tratar
            return $next($request);
        }

        $permissaoId = (int) ($user->permissao_id ?? 0);

        // Se usuário não tem permissao_id, bloqueia
        if ($permissaoId <= 0) {
            return $this->deny($request);
        }

        $allowed = DB::table('permissao_modulo_tela')
            ->where('permissao_id', $permissaoId)
            ->where('tela_id', $screenIdInt)
            ->where('ativo', true)
            ->exists();

        if (!$allowed) {
            return $this->deny($request);
        }

        return $next($request);
    }

    private function deny(Request $request): Response
    {
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'message' => 'Acesso negado para esta tela.'
            ], 403);
        }

        // Web: manda para dashboard
        return redirect()->route('dashboard');
    }
}
