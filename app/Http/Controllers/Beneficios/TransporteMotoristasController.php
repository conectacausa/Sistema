<?php

namespace App\Http\Controllers\Beneficios;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class TransporteMotoristasController extends Controller
{
    private const T_MOTORISTAS = 'transporte_motoristas';

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

        $motoristas = DB::table(self::T_MOTORISTAS)
            ->where('empresa_id', $empresaId)
            ->whereNull('deleted_at')
            ->when($q !== '', function ($qq) use ($q) {
                $qq->where(function ($w) use ($q) {
                    $w->where('nome', 'ilike', "%{$q}%")
                      ->orWhere('cpf', 'ilike', "%{$q}%")
                      ->orWhere('cnh_numero', 'ilike', "%{$q}%");
                });
            })
            ->orderBy('id', 'desc')
            ->paginate(20)
            ->withQueryString();

        return view('beneficios.transporte.motoristas.index', compact('sub', 'motoristas', 'q'));
    }

    public function create(Request $request, string $sub)
    {
        $motorista = null;
        return view('beneficios.transporte.motoristas.create', compact('sub', 'motorista'));
    }

    public function store(Request $request, string $sub)
    {
        $empresaId = $this->empresaId();

        $v = Validator::make($request->all(), [
            'nome'        => 'required|string|max:255',
            'cpf'         => 'nullable|string|max:14',
            'telefone'    => 'nullable|string|max:30',
            'cnh_numero'  => 'nullable|string|max:50',
            'cnh_categoria' => 'nullable|string|max:10',
            'cnh_validade'  => 'nullable|date',
            'observacoes' => 'nullable|string',
            'status'      => 'nullable|in:ativo,inativo',
        ]);

        if ($v->fails()) {
            return back()->withErrors($v)->withInput();
        }

        DB::table(self::T_MOTORISTAS)->insert([
            'empresa_id'     => $empresaId,
            'nome'           => $request->string('nome')->toString(),
            'cpf'            => $request->get('cpf'),
            'telefone'       => $request->get('telefone'),
            'cnh_numero'     => $request->get('cnh_numero'),
            'cnh_categoria'  => $request->get('cnh_categoria'),
            'cnh_validade'   => $request->get('cnh_validade'),
            'observacoes'    => $request->get('observacoes'),
            'status'         => $request->get('status', 'ativo'),
            'created_at'     => $this->now(),
            'updated_at'     => $this->now(),
        ]);

        return redirect()->route('beneficios.transporte.motoristas.index', ['sub' => $sub])
            ->with('success', 'Motorista cadastrado com sucesso.');
    }

    public function edit(Request $request, string $sub, int $id)
    {
        $empresaId = $this->empresaId();

        $motorista = DB::table(self::T_MOTORISTAS)
            ->where('empresa_id', $empresaId)
            ->where('id', $id)
            ->whereNull('deleted_at')
            ->first();

        abort_unless($motorista, 404);

        return view('beneficios.transporte.motoristas.edit', compact('sub', 'motorista'));
    }

    public function update(Request $request, string $sub, int $id)
    {
        $empresaId = $this->empresaId();

        $v = Validator::make($request->all(), [
            'nome'        => 'required|string|max:255',
            'cpf'         => 'nullable|string|max:14',
            'telefone'    => 'nullable|string|max:30',
            'cnh_numero'  => 'nullable|string|max:50',
            'cnh_categoria' => 'nullable|string|max:10',
            'cnh_validade'  => 'nullable|date',
            'observacoes' => 'nullable|string',
            'status'      => 'nullable|in:ativo,inativo',
        ]);

        if ($v->fails()) {
            return back()->withErrors($v)->withInput();
        }

        DB::table(self::T_MOTORISTAS)
            ->where('empresa_id', $empresaId)
            ->where('id', $id)
            ->whereNull('deleted_at')
            ->update([
                'nome'           => $request->string('nome')->toString(),
                'cpf'            => $request->get('cpf'),
                'telefone'       => $request->get('telefone'),
                'cnh_numero'     => $request->get('cnh_numero'),
                'cnh_categoria'  => $request->get('cnh_categoria'),
                'cnh_validade'   => $request->get('cnh_validade'),
                'observacoes'    => $request->get('observacoes'),
                'status'         => $request->get('status', 'ativo'),
                'updated_at'     => $this->now(),
            ]);

        return back()->with('success', 'Motorista atualizado.');
    }

    public function destroy(Request $request, string $sub, int $id)
    {
        $empresaId = $this->empresaId();

        DB::table(self::T_MOTORISTAS)
            ->where('empresa_id', $empresaId)
            ->where('id', $id)
            ->update([
                'deleted_at' => $this->now(),
                'updated_at' => $this->now(),
            ]);

        return back()->with('success', 'Motorista removido.');
    }
}
