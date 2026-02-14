<?php

namespace App\Http\Controllers\Beneficios;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\Beneficios\Transporte\TransporteLinha;
use App\Models\Beneficios\Transporte\TransporteLinhaCusto;
use App\Models\Beneficios\Transporte\TransporteTicketBloco;
use App\Models\Beneficios\Transporte\TransporteTicketEntrega;

class TransporteOperacoesController extends TransporteBaseController
{
    private int $TELA_ID = 27;

    // Importar custos mensais por linha
    public function importarCustos(Request $request, string $sub)
    {
        if ($r = $this->requireTela($request, $sub, $this->TELA_ID)) return $r;

        $empresaId = $this->empresaId();
        $linhas = TransporteLinha::query()->where('empresa_id', $empresaId)->orderBy('nome')->get();

        if ($request->isMethod('get')) {
            return view('beneficios.transporte.linhas.importar_custos', compact('sub','linhas'));
        }

        $v = Validator::make($request->all(), [
            'linha_id' => 'required|integer',
            'competencia' => 'required|date',
            'valor_total' => 'required|numeric|min:0',
            'observacao' => 'nullable|string',
        ]);

        if ($v->fails()) return back()->withErrors($v)->withInput();

        TransporteLinhaCusto::updateOrCreate(
            ['linha_id' => (int)$request->linha_id, 'competencia' => $request->competencia],
            [
                'empresa_id' => $empresaId,
                'valor_total' => (float) $request->valor_total,
                'origem' => 'manual',
                'observacao' => $request->observacao,
            ]
        );

        return back()->with('success', 'Custo mensal registrado com sucesso.');
    }

    // Tickets - blocos
    public function ticketBlocos(Request $request, string $sub)
    {
        if ($r = $this->requireTela($request, $sub, $this->TELA_ID)) return $r;

        $empresaId = $this->empresaId();
        $q = trim((string)$request->get('q',''));

        $blocos = TransporteTicketBloco::query()
            ->where('empresa_id', $empresaId)
            ->when($q, fn($qq) => $qq->where('codigo_bloco','ilike',"%{$q}%"))
            ->orderByDesc('id')
            ->paginate(20)
            ->appends($request->all());

        return view('beneficios.transporte.tickets.blocos', compact('sub','blocos','q'));
    }

    public function ticketBlocoStore(Request $request, string $sub)
    {
        if ($r = $this->requireTela($request, $sub, $this->TELA_ID)) return $r;

        $empresaId = $this->empresaId();

        $v = Validator::make($request->all(), [
            'codigo_bloco' => 'nullable|string|max:80',
            'quantidade_tickets' => 'required|integer|min:0',
            'viagens_por_ticket' => 'required|integer|min:1',
            'status' => 'nullable|in:disponivel,em_uso,encerrado',
            'observacoes' => 'nullable|string',
        ]);

        if ($v->fails()) return back()->withErrors($v)->withInput();

        TransporteTicketBloco::create([
            'empresa_id' => $empresaId,
            'codigo_bloco' => $request->codigo_bloco,
            'quantidade_tickets' => (int)$request->quantidade_tickets,
            'viagens_por_ticket' => (int)$request->viagens_por_ticket,
            'status' => $request->status ?: 'disponivel',
            'observacoes' => $request->observacoes,
        ]);

        return back()->with('success', 'Bloco criado com sucesso.');
    }

    // Tickets - entregas
    public function ticketEntregas(Request $request, string $sub)
    {
        if ($r = $this->requireTela($request, $sub, $this->TELA_ID)) return $r;

        $empresaId = $this->empresaId();

        $entregas = TransporteTicketEntrega::query()
            ->where('empresa_id', $empresaId)
            ->with('bloco')
            ->orderByDesc('id')
            ->paginate(20)
            ->appends($request->all());

        $blocos = TransporteTicketBloco::query()
            ->where('empresa_id', $empresaId)
            ->where('status', '!=', 'encerrado')
            ->orderByDesc('id')
            ->get();

        $usuarios = DB::table('usuarios')
            ->where('empresa_id', $empresaId)
            ->orderBy('nome_completo')
            ->get(['id','nome_completo','cpf','matricula']);

        return view('beneficios.transporte.tickets.entregas', compact('sub','entregas','blocos','usuarios'));
    }

    public function ticketEntregaStore(Request $request, string $sub)
    {
        if ($r = $this->requireTela($request, $sub, $this->TELA_ID)) return $r;

        $empresaId = $this->empresaId();

        $v = Validator::make($request->all(), [
            'usuario_id' => 'required|integer',
            'bloco_id' => 'required|integer',
            'data_entrega' => 'required|date',
            'quantidade_entregue' => 'required|integer|min:1',
            'observacao' => 'nullable|string',
        ]);

        if ($v->fails()) return back()->withErrors($v)->withInput();

        TransporteTicketEntrega::create([
            'empresa_id' => $empresaId,
            'usuario_id' => (int)$request->usuario_id,
            'bloco_id' => (int)$request->bloco_id,
            'data_entrega' => $request->data_entrega,
            'quantidade_entregue' => (int)$request->quantidade_entregue,
            'observacao' => $request->observacao,
        ]);

        return back()->with('success', 'Entrega registrada com sucesso.');
    }
}
