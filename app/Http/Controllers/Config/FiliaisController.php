<?php

namespace App\Http\Controllers\Config;

use App\Http\Controllers\Controller;
use App\Models\Filial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FiliaisController extends Controller
{
    public function index()
    {
        return view('config.filiais.index', [
            'screenId' => 5,
        ]);
    }

    public function create()
    {
        return view('config.filiais.create', [
            'screenId' => 5,
        ]);
    }

    public function edit(Filial $filial)
    {
        $empresaId = $this->empresaId();

        // garante tenant
        if ((int)($filial->empresa_id ?? 0) !== (int)$empresaId) {
            abort(404);
        }

        return view('config.filiais.edit', [
            'screenId' => 5,
            'filial'   => $filial,
        ]);
    }

    /**
     * JSON da grid com filtros + paginação
     */
    public function grid(Request $request)
    {
        $empresaId = $this->empresaId();

        $q        = trim((string)$request->query('q', ''));
        $paisId   = $request->query('pais_id');
        $estadoId = $request->query('estado_id');
        $cidadeId = $request->query('cidade_id');

        $perPage  = (int)$request->query('per_page', 10);
        $perPage  = max(5, min(50, $perPage));

        // IMPORTANTe: para evitar erro de coluna desconhecida, eu fiz o join via cidade->estado->pais
        // assumindo que filiais possui cidade_id (padrão parecido com empresas no documento).
        $query = Filial::query()
            ->where('empresa_id', $empresaId)
            ->leftJoin('cidades', 'cidades.id', '=', 'filiais.cidade_id')
            ->leftJoin('estados', 'estados.id', '=', 'cidades.estado_id')
            ->leftJoin('paises', 'paises.id', '=', 'estados.pais_id')
            ->select([
                'filiais.id',
                'filiais.nome_fantasia',
                'filiais.cnpj',
                DB::raw("COALESCE(cidades.nome, '') as cidade_nome"),
                DB::raw("COALESCE(estados.uf, '') as estado_uf"),
                DB::raw("COALESCE(paises.nome, '') as pais_nome"),
            ])
            ->orderBy('filiais.nome_fantasia');

        if ($q !== '') {
            // busca por nome_fantasia ou cnpj (normaliza removendo pontuação)
            $qDigits = preg_replace('/\D+/', '', $q);

            $query->where(function ($w) use ($q, $qDigits) {
                $w->where('filiais.nome_fantasia', 'ILIKE', "%{$q}%");

                if ($qDigits !== '') {
                    $w->orWhereRaw("regexp_replace(filiais.cnpj, '[^0-9]', '', 'g') ILIKE ?", ["%{$qDigits}%"]);
                } else {
                    $w->orWhere('filiais.cnpj', 'ILIKE', "%{$q}%");
                }
            });
        }

        if (!empty($cidadeId)) {
            $query->where('filiais.cidade_id', (int)$cidadeId);
        }

        if (!empty($estadoId)) {
            $query->where('estados.id', (int)$estadoId);
        }

        if (!empty($paisId)) {
            $query->where('paises.id', (int)$paisId);
        }

        $page = $query->paginate($perPage)->appends($request->query());

        return response()->json([
            'data' => $page->items(),
            'meta' => [
                'current_page' => $page->currentPage(),
                'last_page'    => $page->lastPage(),
                'per_page'     => $page->perPage(),
                'total'        => $page->total(),
                'from'         => $page->firstItem(),
                'to'           => $page->lastItem(),
            ],
        ]);
    }

    public function destroy(Filial $filial)
    {
        $empresaId = $this->empresaId();

        if ((int)($filial->empresa_id ?? 0) !== (int)$empresaId) {
            abort(404);
        }

        $filial->delete();

        return response()->json([
            'ok' => true,
        ]);
    }

    /** Combos */

    public function paises()
    {
        $rows = DB::table('paises')
            ->select('id', 'nome')
            ->whereNull('deleted_at')
            ->orderBy('nome')
            ->get();

        return response()->json(['data' => $rows]);
    }

    public function estados(Request $request)
    {
        $paisId = (int)$request->query('pais_id', 0);

        $rows = DB::table('estados')
            ->select('id', 'nome', 'uf', 'pais_id')
            ->whereNull('deleted_at')
            ->when($paisId > 0, fn($q) => $q->where('pais_id', $paisId))
            ->orderBy('nome')
            ->get();

        return response()->json(['data' => $rows]);
    }

    public function cidades(Request $request)
    {
        $estadoId = (int)$request->query('estado_id', 0);

        $rows = DB::table('cidades')
            ->select('id', 'nome', 'estado_id')
            ->whereNull('deleted_at')
            ->when($estadoId > 0, fn($q) => $q->where('estado_id', $estadoId))
            ->orderBy('nome')
            ->get();

        return response()->json(['data' => $rows]);
    }

    private function empresaId(): int
    {
        // padrão do projeto: tenant carregado por middleware (subdomínio)
        $tenant = app()->bound('tenant') ? app('tenant') : null;

        // fallback: usuário logado
        if (!$tenant && auth()->check()) {
            return (int) (auth()->user()->empresa_id ?? 0);
        }

        return (int)($tenant->id ?? 0);
    }
}
