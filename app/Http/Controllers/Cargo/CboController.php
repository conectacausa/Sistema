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

        // por enquanto deixa true (no próximo passo a gente liga permissão real)
        $podeCadastrar = true;

        // AJAX do filtro/paginação: retorna só a tabela
        if ($request->boolean('ajax')) {
            return view('cargos.cbo._table', compact('cbos'))->render();
        }

        return view('cargos.cbo.index', compact('cbos', 'podeCadastrar'));
    }

    // placeholder do "Novo CBO" (vamos implementar depois)
    public function create()
    {
        return redirect()->route('cargos.cbo.index');
    }
}
