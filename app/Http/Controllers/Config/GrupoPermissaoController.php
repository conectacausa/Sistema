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
     * Resolve a empresa pelo subdomínio da rota: {sub}.conecttarh.com.br
     */
    private function empresaIdFromSub(): int
    {
        $sub = (string) request()->route('sub');

        if ($sub === '') {
            abort(403, 'Subdomínio (tenant) não identificado.');
        }

        $empresa = Empresa::query()
            ->where('subdominio', $sub)
            ->first();

        if (!$empresa) {
            abort(403, 'Empresa não encontrada para este subdomínio.');
        }

        return (int) $empresa->id;
    }

    public function index(Request $request)
    {
        $empresaId = $this->empresaIdFromSub();

        $query = Permissao::query()
            ->where('empresa_id', $empresaId)
            ->withCount('usuarios')
            ->orderBy('nome_grupo');

        if ($request->filled('nome_grupo')) {
            $nome = trim((string) $request->nome_grupo);
            $query->where('nome_grupo', 'ilike', "%{$nome}%");
        }

        $grupos = $query->paginate(10)->withQueryString();

        // AJAX retorna só a tabela
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
        $empresaId = $this->empresaIdFromSub();

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

        // ✅ Redireciona para editar com o {sub} correto
        return redirect()
            ->route('config.grupos.edit', [
                'sub' => request()->route('sub'),
                'id'  => $grupo->id,
            ])
            ->with('success', 'Grupo criado com sucesso!');
    }

    public function edit($id)
    {
        $empresaId = $this->empresaIdFromSub();
        $id = (int) $id;

        $grupo = Permissao::query()
            ->where('empresa_id', $empresaId)
            ->where('id', $id)
            ->firstOrFail();

        return view('config.grupos.edit', compact('grupo'));
    }

    public function update(Request $request, $id)
    {
        $empresaId = $this->empresaIdFromSub();
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
            ->route('config.grupos.edit', [
                'sub' => request()->route('sub'),
                'id'  => $grupo->id,
            ])
            ->with('success', 'Grupo atualizado com sucesso!');
    }
}
