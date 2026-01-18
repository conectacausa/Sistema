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
        $sub = (string) ($request->route('sub') ?? '');

        // Fallback: se por algum motivo não veio via route param, extrai do host
        if ($sub === '') {
            $host = (string) $request->getHost();
            $parts = explode('.', $host);
            if (count($parts) >= 3) {
                $sub = (string) $parts[0];
            }
        }

        $sub = trim($sub);

        if ($sub === '') {
            return $next($request);
        }

        // Define default global para geração de URLs (route('...'))
        URL::defaults(['sub' => $sub]);

        // Busca empresa pelo subdomínio
        $empresa = Empresa::query()
            ->where('subdominio', $sub)
            ->whereNull('deleted_at')
            ->first();

        if (!$empresa) {
            abort(404, 'Empresa não encontrada para este subdomínio.');
        }

        // Carrega configuração (logos) se existir
        $config = Configuracao::query()
            ->where('empresa_id', $empresa->id)
            ->whereNull('deleted_at')
            ->first();

        // Salva em sessão
        session([
            'tenant_empresa_id' => $empresa->id,
            'tenant_subdominio' => $sub,
            'tenant_config_id'  => $config?->id,
        ]);

        // ✅ BIND no container para o layout conseguir ler com app('tenant') / app('tenant.config')
        app()->instance('tenant', $empresa);
        app()->instance('tenant.config', $config);

        // (opcional) deixa também na request
        $request->attributes->set('tenant', $empresa);
        $request->attributes->set('tenant.config', $config);

        return $next($request);
    }
}
