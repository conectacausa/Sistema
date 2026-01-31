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
        $acao = DB::table('permissao_modulo_tela')
            ->select('cadastro', 'editar')
            ->where('permissao_id', auth()->user()->permissao_id)
            ->where('tela_id', 10)
            ->where('ativo', true)
            ->first();

        return [
            'podeCadastrar' => (bool)($acao->cadastro ?? false),
            'podeEditar'    => (bool)($acao->editar ?? false),
        ];
    }

    public function index(Request $request)
    {
        $empresaId = auth()->user()->empresa_id;

        $busca    = trim((string)$request->q);
        $situacao = trim((string)$request->status);

        $usuarios = DB::table('usuarios as u')
            ->leftJoin('permissoes as p', 'p.id', '=', 'u.permissao_id')
            ->selectRaw("
                u.id,
                u.nome_completo,
                CASE 
                    WHEN length(u.cpf) = 11 THEN
                        substring(u.cpf from 1 for 3) || '.' ||
                        substring(u.cpf from 4 for 3) || '.' ||
                        substring(u.cpf from 7 for 3) || '-' ||
                        substring(u.cpf from 10 for 2)
                    ELSE u.cpf
                END as cpf_formatado,
                u.status,
                p.nome_grupo as grupo_permissao
            ")
            ->whereNull('u.deleted_at')
            ->where('u.empresa_id', $empresaId)
            ->when($busca, function ($q) use ($busca) {
                $cpf = preg_replace('/\D/', '', $busca);
                $q->where(function ($qq) use ($busca, $cpf) {
                    $qq->whereRaw('LOWER(u.nome_completo) LIKE ?', ['%' . mb_strtolower($busca) . '%']);
                    if ($cpf) {
                        $qq->orWhere('u.cpf', $cpf);
                    }
                });
            })
            ->when($situacao, fn($q) => $q->where('u.status', $situacao))
            ->orderByRaw("CASE WHEN u.status = 'ativo' THEN 0 ELSE 1 END")
            ->orderBy('u.nome_completo')
            ->paginate(10)
            ->appends($request->query());

        $situacoes = DB::table('usuarios')
            ->where('empresa_id', $empresaId)
            ->whereNull('deleted_at')
            ->groupBy('status')
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

    public function inativar($id)
    {
        $id = (int)$id;
        if ($id <= 0) {
            return redirect()->route('config.usuarios.index')
                ->with('error', 'Parâmetro inválido.');
        }

        DB::table('usuarios')
            ->where('id', $id)
            ->where('empresa_id', auth()->user()->empresa_id)
            ->update([
                'status' => 'inativo',
                'updated_at' => now(),
            ]);

        return redirect()->route('config.usuarios.index')
            ->with('success', 'Usuário inativado com sucesso.');
    }
}
