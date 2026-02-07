<?php

namespace App\Http\Controllers\Config;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FilaMensagensController extends Controller
{
    private function empresaId(): int
    {
        return (int) (auth()->user()->empresa_id ?? 0);
    }

    private function podeAcessarTela(int $telaId): bool
    {
        $empresaId   = $this->empresaId();
        $permissaoId = (int) (auth()->user()->permissao_id ?? 0);

        if ($empresaId <= 0 || $permissaoId <= 0) {
            return false;
        }

        // A maioria das telas foi controlada por permissao_modulo_tela (conforme histórico do projeto)
        return DB::table('permissao_modulo_tela as pmt')
            ->where('pmt.permissao_id', $permissaoId)
            ->where('pmt.tela_id', $telaId)
            ->where(function ($q) {
                $q->whereNull('pmt.ativo')->orWhere('pmt.ativo', 1);
            })
            ->exists();
    }

    public function index(Request $request, string $sub)
    {
        $telaId = 16;

        if (!$this->podeAcessarTela($telaId)) {
            return redirect()->route('dashboard', ['sub' => $sub]);
        }

        $empresaId = $this->empresaId();

        $q         = trim((string) $request->get('q', ''));
        $status    = trim((string) $request->get('status', ''));
        $canal     = trim((string) $request->get('canal', ''));
        $prioridade = trim((string) $request->get('prioridade', ''));
        $dtIni     = trim((string) $request->get('dt_ini', ''));
        $dtFim     = trim((string) $request->get('dt_fim', ''));

        $query = DB::table('fila_mensagens as f')
            ->select([
                'f.id',
                'f.canal',
                'f.destinatario',
                'f.destinatario_nome',
                'f.assunto',
                'f.status',
                'f.prioridade',
                'f.attempts',
                'f.max_attempts',
                'f.available_at',
                'f.sent_at',
                'f.created_at',
            ])
            ->whereNull('f.deleted_at')
            ->where('f.empresa_id', $empresaId);

        if ($q !== '') {
            $query->where(function ($w) use ($q) {
                $w->where('f.destinatario', 'ilike', "%{$q}%")
                  ->orWhere('f.destinatario_nome', 'ilike', "%{$q}%")
                  ->orWhere('f.assunto', 'ilike', "%{$q}%")
                  ->orWhere('f.mensagem', 'ilike', "%{$q}%");
            });
        }

        if ($status !== '') {
            $query->where('f.status', $status);
        }

        if ($canal !== '') {
            $query->where('f.canal', $canal);
        }

        if ($prioridade !== '' && is_numeric($prioridade)) {
            $query->where('f.prioridade', (int) $prioridade);
        }

        if ($dtIni !== '') {
            $query->whereDate('f.created_at', '>=', $dtIni);
        }

        if ($dtFim !== '') {
            $query->whereDate('f.created_at', '<=', $dtFim);
        }

        // ordem: maior prioridade primeiro; depois as mais “disponíveis”
        $itens = $query
            ->orderByDesc('f.prioridade')
            ->orderByRaw("COALESCE(f.available_at, f.created_at) ASC")
            ->orderByDesc('f.id')
            ->paginate(20)
            ->appends($request->all());

        return view('config.fila.index', [
            'sub'   => $sub,
            'telaId'=> $telaId,
            'itens' => $itens,

            // manter filtros na tela
            'q' => $q,
            'status' => $status,
            'canal' => $canal,
            'prioridade' => $prioridade,
            'dtIni' => $dtIni,
            'dtFim' => $dtFim,
        ]);
    }

    // “Cancelar” (soft) sem apagar a linha: muda status para canceled
    public function cancelar(Request $request, string $sub, int $id)
    {
        $telaId = 16;

        if (!$this->podeAcessarTela($telaId)) {
            return response()->json(['ok' => false], 403);
        }

        $empresaId = $this->empresaId();

        DB::table('fila_mensagens')
            ->where('empresa_id', $empresaId)
            ->where('id', $id)
            ->whereNull('deleted_at')
            ->update([
                'status' => 'canceled',
                'updated_at' => now(),
            ]);

        return redirect()->route('config.fila.index', ['sub' => $sub])
            ->with('success', 'Mensagem cancelada.');
    }
}
