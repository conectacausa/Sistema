<?php

namespace App\Http\Controllers\Beneficios;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BolsaEstudosController extends Controller
{
    public function index(Request $request, string $sub)
    {
        $processos = $this->getProcessosList($request);

        return view('beneficios.bolsa.index', [
            'sub'       => $sub,
            'processos' => $processos,
        ]);
    }

    /**
     * Retorna apenas a tabela (para busca dinâmica no input)
     */
    public function grid(Request $request, string $sub)
    {
        $processos = $this->getProcessosList($request);

        return view('beneficios.bolsa.partials._table', [
            'sub'       => $sub,
            'processos' => $processos,
        ]);
    }

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
            'edital'               => ['nullable', 'string', 'max:120'],
            'inscricoes_inicio_at' => ['nullable', 'date'],
            'inscricoes_fim_at'    => ['nullable', 'date'],
            'orcamento_mensal'     => ['nullable', 'numeric', 'min:0'],
            'meses_duracao'        => ['nullable', 'integer', 'min:0'],
            'status'               => ['nullable', 'integer', 'in:0,1,2'],
            'filiais'              => ['nullable', 'array'],
            'filiais.*'            => ['integer'],
        ]);

        DB::beginTransaction();
        try {
            $processoId = DB::table('bolsa_estudos_processos')->insertGetId([
                'empresa_id'            => $empresaId,
                'ciclo'                 => $data['ciclo'],
                'edital'                => $data['edital'] ?? null,
                'inscricoes_inicio_at'  => $data['inscricoes_inicio_at'] ?? null,
                'inscricoes_fim_at'     => $data['inscricoes_fim_at'] ?? null,
                'orcamento_mensal'      => $data['orcamento_mensal'] ?? 0,
                'meses_duracao'         => $data['meses_duracao'] ?? 0,
                'status'                => $data['status'] ?? 0,
                'created_at'            => now(),
                'updated_at'            => now(),
            ]);

            if ($this->tableExists('bolsa_estudos_processo_filiais')) {
                $filiais = $data['filiais'] ?? [];
                if (!empty($filiais)) {
                    $rows = [];
                    foreach ($filiais as $filialId) {
                        $rows[] = [
                            'processo_id' => $processoId,
                            'filial_id'   => (int) $filialId,
                            'created_at'  => now(),
                            'updated_at'  => now(),
                        ];
                    }

                    $rows = collect($rows)
                        ->unique(fn ($r) => $r['processo_id'] . '-' . $r['filial_id'])
                        ->values()
                        ->all();

                    DB::table('bolsa_estudos_processo_filiais')->insert($rows);
                }
            }

            DB::commit();

            return redirect()
                ->route('beneficios.bolsa.index', ['sub' => $sub])
                ->with('success', 'Ciclo criado com sucesso.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()
                ->withInput()
                ->with('error', 'Não foi possível salvar. Verifique os dados e tente novamente.');
        }
    }

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

        $filiaisSelecionadas = [];
        if ($this->tableExists('bolsa_estudos_processo_filiais')) {
            $filiaisSelecionadas = DB::table('bolsa_estudos_processo_filiais')
                ->where('processo_id', $id)
                ->pluck('filial_id')
                ->map(fn ($v) => (int) $v)
                ->all();
        }

        return view('beneficios.bolsa.edit', [
            'sub'                 => $sub,
            'processo'            => $processo,
            'filiais'             => $filiais,
            'filiaisSelecionadas' => $filiaisSelecionadas,
        ]);
    }

    public function update(Request $request, string $sub, int $id)
    {
        $empresaId = (int) (auth()->user()->empresa_id ?? 0);

        $data = $request->validate([
            'ciclo'                => ['required', 'string', 'max:60'],
            'edital'               => ['nullable', 'string', 'max:120'],
            'inscricoes_inicio_at' => ['nullable', 'date'],
            'inscricoes_fim_at'    => ['nullable', 'date'],
            'orcamento_mensal'     => ['nullable', 'numeric', 'min:0'],
            'meses_duracao'        => ['nullable', 'integer', 'min:0'],
            'status'               => ['nullable', 'integer', 'in:0,1,2'],
            'filiais'              => ['nullable', 'array'],
            'filiais.*'            => ['integer'],
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

            DB::table('bolsa_estudos_processos')
                ->where('empresa_id', $empresaId)
                ->where('id', $id)
                ->update([
                    'ciclo'                => $data['ciclo'],
                    'edital'               => $data['edital'] ?? null,
                    'inscricoes_inicio_at' => $data['inscricoes_inicio_at'] ?? null,
                    'inscricoes_fim_at'    => $data['inscricoes_fim_at'] ?? null,
                    'orcamento_mensal'     => $data['orcamento_mensal'] ?? 0,
                    'meses_duracao'        => $data['meses_duracao'] ?? 0,
                    'status'               => $data['status'] ?? 0,
                    'updated_at'           => now(),
                ]);

            if ($this->tableExists('bolsa_estudos_processo_filiais')) {
                DB::table('bolsa_estudos_processo_filiais')
                    ->where('processo_id', $id)
                    ->delete();

                $filiais = $data['filiais'] ?? [];
                if (!empty($filiais)) {
                    $rows = [];
                    foreach ($filiais as $filialId) {
                        $rows[] = [
                            'processo_id' => $id,
                            'filial_id'   => (int) $filialId,
                            'created_at'  => now(),
                            'updated_at'  => now(),
                        ];
                    }

                    $rows = collect($rows)
                        ->unique(fn ($r) => $r['processo_id'] . '-' . $r['filial_id'])
                        ->values()
                        ->all();

                    DB::table('bolsa_estudos_processo_filiais')->insert($rows);
                }
            }

            DB::commit();

            return redirect()
                ->route('beneficios.bolsa.index', ['sub' => $sub])
                ->with('success', 'Ciclo atualizado com sucesso.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()
                ->withInput()
                ->with('error', 'Não foi possível salvar. Verifique os dados e tente novamente.');
        }
    }

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
            ->leftJoin('bolsa_estudos_cursos as cu', 'cu.id', '=', 's.curso_id')
            ->leftJoin('bolsa_estudos_entidades as e', 'e.id', '=', 'cu.entidade_id')
            ->select([
                's.id',
                's.colaborador_id',
                's.curso_id',
                's.valor_total_mensalidade',
                's.valor_concessao',
                's.valor_limite',
                's.status',
                's.solicitacao_at',
                'c.nome as colaborador_nome',
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
    | Internals
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

    private function tableExists(string $table): bool
    {
        try {
            $res = DB::selectOne("SELECT to_regclass(?) as t", ["public.$table"]);
            return !empty($res?->t);
        } catch (\Throwable $e) {
            return false;
        }
    }
}
