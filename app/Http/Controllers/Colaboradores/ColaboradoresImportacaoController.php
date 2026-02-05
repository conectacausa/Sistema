<?php

namespace App\Http\Controllers\Colaboradores;

use App\Http\Controllers\Controller;
use App\Jobs\ImportarColaboradoresJob;
use App\Models\ColaboradoresImportacao;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ColaboradoresImportacaoController extends Controller
{
    /**
     * Tela: Colaboradores > Importar
     * Slug: colaboradores/importar
     * Tela ID: 14
     */
    public function index(Request $request, string $sub)
    {
        $empresaId = (int) (auth()->user()->empresa_id ?? 0);

        $importacoes = ColaboradoresImportacao::query()
            ->where('empresa_id', $empresaId)
            ->orderByDesc('id')
            ->paginate(10);

        return view('colaboradores.importar', [
            'importacoes' => $importacoes,
        ]);
    }

    /**
     * Download do modelo Excel
     * Sugestão: manter em storage/app/private/templates/
     */
    public function downloadModelo(Request $request, string $sub)
    {
        $path = 'templates/modelo_importacao_colaboradores.xlsx';

        if (!Storage::disk('local')->exists($path)) {
            return back()->with('error', 'Modelo de importação não encontrado no servidor.');
        }

        return Storage::disk('local')->download($path, 'modelo_importacao_colaboradores.xlsx');
    }

    /**
     * Upload e enfileiramento do Job
     */
    public function store(Request $request, string $sub)
    {
        $empresaId = (int) (auth()->user()->empresa_id ?? 0);
        $userId    = (int) (auth()->id() ?? 0);

        $request->validate([
            'arquivo' => ['required', 'file', 'mimes:xlsx,xls', 'max:20480'], // 20MB
        ]);

        $file = $request->file('arquivo');

        $dir = 'imports/colaboradores';
        $ext = $file->getClientOriginalExtension() ?: 'xlsx';

        $nomeOriginal = $file->getClientOriginalName() ?: 'importacao.xlsx';

        $filename = 'colaboradores_' . $empresaId . '_' . now()->format('Ymd_His') . '_' . substr(md5(uniqid('', true)), 0, 12) . '.' . $ext;

        // ✅ salva explicitamente no disk local (no seu servidor: storage/app/private)
        $path = $file->storeAs($dir, $filename, 'local');

        // ✅ valida se gravou em disco mesmo
        $fullPath = Storage::disk('local')->path($path);
        if (!file_exists($fullPath)) {
            return back()->with('error', 'Falha ao salvar o arquivo de importação no servidor.');
        }

        $imp = ColaboradoresImportacao::create([
            'empresa_id' => $empresaId,
            'user_id' => $userId,
            'arquivo_path' => $path,
            'arquivo_nome' => $nomeOriginal,
            'status' => 'queued',
            'total_linhas' => null,
            'importados' => 0,
            'ignorados' => 0,
            'rejeitados_count' => 0,
            'rejeitados_path' => null,
            'mensagem_erro' => null,
            'started_at' => null,
            'finished_at' => null,
        ]);

        ImportarColaboradoresJob::dispatch($imp->id)
            ->onConnection('database')
            ->onQueue('default');

        return redirect()
            ->route('colaboradores.importar.index', ['sub' => $sub])
            ->with('success', 'Importação enviada para processamento.');
    }

    /**
     * Download do CSV de rejeitados
     */
    public function downloadRejeitados(Request $request, string $sub, int $id)
    {
        $empresaId = (int) (auth()->user()->empresa_id ?? 0);

        $imp = ColaboradoresImportacao::query()
            ->where('empresa_id', $empresaId)
            ->where('id', $id)
            ->firstOrFail();

        if (empty($imp->rejeitados_path) || !Storage::disk('local')->exists($imp->rejeitados_path)) {
            return back()->with('error', 'Arquivo de rejeitados não encontrado para esta importação.');
        }

        $filename = 'rejeitados_importacao_' . $imp->id . '.csv';

        return Storage::disk('local')->download($imp->rejeitados_path, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
