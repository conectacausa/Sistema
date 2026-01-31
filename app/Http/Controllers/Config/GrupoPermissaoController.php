<?php

namespace App\Http\Controllers\Config;

use App\Http\Controllers\Controller;
use App\Models\Permissao;
use Illuminate\Http\Request;

class GrupoPermissaoController extends Controller
{
    public function index(Request $request)
    {
        $tenant = app()->bound('tenant') ? app('tenant') : null;
        $empresaId = $tenant->id ?? (auth()->user()->empresa_id ?? null);

        $query = Permissao::query()
            ->where('empresa_id', $empresaId)
            ->orderBy('id', 'desc');

        // filtro por nome do grupo
        if ($request->filled('nome_grupo')) {
            $nome = trim((string) $request->nome_grupo);
            $query->where('nome_grupo', 'ilike', "%{$nome}%"); // PostgreSQL
        }

        // você ainda pode colocar comCount depois que ligar usuarios_count
        $grupos = $query->paginate(15)->withQueryString();

        return view('config.grupos.index', compact('grupos'));
    }
}
