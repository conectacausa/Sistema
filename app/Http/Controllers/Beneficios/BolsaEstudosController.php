<?php

namespace App\Http\Controllers\Beneficios;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class BolsaEstudosController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | INDEX (lista de ciclos/processos)
    |--------------------------------------------------------------------------
    */
    public function index(Request $request, string $sub)
    {
        $empresaId = (int) (auth()->user()->empresa_id ?? 0);

        $q = trim((string) $request->get('q', ''));

        // Base do grid
        $grid = DB::table('bolsa_estudos_processos as p')
            ->where('p.empresa_id', $empresaId)
            ->whereNull('p.deleted_at')
            ->leftJoin('bolsa_estudos_solicitacoes as s', function ($join) {
                $join->on('s.processo_id', '=', 'p.id')
                     ->whereNull('s.deleted_at');
            })
            ->select([
                'p.id',
                'p.ciclo',
                'p.edital',
                'p.status',
                'p.orcamento_mensal',
                'p.meses_duracao',
                'p.inscricoes_inicio_at',
                'p.inscricoes_fim_at',
                'p.created_at',
                DB::raw("COALESCE(COUNT(s.id) FILTER (WHERE s.status = 2), 0) as contemplados_count"),
                DB::raw("COALESCE(COUNT(s.id) FILTER (WHERE s.status = 3), 0) as pendentes_count"),
            ])
            ->groupBy('p.id');

        if ($q !== '') {
            $grid->where(function ($w) use ($q) {
                $w->where('p.ciclo', 'ILIKE', "%{$q}%")
                  ->orWhere('p.edital', 'ILIKE', "%{$q}%");
            });
        }

        $processos = $grid
            ->orderByDesc('p.id')
            ->paginate(10);

        // Se for request AJAX do filtro (tecla digitando), devolve só a tabela
        if ($request->ajax()) {
            return view('beneficios.bolsa.partials._table', [
                'sub' => $sub,
                'processos' => $processos,
            ]);
        }

        return view('beneficios.bolsa.index', [
            'sub' => $sub,
            'processos' => $processos,
            'q' => $q,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | EDIT
    |--------------------------------------------------------------------------
    */
    public function edit(Request $request, string $sub, int $id)
    {
        $empresaId = (int) (auth()->user()->empresa_id ?? 0);

        $processo = DB::table('bolsa_estudos_processos')
            ->where('empresa_id', $empresaId)
            ->where('id', $id)
            ->whereNull('deleted_at')
            ->first();

        if (!$processo) {
            return redirect()->route('beneficios.bolsa.index', ['sub' => $sub])
                ->with('error', 'Processo não encontrado.');
        }

        // Documentos do processo (Tab Documentos)
        $docQ      = trim((string)$request->get('doc_q', ''));
        $docStatus = trim((string)$request->get('doc_status', ''));

        $documentosQuery = DB::table('bolsa_estudos_documentos as d')
            ->where('d.empresa_id', $empresaId)
            ->where('d.processo_id', $id)
            ->whereNull('d.deleted_at')
            ->leftJoin('bolsa_estudos_solicitacoes as s', 's.id', '=', 'd.solicitacao_id')
            ->leftJoin('colaboradores as c', 'c.id', '=', 's.colaborador_id')
            ->select([
                'd.id',
                'd.tipo',
                'd.titulo',
                'd.status',
                'd.created_at',
                'd.expira_em',
                'd.arquivo_path',
                'c.nome as colaborador_nome',
            ]);

        if ($docQ !== '') {
            $documentosQuery->where(function ($w) use ($docQ) {
                $w->where('d.titulo', 'ILIKE', "%{$docQ}%")
                  ->orWhere('c.nome', 'ILIKE', "%{$docQ}%");
            });
        }
        if ($docStatus !== '' && is_numeric($docStatus)) {
            $documentosQuery->where('d.status', (int)$docStatus);
        }

        $documentos = $documentosQuery
            ->orderByDesc('d.id')
            ->paginate(10, ['*'], 'docs_page');

        return view('beneficios.bolsa.edit', [
            'sub'        => $sub,
            'processo'   => $processo,
            'documentos' => $documentos,
            'docQ'       => $docQ,
            'docStatus'  => $docStatus,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE (não deleta)
    |--------------------------------------------------------------------------
    */
    public function update(Request $request, string $sub, int $id)
    {
        $empresaId = (int) (auth()->user()->empresa_id ?? 0);

        $processo = DB::table('bolsa_estudos_processos')
            ->where('empresa_id', $empresaId)
            ->where('id', $id)
            ->whereNull('deleted_at')
            ->first();

        if (!$processo) {
            return redirect()->route('beneficios.bolsa.index', ['sub' => $sub])
                ->with('error', 'Processo não encontrado.');
        }

        $data = $request->validate([
            'ciclo'               => ['required', 'string', 'max:160'],
            'edital'              => ['nullable', 'string'],
            'inscricoes_inicio_at'=> ['nullable', 'date'],
            'inscricoes_fim_at'   => ['nullable', 'date'],
            'status'              => ['required', 'integer', 'in:0,1,2'],
            'data_base'           => ['nullable', 'date'],

            'valor_mensal'        => ['nullable', 'string'],
            'meses_duracao'       => ['nullable', 'integer', 'min:0', 'max:120'],

            'lembrete_recibo_ativo'      => ['nullable', 'in:0,1'],
            'lembrete_recibo_dias_antes' => ['nullable', 'integer', 'min:0', 'max:365'],
        ]);

        $valorMensal  = $this->toDecimal($data['valor_mensal'] ?? null);
        $mesesDuracao = (int)($data['meses_duracao'] ?? 0);

        DB::table('bolsa_estudos_processos')
            ->where('empresa_id', $empresaId)
            ->where('id', $id)
            ->whereNull('deleted_at')
            ->update([
                'ciclo'               => $data['ciclo'],
                'edital'              => $data['edital'] ?? null,
                'inscricoes_inicio_at'=> $data['inscricoes_inicio_at'] ?? null,
                'inscricoes_fim_at'   => $data['inscricoes_fim_at'] ?? null,
                'status'              => (int)$data['status'],
                'data_base'           => $data['data_base'] ?? null,

                'orcamento_mensal'    => $valorMensal,
                'meses_duracao'       => $mesesDuracao,

                'lembrete_recibo_ativo'      => (int)($data['lembrete_recibo_ativo'] ?? 0) === 1,
                'lembrete_recibo_dias_antes' => $data['lembrete_recibo_dias_antes'] ?? null,

                'updated_at'          => now(),
            ]);

        return redirect()
            ->route('beneficios.bolsa.edit', ['sub' => $sub, 'id' => $id])
            ->with('success', 'Processo atualizado com sucesso.');
    }

    /*
    |--------------------------------------------------------------------------
    | AJAX: COLABORADOR POR MATRÍCULA (corrige empresa_id inexistente)
    |--------------------------------------------------------------------------
    */
    public function colaboradorPorMatricula(Request $request, string $sub)
    {
        $empresaId = (int) (auth()->user()->empresa_id ?? 0);
        $matricula = trim((string)$request->get('matricula', ''));

        if ($matricula === '') {
            return response()->json(['ok' => false, 'message' => 'Matrícula inválida.'], 422);
        }

        $q = DB::table('colaboradores as c')
            ->select([
                'c.id',
                'c.nome',
                DB::raw($this->hasColumn('colaboradores', 'matricula') ? 'c.matricula' : "NULL as matricula"),
                DB::raw($this->hasColumn('colaboradores', 'filial_id') ? 'c.filial_id' : "NULL as filial_id"),
            ])
            ->whereNull('c.deleted_at');

        if ($this->hasColumn('colaboradores', 'empresa_id')) {
            $q->where('c.empresa_id', $empresaId)
              ->where('c.matricula', $matricula);
        } else {
            if ($this->hasColumn('colaboradores', 'filial_id')) {
                $q->leftJoin('filiais as f', 'f.id', '=', 'c.filial_id')
                  ->where('f.empresa_id', $empresaId);

                if ($this->hasColumn('colaboradores', 'matricula')) {
                    $q->where('c.matricula', $matricula);
                } else {
                    $q->where('c.id', (int)$matricula);
                }

                $q->addSelect(DB::raw("COALESCE(f.nome_fantasia, f.razao_social) as filial_nome"));
            } else {
                return response()->json([
                    'ok' => false,
                    'message' => 'Tabela colaboradores não possui filial_id nem empresa_id para filtrar.',
                ], 500);
            }
        }

        $col = $q->first();

        if (!$col) {
            return response()->json(['ok' => false, 'message' => 'Colaborador não encontrado.'], 404);
        }

        if (!property_exists($col, 'filial_nome')) {
            $filialNome = null;
            if (!empty($col->filial_id)) {
                $f = DB::table('filiais')
                    ->where('empresa_id', $empresaId)
                    ->where('id', (int)$col->filial_id)
                    ->first(['nome_fantasia', 'razao_social']);
                $filialNome = $f ? ($f->nome_fantasia ?: $f->razao_social) : null;
            }
            $col->filial_nome = $filialNome;
        }

        return response()->json([
            'ok' => true,
            'data' => [
                'id'          => (int)$col->id,
                'nome'        => (string)$col->nome,
                'filial_id'   => !empty($col->filial_id) ? (int)$col->filial_id : null,
                'filial_nome' => (string)($col->filial_nome ?? ''),
            ]
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */
    private function hasColumn(string $table, string $column): bool
    {
        try {
            return Schema::hasTable($table) && Schema::hasColumn($table, $column);
        } catch (\Throwable $e) {
            return false;
        }
    }

    private function toDecimal(?string $value): float
    {
        $v = trim((string)$value);
        if ($v === '') return 0.0;

        $v = str_replace(['R$', ' '], '', $v);
        $v = str_replace('.', '', $v);
        $v = str_replace(',', '.', $v);

        $n = (float)$v;
        return $n < 0 ? 0.0 : $n;
    }
}
