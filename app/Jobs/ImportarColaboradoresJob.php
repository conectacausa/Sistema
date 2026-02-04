<?php

namespace App\Jobs;

use App\Models\Colaborador;
use App\Models\ColaboradoresImportacao;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ImportarColaboradoresJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $importacaoId;

    public function __construct(int $importacaoId)
    {
        $this->importacaoId = $importacaoId;
    }

    public function handle(): void
    {
        $imp = ColaboradoresImportacao::query()->find($this->importacaoId);

        if (!$imp) {
            Log::warning('ImportarColaboradoresJob: importação não encontrada', [
                'importacao_id' => $this->importacaoId,
            ]);
            return;
        }

        $imp->update([
            'status' => 'processing',
            'started_at' => now(),
            'mensagem_erro' => null,
        ]);

        $fullPath = Storage::path($imp->arquivo_path);

        if (!file_exists($fullPath)) {
            $imp->update([
                'status' => 'failed',
                'mensagem_erro' => 'Arquivo não encontrado em disco.',
                'finished_at' => now(),
            ]);
            return;
        }

        try {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($fullPath);
            $sheet = $spreadsheet->getActiveSheet();

            $highestRow = $sheet->getHighestRow();
            $highestCol = $sheet->getHighestColumn();

            $headerRow = $sheet->rangeToArray("A1:{$highestCol}1", null, true, false);
            $headers = array_map(fn($h) => trim(mb_strtolower((string) $h)), $headerRow[0] ?? []);

            $idx = function (string $name) use ($headers) {
                $pos = array_search($name, $headers, true);
                return $pos === false ? null : $pos;
            };

            $iNome = $idx('nome');
            $iCpf  = $idx('cpf');
            $iSexo = $idx('sexo');
            $iData = $idx('data_admissao');
            $iMat  = $idx('matricula');

            if ($iNome === null || $iCpf === null) {
                $imp->update([
                    'status' => 'failed',
                    'mensagem_erro' => 'Cabeçalho inválido. Campos obrigatórios: nome, cpf.',
                    'finished_at' => now(),
                ]);
                return;
            }

            $totalLinhas = max(0, $highestRow - 1);
            $imp->update(['total_linhas' => $totalLinhas]);

            $importados = 0;
            $ignorados  = 0;

            for ($row = 2; $row <= $highestRow; $row++) {
                $values = $sheet->rangeToArray("A{$row}:{$highestCol}{$row}", null, true, false);
                $line = $values[0] ?? [];

                $nome = trim((string) ($line[$iNome] ?? ''));
                $cpfRaw = (string) ($line[$iCpf] ?? '');
                $cpf = preg_replace('/\D+/', '', $cpfRaw);

                if ($nome === '' || $cpf === '' || strlen($cpf) !== 11) {
                    $ignorados++;
                    continue;
                }

                $sexo = $iSexo !== null ? trim((string) ($line[$iSexo] ?? '')) : null;
                $matricula = $iMat !== null ? trim((string) ($line[$iMat] ?? '')) : null;

                $dataAdmissao = null;
                if ($iData !== null) {
                    $cell = $line[$iData] ?? null;
                    if (is_numeric($cell)) {
                        $dt = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((float) $cell);
                        $dataAdmissao = Carbon::instance($dt)->format('Y-m-d');
                    } else {
                        $txt = trim((string) $cell);
                        if ($txt !== '') {
                            try { $dataAdmissao = Carbon::parse($txt)->format('Y-m-d'); } catch (\Throwable $e) {}
                        }
                    }
                }

                $colaborador = Colaborador::query()
                    ->where('empresa_id', (int) $imp->empresa_id)
                    ->where('cpf', $cpf)
                    ->first();

                if (!$colaborador) {
                    $colaborador = new Colaborador();
                    $colaborador->empresa_id = (int) $imp->empresa_id;
                    $colaborador->cpf = $cpf;
                }

                $colaborador->nome = $nome;

                if ($sexo) {
                    $sx = mb_strtoupper($sexo);
                    if (in_array($sx, ['M', 'F'], true)) {
                        $colaborador->sexo = $sx;
                    }
                }

                if ($matricula) {
                    $colaborador->matricula = $matricula;
                }

                if ($dataAdmissao) {
                    $colaborador->data_admissao = $dataAdmissao;
                }

                $colaborador->save();
                $importados++;

                // Atualiza progresso a cada 50 linhas (não pesa na fila)
                if (($importados + $ignorados) % 50 === 0) {
                    $imp->update([
                        'importados' => $importados,
                        'ignorados' => $ignorados,
                    ]);
                }
            }

            $imp->update([
                'status' => 'done',
                'importados' => $importados,
                'ignorados' => $ignorados,
                'finished_at' => now(),
            ]);

        } catch (\Throwable $e) {
            $imp->update([
                'status' => 'failed',
                'mensagem_erro' => $e->getMessage(),
                'finished_at' => now(),
            ]);

            Log::error('ImportarColaboradoresJob: erro ao processar', [
                'importacao_id' => $this->importacaoId,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
