<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Filial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class FiliaisApiController extends Controller
{
    private function tenantEmpresaId(Request $request): ?int
    {
        // 1) Se o middleware SetTenantFromSubdomain registrou "tenant" no container
        if (app()->bound('tenant')) {
            $tenant = app('tenant');
            if (is_object($tenant)) {
                // tenta padrões comuns
                if (isset($tenant->id)) return (int) $tenant->id;
                if (isset($tenant->empresa_id)) return (int) $tenant->empresa_id;
                if (method_exists($tenant, 'getKey')) return (int) $tenant->getKey();
            }
        }

        // 2) Se existir helper tenant() (alguns pacotes usam)
        if (function_exists('tenant')) {
            try {
                $t = tenant();
                if (is_object($t)) {
                    if (isset($t->id)) return (int) $t->id;
                    if (method_exists($t, 'getKey')) return (int) $t->getKey();
                }
            } catch (\Throwable $e) {}
        }

        // 3) Se o middleware colocou no request attributes
        $attr = $request->attributes->get('tenant_id');
        if (!empty($attr)) return (int) $attr;

        return null;
    }

    public function index(Request $request)
    {
        $perPage = max(1, min((int) $request->integer('per_page', 50), 200));
        $q = trim((string) $request->query('q', ''));

        $paisId = $request->query('pais_id');
        $estadoId = $request->query('estado_id');
        $cidadeId = $request->query('cidade_id');

        // Colunas reais de localização (aceita pais_id ou pais etc.)
        $colPais   = Schema::hasColumn('filiais', 'pais_id')   ? 'pais_id'   : (Schema::hasColumn('filiais', 'pais')   ? 'pais'   : null);
        $colEstado = Schema::hasColumn('filiais', 'estado_id') ? 'estado_id' : (Schema::hasColumn('filiais', 'estado') ? 'estado' : null);
        $colCidade = Schema::hasColumn('filiais', 'cidade_id') ? 'cidade_id' : (Schema::hasColumn('filiais', 'cidade') ? 'cidade' : null);

        // Coluna de empresa
        $colEmpresa = Schema::hasColumn('filiais', 'empresa_id')
            ? 'empresa_id'
            : (Schema::hasColumn('filiais', 'empresa') ? 'empresa' : null);

        $empresaId = $this->tenantEmpresaId($request);

        $query = Filial::query()
            ->select(['id', 'nome_fantasia', 'razao_social', 'cnpj'])
            ->orderByDesc('id');

        // Sempre filtrar pela empresa do tenant (se possível)
        if ($colEmpresa && $empresaId) {
            $query->where($colEmpresa, $empresaId);
        }

        // Busca por Razão/Nome Fantasia/CNPJ
        if ($q !== '') {
            $qDigits = preg_replace('/\D+/', '', $q);

            $query->where(function ($w) use ($q, $qDigits) {
                $w->where('razao_social', 'ilike', '%' . $q . '%')
                  ->orWhere('nome_fantasia', 'ilike', '%' . $q . '%');

                if ($qDigits !== '') {
                    $w->orWhereRaw(
                        "regexp_replace(cnpj, '[^0-9]', '', 'g') LIKE ?",
                        ['%' . $qDigits . '%']
                    );
                }
            });
        }

        // Filtros por localização
        if ($colPais && $paisId !== null && $paisId !== '') {
            $query->where($colPais, (int) $paisId);
        }
        if ($colEstado && $estadoId !== null && $estadoId !== '') {
            $query->where($colEstado, (int) $estadoId);
        }
        if ($colCidade && $cidadeId !== null && $cidadeId !== '') {
            $query->where($colCidade, (int) $cidadeId);
        }

        // Só faz with() se existir padrão *_id
        if (Schema::hasColumn('filiais', 'pais_id') && Schema::hasColumn('filiais', 'estado_id') && Schema::hasColumn('filiais', 'cidade_id')) {
            $query->with([
                'cidade:id,nome,estado_id',
                'estado:id,nome,sigla,pais_id',
                'pais:id,nome',
            ]);
        }

        $paginator = $query->paginate($perPage);

        return response()->json([
            'data' => $paginator->items(),
            'meta' => [
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
            ],
        ]);
    }

    public function destroy(Request $request, Filial $filial)
    {
        // Segurança: só excluir se for da empresa do tenant
        $colEmpresa = Schema::hasColumn('filiais', 'empresa_id')
            ? 'empresa_id'
            : (Schema::hasColumn('filiais', 'empresa') ? 'empresa' : null);

        $empresaId = $this->tenantEmpresaId($request);

        if ($colEmpresa && $empresaId) {
            $filialEmpresa = (int) ($filial->{$colEmpresa} ?? 0);
            if ($filialEmpresa !== (int) $empresaId) {
                abort(403);
            }
        }

        $filial->delete();
        return response()->json(['ok' => true]);
    }
}
