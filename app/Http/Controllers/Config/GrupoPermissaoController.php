<?php

namespace App\Http\Controllers\Config;

use App\Http\Controllers\Controller;
use App\Models\Permissao;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class GrupoPermissaoController extends Controller
{
    private function empresaId(): int
    {
        // Seu middleware tenant já deve setar app('tenant')
        $tenant = app()->bound('tenant') ? app('tenant') : null;

        // Prioriza tenant (mais confiável)
        if ($tenant && isset($tenant->id) && $tenant->id) {
            return (int) $tenant->id;
        }

        // Fallback (se existir no usuário)
        if (auth()->check() && isset(auth()->user()->empresa_id) && auth()->user()->empresa_id) {
            return (int) auth()->user()->empresa_id;
        }

        // Se cair aqui, é problema de contexto tenant/user
        abort(403, 'Empresa não definida no contexto do tenant.');
    }

    public function index(Request $request)
    {
        $empresaId = $this->empresaId();

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
        $empresaId = $this->empresaId();

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
            ->route('config.grupos.edit', ['id' => $grupo->id])
            ->with('success', 'Grupo criado com sucesso!');
    }

    public function edit($id)
    {
        $empresaId = $this->empresaId();
        $id = (int) $id;

        // ✅ IMPORTANTÍSSIMO: filtra por empresa_id + id na mesma query
        $grupo = Permissao::query()
            ->where('empresa_id', $empresaId)
            ->where('id', $id)
            ->firstOrFail();

        return view('config.grupos.edit', compact('grupo'));
    }

    public function update(Request $request, $id)
    {
        $empresaId = $this->empresaId();
        $id = (int) $id;

        $grupo = Permissao::query()
            ->where('empresa_id', $empresaId)
            ->where('id', $id)
            ->firstOrFail();

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
            ->route('config.grupos.edit', ['id' => $grupo->id])
            ->with('success', 'Grupo atualizado com sucesso!');
    }
}
