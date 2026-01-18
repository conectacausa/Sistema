<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Filial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class FiliaisApiController extends Controller
{
    public function index(Request $request)
    {
        $perPage = max(1, min((int) $request->integer('per_page', 50), 200));
        $q = trim((string) $request->query('q', ''));

        $paisId = $request->query('pais_id');
        $estadoId = $request->query('estado_id');
        $cidadeId = $request->query('cidade_id');

        // colunas reais (aceita pais_id ou pais)
        $colPais = Schema::hasColumn('filiais', 'pais_id') ? 'pais_id' : (Schema::hasColumn('filiais', 'pais') ? 'pais' : null);
        $colEstado = Schema::hasColumn('filiais', 'estado_id') ? 'estado_id' : (Schema::hasColumn('filiais', 'estado') ? 'estado' : null);
        $colCidade = Schema::hasColumn('filiais', 'cidade_id') ? 'cidade_id' : (Schema::hasColumn('filiais', 'cidade') ? 'cidade' : null);

        $query = Filial::query()
            ->select(['id', 'nome_fantasia', 'razao_social', 'cnpj'])
            ->orderByDesc('id');

        // tenta incluir ids se existirem
        foreach ([$colPais, $colEstado, $colCidade] as $col) {
            if ($col && !in_array($col, ['id','nome_fantasia','razao_social','cnpj'])) {
                $query->addSelect($col);
            }
        }

        // Busca (já está ok)
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

        // filtros
        if ($colPais && !empty($paisId))  $query->where($colPais, (int) $paisId);
        if ($colEstado && !empty($estadoId)) $query->where($colEstado, (int) $estadoId);
        if ($colCidade && !empty($cidadeId)) $query->where($colCidade, (int) $cidadeId);

        // relações só se os relacionamentos existirem + colunas padrão existirem
        if (Schema::hasColumn('filiais', 'pais_id') && Schema::hasColumn('filiais', 'estado_id') && Schema::hasColumn('filiais', 'cidade_id')) {
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
        $filial->delete();
        return response()->json(['ok' => true]);
    }
}
