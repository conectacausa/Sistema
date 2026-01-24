<?php

namespace App\Http\Middleware;

use App\Services\AuditoriaLogger;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class ScreenAuthorization
{
    public function handle(Request $request, Closure $next, ?string $screenId = null): Response
    {
        // Determina o screen_id:
        // - prioridade: parâmetro do middleware (screen:6)
        // - fallback: querystring screen_id=6 (usado pelos calls do JS)
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
        $slug = ltrim($request->path(), '/'); // ex: cargos/cbo

        // Se usuário não tem permissao_id, bloqueia + log
        if ($permissaoId <= 0) {
            $this->logNegado($request, $user, $screenIdInt, $slug, $permissaoId, 'Usuário sem permissao_id.');
            return $this->deny($request);
        }

        $allowed = DB::table('permissao_modulo_tela')
            ->where('permissao_id', $permissaoId)
            ->where('tela_id', $screenIdInt)
            ->where('ativo', true)
            ->exists();

        if (!$allowed) {
            $this->logNegado($request, $user, $screenIdInt, $slug, $permissaoId, 'Tela não liberada para o grupo do usuário.');
            return $this->deny($request);
        }

        // Permitido → processa request e loga acesso
        $response = $next($request);

        AuditoriaLogger::log([
            'empresa_id'  => $user->empresa_id ?? null,
            'usuario_id'  => $user->id ?? null,
            'tela_id'     => $screenIdInt,
            'tela_slug'   => $slug,
            'acao'        => 'ACESSO_PERMITIDO',
            'descricao'   => 'Acesso autorizado à tela.',
            'status_code' => method_exists($response, 'getStatusCode') ? $response->getStatusCode() : 200,
            'payload'     => [
                'permissao_id' => $permissaoId,
                'screen_id'    => $screenIdInt,
            ],
            'request'     => $request,
        ]);

        return $response;
    }

    private function logNegado(Request $request, $user, int $screenIdInt, string $slug, int $permissaoId, string $motivo): void
    {
        AuditoriaLogger::log([
            'empresa_id'  => $user->empresa_id ?? null,
            'usuario_id'  => $user->id ?? null,
            'tela_id'     => $screenIdInt,
            'tela_slug'   => $slug,
            'acao'        => 'ACESSO_NEGADO',
            'descricao'   => $motivo,
            'status_code' => 403,
            'payload'     => [
                'permissao_id' => $permissaoId,
                'screen_id'    => $screenIdInt,
            ],
            'request'     => $request,
        ]);
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
