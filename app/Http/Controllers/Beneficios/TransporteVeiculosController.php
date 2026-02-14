<?php

namespace App\Http\Controllers\Beneficios;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Beneficios\Transporte\TransporteVeiculo;

class TransporteVeiculosController extends TransporteBaseController
{
    private int $TELA_ID = 23;

    public function index(Request $request, string $sub)
    {
        if ($r = $this->requireTela($request, $sub, $this->TELA_ID)) return $r;

        $empresaId = $this->empresaId();
        $q = trim((string) $request->get('q', ''));

        $veiculos = TransporteVeiculo::query()
            ->where('empresa_id', $empresaId)
            ->when($q, function ($qq) use ($q) {
                $qq->where('placa', 'ilike', "%{$q}%")
                   ->orWhere('modelo', 'ilike', "%{$q}%")
                   ->orWhere('marca', 'ilike', "%{$q}%");
            })
            ->orderBy('id', 'desc')
            ->paginate(20)
            ->appends($request->all());

        return view('beneficios.transporte.veiculos.index', compact('sub','veiculos','q'));
    }

    public function create(Request $request, string $sub)
    {
        if ($r = $this->requireTela($request, $sub, $this->TELA_ID)) return $r;
        return view('beneficios.transporte.veiculos.create', compact('sub'));
    }

    public function store(Request $request, string $sub)
    {
        if ($r = $this->requireTela($request, $sub, $this->TELA_ID)) return $r;
        $empresaId = $this->empresaId();

        $v = Validator::make($request->all(), [
            'tipo' => 'required|string|max:30',
            'placa' => 'nullable|string|max:20',
            'renavam' => 'nullable|string|max:30',
            'chassi' => 'nullable|string|max:40',
            'marca' => 'nullable|string|max:100',
            'modelo' => 'nullable|string|max:100',
            'ano' => 'nullable|integer|min:1900|max:2100',
            'capacidade_passageiros' => 'nullable|integer|min:0',
            'inspecao_cada_meses' => 'required|integer|min:1|max:120',
            'status' => 'nullable|in:ativo,inativo,manutencao',
            'observacoes' => 'nullable|string',
        ]);

        if ($v->fails()) return back()->withErrors($v)->withInput();

        TransporteVeiculo::create(array_merge(
            $v->validated(),
            ['empresa_id' => $empresaId, 'status' => $request->status ?: 'ativo']
        ));

        return redirect()->route('beneficios.transporte.veiculos.index', ['sub' => $sub])
            ->with('success', 'Veículo cadastrado com sucesso.');
    }

    public function edit(Request $request, string $sub, int $id)
    {
        if ($r = $this->requireTela($request, $sub, $this->TELA_ID)) return $r;

        $empresaId = $this->empresaId();
        $veiculo = TransporteVeiculo::query()->where('empresa_id', $empresaId)->findOrFail($id);

        return view('beneficios.transporte.veiculos.edit', compact('sub','veiculo'));
    }

    public function update(Request $request, string $sub, int $id)
    {
        if ($r = $this->requireTela($request, $sub, $this->TELA_ID)) return $r;

        $empresaId = $this->empresaId();
        $veiculo = TransporteVeiculo::query()->where('empresa_id', $empresaId)->findOrFail($id);

        $v = Validator::make($request->all(), [
            'tipo' => 'required|string|max:30',
            'placa' => 'nullable|string|max:20',
            'renavam' => 'nullable|string|max:30',
            'chassi' => 'nullable|string|max:40',
            'marca' => 'nullable|string|max:100',
            'modelo' => 'nullable|string|max:100',
            'ano' => 'nullable|integer|min:1900|max:2100',
            'capacidade_passageiros' => 'nullable|integer|min:0',
            'inspecao_cada_meses' => 'required|integer|min:1|max:120',
            'status' => 'nullable|in:ativo,inativo,manutencao',
            'observacoes' => 'nullable|string',
        ]);

        if ($v->fails()) return back()->withErrors($v)->withInput();

        $veiculo->update(array_merge(
            $v->validated(),
            ['status' => $request->status ?: $veiculo->status]
        ));

        return redirect()->route('beneficios.transporte.veiculos.index', ['sub' => $sub])
            ->with('success', 'Veículo atualizado com sucesso.');
    }

    public function destroy(Request $request, string $sub, int $id)
    {
        if ($r = $this->requireTela($request, $sub, $this->TELA_ID)) return $r;

        $empresaId = $this->empresaId();
        $veiculo = TransporteVeiculo::query()->where('empresa_id', $empresaId)->findOrFail($id);
        $veiculo->delete();

        return redirect()->route('beneficios.transporte.veiculos.index', ['sub' => $sub])
            ->with('success', 'Veículo removido com sucesso.');
    }
}
