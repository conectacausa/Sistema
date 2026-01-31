<?php

namespace App\Http\Controllers\Recrutamento;

use App\Http\Controllers\Controller;
use App\Models\Recrutamento\RecrutamentoFluxoAprovacao;
use App\Models\Recrutamento\RecrutamentoFluxoAprovacaoEtapa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FluxoAprovacaoController extends Controller
{
    public function index()
    {
        $empresaId = app('tenant')->id;

        $fluxos = RecrutamentoFluxoAprovacao::where('empresa_id', $empresaId)
            ->orderBy('id', 'desc')
            ->get();

        return view('recrutamento.fluxo.index', compact('fluxos'));
    }

    public function create()
    {
        return view('recrutamento.fluxo.create');
    }

    public function store(Request $request)
    {
        $empresaId = app('tenant')->id;
        $usuarioId = auth()->id();

        $request->validate([
            'nome' => 'required|string|max:150',
            'etapas' => 'required|array|min:1',
            'etapas.*.ordem' => 'required|integer',
            'etapas.*.tipo' => 'required|in:aprovacao,ciencia',
            'etapas.*.aprovador_usuario_id' => 'required|integer',
            'etapas.*.prazo_horas' => 'nullable|integer|min:0',
        ]);

        DB::transaction(function () use ($request, $empresaId, $usuarioId) {

            $fluxo = RecrutamentoFluxoAprovacao::create([
                'empresa_id' => $empresaId,
                'nome' => $request->nome,
                'ativo' => true,
                'created_by_usuario_id' => $usuarioId,
            ]);

            foreach ($request->etapas as $etapa) {
                RecrutamentoFluxoAprovacaoEtapa::create([
                    'empresa_id' => $empresaId,
                    'fluxo_id' => $fluxo->id,
                    'ordem' => $etapa['ordem'],
                    'tipo' => $etapa['tipo'],
                    'aprovador_usuario_id' => $etapa['aprovador_usuario_id'],
                    'prazo_horas' => $etapa['prazo_horas'] ?? null,
                    'ativo' => true,
                ]);
            }
        });

        return redirect('/recrutamento/fluxo');
    }

    public function edit($id)
    {
        $empresaId = app('tenant')->id;

        $fluxo = RecrutamentoFluxoAprovacao::where('empresa_id', $empresaId)
            ->with('etapas')
            ->findOrFail($id);

        return view('recrutamento.fluxo.edit', compact('fluxo'));
    }

    public function update(Request $request, $id)
    {
        $empresaId = app('tenant')->id;

        $fluxo = RecrutamentoFluxoAprovacao::where('empresa_id', $empresaId)
            ->findOrFail($id);

        DB::transaction(function () use ($request, $fluxo, $empresaId) {

            $fluxo->update([
                'nome' => $request->nome,
                'ativo' => true,
            ]);

            RecrutamentoFluxoAprovacaoEtapa::where('fluxo_id', $fluxo->id)->delete();

            foreach ($request->etapas as $etapa) {
                RecrutamentoFluxoAprovacaoEtapa::create([
                    'empresa_id' => $empresaId,
                    'fluxo_id' => $fluxo->id,
                    'ordem' => $etapa['ordem'],
                    'tipo' => $etapa['tipo'],
                    'aprovador_usuario_id' => $etapa['aprovador_usuario_id'],
                    'prazo_horas' => $etapa['prazo_horas'] ?? null,
                    'ativo' => true,
                ]);
            }
        });

        return redirect('/recrutamento/fluxo');
    }

    public function destroy($id)
    {
        $empresaId = app('tenant')->id;

        RecrutamentoFluxoAprovacao::where('empresa_id', $empresaId)
            ->where('id', $id)
            ->delete();

        return redirect('/recrutamento/fluxo');
    }
}
