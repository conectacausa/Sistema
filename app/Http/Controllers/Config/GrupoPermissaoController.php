<?php

namespace App\Http\Controllers\Config;

use App\Http\Controllers\Controller;
use App\Models\Empresa;
use App\Models\Permissao;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class GrupoPermissaoController extends Controller
{
    private function empresaFromSub(): Empresa
    {
        $sub = (string) request()->route('sub');

        if ($sub === '') {
            abort(403, 'Subdomínio não identificado na rota (route sub vazio).');
        }

        $empresa = Empresa::query()->where('subdominio', $sub)->first();

        if (!$empresa) {
            abort(403, "Empresa não encontrada para subdominio='{$sub}'.");
        }

        return $empresa;
    }

    public function index(Request $request)
    {
        $empresa = $this->empresaFromSub();

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

    public function create()
    {
        return view('config.grupos.create');
    }

    public function store(Request $request)
    {
        $empresa = $this->empresaFromSub();

        $validated = $request->validate([
            'nome_grupo' => [
                'required',
                'string',
                'max:160',
                Rule::unique('permissoes', 'nome_grupo')->where(function ($q) use ($empresa) {
                    return $q->where('empresa_id', $empresa->id)->whereNull('deleted_at');
                }),
            ],
        ]);

        $grupo = Permissao::create([
            'empresa_id'  => $empresa->id,
            'nome_grupo'  => $validated['nome_grupo'],
            'observacoes' => null,
            'status'      => true,
            'salarios'    => false,
        ]);

        return redirect()->route('config.grupos.edit', [
            'sub' => request()->route('sub'),
            'id'  => $grupo->id,
        ]);
    }

    /**
     * ✅ EDIT DIAGNÓSTICO: não pode dar 404 "mudo".
     */
    public function edit($id)
    {
        $sub = (string) request()->route('sub');
        $empresa = $this->empresaFromSub();

        $idInt = (int) $id;

        // Busca o grupo SEM filtrar por empresa primeiro
        $grupo = Permissao::query()->find($idInt);

        if (!$grupo) {
            abort(404, "DIAG: Grupo id={$idInt} não existe. sub={$sub} empresa_id_contexto={$empresa->id}");
        }

        if ((int)$grupo->empresa_id !== (int)$empresa->id) {
            abort(403, "DIAG: Grupo id={$grupo->id} pertence empresa_id={$grupo->empresa_id}, mas contexto sub={$sub} é empresa_id={$empresa->id}");
        }

        // Se chegou aqui, está tudo certo: abre view
        return view('config.grupos.edit', compact('grupo'));
    }

    public function update(Request $request, $id)
    {
        $empresa = $this->empresaFromSub();
        $idInt = (int) $id;

        $grupo = Permissao::query()->findOrFail($idInt);

        if ((int)$grupo->empresa_id !== (int)$empresa->id) {
            abort(403, "DIAG: Grupo id={$grupo->id} pertence empresa_id={$grupo->empresa_id}, mas contexto empresa_id={$empresa->id}");
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
        ]);

        $grupo->update(['nome_grupo' => $validated['nome_grupo']]);

        return redirect()->route('config.grupos.edit', [
            'sub' => request()->route('sub'),
            'id'  => $grupo->id,
        ]);
    }

    public function destroy($id)
    {
        abort(501);
    }
}
