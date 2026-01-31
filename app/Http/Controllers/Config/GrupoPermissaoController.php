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
            ->withCount('usuarios')
            ->orderBy('id', 'desc');

        if ($request->filled('nome_grupo')) {
            $nome = trim((string) $request->nome_grupo);
            $query->where('nome_grupo', 'ilike', "%{$nome}%");
        }

        $grupos = $query->paginate(15)->withQueryString();

        return view('config.grupos.index', compact('grupos'));
    }

    // próximos passos (vamos implementar depois)
    public function create() { return view('config.grupos.create'); }
    public function store(Request $request) { abort(501); }
    public function edit($id) { abort(501); }
    public function update(Request $request, $id) { abort(501); }
    public function destroy($id) { abort(501); }
}
