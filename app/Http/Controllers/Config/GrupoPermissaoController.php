<?php

namespace App\Http\Controllers\Config;

use App\Http\Controllers\Controller;
use App\Models\Permissao;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class GrupoPermissaoController extends Controller
{
    public function index(Request $request)
    {
        $tenant = app()->bound('tenant') ? app('tenant') : null;
        $empresaId = $tenant->id ?? (auth()->user()->empresa_id ?? null);

        $query = Permissao::query()
            ->where('empresa_id', $empresaId)
            ->withCount('usuarios')
            ->orderBy('nome_grupo');

        if ($request->filled('nome_grupo')) {
            $nome = trim((string) $request->nome_grupo);
            $query->where('nome_grupo', 'ilike', "%{$nome}%");
        }

        $grupos = $query->paginate(10)->withQueryString();

        if ($request->ajax() || $request->boolean('ajax')) {
            return view('config.grupos.partials.tabela', compact('grupos'));
        }

        return view('config.grupos.index', compact('grupos'));
    }

    public function create(Request $request)
    {
        return view('config.grupos.create');
    }

    public function store(Request $request)
    {
        $tenant = app()->bound('tenant') ? app('tenant') : null;
        $empresaId = $tenant->id ?? (auth()->user()->empresa_id ?? null);

        $validated = $request->validate([
            'nome_grupo' => [
                'required',
                'string',
                'max:160',
                Rule::unique('permissoes', 'nome_grupo')->where(function ($q) use ($empresaId) {
                    return $q->where('empresa_id', $empresaId)->whereNull('deleted_at');
                }),
            ],
        ], [
            'nome_grupo.required' => 'Informe o nome do grupo.',
            'nome_grupo.max' => 'O nome do grupo deve ter no máximo 160 caracteres.',
            'nome_grupo.unique' => 'Já existe um grupo com esse nome.',
        ]);

        Permissao::create([
            'empresa_id'   => $empresaId,
            'nome_grupo'   => $validated['nome_grupo'],
            'observacoes'  => null,
            'status'       => true,
            'salarios'     => false,
        ]);

        return redirect()
            ->route('config.grupos.index')
            ->with('success', 'Grupo criado com sucesso!');
    }
}
