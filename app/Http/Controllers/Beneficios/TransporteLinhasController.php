<?php

namespace App\Http\Controllers\Beneficios;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class TransporteLinhasController extends Controller
{
    private function empresaId(): int
    {
        return (int) (auth()->user()->empresa_id ?? 0);
    }

    private function subdomain(Request $request): string
    {
        // Seu tenant usa Route::domain('{sub}.conecttarh.com.br')
        return (string) $request->route('sub');
    }

    private function monthStart(): string
    {
        return now()->startOfMonth()->toDateString();
    }

    private function monthEnd(): string
    {
        return now()->endOfMonth()->toDateString();
    }

    /*
    |--------------------------------------------------------------------------
    | INDEX
    |--------------------------------------------------------------------------
    */
    public function index(Request $request)
    {
        $empresaId = $this->empresaId();
        $sub = $this->subdomain($request);

        $q      = trim((string) $request->get('q', ''));
        $tipo   = trim((string) $request->get('tipo', ''));     // fretada|publica
        $filial = (int) $request->get('filial_id', 0);

        // Filiais (sem coluna "nome")
        $filiais = DB::table('filiais as f')
            ->select([
                'f.id',
                DB::raw("COALESCE(f.nome_fantasia, f.razao_social, ('Filial #'||f.id::text)) as nome"),
            ])
            ->where('f.empresa_id', $empresaId)
            ->whereNull('f.deleted_at')
            ->orderBy('f.id')
            ->get();

        $rows = DB::table('transporte_linhas as l')
            ->leftJoin('transporte_motoristas as m', 'm.id', '=', 'l.motorista_id')
            ->leftJoin('transporte_veiculos as v', 'v.id', '=', 'l.veiculo_id')
            ->leftJoin('transporte_linha_filiais as lf', 'lf.linha_id', '=', 'l.id')
            ->leftJoin('filiais as f', 'f.id', '=', 'lf.filial_id')
            ->where('l.empresa_id', $empresaId)
            ->whereNull('l.deleted_at')
            ->when($q !== '', function ($w) use ($q) {
                $w->where(function ($qq) use ($q) {
                    $qq->where('l.nome', 'ilike', "%{$q}%")
                        ->orWhere('m.nome', 'ilike', "%{$q}%")
                        ->orWhere('v.placa', 'ilike', "%{$q}%")
                        ->orWhere('v.modelo', 'ilike', "%{$q}%");
                });
            })
            ->when(in_array($tipo, ['fretada', 'publica'], true), fn ($w) => $w->where('l.tipo_linha', $tipo))
            ->when($filial > 0, fn ($w) => $w->where('lf.filial_id', $filial))
            ->select([
                'l.id',
                'l.nome',
                'l.tipo_linha',
                'l.controle_acesso',
                'l.status',
                'l.motorista_id',
                'l.veiculo_id',
                'm.nome as motorista_nome',
                'v.modelo as veiculo_modelo',
                'v.placa as veiculo_placa',
                DB::raw('COALESCE(v.capacidade_passageiros, 0) as capacidade'),
                DB::raw("(SELECT COUNT(1)
                          FROM transporte_vinculos tv
                          WHERE tv.linha_id = l.id
                            AND tv.empresa_id = l.empresa_id
                            AND tv.deleted_at IS NULL
                            AND (tv.data_fim IS NULL OR tv.data_fim >= CURRENT_DATE)
                            AND (tv.data_inicio IS NULL OR tv.data_inicio <= CURRENT_DATE)
                         ) as vinculados_ativos"),
            ])
            ->orderByDesc('l.id')
            ->paginate(25)
            ->withQueryString();

        return view('beneficios.transporte.linhas.index', compact(
            'sub',
            'rows',
            'filiais',
            'q',
            'tipo',
            'filial'
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | CREATE / STORE
    |--------------------------------------------------------------------------
    */
    public function create(Request $request)
    {
        $empresaId = $this->empresaId();
        $sub = $this->subdomain($request);

        $filiais = DB::table('filiais as f')
            ->select([
                'f.id',
                DB::raw("COALESCE(f.nome_fantasia, f.razao_social, ('Filial #'||f.id::text)) as nome"),
            ])
            ->where('f.empresa_id', $empresaId)
            ->whereNull('f.deleted_at')
            ->orderBy('f.id')
            ->get();

        $motoristas = DB::table('transporte_motoristas')
            ->select('id', 'nome')
            ->where('empresa_id', $empresaId)
            ->orderBy('nome')
            ->get();

        $veiculos = DB::table('transporte_veiculos')
            ->select('id', 'placa', 'modelo', 'capacidade_passageiros')
            ->where('empresa_id', $empresaId)
            ->orderByDesc('id')
            ->get();

        return view('beneficios.transporte.linhas.create', compact(
            'sub',
            'filiais',
            'motoristas',
            'veiculos'
        ));
    }

    public function store(Request $request)
    {
        $empresaId = $this->empresaId();
        $sub = $this->subdomain($request);

        $data = $request->all();

        $v = Validator::make($data, [
            'nome'           => ['required', 'string', 'max:191'],
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
                'empresa_id'       => $empresaId,
                'nome'             => trim((string) $data['nome']),
                'tipo_linha'       => $data['tipo_linha'],
                'controle_acesso'  => $data['controle_acesso'],
                'status'           => $data['status'],
                'motorista_id'     => !empty($data['motorista_id']) ? (int) $data['motorista_id'] : null,
                'veiculo_id'       => !empty($data['veiculo_id']) ? (int) $data['veiculo_id'] : null,
                'created_at'       => now(),
                'updated_at'       => now(),
            ]);

            // Pivot NÃO TEM empresa_id/deleted_at
            DB::table('transporte_linha_filiais')->insert([
                'linha_id'    => $linhaId,
                'filial_id'   => (int) $data['filial_id'],
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);

            DB::commit();

            return redirect()
                ->route('beneficios.transporte.linhas.edit', ['sub' => $sub, 'id' => $linhaId])
                ->with('alert_success', 'Linha criada com sucesso.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()
                ->withInput()
                ->with('alert_error', 'Erro ao salvar. ' . $e->getMessage());
        }
    }

    /*
    |--------------------------------------------------------------------------
    | EDIT / UPDATE
    |--------------------------------------------------------------------------
    */
    public function edit(Request $request, $id)
    {
        $empresaId = $this->empresaId();
        $sub = $this->subdomain($request);

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
                'm.nome as motorista_nome',
                'v.modelo as veiculo_modelo',
                'v.placa as veiculo_placa',
                DB::raw('COALESCE(v.capacidade_passageiros, 0) as capacidade'),
            ])
            ->first();

        if (!$linha) {
            return redirect()
                ->route('beneficios.transporte.linhas.index', ['sub' => $sub])
                ->with('alert_error', 'Linha não encontrada.');
        }

        $filiais = DB::table('filiais as f')
            ->select([
                'f.id',
                DB::raw("COALESCE(f.nome_fantasia, f.razao_social, ('Filial #'||f.id::text)) as nome"),
            ])
            ->where('f.empresa_id', $empresaId)
            ->whereNull('f.deleted_at')
            ->orderBy('f.id')
            ->get();

        // Pivot sem empresa_id/deleted_at
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
            ->orderByDesc('id')
            ->get();

        $paradas = DB::table('transporte_paradas')
            ->where('empresa_id', $empresaId)
            ->where('linha_id', $id)
            ->whereNull('deleted_at')
            ->orderBy('ordem')
            ->orderBy('id')
            ->get();

        // Vínculos: usa usuario_id; colaborador vem por usuarios.colaborador_id
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
        $fimMes = $this->monthEnd();

        $valorLinha = (float) DB::table('transporte_pedidos')
            ->where('empresa_id', $empresaId)
            ->where('linha_id', $id)
            ->whereNull('deleted_at')
            ->whereBetween('data_pedido', [$inicioMes, $fimMes])
            ->sum('valor_total');

        // usuários que usaram no mês corrente (tabela de usos)
        $usuariosMes = (int) DB::table('transporte_cartao_usos')
            ->where('empresa_id', $empresaId)
            ->where('linha_id', $id)
            ->whereBetween(DB::raw('DATE(data_hora_uso)'), [$inicioMes, $fimMes])
            ->distinct('usuario_id')
            ->count('usuario_id');

        $valorPorUsuario = $usuariosMes > 0 ? ($valorLinha / $usuariosMes) : 0.0;

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
            'nome'           => ['required', 'string', 'max:191'],
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
                    'tipo_linha'       => $data['tipo_linha'],
                    'controle_acesso'  => $data['controle_acesso'],
                    'status'           => $data['status'],
                    'motorista_id'     => !empty($data['motorista_id']) ? (int) $data['motorista_id'] : null,
                    'veiculo_id'       => !empty($data['veiculo_id']) ? (int) $data['veiculo_id'] : null,
                    'updated_at'       => now(),
                ]);

            // Pivot sem empresa_id/deleted_at
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
                ->with('alert_error', 'Erro ao salvar. ' . $e->getMessage());
        }
    }

    /*
    |--------------------------------------------------------------------------
    | DESTROY (soft delete)
    |--------------------------------------------------------------------------
    */
    public function destroy(Request $request, $id)
    {
        $empresaId = $this->empresaId();
        $sub = $this->subdomain($request);

        $id = (int) $id;

        DB::table('transporte_linhas')
            ->where('empresa_id', $empresaId)
            ->where('id', $id)
            ->whereNull('deleted_at')
            ->update(['deleted_at' => now(), 'updated_at' => now()]);

        return redirect()
            ->route('beneficios.transporte.linhas.index', ['sub' => $sub])
            ->with('alert_success', 'Linha removida com sucesso.');
    }

    /*
    |--------------------------------------------------------------------------
    | OPERAÇÃO (mantida p/ rota existente)
    |--------------------------------------------------------------------------
    */
    public function operacao(Request $request, $id)
    {
        $sub = $this->subdomain($request);

        // Você pode apontar para uma tela futura de "operação"
        return redirect()
            ->route('beneficios.transporte.linhas.edit', ['sub' => $sub, 'id' => (int) $id]);
    }

    /*
    |--------------------------------------------------------------------------
    | PARADAS (CRUD básico p/ modal)
    |--------------------------------------------------------------------------
    */
    public function paradaStore(Request $request, $linhaId)
    {
        $empresaId = $this->empresaId();
        $linhaId = (int) $linhaId;

        $v = Validator::make($request->all(), [
            'nome'     => ['required', 'string', 'max:191'],
            'endereco' => ['nullable', 'string', 'max:255'],
            'ordem'    => ['required', 'integer', 'min:0'],
            'horario'  => ['nullable', 'date_format:H:i'],
            'valor'    => ['required', 'numeric', 'min:0'],
        ]);

        if ($v->fails()) {
            return response()->json(['ok' => false, 'message' => 'Dados inválidos', 'errors' => $v->errors()], 422);
        }

        $id = DB::table('transporte_paradas')->insertGetId([
            'empresa_id' => $empresaId,
            'linha_id'   => $linhaId,
            'nome'       => trim((string) $request->get('nome')),
            'endereco'   => $request->get('endereco'),
            'ordem'      => (int) $request->get('ordem'),
            'horario'    => $request->get('horario'),
            'valor'      => (float) $request->get('valor'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json(['ok' => true, 'id' => $id]);
    }

    public function paradaUpdate(Request $request, $linhaId, $paradaId)
    {
        $empresaId = $this->empresaId();

        $linhaId = (int) $linhaId;
        $paradaId = (int) $paradaId;

        $v = Validator::make($request->all(), [
            'nome'     => ['required', 'string', 'max:191'],
            'endereco' => ['nullable', 'string', 'max:255'],
            'ordem'    => ['required', 'integer', 'min:0'],
            'horario'  => ['nullable', 'date_format:H:i'],
            'valor'    => ['required', 'numeric', 'min:0'],
        ]);

        if ($v->fails()) {
            return response()->json(['ok' => false, 'message' => 'Dados inválidos', 'errors' => $v->errors()], 422);
        }

        DB::table('transporte_paradas')
            ->where('empresa_id', $empresaId)
            ->where('linha_id', $linhaId)
            ->where('id', $paradaId)
            ->whereNull('deleted_at')
            ->update([
                'nome'       => trim((string) $request->get('nome')),
                'endereco'   => $request->get('endereco'),
                'ordem'      => (int) $request->get('ordem'),
                'horario'    => $request->get('horario'),
                'valor'      => (float) $request->get('valor'),
                'updated_at' => now(),
            ]);

        return response()->json(['ok' => true]);
    }

    public function paradaDestroy(Request $request, $linhaId, $paradaId)
    {
        $empresaId = $this->empresaId();

        $linhaId = (int) $linhaId;
        $paradaId = (int) $paradaId;

        // Regra: não remover se existir vínculo usando a parada
        $temVinculo = DB::table('transporte_vinculos')
            ->where('empresa_id', $empresaId)
            ->where('linha_id', $linhaId)
            ->where('parada_id', $paradaId)
            ->whereNull('deleted_at')
            ->exists();

        if ($temVinculo) {
            return response()->json(['ok' => false, 'message' => 'Não é possível remover: existe usuário vinculado nesta parada.'], 409);
        }

        DB::table('transporte_paradas')
            ->where('empresa_id', $empresaId)
            ->where('linha_id', $linhaId)
            ->where('id', $paradaId)
            ->whereNull('deleted_at')
            ->update(['deleted_at' => now(), 'updated_at' => now()]);

        return response()->json(['ok' => true]);
    }

    /*
    |--------------------------------------------------------------------------
    | VÍNCULOS
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
            'status'            => ['required', 'in:ativo,inativo'],
        ]);

        if ($v->fails()) {
            return response()->json(['ok' => false, 'message' => 'Dados inválidos', 'errors' => $v->errors()], 422);
        }

        $id = DB::table('transporte_vinculos')->insertGetId([
            'empresa_id'         => $empresaId,
            'usuario_id'         => (int) $request->get('usuario_id'),
            'linha_id'           => $linhaId,
            'parada_id'          => $request->get('parada_id') ? (int) $request->get('parada_id') : null,
            'tipo_acesso'        => $request->get('tipo_acesso'),
            'numero_cartao'      => $request->get('numero_cartao'),
            'numero_vale_ticket' => $request->get('numero_vale_ticket'),
            'valor_passagem'     => (float) $request->get('valor_passagem'),
            'data_inicio'        => $request->get('data_inicio'),
            'data_fim'           => $request->get('data_fim'),
            'status'             => $request->get('status', 'ativo'),
            'observacoes'        => $request->get('observacoes'),
            'created_at'         => now(),
            'updated_at'         => now(),
        ]);

        return response()->json(['ok' => true, 'id' => $id]);
    }

    public function vinculoEncerrar(Request $request, $linhaId, $vinculoId)
    {
        $empresaId = $this->empresaId();

        $linhaId = (int) $linhaId;
        $vinculoId = (int) $vinculoId;

        $v = Validator::make($request->all(), [
            'data_fim' => ['required', 'date'],
        ]);

        if ($v->fails()) {
            return response()->json(['ok' => false, 'message' => 'Data inválida', 'errors' => $v->errors()], 422);
        }

        DB::table('transporte_vinculos')
            ->where('empresa_id', $empresaId)
            ->where('linha_id', $linhaId)
            ->where('id', $vinculoId)
            ->whereNull('deleted_at')
            ->update([
                'data_fim'   => $request->get('data_fim'),
                'updated_at' => now(),
            ]);

        return response()->json(['ok' => true]);
    }

    /*
    |--------------------------------------------------------------------------
    | IMPORTAR CUSTOS (placeholder - tabela não existe)
    |--------------------------------------------------------------------------
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
