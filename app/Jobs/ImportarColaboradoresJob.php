<?php

namespace App\Jobs;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ImportarColaboradoresJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public ?int $importacaoId = null;

    // compatibilidade com jobs antigos
    public ?int $empresaId = null;
    public ?string $path = null;
    public ?int $userId = null;

    public function __construct(...$args)
    {
        if (count($args) === 1) {
            $this->importacaoId = (int) $args[0];
        } elseif (count($args) >= 2) {
            $this->empresaId = (int) $args[0];
            $this->path = (string) $args[1];
            $this->userId = isset($args[2]) ? (int) $args[2] : null;
        }
    }

    public function handle(): void
    {
        if (!class_exists(\PhpOffice\PhpSpreadsheet\IOFactory::class)) {
            $this->failImportDb($this->importacaoId, 'Dependência ausente: phpoffice/phpspreadsheet.');
            return;
        }

        $imp = null;

        if (!empty($this->importacaoId)) {
            $imp = DB::table('colaboradores_importacoes')->where('id', (int) $this->importacaoId)->first();
        }

        if (!$imp && !empty($this->empresaId) && !empty($this->path)) {
            $imp = DB::table('colaboradores_importacoes')
                ->where('empresa_id', (int) $this->empresaId)
                ->where('arquivo_path', (string) $this->path)
                ->orderByDesc('id')
                ->first();
        }

        if (!$imp) {
            Log::warning('ImportarColaboradoresJob: importação não encontrada (DB)', [
                'importacao_id' => $this->importacaoId,
                'empresa_id' => $this->empresaId,
                'path' => $this->path,
            ]);
            return;
        }

        $importacaoId = (int) $imp->id;
        $empresaId = (int) $imp->empresa_id;
        $path = (string) $imp->arquivo_path;

        DB::table('colaboradores_importacoes')->where('id', $importacaoId)->update([
            'status' => 'processing',
            'started_at' => now(),
            'mensagem_erro' => null,
            'updated_at' => now(),
        ]);

        $fullPath = Storage::disk('local')->path($path);

        if (!file_exists($fullPath)) {
            $this->failImportDb($importacaoId, 'Arquivo não encontrado em disco: ' . $fullPath);
            return;
        }

        $this->processarXlsxDb($importacaoId, $empresaId, $fullPath);
    }

    private function processarXlsxDb(int $importacaoId, int $empresaId, string $fullPath): void
    {
        try {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($fullPath);
            $sheet = $spreadsheet->getSheetByName('Colaboradores') ?? $spreadsheet->getActiveSheet();

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
                $this->failImportDb($importacaoId, 'Cabeçalho inválido. Campos obrigatórios: nome, cpf.');
                return;
            }

            $totalLinhas = max(0, $highestRow - 1);
            DB::table('colaboradores_importacoes')->where('id', $importacaoId)->update([
                'total_linhas' => $totalLinhas,
                'updated_at' => now(),
            ]);

            $importados = 0;
            $ignorados  = 0;

            // ✅ rejeitados: vamos gravar CSV com (linha, motivo, nome, cpf_raw)
            $rejeitados = [];
            $rejeitados[] = ['linha', 'motivo', 'nome', 'cpf_raw'];

            for ($row = 2; $row <= $highestRow; $row++) {
                $values = $sheet->rangeToArray("A{$row}:{$highestCol}{$row}", null, true, false);
                $line = $values[0] ?? [];

                $nome = trim((string) ($line[$iNome] ?? ''));
                $cpfRaw = (string) ($line[$iCpf] ?? '');
                $cpf = preg_replace('/\D+/', '', $cpfRaw);

                $motivo = null;

                if ($nome === '') {
                    $motivo = 'Nome vazio';
                } elseif ($cpf === '') {
                    $motivo = 'CPF vazio';
                } elseif (strlen($cpf) !== 11) {
                    $motivo = 'CPF inválido (não tem 11 dígitos)';
                }

                if ($motivo) {
                    $ignorados++;
                    $rejeitados[] = [(string)$row, $motivo, $nome, $cpfRaw];
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

                $sx = null;
                if ($sexo) {
                    $tmp = mb_strtoupper($sexo);
                    if (in_array($tmp, ['M', 'F'], true)) $sx = $tmp;
                }

                $exists = DB::table('colaboradores')
                    ->where('empresa_id', $empresaId)
                    ->where('cpf', $cpf)
                    ->first();

                $now = now();

                if ($exists) {
                    DB::table('colaboradores')
                        ->where('id', (int) $exists->id)
                        ->update([
                            'nome' => $nome,
                            'sexo' => $sx ?? $exists->sexo,
                            'matricula' => $matricula ?: $exists->matricula,
                            'data_admissao' => $dataAdmissao ?: $exists->data_admissao,
                            'updated_at' => $now,
                        ]);
                } else {
                    DB::table('colaboradores')->insert([
                        'empresa_id' => $empresaId,
                        'cpf' => $cpf,
                        'nome' => $nome,
                        'sexo' => $sx,
                        'matricula' => $matricula,
                        'data_admissao' => $dataAdmissao,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }

                $importados++;

                if ((($importados + $ignorados) % 50) === 0) {
                    DB::table('colaboradores_importacoes')->where('id', $importacaoId)->update([
                        'importados' => $importados,
                        'ignorados' => $ignorados,
                        'updated_at' => $now,
                    ]);
                }
            }

            // ✅ grava CSV rejeitados se existir mais do que cabeçalho
            $rejeitadosCount = max(0, count($rejeitados) - 1);
            $rejeitadosPath = null;

            if ($rejeitadosCount > 0) {
                $rejeitadosPath = "imports/colaboradores/rejeitados_{$importacaoId}.csv";

                $fh = fopen('php://temp', 'w+');
                foreach ($rejeitados as $r) {
                    fputcsv($fh, $r, ';');
                }
                rewind($fh);
                $csv = stream_get_contents($fh);
                fclose($fh);

                Storage::disk('local')->put($rejeitadosPath, $csv);
            }

            DB::table('colaboradores_importacoes')->where('id', $importacaoId)->update([
                'status' => 'done',
                'importados' => $importados,
                'ignorados' => $ignorados,
                'rejeitados_count' => $rejeitadosCount,
                'rejeitados_path' => $rejeitadosPath,
                'finished_at' => now(),
                'updated_at' => now(),
            ]);

        } catch (\Throwable $e) {
            $this->failImportDb($importacaoId, $e->getMessage());
            Log::error('ImportarColaboradoresJob: erro ao processar XLSX', [
                'importacao_id' => $importacaoId,
                'empresa_id' => $empresaId,
                'file' => $fullPath,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function failImportDb(?int $importacaoId, string $msg): void
    {
        if ($importacaoId) {
            DB::table('colaboradores_importacoes')->where('id', (int) $importacaoId)->update([
                'status' => 'failed',
                'mensagem_erro' => $msg,
                'finished_at' => now(),
                'updated_at' => now(),
            ]);
            return;
        }

        Log::warning('ImportarColaboradoresJob: falha sem importacaoId', [
            'importacao_id' => $this->importacaoId,
            'empresa_id' => $this->empresaId,
            'path' => $this->path,
            'msg' => $msg,
        ]);
    }
}
