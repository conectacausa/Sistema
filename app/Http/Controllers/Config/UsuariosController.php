<?php

namespace App\Http\Controllers\Config;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UsuariosController extends Controller
{
    /**
     * Lista de usuários
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

        // Filtro por nome ou CPF
        if ($busca !== '') {
            $cpf = preg_replace('/\D/', '', $busca);

            $query->where(function ($q) use ($busca, $cpf) {
                $q->where('u.nome_completo', 'ILIKE', '%' . $busca . '%');

                if ($cpf !== '') {
                    $q->orWhere('u.cpf', $cpf);
                }
            });
        }

        // Filtro por situação
        if ($situacao !== '') {
            $query->where('u.status', $situacao);
        }

        // Ordenação: ativos primeiro, depois nome
        $usuarios = $query
            ->orderByRaw("CASE WHEN u.status = 'ativo' THEN 0 ELSE 1 END")
            ->orderBy('u.nome_completo')
            ->paginate(10)
            ->appends($request->query());

        // CPF formatado (seguro)
        $usuarios->getCollection()->transform(function ($u) {
            $cpf = preg_replace('/\D+/', '', (string)($u->cpf ?? ''));

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

        // Situações disponíveis
        $situacoes = DB::table('usuarios')
            ->where('empresa_id', $empresaId)
            ->whereNull('deleted_at')
            ->groupBy('status')
            ->pluck('status');

        // Permissões da tela
        $podeCadastrar = false;
        $podeEditar = false;

        $permissaoTela = DB::table('permissao_modulo_tela')
            ->where('permissao_id', auth()->user()->permissao_id)
            ->where('tela_id', 10)
            ->where('ativo', true)
            ->first();

        if ($permissaoTela) {
            $podeCadastrar = (bool) $permissaoTela->cadastro;
            $podeEditar    = (bool) $permissaoTela->editar;
        }

        return view('config.usuarios.index', [
            'usuarios' => $usuarios,
            'situacoes' => $situacoes,
            'busca' => $busca,
            'situacaoSelecionada' => $situacao,
            'podeCadastrar' => $podeCadastrar,
            'podeEditar' => $podeEditar,
        ]);
    }

    public function create()
    {
        return view('config.usuarios.create');
    }

    /**
     * Placeholder do store (pra não quebrar)
     */
    public function store(Request $request)
    {
        return redirect()
            ->route('config.usuarios.index')
            ->with('success', 'Cadastro ainda não implementado.');
    }

    /**
     * Tela de edição (preenchida)
     */
    public function edit($id)
    {
        $id = (int) $id;
        $empresaId = auth()->user()->empresa_id;

        $usuario = DB::table('usuarios')
            ->whereNull('deleted_at')
            ->where('empresa_id', $empresaId)
            ->where('id', $id)
            ->first();

        if (!$usuario) {
            return redirect()
                ->route('config.usuarios.index')
                ->with('error', 'Usuário não encontrado.');
        }

        // CPF formatado para exibir no input
        $cpf = preg_replace('/\D+/', '', (string)($usuario->cpf ?? ''));
        $cpf_formatado = (strlen($cpf) === 11)
            ? substr($cpf, 0, 3) . '.' . substr($cpf, 3, 3) . '.' . substr($cpf, 6, 3) . '-' . substr($cpf, 9, 2)
            : ($usuario->cpf ?? '');

        // Lista de grupos/permissões
        $permissoes = DB::table('permissoes')
            ->select('id', 'nome_grupo')
            ->orderBy('nome_grupo')
            ->get();

        return view('config.usuarios.edit', [
            'usuario' => $usuario,
            'cpf_formatado' => $cpf_formatado,
            'permissoes' => $permissoes,
        ]);
    }

    /**
     * Salvar edição
     */
    public function update(Request $request, $id)
    {
        $id = (int) $id;
        $empresaId = auth()->user()->empresa_id;

        $request->validate([
            'nome_completo' => ['required', 'string', 'max:255'],
            'cpf'           => ['required', 'string', 'max:20'],
            'permissao_id'  => ['required', 'integer'],
            'status'        => ['required', 'in:ativo,inativo'],
        ]);

        $cpf = preg_replace('/\D+/', '', (string)$request->cpf);

        DB::table('usuarios')
            ->where('id', $id)
            ->where('empresa_id', $empresaId)
            ->update([
                'nome_completo' => $request->nome_completo,
                'cpf' => $cpf,
                'permissao_id' => (int)$request->permissao_id,
                'status' => $request->status,
                'updated_at' => now(),
            ]);

        return redirect()
            ->route('config.usuarios.index')
            ->with('success', 'Usuário atualizado com sucesso.');
    }

    /**
     * Inativar usuário (com confirmação no front)
     */
    public function inativar($id)
    {
        $id = (int) $id;
        $empresaId = auth()->user()->empresa_id;

        DB::table('usuarios')
            ->where('id', $id)
            ->where('empresa_id', $empresaId)
            ->update([
                'status' => 'inativo',
                'updated_at' => now(),
            ]);

        return redirect()
            ->route('config.usuarios.index')
            ->with('success', 'Usuário inativado com sucesso.');
    }
}
