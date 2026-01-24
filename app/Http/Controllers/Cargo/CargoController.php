<?php

namespace App\Http\Controllers\Cargo;

use App\Http\Controllers\Controller;
use App\Models\Cargo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CargoController extends Controller
{
    private int $telaId = 7;

    public function index(Request $request)
    {
        $user = Auth::user();
        $empresaId = (int) ($user->empresa_id ?? 0);

        // segurança: se usuário não tem empresa_id, não mostra nada
        if ($empresaId <= 0) {
            $cargos = Cargo::query()->whereRaw('1=0')->paginate(50);
            $podeCadastrar = false;
            $podeEditar = false;
            $filiais = collect();
            $setores = collect();

            return view('cargos.cargos.index', compact('cargos', 'podeCadastrar', 'podeEditar', 'filiais', 'setores'));
        }

        $q = trim((string) $request->get('q', ''));
        $filialId = (int) $request->get('filial_id', 0);
        $setorId  = (int) $request->get('setor_id', 0);

        // Filiais ativas da empresa do usuário
        $filiais = DB::table('filiais')
    ->select('id', 'nome_fantasia')
    ->where('empresa_id', $empresaId)
    ->orderBy('nome_fantasia')
    ->get();


        // Setores da empresa (e da filial se selecionada)
        $setoresQuery = DB::table('setores')
            ->select('id', 'nome')
            ->whereNull('deleted_at')
            ->where('empresa_id', $empresaId);

        if ($filialId > 0) {
            $setoresQuery->where('filial_id', $filialId);
        }

        $setores = $setoresQuery->orderBy('nome')->get();

        $cargos = Cargo::query()
            ->with(['cbo'])
            ->where('empresa_id', $empresaId)
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($w) use ($q) {
                    $w->where('titulo', 'ilike', "%{$q}%")
                      ->orWhereHas('cbo', function ($q2) use ($q) {
                          $q2->where('cbo', 'ilike', "%{$q}%")
                             ->orWhere('titulo', 'ilike', "%{$q}%");
                      });
                });
            })
            // Aplica filtros só se as colunas existirem
            ->when($filialId > 0 && Schema::hasColumn('cargos', 'filial_id'), function ($query) use ($filialId) {
                $query->where('filial_id', $filialId);
            })
            ->when($setorId > 0 && Schema::hasColumn('cargos', 'setor_id'), function ($query) use ($setorId) {
                $query->where('setor_id', $setorId);
            })
            ->orderBy('titulo')
            ->paginate(50)
            ->withQueryString();

        $podeCadastrar = $this->temPermissaoFlag($user?->permissao_id, 'cadastro');
        $podeEditar    = $this->temPermissaoFlag($user?->permissao_id, 'editar');

        if ($request->boolean('ajax')) {
            return view('cargos.cargos._table', compact('cargos', 'podeEditar'))->render();
        }

        return view('cargos.cargos.index', compact('cargos', 'podeCadastrar', 'podeEditar', 'filiais', 'setores'));
    }

    public function setoresPorFilial(Request $request)
    {
        $user = Auth::user();
        $empresaId = (int) ($user->empresa_id ?? 0);
        $filialId = (int) $request->query('filial_id', 0);

        if ($empresaId <= 0 || $filialId <= 0) {
            return response()->json([]);
        }

        $setores = DB::table('setores')
            ->select('id', 'nome')
            ->whereNull('deleted_at')
            ->where('empresa_id', $empresaId)
            ->where('filial_id', $filialId)
            ->orderBy('nome')
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
