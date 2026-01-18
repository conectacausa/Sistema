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

    public function estadosByPais(Request $request, int $paisId): JsonResponse
    {
        $items = DB::table('estados')
            ->where('pais_id', $paisId)
            ->select(['id', 'nome', 'sigla', 'pais_id'])
            ->orderBy('nome')
            ->get();

        return response()->json(['data' => $items]);
    }

    public function cidadesByEstado(Request $request, int $estadoId): JsonResponse
    {
        $items = DB::table('cidades')
            ->where('estado_id', $estadoId)
            ->select(['id', 'nome', 'estado_id'])
            ->orderBy('nome')
            ->get();

        return response()->json(['data' => $items]);
    }
}
