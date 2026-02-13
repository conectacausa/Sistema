<?php

namespace App\Http\Controllers\AVD;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CiclosController extends Controller
{
  private function empresaId(): int
  {
    return (int) (auth()->user()->empresa_id ?? 0);
  }

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

    // filtro por filial: existe vínculo em avd_ciclo_unidades
    if ($filial !== '') {
      $query->whereExists(function ($sq) use ($filial) {
        $sq->select(DB::raw(1))
          ->from('avd_ciclo_unidades as cu')
          ->whereColumn('cu.ciclo_id', 'c.id')
          ->where('cu.filial_id', (int)$filial);
      });
    }

    $itens = $query->orderByDesc('c.id')->paginate(15)->withQueryString();

    if ($request->ajax() || $request->get('ajax') == '1') {
      return view('avd.ciclos.partials.tabela', compact('itens', 'q', 'status', 'filial'));
    }

    // Filiais para filtro
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

    // normaliza booleans
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

    // Dados auxiliares
    $filiais = DB::table('filiais')
      ->where('empresa_id', $empresaId)
      ->whereNull('deleted_at')
      ->orderBy('razao_social')
      ->get();

    $unidadesVinculadas = DB::table('avd_ciclo_unidades as cu')
      ->join('filiais as f', 'f.id', '=', 'cu.filial_id')
      ->where('cu.ciclo_id', $id)
      ->select('cu.id', 'cu.filial_id', 'f.nome_fantasia', 'f.cnpj')
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

    return view('avd.ciclos.edit', compact('ciclo', 'filiais', 'unidadesVinculadas', 'pilares', 'participantes'));
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
    // Aqui entra a automação: gerar avaliações + tokens + enfileirar WhatsApp
    // Vou deixar a assinatura pronta; na próxima etapa implementamos a geração.
    return back()->with('success', 'Ciclo iniciado (pendente implementar geração automática).');
  }

  public function encerrar(Request $request, string $sub, int $id)
  {
    return back()->with('success', 'Ciclo encerrado (pendente regras de bloqueio e consenso).');
  }
}
