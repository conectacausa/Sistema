<?php

namespace App\Http\Controllers\Beneficios;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;

class TransporteLinhasController extends Controller
{
    private const T_LINHAS         = 'transporte_linhas';
    private const T_LINHA_FILIAIS  = 'transporte_linha_filiais';
    private const T_MOTORISTAS     = 'transporte_motoristas';
    private const T_VEICULOS       = 'transporte_veiculos';
    private const T_PARADAS        = 'transporte_paradas';
    private const T_VINCULOS       = 'transporte_vinculos';

    private const T_PEDIDOS        = 'transporte_pedidos';
    private const T_PEDIDO_ITENS   = 'transporte_pedido_itens';

    private const T_FILIAIS        = 'filiais';
    private const T_COLABORADORES  = 'colaboradores';

    private function empresaId(): int
    {
        return (int) (auth()->user()->empresa_id ?? 0);
    }

    private function hasColumn(string $table, string $column): bool
    {
        try { return Schema::hasColumn($table, $column); } catch (\Throwable $e) { return false; }
    }

    private function hasTable(string $table): bool
    {
        try { return Schema::hasTable($table); } catch (\Throwable $e) { return false; }
    }

    private function baseFiliaisQuery(int $empresaId)
    {
        $q = DB::table(self::T_FILIAIS)->where('empresa_id', $empresaId);
        if ($this->hasColumn(self::T_FILIAIS, 'deleted_at')) $q->whereNull('deleted_at');
        return $q;
    }

    private function baseMotoristasQuery(int $empresaId)
    {
        $q = DB::table(self::T_MOTORISTAS)->where('empresa_id', $empresaId);
        if ($this->hasColumn(self::T_MOTORISTAS, 'deleted_at')) $q->whereNull('deleted_at');
        return $q;
    }

    private function baseVeiculosQuery(int $empresaId)
    {
        $q = DB::table(self::T_VEICULOS)->where('empresa_id', $empresaId);
        if ($this->hasColumn(self::T_VEICULOS, 'deleted_at')) $q->whereNull('deleted_at');
        return $q;
    }

    private function baseLinhasQuery(int $empresaId)
    {
        $q = DB::table(self::T_LINHAS)->where('empresa_id', $empresaId);
        if ($this->hasColumn(self::T_LINHAS, 'deleted_at')) $q->whereNull('deleted_at');
        return $q;
    }

    public function index(Request $request, string $sub)
    {
        // (mantém seu index atual — não repeti aqui pra não poluir)
        return redirect()->route('beneficios.transporte.linhas.index', ['sub' => $sub]);
    }

    public function create(Request $request, string $sub)
    {
        $empresaId = $this->empresaId();
        $motoristas = $this->baseMotoristasQuery($empresaId)->orderBy('nome')->get();
        $veiculos   = $this->baseVeiculosQuery($empresaId)->orderBy('id', 'desc')->get();
        $filiais    = $this->baseFiliaisQuery($empresaId)->orderBy('id', 'asc')->get();

        return view('beneficios.transporte.linhas.create', compact('sub', 'motoristas', 'veiculos', 'filiais'));
    }

    public function store(Request $request, string $sub)
    {
        // (mantém seu store atual)
        return redirect()->route('beneficios.transporte.linhas.index', ['sub' => $sub]);
    }

    /**
     * ✅ EDIT: carrega tudo que a tela precisa + métricas do mês corrente
     */
    public function edit(Request $request, string $sub, int $id)
    {
        $empresaId = $this->empresaId();

        $linha = DB::table(self::T_LINHAS . ' as l')
            ->leftJoin(self::T_MOTORISTAS . ' as m', 'm.id', '=', 'l.motorista_id')
            ->leftJoin(self::T_VEICULOS . ' as v', 'v.id', '=', 'l.veiculo_id')
            ->select([
                'l.*',
                'm.nome as motorista_nome',
                'v.modelo as veiculo_modelo',
                'v.placa as veiculo_placa',
            ])
            ->addSelect(DB::raw($this->hasColumn(self::T_VEICULOS, 'capacidade_passageiros')
                ? 'COALESCE(v.capacidade_passageiros, 0) as capacidade'
                : '0 as capacidade'
            ))
            ->where('l.empresa_id', $empresaId)
            ->where('l.id', $id)
            ->when($this->hasColumn(self::T_LINHAS, 'deleted_at'), fn($q) => $q->whereNull('l.deleted_at'))
            ->first();

        if (!$linha) {
            return redirect()->route('beneficios.transporte.linhas.index', ['sub' => $sub])
                ->with('error', 'Linha não encontrada.');
        }

        $filiais        = $this->baseFiliaisQuery($empresaId)->orderBy('id')->get();
        $motoristas     = $this->baseMotoristasQuery($empresaId)->orderBy('nome')->get();
        $veiculos       = $this->baseVeiculosQuery($empresaId)->orderBy('id', 'desc')->get();

        $filialAtualId = (int) (DB::table(self::T_LINHA_FILIAIS)
            ->where('linha_id', $id)
            ->orderBy('id')
            ->value('filial_id') ?? 0);

        // Tabs: Paradas
        $paradas = DB::table(self::T_PARADAS)
            ->where('linha_id', $id)
            ->when($this->hasColumn(self::T_PARADAS, 'deleted_at'), fn($q) => $q->whereNull('deleted_at'))
            ->orderBy('ordem')
            ->orderBy('id')
            ->get();

        // Usuários ativos (regra: hoje entre início e fim, ou fim null)
        $hoje = now()->toDateString();

        $usuarios = DB::table(self::T_VINCULOS . ' as tv')
            ->leftJoin(self::T_COLABORADORES . ' as c', 'c.id', '=', 'tv.colaborador_id')
            ->leftJoin(self::T_PARADAS . ' as p', 'p.id', '=', 'tv.parada_id')
            ->select([
                'tv.*',
                'c.nome_completo as colaborador_nome',
                'c.matricula as colaborador_matricula',
                'c.cpf as colaborador_cpf',
                'p.identificacao as parada_nome',
            ])
            ->where('tv.linha_id', $id)
            ->when($this->hasColumn(self::T_VINCULOS, 'deleted_at'), fn($q) => $q->whereNull('tv.deleted_at'))
            ->orderBy('c.nome_completo')
            ->get();

        $usuariosAtivos = DB::table(self::T_VINCULOS . ' as tv')
            ->where('tv.linha_id', $id)
            ->when($this->hasColumn(self::T_VINCULOS, 'deleted_at'), fn($q) => $q->whereNull('tv.deleted_at'))
            ->whereDate('tv.data_inicio', '<=', $hoje)
            ->where(function ($w) use ($hoje) {
                $w->whereNull('tv.data_fim')
                  ->orWhereDate('tv.data_fim', '>=', $hoje);
            })
            ->count();

        // Mês corrente (para métricas financeiras)
        $inicioMes = now()->startOfMonth()->toDateString();
        $fimMes    = now()->endOfMonth()->toDateString();

        $valorLinhaMes = 0.0;
        $pedidos = collect();

        if ($this->hasTable(self::T_PEDIDOS)) {
            $pedidos = DB::table(self::T_PEDIDOS)
                ->where('empresa_id', $empresaId)
                ->where('linha_id', $id)
                ->whereBetween('data_pedido', [$inicioMes, $fimMes])
                ->when($this->hasColumn(self::T_PEDIDOS, 'deleted_at'), fn($q) => $q->whereNull('deleted_at'))
                ->orderBy('data_pedido', 'desc')
                ->orderBy('id', 'desc')
                ->get();

            $valorLinhaMes = (float) $pedidos->sum('valor_total');
        }

        // "Usuários que usaram transporte no mês corrente"
        // (regra prática: vínculo ativo em algum momento do mês)
        $usuariosMes = DB::table(self::T_VINCULOS . ' as tv')
            ->where('tv.linha_id', $id)
            ->when($this->hasColumn(self::T_VINCULOS, 'deleted_at'), fn($q) => $q->whereNull('tv.deleted_at'))
            ->whereDate('tv.data_inicio', '<=', $fimMes)
            ->where(function ($w) use ($inicioMes) {
                $w->whereNull('tv.data_fim')
                  ->orWhereDate('tv.data_fim', '>=', $inicioMes);
            })
            ->count();

        $capacidade = (int) ($linha->capacidade ?? 0);
        $disponivel = max(0, $capacidade - (int) $usuariosAtivos);

        $valorPorUsuario = 0.0;
        if ($usuariosMes > 0) {
            $valorPorUsuario = round($valorLinhaMes / $usuariosMes, 2);
        }

        $metrics = [
            'capacidade'      => $capacidade,
            'usuarios_ativos' => (int) $usuariosAtivos,
            'disponivel'      => (int) $disponivel,
            'valor_linha_mes' => (float) $valorLinhaMes,
            'valor_por_user'  => (float) $valorPorUsuario,
        ];

        return view('beneficios.transporte.linhas.edit', compact(
            'sub',
            'linha',
            'filiais',
            'motoristas',
            'veiculos',
            'filialAtualId',
            'paradas',
            'usuarios',
            'pedidos',
            'metrics'
        ));
    }

    public function update(Request $request, string $sub, int $id)
    {
        $empresaId = $this->empresaId();

        $validator = Validator::make($request->all(), [
            'nome'            => ['required', 'string', 'max:255'],
            'tipo_linha'      => ['required', 'in:fretada,publica'],
            'controle_acesso' => ['required', 'in:cartao,ticket'],
            'status'          => ['required', 'in:ativo,inativo'],
            'filial_id'       => ['required', 'integer', 'min:1'],
            'veiculo_id'      => ['nullable', 'integer', 'min:1'],
            'motorista_id'    => ['nullable', 'integer', 'min:1'],
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput()
                ->with('error', 'Existem campos obrigatórios pendentes.');
        }

        try {
            DB::beginTransaction();

            DB::table(self::T_LINHAS)
                ->where('empresa_id', $empresaId)
                ->where('id', $id)
                ->update([
                    'nome'            => trim((string) $request->input('nome')),
                    'tipo_linha'      => $request->input('tipo_linha'),
                    'controle_acesso' => $request->input('controle_acesso'),
                    'status'          => $request->input('status'),
                    'veiculo_id'      => $request->input('veiculo_id') ?: null,
                    'motorista_id'    => $request->input('motorista_id') ?: null,
                    'updated_at'      => now(),
                ]);

            $exists = DB::table(self::T_LINHA_FILIAIS)->where('linha_id', $id)->exists();
            if ($exists) {
                DB::table(self::T_LINHA_FILIAIS)->where('linha_id', $id)->update([
                    'filial_id'  => (int) $request->input('filial_id'),
                    'updated_at' => now(),
                ]);
            } else {
                DB::table(self::T_LINHA_FILIAIS)->insert([
                    'linha_id'   => $id,
                    'filial_id'  => (int) $request->input('filial_id'),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::commit();

            return redirect()->route('beneficios.transporte.linhas.edit', ['sub' => $sub, 'id' => $id])
                ->with('success', 'Linha atualizada com sucesso.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Não foi possível salvar. Tente novamente.');
        }
    }

    /**
     * ✅ Modal: salvar parada
     */
    public function paradasStore(Request $request, string $sub, int $id)
    {
        $empresaId = $this->empresaId();

        $v = Validator::make($request->all(), [
            'identificacao' => ['required', 'string', 'max:255'],
            'endereco'      => ['nullable', 'string', 'max:255'],
            'ordem'         => ['required', 'integer', 'min:1'],
            'horario'       => ['required', 'date_format:H:i'],
            'valor'         => ['required'],
        ]);

        if ($v->fails()) {
            return back()->withErrors($v)->withInput()->with('error', 'Revise os campos da parada.');
        }

        DB::table(self::T_PARADAS)->insert([
            'empresa_id'     => $empresaId,
            'linha_id'       => $id,
            'identificacao'  => trim((string) $request->identificacao),
            'endereco'       => trim((string) $request->endereco),
            'ordem'          => (int) $request->ordem,
            'horario'        => $request->horario,
            'valor'          => (float) str_replace(',', '.', preg_replace('/[^\d,\.]/', '', (string)$request->valor)),
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);

        return redirect()->route('beneficios.transporte.linhas.edit', ['sub' => $sub, 'id' => $id])
            ->with('success', 'Parada adicionada com sucesso.');
    }

    /**
     * ✅ Remover parada:
     * - se tiver usuário vinculado, bloqueia com error (a view faz o SweetAlert)
     * - se não, remove
     */
    public function paradasDestroy(Request $request, string $sub, int $id, int $parada_id)
    {
        $temVinculo = DB::table(self::T_VINCULOS)
            ->where('linha_id', $id)
            ->where('parada_id', $parada_id)
            ->when($this->hasColumn(self::T_VINCULOS, 'deleted_at'), fn($q) => $q->whereNull('deleted_at'))
            ->exists();

        if ($temVinculo) {
            return redirect()->route('beneficios.transporte.linhas.edit', ['sub' => $sub, 'id' => $id])
                ->with('error', 'Não é possível remover: existem usuários vinculados a esta parada.');
        }

        if ($this->hasColumn(self::T_PARADAS, 'deleted_at')) {
            DB::table(self::T_PARADAS)->where('id', $parada_id)->update([
                'deleted_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            DB::table(self::T_PARADAS)->where('id', $parada_id)->delete();
        }

        return redirect()->route('beneficios.transporte.linhas.edit', ['sub' => $sub, 'id' => $id])
            ->with('success', 'Parada removida com sucesso.');
    }

    /**
     * ✅ Modal: vincular usuário
     * (campos esperados na tabela transporte_vinculos: colaborador_id, parada_id, cartao_numero, valor_passagem, data_inicio, data_fim)
     */
    public function usuariosStore(Request $request, string $sub, int $id)
    {
        $empresaId = $this->empresaId();

        $v = Validator::make($request->all(), [
            'colaborador_id'  => ['required', 'integer', 'min:1'],
            'parada_id'       => ['required', 'integer', 'min:1'],
            'data_inicio'     => ['required', 'date'],
            'data_fim'        => ['nullable', 'date'],
            'cartao_numero'   => ['nullable', 'string', 'max:60'],
            'valor_passagem'  => ['required'],
        ]);

        if ($v->fails()) {
            return back()->withErrors($v)->withInput()->with('error', 'Revise os campos do colaborador.');
        }

        DB::table(self::T_VINCULOS)->insert([
            'empresa_id'      => $empresaId,
            'linha_id'        => $id,
            'colaborador_id'  => (int) $request->colaborador_id,
            'parada_id'       => (int) $request->parada_id,
            'data_inicio'     => $request->data_inicio,
            'data_fim'        => $request->data_fim ?: null,
            'cartao_numero'   => trim((string) $request->cartao_numero) ?: null,
            'valor_passagem'  => (float) str_replace(',', '.', preg_replace('/[^\d,\.]/', '', (string)$request->valor_passagem)),
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);

        return redirect()->route('beneficios.transporte.linhas.edit', ['sub' => $sub, 'id' => $id])
            ->with('success', 'Colaborador vinculado com sucesso.');
    }

    /**
     * ✅ Inativar/editar vínculo (no seu botão "inativar", você vai mandar data_fim no modal)
     */
    public function usuariosUpdate(Request $request, string $sub, int $id, int $vinculo_id)
    {
        $v = Validator::make($request->all(), [
            'parada_id'      => ['required', 'integer', 'min:1'],
            'data_inicio'    => ['required', 'date'],
            'data_fim'       => ['nullable', 'date'],
            'cartao_numero'  => ['nullable', 'string', 'max:60'],
            'valor_passagem' => ['required'],
        ]);

        if ($v->fails()) {
            return back()->withErrors($v)->withInput()->with('error', 'Revise os campos do vínculo.');
        }

        DB::table(self::T_VINCULOS)->where('id', $vinculo_id)->update([
            'parada_id'      => (int) $request->parada_id,
            'data_inicio'    => $request->data_inicio,
            'data_fim'       => $request->data_fim ?: null,
            'cartao_numero'  => trim((string)$request->cartao_numero) ?: null,
            'valor_passagem' => (float) str_replace(',', '.', preg_replace('/[^\d,\.]/', '', (string)$request->valor_passagem)),
            'updated_at'     => now(),
        ]);

        return redirect()->route('beneficios.transporte.linhas.edit', ['sub' => $sub, 'id' => $id])
            ->with('success', 'Vínculo atualizado com sucesso.');
    }

    /**
     * ✅ Modal: salvar pedido (header + itens)
     * itens[]: cartao_numero, colaborador_id (opcional), valor
     */
    public function pedidosStore(Request $request, string $sub, int $id)
    {
        $empresaId = $this->empresaId();

        $v = Validator::make($request->all(), [
            'codigo'          => ['required', 'string', 'max:80'],
            'data_pedido'     => ['required', 'date'],
            'status'          => ['required', 'string', 'max:30'],
            'itens'           => ['required', 'array', 'min:1'],
            'itens.*.valor'   => ['required'],
            'itens.*.cartao_numero' => ['nullable', 'string', 'max:60'],
            'itens.*.colaborador_id' => ['nullable', 'integer'],
        ]);

        if ($v->fails()) {
            return back()->withErrors($v)->withInput()->with('error', 'Revise os campos do pedido.');
        }

        if (!$this->hasTable(self::T_PEDIDOS)) {
            return back()->with('error', 'Tabela de pedidos ainda não foi criada (migration pendente).');
        }

        $valorTotal = 0.0;
        foreach ((array)$request->itens as $it) {
            $valorTotal += (float) str_replace(',', '.', preg_replace('/[^\d,\.]/', '', (string)($it['valor'] ?? 0)));
        }

        DB::beginTransaction();
        try {
            $pedidoId = DB::table(self::T_PEDIDOS)->insertGetId([
                'empresa_id'  => $empresaId,
                'linha_id'    => $id,
                'codigo'      => trim((string)$request->codigo),
                'data_pedido' => $request->data_pedido,
                'status'      => trim((string)$request->status),
                'valor_total' => $valorTotal,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);

            foreach ((array)$request->itens as $it) {
                DB::table(self::T_PEDIDO_ITENS)->insert([
                    'pedido_id'     => $pedidoId,
                    'empresa_id'    => $empresaId,
                    'linha_id'      => $id,
                    'cartao_numero' => trim((string)($it['cartao_numero'] ?? '')) ?: null,
                    'colaborador_id'=> !empty($it['colaborador_id']) ? (int)$it['colaborador_id'] : null,
                    'valor'         => (float) str_replace(',', '.', preg_replace('/[^\d,\.]/', '', (string)($it['valor'] ?? 0))),
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ]);
            }

            DB::commit();

            return redirect()->route('beneficios.transporte.linhas.edit', ['sub' => $sub, 'id' => $id])
                ->with('success', 'Pedido registrado com sucesso.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Não foi possível salvar o pedido.');
        }
    }

    public function pedidosDestroy(Request $request, string $sub, int $id, int $pedido_id)
    {
        if (!$this->hasTable(self::T_PEDIDOS)) {
            return back()->with('error', 'Tabela de pedidos não existe.');
        }

        if ($this->hasColumn(self::T_PEDIDOS, 'deleted_at')) {
            DB::table(self::T_PEDIDOS)->where('id', $pedido_id)->update([
                'deleted_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            DB::table(self::T_PEDIDOS)->where('id', $pedido_id)->delete();
        }

        return redirect()->route('beneficios.transporte.linhas.edit', ['sub' => $sub, 'id' => $id])
            ->with('success', 'Pedido removido com sucesso.');
    }

    public function destroy(Request $request, string $sub, int $id)
    {
        // mantém seu destroy atual
        return redirect()->route('beneficios.transporte.linhas.index', ['sub' => $sub]);
    }

    public function operacao(Request $request, string $sub, int $id)
    {
        return view('beneficios.transporte.linhas.operacao', compact('sub', 'id'));
    }

    // (paradasUpdate pode ser adicionado depois, se você quiser editar parada)
    public function paradasUpdate(Request $request, string $sub, int $id, int $parada_id)
    {
        return redirect()->route('beneficios.transporte.linhas.edit', ['sub' => $sub, 'id' => $id]);
    }
}
