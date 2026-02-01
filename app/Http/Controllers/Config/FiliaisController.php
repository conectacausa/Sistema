<?php

namespace App\Http\Controllers\Config;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FiliaisController extends Controller
{
    public function index(Request $request, $sub)
    {
        return view('config.filiais.index', [
            'screenId' => 5,
        ]);
    }

    public function create(Request $request, $sub)
    {
        return view('config.filiais.create', [
            'screenId' => 5,
        ]);
    }

    public function edit(Request $request, $sub, $id)
    {
        // mantém simples por enquanto (você disse que create/edit será trabalhado depois)
        // aqui você pode buscar a filial e validar tenant quando for montar o form.
        return view('config.filiais.edit', [
            'screenId' => 5,
            'filialId' => (int)$id,
        ]);
    }

    /**
     * Grid JSON (blindada) - agora também remove soft-deletadas se existir deleted_at
     */
    public function grid(Request $request, $sub)
    {
        try {
            $perPage = (int)$request->query('per_page', 10);
            $perPage = max(5, min(50, $perPage));
            $q = trim((string)$request->query('q', ''));

            // Lista colunas reais da tabela filiais
            $cols = collect(DB::select("
                select column_name
                from information_schema.columns
                where table_schema = 'public'
                  and table_name = 'filiais'
            "))->pluck('column_name')->map(fn($c) => strtolower($c))->flip();

            $has = fn(string $c) => $cols->has(strtolower($c));

            $select = [
                'id',
                $has('nome_fantasia') ? 'nome_fantasia' : DB::raw("'' as nome_fantasia"),
                $has('cnpj') ? 'cnpj' : DB::raw("'' as cnpj"),
            ];

            $select[] = $has('cidade') ? DB::raw("coalesce(cidade,'') as cidade_nome") : DB::raw("'' as cidade_nome");
            $select[] = $has('uf')     ? DB::raw("coalesce(uf,'') as estado_uf")       : DB::raw("'' as estado_uf");
            $select[] = $has('pais')   ? DB::raw("coalesce(pais,'') as pais_nome")     : DB::raw("'' as pais_nome");

            $query = DB::table('filiais')->select($select);

            // ✅ não listar soft deletadas
            if ($has('deleted_at')) {
                $query->whereNull('deleted_at');
            }

            // tenant filter se houver empresa_id
            if ($has('empresa_id')) {
                $empresaId = $this->empresaId();
                if ((int)$empresaId > 0) {
                    $query->where('empresa_id', (int)$empresaId);
                }
            }

            // filtro texto
            if ($q !== '') {
                $qDigits = preg_replace('/\D+/', '', $q);

                $query->where(function ($w) use ($q, $qDigits, $has) {
                    if ($has('nome_fantasia')) {
                        $w->orWhere('nome_fantasia', 'ILIKE', "%{$q}%");
                    }

                    if ($has('cnpj')) {
                        if ($qDigits !== '') {
                            $w->orWhereRaw("regexp_replace(cnpj, '[^0-9]', '', 'g') ILIKE ?", ["%{$qDigits}%"]);
                        } else {
                            $w->orWhere('cnpj', 'ILIKE', "%{$q}%");
                        }
                    }
                });
            }

            // ordenação segura
            if ($has('nome_fantasia')) {
                $query->orderBy('nome_fantasia');
            } else {
                $query->orderBy('id', 'desc');
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
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'message' => 'Erro 500 ao carregar grid de filiais.',
                'detail'  => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Soft delete: seta deleted_at com NOW()
     */
    public function destroy(Request $request, $sub, $id)
    {
        try {
            $id = (int)$id;

            // Detecta colunas para aplicar tenant e soft delete com segurança
            $cols = collect(DB::select("
                select column_name
                from information_schema.columns
                where table_schema = 'public'
                  and table_name = 'filiais'
            "))->pluck('column_name')->map(fn($c) => strtolower($c))->flip();

            $has = fn(string $c) => $cols->has(strtolower($c));

            $query = DB::table('filiais')->where('id', $id);

            // tenant filter se houver empresa_id
            if ($has('empresa_id')) {
                $empresaId = $this->empresaId();
                if ((int)$empresaId > 0) {
                    $query->where('empresa_id', (int)$empresaId);
                }
            }

            // se tiver deleted_at: soft delete
            if ($has('deleted_at')) {
                $update = ['deleted_at' => now()];

                // se existir updated_at, atualiza também
                if ($has('updated_at')) {
                    $update['updated_at'] = now();
                }

                $affected = $query->update($update);

                if ($affected < 1) {
                    return response()->json(['message' => 'Registro não encontrado ou sem permissão.'], 404);
                }

                return response()->json(['ok' => true]);
            }

            // fallback: se não existir coluna deleted_at, deleta de verdade (último recurso)
            $affected = $query->delete();

            if ($affected < 1) {
                return response()->json(['message' => 'Registro não encontrado ou sem permissão.'], 404);
            }

            return response()->json(['ok' => true]);

        } catch (\Throwable $e) {
            report($e);
            return response()->json([
                'message' => 'Erro ao excluir filial.',
                'detail'  => $e->getMessage(),
            ], 500);
        }
    }

    /** Combos */
    public function paises(Request $request, $sub)
    {
        if (!Schema::hasTable('paises')) {
            return response()->json(['data' => []]);
        }

        $rows = DB::table('paises')
            ->select('id', 'nome')
            ->orderBy('nome')
            ->get();

        return response()->json(['data' => $rows]);
    }

    public function estados(Request $request, $sub)
    {
        if (!Schema::hasTable('estados')) {
            return response()->json(['data' => []]);
        }

        $paisId = (int)$request->query('pais_id', 0);

        $rows = DB::table('estados')
            ->select('id', 'nome', 'uf', 'pais_id')
            ->when($paisId > 0, fn($q) => $q->where('pais_id', $paisId))
            ->orderBy('nome')
            ->get();

        return response()->json(['data' => $rows]);
    }

    public function cidades(Request $request, $sub)
    {
        if (!Schema::hasTable('cidades')) {
            return response()->json(['data' => []]);
        }

        $estadoId = (int)$request->query('estado_id', 0);

        $rows = DB::table('cidades')
            ->select('id', 'nome', 'estado_id')
            ->when($estadoId > 0, fn($q) => $q->where('estado_id', $estadoId))
            ->orderBy('nome')
            ->get();

        return response()->json(['data' => $rows]);
    }

    private function empresaId(): int
    {
        $tenant = app()->bound('tenant') ? app('tenant') : null;

        if (!$tenant && auth()->check()) {
            return (int)(auth()->user()->empresa_id ?? 0);
        }

        return (int)($tenant->id ?? 0);
    }
}
