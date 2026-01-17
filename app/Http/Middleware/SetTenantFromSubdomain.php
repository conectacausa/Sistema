<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Empresa;
use App\Models\Configuracao;

class SetTenantFromSubdomain
{
    public function handle(Request $request, Closure $next): Response
    {
        // Captura o {sub} vindo da rota de domínio
        $sub = (string) $request->route('sub');

        // Define o default global para geração de URLs (route('...'))
        // Isso resolve o erro "Missing parameter: sub"
        URL::defaults(['sub' => $sub]);

        // Busca empresa pelo subdomínio
        $empresa = Empresa::query()
            ->where('subdominio', $sub)
            ->whereNull('deleted_at')
            ->first();

        if (!$empresa) {
            abort(404, 'Empresa não encontrada para este subdomínio.');
        }

        // Salva empresa do tenant na sessão
        session([
            'tenant_empresa_id' => $empresa->id,
            'tenant_subdominio' => $sub,
        ]);

        // Carrega configuração (logos) se existir
        $config = Configuracao::query()
            ->where('empresa_id', $empresa->id)
            ->whereNull('deleted_at')
            ->first();

        // Mantém na sessão (ou null)
        session([
            'tenant_config_id' => $config?->id,
        ]);

        return $next($request);
    }
}
