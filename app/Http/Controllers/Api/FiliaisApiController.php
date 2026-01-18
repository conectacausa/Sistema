<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Filial;
use Illuminate\Http\Request;

class FiliaisApiController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->integer('per_page', 50);
        $perPage = max(1, min($perPage, 200)); // trava pra evitar abuso
        $q = trim((string) $request->query('q', ''));
        $paisId = $request->query('pais_id');
        $estadoId = $request->query('estado_id');
        $cidadeId = $request->query('cidade_id');

        $query = Filial::query()
            ->whereNull('deleted_at')
            ->with([
                'cidade:id,nome,estado_id',
                'estado:id,nome,sigla,pais_id',
                'pais:id,nome',
            ])
            ->select([
                'id',
                'nome_fantasia',
                'razao_social',
                'cnpj',
                'cidade_id',
                'estado_id',
                'pais_id',
            ])
            ->orderBy('id', 'desc');

        // Filtro Razão OU CNPJ (9)
        if ($q !== '') {
            $qDigits = preg_replace('/\D+/', '', $q);

            $query->where(function ($w) use ($q, $qDigits) {
                $w->where('razao_social', 'ilike', '%' . $q . '%')
                  ->orWhere('nome_fantasia', 'ilike', '%' . $q . '%');

                // Se tiver dígito, compara com CNPJ "limpo"
                if ($qDigits !== '') {
                    $w->orWhere('cnpj', 'like', '%' . $qDigits . '%');
                } else {
                    // fallback (caso armazenem com máscara - não recomendado)
                    $w->orWhere('cnpj', 'ilike', '%' . $q . '%');
                }
            });
        }

        if (!empty($paisId)) {
            $query->where('pais_id', (int) $paisId);
        }
        if (!empty($estadoId)) {
            $query->where('estado_id', (int) $estadoId);
        }
        if (!empty($cidadeId)) {
            $query->where('cidade_id', (int) $cidadeId);
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
        // (8) Soft delete: preencher deleted_at
        $filial->delete();

        return response()->json([
            'ok' => true,
        ]);
    }
}
