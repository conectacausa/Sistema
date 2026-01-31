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
            ->orderBy('nome_grupo');

        if ($request->filled('nome_grupo')) {
            $query->where(
                'nome_grupo',
                'ilike',
                '%' . trim($request->nome_grupo) . '%'
            );
        }

        $grupos = $query->paginate(10)->withQueryString();

        // Se for AJAX, retorna apenas a tabela
        if ($request->ajax() || $request->boolean('ajax')) {
            return view('config.grupos.partials.table', compact('grupos'));
        }

        return view('config.grupos.index', compact('grupos'));
    }
}
