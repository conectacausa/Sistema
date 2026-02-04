<?php

namespace App\Http\Controllers\Colaboradores;

use App\Http\Controllers\Controller;
use App\Jobs\ImportarColaboradoresJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ColaboradoresImportacaoController extends Controller
{
    public function index(Request $request, string $sub)
    {
        return view('colaboradores.importar');
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

        // Dispara Job na fila
        ImportarColaboradoresJob::dispatch($empresaId, $path, (int) auth()->id());

        return redirect()
            ->route('colaboradores.importar.index', ['sub' => $sub])
            ->with('success', 'Arquivo enviado! A importação foi colocada na fila para processamento.');
    }

    public function downloadModelo(Request $request, string $sub)
    {
        // Gera um Excel simples via PhpSpreadsheet
        // (normalmente já vem no projeto por dependência do ecossistema de Excel)
        $headers = [
            'nome',
            'cpf',
            'sexo',
            'data_admissao',
            'matricula',
        ];

        $exemplo = [
            ['João da Silva', '12345678901', 'M', '2026-01-10', '1001'],
            ['Maria Souza',   '98765432100', 'F', '2025-11-05', '1002'],
        ];

        return response()->streamDownload(function () use ($headers, $exemplo) {
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Colaboradores');

            // Cabeçalhos
            $col = 1;
            foreach ($headers as $h) {
                $sheet->setCellValueByColumnAndRow($col, 1, $h);
                $col++;
            }

            // Linhas exemplo
            $row = 2;
            foreach ($exemplo as $line) {
                $col = 1;
                foreach ($line as $val) {
                    $sheet->setCellValueByColumnAndRow($col, $row, $val);
                    $col++;
                }
                $row++;
            }

            // Ajuste largura
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
