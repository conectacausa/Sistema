<?php

namespace App\Http\Controllers\Beneficios;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TransporteLinhasController extends Controller
{
    private function empresaId(): int
    {
        return (int) (auth()->user()->empresa_id ?? 0);
    }

    private function nowDate(): string
    {
        return date('Y-m-d');
    }

    private function filialLabelSelect(): string
    {
        // filiais NÃO tem coluna "nome" no seu banco
        return "COALESCE(nome_fantasia, razao_social, ('Filial #'||id::text)) as nome";
    }

    /*
    |--------------------------------------------------------------------------
    | LISTAGEM
    |--------------------------------------------------------------------------
    */
    public function index(Request $request, string $sub)
    {
        $empresaId = $this->empresaId();
        $hoje = $this->nowDate();

        $q        = trim((string) $request->get('q', ''));
        $tipo     = trim((string) $request->get('tipo', ''));      // fretada/publica
        $filialId = (int) $request->get('filial_id', 0);

        // Filiais para o filtro (dropdown simples)
        $filiais = DB::table('filiais')
            ->select('id', DB::raw($this->filialLabelSelect()))
            ->where('empresa_id', $empresaId)
            ->whereNull('deleted_at')
            ->orderBy('id')
            ->get();

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
                DB::raw("COALESCE(m.nome,'') as motorista_nome"),
                DB::raw("COALESCE(v.modelo,'') as veiculo_modelo"),
                DB::raw("COALESCE(v.placa,'') as veiculo_placa"),
                DB::raw("COALESCE(v.capacidade_passageiros, 0) as capacidade"),
                DB::raw("(
                    SELECT COUNT(1)
                    FROM transporte_vinculos tv
                    WHERE tv.linha_id = l.id
                      AND tv.deleted_at IS NULL
                      AND (tv.data_inicio IS NULL OR tv.data_inicio <= DATE '{$hoje}')
                      AND (tv.data_fim IS NULL OR tv.data_fim >= DATE '{$hoje}')
                ) as vinculados_ativos"),
            ]);

        // Filtro filial (se existir tabela de vínculo)
        if ($filialId > 0 && Schema::hasTable('transporte_linha_filiais')) {
            $query->whereExists(function ($sq) use ($filialId) {
                $sq->select(DB::raw(1))
                    ->from('transporte_linha_filiais as lf')
                    ->whereColumn('lf.linha_id', 'l.id')
                    ->where('lf.filial_id', $filialId)
                    ->whereNull('lf.deleted_at');
            });
        }

        if ($tipo !== '') {
            $query->where('l.tipo_linha', $tipo);
        }

        if ($q !== '') {
            $query->where(function ($w) use ($q) {
                $w->where('l.nome', 'ilike', "%{$q}%")
                    ->orWhere('m.nome', 'ilike', "%{$q}%")
                    ->orWhere('v.modelo', 'ilike', "%{$q}%")
                    ->orWhere('v.placa', 'ilike', "%{$q}%");
            });
        }

        $linhas = $query
            ->orderByDesc('l.id')
            ->paginate(25)
            ->appends($request->query());

        $linhas->getCollection()->transform(function ($row) {
            $cap = (int) ($row->capacidade ?? 0);
            $atv = (int) ($row->vinculados_ativos ?? 0);
            $row->disponibilidade = max(0, $cap - $atv);
            return $row;
        });

        return view('beneficios.transporte.linhas.index', [
            'sub'     => $sub,
            'linhas'  => $linhas,
            'filiais' => $filiais,
            'filtros' => [
                'q' => $q,
                'tipo' => $tipo,
                'filial_id' => $filialId,
            ],
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | CREATE
    |--------------------------------------------------------------------------
    */
    public function create(Request $request, string $sub)
    {
        $empresaId = $this->empresaId();

        $filiais = DB::table('filiais')
            ->select('id', DB::raw($this->filialLabelSelect()))
            ->where('empresa_id', $empresaId)
            ->whereNull('deleted_at')
            ->orderBy('id')
            ->get();

        $veiculos = DB::table('transporte_veiculos')
            ->select('id', 'modelo', 'placa', 'capacidade_passageiros')
            ->where('empresa_id', $empresaId)
            ->orderBy('modelo')
            ->get();

        $motoristas = DB::table('transporte_motoristas')
            ->select('id', 'nome')
            ->where('empresa_id', $empresaId)
            ->orderBy('nome')
            ->get();

        return view('beneficios.transporte.linhas.create', [
            'sub' => $sub,
            'filiais' => $filiais,
            'veiculos' => $veiculos,
            'motoristas' => $motoristas,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | STORE
    |--------------------------------------------------------------------------
    */
    public function store(Request $request, string $sub)
    {
        $empresaId = $this->empresaId();

        $data = $request->validate([
            'nome'            => ['required', 'string', 'max:255'],
            'tipo_linha'      => ['required', 'in:fretada,publica'],
            'controle_acesso' => ['required', 'in:cartao,ticket'],
            'status'          => ['required', 'in:ativo,inativo'],
            'filial_id'       => ['required', 'integer', 'min:1'],
            'veiculo_id'      => ['required', 'integer', 'min:1'],
            'motorista_id'    => ['required', 'integer', 'min:1'],
        ]);

        DB::beginTransaction();
        try {
            $linhaId = DB::table('transporte_linhas')->insertGetId([
                'empresa_id'      => $empresaId,
                'nome'            => $data['nome'],
                'tipo_linha'      => $data['tipo_linha'],
                'controle_acesso' => $data['controle_acesso'],
                'status'          => $data['status'],
                'motorista_id'    => $data['motorista_id'],
                'veiculo_id'      => $data['veiculo_id'],
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);

            if (Schema::hasTable('transporte_linha_filiais')) {
                DB::table('transporte_linha_filiais')->insert([
                    'empresa_id' => $empresaId,
                    'linha_id'   => $linhaId,
                    'filial_id'  => (int) $data['filial_id'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::commit();

            return redirect()
                ->route('beneficios.transporte.linhas.edit', ['sub' => $sub, 'id' => $linhaId])
                ->with('alert_success', 'Linha cadastrada com sucesso.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('alert_error', 'Erro ao salvar linha: ' . $e->getMessage());
        }
    }

    /*
    |--------------------------------------------------------------------------
    | EDIT
    |--------------------------------------------------------------------------
    */
    public function edit(Request $request, string $sub, int $id)
    {
        $empresaId = $this->empresaId();
        $hoje = $this->nowDate();

        $linha = DB::table('transporte_linhas as l')
            ->leftJoin('transporte_motoristas as m', 'm.id', '=', 'l.motorista_id')
            ->leftJoin('transporte_veiculos as v', 'v.id', '=', 'l.veiculo_id')
            ->where('l.empresa_id', $empresaId)
            ->where('l.id', $id)
            ->whereNull('l.deleted_at')
            ->select([
                'l.*',
                DB::raw("COALESCE(m.nome,'') as motorista_nome"),
                DB::raw("COALESCE(v.modelo,'') as veiculo_modelo"),
                DB::raw("COALESCE(v.placa,'') as veiculo_placa"),
                DB::raw("COALESCE(v.capacidade_passageiros,0) as capacidade_passageiros"),
            ])
            ->first();

        if (!$linha) {
            return redirect()
                ->route('beneficios.transporte.linhas.index', ['sub' => $sub])
                ->with('alert_error', 'Linha não encontrada.');
        }

        $filiais = DB::table('filiais')
            ->select('id', DB::raw($this->filialLabelSelect()))
            ->where('empresa_id', $empresaId)
            ->whereNull('deleted_at')
            ->orderBy('id')
            ->get();

        $filialSelecionada = null;
        if (Schema::hasTable('transporte_linha_filiais')) {
            $filialSelecionada = DB::table('transporte_linha_filiais')
                ->where('empresa_id', $empresaId)
                ->where('linha_id', $id)
                ->whereNull('deleted_at')
                ->value('filial_id');
        }

        $veiculos = DB::table('transporte_veiculos')
            ->select('id', 'modelo', 'placa', 'capacidade_passageiros')
            ->where('empresa_id', $empresaId)
            ->orderBy('modelo')
            ->get();

        $motoristas = DB::table('transporte_motoristas')
            ->select('id', 'nome')
            ->where('empresa_id', $empresaId)
            ->orderBy('nome')
            ->get();

        $paradas = DB::table('transporte_paradas')
            ->where('empresa_id', $empresaId)
            ->where('linha_id', $id)
            ->whereNull('deleted_at')
            ->orderBy('ordem')
            ->get();

        $vinculos = DB::table('transporte_vinculos as tv')
            ->leftJoin('usuarios as u', 'u.id', '=', 'tv.usuario_id')
            ->leftJoin('colaboradores as c', 'c.id', '=', 'u.colaborador_id')
            ->leftJoin('transporte_paradas as p', 'p.id', '=', 'tv.parada_id')
            ->where('tv.empresa_id', $empresaId)
            ->where('tv.linha_id', $id)
            ->whereNull('tv.deleted_at')
            ->select([
                'tv.*',
                DB::raw("COALESCE(c.nome, u.nome_completo, '') as colaborador_nome"),
                DB::raw("COALESCE(c.matricula, '') as colaborador_matricula"),
                DB::raw("COALESCE(c.cpf, '') as colaborador_cpf"),
                DB::raw("COALESCE(p.identificacao,'') as parada_nome"),
                DB::raw("COALESCE(p.horario,'') as parada_horario"),
                DB::raw("(
                    SELECT s.saldo
                    FROM transporte_cartoes_saldos s
                    WHERE s.empresa_id = tv.empresa_id
                      AND s.numero_cartao = tv.numero_cartao
                    ORDER BY s.data_referencia DESC NULLS LAST, s.id DESC
                    LIMIT 1
                ) as saldo_atual"),
            ])
            ->orderBy(DB::raw("COALESCE(c.nome, u.nome_completo, '')"))
            ->get();

        $usuariosAtivos = DB::table('transporte_vinculos as tv')
            ->where('tv.empresa_id', $empresaId)
            ->where('tv.linha_id', $id)
            ->whereNull('tv.deleted_at')
            ->where(function ($w) use ($hoje) {
                $w->whereNull('tv.data_inicio')->orWhere('tv.data_inicio', '<=', $hoje);
            })
            ->where(function ($w) use ($hoje) {
                $w->whereNull('tv.data_fim')->orWhere('tv.data_fim', '>=', $hoje);
            })
            ->count();

        $capacidade = (int) ($linha->capacidade_passageiros ?? 0);
        $disponivel = max(0, $capacidade - $usuariosAtivos);

        return view('beneficios.transporte.linhas.edit', [
            'sub' => $sub,
            'linha' => $linha,
            'filiais' => $filiais,
            'filialSelecionada' => $filialSelecionada,
            'veiculos' => $veiculos,
            'motoristas' => $motoristas,
            'capacidade' => $capacidade,
            'usuariosAtivos' => $usuariosAtivos,
            'disponivel' => $disponivel,
            'paradas' => $paradas,
            'vinculos' => $vinculos,
            'pedidos' => collect(),
            'valorLinhaMes' => 0,
            'valorPorUsuario' => 0,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE
    |--------------------------------------------------------------------------
    */
    public function update(Request $request, string $sub, int $id)
    {
        $empresaId = $this->empresaId();

        $data = $request->validate([
            'nome'            => ['required', 'string', 'max:255'],
            'tipo_linha'      => ['required', 'in:fretada,publica'],
            'controle_acesso' => ['required', 'in:cartao,ticket'],
            'status'          => ['required', 'in:ativo,inativo'],
            'filial_id'       => ['required', 'integer', 'min:1'],
            'veiculo_id'      => ['required', 'integer', 'min:1'],
            'motorista_id'    => ['required', 'integer', 'min:1'],
        ]);

        DB::beginTransaction();
        try {
            DB::table('transporte_linhas')
                ->where('empresa_id', $empresaId)
                ->where('id', $id)
                ->update([
                    'nome'            => $data['nome'],
                    'tipo_linha'      => $data['tipo_linha'],
                    'controle_acesso' => $data['controle_acesso'],
                    'status'          => $data['status'],
                    'motorista_id'    => $data['motorista_id'],
                    'veiculo_id'      => $data['veiculo_id'],
                    'updated_at'      => now(),
                ]);

            if (Schema::hasTable('transporte_linha_filiais')) {
                DB::table('transporte_linha_filiais')
                    ->where('empresa_id', $empresaId)
                    ->where('linha_id', $id)
                    ->update(['deleted_at' => now()]);

                DB::table('transporte_linha_filiais')->insert([
                    'empresa_id' => $empresaId,
                    'linha_id'   => $id,
                    'filial_id'  => (int) $data['filial_id'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::commit();

            return redirect()
                ->route('beneficios.transporte.linhas.edit', ['sub' => $sub, 'id' => $id])
                ->with('alert_success', 'Linha atualizada com sucesso.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('alert_error', 'Erro ao atualizar: ' . $e->getMessage());
        }
    }

    public function destroy(Request $request, string $sub, int $id)
    {
        $empresaId = $this->empresaId();

        DB::table('transporte_linhas')
            ->where('empresa_id', $empresaId)
            ->where('id', $id)
            ->update(['deleted_at' => now()]);

        return redirect()
            ->route('beneficios.transporte.linhas.index', ['sub' => $sub])
            ->with('alert_success', 'Linha removida.');
    }

    public function operacao(Request $request, string $sub, int $id)
    {
        return redirect()
            ->route('beneficios.transporte.linhas.edit', ['sub' => $sub, 'id' => $id]);
    }

    public function paradaStore(Request $request, string $sub, int $linhaId)
    {
        $empresaId = $this->empresaId();

        $data = $request->validate([
            'identificacao' => ['required', 'string', 'max:255'],
            'endereco'      => ['nullable', 'string'],
            'ordem'         => ['nullable', 'integer'],
            'horario'       => ['nullable', 'string', 'max:10'],
            'valor'         => ['required', 'numeric', 'min:0'],
        ]);

        DB::table('transporte_paradas')->insert([
            'empresa_id'     => $empresaId,
            'linha_id'       => $linhaId,
            'identificacao'  => $data['identificacao'],
            'endereco'       => $data['endereco'] ?? null,
            'ordem'          => $data['ordem'] ?? 0,
            'horario'        => $data['horario'] ?? null,
            'valor'          => $data['valor'],
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);

        return back()->with('alert_success', 'Parada adicionada.');
    }

    public function paradaUpdate(Request $request, string $sub, int $linhaId, int $paradaId)
    {
        $empresaId = $this->empresaId();

        $data = $request->validate([
            'identificacao' => ['required', 'string', 'max:255'],
            'endereco'      => ['nullable', 'string'],
            'ordem'         => ['nullable', 'integer'],
            'horario'       => ['nullable', 'string', 'max:10'],
            'valor'         => ['required', 'numeric', 'min:0'],
        ]);

        DB::table('transporte_paradas')
            ->where('empresa_id', $empresaId)
            ->where('linha_id', $linhaId)
            ->where('id', $paradaId)
            ->update([
                'identificacao' => $data['identificacao'],
                'endereco'      => $data['endereco'] ?? null,
                'ordem'         => $data['ordem'] ?? 0,
                'horario'       => $data['horario'] ?? null,
                'valor'         => $data['valor'],
                'updated_at'    => now(),
            ]);

        return back()->with('alert_success', 'Parada atualizada.');
    }

    public function paradaDestroy(Request $request, string $sub, int $linhaId, int $paradaId)
    {
        $empresaId = $this->empresaId();

        $temVinculo = DB::table('transporte_vinculos')
            ->where('empresa_id', $empresaId)
            ->where('linha_id', $linhaId)
            ->where('parada_id', $paradaId)
            ->whereNull('deleted_at')
            ->exists();

        if ($temVinculo) {
            return back()->with('alert_error', 'Não é possível remover: existem usuários vinculados a esta parada.');
        }

        DB::table('transporte_paradas')
            ->where('empresa_id', $empresaId)
            ->where('linha_id', $linhaId)
            ->where('id', $paradaId)
            ->update(['deleted_at' => now()]);

        return back()->with('alert_success', 'Parada removida.');
    }

    public function vinculoStore(Request $request, string $sub, int $linhaId)
    {
        $empresaId = $this->empresaId();

        $data = $request->validate([
            'usuario_id'         => ['required', 'integer', 'min:1'],
            'parada_id'          => ['nullable', 'integer', 'min:1'],
            'tipo_acesso'        => ['required', 'in:cartao,ticket'],
            'numero_cartao'      => ['nullable', 'string', 'max:50'],
            'numero_vale_ticket' => ['nullable', 'string', 'max:50'],
            'valor_passagem'     => ['required', 'numeric', 'min:0'],
            'data_inicio'        => ['nullable', 'date'],
            'data_fim'           => ['nullable', 'date'],
            'status'             => ['required', 'in:ativo,inativo'],
            'observacoes'        => ['nullable', 'string'],
        ]);

        DB::table('transporte_vinculos')->insert([
            'empresa_id'         => $empresaId,
            'usuario_id'         => (int) $data['usuario_id'],
            'linha_id'           => $linhaId,
            'parada_id'          => !empty($data['parada_id']) ? (int) $data['parada_id'] : null,
            'tipo_acesso'        => $data['tipo_acesso'],
            'numero_cartao'      => $data['numero_cartao'] ?? null,
            'numero_vale_ticket' => $data['numero_vale_ticket'] ?? null,
            'valor_passagem'     => $data['valor_passagem'],
            'data_inicio'        => $data['data_inicio'] ?? null,
            'data_fim'           => $data['data_fim'] ?? null,
            'status'             => $data['status'],
            'observacoes'        => $data['observacoes'] ?? null,
            'created_at'         => now(),
            'updated_at'         => now(),
        ]);

        return back()->with('alert_success', 'Colaborador vinculado.');
    }

    public function vinculoEncerrar(Request $request, string $sub, int $linhaId, int $vinculoId)
    {
        $empresaId = $this->empresaId();

        $data = $request->validate([
            'data_fim' => ['required', 'date'],
        ]);

        DB::table('transporte_vinculos')
            ->where('empresa_id', $empresaId)
            ->where('linha_id', $linhaId)
            ->where('id', $vinculoId)
            ->update([
                'data_fim'    => $data['data_fim'],
                'status'      => 'inativo',
                'updated_at'  => now(),
            ]);

        return back()->with('alert_success', 'Uso encerrado.');
    }

    public function importarCustosForm(Request $request, string $sub)
    {
        return view('beneficios.transporte.relatorios.importar_custos', ['sub' => $sub]);
    }

    public function importarCustos(Request $request, string $sub)
    {
        return back()->with('alert_error', 'Importação de custos: implementar na próxima etapa.');
    }
}
