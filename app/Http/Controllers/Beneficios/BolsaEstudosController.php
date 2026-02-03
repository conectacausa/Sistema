<?php

namespace App\Http\Controllers\Beneficios;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class BolsaEstudosController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | INDEX
    |--------------------------------------------------------------------------
    */
    public function index(Request $request, string $sub)
    {
        $empresaId = (int) (auth()->user()->empresa_id ?? 0);
        $q = trim((string) $request->get('q', ''));

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
                'p.status',
                'p.orcamento_mensal',
                'p.meses_duracao',
                DB::raw("COALESCE(COUNT(s.id) FILTER (WHERE s.status = 2), 0) as contemplados_count"),
                DB::raw("COALESCE(COUNT(s.id) FILTER (WHERE s.status = 3), 0) as pendentes_count"),
            ])
            ->groupBy('p.id');

        if ($q !== '') {
            $grid->where('p.ciclo', 'ILIKE', "%{$q}%");
        }

        $processos = $grid->orderByDesc('p.id')->paginate(10);

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

        // ✅ Unidades vinculadas
        $unidades = DB::table('bolsa_estudos_processo_filiais as pf')
            ->where('pf.processo_id', $id)
            ->whereNull('pf.deleted_at')
            ->join('filiais as f', 'f.id', '=', 'pf.filial_id')
            ->where('f.empresa_id', $empresaId)
            ->leftJoin('bolsa_estudos_solicitacoes as s', function ($join) use ($id) {
                $join->on('s.processo_id', '=', 'pf.processo_id')
                     ->whereNull('s.deleted_at');
            })
            ->leftJoin('colaboradores as c', 'c.id', '=', 's.colaborador_id')
            // filial do solicitante: tenta s.filial_id se existir, senão c.filial_id
            ->select([
                'pf.id as vinculo_id',
                'pf.filial_id',
                DB::raw("COALESCE(f.nome_fantasia, f.razao_social) as filial_nome"),
                DB::raw("COALESCE(COUNT(s.id) FILTER (WHERE " . $this->sqlFilialMatch('pf', 's', 'c') . "), 0) as inscritos_count"),
                DB::raw("COALESCE(COUNT(s.id) FILTER (WHERE s.status = 2 AND " . $this->sqlFilialMatch('pf', 's', 'c') . "), 0) as aprovados_count"),
                DB::raw("COALESCE(SUM(s.valor_limite) FILTER (WHERE s.status = 2 AND " . $this->sqlFilialMatch('pf', 's', 'c') . "), 0) as soma_limite_aprovados"),
            ])
            ->groupBy('pf.id', 'pf.filial_id', 'f.nome_fantasia', 'f.razao_social')
            ->orderBy('pf.filial_id', 'asc')
            ->get();

        // filiais disponíveis (para modal adicionar unidade)
        $filiaisVinculadasIds = $unidades->pluck('filial_id')->map(fn($x) => (int)$x)->all();
        $filiaisDisponiveis = DB::table('filiais')
            ->where('empresa_id', $empresaId)
            ->whereNull('deleted_at')
            ->when(count($filiaisVinculadasIds) > 0, fn($q) => $q->whereNotIn('id', $filiaisVinculadasIds))
            ->orderByRaw('COALESCE(nome_fantasia, razao_social) asc')
            ->get(['id', 'nome_fantasia', 'razao_social']);

        // ✅ Solicitantes
        $solicitantes = DB::table('bolsa_estudos_solicitacoes as s')
            ->where('s.empresa_id', $empresaId)
            ->where('s.processo_id', $id)
            ->whereNull('s.deleted_at')
            ->join('colaboradores as c', 'c.id', '=', 's.colaborador_id')
            ->leftJoin('bolsa_estudos_cursos as cu', 'cu.id', '=', 's.curso_id')
            ->leftJoin('bolsa_estudos_entidades as e', 'e.id', '=', 'cu.entidade_id')
            ->leftJoin('filiais as f', 'f.id', '=', DB::raw($this->sqlCoalesceFilialId('s', 'c')))
            ->select([
                's.id',
                'c.nome as colaborador_nome',
                DB::raw($this->hasColumn('colaboradores', 'matricula') ? 'c.matricula as matricula' : "NULL as matricula"),
                DB::raw("COALESCE(f.nome_fantasia, f.razao_social) as filial_nome"),
                'e.nome as entidade_nome',
                'cu.nome as curso_nome',
                's.valor_total_mensalidade',
                's.valor_concessao',
                's.valor_limite',
                's.status',
                's.solicitacao_at',
            ])
            ->orderByDesc('s.id')
            ->get();

        // ✅ Documentos (primeira carga da tab)
        $docQ      = trim((string)$request->get('doc_q', ''));
        $docStatus = trim((string)$request->get('doc_status', ''));

        $documentos = $this->documentosQuery($empresaId, $id, $docQ, $docStatus)
            ->orderByDesc('d.id')
            ->paginate(10, ['*'], 'docs_page');

        return view('beneficios.bolsa.edit', [
            'sub'               => $sub,
            'processo'          => $processo,

            'unidades'          => $unidades,
            'filiaisDisponiveis'=> $filiaisDisponiveis,

            'solicitantes'      => $solicitantes,

            'documentos'        => $documentos,
            'docQ'              => $docQ,
            'docStatus'         => $docStatus,
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
    | AJAX: Documentos grid
    |--------------------------------------------------------------------------
    */
    public function documentosGrid(Request $request, string $sub, int $id)
    {
        $empresaId = (int) (auth()->user()->empresa_id ?? 0);

        $docQ      = trim((string)$request->get('doc_q', ''));
        $docStatus = trim((string)$request->get('doc_status', ''));

        $documentos = $this->documentosQuery($empresaId, $id, $docQ, $docStatus)
            ->orderByDesc('d.id')
            ->paginate(10, ['*'], 'docs_page');

        return view('beneficios.bolsa.partials._docs_table', [
            'documentos' => $documentos,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | POST: adicionar unidade ao processo
    |--------------------------------------------------------------------------
    */
    public function addUnidade(Request $request, string $sub, int $id)
    {
        $empresaId = (int)(auth()->user()->empresa_id ?? 0);

        $request->validate([
            'filial_id' => ['required', 'integer'],
        ]);

        $processo = DB::table('bolsa_estudos_processos')
            ->where('empresa_id', $empresaId)
            ->where('id', $id)
            ->whereNull('deleted_at')
            ->first();

        if (!$processo) {
            return back()->with('error', 'Processo não encontrado.');
        }

        $filialId = (int)$request->filial_id;

        $filial = DB::table('filiais')
            ->where('empresa_id', $empresaId)
            ->where('id', $filialId)
            ->whereNull('deleted_at')
            ->first();

        if (!$filial) {
            return back()->with('error', 'Filial inválida.');
        }

        $exists = DB::table('bolsa_estudos_processo_filiais')
            ->where('processo_id', $id)
            ->where('filial_id', $filialId)
            ->whereNull('deleted_at')
            ->exists();

        if (!$exists) {
            DB::table('bolsa_estudos_processo_filiais')->insert([
                'empresa_id' => $empresaId,
                'processo_id'=> $id,
                'filial_id'  => $filialId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return back()->with('success', 'Unidade adicionada com sucesso.');
    }

    /*
    |--------------------------------------------------------------------------
    | POST: adicionar solicitante
    |--------------------------------------------------------------------------
    */
    public function addSolicitante(Request $request, string $sub, int $id)
    {
        $empresaId = (int)(auth()->user()->empresa_id ?? 0);

        $data = $request->validate([
            'colaborador_id'           => ['required', 'integer'],
            'entidade_nome'            => ['required', 'string', 'max:255'],
            'curso_nome'               => ['required', 'string', 'max:255'],
            'valor_total_mensalidade'  => ['required', 'string'],
        ]);

        $processo = DB::table('bolsa_estudos_processos')
            ->where('empresa_id', $empresaId)
            ->where('id', $id)
            ->whereNull('deleted_at')
            ->first();

        if (!$processo) return back()->with('error', 'Processo não encontrado.');

        $colId = (int)$data['colaborador_id'];

        $col = DB::table('colaboradores')
            ->where('id', $colId)
            ->whereNull('deleted_at')
            ->first();

        if (!$col) return back()->with('error', 'Colaborador inválido.');

        // Entidade: cria se não existir (aprovado=0 quando a coluna existir)
        $entNome = trim($data['entidade_nome']);
        $ent = DB::table('bolsa_estudos_entidades')
            ->where('empresa_id', $empresaId)
            ->whereRaw('LOWER(nome) = LOWER(?)', [$entNome])
            ->whereNull('deleted_at')
            ->first();

        if (!$ent) {
            $insert = [
                'empresa_id' => $empresaId,
                'nome'       => $entNome,
                'cnpj'       => null,
                'created_at' => now(),
                'updated_at' => now(),
            ];
            if ($this->hasColumn('bolsa_estudos_entidades', 'aprovado')) {
                $insert['aprovado'] = 0;
            }

            $entId = DB::table('bolsa_estudos_entidades')->insertGetId($insert);
        } else {
            $entId = (int)$ent->id;
        }

        // Curso: cria se não existir (aprovado=0 quando a coluna existir)
        $cursoNome = trim($data['curso_nome']);
        $curso = DB::table('bolsa_estudos_cursos')
            ->where('empresa_id', $empresaId)
            ->where('entidade_id', $entId)
            ->whereRaw('LOWER(nome) = LOWER(?)', [$cursoNome])
            ->whereNull('deleted_at')
            ->first();

        if (!$curso) {
            $insert = [
                'empresa_id'  => $empresaId,
                'entidade_id' => $entId,
                'nome'        => $cursoNome,
                'created_at'  => now(),
                'updated_at'  => now(),
            ];
            if ($this->hasColumn('bolsa_estudos_cursos', 'aprovado')) {
                $insert['aprovado'] = 0;
            }

            $cursoId = DB::table('bolsa_estudos_cursos')->insertGetId($insert);
        } else {
            $cursoId = (int)$curso->id;
        }

        $valorMens = $this->toDecimal($data['valor_total_mensalidade']);

        $insertSolic = [
            'empresa_id'               => $empresaId,
            'processo_id'              => $id,
            'colaborador_id'           => $colId,
            'curso_id'                 => $cursoId,
            'valor_total_mensalidade'  => $valorMens,
            'valor_concessao'          => null,
            'valor_limite'             => null,
            'status'                   => 3, // Em análise
            'aprovador_id'             => null,
            'aprovacao_at'             => null,
            'aprovacao_ip'             => null,
            'solicitacao_at'           => now(),
            'created_at'               => now(),
            'updated_at'               => now(),
        ];

        // Se existir s.filial_id, gravamos
        if ($this->hasColumn('bolsa_estudos_solicitacoes', 'filial_id') && $this->hasColumn('colaboradores', 'filial_id')) {
            $insertSolic['filial_id'] = $col->filial_id ?? null;
        }

        DB::table('bolsa_estudos_solicitacoes')->insert($insertSolic);

        return back()->with('success', 'Solicitante adicionado com sucesso.');
    }

    /*
    |--------------------------------------------------------------------------
    | POST: adicionar documento via modal
    |--------------------------------------------------------------------------
    */
    public function addDocumento(Request $request, string $sub, int $id)
    {
        $empresaId = (int)(auth()->user()->empresa_id ?? 0);

        $data = $request->validate([
            'tipo'       => ['required', 'in:1,2'], // 1 comprovante, 2 documento
            'titulo'     => ['required', 'string', 'max:255'],
            'expira_em'  => ['nullable', 'date'],
            'arquivo'    => ['nullable', 'file', 'max:10240'], // até 10MB
        ]);

        $processo = DB::table('bolsa_estudos_processos')
            ->where('empresa_id', $empresaId)
            ->where('id', $id)
            ->whereNull('deleted_at')
            ->first();

        if (!$processo) return back()->with('error', 'Processo não encontrado.');

        $path = null;
        if ($request->hasFile('arquivo')) {
            // storage/app/public/bolsa_documentos/{empresa}/{processo}/...
            $path = $request->file('arquivo')->store("public/bolsa_documentos/{$empresaId}/{$id}");
        }

        DB::table('bolsa_estudos_documentos')->insert([
            'empresa_id'     => $empresaId,
            'processo_id'    => $id,
            'solicitacao_id' => null,
            'competencia_id' => null,
            'tipo'           => (int)$data['tipo'],
            'titulo'         => $data['titulo'],
            'arquivo_path'   => $path,
            'expira_em'      => $data['expira_em'] ?? null,
            'status'         => 0, // aguardando aprovação
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);

        return back()->with('success', 'Documento incluído com sucesso.');
    }

    /*
    |--------------------------------------------------------------------------
    | AJAX: colaborador por matrícula (já corrigido)
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

        if ($this->hasColumn('colaboradores', 'empresa_id') && $this->hasColumn('colaboradores', 'matricula')) {
            $q->where('c.empresa_id', $empresaId)->where('c.matricula', $matricula);
        } else {
            if ($this->hasColumn('colaboradores', 'filial_id')) {
                $q->leftJoin('filiais as f', 'f.id', '=', 'c.filial_id')
                  ->where('f.empresa_id', $empresaId);

                if ($this->hasColumn('colaboradores', 'matricula')) $q->where('c.matricula', $matricula);
                else $q->where('c.id', (int)$matricula);

                $q->addSelect(DB::raw("COALESCE(f.nome_fantasia, f.razao_social) as filial_nome"));
            } else {
                return response()->json(['ok'=>false,'message'=>'colaboradores sem filial_id/empresa_id.'], 500);
            }
        }

        $col = $q->first();
        if (!$col) return response()->json(['ok'=>false,'message'=>'Colaborador não encontrado.'], 404);

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
    | Query base documentos
    |--------------------------------------------------------------------------
    */
    private function documentosQuery(int $empresaId, int $processoId, string $docQ, string $docStatus)
    {
        $q = DB::table('bolsa_estudos_documentos as d')
            ->where('d.empresa_id', $empresaId)
            ->where('d.processo_id', $processoId)
            ->whereNull('d.deleted_at')
            ->leftJoin('bolsa_estudos_solicitacoes as s', 's.id', '=', 'd.solicitacao_id')
            ->leftJoin('colaboradores as c', 'c.id', '=', 's.colaborador_id')
            ->select([
                'd.id','d.tipo','d.titulo','d.status','d.created_at','d.expira_em','d.arquivo_path',
                'c.nome as colaborador_nome',
            ]);

        if ($docQ !== '') {
            $q->where(function ($w) use ($docQ) {
                $w->where('d.titulo', 'ILIKE', "%{$docQ}%")
                  ->orWhere('c.nome', 'ILIKE', "%{$docQ}%");
            });
        }

        if ($docStatus !== '' && is_numeric($docStatus)) {
            $q->where('d.status', (int)$docStatus);
        }

        return $q;
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

    // retorna expressão para COALESCE(s.filial_id, c.filial_id) apenas se s.filial_id existir
    private function sqlCoalesceFilialId(string $sAlias, string $cAlias): string
    {
        if ($this->hasColumn('bolsa_estudos_solicitacoes', 'filial_id')) {
            return "COALESCE({$sAlias}.filial_id, {$cAlias}.filial_id)";
        }
        return "{$cAlias}.filial_id";
    }

    // condição para contar somente solicitacoes da mesma filial do vínculo
    private function sqlFilialMatch(string $pfAlias, string $sAlias, string $cAlias): string
    {
        return "{$this->sqlCoalesceFilialId($sAlias, $cAlias)} = {$pfAlias}.filial_id";
    }
}
