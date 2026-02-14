<?php

namespace App\Http\Controllers\Beneficios;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class TransporteVeiculosController extends Controller
{
    private const T_VEICULOS = 'transporte_veiculos';

    private function empresaId(): int
    {
        return (int) (auth()->user()->empresa_id ?? 0);
    }

    private function now()
    {
        return now();
    }

    public function index(Request $request, string $sub)
    {
        $empresaId = $this->empresaId();
        $q = trim((string) $request->get('q', ''));

        $veiculos = DB::table(self::T_VEICULOS)
            ->where('empresa_id', $empresaId)
            ->whereNull('deleted_at')
            ->when($q !== '', function ($qq) use ($q) {
                $qq->where(function ($w) use ($q) {
                    $w->where('placa', 'ilike', "%{$q}%")
                      ->orWhere('modelo', 'ilike', "%{$q}%")
                      ->orWhere('descricao', 'ilike', "%{$q}%");
                });
            })
            ->orderBy('id', 'desc')
            ->paginate(20)
            ->withQueryString();

        return view('beneficios.transporte.veiculos.index', compact('sub', 'veiculos', 'q'));
    }

    public function create(Request $request, string $sub)
    {
        $veiculo = null;
        return view('beneficios.transporte.veiculos.create', compact('sub', 'veiculo'));
    }

    public function store(Request $request, string $sub)
    {
        $empresaId = $this->empresaId();

        $v = Validator::make($request->all(), [
            'placa'              => 'nullable|string|max:20',
            'modelo'             => 'required|string|max:255',
            'descricao'          => 'nullable|string|max:255',
            'capacidade'         => 'nullable|integer|min:0',
            'meses_inspecao'     => 'required|integer|min:1|max:60',
            'status'             => 'nullable|in:ativo,inativo',
            'observacoes'        => 'nullable|string',
        ]);

        if ($v->fails()) {
            return back()->withErrors($v)->withInput();
        }

        DB::table(self::T_VEICULOS)->insert([
            'empresa_id'      => $empresaId,
            'placa'           => $request->get('placa'),
            'modelo'          => $request->string('modelo')->toString(),
            'descricao'       => $request->get('descricao'),
            'capacidade'      => $request->get('capacidade'),
            'meses_inspecao'  => (int) $request->get('meses_inspecao'),
            'status'          => $request->get('status', 'ativo'),
            'observacoes'     => $request->get('observacoes'),
            'created_at'      => $this->now(),
            'updated_at'      => $this->now(),
        ]);

        return redirect()->route('beneficios.transporte.veiculos.index', ['sub' => $sub])
            ->with('success', 'Veículo cadastrado com sucesso.');
    }

    public function edit(Request $request, string $sub, int $id)
    {
        $empresaId = $this->empresaId();

        $veiculo = DB::table(self::T_VEICULOS)
            ->where('empresa_id', $empresaId)
            ->where('id', $id)
            ->whereNull('deleted_at')
            ->first();

        abort_unless($veiculo, 404);

        return view('beneficios.transporte.veiculos.edit', compact('sub', 'veiculo'));
    }

    public function update(Request $request, string $sub, int $id)
    {
        $empresaId = $this->empresaId();

        $v = Validator::make($request->all(), [
            'placa'              => 'nullable|string|max:20',
            'modelo'             => 'required|string|max:255',
            'descricao'          => 'nullable|string|max:255',
            'capacidade'         => 'nullable|integer|min:0',
            'meses_inspecao'     => 'required|integer|min:1|max:60',
            'status'             => 'nullable|in:ativo,inativo',
            'observacoes'        => 'nullable|string',
        ]);

        if ($v->fails()) {
            return back()->withErrors($v)->withInput();
        }

        DB::table(self::T_VEICULOS)
            ->where('empresa_id', $empresaId)
            ->where('id', $id)
            ->whereNull('deleted_at')
            ->update([
                'placa'           => $request->get('placa'),
                'modelo'          => $request->string('modelo')->toString(),
                'descricao'       => $request->get('descricao'),
                'capacidade'      => $request->get('capacidade'),
                'meses_inspecao'  => (int) $request->get('meses_inspecao'),
                'status'          => $request->get('status', 'ativo'),
                'observacoes'     => $request->get('observacoes'),
                'updated_at'      => $this->now(),
            ]);

        return back()->with('success', 'Veículo atualizado.');
    }

    public function destroy(Request $request, string $sub, int $id)
    {
        $empresaId = $this->empresaId();

        DB::table(self::T_VEICULOS)
            ->where('empresa_id', $empresaId)
            ->where('id', $id)
            ->update([
                'deleted_at' => $this->now(),
                'updated_at' => $this->now(),
            ]);

        return back()->with('success', 'Veículo removido.');
    }
}
