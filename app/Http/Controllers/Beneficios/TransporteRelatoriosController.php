<?php

namespace App\Http\Controllers\Beneficios;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\Beneficios\Transporte\TransporteVinculo;
use App\Models\Beneficios\Transporte\TransporteCartaoUso;

class TransporteRelatoriosController extends TransporteBaseController
{
    private int $TELA_ID = 27;

    public function recarga(Request $request, string $sub)
    {
        if ($r = $this->requireTela($request, $sub, $this->TELA_ID)) return $r;

        $empresaId = $this->empresaId();

        $v = Validator::make($request->all(), [
            'dt_ini' => 'nullable|date',
            'dt_fim' => 'nullable|date',
        ]);

        $dtIni = $request->get('dt_ini');
        $dtFim = $request->get('dt_fim');

        $resultado = null;

        if ($dtIni && $dtFim && !$v->fails()) {
            // Total de usos por cartão no período (se tiver valor no log)
            $totais = TransporteCartaoUso::query()
                ->where('empresa_id', $empresaId)
                ->whereBetween('data_hora_uso', [$dtIni.' 00:00:00', $dtFim.' 23:59:59'])
                ->selectRaw('numero_cartao, COUNT(*) as qtd, COALESCE(SUM(valor),0) as total')
                ->groupBy('numero_cartao')
                ->orderBy('numero_cartao')
                ->get();

            $resultado = $totais;
        }

        return view('beneficios.transporte.relatorios.recarga', compact('sub','dtIni','dtFim','resultado'));
    }

    public function exportacaoFolha(Request $request, string $sub)
    {
        if ($r = $this->requireTela($request, $sub, $this->TELA_ID)) return $r;

        // Nesta etapa eu deixo a base pronta. A exportação em XLSX/CSV nós fazemos na etapa de Views/Relatório (L),
        // porque depende dos nomes exatos: matricula/cpf/salario do seu schema de usuarios.
        $dtIni = $request->get('dt_ini');
        $dtFim = $request->get('dt_fim');

        return view('beneficios.transporte.relatorios.exportacao_folha', compact('sub','dtIni','dtFim'));
    }
}
