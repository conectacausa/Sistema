<?php

namespace App\Http\Controllers\Beneficios;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class BolsaRelatoriosController extends Controller
{
    public function index(Request $request, string $sub)
    {
        $empresaId = (int)(auth()->user()->empresa_id ?? 0);

        $processos = DB::table('bolsa_estudos_processos')
            ->where('empresa_id', $empresaId)
            ->whereNull('deleted_at')
            ->orderByDesc('id')
            ->get(['id', 'ciclo']);

        // filtros
        $processoId = (int)$request->get('processo_id', 0);
        $status     = trim((string)$request->get('status', '')); // 2 pronto pagamento | 3 pago | '' ambos
        $pagoDe     = trim((string)$request->get('pago_de', ''));
        $pagoAte    = trim((string)$request->get('pago_ate', ''));

        $rows = $this->queryPagamentos($empresaId, $processoId, $status, $pagoDe, $pagoAte)
            ->orderBy('p.ciclo')
            ->orderBy('c.nome')
            ->orderBy('comp.competencia')
            ->paginate(20);

        return view('beneficios.bolsa.relatorios.index', [
            'sub'       => $sub,
            'processos' => $processos,
            'rows'      => $rows,
            'f' => [
                'processo_id' => $processoId,
                'status'      => $status,
                'pago_de'     => $pagoDe,
                'pago_ate'    => $pagoAte,
            ],
        ]);
    }

    public function exportPagamentosExcel(Request $request, string $sub)
    {
        $empresaId = (int)(auth()->user()->empresa_id ?? 0);

        $processoId = (int)$request->get('processo_id', 0);
        $status     = trim((string)$request->get('status', '')); // 2 ou 3 ou '' (ambos)
        $pagoDe     = trim((string)$request->get('pago_de', ''));
        $pagoAte    = trim((string)$request->get('pago_ate', ''));

        $data = $this->queryPagamentos($empresaId, $processoId, $status, $pagoDe, $pagoAte)
            ->orderBy('p.ciclo')
            ->orderBy('c.nome')
            ->orderBy('comp.competencia')
            ->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Pagamentos');

        $headers = [
            'Ciclo',
            'Competência',
            'Status',
            'Nome Colaborador',
            'Matrícula',
            'Filial',
            'Entidade',
            'Curso',
            'Valor Limite',
            'Valor Previsto',
            'Vencimento',
            'Pago em',
        ];

        // Cabeçalho
        $col = 1;
        foreach ($headers as $h) {
            $sheet->setCellValueByColumnAndRow($col++, 1, $h);
        }

        // Linhas
        $r = 2;
        foreach ($data as $row) {
            $sheet->setCellValueByColumnAndRow(1,  $r, (string)$row->ciclo);
            $sheet->setCellValueByColumnAndRow(2,  $r, $row->competencia ? date('m/Y', strtotime($row->competencia)) : '');
            $sheet->setCellValueByColumnAndRow(3,  $r, $this->labelStatus((int)$row->comp_status));
            $sheet->setCellValueByColumnAndRow(4,  $r, (string)($row->colaborador_nome ?? ''));
            $sheet->setCellValueByColumnAndRow(5,  $r, (string)($row->matricula ?? ''));
            $sheet->setCellValueByColumnAndRow(6,  $r, (string)($row->filial_nome ?? ''));
            $sheet->setCellValueByColumnAndRow(7,  $r, (string)($row->entidade_nome ?? ''));
            $sheet->setCellValueByColumnAndRow(8,  $r, (string)($row->curso_nome ?? ''));
            $sheet->setCellValueByColumnAndRow(9,  $r, (float)($row->valor_limite ?? 0));
            $sheet->setCellValueByColumnAndRow(10, $r, (float)($row->valor_previsto ?? 0));
            $sheet->setCellValueByColumnAndRow(11, $r, $row->vencimento ? date('d/m/Y', strtotime($row->vencimento)) : '');
            $sheet->setCellValueByColumnAndRow(12, $r, $row->pago_at ? date('d/m/Y H:i', strtotime($row->pago_at)) : '');
            $r++;
        }

        // autosize simples
        for ($i = 1; $i <= count($headers); $i++) {
            $sheet->getColumnDimensionByColumn($i)->setAutoSize(true);
        }

        $fileName = 'bolsa_pagamentos_' . date('Ymd_His') . '.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    private function queryPagamentos(int $empresaId, int $processoId, string $status, string $pagoDe, string $pagoAte)
    {
        $q = DB::table('bolsa_estudos_solicitacao_competencias as comp')
            ->where('comp.empresa_id', $empresaId)
            ->whereNull('comp.deleted_at')
            ->leftJoin('bolsa_estudos_solicitacoes as s', 's.id', '=', 'comp.solicitacao_id')
            ->whereNull('s.deleted_at')
            ->leftJoin('bolsa_estudos_processos as p', 'p.id', '=', 's.processo_id')
            ->whereNull('p.deleted_at')
            ->leftJoin('colaboradores as c', 'c.id', '=', 's.colaborador_id')
            ->leftJoin('filiais as f', 'f.id', '=', DB::raw('COALESCE(s.filial_id, c.filial_id)'))
            ->leftJoin('bolsa_estudos_cursos as cu', 'cu.id', '=', 's.curso_id')
            ->leftJoin('bolsa_estudos_entidades as e', 'e.id', '=', 'cu.entidade_id')
            ->select([
                'p.ciclo',
                'comp.competencia',
                'comp.status as comp_status',
                'c.nome as colaborador_nome',
                DB::raw("COALESCE(c.matricula, '') as matricula"),
                DB::raw("COALESCE(f.nome_fantasia, f.razao_social, '') as filial_nome"),
                'e.nome as entidade_nome',
                'cu.nome as curso_nome',
                's.valor_limite',
                'comp.valor_previsto',
                'comp.vencimento',
                'comp.pago_at',
                'comp.id as competencia_id',
                's.id as solicitacao_id',
                'p.id as processo_id',
            ]);

        if ($processoId > 0) {
            $q->where('p.id', $processoId);
        }

        if ($status !== '') {
            $q->where('comp.status', (int)$status);
        } else {
            // default: mostrar pronto pagamento (2) e pago (3)
            $q->whereIn('comp.status', [2,3]);
        }

        // Filtro por data de pagamento (pago_at) — só faz sentido se status=3 ou se quiser pegar todos pagos no período
        if ($pagoDe !== '') {
            $q->whereDate('comp.pago_at', '>=', $pagoDe);
        }
        if ($pagoAte !== '') {
            $q->whereDate('comp.pago_at', '<=', $pagoAte);
        }

        return $q;
    }

    private function labelStatus(int $st): string
    {
        return match ($st) {
            2 => 'Pronto p/ pagamento',
            3 => 'Pago',
            default => (string)$st,
        };
    }
}
