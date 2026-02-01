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

        // CPF formatado
        $usuarios->getCollection()->transform(function ($u) {
            $cpf = preg_replace('/\D/', '', (string) ($u->cpf ?? ''));
            $u->cpf_formatado = strlen($cpf) === 11
                ? substr($cpf, 0, 3) . '.' . substr($cpf, 3, 3) . '.' . substr($cpf, 6, 3) . '-' . substr($cpf, 9, 2)
                : $u->cpf;
            return $u;
        });

        $situacoes = DB::table('usuarios')
            ->where('empresa_id', $empresaId)
            ->whereNull('deleted_at')
            ->groupBy('status')
            ->pluck('status');

        $permissaoTela = DB::table('permissao_modulo_tela')
            ->where('permissao_id', (int) auth()->user()->permissao_id)
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
        $empresaId = (int) auth()->user()->empresa_id;

        return view('config.usuarios.create', [
            'filiais' => $this->getFiliais($empresaId),
            'permissoes' => $this->getPermissoes($empresaId),
            'filialId' => null,
            'setorId' => null,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | STORE
    |--------------------------------------------------------------------------
    */
    public function store(Request $request, string $sub)
    {
        $empresaId = (int) auth()->user()->empresa_id;

        $request->validate([
            'nome_completo' => 'required|string|max:255',
            'cpf' => 'required|string|max:20',
            'permissao_id' => 'required|integer',
            'email' => 'nullable|string|max:190',
            'telefone' => 'nullable|string|max:30',
            'data_expiracao' => 'nullable',
            'status' => 'required|in:ativo,inativo',
            'foto' => 'nullable|image|max:2048',

            // ✅ vínculo
            'filial_id' => 'nullable|integer',
            'setor_id'  => 'nullable|integer',
        ]);

        $cpf = preg_replace('/\D/', '', $request->cpf);
        $telefone = preg_replace('/\D/', '', $request->telefone ?? '');

        $fotoPath = $request->hasFile('foto')
            ? $request->file('foto')->store('usuarios', 'public')
            : null;

        DB::beginTransaction();
        try {
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

            // ✅ Salvar vínculo filial/setor (se informado)
            $filialId = (int) ($request->filial_id ?? 0);
            $setorId  = (int) ($request->setor_id ?? 0);

            if ($filialId > 0 && $setorId > 0) {
                // desativa vínculos anteriores
                DB::table('vinculo_usuario_lotacao')
                    ->where('empresa_id', $empresaId)
                    ->where('usuario_id', $id)
                    ->whereNull('deleted_at')
                    ->update([
                        'ativo' => false,
                        'updated_at' => now(),
                    ]);

                // ativa/cria vínculo
                $existente = DB::table('vinculo_usuario_lotacao')
                    ->where('empresa_id', $empresaId)
                    ->where('usuario_id', $id)
                    ->where('filial_id', $filialId)
                    ->where('setor_id', $setorId)
                    ->whereNull('deleted_at')
                    ->first();

                if ($existente) {
                    DB::table('vinculo_usuario_lotacao')
                        ->where('id', $existente->id)
                        ->update([
                            'ativo' => true,
                            'updated_at' => now(),
                        ]);
                } else {
                    DB::table('vinculo_usuario_lotacao')->insert([
                        'empresa_id' => $empresaId,
                        'usuario_id' => $id,
                        'filial_id' => $filialId,
                        'setor_id' => $setorId,
                        'ativo' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('config.usuarios.edit', ['id' => $id])
                ->with('success', 'Usuário cadastrado com sucesso.');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('USUARIO_STORE_ERRO', [
                'empresaId' => $empresaId,
                'sub' => $sub,
                'error' => $e->getMessage(),
            ]);

            return back()->withInput()->with('error', 'Não foi possível salvar o usuário.');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | EDIT
    |--------------------------------------------------------------------------
    */
    public function edit(Request $request, string $sub, int $id)
    {
        $empresaId = (int) auth()->user()->empresa_id;

        $usuario = DB::table('usuarios')
            ->whereNull('deleted_at')
            ->where('empresa_id', $empresaId)
            ->where('id', $id)
            ->first();

        if (!$usuario) {
            Log::error('USUARIO_EDIT_NAO_ENCONTRADO', compact('id', 'empresaId', 'sub'));
            return redirect()->route('config.usuarios.index')
                ->with('error', 'Usuário não encontrado.');
        }

        // Inferir filial e setor
        $filialId = null;
        $setorId = null;

        try {
            $v = DB::table('vinculo_usuario_lotacao')
                ->where('empresa_id', $empresaId)
                ->where('usuario_id', $id)
                ->where('ativo', true)
                ->whereNull('deleted_at')
                ->first();

            if ($v) {
                $filialId = $v->filial_id;
                $setorId = $v->setor_id;
            }
        } catch (\Throwable $e) {}

        return view('config.usuarios.edit', [
            'usuario' => $usuario,
            'filiais' => $this->getFiliais($empresaId),
            'permissoes' => $this->getPermissoes($empresaId),
            'filialId' => $filialId,
            'setorId' => $setorId,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE
    |--------------------------------------------------------------------------
    */
    public function update(Request $request, string $sub, int $id)
    {
        $empresaId = (int) auth()->user()->empresa_id;

        $request->validate([
            'nome_completo' => 'required|string|max:255',
            'cpf' => 'required|string|max:20',
            'permissao_id' => 'required|integer',
            'email' => 'nullable|string|max:190',
            'telefone' => 'nullable|string|max:30',
            'data_expiracao' => 'nullable',
            'status' => 'required|in:ativo,inativo',
            'foto' => 'nullable|image|max:2048',

            // ✅ vínculo
            'filial_id' => 'nullable|integer',
            'setor_id'  => 'nullable|integer',
        ]);

        $usuario = DB::table('usuarios')
            ->where('empresa_id', $empresaId)
            ->where('id', $id)
            ->whereNull('deleted_at')
            ->first();

        if (!$usuario) {
            return redirect()->route('config.usuarios.index')
                ->with('error', 'Usuário não encontrado.');
        }

        $cpf = preg_replace('/\D/', '', $request->cpf);
        $telefone = preg_replace('/\D/', '', $request->telefone ?? '');

        $fotoPath = $usuario->foto;
        if ($request->hasFile('foto')) {
            $fotoPath = $request->file('foto')->store('usuarios', 'public');
        }

        DB::beginTransaction();
        try {
            DB::table('usuarios')
                ->where('empresa_id', $empresaId)
                ->where('id', $id)
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

            // ✅ Salvar vínculo filial/setor
            $filialId = (int) ($request->filial_id ?? 0);
            $setorId  = (int) ($request->setor_id ?? 0);

            if ($filialId <= 0 || $setorId <= 0) {
                // se não selecionou, desativa todos
                DB::table('vinculo_usuario_lotacao')
                    ->where('empresa_id', $empresaId)
                    ->where('usuario_id', $id)
                    ->whereNull('deleted_at')
                    ->update([
                        'ativo' => false,
                        'updated_at' => now(),
                    ]);
            } else {
                // desativa anteriores
                DB::table('vinculo_usuario_lotacao')
                    ->where('empresa_id', $empresaId)
                    ->where('usuario_id', $id)
                    ->whereNull('deleted_at')
                    ->update([
                        'ativo' => false,
                        'updated_at' => now(),
                    ]);

                // ativa/cria selecionado
                $existente = DB::table('vinculo_usuario_lotacao')
                    ->where('empresa_id', $empresaId)
                    ->where('usuario_id', $id)
                    ->where('filial_id', $filialId)
                    ->where('setor_id', $setorId)
                    ->whereNull('deleted_at')
                    ->first();

                if ($existente) {
                    DB::table('vinculo_usuario_lotacao')
                        ->where('id', $existente->id)
                        ->update([
                            'ativo' => true,
                            'updated_at' => now(),
                        ]);
                } else {
                    DB::table('vinculo_usuario_lotacao')->insert([
                        'empresa_id' => $empresaId,
                        'usuario_id' => $id,
                        'filial_id' => $filialId,
                        'setor_id' => $setorId,
                        'ativo' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('config.usuarios.edit', ['id' => $id])
                ->with('success', 'Usuário atualizado com sucesso.');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('USUARIO_UPDATE_ERRO', [
                'empresaId' => $empresaId,
                'sub' => $sub,
                'id' => $id,
                'error' => $e->getMessage(),
            ]);

            return back()->withInput()->with('error', 'Não foi possível salvar o usuário.');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | INATIVAR
    |--------------------------------------------------------------------------
    */
    public function inativar(Request $request, string $sub, int $id)
    {
        DB::table('usuarios')
            ->where('id', $id)
            ->whereNull('deleted_at')
            ->update([
                'status' => 'inativo',
                'updated_at' => now(),
            ]);

        return redirect()->route('config.usuarios.index')
            ->with('success', 'Usuário inativado.');
    }

    /*
    |--------------------------------------------------------------------------
    | AJAX
    |--------------------------------------------------------------------------
    */
    public function setoresPorFilial(Request $request, string $sub)
    {
        $empresaId = (int) auth()->user()->empresa_id;
        $filialId = (int) $request->get('filial_id');

        return DB::table('setores')
            ->where('empresa_id', $empresaId)
            ->where('filial_id', $filialId)
            ->whereNull('deleted_at')
            ->orderBy('nome')
            ->get();
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
            ->orderBy('nome')
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
