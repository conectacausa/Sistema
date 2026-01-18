<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pais;
use App\Models\Estado;
use App\Models\Cidade;

class LocalizacaoApiController extends Controller
{
    public function paises()
    {
        $items = Pais::query()
            ->select(['id', 'nome'])
            ->orderBy('nome')
            ->get();

        return response()->json(['data' => $items]);
    }

    public function estadosByPais(Pais $pais)
    {
        $items = Estado::query()
            ->where('pais_id', $pais->id)
            ->select(['id', 'nome', 'sigla', 'pais_id'])
            ->orderBy('nome')
            ->get();

        return response()->json(['data' => $items]);
    }

    public function cidadesByEstado(Estado $estado)
    {
        $items = Cidade::query()
            ->where('estado_id', $estado->id)
            ->select(['id', 'nome', 'estado_id'])
            ->orderBy('nome')
            ->get();

        return response()->json(['data' => $items]);
    }
}
