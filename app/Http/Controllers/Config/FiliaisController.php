<?php

namespace App\Http\Controllers\Config;

use App\Http\Controllers\Controller;
use App\Models\Filial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

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

        if (isset($filial->empresa_id) && (int)$filial->empresa_id !== (int)$empresaId) {
            abort(404);
        }

        return view('config.filiais.edit', [
            'screenId' => 5,
            'filial'   => $filial,
        ]);
    }

    public function grid(Request $request)
    {
        try {
            $perPage = (int)$request->query('per_page', 10);
            $perPage = max(5, min(50, $perPage));
            $q = trim((string)$request->query('q', ''));

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

            if ($has('empresa_id')) {
                $empresaId = $this->empresaId();
                if ((int)$empresaId > 0) {
                    $query->where('empresa_id', (int)$empresaId);
                }
            }

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

    public function destroy(Filial $filial)
    {
        $empresaId = $this->empresaId();

        if (isset($filial->empresa_id) && (int)$filial->empresa_id !== (int)$empresaId) {
            abort(404);
        }

        $filial->delete();

        return response()->json(['ok' => true]);
    }

    public function paises()
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

    public function estados(Request $request)
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

    public function cidades(Request $request)
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
