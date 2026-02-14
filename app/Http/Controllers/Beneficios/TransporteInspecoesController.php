<?php

namespace App\Http\Controllers\Beneficios;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Beneficios\Transporte\TransporteInspecao;
use App\Models\Beneficios\Transporte\TransporteVeiculo;
use App\Models\Beneficios\Transporte\TransporteLinha;

class TransporteInspecoesController extends TransporteBaseController
{
    private int $TELA_ID = 24;

    public function index(Request $request, string $sub)
    {
        if ($r = $this->requireTela($request, $sub, $this->TELA_ID)) return $r;

        $empresaId = $this->empresaId();

        $inspecoes = TransporteInspecao::query()
            ->where('empresa_id', $empresaId)
            ->with(['veiculo:id,placa,modelo', 'linha:id,nome'])
            ->orderBy('id', 'desc')
            ->paginate(20)
            ->appends($request->all());

        return view('beneficios.transporte.inspecoes.index', compact('sub','inspecoes'));
    }

    public function create(Request $request, string $sub)
    {
        if ($r = $this->requireTela($request, $sub, $this->TELA_ID)) return $r;

        $empresaId = $this->empresaId();

        $veiculos = TransporteVeiculo::query()->where('empresa_id', $empresaId)->orderBy('placa')->get();
        $linhas   = TransporteLinha::query()->where('empresa_id', $empresaId)->orderBy('nome')->get();

        return view('beneficios.transporte.inspecoes.create', compact('sub','veiculos','linhas'));
    }

    public function store(Request $request, string $sub)
    {
        if ($r = $this->requireTela($request, $sub, $this->TELA_ID)) return $r;

        $empresaId = $this->empresaId();

        $v = Validator::make($request->all(), [
            'veiculo_id' => 'required|integer',
            'linha_id' => 'nullable|integer',
            'data_inspecao' => 'required|date',
            'status' => 'required|in:aprovado,reprovado,pendente',
            'validade_ate' => 'nullable|date',
            'observacoes' => 'nullable|string',
            'checklist_json' => 'nullable', // virá do form (a view monta)
        ]);

        if ($v->fails()) return back()->withErrors($v)->withInput();

        TransporteInspecao::create([
            'empresa_id' => $empresaId,
            'veiculo_id' => (int) $request->veiculo_id,
            'linha_id' => $request->linha_id ? (int)$request->linha_id : null,
            'data_inspecao' => $request->data_inspecao,
            'status' => $request->status,
            'validade_ate' => $request->validade_ate,
            'observacoes' => $request->observacoes,
            'checklist_json' => is_array($request->checklist_json) ? $request->checklist_json : null,
            'usuario_id' => $this->userId(),
        ]);

        return redirect()->route('beneficios.transporte.inspecoes.index', ['sub' => $sub])
            ->with('success', 'Inspeção registrada com sucesso.');
    }

    public function show(Request $request, string $sub, int $id)
    {
        if ($r = $this->requireTela($request, $sub, $this->TELA_ID)) return $r;

        $empresaId = $this->empresaId();

        $inspecao = TransporteInspecao::query()
            ->where('empresa_id', $empresaId)
            ->with(['veiculo', 'linha'])
            ->findOrFail($id);

        return view('beneficios.transporte.inspecoes.show', compact('sub','inspecao'));
    }
}
