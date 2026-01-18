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
        if (app()->bound('tenant')) {
            $tenant = app('tenant');
            if (is_object($tenant)) {
                if (isset($tenant->id)) return (int) $tenant->id;
                if (isset($tenant->empresa_id)) return (int) $tenant->empresa_id;
                if (method_exists($tenant, 'getKey')) return (int) $tenant->getKey();
            }
        }

        if (function_exists('tenant')) {
            try {
                $t = tenant();
                if (is_object($t)) {
                    if (isset($t->id)) return (int) $t->id;
                    if (method_exists($t, 'getKey')) return (int) $t->getKey();
                }
            } catch (\Throwable $e) {}
        }

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

        // Colunas de empresa
        $colEmpresa = Schema::hasColumn('filiais', 'empresa_id')
            ? 'empresa_id'
            : (Schema::hasColumn('filiais', 'empresa') ? 'empresa' : null);

        // Colunas padrão de localização (preferimos *_id)
        $hasPaisId = Schema::hasColumn('filiais', 'pais_id');
        $hasEstadoId = Schema::hasColumn('filiais', 'estado_id');
        $hasCidadeId = Schema::hasColumn('filiais', 'cidade_id');

        // Se não existirem *_id, tentamos alternativas (pais/estado/cidade)
        $colPaisAlt   = (!$hasPaisId && Schema::hasColumn('filiais', 'pais'))   ? 'pais'   : null;
        $colEstadoAlt = (!$hasEstadoId && Schema::hasColumn('filiais', 'estado')) ? 'estado' : null;
        $colCidadeAlt = (!$hasCidadeId && Schema::hasColumn('filiais', 'cidade')) ? 'cidade' : null;

        $empresaId = $this->tenantEmpresaId($request);

        // ✅ Inclui FKs no select quando existirem (necessário para with() funcionar)
        $select = ['id', 'nome_fantasia', 'razao_social', 'cnpj'];

        if ($hasPaisId)   $select[] = 'pais_id';
        if ($hasEstadoId) $select[] = 'estado_id';
        if ($hasCidadeId) $select[] = 'cidade_id';

        if (!$hasPaisId && $colPaisAlt)   $select[] = $colPaisAlt;
        if (!$hasEstadoId && $colEstadoAlt) $select[] = $colEstadoAlt;
        if (!$hasCidadeId && $colCidadeAlt) $select[] = $colCidadeAlt;

        if ($colEmpresa) $select[] = $colEmpresa;

        $query = Filial::query()
            ->select(array_values(array_unique($select)))
            ->orderByDesc('id');

        // ✅ Filtrar pela empresa do tenant
        if ($colEmpresa && $empresaId) {
            $query->where($colEmpresa, $empresaId);
        }

        // ✅ Busca por Razão/Nome Fantasia/CNPJ
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

        // ✅ Filtros por localização
        if ($paisId !== null && $paisId !== '') {
            if ($hasPaisId) $query->where('pais_id', (int) $paisId);
            elseif ($colPaisAlt) $query->where($colPaisAlt, (int) $paisId);
        }

        if ($estadoId !== null && $estadoId !== '') {
            if ($hasEstadoId) $query->where('estado_id', (int) $estadoId);
            elseif ($colEstadoAlt) $query->where($colEstadoAlt, (int) $estadoId);
        }

        if ($cidadeId !== null && $cidadeId !== '') {
            if ($hasCidadeId) $query->where('cidade_id', (int) $cidadeId);
            elseif ($colCidadeAlt) $query->where($colCidadeAlt, (int) $cidadeId);
        }

        // ✅ Só faz with() se existirem os *_id (padrão das relações)
        if ($hasPaisId && $hasEstadoId && $hasCidadeId) {
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
