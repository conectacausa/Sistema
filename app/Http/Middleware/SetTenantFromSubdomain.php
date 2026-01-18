<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Empresa;
use App\Models\Configuracao;

class SetTenantFromSubdomain
{
    public function handle(Request $request, Closure $next): Response
    {
        // Pega o {sub} da rota de domínio: Route::domain('{sub}.conecttarh.com.br')
        $sub = (string) ($request->route('sub') ?? '');

        // Fallback: se por algum motivo não veio via route param, extrai do host
        if ($sub === '') {
            $host = (string) $request->getHost(); // ex: teste.conecttarh.com.br
            $parts = explode('.', $host);
            if (count($parts) >= 3) {
                $sub = (string) $parts[0];
            }
        }

        $sub = trim($sub);

        // Se não tiver subdomínio, só segue
        if ($sub === '') {
            return $next($request);
        }

        // Busca empresa do subdomínio
        $empresa = Empresa::query()
            ->where('subdominio', $sub)
            ->first();

        // Se não achou empresa, pode seguir ou abortar (aqui vou seguir)
        if (!$empresa) {
            return $next($request);
        }

        // Busca config dessa empresa (se não existir, cria uma vazia)
        $config = Configuracao::query()
            ->where('empresa_id', $empresa->id)
            ->first();

        // Se não existir config, cria em memória (sem salvar) só para não ficar null
        if (!$config) {
            $config = new Configuracao();
            $config->empresa_id = $empresa->id;
        }

        // BIND no container para qualquer lugar do app conseguir pegar:
        app()->instance('tenant', $empresa);
        app()->instance('tenant.config', $config);

        // Também salva na request (útil para debug e controllers)
        $request->attributes->set('tenant', $empresa);
        $request->attributes->set('tenant.config', $config);

        return $next($request);
    }
}
