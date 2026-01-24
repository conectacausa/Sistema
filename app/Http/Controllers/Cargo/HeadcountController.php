<?php

namespace App\Http\Controllers\Cargo;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class HeadcountController extends Controller
{
    private int $telaId = 8;

    public function index(Request $request)
    {
        $user = Auth::user();

        $empresaId = (int) ($user->empresa_id ?? 0);
        $usuarioId = (int) ($user->id ?? 0);

        if ($empresaId <= 0 || $usuarioId <= 0) {
            $filiais = collect();
            $setores = collect();
            $liberacoes = collect();
            $ym = '';
            $groups = collect();
            $podeEditar = false;
            $podeCadastrar = false;

            return view('cargos.headcount.index', compact(
                'filiais', 'setores', 'liberacoes', 'ym', 'groups',
                'podeEditar', 'podeCadastrar'
            ));
        }

        $q = trim((string) $request->get('q', ''));

        // Multi-select: filial_id[] e setor_id[]
        $filialIds = collect($request->get('filial_id', []))
            ->flatten()
            ->map(fn ($v) => (int) $v)
            ->filter(fn ($v) => $v > 0)
            ->values()
            ->all();

        $setorIds = collect($request->get('setor_id', []))
            ->flatten()
            ->map(fn ($v) => (int) $v)
            ->filter(fn ($v) => $v > 0)
            ->values()
            ->all();

        /**
         * FILIAIS do filtro: somente as filiais onde o usuário tem vínculo ativo
         */
        $filiais = DB::table('vinculo_usuario_lotacao as vul')
            ->join('filiais as f', 'f.id', '=', 'vul.filial_id')
            ->select('f.id', 'f.nome_fantasia')
            ->where('vul.empresa_id', $empresaId)
            ->where('vul.usuario_id', $usuarioId)
            ->where('vul.ativo', true)
            ->whereNull('vul.deleted_at')
            ->distinct()
            ->orderBy('f.nome_fantasia')
            ->get();

        /**
         * SETORES do filtro:
         * - se filiais selecionadas: setores do usuário nessas filiais
         * - se NÃO selecionadas: todos os setores do usuário (todas as filiais)
         */
        $setores = DB::table('vinculo_usuario_lotacao as vul')
            ->join('setores as s', 's.id', '=', 'vul.setor_id')
            ->select('s.id', 's.nome', 'vul.filial_id')
            ->where('vul.empresa_id', $empresaId)
            ->where('vul.usuario_id', $usuarioId)
            ->where('vul.ativo', true)
            ->whereNull('vul.deleted_at')
            ->when(!empty($filialIds), fn ($qq) => $qq->whereIn('vul.filial_id', $filialIds))
            ->distinct()
            ->orderBy('s.nome')
            ->get();

        /**
         * LIBERAÇÕES (YYYY-MM):
         * - somente meses existentes na tabela headcounts
         * - respeitando a lotação do usuário
         * - (filial/setor opcionais filtram a lista quando selecionados)
         */
        $liberacoesBase = DB::table('headcounts as h')
            ->join('vinculo_usuario_lotacao as vul', function ($join) use ($empresaId, $usuarioId) {
                $join->on('vul.empresa_id', '=', 'h.empresa_id')
                    ->on('vul.filial_id', '=', 'h.filial_id')
                    ->on('vul.setor_id', '=', 'h.setor_id')
                    ->where('vul.usuario_id', '=', $usuarioId)
                    ->where('vul.ativo', '=', true)
                    ->whereNull('vul.deleted_at');
            })
            ->where('h.empresa_id', $empresaId)
            ->whereNull('h.deleted_at');

        if (!empty($filialIds)) $liberacoesBase->whereIn('h.filial_id', $filialIds);
        if (!empty($setorIds))  $liberacoesBase->whereIn('h.setor_id', $setorIds);

        // Asc para achar "próximo mês"
        $liberacoesAsc = $liberacoesBase
            ->selectRaw("to_char(h.data_liberacao, 'YYYY-MM') as ym")
            ->distinct()
            ->orderBy('ym')
            ->get();

        // Para exibir no select (desc)
        $liberacoes = $liberacoesAsc->sortByDesc('ym')->values();

        // liberação selecionada (se veio do request)
        $ym = trim((string) $request->get('liberacao', ''));

        // default: mês atual se existir; senão próximo; senão mais recente
        if ($ym === '') {
            $currentYm = now()->format('Y-m');
            $all = $liberacoesAsc->pluck('ym')->map(fn ($v) => (string) $v)->values();

            if ($all->contains($currentYm)) {
                $ym = $currentYm;
            } else {
                $next = $all->first(fn ($v) => $v > $currentYm);
                if (!empty($next)) {
                    $ym = $next;
                } else {
                    $ym = (string) ($all->last() ?? '');
                }
            }
        }

        /**
         * Intervalo do mês selecionado
         * (>= primeiro dia) e (< primeiro dia do próximo mês)
         */
        $inicio = null;
        $fimExclusivo = null;

        if (preg_match('/^\d{4}\-\d{2}$/', $ym)) {
            $inicio = $ym . '-01';
            $fimExclusivo = DB::selectOne(
                "select (date_trunc('month', ?::date) + interval '1 month')::date as dt",
                [$inicio]
            )?->dt ?? null;
        }

        /**
         * Query base dos headcounts (Quadro Ideal)
         * - Somente mês é obrigatório
         * - Filial/Setor/Cargo(CBO) são opcionais
         * - Sempre respeita a lotação do usuário
         */
        $headcountsQuery = DB::table('headcounts as h')
            ->join('filiais as f', 'f.id', '=', 'h.filial_id')
            ->join('setores as s', 's.id', '=', 'h.setor_id')
            ->join('cargos as c', 'c.id', '=', 'h.cargo_id')
            ->leftJoin('cbos as cbo', 'cbo.id', '=', 'c.cbo_id')
            ->join('vinculo_usuario_lotacao as vul', function ($join) use ($empresaId, $usuarioId) {
                $join->on('vul.empresa_id', '=', 'h.empresa_id')
                    ->on('vul.filial_id', '=', 'h.filial_id')
                    ->on('vul.setor_id', '=', 'h.setor_id')
                    ->where('vul.usuario_id', '=', $usuarioId)
                    ->where('vul.ativo', '=', true)
                    ->whereNull('vul.deleted_at');
            })
            ->where('h.empresa_id', $empresaId)
            ->whereNull('h.deleted_at');

        // Filtros opcionais
        if (!empty($filialIds)) $headcountsQuery->whereIn('h.filial_id', $filialIds);
        if (!empty($setorIds))  $headcountsQuery->whereIn('h.setor_id', $setorIds);

        if ($q !== '') {
            $headcountsQuery->where(function ($w) use ($q) {
                $w->where('c.titulo', 'ilike', "%{$q}%")
                  ->orWhere('cbo.cbo', 'ilike', "%{$q}%")
                  ->orWhere('cbo.titulo', 'ilike', "%{$q}%");
            });
        }

        // mês obrigatório: se não tiver mês válido/selecionado, retorna vazio
        if ($inicio && $fimExclusivo) {
            $headcountsQuery->where('h.data_liberacao', '>=', $inicio)
                           ->where('h.data_liberacao', '<',  $fimExclusivo);
        } else {
            $headcountsQuery->whereRaw('1=0');
        }

       $subQuadroAtual = DB::table('vinculo_colaborador_cargo_setor as vccs')
    ->whereNull('vccs.deleted_at')
    ->whereNull('vccs.data_fim') // SOMENTE ATIVOS
    ->groupBy('vccs.cargo_id', 'vccs.setor_id')
    ->selectRaw('vccs.cargo_id, vccs.setor_id, count(*)::int as quadro_atual');

$rows = $headcountsQuery
    ->leftJoinSub($subQuadroAtual, 'qa', function ($join) {
        $join->on('qa.cargo_id', '=', 'h.cargo_id')
             ->on('qa.setor_id', '=', 'h.setor_id');
    })
    ->select([
        'h.filial_id',
        'h.setor_id',
        'h.cargo_id',
        'f.nome_fantasia as filial',
        's.nome as setor',
        'c.titulo as cargo',
    ])
    ->selectRaw('sum(h.quantidade)::int as quadro_ideal')
    ->selectRaw('coalesce(max(qa.quadro_atual), 0)::int as quadro_atual')
    ->groupBy(
        'h.filial_id',
        'h.setor_id',
        'h.cargo_id',
        'f.nome_fantasia',
        's.nome',
        'c.titulo'
    )
    ->orderBy('f.nome_fantasia')
    ->orderBy('s.nome')
    ->orderBy('c.titulo')
    ->get();


        // Agrupa: Filial -> Setor -> Linhas
        $groups = $rows->groupBy('filial')->map(fn ($porFilial) => $porFilial->groupBy('setor'));

        $podeCadastrar = $this->temPermissaoFlag($user?->permissao_id, 'cadastro');
        $podeEditar    = $this->temPermissaoFlag($user?->permissao_id, 'editar');

        if ($request->boolean('ajax')) {
            return view('cargos.headcount._table', compact('groups'))->render();
        }

        return view('cargos.headcount.index', compact(
            'filiais', 'setores', 'liberacoes', 'ym', 'groups',
            'podeCadastrar', 'podeEditar'
        ));
    }

    /**
     * AJAX: setores conforme múltiplas filiais selecionadas
     * Retorna união dos setores do usuário nas filiais selecionadas
     */
    public function setoresPorFiliais(Request $request)
    {
        $user = Auth::user();

        $empresaId = (int) ($user->empresa_id ?? 0);
        $usuarioId = (int) ($user->id ?? 0);

        $filialIds = collect($request->query('filial_id', []))
            ->flatten()
            ->map(fn ($v) => (int) $v)
            ->filter(fn ($v) => $v > 0)
            ->values()
            ->all();

        if ($empresaId <= 0 || $usuarioId <= 0 || empty($filialIds)) {
            return response()->json([]);
        }

        $setores = DB::table('vinculo_usuario_lotacao as vul')
            ->join('setores as s', 's.id', '=', 'vul.setor_id')
            ->select('s.id', 's.nome', 'vul.filial_id')
            ->where('vul.empresa_id', $empresaId)
            ->where('vul.usuario_id', $usuarioId)
            ->where('vul.ativo', true)
            ->whereNull('vul.deleted_at')
            ->whereIn('vul.filial_id', $filialIds)
            ->distinct()
            ->orderBy('s.nome')
            ->get();

        return response()->json($setores);
    }

    private function temPermissaoFlag(?int $permissaoId, string $flag): bool
    {
        $permissaoId = (int) ($permissaoId ?? 0);
        if ($permissaoId <= 0) return false;

        if (!in_array($flag, ['cadastro', 'editar'], true)) return false;

        return DB::table('permissao_modulo_tela')
            ->where('permissao_id', $permissaoId)
            ->where('tela_id', $this->telaId)
            ->where('ativo', true)
            ->where($flag, true)
            ->exists();
    }
}
