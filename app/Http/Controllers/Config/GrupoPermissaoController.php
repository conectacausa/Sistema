<?php

namespace App\Http\Controllers\Config;

use App\Http\Controllers\Controller;
use App\Models\Permissao;
use Illuminate\Http\Request;

class GrupoPermissaoController extends Controller
{
    public function __construct()
    {
        // Ajuste os nomes conforme seu projeto (ex.: auth, tenant, etc.)
        $this->middleware('auth');

        // Se você já tem middleware de permissão por tela, mantenha aqui.
        // Exemplos comuns:
        // $this->middleware('check.tela:11');
        // $this->middleware('permissao.tela:11');
    }

    public function index(Request $request)
    {
        // tenant (empresa) - seguindo o padrão que vocês já usam
        $tenant = app()->bound('tenant') ? app('tenant') : null;
        $empresaId = $tenant->id ?? (auth()->user()->empresa_id ?? null);

        $q = Permissao::query()
            ->where('empresa_id', $empresaId)
            ->orderBy('id', 'desc')
            ->withCount('usuarios');

        if ($request->filled('nome_grupo')) {
            $nome = trim((string) $request->nome_grupo);
            $q->where('nome_grupo', 'ilike', "%{$nome}%"); // PostgreSQL
        }

        $grupos = $q->paginate(15)->withQueryString();

        return view('config.grupos.index', compact('grupos'));
    }
}
