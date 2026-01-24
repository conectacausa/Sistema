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
        $q = trim((string) $request->get('q', ''));

        // Busca por título do cargo e também pelo CBO (código ou título do cbo)
        $cargos = Cargo::query()
            ->with(['cbo'])
            ->when($q !== '', function ($query) use ($q) {
                $query->where('titulo', 'ilike', "%{$q}%")
                      ->orWhereHas('cbo', function ($q2) use ($q) {
                          $q2->where('cbo', 'ilike', "%{$q}%")
                             ->orWhere('titulo', 'ilike', "%{$q}%");
                      });
            })
            ->orderBy('titulo')
            ->paginate(50)
            ->withQueryString();

        $user = Auth::user();

        $podeCadastrar = $this->temPermissaoFlag($user?->permissao_id, 'cadastro');
        $podeEditar    = $this->temPermissaoFlag($user?->permissao_id, 'editar');

        if ($request->boolean('ajax')) {
            return view('cargos.cargos._table', compact('cargos', 'podeEditar'))->render();
        }

        return view('cargos.cargos.index', compact('cargos', 'podeCadastrar', 'podeEditar'));
    }

    // placeholders
    public function create()
    {
        return redirect()->route('cargos.cargos.index');
    }

    public function edit($id)
    {
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
