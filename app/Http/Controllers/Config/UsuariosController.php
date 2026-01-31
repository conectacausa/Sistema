<?php

namespace App\Http\Controllers\Config;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

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

        if ($busca !== '') {
            $cpf = preg_replace('/\D/', '', $busca);

            $query->where(function ($q) use ($busca, $cpf) {
                $q->where('u.nome_completo', 'ILIKE', '%' . $busca . '%');
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

        // CPF formatado para a view
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

        $situacoes = DB::table('usuarios')
            ->where('empresa_id', $empresaId)
            ->whereNull('deleted_at')
            ->groupBy('status')
            ->pluck('status');

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
     * Tela de novo usuário (com suporte a ?id= para habilitar lotação após salvar)
     */
    public function create(Request $request)
    {
        $empresaId = auth()->user()->empresa_id;
        $usuarioId = $request->query('id'); // ?id=xx após salvar
        $usuario = null;

        if ($usuarioId) {
            $usuario = DB::table('usuarios')
                ->whereNull('deleted_at')
                ->where('empresa_id', $empresaId)
                ->where('id', (int)$usuarioId)
                ->first();
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

        return view('config.usuarios.create', [
            'usuario' => $usuario,
            'filiais' => $filiais,
            'permissoes' => $permissoes,
        ]);
    }

    /**
     * Salvar novo usuário
     */
    public function store(Request $request)
    {
        $empresaId = auth()->user()->empresa_id;

        $request->validate([
            'nome_completo' => ['required', 'string', 'max:255'],
            'cpf'           => ['required', 'string', 'max:20'],
            'permissao_id'  => ['required', 'integer'],
            'filial_id'     => ['nullable', 'integer'],
            'setor_id'      => ['nullable', 'integer'],
            'email'         => ['nullable', 'string', 'max:190'],
            'telefone'      => ['nullable', 'string', 'max:30'],
            'data_expiracao'=> ['nullable', 'date'],
            'status'        => ['required', 'in:ativo,inativo'],
            'foto'          => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $cpf = preg_replace('/\D+/', '', (string)$request->cpf);
        $telefone = preg_replace('/\D+/', '', (string)$request->telefone);

        $fotoPath = null;
        if ($request->hasFile('foto')) {
            $fotoPath = $request->file('foto')->store('usuarios', 'public');
        }

        $novoId = DB::table('usuarios')->insertGetId([
            'empresa_id' => $empresaId,
            'nome_completo' => $request->nome_completo,
            'cpf' => $cpf,
            'permissao_id' => (int)$request->permissao_id,
            'email' => $request->email,
            'telefone' => $telefone,
            'data_expiracao' => $request->data_expiracao,
            'status' => $request->status,
            'foto' => $fotoPath,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Se quiser, dá para já criar um vínculo inicial (filial/setor) aqui.
        // Mas como a regra final vai ficar na aba Lotação, eu só deixo o usuário salvo.

        return redirect()
            ->route('config.usuarios.create', ['id' => $novoId])
            ->with('success', 'Usuário cadastrado com sucesso. Agora vincule as lotações.');
    }

    /**
     * AJAX: carrega setores pela filial
     */
    public function setoresPorFilial(Request $request)
    {
        $empresaId = auth()->user()->empresa_id;
        $filialId = (int) $request->query('filial_id', 0);

        $setores = DB::table('setores')
            ->select('id', 'nome')
            ->where('empresa_id', $empresaId)
            ->where('filial_id', $filialId)
            ->whereNull('deleted_at')
            ->orderBy('nome')
            ->get();

        return response()->json($setores);
    }

    /**
     * AJAX: grid de lotações (filtra filial/setor e marca se está vinculado)
     */
    public function lotacoesGrid(Request $request)
    {
        $empresaId = auth()->user()->empresa_id;

        $usuarioId = (int) $request->query('usuario_id', 0);
        $filialId  = (int) $request->query('filial_id', 0);
        $setorId   = (int) $request->query('setor_id', 0);

        if ($usuarioId <= 0) {
            return response()->json(['error' => 'usuario_id inválido'], 422);
        }

        // Combinações disponíveis (vinculo_cargo_lotacao)
        $q = DB::table('vinculo_cargo_lotacao as vcl')
            ->leftJoin('filiais as f', 'f.id', '=', 'vcl.filial_id')
            ->leftJoin('setores as s', 's.id', '=', 'vcl.setor_id')
            ->leftJoin('cargos as c', 'c.id', '=', 'vcl.cargo_id')
            ->where('vcl.empresa_id', $empresaId)
            ->where('vcl.ativo', true)
            ->whereNull('vcl.deleted_at')
            ->select(
                'vcl.id as vcl_id',
                'vcl.filial_id',
                'vcl.setor_id',
                'vcl.cargo_id',
                DB::raw("COALESCE(f.nome_fantasia, f.razao_social) as filial_nome"),
                's.nome as setor_nome',
                'c.titulo as cargo_titulo',
                DB::raw("
                    CASE WHEN EXISTS (
                        SELECT 1
                        FROM vinculo_usuario_lotacao vul
                        WHERE vul.deleted_at IS NULL
                          AND vul.empresa_id = vcl.empresa_id
                          AND vul.usuario_id = {$usuarioId}
                          AND vul.filial_id = vcl.filial_id
                          AND vul.setor_id = vcl.setor_id
                          AND vul.cargo_id = vcl.cargo_id
                          AND vul.ativo = true
                    ) THEN true ELSE false END
                as vinculado")
            );

        if ($filialId > 0) $q->where('vcl.filial_id', $filialId);
        if ($setorId > 0)  $q->where('vcl.setor_id', $setorId);

        // Ordenação: vinculados primeiro, depois filial, setor, cargo
        $rows = $q
            ->orderByRaw("CASE WHEN (CASE WHEN EXISTS (
                        SELECT 1
                        FROM vinculo_usuario_lotacao vul
                        WHERE vul.deleted_at IS NULL
                          AND vul.empresa_id = vcl.empresa_id
                          AND vul.usuario_id = {$usuarioId}
                          AND vul.filial_id = vcl.filial_id
                          AND vul.setor_id = vcl.setor_id
                          AND vul.cargo_id = vcl.cargo_id
                          AND vul.ativo = true
                    ) THEN 1 ELSE 0 END) = 1 THEN 0 ELSE 1 END")
            ->orderByRaw("COALESCE(f.nome_fantasia, f.razao_social)")
            ->orderBy('s.nome')
            ->orderBy('c.titulo')
            ->get();

        return response()->json($rows);
    }

    /**
     * AJAX: toggle do vínculo (checkbox)
     */
    public function toggleLotacao(Request $request)
    {
        $empresaId = auth()->user()->empresa_id;

        $request->validate([
            'usuario_id' => ['required', 'integer'],
            'filial_id'  => ['required', 'integer'],
            'setor_id'   => ['required', 'integer'],
            'cargo_id'   => ['required', 'integer'],
            'checked'    => ['required'],
        ]);

        $usuarioId = (int)$request->usuario_id;
        $filialId  = (int)$request->filial_id;
        $setorId   = (int)$request->setor_id;
        $cargoId   = (int)$request->cargo_id;
        $checked   = filter_var($request->checked, FILTER_VALIDATE_BOOLEAN);

        // procura vínculo existente
        $v = DB::table('vinculo_usuario_lotacao')
            ->whereNull('deleted_at')
            ->where('empresa_id', $empresaId)
            ->where('usuario_id', $usuarioId)
            ->where('filial_id', $filialId)
            ->where('setor_id', $setorId)
            ->where('cargo_id', $cargoId)
            ->first();

        if ($checked) {
            if ($v) {
                DB::table('vinculo_usuario_lotacao')
                    ->where('id', $v->id)
                    ->update([
                        'ativo' => true,
                        'updated_at' => now(),
                    ]);
            } else {
                DB::table('vinculo_usuario_lotacao')->insert([
                    'empresa_id' => $empresaId,
                    'usuario_id' => $usuarioId,
                    'filial_id'  => $filialId,
                    'setor_id'   => $setorId,
                    'cargo_id'   => $cargoId,
                    'ativo'      => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        } else {
            if ($v) {
                DB::table('vinculo_usuario_lotacao')
                    ->where('id', $v->id)
                    ->update([
                        'ativo' => false,
                        'updated_at' => now(),
                    ]);
            }
        }

        return response()->json(['ok' => true]);
    }
}
