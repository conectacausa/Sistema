<?php

namespace App\Http\Controllers\Config;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\Empresa;
use App\Models\Permissao;

class GrupoPermissaoController extends Controller
{
    private function empresaIdFromSubdomain(Request $request): int
    {
        $sub = (string) $request->route('sub');

        $empresaId = (int) Empresa::query()
            ->where('subdominio', $sub)
            ->value('id');

        if ($empresaId <= 0) {
            abort(404);
        }

        return $empresaId;
    }

    public function index(Request $request, $sub)
    {
        $empresaId = $this->empresaIdFromSubdomain($request);
        $q = trim((string) $request->get('q', ''));

        $usuariosCountSub = DB::table('usuarios')
            ->select('permissao_id', DB::raw('COUNT(*)::int as total'))
            ->groupBy('permissao_id');

        $grupos = DB::table('permissoes as p')
            ->leftJoinSub($usuariosCountSub, 'uc', function ($join) {
                $join->on('uc.permissao_id', '=', 'p.id');
            })
            ->where('p.empresa_id', $empresaId)
            ->whereNull('p.deleted_at')
            ->when($q !== '', function ($query) use ($q) {
                $query->where('p.nome_grupo', 'ilike', '%' . $q . '%');
            })
            ->orderBy('p.nome_grupo')
            ->select([
                'p.id',
                'p.empresa_id',
                'p.nome_grupo',
                'p.observacoes',
                'p.status',
                'p.salarios',
                'p.created_at',
                'p.updated_at',
                DB::raw('COALESCE(uc.total, 0) as usuarios_count'),
            ])
            ->paginate(10)
            ->withQueryString();

        if ($request->ajax() || $request->boolean('ajax')) {
            return view('config.grupos.partials.tabela', compact('grupos'));
        }

        return view('config.grupos.index', [
            'grupos' => $grupos,
            'q' => $q,
        ]);
    }

    public function create(Request $request, $sub)
    {
        return view('config.grupos.create');
    }

    public function store(Request $request, $sub)
    {
        $empresaId = $this->empresaIdFromSubdomain($request);

        $data = $request->validate([
            'nome_grupo' => ['required', 'string', 'max:160'],
        ]);

        $grupo = new Permissao();
        $grupo->empresa_id = $empresaId;
        $grupo->nome_grupo = $data['nome_grupo'];
        $grupo->status     = true;
        $grupo->salarios   = false;
        $grupo->save();

        return redirect()
            ->route('config.grupos.edit', [
                'sub' => (string) $request->route('sub'),
                'id'  => $grupo->id,
            ])
            ->with('success', 'Grupo criado com sucesso.');
    }

    // ✅ sub antes do id
    public function edit(Request $request, $sub, $id)
    {
        $empresaId = $this->empresaIdFromSubdomain($request);
        $id = (int) $id;

        $grupo = Permissao::query()
            ->where('empresa_id', $empresaId)
            ->where('id', $id)
            ->firstOrFail();

        $usuarios = DB::table('usuarios as u')
            ->select([
                'u.id',
                'u.nome_completo',
                DB::raw("
                    COALESCE(
                        string_agg(
                            COALESCE(f.nome_fantasia, f.nome, (vul.filial_id::text))
                            || ' > ' ||
                            COALESCE(s.nome, (vul.setor_id::text)),
                            '<br>'
                        ) FILTER (WHERE vul.id IS NOT NULL),
                        ''
                    ) as lotacoes_html
                ")
            ])
            ->leftJoin('vinculo_usuario_lotacao as vul', 'vul.usuario_id', '=', 'u.id')
            ->leftJoin('filiais as f', 'f.id', '=', 'vul.filial_id')
            ->leftJoin('setores as s', 's.id', '=', 'vul.setor_id')
            ->where('u.permissao_id', $grupo->id)
            ->groupBy('u.id', 'u.nome_completo')
            ->orderBy('u.nome_completo')
            ->get();

        $modulos = DB::table('modulos as m')
            ->join('vinculo_modulos_empresas as vme', 'vme.modulo_id', '=', 'm.id')
            ->where('vme.empresa_id', $empresaId)
            ->where('vme.ativo', true)
            ->orderBy('vme.ordem')
            ->select('m.id', 'm.nome', 'm.slug', 'm.icone', 'm.ordem', 'm.ativo', 'm.descricao')
            ->get();

        $telas = DB::table('telas')
            ->orderBy('nome_tela')
            ->get(['id', 'nome_tela', 'slug', 'modulo_id']);

        $telasPorModulo = [];
        foreach ($modulos as $m) {
            $telasPorModulo[$m->id] = $telas->where('modulo_id', $m->id)->values();
        }

        $permissoesExistentes = DB::table('permissao_modulo_tela')
            ->where('permissao_id', $grupo->id)
            ->get()
            ->keyBy('tela_id');

        return view('config.grupos.edit', [
            'grupo' => $grupo,
            'usuarios' => $usuarios,
            'modulos' => $modulos,
            'telasPorModulo' => $telasPorModulo,
            'permissoesExistentes' => $permissoesExistentes,
        ]);
    }

    public function update(Request $request, $sub, $id)
    {
        $empresaId = $this->empresaIdFromSubdomain($request);
        $id = (int) $id;

        $grupo = Permissao::query()
            ->where('empresa_id', $empresaId)
            ->where('id', $id)
            ->firstOrFail();

        $data = $request->validate([
            'nome_grupo'   => ['required', 'string', 'max:160'],
            'observacoes'  => ['nullable', 'string'],
            'status'       => ['required'],
            'salarios'     => ['required'],
        ]);

        $grupo->nome_grupo  = $data['nome_grupo'];
        $grupo->observacoes = $data['observacoes'] ?? null;
        $grupo->status      = (string)$data['status'] === '1';
        $grupo->salarios    = (string)$data['salarios'] === '1';
        $grupo->save();

        return back()->with('success', 'Alterações salvas.');
    }

    public function togglePermissao(Request $request, $sub, $id)
    {
        $empresaId = $this->empresaIdFromSubdomain($request);
        $id = (int) $id;

        $grupo = Permissao::query()
            ->where('empresa_id', $empresaId)
            ->where('id', $id)
            ->firstOrFail();

        $v = Validator::make($request->all(), [
            'tela_id' => ['required', 'integer', 'min:1'],
            'campo'   => ['required', 'in:ativo,cadastro,editar'],
            'valor'   => ['required', 'in:0,1'],
        ]);

        if ($v->fails()) {
            return response()->json([
                'ok' => false,
                'message' => 'Dados inválidos.',
                'errors' => $v->errors(),
            ], 422);
        }

        $telaId = (int) $request->input('tela_id');
        $campo  = (string) $request->input('campo');
        $valor  = (string) $request->input('valor') === '1';

        $tela = DB::table('telas')->where('id', $telaId)->first(['id', 'modulo_id']);
        if (!$tela) {
            return response()->json(['ok' => false, 'message' => 'Tela não encontrada.'], 404);
        }

        $row = DB::table('permissao_modulo_tela')
            ->where('permissao_id', $grupo->id)
            ->where('tela_id', $telaId)
            ->first();

        if (!$row && $valor === false) {
            return response()->json(['ok' => true, 'message' => 'Nenhuma alteração necessária.']);
        }

        $finalAtivo    = $row ? (bool)$row->ativo : false;
        $finalCadastro = $row ? (bool)$row->cadastro : false;
        $finalEditar   = $row ? (bool)$row->editar : false;

        if ($campo === 'ativo')    $finalAtivo = $valor;
        if ($campo === 'cadastro') $finalCadastro = $valor;
        if ($campo === 'editar')   $finalEditar = $valor;

        if ($finalAtivo === false && $finalCadastro === false && $finalEditar === false) {
            DB::table('permissao_modulo_tela')
                ->where('permissao_id', $grupo->id)
                ->where('tela_id', $telaId)
                ->delete();

            return response()->json(['ok' => true, 'message' => 'Acesso removido.']);
        }

        $payload = [
            'permissao_id' => $grupo->id,
            'modulo_id'    => $tela->modulo_id,
            'tela_id'      => $telaId,
            'ativo'        => $finalAtivo,
            'cadastro'     => $finalCadastro,
            'editar'       => $finalEditar,
            'updated_at'   => now(),
        ];

        if (!$row) {
            $payload['created_at'] = now();
            DB::table('permissao_modulo_tela')->insert($payload);
        } else {
            DB::table('permissao_modulo_tela')
                ->where('permissao_id', $grupo->id)
                ->where('tela_id', $telaId)
                ->update($payload);
        }

        return response()->json(['ok' => true, 'message' => 'Permissão atualizada.']);
    }

    public function destroy(Request $request, $sub, $id)
    {
        $empresaId = $this->empresaIdFromSubdomain($request);
        $id = (int) $id;

        $grupo = Permissao::query()
            ->where('empresa_id', $empresaId)
            ->where('id', $id)
            ->firstOrFail();

        $grupo->delete();

        return redirect()
            ->route('config.grupos.index', ['sub' => (string)$request->route('sub')])
            ->with('success', 'Grupo excluído.');
    }
}
