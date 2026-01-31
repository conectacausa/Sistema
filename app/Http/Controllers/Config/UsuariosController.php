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

        // Query base – simples e segura
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

    /**
     * Tela de novo usuário (placeholder)
     * Evita erro fatal ao clicar em "Novo Usuário"
     */
    public function create()
    {
        return view('config.usuarios.create');
    }
}
