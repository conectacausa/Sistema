<?php

namespace App\Http\Controllers\AVD;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PendenciasController extends Controller
{
    private function empresaId(): int
    {
        return (int) (auth()->user()->empresa_id ?? 0);
    }

    public function index(Request $request, $sub)
    {
        $empresaId = $this->empresaId();
        $userId = (int) auth()->id();

        $rows = DB::table('avd_avaliacoes as a')
            ->join('avd_ciclo_participantes as p', 'p.id', '=', 'a.participante_id')
            ->join('avd_ciclos as cy', 'cy.id', '=', 'a.ciclo_id')
            ->leftJoin('colaboradores as c', 'c.id', '=', 'p.colaborador_id')
            ->where('a.empresa_id', $empresaId)
            ->where('a.status', 'pendente')
            ->whereNull('p.deleted_at')
            ->whereNull('cy.deleted_at')
            ->whereIn('a.tipo', ['gestor']) // por enquanto
            ->where(function ($q) use ($userId) {
                $q->where('a.avaliador_usuario_id', $userId)
                  ->orWhere('p.gestor_usuario_id', $userId);
            })
            ->select(
                'a.id',
                'a.tipo',
                'a.status',
                'a.token',
                'cy.titulo',
                'c.nome as colaborador_nome'
            )
            ->orderByDesc('a.id')
            ->get();

        return view('avd.gestor.index', [
            'sub'  => $sub,
            'rows' => $rows,
        ]);
    }
}
