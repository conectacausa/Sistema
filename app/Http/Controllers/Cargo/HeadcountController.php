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
            $groups = collect();
            $podeEditar = false;
            $podeCadastrar = false;

            return view('cargos.headcount.index', compact(
                'filiais','setores','liberacoes','groups',
                'podeEditar','podeCadastrar'
            ));
        }

        $q = trim((string) $request->get('q', ''));

        // Filial/Setor multi (tags)
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

        // Lista de filiais para filtro: somente onde usuário tem vínculo
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

        // Setores possíveis: dependem das filiais selecionadas (ou vazio)
        $setores = collect();
        if (!empty($filialIds)) {
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
        }

        /**
         * Liberacoes (YYYY-MM): somente meses existentes na headcounts,
         * mas respeitando as lotações do usuário.
         */
        $liberacoesQuery = DB::table('headcounts as h')
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

        if (!empty($filialIds)) $liberacoesQuery->whereIn('h.filial_id', $filialIds);
        if (!empty($setorIds))  $liberacoesQuery->whereIn('h.setor_id', $setorIds);

        $liberacoes = $liberacoesQuery
            ->selectRaw("to_char(h.data_liberacao, 'YYYY-MM') as ym")
            ->distinct()
            ->orderByDesc('ym')
            ->get();

        // liberação selecionada: YYYY-MM
        $ym = trim((string) $request->get('liberacao', ''));
        // se não veio nada, usa a mais recente (se existir)
        if ($ym === '' && $liberacoes->isNotEmpty()) {
            $ym = (string) ($liberacoes->first()->ym ?? '');
        }

        // Define intervalo do mês selecionado (primeiro ao último dia)
        $inicio = null;
        $fim = null;
        if (preg_match('/^\d{4}\-\d{2}$/', $ym)) {
            $inicio = $ym . '-01';
            // último dia do mês via SQL (Postgres)
            // fim exclusivo: primeiro dia do próximo mês
            $fim = DB::selectOne("select (date_trunc('month', ?::date) + interval '1 month')::date as dt", [$inicio])?->dt ?? null;
        }

        /**
         * Query base dos headcounts (Quadro Ideal)
         */
        $headcountsQuery = DB::table('headcounts as h')
            ->join('filiais as f', 'f.id', '=', 'h.filial_id')
            ->join('setores as s', 's.id', '=', 'h.setor_id')
            ->join('cargos as c', 'c.id', '=', 'h.cargo_id')
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

        if (!empty($filialIds)) $headcountsQuery->whereIn('h.filial_id', $filialIds);

        // setores só fazem sentido dentro das filiais selecionadas
        if (!empty($setorIds)) $headcountsQuery->whereIn('h.setor_id', $setorIds);

        if ($q !== '') {
            $headcountsQuery->where(function ($w) use ($q) {
                $w->where('c.titulo', 'ilike', "%{$q}%")
                  ->orWhereExists(function ($sub) use ($q) {
                      $sub->selectRaw('1')
                          ->from('cbos as cbo')
                          ->whereColumn('cbo.id', 'c.cbo_id')
                          ->where(function ($x) use ($q) {
                              $x->where('cbo.cbo', 'ilike', "%{$q}%")
                                ->orWhere('cbo.titulo', 'ilike', "%{$q}%");
                          });
                  });
            });
        }

        if ($inicio && $fim) {
            $headcountsQuery->where('h.data_liberacao', '>=', $inicio)
                           ->where('h.data_liberacao', '<',  $fim);
        } else {
            // sem mês válido: retorna vazio
            $headcountsQuery->whereRaw('1=0');
        }

        $rows = $headcountsQuery
            ->select([
                'h.filial_id',
                'h.setor_id',
                'h.cargo_id',
                'f.nome_fantasia as filial',
                's.nome as setor',
                'c.titulo as cargo',
            ])
            ->selectRaw('sum(h.quantidade) as quadro_ideal')
            ->groupBy('h.filial_id', 'h.setor_id', 'h.cargo_id', 'f.nome_fantasia', 's.nome', 'c.titulo')
            ->orderBy('f.nome_fantasia')
            ->orderBy('s.nome')
            ->orderBy('c.titulo')
            ->get();

        /**
         * Agrupamento p/ render:
         * Filial -> Setor -> Linhas
         */
        $groups = $rows->groupBy('filial')->map(function ($porFilial) {
            return $porFilial->groupBy('setor');
        });

        $podeCadastrar = $this->temPermissaoFlag($user?->permissao_id, 'cadastro');
        $podeEditar    = $this->temPermissaoFlag($user?->permissao_id, 'editar');

        if ($request->boolean('ajax')) {
            return view('cargos.headcount._table', compact('groups'))->render();
        }

        return view('cargos.headcount.index', compact(
            'filiais','setores','liberacoes','ym','groups',
            'podeCadastrar','podeEditar'
        ));
    }

    /**
     * AJAX: setores conforme múltiplas filiais selecionadas
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
