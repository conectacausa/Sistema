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
        return (string) ($request->route('sub') ?? '');
    }

    private function monthRange(): array
    {
        $start = date('Y-m-01');
        $end   = date('Y-m-t');
        return [$start, $end];
    }

    /*
    |----------------------------------------------------------------------
    | INDEX
    |----------------------------------------------------------------------
    | /beneficios/transporte/linhas
    */
    public function index(Request $request)
    {
        $empresaId = $this->empresaId();
        $sub = $this->subdomain($request);

        $q        = trim((string) $request->get('q', ''));
        $tipo     = trim((string) $request->get('tipo', ''));     // fretada/publico
        $filialId = (int) $request->get('filial_id', 0);

        $linhasQuery = DB::table('transporte_linhas as l')
            ->leftJoin('transporte_motoristas as m', 'm.id', '=', 'l.motorista_id')
            ->leftJoin('transporte_veiculos as v', 'v.id', '=', 'l.veiculo_id')
            ->where('l.empresa_id', $empresaId)
            ->whereNull('l.deleted_at');

        // filtro texto (linha / placa / modelo / motorista)
        if ($q !== '') {
            $linhasQuery->where(function ($w) use ($q) {
                $w->where('l.nome', 'ilike', "%{$q}%")
                    ->orWhere('v.placa', 'ilike', "%{$q}%")
                    ->orWhere('v.modelo', 'ilike', "%{$q}%")
                    ->orWhere('m.nome', 'ilike', "%{$q}%");
            });
        }

        // filtro tipo
        if ($tipo !== '') {
            $linhasQuery->where('l.tipo_linha', $tipo);
        }

        // filtro filial (join na tabela pivô)
        if ($filialId > 0) {
            $linhasQuery->join('transporte_linha_filiais as lf', 'lf.linha_id', '=', 'l.id')
                ->where('lf.filial_id', $filialId);
        }

        // vinculados ativos (hoje)
        $vinculadosAtivosSql = "
            (
                SELECT COUNT(1)
                FROM transporte_vinculos tv
                WHERE tv.linha_id = l.id
                  AND tv.deleted_at IS NULL
                  AND (tv.data_inicio IS NULL OR tv.data_inicio <= CURRENT_DATE)
                  AND (tv.data_fim IS NULL OR tv.data_fim >= CURRENT_DATE)
            )
        ";

        $linhas = $linhasQuery
            ->select([
                'l.id',
                'l.nome',
                'l.tipo_linha',
                'l.controle_acesso',
                'l.status',
                'l.motorista_id',
                'l.veiculo_id',
                DB::raw("m.nome as motorista_nome"),
                DB::raw("v.modelo as veiculo_modelo"),
                DB::raw("v.placa as veiculo_placa"),
                DB::raw("COALESCE(v.capacidade_passageiros, 0) as capacidade"),
                DB::raw("{$vinculadosAtivosSql} as vinculados_ativos"),
                DB::raw("GREATEST(COALESCE(v.capacidade_passageiros,0) - ({$vinculadosAtivosSql}), 0) as disponibilidade"),
            ])
            ->orderByDesc('l.id')
            ->paginate(25)
            ->appends($request->query());

        // Filiais (sem coluna "nome")
        $filiais = DB::table('filiais')
            ->where('empresa_id', $empresaId)
            ->whereNull('deleted_at')
            ->orderBy('id')
            ->get([
                'id',
                DB::raw("COALESCE(nome_fantasia, razao_social, ('Filial #'||id::text)) as nome"),
            ]);

        return view('beneficios.transporte.linhas.index', compact('sub', 'linhas', 'filiais', 'q', 'tipo', 'filialId'));
    }

    /*
    |----------------------------------------------------------------------
    | CREATE
    |----------------------------------------------------------------------
    | /beneficios/transporte/linhas/novo
    */
    public function create(Request $request)
    {
        $empresaId = $this->empresaId();
        $sub = $this->subdomain($request);

        $filiais = DB::table('filiais')
            ->where('empresa_id', $empresaId)
            ->whereNull('deleted_at')
            ->orderBy('id')
            ->get([
                'id',
                DB::raw("COALESCE(nome_fantasia, razao_social, ('Filial #'||id::text)) as nome"),
            ]);

        // Para popular select2 inicial (opcional)
        $motoristas = DB::table('transporte_motoristas')
            ->where('empresa_id', $empresaId)
            ->whereNull('deleted_at')
            ->orderBy('nome')
            ->limit(50)
            ->get(['id', 'nome']);

        $veiculos = DB::table('transporte_veiculos')
            ->where('empresa_id', $empresaId)
            ->whereNull('deleted_at')
            ->orderBy('placa')
            ->limit(50)
            ->get([
                'id',
                DB::raw("COALESCE(placa,'') as placa"),
                DB::raw("COALESCE(modelo,'') as modelo"),
                DB::raw("COALESCE(capacidade_passageiros,0) as capacidade_passageiros"),
            ]);

        return view('beneficios.transporte.linhas.create', compact('sub', 'filiais', 'motoristas', 'veiculos'));
    }

    /*
    |----------------------------------------------------------------------
    | STORE
    |----------------------------------------------------------------------
    */
    public function store(Request $request)
    {
        $empresaId = $this->empresaId();
        $sub = $this->subdomain($request);

        $validator = Validator::make($request->all(), [
            'nome'            => ['required', 'string', 'max:191'],
            'tipo_linha'      => ['required', 'in:fretada,publico'],
            'controle_acesso' => ['required', 'in:cartao,ticket'],
            'status'          => ['required', 'in:ativo,inativo'],
            'filial_id'       => ['required', 'integer', 'min:1'],
            'veiculo_id'      => ['nullable', 'integer', 'min:1'],
            'motorista_id'    => ['nullable', 'integer', 'min:1'],
        ]);

        if ($validator->fails()) {
            return back()->withInput()->with('alert_error', 'Revise os campos do formulário.');
        }

        $id = DB::table('transporte_linhas')->insertGetId([
            'empresa_id'      => $empresaId,
            'nome'            => trim((string) $request->nome),
            'tipo_linha'      => (string) $request->tipo_linha,
            'controle_acesso' => (string) $request->controle_acesso,
            'status'          => (string) $request->status,
            'motorista_id'    => $request->motorista_id ? (int) $request->motorista_id : null,
            'veiculo_id'      => $request->veiculo_id ? (int) $request->veiculo_id : null,
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);

        // pivô (não tem empresa_id/deleted_at)
        DB::table('transporte_linha_filiais')->insert([
            'linha_id'   => $id,
            'filial_id'  => (int) $request->filial_id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()
            ->route('beneficios.transporte.linhas.edit', ['sub' => $sub, 'id' => $id])
            ->with('alert_success', 'Linha criada com sucesso!');
    }

    /*
    |----------------------------------------------------------------------
    | EDIT
    |----------------------------------------------------------------------
    | /beneficios/transporte/linhas/{id}/editar
    */
    public function edit(Request $request, int $id)
    {
        $empresaId = $this->empresaId();
        $sub = $this->subdomain($request);

        $linha = DB::table('transporte_linhas')
            ->where('empresa_id', $empresaId)
            ->where('id', $id)
            ->whereNull('deleted_at')
            ->first();

        if (!$linha) {
            return redirect()
                ->route('beneficios.transporte.linhas.index', ['sub' => $sub])
                ->with('alert_error', 'Linha não encontrada.');
        }

        $filiais = DB::table('filiais')
            ->where('empresa_id', $empresaId)
            ->whereNull('deleted_at')
            ->orderBy('id')
            ->get([
                'id',
                DB::raw("COALESCE(nome_fantasia, razao_social, ('Filial #'||id::text)) as nome"),
            ]);

        $linhaFiliais = DB::table('transporte_linha_filiais')
            ->where('linha_id', $id)
            ->pluck('filial_id')
            ->toArray();

        $paradas = DB::table('transporte_paradas')
            ->where('empresa_id', $empresaId)
            ->where('linha_id', $id)
            ->whereNull('deleted_at')
            ->orderBy('ordem')
            ->orderBy('id')
            ->get();

        // Vínculos: tabela usa usuario_id (não colaborador_id)
        $vinculos = DB::table('transporte_vinculos as tv')
            ->leftJoin('usuarios as u', 'u.id', '=', 'tv.usuario_id')
            ->leftJoin('transporte_paradas as p', 'p.id', '=', 'tv.parada_id')
            ->where('tv.empresa_id', $empresaId)
            ->where('tv.linha_id', $id)
            ->whereNull('tv.deleted_at')
            ->orderByRaw("COALESCE(u.nome_completo, '') asc")
            ->get([
                'tv.*',
                DB::raw("u.nome_completo as usuario_nome"),
                DB::raw("u.email as usuario_email"),
                DB::raw("p.nome as parada_nome"),
                DB::raw("
                    (
                        SELECT cs.saldo
                        FROM transporte_cartoes_saldos cs
                        WHERE cs.empresa_id = tv.empresa_id
                          AND cs.numero_cartao = tv.numero_cartao
                        ORDER BY cs.data_referencia DESC NULLS LAST, cs.id DESC
                        LIMIT 1
                    ) as saldo_atual
                "),
            ]);

        // Cards (capacidade / usuários ativos / disponível)
        $capacidade = (int) (DB::table('transporte_veiculos')
            ->where('empresa_id', $empresaId)
            ->where('id', $linha->veiculo_id)
            ->whereNull('deleted_at')
            ->value('capacidade_passageiros') ?? 0);

        $usuariosAtivos = (int) DB::table('transporte_vinculos')
            ->where('empresa_id', $empresaId)
            ->where('linha_id', $id)
            ->whereNull('deleted_at')
            ->where(function ($w) {
                $w->whereNull('data_inicio')->orWhere('data_inicio', '<=', DB::raw('CURRENT_DATE'));
            })
            ->where(function ($w) {
                $w->whereNull('data_fim')->orWhere('data_fim', '>=', DB::raw('CURRENT_DATE'));
            })
            ->count();

        $disponivel = max($capacidade - $usuariosAtivos, 0);

        // Financeiro mês atual
        [$mStart, $mEnd] = $this->monthRange();

        $valorLinhaMes = (float) (DB::table('transporte_pedidos')
            ->where('empresa_id', $empresaId)
            ->where('linha_id', $id)
            ->whereNull('deleted_at')
            ->whereBetween('data_pedido', [$mStart, $mEnd])
            ->sum('valor_total') ?? 0);

        // usuários que "usaram" no mês corrente (aproximação: vínculo ativo em algum dia do mês)
        $usuariosMes = (int) DB::table('transporte_vinculos')
            ->where('empresa_id', $empresaId)
            ->where('linha_id', $id)
            ->whereNull('deleted_at')
            ->where(function ($w) use ($mEnd) {
                $w->whereNull('data_inicio')->orWhere('data_inicio', '<=', $mEnd);
            })
            ->where(function ($w) use ($mStart) {
                $w->whereNull('data_fim')->orWhere('data_fim', '>=', $mStart);
            })
            ->distinct('usuario_id')
            ->count('usuario_id');

        $valorPorUsuario = $usuariosMes > 0 ? ($valorLinhaMes / $usuariosMes) : 0.0;

        $pedidos = DB::table('transporte_pedidos')
            ->where('empresa_id', $empresaId)
            ->where('linha_id', $id)
            ->whereNull('deleted_at')
            ->whereBetween('data_pedido', [$mStart, $mEnd])
            ->orderByDesc('data_pedido')
            ->orderByDesc('id')
            ->get();

        return view('beneficios.transporte.linhas.edit', compact(
            'sub',
            'linha',
            'filiais',
            'linhaFiliais',
            'paradas',
            'vinculos',
            'capacidade',
            'usuariosAtivos',
            'disponivel',
            'valorLinhaMes',
            'valorPorUsuario',
            'pedidos'
        ));
    }

    /*
    |----------------------------------------------------------------------
    | UPDATE
    |----------------------------------------------------------------------
    */
    public function update(Request $request, int $id)
    {
        $empresaId = $this->empresaId();
        $sub = $this->subdomain($request);

        $validator = Validator::make($request->all(), [
            'nome'            => ['required', 'string', 'max:191'],
            'tipo_linha'      => ['required', 'in:fretada,publico'],
            'controle_acesso' => ['required', 'in:cartao,ticket'],
            'status'          => ['required', 'in:ativo,inativo'],
            'filial_id'       => ['required', 'integer', 'min:1'],
            'veiculo_id'      => ['nullable', 'integer', 'min:1'],
            'motorista_id'    => ['nullable', 'integer', 'min:1'],
        ]);

        if ($validator->fails()) {
            return back()->withInput()->with('alert_error', 'Revise os campos do formulário.');
        }

        $ok = DB::table('transporte_linhas')
            ->where('empresa_id', $empresaId)
            ->where('id', $id)
            ->whereNull('deleted_at')
            ->update([
                'nome'            => trim((string) $request->nome),
                'tipo_linha'      => (string) $request->tipo_linha,
                'controle_acesso' => (string) $request->controle_acesso,
                'status'          => (string) $request->status,
                'motorista_id'    => $request->motorista_id ? (int) $request->motorista_id : null,
                'veiculo_id'      => $request->veiculo_id ? (int) $request->veiculo_id : null,
                'updated_at'      => now(),
            ]);

        if (!$ok) {
            return back()->with('alert_error', 'Não foi possível salvar. Verifique a linha e tente novamente.');
        }

        // pivô: garante 1 filial (se quiser multi depois, a gente muda para array)
        DB::table('transporte_linha_filiais')->where('linha_id', $id)->delete();
        DB::table('transporte_linha_filiais')->insert([
            'linha_id'   => $id,
            'filial_id'  => (int) $request->filial_id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()
            ->route('beneficios.transporte.linhas.edit', ['sub' => $sub, 'id' => $id])
            ->with('alert_success', 'Linha salva com sucesso!');
    }

    /*
    |----------------------------------------------------------------------
    | DESTROY (soft delete)
    |----------------------------------------------------------------------
    */
    public function destroy(Request $request, int $id)
    {
        $empresaId = $this->empresaId();
        $sub = $this->subdomain($request);

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
    |----------------------------------------------------------------------
    | SELECT2 - SEARCH (opcional para seu live search)
    |----------------------------------------------------------------------
    */
    public function motoristasSearch(Request $request)
    {
        $empresaId = $this->empresaId();
        $term = trim((string) $request->get('q', ''));

        $q = DB::table('transporte_motoristas')
            ->where('empresa_id', $empresaId)
            ->whereNull('deleted_at');

        if ($term !== '') {
            $q->where('nome', 'ilike', "%{$term}%");
        }

        $items = $q->orderBy('nome')->limit(30)->get(['id', 'nome']);

        return response()->json([
            'results' => $items->map(fn($i) => ['id' => $i->id, 'text' => $i->nome])->values(),
        ]);
    }

    public function veiculosSearch(Request $request)
    {
        $empresaId = $this->empresaId();
        $term = trim((string) $request->get('q', ''));

        $q = DB::table('transporte_veiculos')
            ->where('empresa_id', $empresaId)
            ->whereNull('deleted_at');

        if ($term !== '') {
            $q->where(function ($w) use ($term) {
                $w->where('placa', 'ilike', "%{$term}%")
                  ->orWhere('modelo', 'ilike', "%{$term}%")
                  ->orWhere('marca', 'ilike', "%{$term}%");
            });
        }

        $items = $q->orderBy('placa')->limit(30)->get([
            'id',
            DB::raw("COALESCE(placa,'') as placa"),
            DB::raw("COALESCE(modelo,'') as modelo"),
        ]);

        return response()->json([
            'results' => $items->map(fn($i) => [
                'id' => $i->id,
                'text' => trim($i->placa . ' - ' . $i->modelo),
            ])->values(),
        ]);
    }
}
