<?php

namespace App\Jobs;

use App\Models\Colaborador;
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

    public int $empresaId;
    public string $path;
    public int $userId;

    public function __construct(int $empresaId, string $path, int $userId)
    {
        $this->empresaId = $empresaId;
        $this->path = $path;
        $this->userId = $userId;
    }

    public function handle(): void
    {
        $fullPath = Storage::path($this->path);

        if (!file_exists($fullPath)) {
            Log::warning('ImportarColaboradoresJob: arquivo não encontrado', [
                'empresa_id' => $this->empresaId,
                'path' => $this->path,
            ]);
            return;
        }

        try {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($fullPath);
            $sheet = $spreadsheet->getActiveSheet();

            $highestRow = $sheet->getHighestRow();
            $highestCol = $sheet->getHighestColumn();

            // Lê cabeçalho (linha 1)
            $headerRow = $sheet->rangeToArray("A1:{$highestCol}1", null, true, false);
            $headers = array_map(function ($h) {
                return trim(mb_strtolower((string) $h));
            }, $headerRow[0] ?? []);

            $idx = function (string $name) use ($headers) {
                $pos = array_search($name, $headers, true);
                return $pos === false ? null : $pos;
            };

            $iNome = $idx('nome');
            $iCpf = $idx('cpf');
            $iSexo = $idx('sexo');
            $iData = $idx('data_admissao');
            $iMatricula = $idx('matricula');

            if ($iNome === null || $iCpf === null) {
                Log::warning('ImportarColaboradoresJob: cabeçalho inválido (nome/cpf obrigatórios)', [
                    'empresa_id' => $this->empresaId,
                    'headers' => $headers,
                ]);
                return;
            }

            $importados = 0;
            $ignorados = 0;

            // Processa a partir da linha 2
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
                $matricula = $iMatricula !== null ? trim((string) ($line[$iMatricula] ?? '')) : null;

                $dataAdmissao = null;
                if ($iData !== null) {
                    $cell = $line[$iData] ?? null;

                    // Pode vir como texto "YYYY-MM-DD" ou como serial numérico do Excel
                    if (is_numeric($cell)) {
                        $dt = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((float) $cell);
                        $dataAdmissao = Carbon::instance($dt)->format('Y-m-d');
                    } else {
                        $txt = trim((string) $cell);
                        if ($txt !== '') {
                            try {
                                $dataAdmissao = Carbon::parse($txt)->format('Y-m-d');
                            } catch (\Throwable $e) {
                                $dataAdmissao = null;
                            }
                        }
                    }
                }

                // Upsert por cpf + empresa_id
                $colaborador = Colaborador::query()
                    ->where('empresa_id', $this->empresaId)
                    ->where('cpf', $cpf)
                    ->first();

                if (!$colaborador) {
                    $colaborador = new Colaborador();
                    $colaborador->empresa_id = $this->empresaId;
                    $colaborador->cpf = $cpf;
                }

                $colaborador->nome = $nome;

                // Normaliza sexo
                if ($sexo !== null && $sexo !== '') {
                    $sx = mb_strtoupper($sexo);
                    if (in_array($sx, ['M', 'F'], true)) {
                        $colaborador->sexo = $sx;
                    }
                }

                if ($matricula !== null && $matricula !== '') {
                    $colaborador->matricula = $matricula;
                }

                if ($dataAdmissao) {
                    $colaborador->data_admissao = $dataAdmissao;
                }

                $colaborador->save();
                $importados++;
            }

            Log::info('ImportarColaboradoresJob: finalizado', [
                'empresa_id' => $this->empresaId,
                'user_id' => $this->userId,
                'importados' => $importados,
                'ignorados' => $ignorados,
                'path' => $this->path,
            ]);

        } catch (\Throwable $e) {
            Log::error('ImportarColaboradoresJob: erro ao processar', [
                'empresa_id' => $this->empresaId,
                'user_id' => $this->userId,
                'path' => $this->path,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
