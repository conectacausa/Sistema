<?php

namespace App\Http\Controllers\Config;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UsuariosController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | LISTAGEM
    |--------------------------------------------------------------------------
    */
    public function index(Request $request)
    {
        $empresaId = auth()->user()->empresa_id;

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

        // CPF formatado
        $usuarios->getCollection()->transform(function ($u) {
            $cpf = preg_replace('/\D/', '', (string) $u->cpf);
            if (strlen($cpf) === 11) {
                $u->cpf_formatado =
                    substr($cpf, 0, 3) . '.' .
                    substr($cpf, 3, 3) . '.' .
                    substr($cpf, 6, 3) . '-' .
                    substr($cpf, 9, 2);
            } else {
                $u->cpf_formatado = $u->cpf;
            }
            return $u;
        });

        $situacoes = DB::table('usuarios')
            ->where('empresa_id', $empresaId)
            ->whereNull('deleted_at')
            ->groupBy('status')
            ->pluck('status');

        // permissões da tela
        $permissaoTela = DB::table('permissao_modulo_tela')
            ->where('permissao_id', auth()->user()->permissao_id)
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
    public function create(Request $request)
    {
        $empresaId = auth()->user()->empresa_id;
        $usuarioId = $request->query('id');
        $usuario = null;

        if ($usuarioId) {
            $usuario = DB::table('usuarios')
                ->where('empresa_id', $empresaId)
                ->whereNull('deleted_at')
                ->where('id', (int) $usuarioId)
                ->first();
        }

        return view('config.usuarios.create', [
            'usuario' => $usuario,
            'filiais' => $this->getFiliais($empresaId),
            'permissoes' => $this->getPermissoes($empresaId),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | STORE
    |--------------------------------------------------------------------------
    */
    public function store(Request $request)
    {
        $empresaId = auth()->user()->empresa_id;

        $request->validate([
            'nome_completo' => 'required|string|max:255',
            'cpf' => 'required',
            'permissao_id' => 'required|integer',
            'status' => 'required|in:ativo,inativo',
        ]);

        $cpf = preg_replace('/\D/', '', $request->cpf);
        $telefone = preg_replace('/\D/', '', (string) $request->telefone);

        $fotoPath = null;
        if ($request->hasFile('foto')) {
            $fotoPath = $request->file('foto')->store('usuarios', 'public');
        }

        $id = DB::table('usuarios')->insertGetId([
            'empresa_id' => $empresaId,
            'nome_completo' => $request->nome_completo,
            'cpf' => $cpf,
            'permissao_id' => $request->permissao_id,
            'email' => $request->email,
            'telefone' => $telefone,
            'data_expiracao' => $request->data_expiracao ?: null,
            'status' => $request->status,
            'foto' => $fotoPath,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()
            ->route('config.usuarios.create', ['id' => $id])
            ->with('success', 'Usuário cadastrado com sucesso.');
    }

    /*
    |--------------------------------------------------------------------------
    | EDIT
    |--------------------------------------------------------------------------
    */
    public function edit(Request $request, $id)
    {
        $empresaId = (int) ($request->user()->empresa_id ?? 0);
        $id = (int) $id;
    
        $usuario = DB::table('usuarios')
            ->whereNull('deleted_at')
            ->where('empresa_id', $empresaId)
            ->where('id', $id)
            ->first();
    
        if (!$usuario) {
            \Log::error('USUARIOS_EDIT_NAO_ENCONTRADO', [
                'auth_user_id' => $request->user()->id ?? null,
                'auth_empresa_id' => $empresaId,
                'usuario_id' => $id,
                'path' => $request->path(),
                'host' => $request->getHost(),
                'sub' => (string) $request->route('sub'),
            ]);
        
            return redirect()
                ->route('config.usuarios.index')
                ->with('error', 'Usuário não encontrado para esta empresa (verifique tenant/empresa).');
        }
    
        $filiais = DB::table('filiais')
            ->select('id', DB::raw("COALESCE(nome_fantasia, razao_social) as nome"))
            ->where('empresa_id', $empresaId)
            ->whereNull('deleted_at')
            ->orderByRaw("COALESCE(nome_fantasia, razao_social)")
            ->get();
    
        $permissoes = DB::table('permissoes')
            ->select('id', 'nome_grupo')
            ->where('empresa_id', $empresaId)
            ->whereNull('deleted_at')
            ->orderBy('nome_grupo')
            ->get();
    
        // tenta inferir filial/setor inicial (se existir tabela)
        $filialId = null;
        $setorId = null;
    
        try {
            $v = DB::table('vinculo_usuario_lotacao')
                ->whereNull('deleted_at')
                ->where('empresa_id', $empresaId)
                ->where('usuario_id', $usuario->id)
                ->where('ativo', true)
                ->first();
    
            if ($v) {
                $filialId = $v->filial_id ?? null;
                $setorId  = $v->setor_id ?? null;
            }
        } catch (\Throwable $e) {
            // não faz nada
        }
    
        return view('config.usuarios.edit', [
            'usuario' => $usuario,
            'filiais' => $filiais,
            'permissoes' => $permissoes,
            'filialId' => $filialId,
            'setorId' => $setorId,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE
    |--------------------------------------------------------------------------
    */
    public function update(Request $request, $id)
    {
        $empresaId = auth()->user()->empresa_id;

        $request->validate([
            'nome_completo' => 'required|string|max:255',
            'cpf' => 'required',
            'permissao_id' => 'required|integer',
            'status' => 'required|in:ativo,inativo',
        ]);

        $cpf = preg_replace('/\D/', '', $request->cpf);
        $telefone = preg_replace('/\D/', '', (string) $request->telefone);

        $usuario = DB::table('usuarios')
            ->where('empresa_id', $empresaId)
            ->whereNull('deleted_at')
            ->where('id', (int) $id)
            ->first();

        if (!$usuario) {
            return redirect()->route('config.usuarios.index')->with('error', 'Usuário não encontrado.');
        }

        $fotoPath = $usuario->foto;
        if ($request->hasFile('foto')) {
            $fotoPath = $request->file('foto')->store('usuarios', 'public');
        }

        DB::table('usuarios')
            ->where('id', $id)
            ->where('empresa_id', $empresaId)
            ->update([
                'nome_completo' => $request->nome_completo,
                'cpf' => $cpf,
                'permissao_id' => $request->permissao_id,
                'email' => $request->email,
                'telefone' => $telefone,
                'data_expiracao' => $request->data_expiracao ?: null,
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
    | INATIVAR
    |--------------------------------------------------------------------------
    */
    public function inativar($id)
    {
        $empresaId = auth()->user()->empresa_id;

        DB::table('usuarios')
            ->where('empresa_id', $empresaId)
            ->where('id', (int) $id)
            ->update([
                'status' => 'inativo',
                'updated_at' => now(),
            ]);

        return redirect()->route('config.usuarios.index')->with('success', 'Usuário inativado.');
    }

    /*
    |--------------------------------------------------------------------------
    | AJAX
    |--------------------------------------------------------------------------
    */
    public function setoresPorFilial(Request $request)
    {
        return DB::table('setores')
            ->select('id', 'nome')
            ->where('empresa_id', auth()->user()->empresa_id)
            ->where('filial_id', (int) $request->get('filial_id'))
            ->whereNull('deleted_at')
            ->orderBy('nome')
            ->get();
    }

    /*
    |--------------------------------------------------------------------------
    | HELPERS
    |--------------------------------------------------------------------------
    */
    private function getFiliais($empresaId)
    {
        return DB::table('filiais')
            ->select('id', DB::raw("COALESCE(nome_fantasia, razao_social) as nome"))
            ->where('empresa_id', $empresaId)
            ->whereNull('deleted_at')
            ->orderByRaw("COALESCE(nome_fantasia, razao_social)")
            ->get();
    }

    private function getPermissoes($empresaId)
    {
        return DB::table('permissoes')
            ->select('id', 'nome_grupo')
            ->where('empresa_id', $empresaId)
            ->whereNull('deleted_at')
            ->orderBy('nome_grupo')
            ->get();
    }
}
