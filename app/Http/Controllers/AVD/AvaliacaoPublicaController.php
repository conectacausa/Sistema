<?php

namespace App\Http\Controllers\AVD;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AvaliacaoPublicaController extends Controller
{
    private function nowDb(): string
    {
        return now()->format('Y-m-d H:i:s');
    }

    public function show(Request $request, $sub, $token)
    {
        $token = (string) $token;

        $avaliacao = DB::table('avd_avaliacoes as a')
            ->join('avd_ciclos as c', 'c.id', '=', 'a.ciclo_id')
            ->join('avd_ciclo_participantes as p', 'p.id', '=', 'a.participante_id')
            ->where('a.token', $token)
            ->whereNull('c.deleted_at')
            ->whereNull('p.deleted_at')
            ->select(
                'a.*',
                'c.status as ciclo_status',
                'c.titulo as ciclo_titulo',
                'c.tipo as ciclo_tipo'
            )
            ->first();

        abort_if(!$avaliacao, 404);

        if ($avaliacao->status === 'respondido') {
            return view('avd.avaliacao.bloqueado', ['motivo' => 'Esta avaliação já foi respondida.']);
        }

        if ($avaliacao->ciclo_status !== 'iniciada') {
            return view('avd.avaliacao.bloqueado', ['motivo' => 'Ciclo não está aberto para respostas.']);
        }

        $pilares = DB::table('avd_pilares')
            ->where('empresa_id', $avaliacao->empresa_id)
            ->where('ciclo_id', $avaliacao->ciclo_id)
            ->whereNull('deleted_at')
            ->orderBy('ordem')
            ->orderBy('id')
            ->get();

        $perguntas = DB::table('avd_perguntas')
            ->where('empresa_id', $avaliacao->empresa_id)
            ->where('ciclo_id', $avaliacao->ciclo_id)
            ->whereNull('deleted_at')
            ->orderBy('pilar_id')
            ->orderBy('ordem')
            ->orderBy('id')
            ->get();

        return view('avd.avaliacao.public', [
            'sub'       => $sub,
            'token'     => $token,
            'avaliacao' => $avaliacao,
            'pilares'   => $pilares,
            'perguntas' => $perguntas,
        ]);
    }

    public function submit(Request $request, $sub, $token)
    {
        $token = (string) $token;

        $avaliacao = DB::table('avd_avaliacoes as a')
            ->join('avd_ciclos as c', 'c.id', '=', 'a.ciclo_id')
            ->where('a.token', $token)
            ->whereNull('c.deleted_at')
            ->select('a.*', 'c.status as ciclo_status')
            ->first();

        abort_if(!$avaliacao, 404);

        if ($avaliacao->status === 'respondido') {
            return back()->with('warning', 'Esta avaliação já foi respondida.');
        }

        if ($avaliacao->ciclo_status !== 'iniciada') {
            return back()->with('warning', 'Ciclo não está aberto.');
        }

        $respostas = (array) $request->get('respostas', []);

        Validator::make(['respostas' => $respostas], [
            'respostas' => ['required', 'array', 'min:1'],
        ])->validate();

        DB::beginTransaction();
        try {
            foreach ($respostas as $perguntaId => $r) {
                $valor = isset($r['valor']) ? (float) $r['valor'] : null;
                $just  = isset($r['justificativa']) ? trim((string) $r['justificativa']) : null;
                $com   = isset($r['comentario']) ? trim((string) $r['comentario']) : null;

                DB::table('avd_respostas')->updateOrInsert([
                    'empresa_id'      => $avaliacao->empresa_id,
                    'ciclo_id'        => $avaliacao->ciclo_id,
                    'avaliacao_id'    => $avaliacao->id,
                    'participante_id' => $avaliacao->participante_id,
                    'pergunta_id'     => (int) $perguntaId,
                ], [
                    'resposta_valor' => $valor,
                    'justificativa'  => $just ?: null,
                    'comentario'     => $com ?: null,
                    'created_at'     => $this->nowDb(),
                    'updated_at'     => $this->nowDb(),
                ]);
            }

            DB::table('avd_avaliacoes')
                ->where('id', $avaliacao->id)
                ->update([
                    'status'        => 'respondido',
                    'respondido_em' => $this->nowDb(),
                    'updated_at'    => $this->nowDb(),
                ]);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        return view('avd.avaliacao.sucesso');
    }
}
