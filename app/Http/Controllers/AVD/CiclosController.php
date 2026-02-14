<?php

namespace App\Http\Controllers\Avd;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CiclosController extends Controller
{
    private function empresaId(): int
    {
        return (int) (auth()->user()->empresa_id ?? 0);
    }

    private function nowDb(): string
    {
        return now()->format('Y-m-d H:i:s');
    }

    private function cicloOrFail(int $empresaId, int $id)
    {
        $ciclo = DB::table('avd_ciclos')
            ->where('empresa_id', $empresaId)
            ->whereNull('deleted_at')
            ->where('id', $id)
            ->first();

        abort_if(!$ciclo, 404);
        return $ciclo;
    }

    /*
    |--------------------------------------------------------------------------
    | Index
    |--------------------------------------------------------------------------
    */
    public function index(Request $request, string $sub)
    {
        return view('avd.desempenho.index', [
            'sub' => $sub,
        ]);
    }

    public function grid(Request $request, string $sub)
    {
        $empresaId = $this->empresaId();

        $q      = trim((string) $request->get('q', ''));
        $status = trim((string) $request->get('status', ''));
        $filial = (int) $request->get('filial_id', 0);

        $sql = DB::table('avd_ciclos as c')
            ->where('c.empresa_id', $empresaId)
            ->whereNull('c.deleted_at');

        if ($q !== '') {
            $sql->where('c.titulo', 'ilike', "%{$q}%");
        }
        if ($status !== '') {
            $sql->where('c.status', $status);
        }
        if ($filial > 0) {
            $sql->whereExists(function ($q2) use ($filial) {
                $q2->select(DB::raw(1))
                    ->from('avd_ciclo_unidades as u')
                    ->whereColumn('u.ciclo_id', 'c.id')
                    ->where('u.filial_id', $filial);
            });
        }

        $rows = $sql->orderByDesc('c.id')->limit(200)->get();

        // métricas simples por ciclo (participantes/respondentes)
        $ids = $rows->pluck('id')->all();
        $participantes = [];
        $respondentes  = [];

        if (!empty($ids)) {
            $participantes = DB::table('avd_ciclo_participantes')
                ->select('ciclo_id', DB::raw('COUNT(*) as qt'))
                ->whereIn('ciclo_id', $ids)
                ->whereNull('deleted_at')
                ->groupBy('ciclo_id')
                ->pluck('qt', 'ciclo_id')
                ->toArray();

            $respondentes = DB::table('avd_avaliacoes')
                ->select('ciclo_id', DB::raw("COUNT(*) FILTER (WHERE status='respondido') as qt"))
                ->whereIn('ciclo_id', $ids)
                ->groupBy('ciclo_id')
                ->pluck('qt', 'ciclo_id')
                ->toArray();
        }

        return view('avd.desempenho.partials.table', [
            'sub'          => $sub,
            'rows'         => $rows,
            'participantes'=> $participantes,
            'respondentes' => $respondentes,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Create / Store
    |--------------------------------------------------------------------------
    */
    public function create(Request $request, string $sub)
    {
        return view('avd.desempenho.edit', [
            'sub'   => $sub,
            'id'    => 0,
            'ciclo' => null,
        ]);
    }

    public function store(Request $request, string $sub)
    {
        $empresaId = $this->empresaId();

        $v = Validator::make($request->all(), [
            'titulo'                 => ['required', 'string', 'max:180'],
            'inicio_em'              => ['nullable', 'date'],
            'fim_em'                 => ['nullable', 'date'],
            'tipo'                   => ['required', 'in:180,360'],
            'divergencia_tipo'       => ['required', 'in:percent,pontos'],
            'divergencia_valor'      => ['nullable', 'numeric', 'min:0'],
            'permitir_inicio_manual' => ['nullable', 'boolean'],
            'permitir_reabrir'       => ['nullable', 'boolean'],
            'peso_auto'              => ['required', 'numeric', 'min:0', 'max:100'],
            'peso_gestor'            => ['required', 'numeric', 'min:0', 'max:100'],
            'peso_pares'             => ['required', 'numeric', 'min:0', 'max:100'],
        ]);

        $v->validate();

        // regra: em 180° pares = 0
        $tipo = $request->get('tipo');
        $pesoPares = (float) $request->get('peso_pares');
        if ($tipo === '180') {
            $pesoPares = 0;
        }

        $id = DB::table('avd_ciclos')->insertGetId([
            'empresa_id' => $empresaId,
            'titulo'     => trim((string) $request->get('titulo')),
            'inicio_em'  => $request->get('inicio_em') ?: null,
            'fim_em'     => $request->get('fim_em') ?: null,
            'tipo'       => $tipo,
            'divergencia_tipo'  => $request->get('divergencia_tipo', 'percent'),
            'divergencia_valor' => (float) ($request->get('divergencia_valor', 0) ?: 0),
            'permitir_inicio_manual' => (bool) $request->boolean('permitir_inicio_manual', true),
            'permitir_reabrir'       => (bool) $request->boolean('permitir_reabrir', false),
            'status'     => 'aguardando',
            'peso_auto'  => (float) $request->get('peso_auto'),
            'peso_gestor'=> (float) $request->get('peso_gestor'),
            'peso_pares' => (float) $pesoPares,
            'msg_auto'   => $request->get('msg_auto'),
            'msg_gestor' => $request->get('msg_gestor'),
            'msg_pares'  => $request->get('msg_pares'),
            'msg_consenso' => $request->get('msg_consenso'),
            'msg_lembrete' => $request->get('msg_lembrete'),
            'lembrete_cada_dias' => $request->get('lembrete_cada_dias') ?: null,
            'parar_lembrete_apos_responder' => (bool) $request->boolean('parar_lembrete_apos_responder', true),
            'created_at' => $this->nowDb(),
            'updated_at' => $this->nowDb(),
        ]);

        return redirect()->route('avd.ciclos.edit', ['sub' => $sub, 'id' => $id])
            ->with('success', 'Ciclo criado com sucesso.');
    }

    /*
    |--------------------------------------------------------------------------
    | Edit / Update
    |--------------------------------------------------------------------------
    */
    public function edit(Request $request, string $sub, int $id)
    {
        $empresaId = $this->empresaId();
        $ciclo = $this->cicloOrFail($empresaId, $id);

        // listas básicas para as tabs
        $unidades = DB::table('avd_ciclo_unidades as cu')
            ->join('filiais as f', 'f.id', '=', 'cu.filial_id')
            ->where('cu.empresa_id', $empresaId)
            ->where('cu.ciclo_id', $id)
            ->select('cu.id', 'cu.filial_id', 'f.nome_fantasia', 'f.cnpj')
            ->orderBy('f.nome_fantasia')
            ->get();

        $participantes = DB::table('avd_ciclo_participantes as p')
            ->leftJoin('colaboradores as c', 'c.id', '=', 'p.colaborador_id')
            ->leftJoin('filiais as f', 'f.id', '=', 'p.filial_id')
            ->where('p.empresa_id', $empresaId)
            ->where('p.ciclo_id', $id)
            ->whereNull('p.deleted_at')
            ->select(
                'p.*',
                'c.nome as colaborador_nome',
                'c.cpf as colaborador_cpf',
                'f.nome_fantasia as filial_nome'
            )
            ->orderBy('c.nome')
            ->get();

        $pilares = DB::table('avd_pilares')
            ->where('empresa_id', $empresaId)
            ->where('ciclo_id', $id)
            ->whereNull('deleted_at')
            ->orderBy('ordem')
            ->orderBy('id')
            ->get();

        $perguntas = DB::table('avd_perguntas as pg')
            ->leftJoin('avd_pilares as pl', 'pl.id', '=', 'pg.pilar_id')
            ->where('pg.empresa_id', $empresaId)
            ->where('pg.ciclo_id', $id)
            ->whereNull('pg.deleted_at')
            ->select('pg.*', 'pl.nome as pilar_nome')
            ->orderBy('pg.pilar_id')
            ->orderBy('pg.ordem')
            ->orderBy('pg.id')
            ->get();

        return view('avd.desempenho.edit', [
            'sub'          => $sub,
            'id'           => $id,
            'ciclo'        => $ciclo,
            'unidades'     => $unidades,
            'participantes'=> $participantes,
            'pilares'      => $pilares,
            'perguntas'    => $perguntas,
        ]);
    }

    public function update(Request $request, string $sub, int $id)
    {
        $empresaId = $this->empresaId();
        $ciclo = $this->cicloOrFail($empresaId, $id);

        $v = Validator::make($request->all(), [
            'titulo'                 => ['required', 'string', 'max:180'],
            'inicio_em'              => ['nullable', 'date'],
            'fim_em'                 => ['nullable', 'date'],
            'tipo'                   => ['required', 'in:180,360'],
            'divergencia_tipo'       => ['required', 'in:percent,pontos'],
            'divergencia_valor'      => ['nullable', 'numeric', 'min:0'],
            'permitir_inicio_manual' => ['nullable', 'boolean'],
            'permitir_reabrir'       => ['nullable', 'boolean'],
            'peso_auto'              => ['required', 'numeric', 'min:0', 'max:100'],
            'peso_gestor'            => ['required', 'numeric', 'min:0', 'max:100'],
            'peso_pares'             => ['required', 'numeric', 'min:0', 'max:100'],
        ]);
        $v->validate();

        $tipo = $request->get('tipo');
        $pesoPares = (float) $request->get('peso_pares');
        if ($tipo === '180') {
            $pesoPares = 0;
        }

        DB::table('avd_ciclos')
            ->where('empresa_id', $empresaId)
            ->where('id', $id)
            ->update([
                'titulo'     => trim((string) $request->get('titulo')),
                'inicio_em'  => $request->get('inicio_em') ?: null,
                'fim_em'     => $request->get('fim_em') ?: null,
                'tipo'       => $tipo,
                'divergencia_tipo'  => $request->get('divergencia_tipo', 'percent'),
                'divergencia_valor' => (float) ($request->get('divergencia_valor', 0) ?: 0),
                'permitir_inicio_manual' => (bool) $request->boolean('permitir_inicio_manual', true),
                'permitir_reabrir'       => (bool) $request->boolean('permitir_reabrir', false),
                'peso_auto'   => (float) $request->get('peso_auto'),
                'peso_gestor' => (float) $request->get('peso_gestor'),
                'peso_pares'  => (float) $pesoPares,
                'msg_auto'    => $request->get('msg_auto'),
                'msg_gestor'  => $request->get('msg_gestor'),
                'msg_pares'   => $request->get('msg_pares'),
                'msg_consenso'=> $request->get('msg_consenso'),
                'msg_lembrete'=> $request->get('msg_lembrete'),
                'lembrete_cada_dias' => $request->get('lembrete_cada_dias') ?: null,
                'parar_lembrete_apos_responder' => (bool) $request->boolean('parar_lembrete_apos_responder', true),
                'updated_at'  => $this->nowDb(),
            ]);

        // se mudou para 180°, garante peso_pares = 0 em participantes (nota_pares pode ficar)
        if ($tipo === '180') {
            DB::table('avd_ciclo_participantes')
                ->where('empresa_id', $empresaId)
                ->where('ciclo_id', $id)
                ->update(['token_pares' => null]);
        }

        return back()->with('success', 'Ciclo atualizado.');
    }

    /*
    |--------------------------------------------------------------------------
    | Destroy (soft delete)
    |--------------------------------------------------------------------------
    */
    public function destroy(Request $request, string $sub, int $id)
    {
        $empresaId = $this->empresaId();
        $this->cicloOrFail($empresaId, $id);

        DB::table('avd_ciclos')
            ->where('empresa_id', $empresaId)
            ->where('id', $id)
            ->update([
                'deleted_at' => $this->nowDb(),
                'updated_at' => $this->nowDb(),
            ]);

        return redirect()->route('avd.ciclos.index', ['sub' => $sub])
            ->with('success', 'Ciclo removido.');
    }

    /*
    |--------------------------------------------------------------------------
    | Ações do ciclo: iniciar / encerrar
    |--------------------------------------------------------------------------
    */
    public function iniciar(Request $request, string $sub, int $id)
    {
        $empresaId = $this->empresaId();
        $ciclo = $this->cicloOrFail($empresaId, $id);

        if ($ciclo->status !== 'aguardando') {
            return back()->with('warning', 'Somente ciclos em "Aguardando" podem ser iniciados.');
        }

        DB::table('avd_ciclos')
            ->where('empresa_id', $empresaId)
            ->where('id', $id)
            ->update([
                'status' => 'iniciada',
                'updated_at' => $this->nowDb(),
            ]);

        // gera avaliações/tokens para todos participantes ainda sem avaliação
        $this->gerarAvaliacoesParaCiclo($empresaId, $sub, $id);

        return back()->with('success', 'Ciclo iniciado e avaliações geradas.');
    }

    public function encerrar(Request $request, string $sub, int $id)
    {
        $empresaId = $this->empresaId();
        $ciclo = $this->cicloOrFail($empresaId, $id);

        if (!in_array($ciclo->status, ['iniciada', 'em_consenso'], true)) {
            return back()->with('warning', 'Somente ciclos iniciados podem ser encerrados.');
        }

        DB::table('avd_ciclos')
            ->where('empresa_id', $empresaId)
            ->where('id', $id)
            ->update([
                'status' => 'encerrada',
                'updated_at' => $this->nowDb(),
            ]);

        return back()->with('success', 'Ciclo encerrado.');
    }

    /*
    |--------------------------------------------------------------------------
    | Unidades (Tab Unidades)
    |--------------------------------------------------------------------------
    */
    public function unidadesVincular(Request $request, string $sub, int $id)
    {
        $empresaId = $this->empresaId();
        $this->cicloOrFail($empresaId, $id);

        $modo = (string) $request->get('modo', 'uma'); // uma | todas
        $filialId = (int) $request->get('filial_id', 0);

        if ($modo === 'todas') {
            $filiais = DB::table('filiais')
                ->where('empresa_id', $empresaId)
                ->whereNull('deleted_at')
                ->select('id')
                ->get();

            foreach ($filiais as $f) {
                DB::table('avd_ciclo_unidades')->updateOrInsert([
                    'empresa_id' => $empresaId,
                    'ciclo_id'   => $id,
                    'filial_id'  => $f->id,
                ], [
                    'updated_at' => $this->nowDb(),
                    'created_at' => $this->nowDb(),
                ]);
            }

            return response()->json(['ok' => true]);
        }

        abort_if($filialId <= 0, 422, 'Filial inválida.');

        DB::table('avd_ciclo_unidades')->updateOrInsert([
            'empresa_id' => $empresaId,
            'ciclo_id'   => $id,
            'filial_id'  => $filialId,
        ], [
            'updated_at' => $this->nowDb(),
            'created_at' => $this->nowDb(),
        ]);

        return response()->json(['ok' => true]);
    }

    public function unidadesDesvincular(Request $request, string $sub, int $id, int $filialId)
    {
        $empresaId = $this->empresaId();
        $this->cicloOrFail($empresaId, $id);

        DB::table('avd_ciclo_unidades')
            ->where('empresa_id', $empresaId)
            ->where('ciclo_id', $id)
            ->where('filial_id', $filialId)
            ->delete();

        return response()->json(['ok' => true]);
    }

    /*
    |--------------------------------------------------------------------------
    | Participantes (Tab Colaboradores)
    |--------------------------------------------------------------------------
    */
    public function participantesVincular(Request $request, string $sub, int $id)
    {
        $empresaId = $this->empresaId();
        $ciclo = $this->cicloOrFail($empresaId, $id);

        $modo = (string) $request->get('modo', 'individual'); // individual | lote_filial
        $colaboradorId = (int) $request->get('colaborador_id', 0);
        $filialId = (int) $request->get('filial_id', 0);

        if ($modo === 'lote_filial') {
            abort_if($filialId <= 0, 422, 'Filial inválida.');

            $cols = DB::table('colaboradores')
                ->where('empresa_id', $empresaId)
                ->where('filial_id', $filialId)
                ->whereNull('deleted_at')
                ->select('id', 'filial_id', 'whatsapp')
                ->get();

            foreach ($cols as $c) {
                $this->upsertParticipante($empresaId, $id, $c->id, (int)$c->filial_id, (string)($c->whatsapp ?? null));
            }

            // se ciclo já iniciado, gera avaliações
            if ($ciclo->status === 'iniciada') {
                $this->gerarAvaliacoesParaCiclo($empresaId, $sub, $id);
            }

            return response()->json(['ok' => true]);
        }

        abort_if($colaboradorId <= 0, 422, 'Colaborador inválido.');

        $c = DB::table('colaboradores')
            ->where('empresa_id', $empresaId)
            ->where('id', $colaboradorId)
            ->whereNull('deleted_at')
            ->select('id', 'filial_id', 'whatsapp')
            ->first();

        abort_if(!$c, 404);

        $this->upsertParticipante($empresaId, $id, (int)$c->id, (int)($c->filial_id ?? 0), (string)($c->whatsapp ?? null));

        if ($ciclo->status === 'iniciada') {
            $this->gerarAvaliacoesParaCiclo($empresaId, $sub, $id);
        }

        return response()->json(['ok' => true]);
    }

    public function participantesRemover(Request $request, string $sub, int $id, int $participanteId)
    {
        $empresaId = $this->empresaId();
        $this->cicloOrFail($empresaId, $id);

        DB::table('avd_ciclo_participantes')
            ->where('empresa_id', $empresaId)
            ->where('ciclo_id', $id)
            ->where('id', $participanteId)
            ->update([
                'deleted_at' => $this->nowDb(),
                'updated_at' => $this->nowDb(),
            ]);

        // opcional: bloquear avaliações
        DB::table('avd_avaliacoes')
            ->where('empresa_id', $empresaId)
            ->where('ciclo_id', $id)
            ->where('participante_id', $participanteId)
            ->update(['status' => 'bloqueado', 'updated_at' => $this->nowDb()]);

        return response()->json(['ok' => true]);
    }

    public function participantesAtualizarWhatsapp(Request $request, string $sub, int $id, int $participanteId)
    {
        $empresaId = $this->empresaId();
        $this->cicloOrFail($empresaId, $id);

        $whatsapp = trim((string)$request->get('whatsapp', ''));

        DB::table('avd_ciclo_participantes')
            ->where('empresa_id', $empresaId)
            ->where('ciclo_id', $id)
            ->where('id', $participanteId)
            ->update(['whatsapp' => $whatsapp ?: null, 'updated_at' => $this->nowDb()]);

        return response()->json(['ok' => true]);
    }

    /*
    |--------------------------------------------------------------------------
    | Pilares (Tab Pilares)
    |--------------------------------------------------------------------------
    */
    public function pilaresSalvar(Request $request, string $sub, int $id)
    {
        $empresaId = $this->empresaId();
        $this->cicloOrFail($empresaId, $id);

        $nome = trim((string)$request->get('nome', ''));
        $peso = (float)$request->get('peso', 0);
        $pilarId = (int)$request->get('pilar_id', 0);

        abort_if($nome === '', 422, 'Nome inválido.');

        if ($pilarId > 0) {
            DB::table('avd_pilares')
                ->where('empresa_id', $empresaId)
                ->where('ciclo_id', $id)
                ->where('id', $pilarId)
                ->update([
                    'nome' => $nome,
                    'peso' => $peso,
                    'updated_at' => $this->nowDb(),
                ]);
        } else {
            DB::table('avd_pilares')->insert([
                'empresa_id' => $empresaId,
                'ciclo_id'   => $id,
                'nome'       => $nome,
                'peso'       => $peso,
                'ordem'      => (int)$request->get('ordem', 0),
                'created_at' => $this->nowDb(),
                'updated_at' => $this->nowDb(),
            ]);
        }

        return response()->json(['ok' => true]);
    }

    public function pilaresExcluir(Request $request, string $sub, int $id, int $pilarId)
    {
        $empresaId = $this->empresaId();
        $this->cicloOrFail($empresaId, $id);

        DB::table('avd_pilares')
            ->where('empresa_id', $empresaId)
            ->where('ciclo_id', $id)
            ->where('id', $pilarId)
            ->update(['deleted_at' => $this->nowDb(), 'updated_at' => $this->nowDb()]);

        // também “remove” perguntas do pilar
        DB::table('avd_perguntas')
            ->where('empresa_id', $empresaId)
            ->where('ciclo_id', $id)
            ->where('pilar_id', $pilarId)
            ->update(['deleted_at' => $this->nowDb(), 'updated_at' => $this->nowDb()]);

        return response()->json(['ok' => true]);
    }

    /*
    |--------------------------------------------------------------------------
    | Perguntas (Tab Perguntas)
    |--------------------------------------------------------------------------
    */
    public function perguntasSalvar(Request $request, string $sub, int $id)
    {
        $empresaId = $this->empresaId();
        $this->cicloOrFail($empresaId, $id);

        $perguntaId = (int)$request->get('pergunta_id', 0);
        $texto = trim((string)$request->get('texto', ''));
        $pilarId = (int)$request->get('pilar_id', 0);

        abort_if($texto === '', 422, 'Texto inválido.');
        abort_if($pilarId <= 0, 422, 'Pilar inválido.');

        $payload = [
            'empresa_id' => $empresaId,
            'ciclo_id'   => $id,
            'pilar_id'   => $pilarId,
            'texto'      => $texto,
            'peso'       => (float)$request->get('peso', 0),
            'tipo_resposta' => (string)$request->get('tipo_resposta', '1_5'),
            'opcoes'     => $request->get('opcoes') ? json_encode($request->get('opcoes')) : null,
            'exige_justificativa' => (bool)$request->boolean('exige_justificativa', false),
            'permite_comentario'  => (bool)$request->boolean('permite_comentario', true),
            'ordem'      => (int)$request->get('ordem', 0),
            'updated_at' => $this->nowDb(),
        ];

        if ($perguntaId > 0) {
            DB::table('avd_perguntas')
                ->where('empresa_id', $empresaId)
                ->where('ciclo_id', $id)
                ->where('id', $perguntaId)
                ->update($payload);
        } else {
            $payload['created_at'] = $this->nowDb();
            DB::table('avd_perguntas')->insert($payload);
        }

        return response()->json(['ok' => true]);
    }

    public function perguntasExcluir(Request $request, string $sub, int $id, int $perguntaId)
    {
        $empresaId = $this->empresaId();
        $this->cicloOrFail($empresaId, $id);

        DB::table('avd_perguntas')
            ->where('empresa_id', $empresaId)
            ->where('ciclo_id', $id)
            ->where('id', $perguntaId)
            ->update(['deleted_at' => $this->nowDb(), 'updated_at' => $this->nowDb()]);

        return response()->json(['ok' => true]);
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */
    private function upsertParticipante(int $empresaId, int $cicloId, int $colaboradorId, int $filialId = 0, ?string $whatsapp = null): void
    {
        DB::table('avd_ciclo_participantes')->updateOrInsert([
            'empresa_id'     => $empresaId,
            'ciclo_id'       => $cicloId,
            'colaborador_id' => $colaboradorId,
        ], [
            'filial_id'  => $filialId > 0 ? $filialId : null,
            'whatsapp'   => $whatsapp ?: null,
            'deleted_at' => null,
            'updated_at' => $this->nowDb(),
            'created_at' => $this->nowDb(),
        ]);
    }

    private function gerarAvaliacoesParaCiclo(int $empresaId, string $sub, int $cicloId): void
    {
        $ciclo = DB::table('avd_ciclos')->where('empresa_id', $empresaId)->where('id', $cicloId)->first();
        if (!$ciclo) return;

        $participantes = DB::table('avd_ciclo_participantes')
            ->where('empresa_id', $empresaId)
            ->where('ciclo_id', $cicloId)
            ->whereNull('deleted_at')
            ->get();

        foreach ($participantes as $p) {
            // garante tokens no participante
            $tokenAuto = $p->token_auto ?: Str::random(60);
            $tokenGestor = $p->token_gestor ?: Str::random(60);
            $tokenPares = ($ciclo->tipo === '360') ? ($p->token_pares ?: Str::random(60)) : null;

            DB::table('avd_ciclo_participantes')
                ->where('empresa_id', $empresaId)
                ->where('id', $p->id)
                ->update([
                    'token_auto'  => $tokenAuto,
                    'token_gestor'=> $tokenGestor,
                    'token_pares' => $tokenPares,
                    'updated_at'  => $this->nowDb(),
                ]);

            // cria/garante avaliações
            $this->upsertAvaliacao($empresaId, $cicloId, $p->id, 'auto', $tokenAuto);
            $this->upsertAvaliacao($empresaId, $cicloId, $p->id, 'gestor', $tokenGestor);
            if ($ciclo->tipo === '360') {
                $this->upsertAvaliacao($empresaId, $cicloId, $p->id, 'pares', $tokenPares);
            }

            // registra notificações (fila interna AVD)
            // OBS: envio WhatsApp pode ser um worker/cron que lê avd_notificacoes e joga na fila_mensagens
            $this->criarNotificacao($empresaId, $cicloId, $p->id, 'auto', $tokenAuto);
            $this->criarNotificacao($empresaId, $cicloId, $p->id, 'gestor', $tokenGestor);
            if ($ciclo->tipo === '360') {
                $this->criarNotificacao($empresaId, $cicloId, $p->id, 'pares', $tokenPares);
            }
        }
    }

    private function upsertAvaliacao(int $empresaId, int $cicloId, int $participanteId, string $tipo, ?string $token): void
    {
        if (!$token) return;

        DB::table('avd_avaliacoes')->updateOrInsert([
            'empresa_id'      => $empresaId,
            'ciclo_id'        => $cicloId,
            'participante_id' => $participanteId,
            'tipo'            => $tipo,
        ], [
            'token'      => $token,
            'status'     => 'pendente',
            'updated_at' => $this->nowDb(),
            'created_at' => $this->nowDb(),
        ]);
    }

    private function criarNotificacao(int $empresaId, int $cicloId, int $participanteId, string $tipo, ?string $token): void
    {
        if (!$token) return;

        DB::table('avd_notificacoes')->insert([
            'empresa_id'      => $empresaId,
            'ciclo_id'        => $cicloId,
            'participante_id' => $participanteId,
            'tipo'            => $tipo, // auto|gestor|pares|consenso|lembrete
            'canal'           => 'whatsapp',
            'status'          => 'pendente',
            'token'           => $token,
            'payload'         => json_encode(['token' => $token]),
            'created_at'      => $this->nowDb(),
            'updated_at'      => $this->nowDb(),
        ]);
    }
}
