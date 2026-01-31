<?php

namespace App\Http\Controllers\Config;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UsuariosController extends Controller
{
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

        return view('config.usuarios.index', [
            'usuarios' => $usuarios,
            'situacoes' => $situacoes,
            'busca' => $busca,
            'situacaoSelecionada' => $situacao,
        ]);
    }

    public function edit($id)
    {
        return redirect()
            ->route('config.usuarios.index')
            ->with('warning', 'Tela de edição ainda não implementada.');
    }

    public function destroy($id)
    {
        $empresaId = auth()->user()->empresa_id;

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
                ->with('error', 'Não foi possível excluir o usuário.');
        }

        return redirect()
            ->route('config.usuarios.index')
            ->with('success', 'Usuário excluído com sucesso.');
    }
}
