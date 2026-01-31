<?php

namespace App\Http\Controllers\Config;

use App\Http\Controllers\Controller;
use App\Models\Empresa;
use App\Models\Permissao;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class GrupoPermissaoController extends Controller
{
    private function empresaFromSub(string $sub): Empresa
    {
        if ($sub === '') {
            abort(403, 'Subdomínio não identificado.');
        }

        $empresa = Empresa::query()
            ->where('subdominio', $sub)
            ->first();

        if (!$empresa) {
            abort(403, "Empresa não encontrada para subdominio='{$sub}'.");
        }

        return $empresa;
    }

    public function index(Request $request, $sub)
    {
        $empresa = $this->empresaFromSub((string) $sub);

        $query = Permissao::query()
            ->where('empresa_id', $empresa->id)
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

    public function create(Request $request, $sub)
    {
        // só precisa do layout
        return view('config.grupos.create');
    }

    public function store(Request $request, $sub)
    {
        $empresa = $this->empresaFromSub((string) $sub);

        $validated = $request->validate([
            'nome_grupo' => [
                'required',
                'string',
                'max:160',
                Rule::unique('permissoes', 'nome_grupo')->where(function ($q) use ($empresa) {
                    return $q->where('empresa_id', $empresa->id)->whereNull('deleted_at');
                }),
            ],
        ], [
            'nome_grupo.required' => 'Informe o nome do grupo.',
            'nome_grupo.max' => 'O nome do grupo deve ter no máximo 160 caracteres.',
            'nome_grupo.unique' => 'Já existe um grupo com esse nome.',
        ]);

        $grupo = Permissao::create([
            'empresa_id'  => $empresa->id,
            'nome_grupo'  => $validated['nome_grupo'],
            'observacoes' => null,
            'status'      => true,
            'salarios'    => false,
        ]);

        return redirect()->route('config.grupos.edit', [
            'sub' => (string) $sub,
            'id'  => $grupo->id,
        ])->with('success', 'Grupo criado com sucesso!');
    }

    // ✅ AQUI está a correção principal: recebe $sub e $id
    public function edit(Request $request, $sub, $id)
    {
        $empresa = $this->empresaFromSub((string) $sub);
        $id = (int) $id;

        $grupo = Permissao::query()->findOrFail($id);

        if ((int) $grupo->empresa_id !== (int) $empresa->id) {
            abort(403);
        }

        return view('config.grupos.edit', compact('grupo'));
    }

    // ✅ idem
    public function update(Request $request, $sub, $id)
    {
        $empresa = $this->empresaFromSub((string) $sub);
        $id = (int) $id;

        $grupo = Permissao::query()->findOrFail($id);

        if ((int) $grupo->empresa_id !== (int) $empresa->id) {
            abort(403);
        }

        $validated = $request->validate([
            'nome_grupo' => [
                'required',
                'string',
                'max:160',
                Rule::unique('permissoes', 'nome_grupo')
                    ->ignore($grupo->id)
                    ->where(function ($q) use ($empresa) {
                        return $q->where('empresa_id', $empresa->id)->whereNull('deleted_at');
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

        return redirect()->route('config.grupos.edit', [
            'sub' => (string) $sub,
            'id'  => $grupo->id,
        ])->with('success', 'Grupo atualizado com sucesso!');
    }

    // ✅ se você tem destroy na rota listada, precisa também receber $sub
    public function destroy(Request $request, $sub, $id)
    {
        $empresa = $this->empresaFromSub((string) $sub);
        $id = (int) $id;

        $grupo = Permissao::query()->findOrFail($id);

        if ((int) $grupo->empresa_id !== (int) $empresa->id) {
            abort(403);
        }

        $grupo->delete();

        return redirect()->route('config.grupos.index', ['sub' => (string) $sub])
            ->with('success', 'Grupo removido com sucesso!');
    }
}
