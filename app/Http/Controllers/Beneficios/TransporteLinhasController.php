<?php

namespace App\Http\Controllers\Beneficios;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\Beneficios\Transporte\TransporteLinha;
use App\Models\Beneficios\Transporte\TransporteParada;
use App\Models\Beneficios\Transporte\TransporteVinculo;
use App\Models\Beneficios\Transporte\TransporteMotorista;
use App\Models\Beneficios\Transporte\TransporteVeiculo;

class TransporteLinhasController extends TransporteBaseController
{
    private int $TELA_ID = 21;

    public function index(Request $request, string $sub)
    {
        if ($r = $this->requireTela($request, $sub, $this->TELA_ID)) return $r;

        $empresaId = $this->empresaId();
        $q = trim((string) $request->get('q', ''));

        $linhas = TransporteLinha::query()
            ->where('empresa_id', $empresaId)
            ->when($q, fn($qq) => $qq->where('nome', 'ilike', "%{$q}%"))
            ->with(['motorista:id,nome', 'veiculo:id,placa,modelo'])
            ->orderBy('id', 'desc')
            ->paginate(20)
            ->appends($request->all());

        return view('beneficios.transporte.linhas.index', compact('sub', 'linhas', 'q'));
    }

    public function create(Request $request, string $sub)
    {
        if ($r = $this->requireTela($request, $sub, $this->TELA_ID)) return $r;

        $empresaId = $this->empresaId();

        $motoristas = TransporteMotorista::query()->where('empresa_id', $empresaId)->where('status', 'ativo')->orderBy('nome')->get();
        $veiculos   = TransporteVeiculo::query()->where('empresa_id', $empresaId)->where('status', 'ativo')->orderBy('placa')->get();

        // Filiais: ajuste o Model se o seu for outro
        $filiais = DB::table('filiais')->where('empresa_id', $empresaId)->orderBy('nome')->get();

        return view('beneficios.transporte.linhas.create', compact('sub','motoristas','veiculos','filiais'));
    }

    public function store(Request $request, string $sub)
    {
        if ($r = $this->requireTela($request, $sub, $this->TELA_ID)) return $r;

        $empresaId = $this->empresaId();

        $v = Validator::make($request->all(), [
            'nome' => 'required|string|max:191',
            'tipo_linha' => 'required|in:publica,fretada',
            'controle_acesso' => 'required|in:cartao,ticket',
            'motorista_id' => 'required|integer',
            'veiculo_id' => 'required|integer',
            'filiais' => 'required|array|min:1',
            'filiais.*' => 'integer',
            'status' => 'nullable|in:ativo,inativo',
            'observacoes' => 'nullable|string',
        ]);

        if ($v->fails()) {
            return back()->withErrors($v)->withInput();
        }

        DB::transaction(function () use ($request, $empresaId) {
            $linha = TransporteLinha::create([
                'empresa_id' => $empresaId,
                'nome' => $request->nome,
                'tipo_linha' => $request->tipo_linha,
                'controle_acesso' => $request->controle_acesso,
                'motorista_id' => (int) $request->motorista_id,
                'veiculo_id' => (int) $request->veiculo_id,
                'status' => $request->status ?: 'ativo',
                'observacoes' => $request->observacoes,
            ]);

            $linha->filiais()->sync(array_map('intval', (array) $request->filiais));
        });

        return redirect()->route('beneficios.transporte.linhas.index', ['sub' => $sub])
            ->with('success', 'Linha criada com sucesso.');
    }

    public function edit(Request $request, string $sub, int $id)
    {
        if ($r = $this->requireTela($request, $sub, $this->TELA_ID)) return $r;

        $empresaId = $this->empresaId();

        $linha = TransporteLinha::query()
            ->where('empresa_id', $empresaId)
            ->with('filiais')
            ->findOrFail($id);

        $motoristas = TransporteMotorista::query()->where('empresa_id', $empresaId)->orderBy('nome')->get();
        $veiculos   = TransporteVeiculo::query()->where('empresa_id', $empresaId)->orderBy('placa')->get();
        $filiais    = DB::table('filiais')->where('empresa_id', $empresaId)->orderBy('nome')->get();

        $filiaisSelecionadas = $linha->filiais->pluck('id')->map(fn($x) => (int)$x)->all();

        return view('beneficios.transporte.linhas.edit', compact('sub','linha','motoristas','veiculos','filiais','filiaisSelecionadas'));
    }

    public function update(Request $request, string $sub, int $id)
    {
        if ($r = $this->requireTela($request, $sub, $this->TELA_ID)) return $r;

        $empresaId = $this->empresaId();

        $linha = TransporteLinha::query()->where('empresa_id', $empresaId)->findOrFail($id);

        $v = Validator::make($request->all(), [
            'nome' => 'required|string|max:191',
            'tipo_linha' => 'required|in:publica,fretada',
            'controle_acesso' => 'required|in:cartao,ticket',
            'motorista_id' => 'required|integer',
            'veiculo_id' => 'required|integer',
            'filiais' => 'required|array|min:1',
            'filiais.*' => 'integer',
            'status' => 'nullable|in:ativo,inativo',
            'observacoes' => 'nullable|string',
        ]);

        if ($v->fails()) return back()->withErrors($v)->withInput();

        DB::transaction(function () use ($request, $linha) {
            $linha->update([
                'nome' => $request->nome,
                'tipo_linha' => $request->tipo_linha,
                'controle_acesso' => $request->controle_acesso,
                'motorista_id' => (int) $request->motorista_id,
                'veiculo_id' => (int) $request->veiculo_id,
                'status' => $request->status ?: 'ativo',
                'observacoes' => $request->observacoes,
            ]);

            $linha->filiais()->sync(array_map('intval', (array) $request->filiais));
        });

        return redirect()->route('beneficios.transporte.linhas.index', ['sub' => $sub])
            ->with('success', 'Linha atualizada com sucesso.');
    }

    public function destroy(Request $request, string $sub, int $id)
    {
        if ($r = $this->requireTela($request, $sub, $this->TELA_ID)) return $r;

        $empresaId = $this->empresaId();

        $linha = TransporteLinha::query()->where('empresa_id', $empresaId)->findOrFail($id);
        $linha->delete();

        return redirect()->route('beneficios.transporte.linhas.index', ['sub' => $sub])
            ->with('success', 'Linha removida com sucesso.');
    }

    /**
     * OPERAÇÃO DA LINHA (Paradas + Vínculos) — depende da permissão 21 (você pediu).
     */
    public function operacao(Request $request, string $sub, int $id)
    {
        if ($r = $this->requireTela($request, $sub, $this->TELA_ID)) return $r;

        $empresaId = $this->empresaId();

        $linha = TransporteLinha::query()
            ->where('empresa_id', $empresaId)
            ->with(['motorista','veiculo','filiais'])
            ->findOrFail($id);

        $paradas = TransporteParada::query()
            ->where('empresa_id', $empresaId)
            ->where('linha_id', $linha->id)
            ->orderBy('ordem')
            ->get();

        $vinculos = TransporteVinculo::query()
            ->where('empresa_id', $empresaId)
            ->where('linha_id', $linha->id)
            ->orderBy('id', 'desc')
            ->paginate(20)
            ->appends($request->all());

        // Colaboradores (tabela usuarios): ajuste se seu campo de nome for diferente
        $usuarios = DB::table('usuarios')
            ->where('empresa_id', $empresaId)
            ->orderBy('nome_completo')
            ->get(['id','nome_completo','cpf','matricula']);

        return view('beneficios.transporte.linhas.operacao', compact('sub','linha','paradas','vinculos','usuarios'));
    }

    // ---------- PARADAS ----------
    public function paradaStore(Request $request, string $sub, int $linhaId)
    {
        if ($r = $this->requireTela($request, $sub, $this->TELA_ID)) return $r;

        $empresaId = $this->empresaId();

        $v = Validator::make($request->all(), [
            'nome' => 'required|string|max:191',
            'endereco' => 'nullable|string|max:255',
            'horario' => 'nullable|date_format:H:i',
            'valor' => 'required|numeric|min:0',
            'ordem' => 'nullable|integer|min:0',
        ]);

        if ($v->fails()) return back()->withErrors($v)->withInput();

        TransporteParada::create([
            'empresa_id' => $empresaId,
            'linha_id' => $linhaId,
            'nome' => $request->nome,
            'endereco' => $request->endereco,
            'horario' => $request->horario,
            'valor' => (float) $request->valor,
            'ordem' => (int) ($request->ordem ?? 0),
        ]);

        return back()->with('success', 'Parada criada com sucesso.');
    }

    public function paradaUpdate(Request $request, string $sub, int $linhaId, int $paradaId)
    {
        if ($r = $this->requireTela($request, $sub, $this->TELA_ID)) return $r;

        $empresaId = $this->empresaId();

        $parada = TransporteParada::query()
            ->where('empresa_id', $empresaId)
            ->where('linha_id', $linhaId)
            ->findOrFail($paradaId);

        $v = Validator::make($request->all(), [
            'nome' => 'required|string|max:191',
            'endereco' => 'nullable|string|max:255',
            'horario' => 'nullable|date_format:H:i',
            'valor' => 'required|numeric|min:0',
            'ordem' => 'nullable|integer|min:0',
        ]);

        if ($v->fails()) return back()->withErrors($v)->withInput();

        $parada->update([
            'nome' => $request->nome,
            'endereco' => $request->endereco,
            'horario' => $request->horario,
            'valor' => (float) $request->valor,
            'ordem' => (int) ($request->ordem ?? 0),
        ]);

        return back()->with('success', 'Parada atualizada com sucesso.');
    }

    public function paradaDestroy(Request $request, string $sub, int $linhaId, int $paradaId)
    {
        if ($r = $this->requireTela($request, $sub, $this->TELA_ID)) return $r;

        $empresaId = $this->empresaId();

        $parada = TransporteParada::query()
            ->where('empresa_id', $empresaId)
            ->where('linha_id', $linhaId)
            ->findOrFail($paradaId);

        $parada->delete();

        return back()->with('success', 'Parada removida com sucesso.');
    }

    // ---------- VÍNCULOS ----------
    public function vinculoStore(Request $request, string $sub, int $linhaId)
    {
        if ($r = $this->requireTela($request, $sub, $this->TELA_ID)) return $r;
        $empresaId = $this->empresaId();

        $linha = TransporteLinha::query()->where('empresa_id', $empresaId)->findOrFail($linhaId);

        $v = Validator::make($request->all(), [
            'usuario_id' => 'required|integer',
            'parada_id' => 'nullable|integer',
            'numero_cartao' => 'nullable|string|max:50',
            'numero_vale_ticket' => 'nullable|string|max:50',
            'valor_passagem' => 'required|numeric|min:0',
            'data_inicio' => 'required|date',
            'observacoes' => 'nullable|string',
        ]);

        if ($v->fails()) return back()->withErrors($v)->withInput();

        TransporteVinculo::create([
            'empresa_id' => $empresaId,
            'usuario_id' => (int) $request->usuario_id,
            'linha_id' => $linha->id,
            'parada_id' => $request->parada_id ? (int)$request->parada_id : null,
            'tipo_acesso' => $linha->controle_acesso, // grava histórico
            'numero_cartao' => $request->numero_cartao,
            'numero_vale_ticket' => $request->numero_vale_ticket,
            'valor_passagem' => (float) $request->valor_passagem,
            'data_inicio' => $request->data_inicio,
            'data_fim' => null,
            'status' => 'ativo',
            'observacoes' => $request->observacoes,
        ]);

        return back()->with('success', 'Colaborador vinculado com sucesso.');
    }

    public function vinculoEncerrar(Request $request, string $sub, int $linhaId, int $vinculoId)
    {
        if ($r = $this->requireTela($request, $sub, $this->TELA_ID)) return $r;
        $empresaId = $this->empresaId();

        $v = Validator::make($request->all(), [
            'data_fim' => 'required|date',
        ]);

        if ($v->fails()) return back()->withErrors($v)->withInput();

        $vinculo = TransporteVinculo::query()
            ->where('empresa_id', $empresaId)
            ->where('linha_id', $linhaId)
            ->findOrFail($vinculoId);

        $vinculo->update([
            'data_fim' => $request->data_fim,
            'status' => 'encerrado',
        ]);

        return back()->with('success', 'Vínculo encerrado com sucesso.');
    }
}
