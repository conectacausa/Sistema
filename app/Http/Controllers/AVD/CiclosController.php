<?php

namespace App\Http\Controllers\AVD;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CiclosController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */
    private function empresaId(): int
    {
        return (int) (auth()->user()->empresa_id ?? 0);
    }

    private function cicloOrFail(int $empresaId, int $id)
    {
        $ciclo = DB::table('avd_ciclos')
            ->where('empresa_id', $empresaId)
            ->where('id', $id)
            ->whereNull('deleted_at')
            ->first();

        abort_if(!$ciclo, 404);
        return $ciclo;
    }

    private function makeUniqueToken(string $table, string $column = 'token', int $len = 60): string
    {
        do {
            $token = Str::random($len);
            $exists = DB::table($table)->where($column, $token)->exists();
        } while ($exists);

        return $token;
    }

    private function renderMensagem(?string $template, array $vars): string
    {
        $msg = $template ?? '';
        foreach ($vars as $k => $v) {
            $msg = str_replace('{' . $k . '}', (string) $v, $msg);
        }
        return trim($msg);
    }

    private function enqueueWhatsapp(
        int $empresaId,
        string $destinatario,
        ?string $destinatarioNome,
        string $mensagem,
        array $payload = [],
        int $prioridade = 5
    ): void {
        if (trim($destinatario) === '' || trim($mensagem) === '') return;

        DB::table('fila_mensagens')->insert([
            'empresa_id' => $empresaId,
            'canal' => 'whatsapp',
            'destinatario' => $destinatario,
            'destinatario_nome' => $destinatarioNome,
            'assunto' => 'Avaliação de Desempenho',
            'mensagem' => $mensagem,
            'payload' => json_encode($payload),
            'prioridade' => $prioridade,
            'status' => 'queued',
            'attempts' => 0,
            'max_attempts' => 3,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function ensureAvaliacao(int $empresaId, int $cicloId, int $participanteId, string $tipo, string $token): void
    {
        $exists = DB::table('avd_avaliacoes')
            ->where('empresa_id', $empresaId)
            ->where('ciclo_id', $cicloId)
            ->where('participante_id', $participanteId)
            ->where('tipo', $tipo)
            ->exists();

        if ($exists) return;

        DB::table('avd_avaliacoes')->insert([
            'empresa_id' => $empresaId,
            'ciclo_id' => $cicloId,
            'participante_id' => $participanteId,
            'tipo' => $tipo, // auto | gestor | pares | consenso
            'token' => $token,
            'status' => 'pendente',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Index + Grid
    |--------------------------------------------------------------------------
    */
    public function index(Request $request, string $sub)
    {
        $empresaId = $this->empresaId();

        $q      = trim((string) $request->get('q', ''));
        $status = trim((string) $request->get('status', ''));
        $filial = trim((string) $request->get('filial_id', ''));

        $query = DB::table('avd_ciclos as c')
            ->where('c.empresa_id', $empresaId)
            ->whereNull('c.deleted_at');

        if ($q !== '') $query->where('c.titulo', 'ilike', "%{$q}%");
        if ($status !== '') $query->where('c.status', $status);

        // filtro por filial (unidades vinculadas)
        if ($filial !== '') {
            $query->whereExists(function ($sq) use ($filial) {
                $sq->select(DB::raw(1))
                    ->from('avd_ciclo_unidades as cu')
                    ->whereColumn('cu.ciclo_id', 'c.id')
                    ->where('cu.filial_id', (int) $filial);
            });
        }

        $itens = $query->orderByDesc('c.id')->paginate(15)->withQueryString();

        if ($request->ajax() || $request->get('ajax') == '1') {
            return view('avd.ciclos.partials.tabela', compact('itens', 'q', 'status', 'filial'));
        }

        $filiais = DB::table('filiais')
            ->where('empresa_id', $empresaId)
            ->whereNull('deleted_at')
            ->orderBy('razao_social')
            ->get();

        return view('avd.ciclos.index', compact('itens', 'q', 'status', 'filial', 'filiais'));
    }

    public function create(Request $request, string $sub)
    {
        $ciclo = null;
        return view('avd.ciclos.edit', compact('ciclo'));
    }

    public function store(Request $request, string $sub)
    {
        $empresaId = $this->empresaId();

        $data = $request->validate([
            'titulo' => ['required', 'string', 'max:180'],
            'inicio_em' => ['nullable', 'date'],
            'fim_em' => ['nullable', 'date'],
            'tipo' => ['required', 'in:180,360'],
            'divergencia_tipo' => ['required', 'in:percent,pontos'],
            'divergencia_valor' => ['required', 'numeric', 'min:0'],
            'permitir_inicio_manual' => ['nullable'],
            'permitir_reabrir' => ['nullable'],
            'peso_auto' => ['required', 'numeric', 'min:0'],
            'peso_gestor' => ['required', 'numeric', 'min:0'],
            'peso_pares' => ['required', 'numeric', 'min:0'],
            'msg_auto' => ['nullable', 'string'],
            'msg_gestor' => ['nullable', 'string'],
            'msg_pares' => ['nullable', 'string'],
            'msg_consenso' => ['nullable', 'string'],
            'msg_lembrete' => ['nullable', 'string'],
            'lembrete_cada_dias' => ['nullable', 'integer', 'min:0'],
            'parar_lembrete_apos_responder' => ['nullable'],
        ]);

        $data['permitir_inicio_manual'] = (bool) $request->boolean('permitir_inicio_manual');
        $data['permitir_reabrir'] = (bool) $request->boolean('permitir_reabrir');
        $data['parar_lembrete_apos_responder'] = (bool) $request->boolean('parar_lembrete_apos_responder');

        $data['empresa_id'] = $empresaId;
        $data['status'] = 'aguardando';
        $data['created_at'] = now();
        $data['updated_at'] = now();

        $id = DB::table('avd_ciclos')->insertGetId($data);

        return redirect()->route('avd.ciclos.edit', ['sub' => $sub, 'id' => $id])
            ->with('success', 'Ciclo criado com sucesso.');
    }

    public function edit(Request $request, string $sub, int $id)
    {
        $empresaId = $this->empresaId();

        $ciclo = DB::table('avd_ciclos')
            ->where('empresa_id', $empresaId)
            ->where('id', $id)
            ->whereNull('deleted_at')
            ->first();

        abort_if(!$ciclo, 404);

        $filiais = DB::table('filiais')
            ->where('empresa_id', $empresaId)
            ->whereNull('deleted_at')
            ->orderBy('razao_social')
            ->get();

        $unidadesVinculadas = DB::table('avd_ciclo_unidades as cu')
            ->join('filiais as f', 'f.id', '=', 'cu.filial_id')
            ->where('cu.ciclo_id', $id)
            ->select('cu.id', 'cu.filial_id', 'f.nome_fantasia', 'f.razao_social', 'f.cnpj')
            ->orderBy('f.nome_fantasia')
            ->get();

        $pilares = DB::table('avd_pilares')
            ->where('empresa_id', $empresaId)
            ->where('ciclo_id', $id)
            ->whereNull('deleted_at')
            ->orderBy('ordem')
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

        $colaboradores = DB::table('colaboradores')
            ->where('empresa_id', $empresaId)
            ->whereNull('deleted_at')
            ->orderBy('nome')
            ->select('id', 'nome', 'cpf', 'filial_id')
            ->limit(2000)
            ->get();

        return view('avd.ciclos.edit', compact(
            'ciclo',
            'filiais',
            'unidadesVinculadas',
            'pilares',
            'participantes',
            'colaboradores'
        ));
    }

    public function update(Request $request, string $sub, int $id)
    {
        $empresaId = $this->empresaId();

        $exists = DB::table('avd_ciclos')
            ->where('empresa_id', $empresaId)
            ->where('id', $id)
            ->whereNull('deleted_at')
            ->exists();

        abort_if(!$exists, 404);

        $data = $request->validate([
            'titulo' => ['required', 'string', 'max:180'],
            'inicio_em' => ['nullable', 'date'],
            'fim_em' => ['nullable', 'date'],
            'tipo' => ['required', 'in:180,360'],
            'divergencia_tipo' => ['required', 'in:percent,pontos'],
            'divergencia_valor' => ['required', 'numeric', 'min:0'],
            'permitir_inicio_manual' => ['nullable'],
            'permitir_reabrir' => ['nullable'],
            'peso_auto' => ['required', 'numeric', 'min:0'],
            'peso_gestor' => ['required', 'numeric', 'min:0'],
            'peso_pares' => ['required', 'numeric', 'min:0'],
            'msg_auto' => ['nullable', 'string'],
            'msg_gestor' => ['nullable', 'string'],
            'msg_pares' => ['nullable', 'string'],
            'msg_consenso' => ['nullable', 'string'],
            'msg_lembrete' => ['nullable', 'string'],
            'lembrete_cada_dias' => ['nullable', 'integer', 'min:0'],
            'parar_lembrete_apos_responder' => ['nullable'],
        ]);

        $data['permitir_inicio_manual'] = (bool) $request->boolean('permitir_inicio_manual');
        $data['permitir_reabrir'] = (bool) $request->boolean('permitir_reabrir');
        $data['parar_lembrete_apos_responder'] = (bool) $request->boolean('parar_lembrete_apos_responder');
        $data['updated_at'] = now();

        DB::table('avd_ciclos')
            ->where('empresa_id', $empresaId)
            ->where('id', $id)
            ->update($data);

        return redirect()->route('avd.ciclos.edit', ['sub' => $sub, 'id' => $id])
            ->with('success', 'Ciclo atualizado com sucesso.');
    }

    public function destroy(Request $request, string $sub, int $id)
    {
        $empresaId = $this->empresaId();

        DB::table('avd_ciclos')
            ->where('empresa_id', $empresaId)
            ->where('id', $id)
            ->update(['deleted_at' => now(), 'updated_at' => now()]);

        return redirect()->route('avd.ciclos.index', ['sub' => $sub])
            ->with('success', 'Ciclo excluído.');
    }

    public function iniciar(Request $request, string $sub, int $id)
    {
        $empresaId = $this->empresaId();
        $this->cicloOrFail($empresaId, $id);

        DB::table('avd_ciclos')
            ->where('empresa_id', $empresaId)
            ->where('id', $id)
            ->update(['status' => 'iniciada', 'updated_at' => now()]);

        return back()->with('success', 'Ciclo iniciado.');
    }

    public function encerrar(Request $request, string $sub, int $id)
    {
        $empresaId = $this->empresaId();
        $this->cicloOrFail($empresaId, $id);

        DB::table('avd_ciclos')
            ->where('empresa_id', $empresaId)
            ->where('id', $id)
            ->update(['status' => 'encerrada', 'updated_at' => now()]);

        return back()->with('success', 'Ciclo encerrado.');
    }

    /*
    |--------------------------------------------------------------------------
    | TAB UNIDADES (AJAX)
    |--------------------------------------------------------------------------
    */
    public function tabUnidades(Request $request, string $sub, int $id)
    {
        $empresaId = $this->empresaId();
        $ciclo = $this->cicloOrFail($empresaId, $id);

        $unidadesVinculadas = DB::table('avd_ciclo_unidades as cu')
            ->join('filiais as f', 'f.id', '=', 'cu.filial_id')
            ->where('cu.ciclo_id', $id)
            ->select('cu.id', 'cu.filial_id', 'f.nome_fantasia', 'f.razao_social', 'f.cnpj')
            ->orderBy('f.nome_fantasia')
            ->get();

        $filiais = DB::table('filiais')
            ->where('empresa_id', $empresaId)
            ->whereNull('deleted_at')
            ->orderBy('razao_social')
            ->get();

        return view('avd.ciclos.partials.tab_unidades', compact('ciclo', 'unidadesVinculadas', 'filiais'));
    }

    public function unidadesVincular(Request $request, string $sub, int $id)
    {
        $empresaId = $this->empresaId();
        $this->cicloOrFail($empresaId, $id);

        $data = $request->validate([
            'filial_id' => ['required', 'integer'],
        ]);

        $filialOk = DB::table('filiais')
            ->where('empresa_id', $empresaId)
            ->where('id', (int) $data['filial_id'])
            ->whereNull('deleted_at')
            ->exists();

        abort_if(!$filialOk, 403);

        DB::table('avd_ciclo_unidades')->updateOrInsert(
            ['ciclo_id' => $id, 'filial_id' => (int) $data['filial_id']],
            ['empresa_id' => $empresaId, 'updated_at' => now(), 'created_at' => now()]
        );

        return response()->json(['ok' => true]);
    }

    public function unidadesDesvincular(Request $request, string $sub, int $id, int $vinculo_id)
    {
        $empresaId = $this->empresaId();
        $this->cicloOrFail($empresaId, $id);

        DB::table('avd_ciclo_unidades')
            ->where('id', $vinculo_id)
            ->where('ciclo_id', $id)
            ->delete();

        return response()->json(['ok' => true]);
    }

    /*
    |--------------------------------------------------------------------------
    | TAB PARTICIPANTES (AJAX)
    |--------------------------------------------------------------------------
    */
    public function tabParticipantes(Request $request, string $sub, int $id)
    {
        $empresaId = $this->empresaId();
        $ciclo = $this->cicloOrFail($empresaId, $id);

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

        $colaboradores = DB::table('colaboradores')
            ->where('empresa_id', $empresaId)
            ->whereNull('deleted_at')
            ->orderBy('nome')
            ->select('id', 'nome', 'cpf', 'filial_id')
            ->limit(2000)
            ->get();

        $filiais = DB::table('filiais')
            ->where('empresa_id', $empresaId)
            ->whereNull('deleted_at')
            ->orderBy('razao_social')
            ->get();

        return view('avd.ciclos.partials.tab_participantes', compact('ciclo', 'participantes', 'colaboradores', 'filiais'));
    }

    /*
    |--------------------------------------------------------------------------
    | PARTICIPANTES - VINCULAR INDIVIDUAL
    |--------------------------------------------------------------------------
    */
    public function participantesVincular(Request $request, string $sub, int $id)
    {
        $empresaId = $this->empresaId();
        $ciclo = $this->cicloOrFail($empresaId, $id);

        $data = $request->validate([
            'colaborador_id' => ['required', 'integer'],
            'whatsapp' => ['nullable', 'string', 'max:30'],
        ]);

        $colaborador = DB::table('colaboradores')
            ->where('empresa_id', $empresaId)
            ->where('id', (int) $data['colaborador_id'])
            ->whereNull('deleted_at')
            ->first();

        abort_if(!$colaborador, 422);

        DB::beginTransaction();
        try {
            $exist = DB::table('avd_ciclo_participantes')
                ->where('empresa_id', $empresaId)
                ->where('ciclo_id', $id)
                ->where('colaborador_id', (int) $data['colaborador_id'])
                ->first();

            if ($exist && $exist->deleted_at) {
                DB::table('avd_ciclo_participantes')
                    ->where('id', $exist->id)
                    ->update(['deleted_at' => null, 'updated_at' => now()]);
                $participanteId = (int) $exist->id;
            } elseif ($exist) {
                DB::commit();
                return response()->json(['ok' => true, 'already' => true]);
            } else {
                $participanteId = DB::table('avd_ciclo_participantes')->insertGetId([
                    'empresa_id' => $empresaId,
                    'ciclo_id' => $id,
                    'colaborador_id' => (int) $data['colaborador_id'],
                    'filial_id' => $colaborador->filial_id ?? null,
                    'whatsapp' => $data['whatsapp'] ?? null,

                    // gestor ainda será configurado (UI)
                    'gestor_usuario_id' => null,
                    'gestor_colaborador_id' => null,

                    'status' => 'pendente',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $p = DB::table('avd_ciclo_participantes')->where('id', $participanteId)->first();

            // tokens do participante
            $tokenAuto = $p->token_auto ?: $this->makeUniqueToken('avd_avaliacoes', 'token', 60);
            $tokenGestor = $p->token_gestor ?: $this->makeUniqueToken('avd_avaliacoes', 'token', 60);
            $tokenPares = null;

            if (($ciclo->tipo ?? '180') === '360') {
                $tokenPares = $p->token_pares ?: $this->makeUniqueToken('avd_avaliacoes', 'token', 60);
            }

            DB::table('avd_ciclo_participantes')->where('id', $participanteId)->update([
                'token_auto' => $tokenAuto,
                'token_gestor' => $tokenGestor,
                'token_pares' => $tokenPares,
                'updated_at' => now(),
            ]);

            // cria avaliações
            $this->ensureAvaliacao($empresaId, $id, $participanteId, 'auto', $tokenAuto);
            $this->ensureAvaliacao($empresaId, $id, $participanteId, 'gestor', $tokenGestor);

            if (($ciclo->tipo ?? '180') === '360' && $tokenPares) {
                $this->ensureAvaliacao($empresaId, $id, $participanteId, 'pares', $tokenPares);
            }

            // enfileira whatsapp (auto) se houver whatsapp
            $empresaNome = DB::table('empresas')->where('id', $empresaId)->value('nome_fantasia')
                ?? DB::table('empresas')->where('id', $empresaId)->value('razao_social')
                ?? 'Empresa';

            $linkAuto = url("/avaliacao/{$tokenAuto}");
            $vars = [
                'nome' => $colaborador->nome ?? 'Colaborador',
                'empresa' => $empresaNome,
                'link' => $linkAuto,
                'data_limite' => $ciclo->fim_em ? \Carbon\Carbon::parse($ciclo->fim_em)->format('d/m/Y') : '',
            ];

            $msgAutoTpl = $ciclo->msg_auto ?: "Olá {nome}! Você tem uma autoavaliação disponível em {empresa}. Acesse: {link}";
            $msgAuto = $this->renderMensagem($msgAutoTpl, $vars);

            if (!empty($p->whatsapp)) {
                $this->enqueueWhatsapp($empresaId, $p->whatsapp, $colaborador->nome ?? null, $msgAuto, [
                    'ciclo_id' => $id,
                    'participante_id' => $participanteId,
                    'tipo' => 'auto',
                    'token' => $tokenAuto,
                ]);
            }

            DB::commit();
            return response()->json(['ok' => true]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['ok' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | PARTICIPANTES - VINCULAR LOTE (POR FILIAL)
    |--------------------------------------------------------------------------
    */
    public function participantesVincularLote(Request $request, string $sub, int $id)
    {
        $empresaId = $this->empresaId();
        $this->cicloOrFail($empresaId, $id);

        $data = $request->validate([
            'filial_id' => ['required', 'integer'],
        ]);

        $filialId = (int) $data['filial_id'];

        $colabs = DB::table('colaboradores')
            ->where('empresa_id', $empresaId)
            ->where('filial_id', $filialId)
            ->whereNull('deleted_at')
            ->select('id')
            ->get();

        $count = 0;
        foreach ($colabs as $c) {
            // reusa a lógica do individual, sem whatsapp
            $fake = new Request(['colaborador_id' => $c->id]);
            $this->participantesVincular($fake, $sub, $id);
            $count++;
        }

        return response()->json(['ok' => true, 'count' => $count]);
    }

    /*
    |--------------------------------------------------------------------------
    | PARTICIPANTES - ATUALIZAR (whatsapp / gestor)
    |--------------------------------------------------------------------------
    */
    public function participantesAtualizar(Request $request, string $sub, int $id, int $pid)
    {
        $empresaId = $this->empresaId();
        $this->cicloOrFail($empresaId, $id);

        $data = $request->validate([
            'whatsapp' => ['nullable', 'string', 'max:30'],
            'gestor_usuario_id' => ['nullable', 'integer'],
            'gestor_colaborador_id' => ['nullable', 'integer'],
        ]);

        DB::table('avd_ciclo_participantes')
            ->where('empresa_id', $empresaId)
            ->where('ciclo_id', $id)
            ->where('id', $pid)
            ->update([
                'whatsapp' => $data['whatsapp'] ?? null,
                'gestor_usuario_id' => $data['gestor_usuario_id'] ?? null,
                'gestor_colaborador_id' => $data['gestor_colaborador_id'] ?? null,
                'updated_at' => now(),
            ]);

        return response()->json(['ok' => true]);
    }

    /*
    |--------------------------------------------------------------------------
    | PARTICIPANTES - REMOVER (soft delete)
    |--------------------------------------------------------------------------
    */
    public function participantesRemover(Request $request, string $sub, int $id, int $pid)
    {
        $empresaId = $this->empresaId();
        $this->cicloOrFail($empresaId, $id);

        DB::table('avd_ciclo_participantes')
            ->where('empresa_id', $empresaId)
            ->where('ciclo_id', $id)
            ->where('id', $pid)
            ->update(['deleted_at' => now(), 'updated_at' => now()]);

        return response()->json(['ok' => true]);
    }
}
