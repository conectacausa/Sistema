<?php

namespace App\Http\Controllers\Beneficios;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class BolsaEstudosController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */
    private function hasColumn(string $table, string $column): bool
    {
        try {
            return Schema::hasColumn($table, $column);
        } catch (\Throwable $e) {
            return false;
        }
    }

    private function empresaId(): int
    {
        return (int)(auth()->user()->empresa_id ?? 0);
    }

    /*
    |--------------------------------------------------------------------------
    | Index + Grid
    |--------------------------------------------------------------------------
    */
    public function index(Request $request, string $sub)
    {
        $empresaId = $this->empresaId();
        $q = trim((string)$request->get('q', ''));

        $query = DB::table('bolsa_estudos_processos as p')
            ->select([
                'p.id',
                'p.ciclo',
                'p.status',
                'p.orcamento_mensal',
                'p.meses_duracao',
                'p.created_at',
                DB::raw("COALESCE(COUNT(s.id) FILTER (WHERE s.status = 2), 0) as contemplados_count"),
                DB::raw("COALESCE(COUNT(s.id) FILTER (WHERE s.status = 3), 0) as pendentes_count"),
            ])
            ->leftJoin('bolsa_estudos_solicitacoes as s', function ($j) {
                $j->on('s.processo_id', '=', 'p.id');
                if ($this->hasColumn('bolsa_estudos_solicitacoes', 'deleted_at')) {
                    $j->whereNull('s.deleted_at');
                }
            })
            ->where('p.empresa_id', $empresaId);

        if ($this->hasColumn('bolsa_estudos_processos', 'deleted_at')) {
            $query->whereNull('p.deleted_at');
        }

        if ($q !== '') {
            $query->where(function ($w) use ($q) {
                $w->whereRaw('LOWER(p.ciclo) LIKE ?', ['%' . mb_strtolower($q) . '%'])
                  ->orWhereRaw('CAST(p.id AS TEXT) LIKE ?', ['%' . $q . '%']);
            });
        }

        $processos = $query
            ->groupBy('p.id', 'p.ciclo', 'p.status', 'p.orcamento_mensal', 'p.meses_duracao', 'p.created_at')
            ->orderByDesc('p.id')
            ->paginate(10)
            ->appends(['q' => $q]);

        return view('beneficios.bolsa.index', compact('processos', 'q'));
    }

    public function grid(Request $request, string $sub)
    {
        $empresaId = $this->empresaId();
        $q = trim((string)$request->get('q', ''));

        $query = DB::table('bolsa_estudos_processos as p')
            ->select([
                'p.id',
                'p.ciclo',
                'p.status',
                'p.orcamento_mensal',
                'p.meses_duracao',
                'p.created_at',
                DB::raw("COALESCE(COUNT(s.id) FILTER (WHERE s.status = 2), 0) as contemplados_count"),
                DB::raw("COALESCE(COUNT(s.id) FILTER (WHERE s.status = 3), 0) as pendentes_count"),
            ])
            ->leftJoin('bolsa_estudos_solicitacoes as s', function ($j) {
                $j->on('s.processo_id', '=', 'p.id');
                if ($this->hasColumn('bolsa_estudos_solicitacoes', 'deleted_at')) {
                    $j->whereNull('s.deleted_at');
                }
            })
            ->where('p.empresa_id', $empresaId);

        if ($this->hasColumn('bolsa_estudos_processos', 'deleted_at')) {
            $query->whereNull('p.deleted_at');
        }

        if ($q !== '') {
            $query->where(function ($w) use ($q) {
                $w->whereRaw('LOWER(p.ciclo) LIKE ?', ['%' . mb_strtolower($q) . '%'])
                  ->orWhereRaw('CAST(p.id AS TEXT) LIKE ?', ['%' . $q . '%']);
            });
        }

        $processos = $query
            ->groupBy('p.id', 'p.ciclo', 'p.status', 'p.orcamento_mensal', 'p.meses_duracao', 'p.created_at')
            ->orderByDesc('p.id')
            ->paginate(10)
            ->appends(['q' => $q]);

        return view('beneficios.bolsa.partials._grid', compact('processos'));
    }

    /*
    |--------------------------------------------------------------------------
    | Create / Store
    |--------------------------------------------------------------------------
    */
    public function create(Request $request, string $sub)
    {
        return view('beneficios.bolsa.create');
    }

    public function store(Request $request, string $sub)
    {
        $empresaId = $this->empresaId();

        $v = Validator::make($request->all(), [
            'ciclo' => ['required','string','max:160'],
            'edital' => ['nullable','string'],
            'inscricoes_inicio_at' => ['nullable'],
            'inscricoes_fim_at' => ['nullable'],
            'status' => ['nullable','integer'],
            'data_base' => ['nullable','date'],
            'valor_mensal' => ['nullable'],
            'meses_duracao' => ['nullable','integer','min:0','max:120'],
            'lembrete_recibo_ativo' => ['nullable','integer','in:0,1'],
            'lembrete_recibo_dias_antes' => ['nullable','integer','min:0','max:365'],
        ]);

        if ($v->fails()) {
            return redirect()->back()->withErrors($v)->withInput();
        }

        $valorMensal = $this->parseMoney($request->get('valor_mensal'));

        $id = DB::table('bolsa_estudos_processos')->insertGetId([
            'empresa_id' => $empresaId,
            'ciclo' => $request->get('ciclo'),
            'edital' => $request->get('edital'),
            'inscricoes_inicio_at' => $request->get('inscricoes_inicio_at') ?: null,
            'inscricoes_fim_at' => $request->get('inscricoes_fim_at') ?: null,
            'status' => (int)($request->get('status', 0)),
            'data_base' => $request->get('data_base') ?: null,
            'orcamento_mensal' => $valorMensal,
            'meses_duracao' => (int)($request->get('meses_duracao', 0)),
            'lembrete_recibo_ativo' => (int)($request->get('lembrete_recibo_ativo', 0)),
            'lembrete_recibo_dias_antes' => $request->get('lembrete_recibo_dias_antes') !== null ? (int)$request->get('lembrete_recibo_dias_antes') : null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('beneficios.bolsa.edit', ['sub' => $sub, 'id' => $id])
            ->with('success', 'Processo criado com sucesso.');
    }

    /*
    |--------------------------------------------------------------------------
    | Edit / Update / Destroy
    |--------------------------------------------------------------------------
    */
    public function edit(Request $request, string $sub, int $id)
    {
        $empresaId = $this->empresaId();

        $processo = DB::table('bolsa_estudos_processos')
            ->where('empresa_id', $empresaId)
            ->where('id', $id)
            ->when($this->hasColumn('bolsa_estudos_processos', 'deleted_at'), fn($q) => $q->whereNull('deleted_at'))
            ->first();

        if (!$processo) {
            return redirect()->route('beneficios.bolsa.index', ['sub' => $sub])
                ->with('error', 'Processo não encontrado.');
        }

        // Filiais empresa (modal unidades)
        $filiaisEmpresa = DB::table('filiais')
            ->where('empresa_id', $empresaId)
            ->when($this->hasColumn('filiais', 'deleted_at'), fn($q) => $q->whereNull('deleted_at'))
            ->orderBy('id')
            ->get(['id','nome_fantasia','razao_social']);

        $filiaisVinculadasIds = DB::table('bolsa_estudos_processo_filiais')
            ->where('processo_id', $id)
            ->when($this->hasColumn('bolsa_estudos_processo_filiais', 'deleted_at'), fn($q) => $q->whereNull('deleted_at'))
            ->pluck('filial_id')
            ->map(fn($v) => (int)$v)
            ->toArray();

        // Unidades grid
        $unidades = DB::table('bolsa_estudos_processo_filiais as pf')
            ->select([
                'pf.id as vinculo_id',
                'pf.filial_id',
                DB::raw("COALESCE(f.nome_fantasia, f.razao_social) as filial_nome"),
                DB::raw("COALESCE(COUNT(s.id) FILTER (WHERE COALESCE(s.filial_id, c.filial_id) = pf.filial_id), 0) as inscritos_count"),
                DB::raw("COALESCE(COUNT(s.id) FILTER (WHERE s.status = 2 AND COALESCE(s.filial_id, c.filial_id) = pf.filial_id), 0) as aprovados_count"),
                DB::raw("COALESCE(SUM(s.valor_limite) FILTER (WHERE s.status = 2 AND COALESCE(s.filial_id, c.filial_id) = pf.filial_id), 0) as soma_limite_aprovados"),
            ])
            ->join('filiais as f', 'f.id', '=', 'pf.filial_id')
            ->leftJoin('bolsa_estudos_solicitacoes as s', function($j){
                $j->on('s.processo_id', '=', 'pf.processo_id');
                if ($this->hasColumn('bolsa_estudos_solicitacoes', 'deleted_at')) {
                    $j->whereNull('s.deleted_at');
                }
            })
            ->leftJoin('colaboradores as c', 'c.id', '=', 's.colaborador_id')
            ->where('pf.processo_id', $id)
            ->where('f.empresa_id', $empresaId)
            ->when($this->hasColumn('bolsa_estudos_processo_filiais', 'deleted_at'), fn($q) => $q->whereNull('pf.deleted_at'))
            ->groupBy('pf.id','pf.filial_id','f.nome_fantasia','f.razao_social')
            ->orderBy('pf.filial_id')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Solicitantes (CORRIGIDO)
        | - Se existir s.entidade_id: usa direto
        | - Se NÃO existir: entidade vem por cu.entidade_id
        |--------------------------------------------------------------------------
        */
        $hasEntidadeId = $this->hasColumn('bolsa_estudos_solicitacoes', 'entidade_id');

        $solicitantesQ = DB::table('bolsa_estudos_solicitacoes as s')
            ->select([
                's.id',
                's.status',
                's.valor_total_mensalidade',
                's.valor_concessao',
                's.valor_limite',
                'c.nome as colaborador_nome',
                DB::raw("COALESCE(f.nome_fantasia, f.razao_social) as filial_nome"),
                'cu.nome as curso_nome',
                DB::raw("e.nome as entidade_nome"),
            ])
            ->join('colaboradores as c', 'c.id', '=', 's.colaborador_id')
            ->leftJoin('filiais as f', function($j){
                $j->on('f.id', '=', DB::raw('COALESCE(s.filial_id, c.filial_id)'));
            })
            ->leftJoin('bolsa_estudos_cursos as cu', 'cu.id', '=', 's.curso_id');

        if ($hasEntidadeId) {
            $solicitantesQ->leftJoin('bolsa_estudos_entidades as e', 'e.id', '=', 's.entidade_id');
        } else {
            $solicitantesQ->leftJoin('bolsa_estudos_entidades as e', 'e.id', '=', 'cu.entidade_id');
        }

        $solicitantes = $solicitantesQ
            ->where('s.empresa_id', $empresaId)
            ->where('s.processo_id', $id)
            ->when($this->hasColumn('bolsa_estudos_solicitacoes', 'deleted_at'), fn($q) => $q->whereNull('s.deleted_at'))
            ->orderByDesc('s.id')
            ->get();

        // Documentos (tab)
        $docQ = trim((string)$request->get('doc_q', ''));
        $docStatus = trim((string)$request->get('doc_status', ''));

        $documentos = $this->queryDocumentos($empresaId, $id, $docQ, $docStatus)
            ->paginate(10)
            ->appends(['doc_q' => $docQ, 'doc_status' => $docStatus]);

        // Select de solicitantes para docs (modal)
        $solicitantesParaDocs = DB::table('bolsa_estudos_solicitacoes as s')
            ->select([
                's.id',
                'c.nome as nome',
                DB::raw("COALESCE(f.nome_fantasia, f.razao_social) as filial"),
                'cu.nome as curso',
            ])
            ->join('colaboradores as c', 'c.id', '=', 's.colaborador_id')
            ->leftJoin('filiais as f', function($j){
                $j->on('f.id', '=', DB::raw('COALESCE(s.filial_id, c.filial_id)'));
            })
            ->leftJoin('bolsa_estudos_cursos as cu', 'cu.id', '=', 's.curso_id')
            ->where('s.empresa_id', $empresaId)
            ->where('s.processo_id', $id)
            ->when($this->hasColumn('bolsa_estudos_solicitacoes', 'deleted_at'), fn($q) => $q->whereNull('s.deleted_at'))
            ->orderBy('c.nome')
            ->get();

        return view('beneficios.bolsa.edit', compact(
            'processo',
            'filiaisEmpresa',
            'filiaisVinculadasIds',
            'unidades',
            'solicitantes',
            'documentos',
            'docQ',
            'docStatus',
            'solicitantesParaDocs'
        ));
    }

    public function update(Request $request, string $sub, int $id)
    {
        $empresaId = $this->empresaId();

        $processo = DB::table('bolsa_estudos_processos')
            ->where('empresa_id', $empresaId)
            ->where('id', $id)
            ->when($this->hasColumn('bolsa_estudos_processos', 'deleted_at'), fn($q) => $q->whereNull('deleted_at'))
            ->first();

        if (!$processo) {
            return redirect()->route('beneficios.bolsa.index', ['sub' => $sub])
                ->with('error', 'Processo não encontrado.');
        }

        $v = Validator::make($request->all(), [
            'ciclo' => ['required','string','max:160'],
            'edital' => ['nullable','string'],
            'inscricoes_inicio_at' => ['nullable'],
            'inscricoes_fim_at' => ['nullable'],
            'status' => ['nullable','integer'],
            'data_base' => ['nullable','date'],
            'valor_mensal' => ['nullable'],
            'meses_duracao' => ['nullable','integer','min:0','max:120'],
            'lembrete_recibo_ativo' => ['nullable','integer','in:0,1'],
            'lembrete_recibo_dias_antes' => ['nullable','integer','min:0','max:365'],
        ]);

        if ($v->fails()) {
            return redirect()->back()->withErrors($v)->withInput();
        }

        $valorMensal = $this->parseMoney($request->get('valor_mensal'));

        DB::table('bolsa_estudos_processos')
            ->where('empresa_id', $empresaId)
            ->where('id', $id)
            ->update([
                'ciclo' => $request->get('ciclo'),
                'edital' => $request->get('edital'),
                'inscricoes_inicio_at' => $request->get('inscricoes_inicio_at') ?: null,
                'inscricoes_fim_at' => $request->get('inscricoes_fim_at') ?: null,
                'status' => (int)($request->get('status', 0)),
                'data_base' => $request->get('data_base') ?: null,
                'orcamento_mensal' => $valorMensal,
                'meses_duracao' => (int)($request->get('meses_duracao', 0)),
                'lembrete_recibo_ativo' => (int)($request->get('lembrete_recibo_ativo', 0)),
                'lembrete_recibo_dias_antes' => $request->get('lembrete_recibo_dias_antes') !== null ? (int)$request->get('lembrete_recibo_dias_antes') : null,
                'updated_at' => now(),
            ]);

        return redirect()->back()->with('success', 'Processo atualizado com sucesso.');
    }

    public function destroy(Request $request, string $sub, int $id)
    {
        $empresaId = $this->empresaId();

        if ($this->hasColumn('bolsa_estudos_processos', 'deleted_at')) {
            DB::table('bolsa_estudos_processos')
                ->where('empresa_id', $empresaId)
                ->where('id', $id)
                ->update(['deleted_at' => now(), 'updated_at' => now()]);
        } else {
            DB::table('bolsa_estudos_processos')
                ->where('empresa_id', $empresaId)
                ->where('id', $id)
                ->delete();
        }

        return redirect()->route('beneficios.bolsa.index', ['sub' => $sub])
            ->with('success', 'Registro excluído.');
    }

    /*
    |--------------------------------------------------------------------------
    | Unidades
    |--------------------------------------------------------------------------
    */
    public function addUnidade(Request $request, string $sub, int $id)
    {
        $empresaId = $this->empresaId();

        $v = Validator::make($request->all(), [
            'filial_id' => ['required','integer'],
        ]);

        if ($v->fails()) {
            return redirect()->back()->with('error', 'Selecione uma unidade válida.');
        }

        $filial = DB::table('filiais')
            ->where('empresa_id', $empresaId)
            ->where('id', (int)$request->get('filial_id'))
            ->when($this->hasColumn('filiais', 'deleted_at'), fn($q) => $q->whereNull('deleted_at'))
            ->first(['id']);

        if (!$filial) {
            return redirect()->back()->with('error', 'Unidade inválida para esta empresa.');
        }

        $exists = DB::table('bolsa_estudos_processo_filiais')
            ->where('processo_id', $id)
            ->where('filial_id', (int)$filial->id)
            ->when($this->hasColumn('bolsa_estudos_processo_filiais', 'deleted_at'), fn($q) => $q->whereNull('deleted_at'))
            ->exists();

        if ($exists) {
            return redirect()->back()->with('error', 'Esta unidade já está vinculada.');
        }

        $insert = [
            'processo_id' => $id,
            'filial_id' => (int)$filial->id,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        if ($this->hasColumn('bolsa_estudos_processo_filiais', 'empresa_id')) {
            $insert['empresa_id'] = $empresaId;
        }

        DB::table('bolsa_estudos_processo_filiais')->insert($insert);

        return redirect()->back()->with('success', 'Unidade vinculada com sucesso.');
    }

    public function destroyUnidade(Request $request, string $sub, int $id, int $vinculo_id)
    {
        $empresaId = $this->empresaId();

        $q = DB::table('bolsa_estudos_processo_filiais')
            ->where('id', $vinculo_id)
            ->where('processo_id', $id);

        if ($this->hasColumn('bolsa_estudos_processo_filiais', 'empresa_id')) {
            $q->where('empresa_id', $empresaId);
        }

        if ($this->hasColumn('bolsa_estudos_processo_filiais', 'deleted_at')) {
            $q->update(['deleted_at' => now(), 'updated_at' => now()]);
        } else {
            $q->delete();
        }

        return redirect()->back()->with('success', 'Unidade removida.');
    }

    /*
    |--------------------------------------------------------------------------
    | Solicitantes
    |--------------------------------------------------------------------------
    */
    public function addSolicitante(Request $request, string $sub, int $id)
    {
        $empresaId = $this->empresaId();

        $v = Validator::make($request->all(), [
            'colaborador_id' => ['required','integer'],
            'entidade_nome' => ['required'],
            'curso_nome' => ['required'],
            'valor_total_mensalidade' => ['required'],
        ]);

        if ($v->fails()) {
            return redirect()->back()->with('error', 'Preencha os campos obrigatórios do solicitante.');
        }

        $colId = (int)$request->get('colaborador_id');

        $col = DB::table('colaboradores')
            ->where('id', $colId)
            ->when($this->hasColumn('colaboradores', 'deleted_at'), fn($q) => $q->whereNull('deleted_at'))
            ->first(['id','filial_id','nome']);

        if (!$col) {
            return redirect()->back()->with('error', 'Colaborador não encontrado.');
        }

        $entidadeId = $this->resolveEntidade($empresaId, $request->get('entidade_nome'));
        $cursoId = $this->resolveCurso($empresaId, $entidadeId, $request->get('curso_nome'));

        $valorMensalidade = $this->parseMoney($request->get('valor_total_mensalidade'));

        $data = [
            'empresa_id' => $empresaId,
            'processo_id' => $id,
            'colaborador_id' => $colId,
            'curso_id' => $cursoId,
            'valor_total_mensalidade' => $valorMensalidade,
            'status' => 0,
            'solicitacao_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ];

        // ✅ só grava filial_id se existir
        if ($this->hasColumn('bolsa_estudos_solicitacoes', 'filial_id')) {
            $data['filial_id'] = $col->filial_id ? (int)$col->filial_id : null;
        }

        // ✅ só grava entidade_id se existir
        if ($this->hasColumn('bolsa_estudos_solicitacoes', 'entidade_id')) {
            $data['entidade_id'] = $entidadeId ?: null;
        }

        DB::table('bolsa_estudos_solicitacoes')->insert($data);

        return redirect()->back()->with('success', 'Solicitante adicionado com sucesso.');
    }

    public function destroySolicitante(Request $request, string $sub, int $id, int $solicitacao_id)
    {
        $empresaId = $this->empresaId();

        $q = DB::table('bolsa_estudos_solicitacoes')
            ->where('empresa_id', $empresaId)
            ->where('processo_id', $id)
            ->where('id', $solicitacao_id);

        if ($this->hasColumn('bolsa_estudos_solicitacoes', 'deleted_at')) {
            $q->update(['deleted_at' => now(), 'updated_at' => now()]);
        } else {
            $q->delete();
        }

        return redirect()->back()->with('success', 'Solicitante removido.');
    }

    /*
    |--------------------------------------------------------------------------
    | Lookup colaborador por matrícula (modal)
    |--------------------------------------------------------------------------
    */
    public function colaboradorPorMatricula(Request $request, string $sub)
    {
        $empresaId = $this->empresaId();

        $matricula = trim((string)$request->get('matricula', ''));
        if ($matricula === '') {
            return response()->json(['ok' => false, 'data' => null]);
        }

        $matriculaNorm = preg_replace('/\s+/', '', $matricula);

        if (!$this->hasColumn('colaboradores', 'matricula')) {
            return response()->json(['ok' => false, 'data' => null]);
        }

        $colQ = DB::table('colaboradores as c')
            ->select([
                'c.id',
                'c.nome',
                'c.filial_id',
                DB::raw('CAST(c.matricula AS TEXT) as matricula_txt'),
            ])
            ->whereRaw("TRIM(CAST(c.matricula AS TEXT)) = ?", [$matriculaNorm]);

        if ($this->hasColumn('colaboradores', 'deleted_at')) {
            $colQ->whereNull('c.deleted_at');
        }

        $col = $colQ->first();

        if (!$col) {
            return response()->json(['ok' => false, 'data' => null]);
        }

        $filialNome = null;
        $filialId = (int)($col->filial_id ?? 0);

        if ($filialId > 0) {
            $fil = DB::table('filiais')
                ->where('id', $filialId)
                ->where('empresa_id', $empresaId)
                ->when($this->hasColumn('filiais', 'deleted_at'), fn($q) => $q->whereNull('deleted_at'))
                ->first(['nome_fantasia', 'razao_social']);

            if ($fil) {
                $filialNome = $fil->nome_fantasia ?: $fil->razao_social;
            }
        }

        return response()->json([
            'ok' => true,
            'data' => [
                'id' => (int)$col->id,
                'nome' => (string)$col->nome,
                'filial_id' => $filialId ?: null,
                'filial_nome' => $filialNome,
            ],
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Select2 searches
    |--------------------------------------------------------------------------
    */
    public function entidadesSearch(Request $request, string $sub)
    {
        $empresaId = $this->empresaId();
        $q = trim((string)$request->get('q', ''));

        $query = DB::table('bolsa_estudos_entidades')
            ->where('empresa_id', $empresaId);

        if ($this->hasColumn('bolsa_estudos_entidades', 'deleted_at')) {
            $query->whereNull('deleted_at');
        }

        if ($q !== '') {
            $query->whereRaw('LOWER(nome) LIKE ?', ['%' . mb_strtolower($q) . '%']);
        }

        $items = $query->orderBy('nome')->limit(20)->get(['id','nome']);

        return response()->json([
            'results' => $items->map(fn($i) => ['id' => (string)$i->id, 'text' => $i->nome])->values()
        ]);
    }

    public function cursosSearch(Request $request, string $sub)
    {
        $empresaId = $this->empresaId();
        $q = trim((string)$request->get('q', ''));
        $entidadeId = (int)$request->get('entidade_id', 0);

        $query = DB::table('bolsa_estudos_cursos')
            ->where('empresa_id', $empresaId);

        if ($this->hasColumn('bolsa_estudos_cursos', 'deleted_at')) {
            $query->whereNull('deleted_at');
        }

        if ($entidadeId > 0 && $this->hasColumn('bolsa_estudos_cursos', 'entidade_id')) {
            $query->where('entidade_id', $entidadeId);
        }

        if ($q !== '') {
            $query->whereRaw('LOWER(nome) LIKE ?', ['%' . mb_strtolower($q) . '%']);
        }

        $items = $query->orderBy('nome')->limit(20)->get(['id','nome']);

        return response()->json([
            'results' => $items->map(fn($i) => ['id' => (string)$i->id, 'text' => $i->nome])->values()
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Documentos grid (tab)
    |--------------------------------------------------------------------------
    */
    public function documentosGrid(Request $request, string $sub, int $id)
    {
        $empresaId = $this->empresaId();

        $docQ = trim((string)$request->get('doc_q', ''));
        $docStatus = trim((string)$request->get('doc_status', ''));

        $documentos = $this->queryDocumentos($empresaId, $id, $docQ, $docStatus)
            ->paginate(10)
            ->appends(['doc_q' => $docQ, 'doc_status' => $docStatus]);

        return view('beneficios.bolsa.partials._docs_table', compact('documentos'));
    }

    private function queryDocumentos(int $empresaId, int $processoId, string $q, string $status)
    {
        $query = DB::table('bolsa_estudos_documentos as d')
            ->select([
                'd.id',
                'd.titulo',
                'd.tipo',
                'd.status',
                'd.expira_em',
                'd.created_at',
                'c.nome as colaborador_nome',
            ])
            ->leftJoin('bolsa_estudos_solicitacoes as s', 's.id', '=', 'd.solicitacao_id')
            ->leftJoin('colaboradores as c', 'c.id', '=', 's.colaborador_id')
            ->where('d.empresa_id', $empresaId)
            ->where('d.processo_id', $processoId);

        if ($this->hasColumn('bolsa_estudos_documentos', 'deleted_at')) {
            $query->whereNull('d.deleted_at');
        }

        if ($q !== '') {
            $query->where(function($w) use ($q){
                $w->whereRaw('LOWER(d.titulo) LIKE ?', ['%'.mb_strtolower($q).'%'])
                  ->orWhereRaw('LOWER(COALESCE(c.nome, \'\')) LIKE ?', ['%'.mb_strtolower($q).'%']);
            });
        }

        if ($status !== '') {
            $query->where('d.status', (int)$status);
        }

        return $query->orderByDesc('d.id');
    }

    public function addDocumento(Request $request, string $sub, int $id)
    {
        return redirect()->back()->with('error', 'Upload de documento ainda não configurado neste controller.');
    }

    public function aprovacoes(Request $request, string $sub, int $id)
    {
        return redirect()->route('beneficios.bolsa.aprovacoes.index', ['sub' => $sub, 'processo_id' => $id]);
    }

    /*
    |--------------------------------------------------------------------------
    | Resolvers + Money
    |--------------------------------------------------------------------------
    */
    private function resolveEntidade(int $empresaId, $value): int
    {
        $val = is_string($value) ? trim($value) : (string)$value;
        if ($val === '') return 0;

        if (ctype_digit($val)) {
            $id = (int)$val;
            $existe = DB::table('bolsa_estudos_entidades')
                ->where('empresa_id', $empresaId)
                ->where('id', $id)
                ->when($this->hasColumn('bolsa_estudos_entidades', 'deleted_at'), fn($q) => $q->whereNull('deleted_at'))
                ->exists();
            if ($existe) return $id;
        }

        $nome = Str::limit($val, 255, '');

        $ent = DB::table('bolsa_estudos_entidades')
            ->where('empresa_id', $empresaId)
            ->whereRaw('LOWER(nome) = ?', [mb_strtolower($nome)])
            ->when($this->hasColumn('bolsa_estudos_entidades', 'deleted_at'), fn($q) => $q->whereNull('deleted_at'))
            ->first(['id']);

        if ($ent) return (int)$ent->id;

        $data = [
            'empresa_id' => $empresaId,
            'nome' => $nome,
            'cnpj' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        if ($this->hasColumn('bolsa_estudos_entidades', 'aprovado')) {
            $data['aprovado'] = 0;
        }

        return (int)DB::table('bolsa_estudos_entidades')->insertGetId($data);
    }

    private function resolveCurso(int $empresaId, int $entidadeId, $value): int
    {
        $val = is_string($value) ? trim($value) : (string)$value;
        if ($val === '') return 0;

        if (ctype_digit($val)) {
            $id = (int)$val;
            $existe = DB::table('bolsa_estudos_cursos')
                ->where('empresa_id', $empresaId)
                ->where('id', $id)
                ->when($this->hasColumn('bolsa_estudos_cursos', 'deleted_at'), fn($q) => $q->whereNull('deleted_at'))
                ->exists();
            if ($existe) return $id;
        }

        $nome = Str::limit($val, 255, '');

        $cur = DB::table('bolsa_estudos_cursos')
            ->where('empresa_id', $empresaId)
            ->when($this->hasColumn('bolsa_estudos_cursos', 'entidade_id') && $entidadeId > 0, fn($q) => $q->where('entidade_id', $entidadeId))
            ->whereRaw('LOWER(nome) = ?', [mb_strtolower($nome)])
            ->when($this->hasColumn('bolsa_estudos_cursos', 'deleted_at'), fn($q) => $q->whereNull('deleted_at'))
            ->first(['id']);

        if ($cur) return (int)$cur->id;

        $data = [
            'empresa_id' => $empresaId,
            'entidade_id' => $entidadeId,
            'nome' => $nome,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        if ($this->hasColumn('bolsa_estudos_cursos', 'aprovado')) {
            $data['aprovado'] = 0;
        }

        return (int)DB::table('bolsa_estudos_cursos')->insertGetId($data);
    }

    private function parseMoney($value): float
    {
        $str = trim((string)$value);
        if ($str === '') return 0.0;

        $str = str_replace('R$', '', $str);
        $str = trim($str);
        $str = str_replace('.', '', $str);
        $str = str_replace(',', '.', $str);

        $n = (float)$str;
        return is_nan($n) ? 0.0 : $n;
    }
}
