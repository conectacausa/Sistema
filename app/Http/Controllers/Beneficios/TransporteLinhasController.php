<?php

namespace App\Http\Controllers\Beneficios;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class TransporteLinhasController extends Controller
{
    // ✅ Ajuste apenas se seus migrations usaram outros nomes
    private const T_LINHAS   = 'transporte_linhas';
    private const T_LINHA_FILIAIS = 'transporte_linha_filiais';
    private const T_PARADAS  = 'transporte_paradas';
    private const T_VINCULOS = 'transporte_vinculos';
    private const T_MOTORISTAS = 'transporte_motoristas';
    private const T_VEICULOS = 'transporte_veiculos';
    private const T_FILIAIS  = 'filiais';
    private const T_COLABS   = 'colaboradores';
    private const T_CUSTOS   = 'transporte_linha_custos';

    private function empresaId(): int
    {
        return (int) (auth()->user()->empresa_id ?? 0);
    }

    private function now()
    {
        return now();
    }

    /*
    |--------------------------------------------------------------------------
    | INDEX
    |--------------------------------------------------------------------------
    */
    public function index(Request $request, string $sub)
    {
        $empresaId = $this->empresaId();
        $q = trim((string) $request->get('q', ''));

        // ✅ A view usa $motoristas, $veiculos e $filiais
        $motoristas = DB::table(self::T_MOTORISTAS)
            ->where('empresa_id', $empresaId)
            ->whereNull('deleted_at')
            ->orderBy('nome')
            ->get();

        $veiculos = DB::table(self::T_VEICULOS)
            ->where('empresa_id', $empresaId)
            ->whereNull('deleted_at')
            ->orderBy('id', 'desc')
            ->get();

        $filiais = DB::table(self::T_FILIAIS)
            ->where('empresa_id', $empresaId)
            ->whereNull('deleted_at')
            ->orderBy('id', 'asc')
            ->get();

        $linhas = DB::table(self::T_LINHAS . ' as l')
            ->select('l.*')
            ->where('l.empresa_id', $empresaId)
            ->whereNull('l.deleted_at')
            ->when($q !== '', function ($qq) use ($q) {
                $qq->where(function ($w) use ($q) {
                    $w->where('l.nome', 'ilike', "%{$q}%")
                        ->orWhere('l.tipo_linha', 'ilike', "%{$q}%")
                        ->orWhere('l.controle_acesso', 'ilike', "%{$q}%");
                });
            })
            ->orderBy('l.id', 'desc')
            ->paginate(20)
            ->withQueryString();

        return view('beneficios.transporte.linhas.index', compact(
            'sub',
            'linhas',
            'q',
            'motoristas',
            'veiculos',
            'filiais'
        ));
    }

    public function create(Request $request, string $sub)
    {
        $empresaId = $this->empresaId();

        $motoristas = DB::table(self::T_MOTORISTAS)
            ->where('empresa_id', $empresaId)
            ->whereNull('deleted_at')
            ->orderBy('nome')
            ->get();

        $veiculos = DB::table(self::T_VEICULOS)
            ->where('empresa_id', $empresaId)
            ->whereNull('deleted_at')
            ->orderBy('id', 'desc')
            ->get();

        $filiais = DB::table(self::T_FILIAIS)
            ->where('empresa_id', $empresaId)
            ->whereNull('deleted_at')
            ->orderBy('id', 'asc')
            ->get();

        return view('beneficios.transporte.linhas.create', compact('sub', 'motoristas', 'veiculos', 'filiais'));
    }

    public function store(Request $request, string $sub)
    {
        $empresaId = $this->empresaId();

        $v = Validator::make($request->all(), [
            'nome'            => 'required|string|max:255',
            'tipo_linha'      => 'required|in:publica,fretada',
            'controle_acesso' => 'required|in:cartao,ticket',
            'motorista_id'    => 'required|integer|min:1',
            'veiculo_id'      => 'required|integer|min:1',
            'status'          => 'nullable|in:ativo,inativo',
            'filiais'         => 'required|array|min:1',
            'filiais.*'       => 'integer|min:1',
            'observacoes'     => 'nullable|string',
        ]);

        if ($v->fails()) {
            return back()->withErrors($v)->withInput();
        }

        return DB::transaction(function () use ($request, $sub, $empresaId) {
            $id = DB::table(self::T_LINHAS)->insertGetId([
                'empresa_id'      => $empresaId,
                'nome'            => $request->string('nome')->toString(),
                'tipo_linha'      => $request->string('tipo_linha')->toString(),
                'controle_acesso' => $request->string('controle_acesso')->toString(),
                'motorista_id'    => (int) $request->get('motorista_id'),
                'veiculo_id'      => (int) $request->get('veiculo_id'),
                'status'          => $request->get('status', 'ativo'),
                'observacoes'     => $request->get('observacoes'),
                'created_at'      => $this->now(),
                'updated_at'      => $this->now(),
            ]);

            $filiais = array_values(array_unique(array_map('intval', (array) $request->get('filiais', []))));
            foreach ($filiais as $filialId) {
                DB::table(self::T_LINHA_FILIAIS)->insert([
                    'linha_id'   => $id,
                    'filial_id'  => $filialId,
                    'created_at' => $this->now(),
                    'updated_at' => $this->now(),
                ]);
            }

            return redirect()->route('beneficios.transporte.linhas.edit', ['sub' => $sub, 'id' => $id])
                ->with('success', 'Linha criada com sucesso.');
        });
    }

    public function edit(Request $request, string $sub, int $id)
    {
        $empresaId = $this->empresaId();

        $linha = DB::table(self::T_LINHAS)
            ->where('empresa_id', $empresaId)
            ->where('id', $id)
            ->whereNull('deleted_at')
            ->first();

        abort_unless($linha, 404);

        $motoristas = DB::table(self::T_MOTORISTAS)
            ->where('empresa_id', $empresaId)
            ->whereNull('deleted_at')
            ->orderBy('nome')
            ->get();

        $veiculos = DB::table(self::T_VEICULOS)
            ->where('empresa_id', $empresaId)
            ->whereNull('deleted_at')
            ->orderBy('id', 'desc')
            ->get();

        $filiais = DB::table(self::T_FILIAIS)
            ->where('empresa_id', $empresaId)
            ->whereNull('deleted_at')
            ->orderBy('id', 'asc')
            ->get();

        $linhaFiliais = DB::table(self::T_LINHA_FILIAIS)
            ->where('linha_id', $id)
            ->pluck('filial_id')
            ->map(fn ($x) => (int) $x)
            ->toArray();

        return view('beneficios.transporte.linhas.edit', compact('sub', 'linha', 'motoristas', 'veiculos', 'filiais', 'linhaFiliais'));
    }

    public function update(Request $request, string $sub, int $id)
    {
        $empresaId = $this->empresaId();

        $v = Validator::make($request->all(), [
            'nome'            => 'required|string|max:255',
            'tipo_linha'      => 'required|in:publica,fretada',
            'controle_acesso' => 'required|in:cartao,ticket',
            'motorista_id'    => 'required|integer|min:1',
            'veiculo_id'      => 'required|integer|min:1',
            'status'          => 'nullable|in:ativo,inativo',
            'filiais'         => 'required|array|min:1',
            'filiais.*'       => 'integer|min:1',
            'observacoes'     => 'nullable|string',
        ]);

        if ($v->fails()) {
            return back()->withErrors($v)->withInput();
        }

        return DB::transaction(function () use ($request, $sub, $empresaId, $id) {
            $ok = DB::table(self::T_LINHAS)
                ->where('empresa_id', $empresaId)
                ->where('id', $id)
                ->whereNull('deleted_at')
                ->update([
                    'nome'            => $request->string('nome')->toString(),
                    'tipo_linha'      => $request->string('tipo_linha')->toString(),
                    'controle_acesso' => $request->string('controle_acesso')->toString(),
                    'motorista_id'    => (int) $request->get('motorista_id'),
                    'veiculo_id'      => (int) $request->get('veiculo_id'),
                    'status'          => $request->get('status', 'ativo'),
                    'observacoes'     => $request->get('observacoes'),
                    'updated_at'      => $this->now(),
                ]);

            if (!$ok) {
                return back()->with('error', 'Linha não encontrada.');
            }

            DB::table(self::T_LINHA_FILIAIS)->where('linha_id', $id)->delete();

            $filiais = array_values(array_unique(array_map('intval', (array) $request->get('filiais', []))));
            foreach ($filiais as $filialId) {
                DB::table(self::T_LINHA_FILIAIS)->insert([
                    'linha_id'   => $id,
                    'filial_id'  => $filialId,
                    'created_at' => $this->now(),
                    'updated_at' => $this->now(),
                ]);
            }

            return back()->with('success', 'Linha atualizada com sucesso.');
        });
    }

    public function destroy(Request $request, string $sub, int $id)
    {
        $empresaId = $this->empresaId();

        DB::table(self::T_LINHAS)
            ->where('empresa_id', $empresaId)
            ->where('id', $id)
            ->update([
                'deleted_at' => $this->now(),
                'updated_at' => $this->now(),
            ]);

        return redirect()->route('beneficios.transporte.linhas.index', ['sub' => $sub])
            ->with('success', 'Linha removida com sucesso.');
    }

    public function operacao(Request $request, string $sub, int $id)
    {
        $empresaId = $this->empresaId();

        $linha = DB::table(self::T_LINHAS . ' as l')
            ->leftJoin(self::T_MOTORISTAS . ' as m', 'm.id', '=', 'l.motorista_id')
            ->leftJoin(self::T_VEICULOS . ' as v', 'v.id', '=', 'l.veiculo_id')
            ->select('l.*', 'm.nome as motorista_nome', 'v.placa as veiculo_placa', 'v.modelo as veiculo_modelo')
            ->where('l.empresa_id', $empresaId)
            ->where('l.id', $id)
            ->whereNull('l.deleted_at')
            ->first();

        abort_unless($linha, 404);

        $paradas = DB::table(self::T_PARADAS)
            ->where('linha_id', $id)
            ->whereNull('deleted_at')
            ->orderBy('hora', 'asc')
            ->get();

        $vinculos = DB::table(self::T_VINCULOS . ' as tv')
            ->leftJoin(self::T_COLABS . ' as c', 'c.id', '=', 'tv.colaborador_id')
            ->leftJoin(self::T_PARADAS . ' as p', 'p.id', '=', 'tv.parada_id')
            ->select(
                'tv.*',
                'c.nome_completo as colaborador_nome',
                'c.matricula as colaborador_matricula',
                'p.nome as parada_nome',
                'p.hora as parada_hora'
            )
            ->where('tv.linha_id', $id)
            ->whereNull('tv.deleted_at')
            ->orderBy('tv.id', 'desc')
            ->get();

        $colaboradores = DB::table(self::T_COLABS)
            ->where('empresa_id', $empresaId)
            ->whereNull('deleted_at')
            ->orderBy('nome_completo')
            ->limit(5000)
            ->get();

        return view('beneficios.transporte.linhas.operacao', compact('sub', 'linha', 'paradas', 'vinculos', 'colaboradores'));
    }

    public function paradaStore(Request $request, string $sub, int $linhaId)
    {
        $v = Validator::make($request->all(), [
            'nome'  => 'required|string|max:255',
            'hora'  => 'required|string|max:10',
            'valor' => 'nullable|numeric|min:0',
        ]);

        if ($v->fails()) {
            return back()->withErrors($v)->withInput();
        }

        DB::table(self::T_PARADAS)->insert([
            'linha_id'   => $linhaId,
            'nome'       => $request->string('nome')->toString(),
            'hora'       => $request->string('hora')->toString(),
            'valor'      => $request->get('valor'),
            'created_at' => $this->now(),
            'updated_at' => $this->now(),
        ]);

        return back()->with('success', 'Parada adicionada.');
    }

    public function paradaUpdate(Request $request, string $sub, int $linhaId, int $paradaId)
    {
        $v = Validator::make($request->all(), [
            'nome'  => 'required|string|max:255',
            'hora'  => 'required|string|max:10',
            'valor' => 'nullable|numeric|min:0',
        ]);

        if ($v->fails()) {
            return back()->withErrors($v)->withInput();
        }

        DB::table(self::T_PARADAS)
            ->where('linha_id', $linhaId)
            ->where('id', $paradaId)
            ->update([
                'nome'       => $request->string('nome')->toString(),
                'hora'       => $request->string('hora')->toString(),
                'valor'      => $request->get('valor'),
                'updated_at' => $this->now(),
            ]);

        return back()->with('success', 'Parada atualizada.');
    }

    public function paradaDestroy(Request $request, string $sub, int $linhaId, int $paradaId)
    {
        DB::table(self::T_PARADAS)
            ->where('linha_id', $linhaId)
            ->where('id', $paradaId)
            ->update([
                'deleted_at' => $this->now(),
                'updated_at' => $this->now(),
            ]);

        return back()->with('success', 'Parada removida.');
    }

    public function vinculoStore(Request $request, string $sub, int $linhaId)
    {
        $v = Validator::make($request->all(), [
            'colaborador_id' => 'required|integer|min:1',
            'parada_id'      => 'required|integer|min:1',
            'tipo'           => 'required|in:cartao,ticket',
            'cartao_numero'  => 'nullable|string|max:50',
            'ticket_ref'     => 'nullable|string|max:50',
            'valor_passagem' => 'required|numeric|min:0',
            'data_inicio'    => 'required|date',
        ]);

        if ($v->fails()) {
            return back()->withErrors($v)->withInput();
        }

        DB::table(self::T_VINCULOS)->insert([
            'linha_id'       => $linhaId,
            'parada_id'      => (int) $request->get('parada_id'),
            'colaborador_id' => (int) $request->get('colaborador_id'),
            'tipo'           => $request->string('tipo')->toString(),
            'cartao_numero'  => $request->get('cartao_numero'),
            'ticket_ref'     => $request->get('ticket_ref'),
            'valor_passagem' => $request->get('valor_passagem'),
            'data_inicio'    => $request->get('data_inicio'),
            'data_fim'       => null,
            'created_at'     => $this->now(),
            'updated_at'     => $this->now(),
        ]);

        return back()->with('success', 'Colaborador vinculado à linha.');
    }

    public function vinculoEncerrar(Request $request, string $sub, int $linhaId, int $vinculoId)
    {
        $v = Validator::make($request->all(), [
            'data_fim' => 'required|date',
        ]);

        if ($v->fails()) {
            return back()->withErrors($v)->withInput();
        }

        DB::table(self::T_VINCULOS)
            ->where('linha_id', $linhaId)
            ->where('id', $vinculoId)
            ->update([
                'data_fim'   => $request->get('data_fim'),
                'updated_at' => $this->now(),
            ]);

        return back()->with('success', 'Vínculo encerrado.');
    }

    public function importarCustosForm(Request $request, string $sub)
    {
        $empresaId = $this->empresaId();

        $linhas = DB::table(self::T_LINHAS)
            ->where('empresa_id', $empresaId)
            ->whereNull('deleted_at')
            ->orderBy('nome')
            ->get();

        return view('beneficios.transporte.linhas.importar_custos', compact('sub', 'linhas'));
    }

    public function importarCustos(Request $request, string $sub)
    {
        $empresaId = $this->empresaId();

        $v = Validator::make($request->all(), [
            'linha_id'   => 'required|integer|min:1',
            'mes'        => 'required|date_format:Y-m',
            'valor'      => 'required|numeric|min:0',
            'observacao' => 'nullable|string|max:255',
        ]);

        if ($v->fails()) {
            return back()->withErrors($v)->withInput();
        }

        DB::table(self::T_CUSTOS)->insert([
            'empresa_id' => $empresaId,
            'linha_id'   => (int) $request->get('linha_id'),
            'mes'        => $request->string('mes')->toString(),
            'valor'      => $request->get('valor'),
            'observacao' => $request->get('observacao'),
            'created_at' => $this->now(),
            'updated_at' => $this->now(),
        ]);

        return back()->with('success', 'Custo registrado com sucesso.');
    }
}
