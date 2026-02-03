<?php

namespace App\Http\Controllers\Beneficios;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class BolsaAprovacoesController extends Controller
{
    public function index(Request $request, string $sub, int $processo_id)
    {
        $empresaId = (int) (auth()->user()->empresa_id ?? 0);

        $processo = $this->getProcesso($empresaId, $processo_id);
        if (!$processo) {
            return redirect()->route('beneficios.bolsa.index', ['sub' => $sub])
                ->with('error', 'Processo não encontrado.');
        }

        $q = trim((string)$request->get('q', ''));

        $solicitacoes = DB::table('bolsa_estudos_solicitacoes as s')
            ->where('s.empresa_id', $empresaId)
            ->where('s.processo_id', $processo_id)
            ->whereNull('s.deleted_at')
            ->where('s.status', 3) // Em análise
            ->leftJoin('colaboradores as c', 'c.id', '=', 's.colaborador_id')
            ->leftJoin('bolsa_estudos_cursos as cu', 'cu.id', '=', 's.curso_id')
            ->leftJoin('bolsa_estudos_entidades as e', 'e.id', '=', 'cu.entidade_id')
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($w) use ($q) {
                    $w->where('c.nome', 'ILIKE', "%{$q}%")
                      ->orWhere('cu.nome', 'ILIKE', "%{$q}%")
                      ->orWhere('e.nome', 'ILIKE', "%{$q}%");
                });
            })
            ->select([
                's.id',
                's.solicitacao_at',
                's.valor_total_mensalidade',
                'c.nome as colaborador_nome',
                'cu.nome as curso_nome',
                'e.nome as entidade_nome',
            ])
            ->orderByDesc('s.id')
            ->paginate(20);

        return view('beneficios.bolsa.aprovacoes.index', [
            'sub'         => $sub,
            'processo'    => $processo,
            'solicitacoes'=> $solicitacoes,
            'q'           => $q,
        ]);
    }

    public function show(Request $request, string $sub, int $processo_id, int $solicitacao_id)
    {
        $empresaId = (int) (auth()->user()->empresa_id ?? 0);

        $processo = $this->getProcesso($empresaId, $processo_id);
        if (!$processo) {
            return redirect()->route('beneficios.bolsa.index', ['sub' => $sub])
                ->with('error', 'Processo não encontrado.');
        }

        $sol = $this->getSolicitacaoDetalhe($empresaId, $processo_id, $solicitacao_id);
        if (!$sol) {
            return redirect()->route('beneficios.bolsa.aprovacoes.index', ['sub' => $sub, 'processo_id' => $processo_id])
                ->with('error', 'Solicitação não encontrada.');
        }

        return view('beneficios.bolsa.aprovacoes.show', [
            'sub'      => $sub,
            'processo' => $processo,
            'sol'      => $sol,
        ]);
    }

    public function aprovar(Request $request, string $sub, int $processo_id, int $solicitacao_id)
    {
        $empresaId = (int) (auth()->user()->empresa_id ?? 0);
        $userId    = (int) (auth()->user()->id ?? 0);

        $processo = $this->getProcesso($empresaId, $processo_id);
        if (!$processo) {
            return redirect()->route('beneficios.bolsa.index', ['sub' => $sub])
                ->with('error', 'Processo não encontrado.');
        }

        $sol = DB::table('bolsa_estudos_solicitacoes')
            ->where('empresa_id', $empresaId)
            ->where('processo_id', $processo_id)
            ->where('id', $solicitacao_id)
            ->whereNull('deleted_at')
            ->first();

        if (!$sol) {
            return back()->with('error', 'Solicitação não encontrada.');
        }

        // só aprova se está em análise
        if ((int)$sol->status !== 3) {
            return back()->with('error', 'Esta solicitação não está em análise.');
        }

        $data = $request->validate([
            'percentual' => ['required', 'numeric', 'min:0', 'max:100'],
        ]);

        $valorTotal = (float) ($sol->valor_total_mensalidade ?? 0);
        $perc = (float) $data['percentual'];

        $valorLimite = round($valorTotal * ($perc / 100), 2);
        $valorConcessao = $valorLimite;

        DB::beginTransaction();
        try {
            DB::table('bolsa_estudos_solicitacoes')
                ->where('empresa_id', $empresaId)
                ->where('processo_id', $processo_id)
                ->where('id', $solicitacao_id)
                ->whereNull('deleted_at')
                ->update([
                    'status'               => 2,
                    'percentual_concessao' => $this->hasColumn('bolsa_estudos_solicitacoes', 'percentual_concessao') ? $perc : null,
                    'valor_limite'         => $valorLimite,
                    'valor_concessao'      => $valorConcessao,
                    'aprovador_id'         => $userId ?: null,
                    'aprovacao_at'         => now(),
                    'aprovacao_ip'         => (string) $request->ip(),
                    'justificativa_reprovacao' => null,
                    'updated_at'           => now(),
                ]);

            // gerar competências mensais
            $this->gerarCompetencias($empresaId, $processo, $solicitacao_id, $valorLimite);

            DB::commit();

            return redirect()
                ->route('beneficios.bolsa.aprovacoes.index', ['sub' => $sub, 'processo_id' => $processo_id])
                ->with('success', 'Solicitação aprovada com sucesso.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Não foi possível aprovar. Tente novamente.');
        }
    }

    public function reprovar(Request $request, string $sub, int $processo_id, int $solicitacao_id)
    {
        $empresaId = (int) (auth()->user()->empresa_id ?? 0);
        $userId    = (int) (auth()->user()->id ?? 0);

        $processo = $this->getProcesso($empresaId, $processo_id);
        if (!$processo) {
            return redirect()->route('beneficios.bolsa.index', ['sub' => $sub])
                ->with('error', 'Processo não encontrado.');
        }

        $sol = DB::table('bolsa_estudos_solicitacoes')
            ->where('empresa_id', $empresaId)
            ->where('processo_id', $processo_id)
            ->where('id', $solicitacao_id)
            ->whereNull('deleted_at')
            ->first();

        if (!$sol) {
            return back()->with('error', 'Solicitação não encontrada.');
        }

        if ((int)$sol->status !== 3) {
            return back()->with('error', 'Esta solicitação não está em análise.');
        }

        $data = $request->validate([
            'justificativa' => ['required', 'string', 'min:5'],
        ]);

        DB::table('bolsa_estudos_solicitacoes')
            ->where('empresa_id', $empresaId)
            ->where('processo_id', $processo_id)
            ->where('id', $solicitacao_id)
            ->whereNull('deleted_at')
            ->update([
                'status'                  => 1,
                'justificativa_reprovacao'=> $this->hasColumn('bolsa_estudos_solicitacoes', 'justificativa_reprovacao') ? $data['justificativa'] : null,
                'aprovador_id'            => $userId ?: null,
                'aprovacao_at'            => now(),
                'aprovacao_ip'            => (string) $request->ip(),
                'updated_at'              => now(),
            ]);

        return redirect()
            ->route('beneficios.bolsa.aprovacoes.index', ['sub' => $sub, 'processo_id' => $processo_id])
            ->with('success', 'Solicitação reprovada com sucesso.');
    }

    // ------------------------
    // Internals
    // ------------------------

    private function getProcesso(int $empresaId, int $processoId)
    {
        return DB::table('bolsa_estudos_processos')
            ->where('empresa_id', $empresaId)
            ->where('id', $processoId)
            ->whereNull('deleted_at')
            ->first();
    }

    private function getSolicitacaoDetalhe(int $empresaId, int $processoId, int $solicitacaoId)
    {
        $q = DB::table('bolsa_estudos_solicitacoes as s')
            ->where('s.empresa_id', $empresaId)
            ->where('s.processo_id', $processoId)
            ->where('s.id', $solicitacaoId)
            ->whereNull('s.deleted_at')
            ->leftJoin('colaboradores as c', 'c.id', '=', 's.colaborador_id')
            ->leftJoin('bolsa_estudos_cursos as cu', 'cu.id', '=', 's.curso_id')
            ->leftJoin('bolsa_estudos_entidades as e', 'e.id', '=', 'cu.entidade_id')
            ->select([
                's.*',
                'c.nome as colaborador_nome',
                DB::raw($this->hasColumn('colaboradores', 'email') ? 'c.email as colaborador_email' : 'NULL as colaborador_email'),
                DB::raw($this->hasColumn('colaboradores', 'telefone') ? 'c.telefone as colaborador_telefone' : 'NULL as colaborador_telefone'),
                DB::raw($this->hasColumn('colaboradores', 'data_admissao') ? 'c.data_admissao as colaborador_data_admissao' : 'NULL as colaborador_data_admissao'),
                DB::raw($this->hasColumn('colaboradores', 'matricula') ? 'c.matricula as colaborador_matricula' : 'NULL as colaborador_matricula'),
                'cu.nome as curso_nome',
                'e.nome as entidade_nome',
            ]);

        // filial pelo s.filial_id (preferencial)
        if ($this->hasColumn('bolsa_estudos_solicitacoes', 'filial_id')) {
            $q->leftJoin('filiais as f', 'f.id', '=', 's.filial_id')
              ->addSelect(DB::raw("COALESCE(f.nome_fantasia, f.razao_social) as filial_nome"));
        } elseif ($this->hasColumn('colaboradores', 'filial_id')) {
            $q->leftJoin('filiais as f', 'f.id', '=', 'c.filial_id')
              ->addSelect(DB::raw("COALESCE(f.nome_fantasia, f.razao_social) as filial_nome"));
        } else {
            $q->addSelect(DB::raw("NULL as filial_nome"));
        }

        return $q->first();
    }

    private function gerarCompetencias(int $empresaId, object $processo, int $solicitacaoId, float $valorPrevisto): void
    {
        if (!Schema::hasTable('bolsa_estudos_solicitacao_competencias')) {
            return;
        }

        $meses = (int)($processo->meses_duracao ?? 0);
        if ($meses <= 0) return;

        // data base: se tiver, usa. Senão, usa o mês atual.
        $base = null;
        if (!empty($processo->data_base)) {
            $base = Carbon::parse($processo->data_base)->startOfMonth();
        } else {
            $base = now()->startOfMonth();
        }

        // evita duplicar (se já gerou antes)
        $jaExiste = DB::table('bolsa_estudos_solicitacao_competencias')
            ->where('empresa_id', $empresaId)
            ->where('solicitacao_id', $solicitacaoId)
            ->whereNull('deleted_at')
            ->exists();

        if ($jaExiste) return;

        $rows = [];
        for ($i = 0; $i < $meses; $i++) {
            $comp = (clone $base)->addMonths($i)->startOfMonth();

            $rows[] = [
                'empresa_id'     => $empresaId,
                'solicitacao_id' => $solicitacaoId,
                'competencia'    => $comp->format('Y-m-d'),
                'vencimento'     => null,
                'status'         => 0,
                'valor_previsto' => $valorPrevisto,
                'created_at'     => now(),
                'updated_at'     => now(),
            ];
        }

        DB::table('bolsa_estudos_solicitacao_competencias')->insert($rows);
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
