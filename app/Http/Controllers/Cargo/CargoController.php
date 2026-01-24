<?php

namespace App\Http\Controllers\Cargo;

use App\Http\Controllers\Controller;
use App\Models\Cargo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CargoController extends Controller
{
    private int $telaId = 7;

    public function index(Request $request)
    {
        $user = Auth::user();

        $empresaId = (int) ($user->empresa_id ?? 0);
        $usuarioId = (int) ($user->id ?? 0);

        if ($empresaId <= 0 || $usuarioId <= 0) {
            $cargos = Cargo::query()->whereRaw('1=0')->paginate(50);
            $podeCadastrar = false;
            $podeEditar = false;
            $filiais = collect();
            $setores = collect();
            $lotacoesPorCargo = collect();

            return view('cargos.cargos.index', compact(
                'cargos',
                'podeCadastrar',
                'podeEditar',
                'filiais',
                'setores',
                'lotacoesPorCargo'
            ));
        }

        $q = trim((string) $request->get('q', ''));
        $filialId = (int) $request->get('filial_id', 0);
        $setorId  = (int) $request->get('setor_id', 0);

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
         * - se filial selecionada: setores do vínculo do usuário naquela filial
         * - se não selecionada: vazio (força escolher filial antes)
         */
        $setores = collect();
        if ($filialId > 0) {
            $setores = DB::table('vinculo_usuario_lotacao as vul')
                ->join('setores as s', 's.id', '=', 'vul.setor_id')
                ->select('s.id', 's.nome')
                ->where('vul.empresa_id', $empresaId)
                ->where('vul.usuario_id', $usuarioId)
                ->where('vul.ativo', true)
                ->whereNull('vul.deleted_at')
                ->where('vul.filial_id', $filialId)
                ->distinct()
                ->orderBy('s.nome')
                ->get();
        }

        /**
         * CARGOS visíveis:
         * Somente se existir vínculo:
         * vinculo_cargo_lotacao (cargo, filial, setor) ATIVO
         * E o usuário também tiver vínculo com a MESMA filial+setor ATIVO
         */
        $cargos = Cargo::query()
            ->with(['cbo'])
            ->where('cargos.empresa_id', $empresaId)
            ->whereExists(function ($sub) use ($empresaId, $usuarioId, $filialId, $setorId) {
                $sub->selectRaw('1')
                    ->from('vinculo_cargo_lotacao as vcl')
                    ->join('vinculo_usuario_lotacao as vul', function ($join) use ($usuarioId) {
                        $join->on('vul.empresa_id', '=', 'vcl.empresa_id')
                            ->on('vul.filial_id', '=', 'vcl.filial_id')
                            ->on('vul.setor_id', '=', 'vcl.setor_id')
                            ->where('vul.usuario_id', '=', $usuarioId)
                            ->where('vul.ativo', '=', true)
                            ->whereNull('vul.deleted_at');
                    })
                    ->whereColumn('vcl.cargo_id', 'cargos.id')
                    ->where('vcl.empresa_id', $empresaId)
                    ->where('vcl.ativo', true)
                    ->whereNull('vcl.deleted_at');

                if ($filialId > 0) $sub->where('vcl.filial_id', $filialId);
                if ($setorId > 0)  $sub->where('vcl.setor_id', $setorId);
            })
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($w) use ($q) {
                    $w->where('cargos.titulo', 'ilike', "%{$q}%")
                      ->orWhereHas('cbo', function ($q2) use ($q) {
                          $q2->where('cbo', 'ilike', "%{$q}%")
                             ->orWhere('titulo', 'ilike', "%{$q}%");
                      });
                });
            })
            ->orderBy('cargos.titulo')
            ->paginate(50)
            ->withQueryString();

        /**
         * LOTAÇÕES (Filial > Setor) por cargo:
         * - somente vínculos ativos do cargo
         * - somente vínculos que o usuário também possui
         * - para exibir múltiplas linhas na coluna Lotação
         */
        $cargoIds = $cargos->getCollection()->pluck('id')->map(fn ($v) => (int) $v)->values()->all();
        $lotacoesPorCargo = collect();

        if (!empty($cargoIds)) {
            $rows = DB::table('vinculo_cargo_lotacao as vcl')
                ->join('filiais as f', 'f.id', '=', 'vcl.filial_id')
                ->join('setores as s', 's.id', '=', 'vcl.setor_id')
                ->join('vinculo_usuario_lotacao as vul', function ($join) use ($empresaId, $usuarioId) {
                    $join->on('vul.empresa_id', '=', 'vcl.empresa_id')
                        ->on('vul.filial_id', '=', 'vcl.filial_id')
                        ->on('vul.setor_id', '=', 'vcl.setor_id')
                        ->where('vul.usuario_id', '=', $usuarioId)
                        ->where('vul.ativo', '=', true)
                        ->whereNull('vul.deleted_at');
                })
                ->where('vcl.empresa_id', $empresaId)
                ->whereIn('vcl.cargo_id', $cargoIds)
                ->where('vcl.ativo', true)
                ->whereNull('vcl.deleted_at')
                ->select([
                    'vcl.cargo_id',
                    'f.nome_fantasia as filial',
                    's.nome as setor',
                ])
                ->orderBy('f.nome_fantasia')
                ->orderBy('s.nome')
                ->get();

            $lotacoesPorCargo = $rows->groupBy('cargo_id');
        }

        $podeCadastrar = $this->temPermissaoFlag($user?->permissao_id, 'cadastro');
        $podeEditar    = $this->temPermissaoFlag($user?->permissao_id, 'editar');

        if ($request->boolean('ajax')) {
            return view('cargos.cargos._table', compact('cargos', 'podeEditar', 'lotacoesPorCargo'))->render();
        }

        return view('cargos.cargos.index', compact(
            'cargos',
            'podeCadastrar',
            'podeEditar',
            'filiais',
            'setores',
            'lotacoesPorCargo'
        ));
    }

    /**
     * Endpoint AJAX: setores conforme filial, mas somente os que o usuário tem vínculo.
     */
    public function setoresPorFilial(Request $request)
    {
        $user = Auth::user();

        $empresaId = (int) ($user->empresa_id ?? 0);
        $usuarioId = (int) ($user->id ?? 0);
        $filialId  = (int) $request->query('filial_id', 0);

        if ($empresaId <= 0 || $usuarioId <= 0 || $filialId <= 0) {
            return response()->json([]);
        }

        $setores = DB::table('vinculo_usuario_lotacao as vul')
            ->join('setores as s', 's.id', '=', 'vul.setor_id')
            ->select('s.id', 's.nome')
            ->where('vul.empresa_id', $empresaId)
            ->where('vul.usuario_id', $usuarioId)
            ->where('vul.filial_id', $filialId)
            ->where('vul.ativo', true)
            ->whereNull('vul.deleted_at')
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
