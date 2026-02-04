<?php

namespace App\Http\Controllers\Colaboradores;

use App\Http\Controllers\Controller;
use App\Jobs\ImportarColaboradoresJob;
use App\Models\ColaboradoresImportacao;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ColaboradoresImportacaoController extends Controller
{
    public function index(Request $request, string $sub)
    {
        $empresaId = (int) (auth()->user()->empresa_id ?? 0);

        $importacoes = ColaboradoresImportacao::query()
            ->where('empresa_id', $empresaId)
            ->orderByDesc('id')
            ->limit(20)
            ->get();

        return view('colaboradores.importar', [
            'importacoes' => $importacoes,
        ]);
    }

    public function store(Request $request, string $sub)
    {
        $empresaId = (int) (auth()->user()->empresa_id ?? 0);

        $request->validate([
            'arquivo' => ['required', 'file', 'mimes:xlsx', 'max:20480'], // 20MB
        ]);

        if ($empresaId <= 0) {
            return back()->with('error', 'Empresa inválida para importação.');
        }

        $file = $request->file('arquivo');

        $dir = 'imports/colaboradores';
        $filename = 'colaboradores_' . $empresaId . '_' . date('Ymd_His') . '_' . uniqid() . '.xlsx';
        $path = $file->storeAs($dir, $filename);

        $imp = ColaboradoresImportacao::create([
            'empresa_id' => $empresaId,
            'user_id' => (int) auth()->id(),
            'arquivo_path' => $path,
            'arquivo_nome' => $file->getClientOriginalName(),
            'status' => 'queued',
        ]);

        try {
            // ✅ força conexão/fila corretas e garante insert em `jobs`
            ImportarColaboradoresJob::dispatch($imp->id)
                ->onConnection('database')
                ->onQueue('default');
        } catch (\Throwable $e) {
            $imp->update([
                'status' => 'failed',
                'mensagem_erro' => 'Falha ao enfileirar job: ' . $e->getMessage(),
                'finished_at' => now(),
            ]);

            Log::error('Falha ao enfileirar ImportarColaboradoresJob', [
                'importacao_id' => $imp->id,
                'empresa_id' => $empresaId,
                'error' => $e->getMessage(),
            ]);

            return redirect()
                ->route('colaboradores.importar.index', ['sub' => $sub])
                ->with('error', 'Falha ao enfileirar a importação. Verifique logs.');
        }

        return redirect()
            ->route('colaboradores.importar.index', ['sub' => $sub])
            ->with('success', 'Arquivo enviado! A importação foi colocada na fila para processamento.');
    }

    public function downloadModelo(Request $request, string $sub)
    {
        $headers = ['nome', 'cpf', 'sexo', 'data_admissao', 'matricula'];
        $exemplo = [
            ['João da Silva', '12345678901', 'M', '2026-01-10', '1001'],
            ['Maria Souza',   '98765432100', 'F', '2025-11-05', '1002'],
        ];

        return response()->streamDownload(function () use ($headers, $exemplo) {
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Colaboradores');

            $col = 1;
            foreach ($headers as $h) {
                $sheet->setCellValueByColumnAndRow($col, 1, $h);
                $col++;
            }

            $row = 2;
            foreach ($exemplo as $line) {
                $col = 1;
                foreach ($line as $val) {
                    $sheet->setCellValueByColumnAndRow($col, $row, $val);
                    $col++;
                }
                $row++;
            }

            foreach (range('A', 'E') as $c) {
                $sheet->getColumnDimension($c)->setAutoSize(true);
            }

            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $writer->save('php://output');
        }, 'modelo_importacao_colaboradores.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}
