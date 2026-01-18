<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LocalizacaoApiController extends Controller
{
    private function central()
    {
        return DB::connection(env('DB_CENTRAL_CONNECTION', config('database.default')));
    }

    public function paises(): JsonResponse
    {
        $items = $this->central()->table('paises')
            ->select(['id', 'nome'])
            ->orderBy('nome')
            ->get();

        return response()->json(['data' => $items]);
    }

    public function estadosByPais(Request $request, string $paisId): JsonResponse
    {
        $paisIdInt = (int) $paisId;

        $items = $this->central()->table('estados')
            ->where('pais_id', $paisIdInt)
            ->select(['id', 'nome', 'sigla', 'pais_id'])
            ->orderBy('nome')
            ->get();

        return response()->json(['data' => $items]);
    }

    public function cidadesByEstado(Request $request, string $estadoId): JsonResponse
    {
        $estadoIdInt = (int) $estadoId;

        $items = $this->central()->table('cidades')
            ->where('estado_id', $estadoIdInt)
            ->select(['id', 'nome', 'estado_id'])
            ->orderBy('nome')
            ->get();

        return response()->json(['data' => $items]);
    }
}
