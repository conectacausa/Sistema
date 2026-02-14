<?php

namespace App\Http\Controllers\Beneficios;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;

class TransporteLinhasController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */
    private function empresaId(): int
    {
        return (int) (auth()->user()->empresa_id ?? 0);
    }

    private function hasColumn(string $table, string $column): bool
    {
        try {
            return Schema::hasColumn($table, $column);
        } catch (\Throwable $e) {
            return false;
        }
    }

    private function colaboradorNomeColumn(): string
    {
        // pelo seu print de colaboradores: existe "nome" (não "nome_completo")
        if ($this->hasColumn('colaboradores', 'nome_completo')) return 'nome_completo';
        if ($this->hasColumn('colaboradores', 'nome')) return 'nome';
        return 'id';
    }

    private function veiculoCapacidadeColumn(): ?string
    {
        // pelo seu print: capacidade_passageiros existe
        $candidates = ['capacidade_passageiros', 'capacidade', 'lotacao'];
        foreach ($candidates as $col) {
            if ($this->hasColumn('transporte_veiculos', $col)) return $col;
        }
        return null;
    }

    private function pivotLinhaFilialTable(): ?string
    {
        $candidates = [
            'transporte_linha_filial',
            'transporte_linhas_filiais',
            'transporte_linhas_filial',
            'transporte_linha_filiais',
        ];

        foreach ($candidates as $t) {
            if (Schema::hasTable($t)) return $t;
        }

        return null;
    }

    /*
    |--------------------------------------------------------------------------
    | Index
    |--------------------------------------------------------------------------
    */
    public function index(Request $request, string $sub)
    {
        $empresaId = $this->empresaId();

        $filiais = DB::table('filiais')
            ->where('empresa_id', $empresaId)
            ->whereNull('deleted_at')
            ->orderBy('nome_fantasia')
            ->get(['id', 'nome_fantasia']);

        return view('beneficios.transporte.linhas.index', [
            'filiais' => $filiais,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Grid (AJAX)
    |--------------------------------------------------------------------------
    */
    public function grid(Request $request, string $sub)
    {
        $empresaId = $this->empresaId();

        $q       = trim((string) $request->get('q', ''));
        $tipo    = trim((string) $request->get('tipo', ''));
        $filial  = (int) $request->get('filial_id', 0);

        $capCol = $this->veiculoCapacidadeColumn(); // capacidade_passageiros
        $capSelect = $capCol ? "v.$capCol" : "NULL";

        $base = DB::table('transporte_linhas as l')
            ->leftJoin('transporte_motoristas as m', 'm.id', '=', 'l.motorista_id')
            ->leftJoin('transporte_veiculos as v', 'v.id', '=', 'l.veiculo_id')
            ->where('l.empresa_id', $empresaId)
            ->whereNull('l.deleted_at');

        if ($q !== '') {
            $base->where(function ($w) use ($q) {
                $w->where('l.nome', 'ilike', "%{$q}%")
                    ->orWhere('m.nome', 'ilike', "%{$q}%")
                    ->orWhere('v.modelo', 'ilike', "%{$q}%")
                    ->orWhere('v.placa', 'ilike', "%{$q}%");
            });
        }

        if ($tipo !== '') {
            $base->where('l.tipo_linha', $tipo);
        }

        if ($filial > 0) {
            $pivot = $this->pivotLinhaFilialTable();
            if ($pivot) {
                $base->whereExists(function ($sq) use ($pivot, $filial) {
                    $sq->select(DB::raw(1))
                        ->from("$pivot as lf")
                        ->whereRaw('lf.linha_id = l.id')
                        ->where('lf.filial_id', $filial);
                });
            }
        }

        $rows = $base
            ->selectRaw("
                l.id,
                l.nome,
                l.tipo_linha,
                l.controle_acesso,
                l.status,
                l.motorista_id,
                l.veiculo_id,
                m.nome as motorista_nome,
                v.modelo as veiculo_modelo,
                v.placa as veiculo_placa,
                {$capSelect} as capacidade,
                (
                    SELECT COUNT(1)
                    FROM transporte_vinculos tv
                    WHERE tv.linha_id = l.id
                      AND (tv.status IS NULL OR tv.status = 'ativo')
                      AND (tv.data_inicio IS NULL OR tv.data_inicio <= CURRENT_DATE)
                      AND (tv.data_fim IS NULL OR tv.data_fim >= CURRENT_DATE)
                ) as vinculados_ativos
            ")
            ->orderByDesc('l.id')
            ->paginate(25)
            ->appends($request->query());

        $items = collect($rows->items())->map(function ($r) {
            $cap = (int) ($r->capacidade ?? 0);
            $ativos = (int) ($r->vinculados_ativos ?? 0);
            $r->disponibilidade = max($cap - $ativos, 0);
            return $r;
        });

        return response()->json([
            'data' => $items,
            'pagination' => [
                'current_page' => $rows->currentPage(),
                'last_page'    => $rows->lastPage(),
                'per_page'     => $rows->perPage(),
                'total'        => $rows->total(),
            ],
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Create / Store
    |--------------------------------------------------------------------------
    */
    public function create(Request $request, string $sub)
    {
        $empresaId = $this->empresaId();

        $filiais = DB::table('filiais')
            ->where('empresa_id', $empresaId)
            ->whereNull('deleted_at')
            ->orderBy('nome_fantasia')
            ->get(['id', 'nome_fantasia']);

        $veiculos = DB::table('transporte_veiculos')
            ->where('empresa_id', $empresaId)
            ->orderBy('modelo')
            ->get(['id', 'modelo', 'placa']);

        $motoristas = DB::table('transporte_motoristas')
            ->where('empresa_id', $empresaId)
            ->orderBy('nome')
            ->get(['id', 'nome']);

        return view('beneficios.transporte.linhas.create', compact('filiais', 'veiculos', 'motoristas'));
    }

    public function store(Request $request, string $sub)
    {
        $empresaId = $this->empresaId();

        $v = Validator::make($request->all(), [
            'nome'            => ['required', 'string', 'max:255'],
            'tipo_linha'      => ['required', 'in:fretada,publica'],
            'controle_acesso' => ['required', 'in:cartao,ticket'],
            'status'          => ['required', 'in:ativa,inativa'],
            'filial_id'       => ['required', 'integer', 'min:1'],
            'veiculo_id'      => ['required', 'integer', 'min:1'],
            'motorista_id'    => ['required', 'integer', 'min:1'],
        ]);

        if ($v->fails()) {
            return back()
                ->withErrors($v)
                ->withInput()
                ->with('alert_error', 'Revise os campos do formulário.');
        }

        DB::beginTransaction();
        try {
            $linhaId = DB::table('transporte_linhas')->insertGetId([
                'empresa_id'      => $empresaId,
                'nome'            => trim((string) $request->nome),
                'tipo_linha'      => $request->tipo_linha,
                'controle_acesso' => $request->controle_acesso,
                'status'          => $request->status,
                'motorista_id'    => (int) $request->motorista_id,
                'veiculo_id'      => (int) $request->veiculo_id,
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);

            $pivot = $this->pivotLinhaFilialTable();
            if ($pivot) {
                DB::table($pivot)->insert([
                    'linha_id'   => $linhaId,
                    'filial_id'  => (int) $request->filial_id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::commit();

            return redirect()
                ->route('beneficios.transporte.linhas.edit', ['sub' => $sub, 'id' => $linhaId])
                ->with('alert_success', 'Linha criada com sucesso.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('alert_error', 'Erro ao salvar. Verifique os dados e tente novamente.');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Edit
    |--------------------------------------------------------------------------
    */
    public function edit(Request $request, string $sub, int $id)
    {
        $empresaId = $this->empresaId();

        $linha = DB::table('transporte_linhas')
            ->where('empresa_id', $empresaId)
            ->whereNull('deleted_at')
            ->where('id', $id)
            ->first();

        if (!$linha) {
            return redirect()
                ->route('beneficios.transporte.linhas.index', ['sub' => $sub])
                ->with('alert_error', 'Linha não encontrada.');
        }

        $filiais = DB::table('filiais')
            ->where('empresa_id', $empresaId)
            ->whereNull('deleted_at')
            ->orderBy('nome_fantasia')
            ->get(['id', 'nome_fantasia']);

        $veiculos = DB::table('transporte_veiculos')
            ->where('empresa_id', $empresaId)
            ->orderBy('modelo')
            ->get(['id', 'modelo', 'placa']);

        $motoristas = DB::table('transporte_motoristas')
            ->where('empresa_id', $empresaId)
            ->orderBy('nome')
            ->get(['id', 'nome']);

        // capacidade (capacidade_passageiros)
        $capCol = $this->veiculoCapacidadeColumn();
        $capacidade = 0;
        if (!empty($linha->veiculo_id) && $capCol) {
            $capacidade = (int) (DB::table('transporte_veiculos')
                ->where('empresa_id', $empresaId)
                ->where('id', $linha->veiculo_id)
                ->value($capCol) ?? 0);
        }

        // usuários ativos
        $usuariosAtivos = (int) DB::table('transporte_vinculos')
            ->where('linha_id', $linha->id)
            ->where(function ($w) {
                $w->whereNull('status')->orWhere('status', 'ativo');
            })
            ->where(function ($w) {
                $w->whereNull('data_inicio')->orWhere('data_inicio', '<=', DB::raw('CURRENT_DATE'));
            })
            ->where(function ($w) {
                $w->whereNull('data_fim')->orWhere('data_fim', '>=', DB::raw('CURRENT_DATE'));
            })
            ->count();

        $disponivel = max($capacidade - $usuariosAtivos, 0);

        // paradas
        $paradas = DB::table('transporte_paradas')
            ->where('linha_id', $linha->id)
            ->whereNull('deleted_at')
            ->orderBy('ordem')
            ->get();

        // ✅ VÍNCULOS (aqui está a correção definitiva)
        // transporte_vinculos.usuario_id -> usuarios.id -> usuarios.colaborador_id -> colaboradores.id
        $nomeCol = $this->colaboradorNomeColumn();

        $vinculos = DB::table('transporte_vinculos as tv')
            ->leftJoin('usuarios as u', 'u.id', '=', 'tv.usuario_id')
            ->leftJoin('colaboradores as c', 'c.id', '=', 'u.colaborador_id')
            ->leftJoin('transporte_paradas as p', 'p.id', '=', 'tv.parada_id')
            ->where('tv.linha_id', $linha->id)
            ->orderBy("c.$nomeCol")
            ->selectRaw("
                tv.*,
                c.$nomeCol as colaborador_nome,
                c.matricula as colaborador_matricula,
                c.cpf as colaborador_cpf,
                p.identificacao as parada_nome
            ")
            ->get();

        // financeiro (pedidos) - não quebra se ainda não existir
        $pedidos = collect();
        $valorLinhaMes = 0.0;
        $valorPorUsuario = 0.0;

        if (Schema::hasTable('transporte_pedidos')) {
            $mesInicio = now()->startOfMonth()->toDateString();
            $mesFim    = now()->endOfMonth()->toDateString();

            $pedidos = DB::table('transporte_pedidos')
                ->where('empresa_id', $empresaId)
                ->where('linha_id', $linha->id)
                ->whereBetween('data_pedido', [$mesInicio, $mesFim])
                ->whereNull('deleted_at')
                ->orderByDesc('id')
                ->get();

            $valorLinhaMes = (float) DB::table('transporte_pedidos')
                ->where('empresa_id', $empresaId)
                ->where('linha_id', $linha->id)
                ->whereBetween('data_pedido', [$mesInicio, $mesFim])
                ->whereNull('deleted_at')
                ->sum('valor_total');

            $usuariosMes = max($usuariosAtivos, 1);
            $valorPorUsuario = $valorLinhaMes / $usuariosMes;
        }

        return view('beneficios.transporte.linhas.edit', compact(
            'linha',
            'filiais',
            'veiculos',
            'motoristas',
            'capacidade',
            'usuariosAtivos',
            'disponivel',
            'paradas',
            'vinculos',
            'pedidos',
            'valorLinhaMes',
            'valorPorUsuario'
        ));
    }
}
