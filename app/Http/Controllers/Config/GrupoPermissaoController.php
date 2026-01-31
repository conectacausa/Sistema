<?php

namespace App\Http\Controllers\Config;

use App\Http\Controllers\Controller;
use App\Models\Empresa;
use App\Models\Permissao;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class GrupoPermissaoController extends Controller
{
    /**
     * Empresa do contexto do request.
     * Prioriza subdomínio. Se não resolver, tenta pelo usuário.
     */
    private function empresaIdFromContext(): int
    {
        // 1) Pelo subdomínio {sub}
        $sub = (string) request()->route('sub');
        if ($sub !== '') {
            $empresa = Empresa::query()
                ->where('subdominio', $sub)
                ->first();

            if ($empresa) {
                return (int) $empresa->id;
            }
        }

        // 2) Fallback pelo usuário logado
        if (auth()->check() && !empty(auth()->user()->empresa_id)) {
            return (int) auth()->user()->empresa_id;
        }

        abort(403, 'Não foi possível identificar a empresa do contexto (subdomínio/usuário).');
    }

    public function index(Request $request)
    {
        $empresaId = $this->empresaIdFromContext();

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

    public function create()
    {
        return view('config.grupos.create');
    }

    public function store(Request $request)
    {
        $empresaId = $this->empresaIdFromContext();

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

        $grupo = Permissao::create([
            'empresa_id'  => $empresaId,
            'nome_grupo'  => $validated['nome_grupo'],
            'observacoes' => null,
            'status'      => true,
            'salarios'    => false,
        ]);

        return redirect()
            ->route('config.grupos.edit', [
                'sub' => request()->route('sub'),
                'id'  => $grupo->id,
            ])
            ->with('success', 'Grupo criado com sucesso!');
    }

    public function edit($id)
    {
        $empresaId = $this->empresaIdFromContext();
        $id = (int) $id;

        // ✅ busca SEM filtrar por empresa para não dar 404 “misterioso”
        $grupo = Permissao::query()->findOrFail($id);

        // ✅ se não for da empresa do contexto, retorna 403 explicando
        if ((int) $grupo->empresa_id !== (int) $empresaId) {
            abort(403, "Grupo {$grupo->id} pertence à empresa_id={$grupo->empresa_id}, mas o contexto atual é empresa_id={$empresaId} (sub=".(string)request()->route('sub').").");
        }

        return view('config.grupos.edit', compact('grupo'));
    }

    public function update(Request $request, $id)
    {
        $empresaId = $this->empresaIdFromContext();
        $id = (int) $id;

        $grupo = Permissao::query()->findOrFail($id);

        if ((int) $grupo->empresa_id !== (int) $empresaId) {
            abort(403, "Grupo {$grupo->id} pertence à empresa_id={$grupo->empresa_id}, mas o contexto atual é empresa_id={$empresaId}.");
        }

        $validated = $request->validate([
            'nome_grupo' => [
                'required',
                'string',
                'max:160',
                Rule::unique('permissoes', 'nome_grupo')
                    ->ignore($grupo->id)
                    ->where(function ($q) use ($empresaId) {
                        return $q->where('empresa_id', $empresaId)->whereNull('deleted_at');
                    }),
            ],
        ], [
            'nome_grupo.required' => 'Informe o nome do grupo.',
            'nome_grupo.max' => 'O nome do grupo deve ter no máximo 160 caracteres.',
            'nome_grupo.unique' => 'Já existe um grupo com esse nome.',
        ]);

        $grupo->update([
            'nome_grupo' => $validated['nome_grupo'],
        ]);

        return redirect()
            ->route('config.grupos.edit', [
                'sub' => request()->route('sub'),
                'id'  => $grupo->id,
            ])
            ->with('success', 'Grupo atualizado com sucesso!');
    }
}
