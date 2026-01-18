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
        $items = DB::table('paises')
            ->select(['id', 'nome'])
            ->orderBy('nome')
            ->get();

        return response()->json(['data' => $items]);
    }

    // 👇 aceita string e converte (evita TypeError)
    public function estadosByPais(Request $request, string $paisId): JsonResponse
    {
        $paisIdInt = (int) $paisId;

        $items = DB::table('estados')
            ->where('pais_id', $paisIdInt)
            ->select(['id', 'nome', 'sigla', 'pais_id'])
            ->orderBy('nome')
            ->get();

        return response()->json(['data' => $items]);
    }

    // 👇 mesma ideia para cidades
    public function cidadesByEstado(Request $request, string $estadoId): JsonResponse
    {
        $estadoIdInt = (int) $estadoId;

        $items = DB::table('cidades')
            ->where('estado_id', $estadoIdInt)
            ->select(['id', 'nome', 'estado_id'])
            ->orderBy('nome')
            ->get();

        return response()->json(['data' => $items]);
    }
}
