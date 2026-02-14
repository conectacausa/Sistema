<?php

namespace App\Http\Controllers\Beneficios;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;

class TransporteLinhasController extends Controller
{
    // Tabelas
    private const T_LINHAS         = 'transporte_linhas';
    private const T_LINHA_FILIAIS  = 'transporte_linha_filiais';
    private const T_MOTORISTAS     = 'transporte_motoristas';
    private const T_VEICULOS       = 'transporte_veiculos';
    private const T_VINCULOS       = 'transporte_vinculos';
    private const T_FILIAIS        = 'filiais';

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

    private function baseFiliaisQuery(int $empresaId)
    {
        $q = DB::table(self::T_FILIAIS)->where('empresa_id', $empresaId);

        if ($this->hasColumn(self::T_FILIAIS, 'deleted_at')) {
            $q->whereNull('deleted_at');
        }

        return $q;
    }

    private function baseMotoristasQuery(int $empresaId)
    {
        $q = DB::table(self::T_MOTORISTAS)->where('empresa_id', $empresaId);

        if ($this->hasColumn(self::T_MOTORISTAS, 'deleted_at')) {
            $q->whereNull('deleted_at');
        }

        return $q;
    }

    private function baseVeiculosQuery(int $empresaId)
    {
        $q = DB::table(self::T_VEICULOS)->where('empresa_id', $empresaId);

        if ($this->hasColumn(self::T_VEICULOS, 'deleted_at')) {
            $q->whereNull('deleted_at');
        }

        return $q;
    }

    private function baseLinhasQuery(int $empresaId)
    {
        $q = DB::table(self::T_LINHAS)->where('empresa_id', $empresaId);

        if ($this->hasColumn(self::T_LINHAS, 'deleted_at')) {
            $q->whereNull('deleted_at');
        }

        return $q;
    }

    /*
    |--------------------------------------------------------------------------
    | Index (Listagem)
    |--------------------------------------------------------------------------
    */
    public function index(Request $request, string $sub)
    {
        $empresaId = $this->empresaId();

        $q        = trim((string) $request->get('q', ''));
        $tipo     = trim((string) $request->get('tipo', '')); // publica|fretada
        $filialId = (int) $request->get('filial_id', 0);

        // Para selects/filtros
        $motoristas = $this->baseMotoristasQuery($empresaId)->orderBy('nome')->get();
        $veiculos   = $this->baseVeiculosQuery($empresaId)->orderBy('id', 'desc')->get();
        $filiais    = $this->baseFiliaisQuery($empresaId)->orderBy('id', 'asc')->get();

        // ✅ coluna real no seu banco
        $hasCapacidade = $this->hasColumn(self::T_VEICULOS, 'capacidade_passageiros');

        $linhasQuery = DB::table(self::T_LINHAS . ' as l')
            ->leftJoin(self::T_MOTORISTAS . ' as m', 'm.id', '=', 'l.motorista_id')
            ->leftJoin(self::T_VEICULOS . ' as v', 'v.id', '=', 'l.veiculo_id')
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
            ])
            ->when(
                $hasCapacidade,
                fn($qq) => $qq->addSelect('v.capacidade_passageiros as capacidade'),
                fn($qq) => $qq->selectRaw('0 as capacidade')
            )
            ->selectRaw("
                (
                    SELECT COUNT(1)
                    FROM " . self::T_VINCULOS . " tv
                    WHERE tv.linha_id = l.id
                      AND tv.deleted_at IS NULL
                      AND (tv.data_fim IS NULL OR tv.data_fim >= CURRENT_DATE)
                ) as vinculados_ativos
            ")
            ->where('l.empresa_id', $empresaId);

        if ($this->hasColumn(self::T_LINHAS, 'deleted_at')) {
            $linhasQuery->whereNull('l.deleted_at');
        }

        if ($q !== '') {
            $linhasQuery->where(function ($w) use ($q) {
                $w->where('l.nome', 'ilike', "%{$q}%")
                    ->orWhere('m.nome', 'ilike', "%{$q}%")
                    ->orWhere('v.modelo', 'ilike', "%{$q}%")
                    ->orWhere('v.placa', 'ilike', "%{$q}%");
            });
        }

        if (in_array($tipo, ['publica', 'fretada'], true)) {
            $linhasQuery->where('l.tipo_linha', $tipo);
        }

        if ($filialId > 0) {
            $linhasQuery->whereExists(function ($sq) use ($filialId) {
                $sq->select(DB::raw(1))
                    ->from(self::T_LINHA_FILIAIS . ' as lf')
                    ->whereColumn('lf.linha_id', 'l.id')
                    ->where('lf.filial_id', $filialId);
            });
        }

        $linhas = $linhasQuery
            ->orderBy('l.id', 'desc')
            ->paginate(25)
            ->withQueryString();

        return view('beneficios.transporte.linhas.index', compact(
            'sub',
            'linhas',
            'q',
            'tipo',
            'filialId',
            'motoristas',
            'veiculos',
            'filiais'
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | Create
    |--------------------------------------------------------------------------
    */
    public function create(Request $request, string $sub)
    {
        $empresaId = $this->empresaId();

        $motoristas = $this->baseMotoristasQuery($empresaId)->orderBy('nome')->get();
        $veiculos   = $this->baseVeiculosQuery($empresaId)->orderBy('id', 'desc')->get();
        $filiais    = $this->baseFiliaisQuery($empresaId)->orderBy('id', 'asc')->get();

        return view('beneficios.transporte.linhas.create', compact('sub', 'motoristas', 'veiculos', 'filiais'));
    }

    /*
    |--------------------------------------------------------------------------
    | Store
    |--------------------------------------------------------------------------
    */
    public function store(Request $request, string $sub)
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
        ], [
            'nome.required'            => 'Informe o nome da linha.',
            'tipo_linha.required'      => 'Selecione o tipo da linha.',
            'controle_acesso.required' => 'Selecione o tipo de controle.',
            'status.required'          => 'Selecione a situação.',
            'filial_id.required'       => 'Selecione uma filial.',
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Existem campos obrigatórios pendentes.');
        }

        try {
            DB::beginTransaction();

            $linhaId = DB::table(self::T_LINHAS)->insertGetId([
                'empresa_id'      => $empresaId,
                'nome'            => trim((string) $request->input('nome')),
                'tipo_linha'      => $request->input('tipo_linha'),
                'controle_acesso' => $request->input('controle_acesso'),
                'status'          => $request->input('status'),
                'veiculo_id'      => $request->input('veiculo_id') ?: null,
                'motorista_id'    => $request->input('motorista_id') ?: null,
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);

            // Pivot filial (single select)
            DB::table(self::T_LINHA_FILIAIS)->insert([
                'linha_id'   => $linhaId,
                'filial_id'  => (int) $request->input('filial_id'),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::commit();

            return redirect()
                ->route('beneficios.transporte.linhas.edit', ['sub' => $sub, 'id' => $linhaId])
                ->with('success', 'Linha criada com sucesso.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()
                ->withInput()
                ->with('error', 'Não foi possível salvar a linha. Verifique os dados e tente novamente.');
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

        $linha = $this->baseLinhasQuery($empresaId)->where('id', $id)->first();
        if (!$linha) {
            return redirect()
                ->route('beneficios.transporte.linhas.index', ['sub' => $sub])
                ->with('error', 'Linha não encontrada.');
        }

        $motoristas = $this->baseMotoristasQuery($empresaId)->orderBy('nome')->get();
        $veiculos   = $this->baseVeiculosQuery($empresaId)->orderBy('id', 'desc')->get();
        $filiais    = $this->baseFiliaisQuery($empresaId)->orderBy('id', 'asc')->get();

        $filialAtualId = (int) (DB::table(self::T_LINHA_FILIAIS)
            ->where('linha_id', $id)
            ->orderBy('id', 'asc')
            ->value('filial_id') ?? 0);

        return view('beneficios.transporte.linhas.edit', compact(
            'sub',
            'linha',
            'motoristas',
            'veiculos',
            'filiais',
            'filialAtualId'
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | Update
    |--------------------------------------------------------------------------
    */
    public function update(Request $request, string $sub, int $id)
    {
        $empresaId = $this->empresaId();

        $linha = $this->baseLinhasQuery($empresaId)->where('id', $id)->first();
        if (!$linha) {
            return redirect()
                ->route('beneficios.transporte.linhas.index', ['sub' => $sub])
                ->with('error', 'Linha não encontrada.');
        }

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
            return back()
                ->withErrors($validator)
                ->withInput()
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

            // Pivot filial (single select)
            $exists = DB::table(self::T_LINHA_FILIAIS)->where('linha_id', $id)->exists();
            if ($exists) {
                DB::table(self::T_LINHA_FILIAIS)
                    ->where('linha_id', $id)
                    ->update([
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

            return redirect()
                ->route('beneficios.transporte.linhas.edit', ['sub' => $sub, 'id' => $id])
                ->with('success', 'Linha atualizada com sucesso.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()
                ->withInput()
                ->with('error', 'Não foi possível atualizar a linha. Tente novamente.');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Destroy
    |--------------------------------------------------------------------------
    */
    public function destroy(Request $request, string $sub, int $id)
    {
        $empresaId = $this->empresaId();

        try {
            if ($this->hasColumn(self::T_LINHAS, 'deleted_at')) {
                DB::table(self::T_LINHAS)
                    ->where('empresa_id', $empresaId)
                    ->where('id', $id)
                    ->update([
                        'deleted_at' => now(),
                        'updated_at' => now(),
                    ]);
            } else {
                DB::table(self::T_LINHAS)
                    ->where('empresa_id', $empresaId)
                    ->where('id', $id)
                    ->delete();
            }

            return redirect()
                ->route('beneficios.transporte.linhas.index', ['sub' => $sub])
                ->with('success', 'Linha removida com sucesso.');
        } catch (\Throwable $e) {
            return redirect()
                ->route('beneficios.transporte.linhas.index', ['sub' => $sub])
                ->with('error', 'Não foi possível remover a linha.');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Operação (placeholder)
    |--------------------------------------------------------------------------
    */
    public function operacao(Request $request, string $sub, int $id)
    {
        return view('beneficios.transporte.linhas.operacao', compact('sub', 'id'));
    }
}
