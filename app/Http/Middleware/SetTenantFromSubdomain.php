<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Empresa;
use App\Models\Configuracao;

class SetTenantFromSubdomain
{
    public function handle(Request $request, Closure $next)
    {
        $host = $request->getHost(); // ex: teste.conecttarh.com.br
        $parts = explode('.', $host);
        $sub = $parts[0] ?? null; // "teste"

        // master.conecttarh.com.br pode ter regras próprias depois
        $empresa = Empresa::query()
            ->where('subdominio', $sub)
            ->first();

        if (!$empresa) {
            abort(404, 'Empresa não encontrada para este subdomínio.');
        }

        $cfg = Configuracao::where('empresa_id', $empresa->id)->first();

        config([
            'tenant.subdominio' => $sub,
            'tenant.empresa' => $empresa,
            'tenant.config' => $cfg,
        ]);

        return $next($request);
    }
}
