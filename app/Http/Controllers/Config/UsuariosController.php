<?php

namespace App\Http\Controllers\Config;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsuariosController extends Controller
{
    private function getAcoesTela10(): array
    {
        $permissaoId = auth()->user()->permissao_id;

        $acao = DB::table('permissao_modulo_tela')
            ->select('cadastro', 'editar')
            ->where('permissao_id', $permissaoId)
            ->where('tela_id', 10)
            ->where('ativo', true)
            ->first();

        return [
            'podeCadastrar' => (bool) ($acao->cadastro ?? false),
            'podeEditar'    => (bool) ($acao->editar ?? false),
        ];
    }

    public function index(Request $request)
    {
        $empresaId = auth()->user()->empresa_id;

        $busca = trim((string) $request->get('q', ''));
        $situacao = trim((string) $request->get('status', ''));

        $query = DB::table('usuarios as u')
            ->leftJoin('permissoes as p', 'p.id', '=', 'u.permissao_id')
            ->select([
                'u.id',
                'u.nome_completo',
                'u.cpf',
                'u.status',
                'p.nome_grupo as grupo_permissao',
            ])
            ->whereNull('u.deleted_at')
            ->where('u.empresa_id', $empresaId);

        if ($busca !== '') {
            $cpfSomenteNumeros = preg_replace('/\D+/', '', $busca);

            $query->where(function ($q) use ($busca, $cpfSomenteNumeros) {
                $q->whereRaw('LOWER(u.nome_completo) LIKE ?', ['%' . mb_strtolower($busca) . '%']);

                if ($cpfSomenteNumeros !== '') {
                    $q->orWhere('u.cpf', $cpfSomenteNumeros);
                }
            });
        }

        if ($situacao !== '') {
            $query->where('u.status', $situacao);
        }

        // Ordenação: ativos primeiro, depois inativos, depois por nome
        $usuarios = $query
            ->orderByRaw("CASE WHEN LOWER(u.status) = 'ativo' THEN 0 ELSE 1 END")
            ->orderBy('u.nome_completo')
            ->paginate(10)
            ->appends($request->query());

        $situacoes = DB::table('usuarios')
            ->select('status')
            ->whereNull('deleted_at')
            ->where('empresa_id', $empresaId)
            ->whereNotNull('status')
            ->groupBy('status')
            ->orderBy('status')
            ->pluck('status');

        $acoes = $this->getAcoesTela10();

        return view('config.usuarios.index', [
            'usuarios' => $usuarios,
            'situacoes' => $situacoes,
            'busca' => $busca,
            'situacaoSelecionada' => $situacao,
            'podeCadastrar' => $acoes['podeCadastrar'],
            'podeEditar' => $acoes['podeEditar'],
        ]);
    }

    public function create()
    {
        $empresaId = auth()->user()->empresa_id;

        $acoes = $this->getAcoesTela10();
        if (!$acoes['podeCadastrar']) {
            return redirect()
                ->route('config.usuarios.index')
                ->with('error', 'Você não tem permissão para cadastrar usuários.');
        }

        $permissoes = DB::table('permissoes')
            ->select('id', 'nome_grupo')
            ->whereNull('deleted_at')
            ->where('empresa_id', $empresaId)
            ->where('status', true)
            ->orderBy('nome_grupo')
            ->get();

        return view('config.usuarios.create', [
            'permissoes' => $permissoes,
        ]);
    }

    public function store(Request $request)
    {
        $empresaId = auth()->user()->empresa_id;

        $acoes = $this->getAcoesTela10();
        if (!$acoes['podeCadastrar']) {
            return redirect()
                ->route('config.usuarios.index')
                ->with('error', 'Você não tem permissão para cadastrar usuários.');
        }

        $cpf = preg_replace('/\D+/', '', (string) $request->input('cpf', ''));

        $validated = $request->validate([
            'nome_completo' => ['required', 'string', 'max:255'],
            'cpf'           => ['required'],
            'email'         => ['nullable', 'email', 'max:190'],
            'telefone'      => ['nullable', 'string', 'max:30'],
            'permissao_id'  => ['required', 'integer'],
            'status'        => ['required', 'in:ativo,inativo'],
            'senha'         => ['required', 'string', 'min:6', 'max:255'],
        ]);

        if (strlen($cpf) !== 11) {
            return back()->withErrors(['cpf' => 'CPF inválido.'])->withInput();
        }

        $cpfExiste = DB::table('usuarios')
            ->whereNull('deleted_at')
            ->where('empresa_id', $empresaId)
            ->where('cpf', $cpf)
            ->exists();

        if ($cpfExiste) {
            return back()->withErrors(['cpf' => 'Já existe um usuário com este CPF nesta empresa.'])->withInput();
        }

        $permValida = DB::table('permissoes')
            ->whereNull('deleted_at')
            ->where('empresa_id', $empresaId)
            ->where('id', (int) $validated['permissao_id'])
            ->where('status', true)
            ->exists();

        if (!$permValida) {
            return back()->withErrors(['permissao_id' => 'Grupo de permissão inválido para esta empresa.'])->withInput();
        }

        DB::table('usuarios')->insert([
            'nome_completo'     => $validated['nome_completo'],
            'cpf'               => $cpf,
            'empresa_id'        => $empresaId,
            'permissao_id'      => (int) $validated['permissao_id'],
            'email'             => $validated['email'] ?? null,
            'telefone'          => $validated['telefone'] ?? null,
            'senha'             => Hash::make($validated['senha']),
            'status'            => $validated['status'],
            'salarios'          => false,
            'operador_whatsapp' => false,
            'created_at'        => now(),
            'updated_at'        => now(),
        ]);

        return redirect()
            ->route('config.usuarios.index')
            ->with('success', 'Usuário cadastrado com sucesso.');
    }

    public function edit($id)
    {
        $empresaId = auth()->user()->empresa_id;

        $acoes = $this->getAcoesTela10();
        if (!$acoes['podeEditar']) {
            return redirect()
                ->route('config.usuarios.index')
                ->with('error', 'Você não tem permissão para editar usuários.');
        }

        $usuario = DB::table('usuarios')
            ->whereNull('deleted_at')
            ->where('empresa_id', $empresaId)
            ->where('id', $id)
            ->first();

        if (!$usuario) {
            return redirect()->route('config.usuarios.index')->with('error', 'Usuário não encontrado.');
        }

        $permissoes = DB::table('permissoes')
            ->select('id', 'nome_grupo')
            ->whereNull('deleted_at')
            ->where('empresa_id', $empresaId)
            ->where('status', true)
            ->orderBy('nome_grupo')
            ->get();

        return view('config.usuarios.edit', [
            'usuario' => $usuario,
            'permissoes' => $permissoes,
        ]);
    }

    public function update(Request $request, $id)
    {
        $empresaId = auth()->user()->empresa_id;

        $acoes = $this->getAcoesTela10();
        if (!$acoes['podeEditar']) {
            return redirect()
                ->route('config.usuarios.index')
                ->with('error', 'Você não tem permissão para editar usuários.');
        }

        $usuario = DB::table('usuarios')
            ->whereNull('deleted_at')
            ->where('empresa_id', $empresaId)
            ->where('id', $id)
            ->first();

        if (!$usuario) {
            return redirect()->route('config.usuarios.index')->with('error', 'Usuário não encontrado.');
        }

        $cpf = preg_replace('/\D+/', '', (string) $request->input('cpf', ''));

        $validated = $request->validate([
            'nome_completo' => ['required', 'string', 'max:255'],
            'cpf'           => ['required'],
            'email'         => ['nullable', 'email', 'max:190'],
            'telefone'      => ['nullable', 'string', 'max:30'],
            'permissao_id'  => ['required', 'integer'],
            'status'        => ['required', 'in:ativo,inativo'],
            'senha'         => ['nullable', 'string', 'min:6', 'max:255'],
        ]);

        if (strlen($cpf) !== 11) {
            return back()->withErrors(['cpf' => 'CPF inválido.'])->withInput();
        }

        $cpfExiste = DB::table('usuarios')
            ->whereNull('deleted_at')
            ->where('empresa_id', $empresaId)
            ->where('cpf', $cpf)
            ->where('id', '<>', $id)
            ->exists();

        if ($cpfExiste) {
            return back()->withErrors(['cpf' => 'Já existe outro usuário com este CPF nesta empresa.'])->withInput();
        }

        $permValida = DB::table('permissoes')
            ->whereNull('deleted_at')
            ->where('empresa_id', $empresaId)
            ->where('id', (int) $validated['permissao_id'])
            ->where('status', true)
            ->exists();

        if (!$permValida) {
            return back()->withErrors(['permissao_id' => 'Grupo de permissão inválido para esta empresa.'])->withInput();
        }

        $updateData = [
            'nome_completo' => $validated['nome_completo'],
            'cpf'           => $cpf,
            'email'         => $validated['email'] ?? null,
            'telefone'      => $validated['telefone'] ?? null,
            'permissao_id'  => (int) $validated['permissao_id'],
            'status'        => $validated['status'],
            'updated_at'    => now(),
        ];

        if (!empty($validated['senha'])) {
            $updateData['senha'] = Hash::make($validated['senha']);
        }

        DB::table('usuarios')
            ->where('empresa_id', $empresaId)
            ->where('id', $id)
            ->whereNull('deleted_at')
            ->update($updateData);

        return redirect()
            ->route('config.usuarios.index')
            ->with('success', 'Usuário atualizado com sucesso.');
    }

    public function destroy($id)
    {
        $empresaId = auth()->user()->empresa_id;

        // Sugestão: se quiser amarrar exclusão à permissão "editar"
        $acoes = $this->getAcoesTela10();
        if (!$acoes['podeEditar']) {
            return redirect()
                ->route('config.usuarios.index')
                ->with('error', 'Você não tem permissão para excluir usuários.');
        }

        $updated = DB::table('usuarios')
            ->where('id', $id)
            ->where('empresa_id', $empresaId)
            ->whereNull('deleted_at')
            ->update([
                'deleted_at' => now(),
                'updated_at' => now(),
            ]);

        if (!$updated) {
            return redirect()
                ->route('config.usuarios.index')
                ->with('error', 'Não foi possível excluir o usuário (registro não encontrado).');
        }

        return redirect()
            ->route('config.usuarios.index')
            ->with('success', 'Usuário excluído com sucesso.');
    }
}
