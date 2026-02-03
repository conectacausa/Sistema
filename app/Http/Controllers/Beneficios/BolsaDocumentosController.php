<?php

namespace App\Http\Controllers\Beneficios;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class BolsaDocumentosController extends Controller
{
    public function index(Request $request, string $sub, int $processo_id)
    {
        $empresaId = (int) (auth()->user()->empresa_id ?? 0);

        $processo = $this->getProcesso($empresaId, $processo_id);
        if (!$processo) {
            return redirect()->route('beneficios.bolsa.index', ['sub' => $sub])
                ->with('error', 'Processo não encontrado.');
        }

        // Documentos pendentes (status=0)
        $docsPendentes = DB::table('bolsa_estudos_documentos as d')
            ->where('d.empresa_id', $empresaId)
            ->where('d.processo_id', $processo_id)
            ->whereNull('d.deleted_at')
            ->where('d.status', 0)
            ->leftJoin('bolsa_estudos_solicitacoes as s', 's.id', '=', 'd.solicitacao_id')
            ->leftJoin('colaboradores as c', 'c.id', '=', 's.colaborador_id')
            ->leftJoin('bolsa_estudos_solicitacao_competencias as comp', 'comp.id', '=', 'd.competencia_id')
            ->select([
                'd.id',
                'd.tipo',
                'd.titulo',
                'd.arquivo_path',
                'd.created_at',
                'd.solicitacao_id',
                'd.competencia_id',
                'c.nome as colaborador_nome',
                DB::raw("comp.competencia as competencia"),
            ])
            ->orderByDesc('d.id')
            ->paginate(15, ['*'], 'docs_page');

        // Competências prontas para pagamento (status=2)
        $competenciasPagamento = DB::table('bolsa_estudos_solicitacao_competencias as comp')
            ->where('comp.empresa_id', $empresaId)
            ->whereNull('comp.deleted_at')
            ->where('comp.status', 2)
            ->leftJoin('bolsa_estudos_solicitacoes as s', 's.id', '=', 'comp.solicitacao_id')
            ->where('s.processo_id', $processo_id)
            ->leftJoin('colaboradores as c', 'c.id', '=', 's.colaborador_id')
            ->select([
                'comp.id',
                'comp.competencia',
                'comp.vencimento',
                'comp.valor_previsto',
                'comp.valor_comprovado',
                's.id as solicitacao_id',
                'c.nome as colaborador_nome',
            ])
            ->orderBy('comp.competencia', 'asc')
            ->paginate(15, ['*'], 'pay_page');

        return view('beneficios.bolsa.documentos.index', [
            'sub'                  => $sub,
            'processo'             => $processo,
            'docsPendentes'        => $docsPendentes,
            'competenciasPagamento'=> $competenciasPagamento,
        ]);
    }

    public function show(Request $request, string $sub, int $processo_id, int $doc_id)
    {
        $empresaId = (int) (auth()->user()->empresa_id ?? 0);

        $processo = $this->getProcesso($empresaId, $processo_id);
        if (!$processo) {
            return redirect()->route('beneficios.bolsa.index', ['sub' => $sub])
                ->with('error', 'Processo não encontrado.');
        }

        $doc = DB::table('bolsa_estudos_documentos as d')
            ->where('d.empresa_id', $empresaId)
            ->where('d.processo_id', $processo_id)
            ->where('d.id', $doc_id)
            ->whereNull('d.deleted_at')
            ->leftJoin('bolsa_estudos_solicitacoes as s', 's.id', '=', 'd.solicitacao_id')
            ->leftJoin('colaboradores as c', 'c.id', '=', 's.colaborador_id')
            ->leftJoin('bolsa_estudos_solicitacao_competencias as comp', 'comp.id', '=', 'd.competencia_id')
            ->select([
                'd.*',
                'c.nome as colaborador_nome',
                DB::raw("comp.competencia as competencia"),
            ])
            ->first();

        if (!$doc) {
            return redirect()->route('beneficios.bolsa.documentos.index', ['sub' => $sub, 'processo_id' => $processo_id])
                ->with('error', 'Documento não encontrado.');
        }

        return view('beneficios.bolsa.documentos.show', [
            'sub'      => $sub,
            'processo' => $processo,
            'doc'      => $doc,
        ]);
    }

    public function aprovar(Request $request, string $sub, int $processo_id, int $doc_id)
    {
        $empresaId = (int) (auth()->user()->empresa_id ?? 0);
        $userId    = (int) (auth()->user()->id ?? 0);

        $doc = DB::table('bolsa_estudos_documentos')
            ->where('empresa_id', $empresaId)
            ->where('processo_id', $processo_id)
            ->where('id', $doc_id)
            ->whereNull('deleted_at')
            ->first();

        if (!$doc) {
            return back()->with('error', 'Documento não encontrado.');
        }

        DB::beginTransaction();
        try {
            DB::table('bolsa_estudos_documentos')
                ->where('empresa_id', $empresaId)
                ->where('processo_id', $processo_id)
                ->where('id', $doc_id)
                ->whereNull('deleted_at')
                ->update([
                    'status'      => 2,
                    'justificativa'=> null,
                    'aprovador_id'=> $userId ?: null,
                    'aprovacao_at'=> now(),
                    'aprovacao_ip'=> (string)$request->ip(),
                    'updated_at'  => now(),
                ]);

            // Se for comprovante mensal (tipo=1) e tiver competencia_id → competência vai para "aprovado / pagamento" (status=2)
            if ((int)($doc->tipo ?? 2) === 1 && !empty($doc->competencia_id) && Schema::hasTable('bolsa_estudos_solicitacao_competencias')) {
                DB::table('bolsa_estudos_solicitacao_competencias')
                    ->where('empresa_id', $empresaId)
                    ->where('id', (int)$doc->competencia_id)
                    ->whereNull('deleted_at')
                    ->update([
                        'status'      => 2,
                        'aprovador_id'=> $userId ?: null,
                        'aprovacao_at'=> now(),
                        'aprovacao_ip'=> (string)$request->ip(),
                        'updated_at'  => now(),
                    ]);
            }

            DB::commit();
            return back()->with('success', 'Documento aprovado com sucesso.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Não foi possível aprovar o documento.');
        }
    }

    public function reprovar(Request $request, string $sub, int $processo_id, int $doc_id)
    {
        $empresaId = (int) (auth()->user()->empresa_id ?? 0);
        $userId    = (int) (auth()->user()->id ?? 0);

        $data = $request->validate([
            'justificativa' => ['required', 'string', 'min:5'],
        ]);

        $doc = DB::table('bolsa_estudos_documentos')
            ->where('empresa_id', $empresaId)
            ->where('processo_id', $processo_id)
            ->where('id', $doc_id)
            ->whereNull('deleted_at')
            ->first();

        if (!$doc) {
            return back()->with('error', 'Documento não encontrado.');
        }

        DB::beginTransaction();
        try {
            DB::table('bolsa_estudos_documentos')
                ->where('empresa_id', $empresaId)
                ->where('processo_id', $processo_id)
                ->where('id', $doc_id)
                ->whereNull('deleted_at')
                ->update([
                    'status'       => 1,
                    'justificativa'=> $data['justificativa'],
                    'aprovador_id' => $userId ?: null,
                    'aprovacao_at' => now(),
                    'aprovacao_ip' => (string)$request->ip(),
                    'updated_at'   => now(),
                ]);

            // Se for comprovante mensal e tiver competência → competência vira "recibo reprovado" (status=4)
            if ((int)($doc->tipo ?? 2) === 1 && !empty($doc->competencia_id) && Schema::hasTable('bolsa_estudos_solicitacao_competencias')) {
                DB::table('bolsa_estudos_solicitacao_competencias')
                    ->where('empresa_id', $empresaId)
                    ->where('id', (int)$doc->competencia_id)
                    ->whereNull('deleted_at')
                    ->update([
                        'status'     => 4,
                        'updated_at' => now(),
                    ]);
            }

            DB::commit();
            return back()->with('success', 'Documento reprovado com sucesso.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Não foi possível reprovar o documento.');
        }
    }

    public function pagar(Request $request, string $sub, int $processo_id, int $competencia_id)
    {
        $empresaId = (int) (auth()->user()->empresa_id ?? 0);

        if (!Schema::hasTable('bolsa_estudos_solicitacao_competencias')) {
            return back()->with('error', 'Tabela de competências não existe.');
        }

        // garante que a competência pertence ao processo
        $row = DB::table('bolsa_estudos_solicitacao_competencias as comp')
            ->where('comp.empresa_id', $empresaId)
            ->where('comp.id', $competencia_id)
            ->whereNull('comp.deleted_at')
            ->leftJoin('bolsa_estudos_solicitacoes as s', 's.id', '=', 'comp.solicitacao_id')
            ->select(['comp.id', 'comp.status', 's.processo_id'])
            ->first();

        if (!$row || (int)$row->processo_id !== (int)$processo_id) {
            return back()->with('error', 'Competência inválida.');
        }

        // só permite marcar pago se estiver pronto para pagamento (status=2)
        if ((int)$row->status !== 2) {
            return back()->with('error', 'Esta competência não está pronta para pagamento.');
        }

        DB::table('bolsa_estudos_solicitacao_competencias')
            ->where('empresa_id', $empresaId)
            ->where('id', $competencia_id)
            ->whereNull('deleted_at')
            ->update([
                'status'     => 3,
                'updated_at' => now(),
            ]);

        return back()->with('success', 'Competência marcada como paga.');
    }

    private function getProcesso(int $empresaId, int $processoId)
    {
        return DB::table('bolsa_estudos_processos')
            ->where('empresa_id', $empresaId)
            ->where('id', $processoId)
            ->whereNull('deleted_at')
            ->first();
    }
}
