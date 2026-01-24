<?php

namespace App\Http\Controllers\Cargo;

use App\Http\Controllers\Controller;
use App\Models\Cbo;
use Illuminate\Http\Request;

class CboController extends Controller
{
    public function index(Request $request)
{
    $q = trim((string) $request->get('q', ''));

    $cbos = Cbo::query()
        ->when($q !== '', function ($query) use ($q) {
            $query->where('cbo', 'ilike', "%{$q}%")
                  ->orWhere('titulo', 'ilike', "%{$q}%");
        })
        ->orderBy('titulo')
        ->paginate(50)
        ->withQueryString();

    $user = Auth::user();

    $podeCadastrar = false;

    if ($user && $user->permissao_id) {
        $podeCadastrar = DB::table('permissao_modulo_tela')
            ->where('permissao_id', $user->permissao_id)
            ->where('tela_id', 6)
            ->where('ativo', true)
            ->where('cadastro', true)
            ->exists();
    }

    // AJAX do filtro/paginação
    if ($request->boolean('ajax')) {
        return view('cargos.cbo._table', compact('cbos'))->render();
    }

    return view('cargos.cbo.index', compact('cbos', 'podeCadastrar'));
}


    public function create()
    {
        // placeholder - vamos implementar depois
        return redirect()->route('cargos.cbo.index');
    }
}
