<?php

namespace App\Http\Controllers\Beneficios;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Beneficios\Transporte\TransporteMotorista;

class TransporteMotoristasController extends TransporteBaseController
{
    private int $TELA_ID = 22;

    public function index(Request $request, string $sub)
    {
        if ($r = $this->requireTela($request, $sub, $this->TELA_ID)) return $r;

        $empresaId = $this->empresaId();
        $q = trim((string) $request->get('q', ''));

        $motoristas = TransporteMotorista::query()
            ->where('empresa_id', $empresaId)
            ->when($q, fn($qq) => $qq->where('nome', 'ilike', "%{$q}%"))
            ->orderBy('nome')
            ->paginate(20)
            ->appends($request->all());

        return view('beneficios.transporte.motoristas.index', compact('sub','motoristas','q'));
    }

    public function create(Request $request, string $sub)
    {
        if ($r = $this->requireTela($request, $sub, $this->TELA_ID)) return $r;
        return view('beneficios.transporte.motoristas.create', compact('sub'));
    }

    public function store(Request $request, string $sub)
    {
        if ($r = $this->requireTela($request, $sub, $this->TELA_ID)) return $r;
        $empresaId = $this->empresaId();

        $v = Validator::make($request->all(), [
            'nome' => 'required|string|max:191',
            'cpf' => 'nullable|string|max:20',
            'cnh_numero' => 'nullable|string|max:50',
            'cnh_categoria' => 'nullable|string|max:10',
            'cnh_validade' => 'nullable|date',
            'telefone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:191',
            'status' => 'nullable|in:ativo,inativo',
            'observacoes' => 'nullable|string',
        ]);

        if ($v->fails()) return back()->withErrors($v)->withInput();

        TransporteMotorista::create(array_merge(
            $v->validated(),
            ['empresa_id' => $empresaId, 'status' => $request->status ?: 'ativo']
        ));

        return redirect()->route('beneficios.transporte.motoristas.index', ['sub' => $sub])
            ->with('success', 'Motorista cadastrado com sucesso.');
    }

    public function edit(Request $request, string $sub, int $id)
    {
        if ($r = $this->requireTela($request, $sub, $this->TELA_ID)) return $r;

        $empresaId = $this->empresaId();
        $motorista = TransporteMotorista::query()->where('empresa_id', $empresaId)->findOrFail($id);

        return view('beneficios.transporte.motoristas.edit', compact('sub','motorista'));
    }

    public function update(Request $request, string $sub, int $id)
    {
        if ($r = $this->requireTela($request, $sub, $this->TELA_ID)) return $r;

        $empresaId = $this->empresaId();
        $motorista = TransporteMotorista::query()->where('empresa_id', $empresaId)->findOrFail($id);

        $v = Validator::make($request->all(), [
            'nome' => 'required|string|max:191',
            'cpf' => 'nullable|string|max:20',
            'cnh_numero' => 'nullable|string|max:50',
            'cnh_categoria' => 'nullable|string|max:10',
            'cnh_validade' => 'nullable|date',
            'telefone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:191',
            'status' => 'nullable|in:ativo,inativo',
            'observacoes' => 'nullable|string',
        ]);

        if ($v->fails()) return back()->withErrors($v)->withInput();

        $motorista->update(array_merge(
            $v->validated(),
            ['status' => $request->status ?: $motorista->status]
        ));

        return redirect()->route('beneficios.transporte.motoristas.index', ['sub' => $sub])
            ->with('success', 'Motorista atualizado com sucesso.');
    }

    public function destroy(Request $request, string $sub, int $id)
    {
        if ($r = $this->requireTela($request, $sub, $this->TELA_ID)) return $r;

        $empresaId = $this->empresaId();
        $motorista = TransporteMotorista::query()->where('empresa_id', $empresaId)->findOrFail($id);
        $motorista->delete();

        return redirect()->route('beneficios.transporte.motoristas.index', ['sub' => $sub])
            ->with('success', 'Motorista removido com sucesso.');
    }
}
