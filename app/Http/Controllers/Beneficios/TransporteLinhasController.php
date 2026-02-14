<?php

namespace App\Http\Controllers\Beneficios;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
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

    private function hasColumn(string $table, string $column): bool
    {
        try {
            return Schema::hasColumn($table, $column);
        } catch (\Throwable $e) {
            return false;
        }
    }

    private function empresaId(): int
    {
        return (int) (auth()->user()->empresa_id ?? 0);
    }

    private function subdomain(Request $request): string
    {
        return (string) $request->route('sub');
    }

    private function filialLabelExpr(): string
    {
        // filiais NÃO tem coluna "nome"
        // usa nome_fantasia / razao_social
        return "COALESCE(f.nome_fantasia, f.razao_social, ('Filial #'||f.id::text))";
    }

    private function monthStart(): string
    {
        return Carbon::now()->startOfMonth()->toDateString();
    }

    private function monthEnd(): string
    {
        return Carbon::now()->endOfMonth()->toDateString();
    }

    /*
    |--------------------------------------------------------------------------
    | Index
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        $empresaId = $this->empresaId();
        $sub = $this->subdomain($request);

        $q        = trim((string) $request->get('q', ''));
        $tipo     = trim((string) $request->get('tipo', ''));        // fretada/publica (ou vazio)
        $filialId = (int) $request->get('filial_id', 0);

        // lista filiais p/ filtro (dropdown simples)
        $filiais = DB::table('filiais as f')
            ->selectRaw('f.id, ' . $this->filialLabelExpr() . ' as nome')
            ->where('f.empresa_id', $empresaId)
            ->whereNull('f.deleted_at')
            ->orderBy('f.id')
            ->get();

        // query linhas + joins
        $query = DB::table('transporte_linhas as l')
            ->leftJoin('transporte_motoristas as m', 'm.id', '=', 'l.motorista_id')
            ->leftJoin('transporte_veiculos as v', 'v.id', '=', 'l.veiculo_id')
            ->where('l.empresa_id', $empresaId)
            ->whereNull('l.deleted_at')
            ->select([
                'l.id',
                'l.nome',
                'l.tipo_linha',
                'l.controle_acesso',
                'l.status',
                'l.motorista_id',
                'l.veiculo_id',
                DB::raw('m.nome as motorista_nome'),
                DB::raw('v.modelo as veiculo_modelo'),
                DB::raw('v.placa as veiculo_placa'),
                DB::raw('COALESCE(v.capacidade_passageiros, 0) as capacidade'),
                DB::raw("(
                    SELECT COUNT(1)
                    FROM transporte_vinculos tv
                    WHERE tv.linha_id = l.id
                      AND tv.empresa_id = {$empresaId}
                      AND tv.deleted_at IS NULL
                      AND (tv.data_inicio IS NULL OR tv.data_inicio <= CURRENT_DATE)
                      AND (tv.data_fim IS NULL OR tv.data_fim >= CURRENT_DATE)
                ) as vinculados_ativos"),
            ])
            ->orderByDesc('l.id');

        // filtro filial (tabela pivot não tem empresa_id)
        if ($filialId > 0) {
            $query->whereExists(function ($subq) use ($filialId) {
                $subq->select(DB::raw(1))
                    ->from('transporte_linha_filiais as lf')
                    ->whereColumn('lf.linha_id', 'l.id')
                    ->where('lf.filial_id', $filialId);
            });
        }

        // filtro tipo
        if ($tipo !== '') {
            $query->where('l.tipo_linha', $tipo);
        }

        // filtro texto (linha, veículo, placa, motorista)
        if ($q !== '') {
            $query->where(function ($w) use ($q) {
                $w->where('l.nome', 'ilike', '%' . $q . '%')
                  ->orWhere('m.nome', 'ilike', '%' . $q . '%')
                  ->orWhere('v.modelo', 'ilike', '%' . $q . '%')
                  ->orWhere('v.placa', 'ilike', '%' . $q . '%');
            });
        }

        $linhas = $query->paginate(25)->withQueryString();

        // calcula disponibilidade (capacidade - vinculados) já no PHP
        $linhas->getCollection()->transform(function ($row) {
            $cap = (int) ($row->capacidade ?? 0);
            $vinc = (int) ($row->vinculados_ativos ?? 0);
            $row->disponibilidade = max($cap - $vinc, 0);
            return $row;
        });

        return view('beneficios.transporte.linhas.index', compact(
            'sub',
            'linhas',
            'filiais',
            'q',
            'tipo',
            'filialId'
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | Create / Store
    |--------------------------------------------------------------------------
    */

    public function create(Request $request)
    {
        $empresaId = $this->empresaId();
        $sub = $this->subdomain($request);

        $filiais = DB::table('filiais as f')
            ->selectRaw('f.id, ' . $this->filialLabelExpr() . ' as nome')
            ->where('f.empresa_id', $empresaId)
            ->whereNull('f.deleted_at')
            ->orderBy('f.id')
            ->get();

        // Para o select2: a view pode carregar vazio e buscar via AJAX,
        // mas deixo uma lista inicial pequena para não ficar "branco".
        $veiculos = DB::table('transporte_veiculos')
            ->select('id', 'placa', 'modelo')
            ->where('empresa_id', $empresaId)
            ->orderBy('id', 'desc')
            ->limit(30)
            ->get();

        $motoristas = DB::table('transporte_motoristas')
            ->select('id', 'nome')
            ->where('empresa_id', $empresaId)
            ->orderBy('nome')
            ->limit(30)
            ->get();

        return view('beneficios.transporte.linhas.create', compact(
            'sub',
            'filiais',
            'veiculos',
            'motoristas'
        ));
    }

    public function store(Request $request)
    {
        $empresaId = $this->empresaId();
        $sub = $this->subdomain($request);

        $data = $request->all();

        $v = Validator::make($data, [
            'nome'            => ['required', 'string', 'max:191'],
            'tipo_linha'      => ['required', 'in:fretada,publica'],
            'controle_acesso' => ['required', 'in:cartao,ticket'],
            'status'          => ['required', 'in:ativo,inativo'],
            'filial_id'       => ['required', 'integer', 'min:1'],
            'veiculo_id'      => ['nullable', 'integer', 'min:1'],
            'motorista_id'    => ['nullable', 'integer', 'min:1'],
        ]);

        if ($v->fails()) {
            return back()
                ->withInput()
                ->with('alert_error', 'Revise os campos do formulário.')
                ->withErrors($v);
        }

        try {
            DB::beginTransaction();

            $linhaId = DB::table('transporte_linhas')->insertGetId([
                'empresa_id'      => $empresaId,
                'nome'            => trim((string) $data['nome']),
                'tipo_linha'      => $data['tipo_linha'],
                'controle_acesso' => $data['controle_acesso'],
                'status'          => $data['status'],
                'motorista_id'    => !empty($data['motorista_id']) ? (int) $data['motorista_id'] : null,
                'veiculo_id'      => !empty($data['veiculo_id']) ? (int) $data['veiculo_id'] : null,
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);

            // pivot (não tem empresa_id nem deleted_at)
            DB::table('transporte_linha_filiais')->insert([
                'linha_id'   => $linhaId,
                'filial_id'  => (int) $data['filial_id'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::commit();

            return redirect()
                ->route('beneficios.transporte.linhas.edit', ['sub' => $sub, 'id' => $linhaId])
                ->with('alert_success', 'Linha criada com sucesso.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()
                ->withInput()
                ->with('alert_error', 'Erro ao salvar. Verifique e tente novamente.');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Edit / Update
    |--------------------------------------------------------------------------
    */

    public function edit(Request $request, $id)
    {
        $empresaId = $this->empresaId();
        $sub = $this->subdomain($request);

        // ✅ blindagem: id pode vir string por conflito de rota
        $id = (int) $id;
        if ($id <= 0) {
            return redirect()
                ->route('beneficios.transporte.linhas.index', ['sub' => $sub])
                ->with('alert_error', 'ID inválido.');
        }

        $linha = DB::table('transporte_linhas as l')
            ->leftJoin('transporte_motoristas as m', 'm.id', '=', 'l.motorista_id')
            ->leftJoin('transporte_veiculos as v', 'v.id', '=', 'l.veiculo_id')
            ->where('l.empresa_id', $empresaId)
            ->where('l.id', $id)
            ->whereNull('l.deleted_at')
            ->select([
                'l.*',
                DB::raw('m.nome as motorista_nome'),
                DB::raw('v.modelo as veiculo_modelo'),
                DB::raw('v.placa as veiculo_placa'),
                DB::raw('COALESCE(v.capacidade_passageiros, 0) as capacidade'),
            ])
            ->first();

        if (!$linha) {
            return redirect()
                ->route('beneficios.transporte.linhas.index', ['sub' => $sub])
                ->with('alert_error', 'Linha não encontrada.');
        }

        $filiais = DB::table('filiais as f')
            ->selectRaw('f.id, ' . $this->filialLabelExpr() . ' as nome')
            ->where('f.empresa_id', $empresaId)
            ->whereNull('f.deleted_at')
            ->orderBy('f.id')
            ->get();

        $filialId = DB::table('transporte_linha_filiais')
            ->where('linha_id', $id)
            ->value('filial_id');

        $motoristas = DB::table('transporte_motoristas')
            ->select('id', 'nome')
            ->where('empresa_id', $empresaId)
            ->orderBy('nome')
            ->get();

        $veiculos = DB::table('transporte_veiculos')
            ->select('id', 'placa', 'modelo', 'capacidade_passageiros')
            ->where('empresa_id', $empresaId)
            ->orderBy('id', 'desc')
            ->get();

        // Paradas
        $paradas = DB::table('transporte_paradas')
            ->where('empresa_id', $empresaId)
            ->where('linha_id', $id)
            ->whereNull('deleted_at')
            ->orderBy('ordem')
            ->orderBy('id')
            ->get();

        // Vínculos (usa usuario_id) -> mostra colaborador via usuarios/colaboradores
        $vinculos = DB::table('transporte_vinculos as tv')
            ->leftJoin('usuarios as u', 'u.id', '=', 'tv.usuario_id')
            ->leftJoin('colaboradores as c', 'c.id', '=', 'u.colaborador_id')
            ->leftJoin('transporte_paradas as p', 'p.id', '=', 'tv.parada_id')
            ->where('tv.empresa_id', $empresaId)
            ->where('tv.linha_id', $id)
            ->whereNull('tv.deleted_at')
            ->orderByRaw("COALESCE(u.nome_completo, c.nome, 'Sem nome') asc")
            ->select([
                'tv.*',
                DB::raw("COALESCE(u.nome_completo, c.nome, 'Sem nome') as colaborador_nome"),
                DB::raw("c.matricula as colaborador_matricula"),
                DB::raw("c.cpf as colaborador_cpf"),
                DB::raw("p.nome as parada_nome"),
            ])
            ->get();

        // métricas
        $usuariosAtivos = DB::table('transporte_vinculos as tv')
            ->where('tv.empresa_id', $empresaId)
            ->where('tv.linha_id', $id)
            ->whereNull('tv.deleted_at')
            ->where(function ($w) {
                $w->whereNull('tv.data_inicio')->orWhere('tv.data_inicio', '<=', DB::raw('CURRENT_DATE'));
            })
            ->where(function ($w) {
                $w->whereNull('tv.data_fim')->orWhere('tv.data_fim', '>=', DB::raw('CURRENT_DATE'));
            })
            ->count();

        $capacidade = (int) ($linha->capacidade ?? 0);
        $disponivel = max($capacidade - $usuariosAtivos, 0);

        $inicioMes = $this->monthStart();
        $fimMes    = $this->monthEnd();

        $valorLinha = (float) DB::table('transporte_pedidos')
            ->where('empresa_id', $empresaId)
            ->where('linha_id', $id)
            ->whereNull('deleted_at')
            ->whereBetween('data_pedido', [$inicioMes, $fimMes])
            ->sum('valor_total');

        $usuariosMes = (int) DB::table('transporte_cartao_usos')
            ->where('empresa_id', $empresaId)
            ->where('linha_id', $id)
            ->whereBetween(DB::raw('DATE(data_hora_uso)'), [$inicioMes, $fimMes])
            ->distinct('usuario_id')
            ->count('usuario_id');

        $valorPorUsuario = $usuariosMes > 0 ? ($valorLinha / $usuariosMes) : 0.0;

        // Pedidos (financeiro tab)
        $pedidos = DB::table('transporte_pedidos')
            ->where('empresa_id', $empresaId)
            ->where('linha_id', $id)
            ->whereNull('deleted_at')
            ->orderByDesc('data_pedido')
            ->orderByDesc('id')
            ->get();

        return view('beneficios.transporte.linhas.edit', compact(
            'sub',
            'linha',
            'filiais',
            'filialId',
            'motoristas',
            'veiculos',
            'paradas',
            'vinculos',
            'capacidade',
            'usuariosAtivos',
            'disponivel',
            'valorLinha',
            'valorPorUsuario',
            'pedidos'
        ));
    }

    public function update(Request $request, $id)
    {
        $empresaId = $this->empresaId();
        $sub = $this->subdomain($request);

        $id = (int) $id;
        if ($id <= 0) {
            return redirect()
                ->route('beneficios.transporte.linhas.index', ['sub' => $sub])
                ->with('alert_error', 'ID inválido.');
        }

        $data = $request->all();

        $v = Validator::make($data, [
            'nome'            => ['required', 'string', 'max:191'],
            'tipo_linha'      => ['required', 'in:fretada,publica'],
            'controle_acesso' => ['required', 'in:cartao,ticket'],
            'status'          => ['required', 'in:ativo,inativo'],
            'filial_id'       => ['required', 'integer', 'min:1'],
            'veiculo_id'      => ['nullable', 'integer', 'min:1'],
            'motorista_id'    => ['nullable', 'integer', 'min:1'],
        ]);

        if ($v->fails()) {
            return back()
                ->withInput()
                ->with('alert_error', 'Revise os campos do formulário.')
                ->withErrors($v);
        }

        try {
            DB::beginTransaction();

            DB::table('transporte_linhas')
                ->where('empresa_id', $empresaId)
                ->where('id', $id)
                ->whereNull('deleted_at')
                ->update([
                    'nome'            => trim((string) $data['nome']),
                    'tipo_linha'      => $data['tipo_linha'],
                    'controle_acesso' => $data['controle_acesso'],
                    'status'          => $data['status'],
                    'motorista_id'    => !empty($data['motorista_id']) ? (int) $data['motorista_id'] : null,
                    'veiculo_id'      => !empty($data['veiculo_id']) ? (int) $data['veiculo_id'] : null,
                    'updated_at'      => now(),
                ]);

            // atualiza pivot (sem empresa_id)
            DB::table('transporte_linha_filiais')->where('linha_id', $id)->delete();
            DB::table('transporte_linha_filiais')->insert([
                'linha_id'   => $id,
                'filial_id'  => (int) $data['filial_id'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::commit();

            return redirect()
                ->route('beneficios.transporte.linhas.edit', ['sub' => $sub, 'id' => $id])
                ->with('alert_success', 'Linha atualizada com sucesso.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()
                ->withInput()
                ->with('alert_error', 'Erro ao salvar. Verifique e tente novamente.');
        }
    }

    public function destroy(Request $request, $id)
    {
        $empresaId = $this->empresaId();
        $sub = $this->subdomain($request);

        $id = (int) $id;
        if ($id <= 0) {
            return redirect()
                ->route('beneficios.transporte.linhas.index', ['sub' => $sub])
                ->with('alert_error', 'ID inválido.');
        }

        DB::table('transporte_linhas')
            ->where('empresa_id', $empresaId)
            ->where('id', $id)
            ->whereNull('deleted_at')
            ->update([
                'deleted_at' => now(),
                'updated_at' => now(),
            ]);

        return redirect()
            ->route('beneficios.transporte.linhas.index', ['sub' => $sub])
            ->with('alert_success', 'Linha removida.');
    }

    /*
    |--------------------------------------------------------------------------
    | Operação (placeholder)
    |--------------------------------------------------------------------------
    */

    public function operacao(Request $request, $id)
    {
        $sub = $this->subdomain($request);
        $id = (int) $id;

        return redirect()
            ->route('beneficios.transporte.linhas.edit', ['sub' => $sub, 'id' => $id]);
    }

    /*
    |--------------------------------------------------------------------------
    | Paradas (CRUD)
    |--------------------------------------------------------------------------
    */

    public function paradaStore(Request $request, $linhaId)
    {
        $empresaId = $this->empresaId();
        $linhaId = (int) $linhaId;

        $v = Validator::make($request->all(), [
            'nome'     => ['required', 'string', 'max:191'],
            'endereco' => ['nullable', 'string', 'max:255'],
            'horario'  => ['nullable'],
            'valor'    => ['required', 'numeric', 'min:0'],
            'ordem'    => ['required', 'integer', 'min:0'],
        ]);

        if ($v->fails()) {
            return response()->json(['message' => 'Revise os campos.'], 422);
        }

        $id = DB::table('transporte_paradas')->insertGetId([
            'empresa_id' => $empresaId,
            'linha_id'   => $linhaId,
            'nome'       => $request->string('nome'),
            'endereco'   => $request->string('endereco')->toString(),
            'horario'    => $request->input('horario') ?: null,
            'valor'      => (float) $request->input('valor', 0),
            'ordem'      => (int) $request->input('ordem', 0),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json(['id' => $id, 'message' => 'Parada adicionada.']);
    }

    public function paradaUpdate(Request $request, $linhaId, $paradaId)
    {
        $empresaId = $this->empresaId();
        $linhaId = (int) $linhaId;
        $paradaId = (int) $paradaId;

        $v = Validator::make($request->all(), [
            'nome'     => ['required', 'string', 'max:191'],
            'endereco' => ['nullable', 'string', 'max:255'],
            'horario'  => ['nullable'],
            'valor'    => ['required', 'numeric', 'min:0'],
            'ordem'    => ['required', 'integer', 'min:0'],
        ]);

        if ($v->fails()) {
            return response()->json(['message' => 'Revise os campos.'], 422);
        }

        DB::table('transporte_paradas')
            ->where('empresa_id', $empresaId)
            ->where('linha_id', $linhaId)
            ->where('id', $paradaId)
            ->whereNull('deleted_at')
            ->update([
                'nome'       => $request->string('nome'),
                'endereco'   => $request->string('endereco')->toString(),
                'horario'    => $request->input('horario') ?: null,
                'valor'      => (float) $request->input('valor', 0),
                'ordem'      => (int) $request->input('ordem', 0),
                'updated_at' => now(),
            ]);

        return response()->json(['message' => 'Parada atualizada.']);
    }

    public function paradaDestroy(Request $request, $linhaId, $paradaId)
    {
        $empresaId = $this->empresaId();
        $linhaId = (int) $linhaId;
        $paradaId = (int) $paradaId;

        // não pode remover se existir usuário ativo vinculado à parada
        $temVinculo = DB::table('transporte_vinculos')
            ->where('empresa_id', $empresaId)
            ->where('linha_id', $linhaId)
            ->where('parada_id', $paradaId)
            ->whereNull('deleted_at')
            ->where(function ($w) {
                $w->whereNull('data_inicio')->orWhere('data_inicio', '<=', DB::raw('CURRENT_DATE'));
            })
            ->where(function ($w) {
                $w->whereNull('data_fim')->orWhere('data_fim', '>=', DB::raw('CURRENT_DATE'));
            })
            ->exists();

        if ($temVinculo) {
            return response()->json(['message' => 'Não é possível remover: existe usuário vinculado à parada.'], 422);
        }

        DB::table('transporte_paradas')
            ->where('empresa_id', $empresaId)
            ->where('linha_id', $linhaId)
            ->where('id', $paradaId)
            ->whereNull('deleted_at')
            ->update([
                'deleted_at' => now(),
                'updated_at' => now(),
            ]);

        return response()->json(['message' => 'Parada removida.']);
    }

    /*
    |--------------------------------------------------------------------------
    | Vínculos
    |--------------------------------------------------------------------------
    */

    public function vinculoStore(Request $request, $linhaId)
    {
        $empresaId = $this->empresaId();
        $linhaId = (int) $linhaId;

        $v = Validator::make($request->all(), [
            'usuario_id'        => ['required', 'integer', 'min:1'],
            'parada_id'         => ['nullable', 'integer', 'min:1'],
            'tipo_acesso'       => ['required', 'in:cartao,ticket'],
            'numero_cartao'     => ['nullable', 'string', 'max:50'],
            'numero_vale_ticket'=> ['nullable', 'string', 'max:50'],
            'valor_passagem'    => ['required', 'numeric', 'min:0'],
            'data_inicio'       => ['nullable', 'date'],
            'data_fim'          => ['nullable', 'date'],
        ]);

        if ($v->fails()) {
            return response()->json(['message' => 'Revise os campos.'], 422);
        }

        // garante que o usuário é do tenant
        $usuarioOk = DB::table('usuarios')
            ->where('empresa_id', $empresaId)
            ->where('id', (int) $request->input('usuario_id'))
            ->whereNull('deleted_at')
            ->exists();

        if (!$usuarioOk) {
            return response()->json(['message' => 'Usuário inválido para esta empresa.'], 422);
        }

        $id = DB::table('transporte_vinculos')->insertGetId([
            'empresa_id'        => $empresaId,
            'usuario_id'        => (int) $request->input('usuario_id'),
            'linha_id'          => $linhaId,
            'parada_id'         => $request->filled('parada_id') ? (int) $request->input('parada_id') : null,
            'tipo_acesso'       => $request->input('tipo_acesso'),
            'numero_cartao'     => $request->input('numero_cartao') ?: null,
            'numero_vale_ticket'=> $request->input('numero_vale_ticket') ?: null,
            'valor_passagem'    => (float) $request->input('valor_passagem', 0),
            'data_inicio'       => $request->input('data_inicio') ?: null,
            'data_fim'          => $request->input('data_fim') ?: null,
            'status'            => 'ativo',
            'created_at'        => now(),
            'updated_at'        => now(),
        ]);

        return response()->json(['id' => $id, 'message' => 'Colaborador vinculado.']);
    }

    public function vinculoEncerrar(Request $request, $linhaId, $vinculoId)
    {
        $empresaId = $this->empresaId();
        $linhaId = (int) $linhaId;
        $vinculoId = (int) $vinculoId;

        $dataFim = $request->input('data_fim') ?: Carbon::now()->toDateString();

        DB::table('transporte_vinculos')
            ->where('empresa_id', $empresaId)
            ->where('linha_id', $linhaId)
            ->where('id', $vinculoId)
            ->whereNull('deleted_at')
            ->update([
                'data_fim'    => $dataFim,
                'status'      => 'inativo',
                'updated_at'  => now(),
            ]);

        return response()->json(['message' => 'Vínculo encerrado.']);
    }

    /*
    |--------------------------------------------------------------------------
    | AJAX: Select2 (opcional)
    |--------------------------------------------------------------------------
    */

    public function motoristasSearch(Request $request)
    {
        $empresaId = $this->empresaId();
        $q = trim((string) $request->get('q', ''));

        $items = DB::table('transporte_motoristas')
            ->select('id', 'nome')
            ->where('empresa_id', $empresaId)
            ->when($q !== '', fn($w) => $w->where('nome', 'ilike', "%{$q}%"))
            ->orderBy('nome')
            ->limit(20)
            ->get()
            ->map(fn($r) => ['id' => $r->id, 'text' => $r->nome]);

        return response()->json(['results' => $items]);
    }

    public function veiculosSearch(Request $request)
    {
        $empresaId = $this->empresaId();
        $q = trim((string) $request->get('q', ''));

        $items = DB::table('transporte_veiculos')
            ->select('id', 'placa', 'modelo')
            ->where('empresa_id', $empresaId)
            ->when($q !== '', function ($w) use ($q) {
                $w->where('placa', 'ilike', "%{$q}%")
                  ->orWhere('modelo', 'ilike', "%{$q}%");
            })
            ->orderByDesc('id')
            ->limit(20)
            ->get()
            ->map(fn($r) => [
                'id' => $r->id,
                'text' => trim(($r->placa ?? '') . ' - ' . ($r->modelo ?? ''))
            ]);

        return response()->json(['results' => $items]);
    }

    /*
    |--------------------------------------------------------------------------
    | Importar custos (mantido para rota existente)
    |--------------------------------------------------------------------------
    | OBS: você comentou que "transporte_custos" não existe.
    | Aqui fica como placeholder para não quebrar rota.
    */

    public function importarCustosForm(Request $request)
    {
        $sub = $this->subdomain($request);
        return redirect()
            ->route('beneficios.transporte.linhas.index', ['sub' => $sub])
            ->with('alert_error', 'Importação de custos ainda não configurada.');
    }

    public function importarCustos(Request $request)
    {
        $sub = $this->subdomain($request);
        return redirect()
            ->route('beneficios.transporte.linhas.index', ['sub' => $sub])
            ->with('alert_error', 'Importação de custos ainda não configurada.');
    }
}
