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

    /**
     * ✅ EDIT com abas: Grupo / Usuários / Permissões
     */
    public function edit(Request $request, $sub, $id)
    {
        $empresa = $this->empresaFromSub((string) $sub);
        $id = (int) $id;

        $grupo = Permissao::query()->findOrFail($id);
        if ((int) $grupo->empresa_id !== (int) $empresa->id) {
            abort(403);
        }

        /**
         * -----------------------------
         * ABA USUÁRIOS (do grupo)
         * -----------------------------
         * Assumimos: usuarios.permissao_id = permissoes.id
         * + Tentativa de montar "Filial > Setor" via vinculo_usuario_lotacao.
         * Se suas colunas/tabelas tiverem nomes diferentes, ajuste aqui.
         */
        $usuarios = DB::table('usuarios as u')
            ->where('u.permissao_id', $grupo->id)
            ->leftJoin('vinculo_usuario_lotacao as vul', 'vul.usuario_id', '=', 'u.id')
            ->leftJoin('filiais as f', 'f.id', '=', 'vul.filial_id')
            ->leftJoin('setores as s', 's.id', '=', 'vul.setor_id')
            ->select([
                'u.id',
                'u.nome',
                DB::raw("
                    COALESCE(
                        string_agg(
                            COALESCE(f.nome, f.nome_filial, (vul.filial_id::text)) || ' > ' ||
                            COALESCE(s.nome, s.nome_setor, (vul.setor_id::text)),
                            '<br>'
                        ) FILTER (WHERE vul.id IS NOT NULL),
                        ''
                    ) as lotacoes_html
                ")
            ])
            ->groupBy('u.id', 'u.nome')
            ->orderBy('u.nome')
            ->get();

        /**
         * -----------------------------
         * ABA PERMISSÕES
         * -----------------------------
         * - Pega módulos vinculados à empresa (vinculo_modulos_empresas.ativo=true)
         * - Para cada módulo, pega telas
         * - Pega permissões existentes para este grupo em permissao_modulo_tela
         */
        $modulos = DB::table('modulos as m')
            ->join('vinculo_modulos_empresas as vme', 'vme.modulo_id', '=', 'm.id')
            ->where('vme.empresa_id', $empresa->id)
            ->where('vme.ativo', true)
            ->orderBy('vme.ordem')
            ->orderBy('m.ordem')
            ->select([
                'm.id',
                'm.nome',
                'm.slug',
                'm.icone',
                'm.ordem',
                'm.ativo',
                'm.descricao',
                'vme.ordem as ordem_empresa',
            ])
            ->get();

        $telas = DB::table('telas')
            ->whereIn('modulo_id', $modulos->pluck('id')->all())
            ->orderBy('modulo_id')
            ->orderBy('nome_tela')
            ->get();

        $permissoesExistentes = DB::table('permissao_modulo_tela')
            ->where('permissao_id', $grupo->id)
            ->get()
            ->keyBy('tela_id'); // facilita check

        // Monta estrutura módulo -> telas
        $telasPorModulo = $telas->groupBy('modulo_id');

        return view('config.grupos.edit', [
            'grupo' => $grupo,
            'usuarios' => $usuarios,
            'modulos' => $modulos,
            'telasPorModulo' => $telasPorModulo,
            'permissoesExistentes' => $permissoesExistentes,
        ]);
    }

    /**
     * ✅ Salva: dados do grupo + permissões (grade)
     */
    public function update(Request $request, $sub, $id)
    {
        $empresa = $this->empresaFromSub((string) $sub);
        $id = (int) $id;

        $grupo = Permissao::query()->findOrFail($id);
        if ((int) $grupo->empresa_id !== (int) $empresa->id) {
            abort(403);
        }

        $validated = $request->validate([
            'nome_grupo' => ['required', 'string', 'max:160'],
            'observacoes' => ['nullable', 'string'],
            'status' => ['required', 'in:0,1'],
            'salarios' => ['required', 'in:0,1'],
            'perm' => ['array'],
        ], [
            'nome_grupo.required' => 'Informe o nome do grupo.',
        ]);

        $grupo->update([
            'nome_grupo' => $validated['nome_grupo'],
            'observacoes' => $validated['observacoes'] ?? null,
            'status' => ((string) $validated['status'] === '1'),
            'salarios' => ((string) $validated['salarios'] === '1'),
        ]);

        /**
         * Atualiza permissões:
         * perm[tela_id][ativo|cadastro|editar] = 1
         */
        $perm = $request->input('perm', []);
        if (!is_array($perm)) $perm = [];

        $telaIds = array_map('intval', array_keys($perm));
        if (!empty($telaIds)) {

            $telas = DB::table('telas')
                ->whereIn('id', $telaIds)
                ->get(['id', 'modulo_id']);

            $telaToModulo = $telas->keyBy('id');

            foreach ($telaIds as $telaId) {
                $row = $perm[$telaId] ?? [];

                $ativo = isset($row['ativo']) ? true : false;
                $cadastro = isset($row['cadastro']) ? true : false;
                $editar = isset($row['editar']) ? true : false;

                // Se não marcou nada, podemos manter registro como ativo=false ou deletar.
                // Aqui: se nada marcado, delete para limpar.
                if (!$ativo && !$cadastro && !$editar) {
                    DB::table('permissao_modulo_tela')
                        ->where('permissao_id', $grupo->id)
                        ->where('tela_id', $telaId)
                        ->delete();
                    continue;
                }

                $moduloId = (int) ($telaToModulo[$telaId]->modulo_id ?? 0);
                if ($moduloId <= 0) continue;

                // Upsert (Postgres): tenta atualizar; se não existe, insere
                $exists = DB::table('permissao_modulo_tela')
                    ->where('permissao_id', $grupo->id)
                    ->where('tela_id', $telaId)
                    ->exists();

                $payload = [
                    'permissao_id' => $grupo->id,
                    'modulo_id' => $moduloId,
                    'tela_id' => $telaId,
                    'ativo' => $ativo,
                    'cadastro' => $cadastro,
                    'editar' => $editar,
                    'updated_at' => now(),
                ];

                if ($exists) {
                    DB::table('permissao_modulo_tela')
                        ->where('permissao_id', $grupo->id)
                        ->where('tela_id', $telaId)
                        ->update($payload);
                } else {
                    $payload['created_at'] = now();
                    DB::table('permissao_modulo_tela')->insert($payload);
                }
            }
        }

        return redirect()->route('config.grupos.edit', [
            'sub' => (string) $sub,
            'id'  => $grupo->id,
        ])->with('success', 'Grupo atualizado com sucesso!');
    }
}
