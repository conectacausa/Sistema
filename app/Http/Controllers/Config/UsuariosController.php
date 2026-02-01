<?php

namespace App\Http\Controllers\Config;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UsuariosController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | LISTAGEM
    |--------------------------------------------------------------------------
    */
    public function index(Request $request, string $sub)
    {
        $empresaId = (int) (auth()->user()->empresa_id ?? 0);

        $busca    = trim((string) $request->get('q', ''));
        $situacao = trim((string) $request->get('status', ''));

        $query = DB::table('usuarios as u')
            ->leftJoin('permissoes as p', 'p.id', '=', 'u.permissao_id')
            ->select(
                'u.id',
                'u.nome_completo',
                'u.cpf',
                'u.status',
                'p.nome_grupo as grupo_permissao'
            )
            ->whereNull('u.deleted_at')
            ->where('u.empresa_id', $empresaId);

        if ($busca !== '') {
            $cpf = preg_replace('/\D/', '', $busca);

            $query->where(function ($q) use ($busca, $cpf) {
                $q->where('u.nome_completo', 'ILIKE', "%{$busca}%");
                if ($cpf !== '') {
                    $q->orWhere('u.cpf', $cpf);
                }
            });
        }

        if ($situacao !== '') {
            $query->where('u.status', $situacao);
        }

        $usuarios = $query
            ->orderByRaw("CASE WHEN u.status = 'ativo' THEN 0 ELSE 1 END")
            ->orderBy('u.nome_completo')
            ->paginate(10)
            ->appends($request->query());

        $usuarios->getCollection()->transform(function ($u) {
            $cpf = preg_replace('/\D/', '', (string) ($u->cpf ?? ''));
            if (strlen($cpf) === 11) {
                $u->cpf_formatado =
                    substr($cpf, 0, 3) . '.' .
                    substr($cpf, 3, 3) . '.' .
                    substr($cpf, 6, 3) . '-' .
                    substr($cpf, 9, 2);
            } else {
                $u->cpf_formatado = $u->cpf ?? '';
            }
            return $u;
        });

        $situacoes = DB::table('usuarios')
            ->where('empresa_id', $empresaId)
            ->whereNull('deleted_at')
            ->groupBy('status')
            ->pluck('status');

        $permissaoTela = DB::table('permissao_modulo_tela')
            ->where('permissao_id', (int) (auth()->user()->permissao_id ?? 0))
            ->where('tela_id', 10)
            ->where('ativo', true)
            ->first();

        return view('config.usuarios.index', [
            'usuarios' => $usuarios,
            'situacoes' => $situacoes,
            'busca' => $busca,
            'situacaoSelecionada' => $situacao,
            'podeCadastrar' => (bool) ($permissaoTela->cadastro ?? false),
            'podeEditar' => (bool) ($permissaoTela->editar ?? false),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | CREATE
    |--------------------------------------------------------------------------
    */
    public function create(Request $request, string $sub)
    {
        $empresaId = (int) (auth()->user()->empresa_id ?? 0);

        return view('config.usuarios.create', [
            'filiais' => $this->getFiliais($empresaId),
            'permissoes' => $this->getPermissoes($empresaId),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | STORE
    |--------------------------------------------------------------------------
    */
    public function store(Request $request, string $sub)
    {
        $empresaId = (int) (auth()->user()->empresa_id ?? 0);

        $request->validate([
            'nome_completo' => ['required', 'string', 'max:255'],
            'cpf'           => ['required', 'string', 'max:20'],
            'permissao_id'  => ['required', 'integer'],
            'email'         => ['nullable', 'string', 'max:190'],
            'telefone'      => ['nullable', 'string', 'max:30'],
            'data_expiracao'=> ['nullable'],
            'status'        => ['required', 'in:ativo,inativo'],
            'foto'          => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $cpf = preg_replace('/\D+/', '', (string) $request->cpf);
        $telefone = preg_replace('/\D+/', '', (string) $request->telefone);

        $dataExp = $request->data_expiracao ?: null;
        if ($dataExp) {
            $dataExp = str_replace('T', ' ', $dataExp) . ':00';
        }

        $fotoPath = null;
        if ($request->hasFile('foto')) {
            $fotoPath = $request->file('foto')->store('usuarios', 'public');
        }

        $id = DB::table('usuarios')->insertGetId([
            'empresa_id' => $empresaId,
            'nome_completo' => $request->nome_completo,
            'cpf' => $cpf,
            'permissao_id' => (int) $request->permissao_id,
            'email' => $request->email,
            'telefone' => $telefone,
            'data_expiracao' => $dataExp,
            'status' => $request->status,
            'foto' => $fotoPath,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()
            ->route('config.usuarios.edit', ['id' => $id])
            ->with('success', 'Usuário cadastrado com sucesso.');
    }

    /*
    |--------------------------------------------------------------------------
    | EDIT  ✅ (sub + id)
    |--------------------------------------------------------------------------
    */
    public function edit(Request $request, string $sub, int $id)
    {
        $empresaId = (int) (auth()->user()->empresa_id ?? 0);

        $usuario = DB::table('usuarios')
            ->whereNull('deleted_at')
            ->where('empresa_id', $empresaId)
            ->where('id', $id)
            ->first();

        if (!$usuario) {
            Log::error('USUARIOS_EDIT_NAO_ENCONTRADO', [
                'sub' => $sub,
                'auth_user_id' => auth()->id(),
                'auth_empresa_id' => $empresaId,
                'usuario_id' => $id,
                'path' => $request->path(),
                'host' => $request->getHost(),
                'route_params' => $request->route()?->parameters() ?? [],
            ]);

            return redirect()
                ->route('config.usuarios.index')
                ->with('error', 'Usuário não encontrado para esta empresa.');
        }

        $filiais = $this->getFiliais($empresaId);
        $permissoes = $this->getPermissoes($empresaId);

        return view('config.usuarios.edit', [
            'usuario' => $usuario,
            'filiais' => $filiais,
            'permissoes' => $permissoes,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE ✅ (sub + id)
    |--------------------------------------------------------------------------
    */
    public function update(Request $request, string $sub, int $id)
    {
        $empresaId = (int) (auth()->user()->empresa_id ?? 0);

        $request->validate([
            'nome_completo' => ['required', 'string', 'max:255'],
            'cpf'           => ['required', 'string', 'max:20'],
            'permissao_id'  => ['required', 'integer'],
            'email'         => ['nullable', 'string', 'max:190'],
            'telefone'      => ['nullable', 'string', 'max:30'],
            'data_expiracao'=> ['nullable'],
            'status'        => ['required', 'in:ativo,inativo'],
            'foto'          => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $usuarioAtual = DB::table('usuarios')
            ->whereNull('deleted_at')
            ->where('empresa_id', $empresaId)
            ->where('id', $id)
            ->first();

        if (!$usuarioAtual) {
            return redirect()
                ->route('config.usuarios.index')
                ->with('error', 'Usuário não encontrado para atualizar.');
        }

        $cpf = preg_replace('/\D+/', '', (string) $request->cpf);
        $telefone = preg_replace('/\D+/', '', (string) $request->telefone);

        $dataExp = $request->data_expiracao ?: null;
        if ($dataExp) {
            $dataExp = str_replace('T', ' ', $dataExp) . ':00';
        }

        $fotoPath = $usuarioAtual->foto ?? null;
        if ($request->hasFile('foto')) {
            $fotoPath = $request->file('foto')->store('usuarios', 'public');
        }

        DB::table('usuarios')
            ->where('empresa_id', $empresaId)
            ->where('id', $id)
            ->update([
                'nome_completo' => $request->nome_completo,
                'cpf' => $cpf,
                'permissao_id' => (int) $request->permissao_id,
                'email' => $request->email,
                'telefone' => $telefone,
                'data_expiracao' => $dataExp,
                'status' => $request->status,
                'foto' => $fotoPath,
                'updated_at' => now(),
            ]);

        return redirect()
            ->route('config.usuarios.edit', ['id' => $id])
            ->with('success', 'Usuário atualizado com sucesso.');
    }

    /*
    |--------------------------------------------------------------------------
    | INATIVAR ✅ (sub + id)
    |--------------------------------------------------------------------------
    */
    public function inativar(Request $request, string $sub, int $id)
    {
        $empresaId = (int) (auth()->user()->empresa_id ?? 0);

        DB::table('usuarios')
            ->where('empresa_id', $empresaId)
            ->where('id', $id)
            ->whereNull('deleted_at')
            ->update([
                'status' => 'inativo',
                'updated_at' => now(),
            ]);

        return redirect()
            ->route('config.usuarios.index')
            ->with('success', 'Usuário inativado com sucesso.');
    }

    /*
    |--------------------------------------------------------------------------
    | AJAX: Setores por filial
    |--------------------------------------------------------------------------
    */
    public function setoresPorFilial(Request $request, string $sub)
    {
        $empresaId = (int) (auth()->user()->empresa_id ?? 0);
        $filialId = (int) $request->get('filial_id', 0);

        if ($filialId <= 0) {
            return response()->json([]);
        }

        $setores = DB::table('setores')
            ->select('id', 'nome')
            ->where('empresa_id', $empresaId)
            ->where('filial_id', $filialId)
            ->whereNull('deleted_at')
            ->orderBy('nome')
            ->get();

        return response()->json($setores);
    }

    /*
    |--------------------------------------------------------------------------
    | HELPERS
    |--------------------------------------------------------------------------
    */
    private function getFiliais(int $empresaId)
    {
        return DB::table('filiais')
            ->select('id', DB::raw("COALESCE(nome_fantasia, razao_social) as nome"))
            ->where('empresa_id', $empresaId)
            ->whereNull('deleted_at')
            ->orderByRaw("COALESCE(nome_fantasia, razao_social)")
            ->get();
    }

    private function getPermissoes(int $empresaId)
    {
        return DB::table('permissoes')
            ->select('id', 'nome_grupo')
            ->where('empresa_id', $empresaId)
            ->whereNull('deleted_at')
            ->orderBy('nome_grupo')
            ->get();
    }
}
