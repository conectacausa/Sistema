<?php

namespace App\Http\Controllers\AVD;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ResultadosController extends Controller
{
    private function empresaId(): int
    {
        return (int) (auth()->user()->empresa_id ?? 0);
    }

    public function index(Request $request, $sub)
    {
        $empresaId = $this->empresaId();
        $cicloId = (int) $request->get('ciclo_id', 0);

        $ciclos = DB::table('avd_ciclos')
            ->where('empresa_id', $empresaId)
            ->whereNull('deleted_at')
            ->orderByDesc('id')
            ->select('id', 'titulo', 'status')
            ->get();

        $rows = collect();

        if ($cicloId > 0) {
            $rows = DB::table('avd_ciclo_participantes as p')
                ->leftJoin('colaboradores as c', 'c.id', '=', 'p.colaborador_id')
                ->leftJoin('filiais as f', 'f.id', '=', 'p.filial_id')
                ->where('p.empresa_id', $empresaId)
                ->where('p.ciclo_id', $cicloId)
                ->whereNull('p.deleted_at')
                ->select(
                    'p.id',
                    'c.nome as colaborador',
                    'f.nome_fantasia as filial',
                    'p.nota_auto',
                    'p.nota_gestor',
                    'p.nota_pares',
                    'p.nota_final',
                    'p.status',
                    'p.divergente',
                    'p.consenso_necessario'
                )
                ->orderBy('c.nome')
                ->get();
        }

        return view('avd.resultados.index', [
            'sub'     => $sub,
            'ciclos'  => $ciclos,
            'cicloId' => $cicloId,
            'rows'    => $rows,
        ]);
    }
}
