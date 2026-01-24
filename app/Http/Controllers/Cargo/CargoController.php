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

        // segurança: se usuário não tem empresa_id, não mostra nada
        if ($empresaId <= 0) {
            $cargos = Cargo::query()->whereRaw('1=0')->paginate(50);
            $podeCadastrar = false;
            $podeEditar = false;
            return view('cargos.cargos.index', compact('cargos', 'podeCadastrar', 'podeEditar'));
        }

        $q = trim((string) $request->get('q', ''));

        $cargos = Cargo::query()
            ->with(['cbo'])
            ->where('empresa_id', $empresaId) // ✅ FILTRA PELA EMPRESA DO USUÁRIO
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($w) use ($q) {
                    $w->where('titulo', 'ilike', "%{$q}%")
                      ->orWhereHas('cbo', function ($q2) use ($q) {
                          $q2->where('cbo', 'ilike', "%{$q}%")
                             ->orWhere('titulo', 'ilike', "%{$q}%");
                      });
                });
            })
            ->orderBy('titulo')
            ->paginate(50)
            ->withQueryString();

        $podeCadastrar = $this->temPermissaoFlag($user?->permissao_id, 'cadastro');
        $podeEditar    = $this->temPermissaoFlag($user?->permissao_id, 'editar');

        if ($request->boolean('ajax')) {
            return view('cargos.cargos._table', compact('cargos', 'podeEditar'))->render();
        }

        return view('cargos.cargos.index', compact('cargos', 'podeCadastrar', 'podeEditar'));
    }

    public function create()
    {
        // placeholder por enquanto
        return redirect()->route('cargos.cargos.index');
    }

    public function edit($id)
    {
        $user = Auth::user();
        $empresaId = (int) ($user->empresa_id ?? 0);

        // ✅ BLOQUEIA ACESSO A CARGO DE OUTRA EMPRESA
        $cargo = Cargo::query()->where('id', (int)$id)->first();
        if (!$cargo || (int)$cargo->empresa_id !== $empresaId) {
            return redirect()->route('cargos.cargos.index');
        }

        // placeholder por enquanto (quando formos criar a tela de editar)
        return redirect()->route('cargos.cargos.index');
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
