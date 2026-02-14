<?php

namespace App\Http\Controllers\Beneficios;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class TransporteRelatoriosController extends Controller
{
    private const T_USOS   = 'transporte_cartoes_usos';
    private const T_SALDOS = 'transporte_cartoes_saldos';
    private const T_VINCULOS = 'transporte_vinculos';
    private const T_COLABS = 'colaboradores';

    private function empresaId(): int
    {
        return (int) (auth()->user()->empresa_id ?? 0);
    }

    public function recarga(Request $request, string $sub)
    {
        $empresaId = $this->empresaId();

        $dataIni = $request->get('data_inicio');
        $dataFim = $request->get('data_fim');

        $totalUsos = 0;
        $totalEstimado = 0.0;

        if ($dataIni && $dataFim) {
            // total de usos no período (se você registrar usos)
            $totalUsos = (int) DB::table(self::T_USOS)
                ->where('empresa_id', $empresaId)
                ->whereDate('data_hora', '>=', $dataIni)
                ->whereDate('data_hora', '<=', $dataFim)
                ->count();

            // estimativa de recarga (soma valor_passagem por uso, se você grava valor no uso)
            $totalEstimado = (float) DB::table(self::T_USOS)
                ->where('empresa_id', $empresaId)
                ->whereDate('data_hora', '>=', $dataIni)
                ->whereDate('data_hora', '<=', $dataFim)
                ->sum('valor');
        }

        return view('beneficios.transporte.relatorios.recarga', compact('sub', 'dataIni', 'dataFim', 'totalUsos', 'totalEstimado'));
    }

    public function exportacaoFolha(Request $request, string $sub)
    {
        $empresaId = $this->empresaId();

        $dataIni = $request->get('data_inicio');
        $dataFim = $request->get('data_fim');

        // Se não pediu export, apenas mostra a tela
        if (!$request->get('export')) {
            return view('beneficios.transporte.relatorios.exportacao_folha', compact('sub', 'dataIni', 'dataFim'));
        }

        $v = Validator::make($request->all(), [
            'data_inicio' => 'required|date',
            'data_fim'    => 'required|date',
        ]);

        if ($v->fails()) {
            return back()->withErrors($v)->withInput();
        }

        // Exemplo: calcula "valor carregado" baseado na soma de usos por colaborador (se o uso guarda colaborador_id)
        $rows = DB::table('transporte_cartoes_usos as u')
            ->leftJoin(self::T_COLABS . ' as c', 'c.id', '=', 'u.colaborador_id')
            ->select(
                'c.matricula',
                'c.cpf',
                DB::raw('COALESCE(SUM(u.valor),0) as valor_carregado'),
                DB::raw('COALESCE(c.salario,0) as salario')
            )
            ->where('u.empresa_id', $empresaId)
            ->whereDate('u.data_hora', '>=', $dataIni)
            ->whereDate('u.data_hora', '<=', $dataFim)
            ->groupBy('c.matricula', 'c.cpf', 'c.salario')
            ->orderBy('c.matricula')
            ->get();

        $csv = "matricula;cpf;valor_carregado;desconto_6porcento\n";
        foreach ($rows as $r) {
            $valor = (float) $r->valor_carregado;
            $salario = (float) ($r->salario ?? 0);
            $desconto = round($salario * 0.06, 2);

            $csv .= sprintf(
                "%s;%s;%.2f;%.2f\n",
                (string) ($r->matricula ?? ''),
                (string) ($r->cpf ?? ''),
                $valor,
                $desconto
            );
        }

        $filename = "exportacao_folha_transporte_{$dataIni}_{$dataFim}.csv";

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }
}
