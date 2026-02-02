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
    | INDEX + GRID (busca dinâmica)
    |--------------------------------------------------------------------------
    */
    public function index(Request $request, string $sub)
    {
        $processos = $this->getProcessosList($request);

        return view('beneficios.bolsa.index', [
            'sub'       => $sub,
            'processos' => $processos,
        ]);
    }

    public function grid(Request $request, string $sub)
    {
        $processos = $this->getProcessosList($request);

        return view('beneficios.bolsa.partials._table', [
            'sub'       => $sub,
            'processos' => $processos,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | CREATE / STORE
    |--------------------------------------------------------------------------
    */
    public function create(Request $request, string $sub)
    {
        $empresaId = (int) (auth()->user()->empresa_id ?? 0);

        $filiais = DB::table('filiais')
            ->select(['id', 'razao_social', 'nome_fantasia'])
            ->where('empresa_id', $empresaId)
            ->orderBy('id')
            ->get();

        return view('beneficios.bolsa.create', [
            'sub'     => $sub,
            'filiais' => $filiais,
        ]);
    }

    public function store(Request $request, string $sub)
    {
        $empresaId = (int) (auth()->user()->empresa_id ?? 0);

        $data = $request->validate([
            'ciclo'                => ['required', 'string', 'max:60'],
            'edital'               => ['nullable', 'string'],
            'inscricoes_inicio_at' => ['nullable', 'date'],
            'inscricoes_fim_at'    => ['nullable', 'date'],
            'orcamento_total'      => ['nullable', 'numeric', 'min:0'],
            'orcamento_mensal'     => ['nullable', 'numeric', 'min:0'],
            'meses_duracao'        => ['nullable', 'integer', 'min:0'],
            'status'               => ['nullable', 'integer', 'in:0,1,2'],
        ]);

        DB::beginTransaction();
        try {
            $insert = [
                'empresa_id'           => $empresaId,
                'ciclo'                => $data['ciclo'],
                'edital'               => $data['edital'] ?? null,
                'inscricoes_inicio_at' => $data['inscricoes_inicio_at'] ?? null,
                'inscricoes_fim_at'    => $data['inscricoes_fim_at'] ?? null,
                'orcamento_mensal'     => $data['orcamento_mensal'] ?? 0,
                'meses_duracao'        => $data['meses_duracao'] ?? 0,
                'status'               => $data['status'] ?? 0,
                'created_at'           => now(),
                'updated_at'           => now(),
            ];

            // Campo opcional (se existir no banco)
            if ($this->hasColumn('bolsa_estudos_processos', 'orcamento_total')) {
                $insert['orcamento_total'] = $data['orcamento_total'] ?? null;
            }

            $processoId = (int) DB::table('bolsa_estudos_processos')->insertGetId($insert);

            DB::commit();

            return redirect()
                ->route('beneficios.bolsa.edit', ['sub' => $sub, 'id' => $processoId])
                ->with('success', 'Processo criado com sucesso.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()
                ->withInput()
                ->with('error', 'Não foi possível salvar. Verifique os dados e tente novamente.');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | EDIT / UPDATE
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
            return redirect()
                ->route('beneficios.bolsa.index', ['sub' => $sub])
                ->with('error', 'Registro não encontrado.');
        }

        $filiais = DB::table('filiais')
            ->select(['id', 'razao_social', 'nome_fantasia'])
            ->where('empresa_id', $empresaId)
            ->orderBy('id')
            ->get();

        // Aba Unidades (métricas por filial)
        $unidades = [];
        if ($this->tableExists('bolsa_estudos_processo_filiais')) {
            $unidades = $this->getUnidadesDoProcesso($empresaId, $id);
        }

        // Aba Solicitantes
        $solicitantes = [];
        if ($this->tableExists('bolsa_estudos_solicitacoes')) {
            $solicitantes = $this->getSolicitantesDoProcesso($empresaId, $id);
        }

        return view('beneficios.bolsa.edit', [
            'sub'         => $sub,
            'processo'    => $processo,
            'filiais'     => $filiais,
            'unidades'    => $unidades,
            'solicitantes'=> $solicitantes,
        ]);
    }

    public function update(Request $request, string $sub, int $id)
    {
        $empresaId = (int) (auth()->user()->empresa_id ?? 0);

        $data = $request->validate([
            'ciclo'                => ['required', 'string', 'max:60'],
            'edital'               => ['nullable', 'string'],
            'inscricoes_inicio_at' => ['nullable', 'date'],
            'inscricoes_fim_at'    => ['nullable', 'date'],
            'orcamento_total'      => ['nullable', 'numeric', 'min:0'],
            'orcamento_mensal'     => ['nullable', 'numeric', 'min:0'],
            'meses_duracao'        => ['nullable', 'integer', 'min:0'],
            'status'               => ['nullable', 'integer', 'in:0,1,2'],
        ]);

        DB::beginTransaction();
        try {
            $exists = DB::table('bolsa_estudos_processos')
                ->where('empresa_id', $empresaId)
                ->where('id', $id)
                ->whereNull('deleted_at')
                ->exists();

            if (!$exists) {
                DB::rollBack();
                return redirect()
                    ->route('beneficios.bolsa.index', ['sub' => $sub])
                    ->with('error', 'Registro não encontrado.');
            }

            $update = [
                'ciclo'                => $data['ciclo'],
                'edital'               => $data['edital'] ?? null,
                'inscricoes_inicio_at' => $data['inscricoes_inicio_at'] ?? null,
                'inscricoes_fim_at'    => $data['inscricoes_fim_at'] ?? null,
                'orcamento_mensal'     => $data['orcamento_mensal'] ?? 0,
                'meses_duracao'        => $data['meses_duracao'] ?? 0,
                'status'               => $data['status'] ?? 0,
                'updated_at'           => now(),
            ];

            if ($this->hasColumn('bolsa_estudos_processos', 'orcamento_total')) {
                $update['orcamento_total'] = $data['orcamento_total'] ?? null;
            }

            DB::table('bolsa_estudos_processos')
                ->where('empresa_id', $empresaId)
                ->where('id', $id)
                ->update($update);

            DB::commit();

            return redirect()
                ->route('beneficios.bolsa.edit', ['sub' => $sub, 'id' => $id])
                ->with('success', 'Processo atualizado com sucesso.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()
                ->withInput()
                ->with('error', 'Não foi possível salvar. Verifique os dados e tente novamente.');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | DESTROY (soft delete)
    |--------------------------------------------------------------------------
    */
    public function destroy(Request $request, string $sub, int $id)
    {
        $empresaId = (int) (auth()->user()->empresa_id ?? 0);

        $updated = DB::table('bolsa_estudos_processos')
            ->where('empresa_id', $empresaId)
            ->where('id', $id)
            ->whereNull('deleted_at')
            ->update([
                'deleted_at' => now(),
                'updated_at' => now(),
            ]);

        if (!$updated) {
            return redirect()
                ->route('beneficios.bolsa.index', ['sub' => $sub])
                ->with('error', 'Registro não encontrado.');
        }

        return redirect()
            ->route('beneficios.bolsa.index', ['sub' => $sub])
            ->with('success', 'Registro excluído com sucesso.');
    }

    /*
    |--------------------------------------------------------------------------
    | APROVAÇÕES (lista pendentes status=3)
    |--------------------------------------------------------------------------
    */
    public function aprovacoes(Request $request, string $sub, int $id)
    {
        if (!$this->tableExists('bolsa_estudos_solicitacoes')) {
            return redirect()
                ->route('beneficios.bolsa.index', ['sub' => $sub])
                ->with('error', 'Tabela de solicitações ainda não foi criada (migration pendente).');
        }

        $empresaId = (int) (auth()->user()->empresa_id ?? 0);

        $processo = DB::table('bolsa_estudos_processos')
            ->where('empresa_id', $empresaId)
            ->where('id', $id)
            ->whereNull('deleted_at')
            ->first();

        if (!$processo) {
            return redirect()
                ->route('beneficios.bolsa.index', ['sub' => $sub])
                ->with('error', 'Ciclo não encontrado.');
        }

        $solicitacoes = DB::table('bolsa_estudos_solicitacoes as s')
            ->where('s.empresa_id', $empresaId)
            ->where('s.processo_id', $id)
            ->where('s.status', 3)
            ->whereNull('s.deleted_at')
            ->leftJoin('colaboradores as c', 'c.id', '=', 's.colaborador_id')
            ->leftJoin('filiais as f', 'f.id', '=', 's.filial_id')
            ->leftJoin('bolsa_estudos_cursos as cu', 'cu.id', '=', 's.curso_id')
            ->leftJoin('bolsa_estudos_entidades as e', 'e.id', '=', 'cu.entidade_id')
            ->select([
                's.id',
                's.colaborador_id',
                's.filial_id',
                's.curso_id',
                's.valor_total_mensalidade',
                's.valor_concessao',
                's.valor_limite',
                's.status',
                's.solicitacao_at',
                'c.nome as colaborador_nome',
                DB::raw("COALESCE(f.nome_fantasia, f.razao_social) as filial_nome"),
                'cu.nome as curso_nome',
                'e.nome as entidade_nome',
            ])
            ->orderByDesc('s.id')
            ->paginate(15);

        return view('beneficios.bolsa.aprovacoes', [
            'sub'          => $sub,
            'processo'     => $processo,
            'solicitacoes' => $solicitacoes,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | UNIDADES (vínculo processo_filiais)
    |--------------------------------------------------------------------------
    */
    public function storeUnidade(Request $request, string $sub, int $id)
    {
        $empresaId = (int) (auth()->user()->empresa_id ?? 0);

        if (!$this->tableExists('bolsa_estudos_processo_filiais')) {
            return back()->with('error', 'Tabela de vínculo de unidades não existe.');
        }

        $data = $request->validate([
            'filial_id' => ['required', 'integer', 'min:1'],
        ]);

        // Garante processo
        $ok = DB::table('bolsa_estudos_processos')
            ->where('empresa_id', $empresaId)
            ->where('id', $id)
            ->whereNull('deleted_at')
            ->exists();

        if (!$ok) {
            return back()->with('error', 'Processo não encontrado.');
        }

        // Garante filial da empresa
        $filialOk = DB::table('filiais')
            ->where('empresa_id', $empresaId)
            ->where('id', (int)$data['filial_id'])
            ->exists();

        if (!$filialOk) {
            return back()->with('error', 'Filial inválida.');
        }

        // Evita duplicado
        $exists = DB::table('bolsa_estudos_processo_filiais')
            ->where('processo_id', $id)
            ->where('filial_id', (int)$data['filial_id'])
            ->exists();

        if ($exists) {
            return back()->with('success', 'Unidade já estava vinculada.');
        }

        DB::table('bolsa_estudos_processo_filiais')->insert([
            'processo_id' => $id,
            'filial_id'   => (int)$data['filial_id'],
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        return back()->with('success', 'Unidade vinculada com sucesso.');
    }

    public function destroyUnidade(Request $request, string $sub, int $id, int $vinculo_id)
    {
        $empresaId = (int) (auth()->user()->empresa_id ?? 0);

        if (!$this->tableExists('bolsa_estudos_processo_filiais')) {
            return back()->with('error', 'Tabela de vínculo de unidades não existe.');
        }

        // Só remove vínculos do processo da empresa do usuário
        $processoOk = DB::table('bolsa_estudos_processos')
            ->where('empresa_id', $empresaId)
            ->where('id', $id)
            ->whereNull('deleted_at')
            ->exists();

        if (!$processoOk) {
            return back()->with('error', 'Processo não encontrado.');
        }

        $deleted = DB::table('bolsa_estudos_processo_filiais')
            ->where('id', $vinculo_id)
            ->where('processo_id', $id)
            ->delete();

        if (!$deleted) {
            return back()->with('error', 'Vínculo não encontrado.');
        }

        return back()->with('success', 'Unidade removida com sucesso.');
    }

    /*
    |--------------------------------------------------------------------------
    | SOLICITANTES
    |--------------------------------------------------------------------------
    */
    public function storeSolicitante(Request $request, string $sub, int $id)
    {
        $empresaId = (int) (auth()->user()->empresa_id ?? 0);

        if (!$this->tableExists('bolsa_estudos_solicitacoes')) {
            return back()->with('error', 'Tabela de solicitações ainda não foi criada.');
        }

        $data = $request->validate([
            'colaborador_id'          => ['required', 'integer', 'min:1'],
            'filial_id'               => ['required', 'integer', 'min:1'],
            'valor_total_mensalidade' => ['required', 'numeric', 'min:0'],

            'entidade_id'             => ['nullable', 'integer', 'min:1'],
            'entidade_nome'           => ['required', 'string', 'max:255'],

            'curso_id'                => ['nullable', 'integer', 'min:1'],
            'curso_nome'              => ['required', 'string', 'max:255'],
        ]);

        // Processo ok
        $processoOk = DB::table('bolsa_estudos_processos')
            ->where('empresa_id', $empresaId)
            ->where('id', $id)
            ->whereNull('deleted_at')
            ->exists();

        if (!$processoOk) {
            return back()->with('error', 'Processo não encontrado.');
        }

        // Filial ok
        $filialOk = DB::table('filiais')
            ->where('empresa_id', $empresaId)
            ->where('id', (int)$data['filial_id'])
            ->exists();

        if (!$filialOk) {
            return back()->with('error', 'Filial inválida.');
        }

        // Colaborador ok (garante que pertence à empresa)
        $colOk = DB::table('colaboradores')
            ->where('empresa_id', $empresaId)
            ->where('id', (int)$data['colaborador_id'])
            ->exists();

        if (!$colOk) {
            return back()->with('error', 'Colaborador inválido.');
        }

        DB::beginTransaction();
        try {
            // Entidade: usa id se veio, senão cria/acha por nome
            $entidadeId = (int)($data['entidade_id'] ?? 0);
            $entNome = trim((string)$data['entidade_nome']);

            if ($entidadeId <= 0) {
                $exist = DB::table('bolsa_estudos_entidades')
                    ->where('empresa_id', $empresaId)
                    ->where('nome', 'ILIKE', $entNome)
                    ->first(['id']);

                if ($exist?->id) {
                    $entidadeId = (int)$exist->id;
                } else {
                    $insertEnt = [
                        'empresa_id' => $empresaId,
                        'nome'       => $entNome,
                        'cnpj'       => null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                    if ($this->hasColumn('bolsa_estudos_entidades', 'aprovado')) {
                        $insertEnt['aprovado'] = 0;
                    }
                    $entidadeId = (int) DB::table('bolsa_estudos_entidades')->insertGetId($insertEnt);
                }
            }

            // Curso: usa id se veio, senão cria/acha por nome+entidade
            $cursoId = (int)($data['curso_id'] ?? 0);
            $curNome = trim((string)$data['curso_nome']);

            if ($cursoId <= 0) {
                $existC = DB::table('bolsa_estudos_cursos')
                    ->where('empresa_id', $empresaId)
                    ->where('entidade_id', $entidadeId)
                    ->where('nome', 'ILIKE', $curNome)
                    ->first(['id']);

                if ($existC?->id) {
                    $cursoId = (int)$existC->id;
                } else {
                    $insertCur = [
                        'empresa_id'  => $empresaId,
                        'entidade_id' => $entidadeId,
                        'nome'        => $curNome,
                        'created_at'  => now(),
                        'updated_at'  => now(),
                    ];
                    if ($this->hasColumn('bolsa_estudos_cursos', 'aprovado')) {
                        $insertCur['aprovado'] = 0;
                    }
                    $cursoId = (int) DB::table('bolsa_estudos_cursos')->insertGetId($insertCur);
                }
            }

            // Insere solicitação (status 0 = Digitação)
            DB::table('bolsa_estudos_solicitacoes')->insert([
                'empresa_id'              => $empresaId,
                'processo_id'             => $id,
                'colaborador_id'          => (int)$data['colaborador_id'],
                'filial_id'               => (int)$data['filial_id'],
                'curso_id'                => $cursoId,
                'valor_total_mensalidade' => $data['valor_total_mensalidade'],
                'valor_concessao'         => null,
                'valor_limite'            => null,
                'status'                  => 0,
                'solicitacao_at'          => now(),
                'created_at'              => now(),
                'updated_at'              => now(),
            ]);

            DB::commit();

            return back()->with('success', 'Solicitante adicionado com sucesso.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Não foi possível adicionar o solicitante.');
        }
    }

    public function destroySolicitante(Request $request, string $sub, int $id, int $solicitacao_id)
    {
        $empresaId = (int) (auth()->user()->empresa_id ?? 0);

        if (!$this->tableExists('bolsa_estudos_solicitacoes')) {
            return back()->with('error', 'Tabela de solicitações ainda não foi criada.');
        }

        $processoOk = DB::table('bolsa_estudos_processos')
            ->where('empresa_id', $empresaId)
            ->where('id', $id)
            ->whereNull('deleted_at')
            ->exists();

        if (!$processoOk) {
            return back()->with('error', 'Processo não encontrado.');
        }

        // Soft delete se existir coluna deleted_at, senão delete
        if ($this->hasColumn('bolsa_estudos_solicitacoes', 'deleted_at')) {
            $updated = DB::table('bolsa_estudos_solicitacoes')
                ->where('empresa_id', $empresaId)
                ->where('processo_id', $id)
                ->where('id', $solicitacao_id)
                ->whereNull('deleted_at')
                ->update([
                    'deleted_at' => now(),
                    'updated_at' => now(),
                ]);

            if (!$updated) {
                return back()->with('error', 'Solicitação não encontrada.');
            }
        } else {
            $deleted = DB::table('bolsa_estudos_solicitacoes')
                ->where('empresa_id', $empresaId)
                ->where('processo_id', $id)
                ->where('id', $solicitacao_id)
                ->delete();

            if (!$deleted) {
                return back()->with('error', 'Solicitação não encontrada.');
            }
        }

        return back()->with('success', 'Solicitante removido com sucesso.');
    }

    /*
    |--------------------------------------------------------------------------
    | AJAX: colaborador por matrícula (preenche nome + filial)
    |--------------------------------------------------------------------------
    */
    public function colaboradorPorMatricula(Request $request, string $sub)
    {
        $empresaId = (int) (auth()->user()->empresa_id ?? 0);
        $matricula = trim((string) $request->get('matricula', ''));

        if ($matricula === '') {
            return response()->json(['ok' => false, 'message' => 'Informe a matrícula.']);
        }

        // Ajuste aqui caso seu campo se chame diferente
        $col = DB::table('colaboradores')
            ->where('empresa_id', $empresaId)
            ->where(function ($q) use ($matricula) {
                $q->where('matricula', $matricula)
                  ->orWhere('codigo', $matricula);
            })
            ->select(['id', 'nome', 'filial_id'])
            ->first();

        if (!$col) {
            return response()->json(['ok' => false, 'message' => 'Colaborador não encontrado.']);
        }

        $filial = null;
        if (!empty($col->filial_id)) {
            $f = DB::table('filiais')
                ->where('empresa_id', $empresaId)
                ->where('id', (int)$col->filial_id)
                ->select(['id', DB::raw("COALESCE(nome_fantasia, razao_social) as nome")])
                ->first();

            if ($f) {
                $filial = ['id' => (int)$f->id, 'nome' => (string)$f->nome];
            }
        }

        return response()->json([
            'ok' => true,
            'colaborador' => [
                'id'   => (int)$col->id,
                'nome' => (string)$col->nome,
            ],
            'filial' => $filial,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | AJAX: entidades e cursos (autocomplete)
    |--------------------------------------------------------------------------
    */
    public function entidadesSearch(Request $request, string $sub)
    {
        $empresaId = (int) (auth()->user()->empresa_id ?? 0);
        $q = trim((string) $request->get('q', ''));

        $items = DB::table('bolsa_estudos_entidades')
            ->where('empresa_id', $empresaId)
            ->when($q !== '', fn ($qq) => $qq->where('nome', 'ILIKE', "%{$q}%"))
            ->orderBy('nome')
            ->limit(20)
            ->get(['id', 'nome']);

        return response()->json(['items' => $items]);
    }

    public function cursosSearch(Request $request, string $sub)
    {
        $empresaId = (int) (auth()->user()->empresa_id ?? 0);
        $entidadeId = (int) $request->get('entidade_id', 0);
        $q = trim((string) $request->get('q', ''));

        if ($entidadeId <= 0) {
            return response()->json(['items' => []]);
        }

        $items = DB::table('bolsa_estudos_cursos')
            ->where('empresa_id', $empresaId)
            ->where('entidade_id', $entidadeId)
            ->when($q !== '', fn ($qq) => $qq->where('nome', 'ILIKE', "%{$q}%"))
            ->orderBy('nome')
            ->limit(20)
            ->get(['id', 'nome']);

        return response()->json(['items' => $items]);
    }

    /*
    |--------------------------------------------------------------------------
    | Internals: listagem do index
    |--------------------------------------------------------------------------
    */
    private function getProcessosList(Request $request)
    {
        $empresaId = (int) (auth()->user()->empresa_id ?? 0);
        $q = trim((string) $request->get('q', ''));

        $hasSolicitacoes = $this->tableExists('bolsa_estudos_solicitacoes');

        $base = DB::table('bolsa_estudos_processos as p')
            ->where('p.empresa_id', $empresaId)
            ->whereNull('p.deleted_at')
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($w) use ($q) {
                    $w->where('p.ciclo', 'ILIKE', '%' . $q . '%')
                      ->orWhere('p.edital', 'ILIKE', '%' . $q . '%');
                });
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
            ])
            ->orderByDesc('p.id');

        if ($hasSolicitacoes) {
            return $base
                ->leftJoin('bolsa_estudos_solicitacoes as s', function ($join) {
                    $join->on('s.processo_id', '=', 'p.id')
                        ->whereNull('s.deleted_at');
                })
                ->selectRaw("COALESCE(COUNT(s.id) FILTER (WHERE s.status = 2), 0) as contemplados_count")
                ->selectRaw("COALESCE(COUNT(s.id) FILTER (WHERE s.status = 3), 0) as pendentes_count")
                ->groupBy('p.id')
                ->paginate(15);
        }

        return $base
            ->selectRaw("0 as contemplados_count")
            ->selectRaw("0 as pendentes_count")
            ->paginate(15);
    }

    /*
    |--------------------------------------------------------------------------
    | Internals: unidades do processo (métricas)
    |--------------------------------------------------------------------------
    */
    private function getUnidadesDoProcesso(int $empresaId, int $processoId): array
    {
        // inscritos = todas solicitações do processo por filial (qualquer status)
        // aprovados = status=2
        // soma limite aprovados = SUM(valor_limite) status=2
        if (!$this->tableExists('bolsa_estudos_solicitacoes')) {
            // sem tabela de solicitações, retorna só as filiais vinculadas
            $rows = DB::table('bolsa_estudos_processo_filiais as pf')
                ->join('filiais as f', 'f.id', '=', 'pf.filial_id')
                ->where('pf.processo_id', $processoId)
                ->where('f.empresa_id', $empresaId)
                ->select([
                    'pf.id as vinculo_id',
                    DB::raw("COALESCE(f.nome_fantasia, f.razao_social) as filial_nome_fantasia"),
                    DB::raw("0 as inscritos_count"),
                    DB::raw("0 as aprovados_count"),
                    DB::raw("0 as soma_limite_aprovados"),
                ])
                ->orderBy('f.id')
                ->get();

            return $rows->all();
        }

        $rows = DB::table('bolsa_estudos_processo_filiais as pf')
            ->join('filiais as f', 'f.id', '=', 'pf.filial_id')
            ->leftJoin('bolsa_estudos_solicitacoes as s', function ($join) {
                $join->on('s.processo_id', '=', 'pf.processo_id')
                     ->on('s.filial_id', '=', 'pf.filial_id')
                     ->whereNull('s.deleted_at');
            })
            ->where('pf.processo_id', $processoId)
            ->where('f.empresa_id', $empresaId)
            ->select([
                'pf.id as vinculo_id',
                'pf.filial_id',
                DB::raw("COALESCE(f.nome_fantasia, f.razao_social) as filial_nome_fantasia"),
            ])
            ->selectRaw("COALESCE(COUNT(s.id), 0) as inscritos_count")
            ->selectRaw("COALESCE(COUNT(s.id) FILTER (WHERE s.status = 2), 0) as aprovados_count")
            ->selectRaw("COALESCE(SUM(s.valor_limite) FILTER (WHERE s.status = 2), 0) as soma_limite_aprovados")
            ->groupBy('pf.id', 'pf.filial_id', 'f.nome_fantasia', 'f.razao_social')
            ->orderBy('pf.filial_id')
            ->get();

        return $rows->all();
    }

    /*
    |--------------------------------------------------------------------------
    | Internals: solicitantes do processo
    |--------------------------------------------------------------------------
    */
    private function getSolicitantesDoProcesso(int $empresaId, int $processoId): array
    {
        $rows = DB::table('bolsa_estudos_solicitacoes as s')
            ->where('s.empresa_id', $empresaId)
            ->where('s.processo_id', $processoId)
            ->whereNull('s.deleted_at')
            ->leftJoin('colaboradores as c', 'c.id', '=', 's.colaborador_id')
            ->leftJoin('filiais as f', 'f.id', '=', 's.filial_id')
            ->leftJoin('bolsa_estudos_cursos as cu', 'cu.id', '=', 's.curso_id')
            ->leftJoin('bolsa_estudos_entidades as e', 'e.id', '=', 'cu.entidade_id')
            ->select([
                's.id',
                's.status',
                's.valor_total_mensalidade',
                's.valor_concessao',
                's.valor_limite',
                's.solicitacao_at',
                'c.nome as colaborador_nome',
                DB::raw("COALESCE(f.nome_fantasia, f.razao_social) as filial_nome_fantasia"),
                'cu.nome as curso_nome',
                'e.nome as entidade_nome',
            ])
            ->orderByDesc('s.id')
            ->get();

        return $rows->all();
    }

    /*
    |--------------------------------------------------------------------------
    | Utils
    |--------------------------------------------------------------------------
    */
    private function tableExists(string $table): bool
    {
        try {
            $res = DB::selectOne("SELECT to_regclass(?) as t", ["public.$table"]);
            return !empty($res?->t);
        } catch (\Throwable $e) {
            return false;
        }
    }

    private function hasColumn(string $table, string $column): bool
    {
        try {
            return Schema::hasTable($table) && Schema::hasColumn($table, $column);
        } catch (\Throwable $e) {
            return false;
        }
    }
}
