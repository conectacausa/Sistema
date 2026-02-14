<?php

namespace App\Http\Controllers\Beneficios;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class TransporteTicketsController extends Controller
{
    private const T_BLOCOS   = 'transporte_tickets_blocos';
    private const T_ENTREGAS = 'transporte_tickets_entregas';
    private const T_COLABS   = 'colaboradores';

    private function empresaId(): int
    {
        return (int) (auth()->user()->empresa_id ?? 0);
    }

    private function now()
    {
        return now();
    }

    public function blocos(Request $request, string $sub)
    {
        $empresaId = $this->empresaId();

        $blocos = DB::table(self::T_BLOCOS)
            ->where('empresa_id', $empresaId)
            ->whereNull('deleted_at')
            ->orderBy('id', 'desc')
            ->paginate(20)
            ->withQueryString();

        return view('beneficios.transporte.tickets.blocos', compact('sub', 'blocos'));
    }

    public function blocosStore(Request $request, string $sub)
    {
        $empresaId = $this->empresaId();

        $v = Validator::make($request->all(), [
            'codigo'        => 'required|string|max:50',
            'qtd_viagens'    => 'required|integer|min:1',
            'observacoes'    => 'nullable|string|max:255',
        ]);

        if ($v->fails()) {
            return back()->withErrors($v)->withInput();
        }

        DB::table(self::T_BLOCOS)->insert([
            'empresa_id'   => $empresaId,
            'codigo'       => $request->string('codigo')->toString(),
            'qtd_viagens'  => (int) $request->get('qtd_viagens'),
            'observacoes'  => $request->get('observacoes'),
            'created_at'   => $this->now(),
            'updated_at'   => $this->now(),
        ]);

        return back()->with('success', 'Bloco cadastrado.');
    }

    public function entregas(Request $request, string $sub)
    {
        $empresaId = $this->empresaId();

        $entregas = DB::table(self::T_ENTREGAS . ' as e')
            ->leftJoin(self::T_COLABS . ' as c', 'c.id', '=', 'e.colaborador_id')
            ->select('e.*', 'c.nome_completo', 'c.matricula')
            ->where('e.empresa_id', $empresaId)
            ->whereNull('e.deleted_at')
            ->orderBy('e.id', 'desc')
            ->paginate(20)
            ->withQueryString();

        $colaboradores = DB::table(self::T_COLABS)
            ->where('empresa_id', $empresaId)
            ->whereNull('deleted_at')
            ->orderBy('nome_completo')
            ->limit(5000)
            ->get();

        return view('beneficios.transporte.tickets.entregas', compact('sub', 'entregas', 'colaboradores'));
    }

    public function entregasStore(Request $request, string $sub)
    {
        $empresaId = $this->empresaId();

        $v = Validator::make($request->all(), [
            'colaborador_id' => 'required|integer|min:1',
            'bloco_codigo'   => 'required|string|max:50',
            'qtd_entregue'   => 'required|integer|min:1',
            'data_entrega'   => 'required|date',
            'observacoes'    => 'nullable|string|max:255',
        ]);

        if ($v->fails()) {
            return back()->withErrors($v)->withInput();
        }

        DB::table(self::T_ENTREGAS)->insert([
            'empresa_id'     => $empresaId,
            'colaborador_id' => (int) $request->get('colaborador_id'),
            'bloco_codigo'   => $request->string('bloco_codigo')->toString(),
            'qtd_entregue'   => (int) $request->get('qtd_entregue'),
            'data_entrega'   => $request->get('data_entrega'),
            'observacoes'    => $request->get('observacoes'),
            'created_at'     => $this->now(),
            'updated_at'     => $this->now(),
        ]);

        return back()->with('success', 'Entrega registrada.');
    }
}
