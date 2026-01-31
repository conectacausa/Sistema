<?php

namespace App\Http\Controllers\Config;

use App\Http\Controllers\Controller;
use App\Models\Empresa;
use App\Models\Permissao;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class GrupoPermissaoController extends Controller
{
    private function empresaFromSub(): Empresa
    {
        $sub = (string) request()->route('sub');

        if ($sub === '') {
            abort(403, 'Subdomínio não identificado na rota.');
        }

        $empresa = Empresa::query()
            ->where('subdominio', $sub)
            ->first();

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
            'sub' => request()->route('sub'),
            'id'  => $grupo->id,
        ]);
    }

  public function edit(Request $request, $id)
{
    // ✅ DIAGNÓSTICO VISÍVEL
    if ($request->query('diag') == '1') {
        $sub = (string) request()->route('sub');
        $empresa = \App\Models\Empresa::query()->where('subdominio', $sub)->first();

        $params = request()->route() ? request()->route()->parameters() : [];

        $conn = \Illuminate\Support\Facades\DB::connection();
        $dbName = method_exists($conn, 'getDatabaseName') ? $conn->getDatabaseName() : null;

        $rawId = $id;
        $idInt = (int) $id;

        $grupo = \App\Models\Permissao::query()->find($idInt);

        return response()->json([
            'reached' => true,
            'url' => $request->fullUrl(),
            'route_uri' => request()->route()?->uri(),
            'route_name' => request()->route()?->getName(),
            'route_params' => $params,

            'route_sub' => $sub,
            'empresa_found' => (bool) $empresa,
            'empresa_id' => $empresa?->id,

            'db_name' => $dbName,

            'raw_id' => $rawId,
            'grupo_id_int' => $idInt,
            'grupo_found' => (bool) $grupo,
            'grupo_empresa_id' => $grupo?->empresa_id,
        ], 200);
    }

    // fluxo normal
    $empresa = $this->empresaFromSub();
    $id = (int) $id;

    $grupo = \App\Models\Permissao::query()->findOrFail($id);

    if ((int)$grupo->empresa_id !== (int)$empresa->id) {
        abort(403);
    }

    return view('config.grupos.edit', compact('grupo'));
}


    public function update(Request $request, $id)
    {
        $empresa = $this->empresaFromSub();
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
            'sub' => request()->route('sub'),
            'id'  => $grupo->id,
        ])->with('success', 'Grupo atualizado com sucesso!');
    }
}
