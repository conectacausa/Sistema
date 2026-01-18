<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LocalizacaoApiController extends Controller
{
    public function paises(): JsonResponse
    {
        $items = DB::table('public.paises')
            ->select(['id', 'nome'])
            ->orderBy('nome')
            ->get();

        return response()->json(['data' => $items]);
    }

    public function estadosByPais(Request $request): JsonResponse
    {
        // ✅ pega o parâmetro correto da rota
        $paisIdInt = (int) $request->route('pais_id');

        $items = DB::table('public.estados')
            ->where('pais_id', $paisIdInt)
            ->select(['id', 'nome', 'sigla', 'pais_id'])
            ->orderBy('nome')
            ->get();

        $searchPath = null;
        try {
            $searchPath = DB::selectOne("SHOW search_path")->search_path ?? null;
        } catch (\Throwable $e) {}

        return response()->json([
            'data' => $items,
            'debug' => [
                'pais_id_route' => $request->route('pais_id'),
                'pais_id_int' => $paisIdInt,
                'qtd' => $items->count(),
                'search_path' => $searchPath,
            ],
        ]);
    }

    public function cidadesByEstado(Request $request): JsonResponse
    {
        $estadoIdInt = (int) $request->route('estado_id');

        $items = DB::table('public.cidades')
            ->where('estado_id', $estadoIdInt)
            ->select(['id', 'nome', 'estado_id'])
            ->orderBy('nome')
            ->get();

        return response()->json([
            'data' => $items,
            'debug' => [
                'estado_id_route' => $request->route('estado_id'),
                'estado_id_int' => $estadoIdInt,
                'qtd' => $items->count(),
            ],
        ]);
    }
}
